#!php
<?php

require_once 'vendor/autoload.php';
require_once 'config.php';

$sendgrid_template = 'd-520eadfd50f2475a93a08afd9b0eb073';

if ( 1 == count($argv) )
    die('no email file provided');

$file_name = $argv[1];

if ( !file_exists($file_name) )
    die('email file does not exist');

$file_content = file_get_contents($file_name);
$file_lines = explode("\n", $file_content);

$emails = array_filter($file_lines, fn($x) => filter_var($x, FILTER_VALIDATE_EMAIL));
printf("found %d emails in %d lines\n", count($emails), count($file_lines));


$mail = new \SendGrid\Mail\Mail();
$mail->setFrom('send@stafettforlivet.no');
$mail->setTemplateId($sendgrid_template);

$mail_settings = new \SendGrid\Mail\MailSettings();
$mail_settings->setSandboxMode(true);
$mail->setMailSettings($mail_settings);

foreach( $emails as $email )
{
    $to = new \SendGrid\Mail\To($email);
    $personalization = new \SendGrid\Mail\Personalization();
    $personalization->addTo($to);
    $mail->addPersonalization($personalization);
}


$sendgrid = new \SendGrid(SENDGRID_API_KEY);
$result = $sendgrid->client->mail()->send()->post($mail);

print_r($result);

