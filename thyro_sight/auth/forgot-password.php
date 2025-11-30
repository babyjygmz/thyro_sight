<?php
session_start();
require_once '../config/database.php';
require_once '../config/mailer.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    // Check if database connection is available
    if (!$pdo) {
        throw new Exception('Database connection not available');
    }
    
    switch ($action) {
        case 'send_otp':
            handleSendOTP();
            break;
        case 'verify_otp':
            handleVerifyOTP();
            break;
        case 'reset_password':
            handleResetPassword();
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
} catch (Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

function handleSendOTP() {
    global $pdo;
    
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid email is required']);
        exit;
    }
    
    // Check if user exists - using correct column names
            $stmt = $pdo->prepare("SELECT user_id, first_name, last_name FROM USER WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Email not found in our system']);
        exit;
    }
    
    // Generate OTP
    $otp = sprintf('%06d', mt_rand(0, 999999));
    $otpExpiry = date('Y-m-d H:i:s', time() + 90); // 1 minute 30 seconds
    
    // Store OTP in database - using correct column name
    $stmt = $pdo->prepare("UPDATE USER SET otp = ?, otp_expiry = ? WHERE email = ?");
    $stmt->execute([$otp, $otpExpiry, $email]);
    
    // Send OTP email using the mailer helper
    $mailSent = sendOTPEmailTemplate($email, $user['first_name'], $otp, 1.5);
    
    if (!$mailSent) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP email']);
        exit;
    }
    
    // Store email in session for verification
    $_SESSION['reset_email'] = $email;
    
    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully',
        'email' => $email
    ]);
}

function handleVerifyOTP() {
    global $pdo;
    
    $email = $_SESSION['reset_email'] ?? '';
    $otp = $_POST['otp'] ?? '';
    
    if (empty($email) || empty($otp)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email and OTP are required']);
        exit;
    }
    
    // Verify OTP - using correct column name
            $stmt = $pdo->prepare("SELECT user_id, otp, otp_expiry FROM USER WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    if ($user['otp'] !== $otp) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
        exit;
    }
    
    if (strtotime($user['otp_expiry']) < time()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'OTP has expired']);
        exit;
    }
    
    // Mark OTP as verified
    $_SESSION['otp_verified'] = true;
    
    echo json_encode([
        'success' => true,
        'message' => 'OTP verified successfully'
    ]);
}

function handleResetPassword() {
    global $pdo;
    
    $email = $_SESSION['reset_email'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    if (empty($email) || empty($newPassword) || empty($confirmPassword)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if ($newPassword !== $confirmPassword) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }
    
    if (strlen($newPassword) < 8) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
        exit;
    }
    
    if (!$_SESSION['otp_verified']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'OTP verification required']);
        exit;
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password and clear OTP - using correct column name
    $stmt = $pdo->prepare("UPDATE USER SET password = ?, otp = NULL, otp_expiry = NULL, updated_at = NOW() WHERE email = ?");
    $stmt->execute([$hashedPassword, $email]);
    
    // Clear session
    unset($_SESSION['reset_email']);
    unset($_SESSION['otp_verified']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Password reset successfully'
    ]);
}
?>
