#!php
<?php

require_once 'vendor/autoload.php';
require_once 'config.php';

$file_name      = null;
$dry_run        = false;
$debug          = false;
$template_id    = null;
$from_email     = null;
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
            print("Can only specify one file. $arg rejected\n");
            $halt = true;
            continue;
        }
        $file_name = $arg;
        if ( !file_exists($file_name) )
        {
            printf("File '%s' does not exist\n", $file_name);
            $halt = true;
        }
        continue;
    }

    // process switches
    $parts = explode('=', $arg);
    switch ( $parts[0] )
    {
        case "--dry-run": $dry_run  = true; break;
        case "--debug"  : $debug    = true; break;
        case "--from" :
            if ( 0 == strlen($parts[1]) )
            {
                printf("Option '%s' missing value\n", $parts[0]);
                $halt = true;
                continue 2;
            }
            $from_email = $parts[1];
        break;
        case "--template" :
            if ( 0 == strlen($parts[1]) )
            {
                printf("Option '%s' missing value\n", $parts[0]);
                $halt = true;
                continue 2;
            }
            $template_id = $parts[1];
        break;
        default :
            printf("Option '%s' not recognized\n", $parts[0]);
            $halt = true;
        break;
    }
}
if ( $halt ) die;

if ( !$file_name )
    die("No file provided\n");

$file_content = file_get_contents($file_name);
$file_lines = explode("\n", $file_content);
$file_lines = array_filter($file_lines, fn($x) => filter_var($x, FILTER_SANITIZE_EMAIL));
$emails = array_filter($file_lines, fn($x) => filter_var($x, FILTER_VALIDATE_EMAIL));
printf("Found %d emails in %d lines\n", count($emails), count($file_lines));

if ( !$dry_run )
{
    print "\nThis run is NOT dry!\nCtrl+C to stop\n";
    foreach ( range(10, 1) as $num )
    {
        printf("%d\n", $num);
        sleep(1);
    }
}
else
{
    print("\nThis run is dry\n");
    sleep(2);
}

// SendGrid accepts maximum 1000 recipients in each call
$email_chunks = array_chunk($emails, 1000);
foreach($email_chunks as $email_chunk)
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


    $sendgrid = new \SendGrid(SENDGRID_API_KEY);
    $result = $sendgrid->client->mail()->send()->post($mail);

    if ( $debug )
    {
        print_r(['email_count' => count($email_chunk), 'result' => $result]);
    }
}
