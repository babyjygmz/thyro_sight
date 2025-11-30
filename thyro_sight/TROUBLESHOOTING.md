# ThyroSight Troubleshooting Guide

## Database Connection Issues

If you're experiencing "Database error occurred" messages, follow these steps:

### 1. Check XAMPP Status
- Make sure XAMPP Control Panel is running
- Start both Apache and MySQL services
- Verify MySQL is running on port 3306

### 2. Test Database Connection
Open these files in your browser to test:

1. **Database Connection Test**: `http://localhost/thyro_sight/test-db.php`
   - This will show if MySQL is running
   - Check if the database and USER table exist
   - Verify table structure

2. **Signup Process Test**: `http://localhost/thyro_sight/test-signup.php`
   - This will test the complete signup flow
   - Check each step of the process
   - Verify data insertion works

3. **Forgot Password Test**: `http://localhost/thyro_sight/test-forgot-password.php`
   - This will test the forgot password functionality
   - Check mailer configuration
   - Verify OTP functionality

### 3. Common Issues and Solutions

#### Issue: "No database selected"
- **Solution**: The SQL file now includes `CREATE DATABASE` and `USE` statements
- Re-import `thydb.sql` in phpMyAdmin

#### Issue: "Duplicate key name 'email'"
- **Solution**: The SQL file now includes `DROP DATABASE IF EXISTS` to ensure clean import
- Re-import `thydb.sql` in phpMyAdmin

#### Issue: "Database connection not available"
- **Solution**: Check if XAMPP MySQL service is running
- Verify database credentials in `config/database.php`

#### Issue: Column name mismatches
- **Solution**: All column names now match between frontend and database
- `first_name`, `last_name`, `date_of_birth`, `gender` (lowercase)

#### Issue: "An error occurred" on forgot password page
- **Solution**: Fixed column name mismatches in `auth/forgot-password.php`
- Corrected OTP column names (`otp_expiry` instead of `otp_expires_at`)
- Added database connection validation
- Check if PHPMailer is properly installed

### 4. Database Schema

The USER table has the following structure:
```sql
CREATE TABLE `USER` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 5. Testing Steps

1. **Start XAMPP**: Apache and MySQL
2. **Test Database**: Open `test-db.php` in browser
3. **Test Signup**: Open `test-signup.php` in browser
4. **Test Forgot Password**: Open `test-forgot-password.php` in browser
5. **Try Signup Form**: Fill out the signup form on `signup.html`
6. **Try Forgot Password**: Test the forgot password form on `forgot-password.html`
7. **Check Console**: Look for JavaScript errors in browser console

### 6. File Locations

- **Database Config**: `config/database.php`
- **Mailer Config**: `config/mailer.php`
- **Signup Handler**: `auth/signup.php`
- **Login Handler**: `auth/login.php`
- **Forgot Password Handler**: `auth/forgot-password.php`
- **Database Schema**: `thydb.sql`
- **Test Files**: `test-db.php`, `test-signup.php`, `test-forgot-password.php`

### 7. Browser Console

Open browser developer tools (F12) and check:
- **Console tab**: For JavaScript errors
- **Network tab**: For failed HTTP requests
- **Response**: For detailed error messages from PHP

### 8. PHP Error Logs

Check XAMPP error logs:
- **Apache**: `C:\xampp\apache\logs\error.log`
- **PHP**: `C:\xampp\php\logs\php_error_log`

### 9. Email Configuration Issues

If forgot password emails are not working:
1. Check if PHPMailer is installed: `vendor/phpmailer/phpmailer/src/PHPMailer.php`
2. Verify Gmail App Password in `config/mailer.php`
3. Check SMTP settings (host, port, encryption)
4. Test email configuration with `test-forgot-password.php`

### 10. Still Having Issues?

If problems persist:
1. Check all test files in browser
2. Verify XAMPP services are running
3. Check browser console for errors
4. Review PHP error logs
5. Ensure all files are in the correct directory structure
6. Verify PHPMailer installation for email functionality
