// Authentication JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all authentication features
    initPasswordToggles();
    initPasswordStrength();
    initOTPInputs();
    initFormValidation();
    initNotifications();
    
    // Handle form submissions
    handleSignupForm();
    handleLoginForm();
    handleForgotPasswordForm();
    
    // Highlight current page in navigation
    highlightCurrentPage();
});

// Password Toggle Functionality
function initPasswordToggles() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
}

// Password Strength Indicator
function initPasswordStrength() {
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    
    passwordInputs.forEach(input => {
        if (input.id === 'password' || input.id === 'newPassword') {
            input.addEventListener('input', function() {
                const strength = calculatePasswordStrength(this.value);
                updatePasswordStrength(this.id, strength);
            });
        }
    });
}

function calculatePasswordStrength(password) {
    let score = 0;
    
    if (password.length >= 8) score += 1;
    if (/[a-z]/.test(password)) score += 1;
    if (/[A-Z]/.test(password)) score += 1;
    if (/[0-9]/.test(password)) score += 1;
    if (/[^A-Za-z0-9]/.test(password)) score += 1;
    
    if (score <= 2) return 'weak';
    if (score <= 4) return 'medium';
    return 'strong';
}

function updatePasswordStrength(inputId, strength) {
    const strengthFill = document.getElementById(inputId === 'password' ? 'strengthFill' : 'newStrengthFill');
    const strengthText = document.getElementById(inputId === 'password' ? 'strengthText' : 'newStrengthText');
    
    if (strengthFill && strengthText) {
        strengthFill.className = `strength-fill ${strength}`;
        
        const strengthLabels = {
            weak: 'Weak password',
            medium: 'Medium password',
            strong: 'Strong password'
        };
        
        strengthText.textContent = strengthLabels[strength];
    }
}

// OTP Input Functionality
function initOTPInputs() {
    const otpInputs = document.querySelectorAll('.otp-input');
    
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', function() {
            if (this.value.length === 1 && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });
        
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                otpInputs[index - 1].focus();
            }
        });
    });
}

