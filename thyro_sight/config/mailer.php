<?php
// PHPMailer Configuration
// This file contains email configuration and helper functions

// Load PHPMailer classes
require_once __DIR__ . '/../vendor/autoload.php';

// Email configuration constants (these should match the ones in database.php)
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USERNAME')) define('SMTP_USERNAME', 'thyrosight@gmail.com');
if (!defined('SMTP_PASSWORD')) define('SMTP_PASSWORD', 'vqti cmzi msjx rylk'); // Gmail App Password
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'thyrosight@gmail.com');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'ThyroSight');

// Function to check if PHPMailer is available
function isPHPMailerAvailable() {
    return class_exists('PHPMailer\PHPMailer\PHPMailer') || class_exists('\PHPMailer\PHPMailer\PHPMailer');
}

// Function to get PHPMailer instance
function getPHPMailer() {
    if (!isPHPMailerAvailable()) {
        throw new Exception('PHPMailer is not available. Please install it via Composer.');
    }
    
    // Use the correct namespace for PHPMailer
    return new \PHPMailer\PHPMailer\PHPMailer(true);
}

// Function to configure PHPMailer with default settings
function configurePHPMailer($mail) {
    // Server settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    
    // SSL/TLS settings for development environment
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Default settings
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    
    // Set default from address
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    
    return $mail;
}

