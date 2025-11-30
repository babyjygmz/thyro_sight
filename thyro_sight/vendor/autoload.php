<?php
// Root autoloader for ThyroSight project
// This file makes all classes available to the PHP language server

// Load PHPMailer classes using the official autoloader
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';
require_once __DIR__ . '/phpmailer/src/Exception.php';

// Register class aliases for VS Code PHP language server
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    class_alias('PHPMailer\PHPMailer\PHPMailer', 'PHPMailer\PHPMailer\PHPMailer');
}

if (!class_exists('PHPMailer\PHPMailer\SMTP')) {
    class_alias('PHPMailer\PHPMailer\SMTP', 'PHPMailer\PHPMailer\SMTP');
}

if (!class_exists('PHPMailer\PHPMailer\Exception')) {
    class_alias('PHPMailer\PHPMailer\Exception', 'PHPMailer\PHPMailer\Exception');
}

// Register any other classes here if needed