// Form Validation
function initFormValidation() {
    // Real-time validation for signup form
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        const inputs = signupForm.querySelectorAll('input[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    }
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Remove existing error styling
    field.classList.remove('error');
    removeFieldError(field);
    
    // Validation rules
    switch (field.type) {
        case 'email':
            if (!isValidEmail(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
            break;
        case 'password':
            if (value.length < 8) {
                isValid = false;
                errorMessage = 'Password must be at least 8 characters long';
            }
            break;
        default:
            if (value.length === 0) {
                isValid = false;
                errorMessage = 'This field is required';
            }
    }
    
    if (!isValid) {
        field.classList.add('error');
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showFieldError(field, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 0.25rem;
        animation: fadeIn 0.3s ease;
    `;
    
    field.parentNode.appendChild(errorDiv);
}

function removeFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Notification System
function initNotifications() {
    const notification = document.getElementById('notification');
    if (notification) {
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', function() {
            hideNotification();
        });
    }
}

function showNotification(message, type = 'success', duration = 5000) {
    const notification = document.getElementById('notification');
    if (!notification) return;
    
    const messageEl = notification.querySelector('.notification-message');
    const iconEl = notification.querySelector('.notification-icon');
    
    // Set message and type
    messageEl.textContent = message;
    notification.className = `notification ${type}`;
    
    // Show notification
    notification.classList.add('show');
    
    // Auto-hide after duration
    setTimeout(() => {
        hideNotification();
    }, duration);
}

function hideNotification() {
    const notification = document.getElementById('notification');
    if (notification) {
        notification.classList.remove('show');
    }
}

// Form Submission Handlers
function handleSignupForm() {
    const signupForm = document.getElementById('signupForm');
    if (!signupForm) return;
    
    signupForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateSignupForm()) {
            submitSignupForm(this);
        }
    });
}

function validateSignupForm() {
    const form = document.getElementById('signupForm');
    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    // Check password confirmation
    const password = form.querySelector('#password');
    const confirmPassword = form.querySelector('#confirmPassword');
    
    if (password.value !== confirmPassword.value) {
        showFieldError(confirmPassword, 'Passwords do not match');
        confirmPassword.classList.add('error');
        isValid = false;
    }
    
    return isValid;
}

function submitSignupForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');
    
    // Show loading state
    btnText.style.display = 'none';
    btnLoader.style.display = 'block';
    submitBtn.disabled = true;
    
    // Get form data
    const formData = new FormData(form);
    
    // Debug: Log form data
    console.log('Form data being sent:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
    
    // Make actual AJAX call to signup.php
    fetch('auth/signup.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Reset button state
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;
        
        if (data.success) {
            // Show success message and redirect
            showNotification(data.message || 'Account created successfully!', 'success');
            setTimeout(() => {
                window.location.href = 'signup-success.html';
            }, 1500);
        } else {
            // Show detailed error message
            let errorMessage = data.message || 'Failed to create account';
            if (data.errors && data.errors.length > 0) {
                errorMessage += ': ' + data.errors.join(', ');
            }
            showNotification(errorMessage, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Reset button state
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;
        
        // Show error message
        showNotification('An error occurred. Please try again.', 'error');
    });
}

function handleLoginForm() {
    const loginForm = document.getElementById('loginForm');
    if (!loginForm) return;
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateLoginForm()) {
            submitLoginForm(this);
        }
    });
}

function validateLoginForm() {
    const form = document.getElementById('loginForm');
    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function submitLoginForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');
    
    // Show loading state
    btnText.style.display = 'none';
    btnLoader.style.display = 'block';
    submitBtn.disabled = true;
    
    // Get form data
    const formData = new FormData(form);
    
    // Make actual AJAX call to login.php
    fetch('auth/login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Reset button state
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;
        
        if (data.success) {
            // Show success message and redirect
            showNotification(data.message || 'Login successful!', 'success');
            setTimeout(() => {
                window.location.href = 'homepage.html';
            }, 1500);
        } else {
            // Show error message
            showNotification(data.message || 'Login failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Reset button state
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;
        
        // Show error message
        showNotification('An error occurred. Please try again.', 'error');
    });
}

function handleForgotPasswordForm() {
    const emailForm = document.getElementById('emailForm');
    const otpForm = document.getElementById('otpForm');
    const passwordForm = document.getElementById('passwordForm');
    
    if (emailForm) {
        emailForm.addEventListener('submit', handleEmailSubmit);
    }
    
    if (otpForm) {
        otpForm.addEventListener('submit', handleOTPSubmit);
    }
    
    if (passwordForm) {
        passwordForm.addEventListener('submit', handlePasswordSubmit);
    }
    
    // Handle resend OTP and change email
    const resendBtn = document.getElementById('resendOtp');
    const changeEmailBtn = document.getElementById('changeEmail');
    
    if (resendBtn) {
        resendBtn.addEventListener('click', handleResendOTP);
    }
    
    if (changeEmailBtn) {
        changeEmailBtn.addEventListener('click', handleChangeEmail);
    }
}

function handleEmailSubmit(e) {
    e.preventDefault();
    
    const email = document.getElementById('resetEmail').value;
    if (!isValidEmail(email)) {
        showNotification('Please enter a valid email address', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');
    
    btnText.style.display = 'none';
    btnLoader.style.display = 'block';
    submitBtn.disabled = true;
    
    // Make actual AJAX call to forgot-password.php
    const formData = new FormData();
    formData.append('action', 'send_otp');
    formData.append('email', email);
    
    fetch('auth/forgot-password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Reset button state
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;
        
        if (data.success) {
            // Show success and move to next step
            showNotification(data.message, 'success');
            document.getElementById('userEmail').textContent = email;
            showStep(2);
            startOTPTimer();
        } else {
            // Show error message
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Reset button state
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;
        
        // Show error message
        showNotification('An error occurred. Please try again.', 'error');
    });
}

function handleOTPSubmit(e) {
    e.preventDefault();
    
    const otpInputs = document.querySelectorAll('.otp-input');
    let otp = '';
    let isValid = true;
    
    otpInputs.forEach(input => {
        if (input.value.length === 0) {
            isValid = false;
        }
        otp += input.value;
    });
    
    if (!isValid) {
        showNotification('Please enter the complete 6-digit code', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');
    
    btnText.style.display = 'none';
    btnLoader.style.display = 'block';
    submitBtn.disabled = true;
    
    // Make actual AJAX call to verify OTP
    const formData = new FormData();
    formData.append('action', 'verify_otp');
    formData.append('otp', otp);
    
    fetch('auth/forgot-password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Reset button state
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;
        
        if (data.success) {
            // Show success and move to next step
            showNotification(data.message, 'success');
            showStep(3);
        } else {
            // Show error message
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Reset button state
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;
        
        // Show error message
        showNotification('An error occurred. Please try again.', 'error');
    });
}

function handlePasswordSubmit(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmNewPassword').value;
    
    if (newPassword.length < 8) {
        showNotification('Password must be at least 8 characters long', 'error');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showNotification('Passwords do not match', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');
    
    btnText.style.display = 'none';
    btnLoader.style.display = 'block';
    submitBtn.disabled = true;
    
    // Make actual AJAX call to reset password
    const formData = new FormData();
    formData.append('action', 'reset_password');
    formData.append('newPassword', newPassword);
    formData.append('confirmPassword', confirmPassword);
    
    fetch('auth/forgot-password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Reset button state
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;
        
        if (data.success) {
            // Show success and move to final step
            showNotification(data.message, 'success');
            showStep(4);
        } else {
            // Show error message
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Reset button state
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;
        
        // Show error message
        showNotification('An error occurred. Please try again.', 'error');
    });
}

function handleResendOTP() {
    const resendBtn = document.getElementById('resendOtp');
    const btnText = resendBtn.querySelector('.btn-text');
    const resendTimer = resendBtn.querySelector('.resend-timer');
    
    resendBtn.disabled = true;
    btnText.style.display = 'none';
    resendTimer.style.display = 'inline';
    
    let countdown = 60;
    const timer = setInterval(() => {
        countdown--;
        document.getElementById('resendTimer').textContent = countdown;
        
        if (countdown <= 0) {
            clearInterval(timer);
            resendBtn.disabled = false;
            btnText.style.display = 'inline';
            resendTimer.style.display = 'none';
        }
    }, 1000);
    
    // Simulate sending new OTP
    showNotification('New OTP sent to your email!', 'success');
}

function handleChangeEmail() {
    showStep(1);
    // Reset OTP inputs
    const otpInputs = document.querySelectorAll('.otp-input');
    otpInputs.forEach(input => input.value = '');
}

// Step Navigation
function showStep(stepNumber) {
    const steps = document.querySelectorAll('.forgot-step');
    steps.forEach((step, index) => {
        if (index + 1 === stepNumber) {
            step.classList.add('active');
        } else {
            step.classList.remove('active');
        }
    });
}

// OTP Timer
function startOTPTimer() {
    let timeLeft = 90; // 1 minute 30 seconds
    const timerDisplay = document.getElementById('otpTimer');
    
    const timer = setInterval(() => {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        
        timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            showNotification('OTP has expired. Please request a new one.', 'warning');
            // Optionally redirect back to email step
        }
        
        timeLeft--;
    }, 1000);
}

// Utility Functions
function showLoadingState(button) {
    const btnText = button.querySelector('.btn-text');
    const btnLoader = button.querySelector('.btn-loader');
    
    if (btnText && btnLoader) {
        btnText.style.display = 'none';
        btnLoader.style.display = 'block';
        button.disabled = true;
    }
}

function hideLoadingState(button) {
    const btnText = button.querySelector('.btn-text');
    const btnLoader = button.querySelector('.btn-loader');
    
    if (btnText && btnLoader) {
        btnText.style.display = 'block';
        btnLoader.style.display = 'none';
        button.disabled = false;
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .field-error {
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 0.25rem;
        animation: fadeIn 0.3s ease;
    }
    
    input.error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
`;
document.head.appendChild(style);

// Navigation highlighting function
function highlightCurrentPage() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const authButtons = document.querySelectorAll('.auth-buttons .btn');
    
    console.log('Current page:', currentPage);
    console.log('Found auth buttons:', authButtons.length);
    
    authButtons.forEach(button => {
        // Remove any existing active class
        button.classList.remove('active');
        
        // Check if this button links to the current page
        const href = button.getAttribute('href');
        console.log('Button href:', href, 'Current page:', currentPage);
        
        // For login page, highlight the Log In button
        if (currentPage === 'login.html' && href === 'login.html') {
            button.classList.add('active');
            console.log('Added active class to Log In button');
        }
        // For signup page, highlight the Sign Up button
        else if (currentPage === 'signup.html' && href === 'signup.html') {
            button.classList.add('active');
            console.log('Added active class to Sign Up button');
        }
        // For forgot-password page, highlight the Log In button
        else if (currentPage === 'forgot-password.html' && href === 'login.html') {
            button.classList.add('active');
            console.log('Added active class to Log In button');
        }
        // For signup-success page, highlight the Sign Up button
        else if (currentPage === 'signup-success.html' && href === 'signup.html') {
            button.classList.add('active');
            console.log('Added active class to Sign Up button');
        }
        // For landing page (index.html), no highlighting
        else if (currentPage === 'index.html') {
            // No buttons should be highlighted on landing page
            console.log('Landing page - no highlighting');
        }
    });
}
