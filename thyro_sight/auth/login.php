<?php
// Set session configuration to ensure consistency
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
ini_set('session.cookie_secure', false);
ini_set('session.cookie_httponly', true);
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Check if database connection is available
    if (!$pdo) {
        throw new Exception('Database connection not available. Please try again later.');
    }
    
    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Debug logging
    error_log("Login attempt for email: " . $email);
    
    // Validation
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
        exit;
    }
    
    // Check if user exists and verify password
    error_log("Preparing SQL query for user lookup");
    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email, password, date_of_birth, gender FROM USER WHERE email = ?");
    error_log("Executing SQL query with email: " . $email);
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        error_log("User not found for email: " . $email);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    error_log("User found, verifying password");
    if (!password_verify($password, $user['password'])) {
        error_log("Password verification failed for user: " . $email);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    error_log("Password verified successfully for user: " . $email);
    
    // Calculate age from date_of_birth
    $age = null;
    if (!empty($user['date_of_birth'])) {
        $birthdate = new DateTime($user['date_of_birth']);
        $today = new DateTime();
        $age = $today->diff($birthdate)->y;
    }

    // Set session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_age'] = $age;
    $_SESSION['user_gender'] = $user['gender'];
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['user_id'],
            'email' => $user['email'],
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'age' => $age,
            'gender' => $user['gender']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in login.php: " . $e->getMessage());
    error_log("Database error trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error in login.php: " . $e->getMessage());
    error_log("General error trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