// Function to send a simple email
function sendSimpleEmail($to, $toName, $subject, $body, $altBody = '') {
    try {
        $mail = getPHPMailer();
        $mail = configurePHPMailer($mail);
        
        // Recipients
        $mail->addAddress($to, $toName);
        
        // Content
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

// Function to send OTP email with custom template
function sendOTPEmailTemplate($to, $toName, $otp, $expiryMinutes = 1.5) {
    $subject = 'ThyroSight - Password Reset OTP';
    
    $emailBody = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <div style='background: linear-gradient(135deg, #2563eb, #10b981); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
            <h1 style='color: white; margin: 0; font-size: 24px;'>ThyroSight</h1>
            <p style='color: white; margin: 10px 0 0 0; opacity: 0.9;'>Password Reset Request</p>
        </div>
        
        <div style='background: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);'>
            <h2 style='color: #1e293b; margin-bottom: 20px;'>Hello {$toName},</h2>
            
            <p style='color: #64748b; line-height: 1.6; margin-bottom: 25px;'>
                You have requested to reset your password for your ThyroSight account. 
                Use the verification code below to complete the process.
            </p>
            
            <div style='background: #f8fafc; padding: 20px; border-radius: 10px; text-align: center; margin: 25px 0;'>
                <h3 style='color: #1e293b; margin: 0 0 15px 0; font-size: 18px;'>Your Verification Code</h3>
                <div style='background: #2563eb; color: white; font-size: 32px; font-weight: bold; padding: 15px; border-radius: 8px; letter-spacing: 5px; font-family: monospace;'>
                    {$otp}
                </div>
            </div>
            
            <p style='color: #64748b; line-height: 1.6; margin-bottom: 20px;'>
                <strong>Important:</strong> This code will expire in 1 minute and 30 seconds for security reasons.
            </p>
            
            <p style='color: #64748b; line-height: 1.6; margin-bottom: 20px;'>
                If you didn't request this password reset, please ignore this email or contact our support team.
            </p>
            
            <div style='text-align: center; margin-top: 30px;'>
                <a href='https://thyrosight.com' style='background: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; display: inline-block; font-weight: 500;'>
                    Visit ThyroSight
                </a>
            </div>
        </div>
        
        <div style='text-align: center; margin-top: 20px; color: #94a3b8; font-size: 14px;'>
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; 2025 ThyroSight. All rights reserved.</p>
        </div>
    </div>
    ";
    
    $altBody = "Hello {$toName},\n\nYour password reset OTP is: {$otp}\n\nThis code expires in 1 minute and 30 seconds.\n\nIf you didn't request this, please ignore this email.\n\nBest regards,\nThyroSight Team";
    
    return sendSimpleEmail($to, $toName, $subject, $emailBody, $altBody);
}

// Function to send welcome email
function sendWelcomeEmail($to, $toName) {
    $subject = 'Welcome to ThyroSight!';
    
    $emailBody = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <div style='background: linear-gradient(135deg, #2563eb, #10b981); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
            <h1 style='color: white; margin: 0; font-size: 24px;'>Welcome to ThyroSight!</h1>
            <p style='color: white; margin: 10px 0 0 0; opacity: 0.9;'>Your thyroid health journey begins here</p>
        </div>
        
        <div style='background: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);'>
            <h2 style='color: #1e293b; margin-bottom: 20px;'>Hello {$toName},</h2>
            
            <p style='color: #64748b; line-height: 1.6; margin-bottom: 25px;'>
                Welcome to ThyroSight! We're excited to have you on board. 
                Your account has been successfully created and you can now access all our features.
            </p>
            
            <div style='background: #f8fafc; padding: 20px; border-radius: 10px; margin: 25px 0;'>
                <h3 style='color: #1e293b; margin: 0 0 15px 0; font-size: 18px;'>What's Next?</h3>
                <ul style='color: #64748b; line-height: 1.8; margin: 0; padding-left: 20px;'>
                    <li>Complete your profile information</li>
                    <li>Explore our thyroid health assessment tools</li>
                    <li>Schedule your first consultation</li>
                    <li>Join our community forums</li>
                </ul>
            </div>
            
            <p style='color: #64748b; line-height: 1.6; margin-bottom: 20px;'>
                If you have any questions or need assistance, our support team is here to help.
            </p>
            
            <div style='text-align: center; margin-top: 30px;'>
                <a href='https://thyrosight.com/login' style='background: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; display: inline-block; font-weight: 500; margin-right: 15px;'>
                    Sign In Now
                </a>
                <a href='https://thyrosight.com' style='background: transparent; color: #2563eb; padding: 12px 30px; text-decoration: none; border-radius: 25px; display: inline-block; font-weight: 500; border: 2px solid #2563eb;'>
                    Explore Features
                </a>
            </div>
        </div>
        
        <div style='text-align: center; margin-top: 20px; color: #94a3b8; font-size: 14px;'>
            <p>Thank you for choosing ThyroSight for your thyroid health needs.</p>
            <p>&copy; 2025 ThyroSight. All rights reserved.</p>
        </div>
    </div>
    ";
    
    $altBody = "Hello {$toName},\n\nWelcome to ThyroSight! Your account has been successfully created.\n\nYou can now sign in and explore all our features.\n\nBest regards,\nThyroSight Team";
    
    return sendSimpleEmail($to, $toName, $subject, $emailBody, $altBody);
}

// Function to validate email configuration
function validateEmailConfig() {
    $errors = [];
    
    if (empty(SMTP_HOST)) $errors[] = 'SMTP_HOST is not configured';
    if (empty(SMTP_PORT)) $errors[] = 'SMTP_PORT is not configured';
    if (empty(SMTP_USERNAME)) $errors[] = 'SMTP_USERNAME is not configured';
    if (empty(SMTP_PASSWORD) || SMTP_PASSWORD === 'your_app_password_here') {
        $errors[] = 'SMTP_PASSWORD is not configured or still using default value';
    }
    if (empty(SMTP_FROM_EMAIL)) $errors[] = 'SMTP_FROM_EMAIL is not configured';
    if (empty(SMTP_FROM_NAME)) $errors[] = 'SMTP_FROM_NAME is not configured';
    
    return $errors;
}

// Function to test email configuration
function testEmailConfiguration() {
    $errors = validateEmailConfig();
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    if (!isPHPMailerAvailable()) {
        return ['success' => false, 'errors' => ['PHPMailer is not available']];
    }
    
    try {
        $mail = getPHPMailer();
        $mail = configurePHPMailer($mail);
        
        // Test connection
        $mail->smtpConnect();
        $mail->smtpClose();
        
        return ['success' => true, 'message' => 'Email configuration is valid'];
        
    } catch (Exception $e) {
        return ['success' => false, 'errors' => ['Connection test failed: ' . $e->getMessage()]];
    }
}
?>
