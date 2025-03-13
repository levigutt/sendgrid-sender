#!php
<?php

require_once 'vendor/autoload.php';
require_once 'config.php';

if ( 1 == count($argv) )
    die('no email file provided');

$file_name = $argv[1];

if ( !file_exists($file_name) )
    die('email file does not exist');

$file_content = file_get_contents($file_name);
$file_lines = explode("\n", $file_content);

$emails = array_filter($file_lines, fn($x) => filter_var($x, FILTER_VALIDATE_EMAIL));
printf("found %d emails in %d lines\n", count($emails), count($file_lines));


$sendgrid = new \SendGrid(SENDGRID_API_KEY);

foreach( $emails as $key => $val )
    print "$key => $val\n";
