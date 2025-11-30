<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("get-user-data.php: User not logged in. Session data: " . json_encode($_SESSION));
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'User not logged in',
        'message' => 'Please log in to access your health assessment',
        'debug' => [
            'session_id' => session_id(),
            'session_data' => $_SESSION
        ]
    ]);
    exit();
}

error_log("get-user-data.php: User ID from session: " . $_SESSION['user_id']);

// Database connection
require_once 'config/database.php';

try {
    // Use the existing PDO connection from database.php
    global $pdo;
    
    if (!isset($pdo)) {
        throw new Exception('Database connection not available. Please check if MySQL is running.');
    }
    
    error_log("get-user-data.php: Database connection successful");
    
    // Get user data
    $stmt = $pdo->prepare("SELECT date_of_birth, gender FROM USER WHERE user_id = ?");
    error_log("get-user-data.php: Executing query for user ID: " . $_SESSION['user_id']);
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("get-user-data.php: Query result: " . json_encode($user));
    
    if ($user) {
        // Calculate age
        $birthdate = $user['date_of_birth'];
        $age = null;
        
        if ($birthdate) {
            $birth = new DateTime($birthdate);
            $today = new DateTime();
            $age = $today->diff($birth)->y;
        }
        
        $response = [
            'success' => true,
            'data' => [
                'birthdate' => $birthdate,
                'gender' => $user['gender'],
                'age' => $age
            ]
        ];
        
        error_log("get-user-data.php: Success response: " . json_encode($response));
    } else {
        $response = [
            'success' => false,
            'error' => 'User not found',
            'message' => 'User profile not found in database. Please complete your profile.',
            'debug' => [
                'searched_user_id' => $_SESSION['user_id']
            ]
        ];
        
        error_log("get-user-data.php: User not found in database");
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("get-user-data.php: PDO Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'message' => 'Database connection failed. Please try again later.'
    ]);
} catch (Exception $e) {
    error_log("get-user-data.php: General Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'System error: ' . $e->getMessage(),
        'message' => 'System temporarily unavailable. Please try again later.'
    ]);
}
?>
