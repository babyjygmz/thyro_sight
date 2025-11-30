<?php
// Simple test login setup - creates a session for testing
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if database has a test user
try {
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Look for any existing user
    $stmt = $pdo->query("SELECT user_id, email FROM USER LIMIT 1");
    $user = $stmt->fetch();
    
    if ($user) {
        // Set session with existing user
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['email'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Test session created with existing user',
            'user_id' => $user['user_id'],
            'email' => $user['email'],
            'session_data' => $_SESSION
        ]);
    } else {
        // Create a test user
        $testUserId = 'test_user_' . time();
        $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO USER (user_id, first_name, last_name, email, password, date_of_birth, gender, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$testUserId, 'Test', 'User', 'test@example.com', $hashedPassword, '1990-01-01', 'male', 1]);
        
        // Set session with new test user
        $_SESSION['user_id'] = $testUserId;
        $_SESSION['user_email'] = 'test@example.com';
        
        echo json_encode([
            'success' => true,
            'message' => 'Test user created and session set',
            'user_id' => $testUserId,
            'email' => 'test@example.com',
            'session_data' => $_SESSION
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
