#!php
<?php

require_once 'vendor/autoload.php';
require_once 'config.php';

$file_name      = null;
$dry_run        = false;
$template_id    = DEFAULT_TEMPLATE_ID;
$from_email     = DEFAULT_FROM_EMAIL;
$halt           = false;
foreach ( $argv as $key => $arg )
{
    if( $key == 0 )
        continue;

    // process file names
    if ( !str_contains($arg, '=') && '-' != substr($arg, 0, 1) )
    {
        if ( $file_name )
        {
            errf("Can only specify one file. '%s' rejected\n", $arg);
            $halt = true;
            continue;
        }
        $file_name = $arg;
        if ( !file_exists($file_name) )
        {
            errf("File '%s' does not exist\n", $file_name);
            $halt = true;
        }
        continue;
    }

    // process switches
    $parts = explode('=', $arg);
    switch ( $parts[0] )
    {
        case "--dry-run": $dry_run  = true; break;
        case "--from" :
            if ( 0 == strlen($parts[1]) )
            {
                errf("Option '%s' missing value\n", $parts[0]);
                $halt = true;
                continue 2;
            }
            $from_email = $parts[1];
        break;
        case "--template" :
            if ( 0 == strlen($parts[1]) )
            {
                errf("Option '%s' missing value\n", $parts[0]);
                $halt = true;
                continue 2;
            }
            $template_id = $parts[1];
        break;
        default :
            errf("Option '%s' not recognized\n", $parts[0]);
            $halt = true;
            continue 2;
        break;
    }
}
if ( $halt )
    die;

if ( !$file_name )
    die("No file provided\n");

if ( $from_email == DEFAULT_FROM_EMAIL )
    printf("Using default from email: %s\n", $from_email);

if ( $template_id == DEFAULT_TEMPLATE_ID )
    printf("Using default template: %s\n", $template_id);

printf("Loading emails from file '%s'\n", $file_name);
$file_content = file_get_contents($file_name);
$file_lines = explode("\n", $file_content);
$file_lines = array_filter($file_lines, fn($x) => filter_var($x, FILTER_SANITIZE_EMAIL));
$emails = array_filter($file_lines, fn($x) => filter_var($x, FILTER_VALIDATE_EMAIL));
$emails = array_unique($emails);
printf("Found %d unique emails in %d lines\n", count($emails), count($file_lines));

if ( !$dry_run )
{
    print "\nThis run is NOT dry!\nWill send in 10 seconds (ctrl+C to stop)\n";
    print("10  ");
    foreach ( range(9, 0) as $num )
    {
        usleep(1_000_000);
        printf("%d  ", $num);
    }
    print("\n");
}
else
{
    print("This run is dry\n");
    sleep(2);
}

// SendGrid accepts maximum 1000 recipients in each call
$email_chunks = array_chunk($emails, 1000);
foreach($email_chunks as $email_num => $email_chunk)
{
    $mail = new \SendGrid\Mail\Mail();
    $mail->setFrom($from_email);
    if ( $template_id )
    {
        $mail->setTemplateId($template_id);
    }
    else
    {
        die("Missing HTML or template for email\n");
        //html option not implemented yet
    }

    $mail_settings = new \SendGrid\Mail\MailSettings();
    $mail_settings->setSandboxMode($dry_run);
    $mail->setMailSettings($mail_settings);

    foreach( $email_chunk as $email )
    {
        $to = new \SendGrid\Mail\To($email);
        $personalization = new \SendGrid\Mail\Personalization();
        $personalization->addTo($to);
        $mail->addPersonalization($personalization);
    }

    printf("\nChunk %d of %d, with %d recipients\n",
        $email_num+1, count($email_chunks), count($email_chunk));

    $sendgrid = new \SendGrid(SENDGRID_API_KEY);
    $result = $sendgrid->client->mail()->send()->post($mail);
    $statusCode = $result->statusCode();
    $body = json_decode($result->body());

    if ( $statusCode < 200 || $statusCode > 299 )
    {
        errf("SendGrid responded with error code: %d\n", $statusCode);
        foreach( $body->errors as $errnum => $error )
        {
            errf("Error %d\n", $errnum);
            foreach( $error as $key => $val )
                errf("\t%s => %s\n", $key, $val);
        }
        continue;
    }

    print("OK\n");
}


function errf(string $str, ...$args)
{
    return fwrite(STDERR, sprintf($str, ...$args));
}
