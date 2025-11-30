<?php
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
        throw new Exception('Database connection not available');
    }
    
    // Get form data
    $firstName = trim($_POST['firstName'] ?? '');
    $middleInitial = trim($_POST['middleInitial'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $dateOfBirth = $_POST['dateOfBirth'] ?? null;
    $gender = $_POST['gender'] ?? null;
    
    // Validation
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    // Check password confirmation
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    // Validate required fields for database
    if (empty($dateOfBirth)) {
        $errors[] = 'Date of birth is required';
    }
    
    if (empty($gender)) {
        $errors[] = 'Gender is required';
    }
    
    // Check if email already exists
            $stmt = $pdo->prepare("SELECT user_id FROM USER WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $errors[] = 'Email address is already registered';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
        exit;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate a unique user_id
    $user_id = 'user_' . time() . '_' . rand(1000, 9999);
    
    // Insert user into database - including user_id as PRIMARY KEY
    $stmt = $pdo->prepare("
        INSERT INTO USER (user_id, first_name, last_name, email, password, date_of_birth, gender, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $user_id,
        $firstName,
        $lastName,
        $email,
        $hashedPassword,
        $dateOfBirth,
        $gender
    ]);
    
    // Set session using the generated user_id (not auto-increment ID)
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $firstName . ' ' . $lastName;
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully',
        'user' => [
            'id' => $user_id,
            'email' => $email,
            'name' => $firstName . ' ' . $lastName
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
