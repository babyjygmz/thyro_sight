# PHPMailer Installation Guide

Since Composer is not available on your system, here are alternative ways to install PHPMailer:

## Option 1: Manual Installation (Recommended)

1. **Download PHPMailer manually:**
   - Go to: https://github.com/PHPMailer/PHPMailer/releases
   - Download the latest release ZIP file
   - Extract it to your project

2. **Create the vendor directory structure:**
   ```
   thyro_sight/
   ├── vendor/
   │   └── phpmailer/
   │       └── phpmailer/
   │           ├── src/
   │           │   └── PHPMailer.php
   │           └── autoload.php
   ```

3. **Update the require_once path in auth/forgot-password.php:**
   ```php
   // Change this line:
   require_once '../vendor/autoload.php';
   
   // To this:
   require_once '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
   require_once '../vendor/phpmailer/phpmailer/src/SMTP.php';
   require_once '../vendor/phpmailer/phpmailer/src/Exception.php';
   ```

## Option 2: Install Composer (Recommended for future projects)

1. **Download Composer:**
   - Go to: https://getcomposer.org/download/
   - Download and run Composer-Setup.exe for Windows

2. **Install PHPMailer:**
   ```bash
   composer install
   ```

## Option 3: Use XAMPP's built-in mail function (Temporary solution)

If you want to test the system without PHPMailer, you can temporarily modify the mailer functions to use PHP's built-in `mail()` function.

## Current Status

✅ **Fixed Issues:**
- Syntax errors in mailer.php (missing assignment operators)
- PHPMailer class namespace references
- Constant usage for SMTP credentials

⚠️ **Remaining Issue:**
- PHPMailer library not installed

## Next Steps

1. Choose an installation method above
2. Install PHPMailer
3. Update your SMTP password in `config/database.php`
4. Test the email functionality

## Testing

After installation, you can test the email configuration by:
1. Setting up a valid Gmail app password
2. Running the forgot password flow
3. Checking if OTP emails are sent successfully
