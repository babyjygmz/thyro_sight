# ThyroSight Authentication System

A complete, modern authentication system for ThyroSight with signup, login, and forgot password functionality, featuring OTP verification and email notifications.

## 🌟 Features

### **User Authentication**
- **Sign Up**: Complete registration form with validation
- **Login**: Secure authentication with remember me option
- **Forgot Password**: Multi-step password reset with OTP
- **Password Strength**: Real-time password strength indicator
- **Form Validation**: Client-side and server-side validation

### **Security Features**
- **Password Hashing**: Secure password storage using PHP's built-in hashing
- **OTP System**: 6-digit verification codes with 1.5-minute expiry
- **Session Management**: Secure session handling
- **Input Sanitization**: Protection against common vulnerabilities
- **Email Verification**: OTP sent via email for password reset

### **User Experience**
- **Modern UI**: Creative, responsive design matching ThyroSight theme
- **Real-time Feedback**: Instant validation and notifications
- **Loading States**: Visual feedback during form submission
- **Responsive Design**: Works on all devices and screen sizes
- **Accessibility**: Keyboard navigation and screen reader support

## 🗄️ Database Structure

### **USER Table**
```sql
CREATE TABLE `USER` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
);
```

## 📁 File Structure

```
thyro_sight/
├── auth/                          # Authentication backend
│   ├── signup.php                # User registration handler
│   ├── login.php                 # User login handler
│   └── forgot-password.php       # Password reset handler
├── config/                        # Configuration files
│   ├── database.php              # Database connection
│   └── mailer.php                # Email configuration
├── signup.html                   # Registration page
├── signup-success.html           # Success page after registration
├── login.html                    # Login page
├── forgot-password.html          # Password reset page
├── auth.css                      # Authentication styles
├── auth.js                       # Authentication functionality
├── thydb.sql                     # Database structure
├── composer.json                 # PHP dependencies
└── AUTH_README.md               # This file
```

## 🚀 Installation & Setup

### **1. Database Setup**
1. Import the `thydb.sql` file into your XAMPP MySQL database
2. Create a database named `thydb`
3. Ensure the USER table is created successfully

### **2. PHP Dependencies**
1. Install Composer (if not already installed)
2. Run in project directory:
   ```bash
   composer install
   ```
3. This will install PHPMailer for email functionality

### **3. Configuration**
1. **Database Configuration** (`config/database.php`):
   - Update database credentials if needed
   - Default: `localhost`, `root`, no password

2. **Email Configuration** (`config/database.php`):
   - Update `SMTP_PASSWORD` with your Gmail app password
   - Ensure `thyrosight@gmail.com` is configured for SMTP

### **4. Gmail SMTP Setup**
1. Enable 2-factor authentication on your Gmail account
2. Generate an App Password for "Mail"
3. Use this password in `SMTP_PASSWORD`

## 📧 Email Configuration

### **SMTP Settings**
- **Host**: `smtp.gmail.com`
- **Port**: `587`
- **Security**: `TLS`
- **Authentication**: Required

### **Email Templates**
- **OTP Email**: Professional design with verification code
- **Welcome Email**: Branded welcome message for new users
- **Responsive**: Works on all email clients

## 🔐 Authentication Flow

### **Sign Up Process**
1. User fills registration form
2. Client-side validation
3. Server-side validation
4. Password hashing
5. Database insertion
6. Success redirect

### **Login Process**
1. User enters credentials
2. Server validates email/password
3. Password verification
4. Session creation
5. Success redirect

### **Forgot Password Process**
1. **Step 1**: User enters email
2. **Step 2**: System generates OTP and sends email
3. **Step 3**: User enters 6-digit OTP
4. **Step 4**: OTP verification
5. **Step 5**: User sets new password
6. **Step 6**: Success confirmation

## 🎨 UI Components

### **Form Elements**
- **Input Fields**: Styled with icons and focus effects
- **Password Toggle**: Show/hide password functionality
- **Strength Indicator**: Visual password strength meter
- **Validation Messages**: Real-time error feedback

### **Buttons**
- **Primary Buttons**: Main actions with hover effects
- **Secondary Buttons**: Alternative actions
- **Loading States**: Spinner animation during submission
- **Social Login**: Google and Facebook integration ready

### **Notifications**
- **Success Messages**: Green-themed success notifications
- **Error Messages**: Red-themed error notifications
- **Warning Messages**: Yellow-themed warning notifications
- **Auto-hide**: Notifications disappear after 5 seconds

## 🔧 Customization

### **Colors**
Update CSS variables in `auth.css`:
```css
:root {
    --primary-blue: #2563eb;
    --light-blue: #3b82f6;
    --light-green: #10b981;
    --white: #ffffff;
}
```

### **Email Templates**
Modify email content in `config/mailer.php`:
- HTML email body
- Plain text alternatives
- Branding and styling

### **Validation Rules**
Update validation logic in `auth.js`:
- Password requirements
- Field validation rules
- Error messages

## 🛡️ Security Considerations

### **Password Security**
- Minimum 8 characters
- Hashing using `password_hash()`
- Verification using `password_verify()`

### **OTP Security**
- 6-digit random codes
- 1.5-minute expiration
- One-time use only
- Rate limiting ready

### **Session Security**
- Secure session handling
- CSRF protection ready
- Input sanitization
- SQL injection prevention

## 📱 Responsive Design

### **Breakpoints**
- **Desktop**: 1200px and above
- **Tablet**: 768px - 1199px
- **Mobile**: Below 768px

### **Mobile Features**
- Touch-friendly inputs
- Optimized spacing
- Swipe gestures ready
- Mobile-first approach

## 🧪 Testing

### **Manual Testing**
1. Test all form validations
2. Verify email sending
3. Check OTP functionality
4. Test responsive design
5. Validate security measures

### **Browser Compatibility**
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## 🚨 Troubleshooting

### **Common Issues**

1. **Database Connection Failed**
   - Check XAMPP is running
   - Verify database credentials
   - Ensure `thydb` database exists

2. **Email Not Sending**
   - Verify Gmail app password
   - Check SMTP settings
   - Ensure PHPMailer is installed

3. **OTP Not Working**
   - Check database table structure
   - Verify OTP expiry logic
   - Check session handling

4. **Form Validation Issues**
   - Check JavaScript console for errors
   - Verify form field names
   - Check CSS class names

### **Debug Mode**
Enable error reporting in PHP:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 🔄 Updates & Maintenance

### **Regular Tasks**
- Update PHPMailer to latest version
- Review security best practices
- Monitor error logs
- Backup database regularly

### **Version Control**
- Track changes in Git
- Tag releases for authentication system
- Maintain changelog
- Document breaking changes

## 📞 Support

### **Documentation**
- This README file
- Code comments
- Database schema
- API endpoints

### **Contact**
- Technical issues: Check error logs
- Feature requests: Document requirements
- Security concerns: Immediate attention required

## 📝 License

This authentication system is created for ThyroSight and is part of the main project. All rights reserved.

---

**ThyroSight Authentication System** - Secure, modern, and user-friendly authentication for thyroid health technology.
