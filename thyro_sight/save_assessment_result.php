<?php
session_start();
require_once 'config/database.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Create database connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get JSON data from request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    $required_fields = ['form_id', 'prediction', 'confidence_score'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Validate prediction values
    $valid_predictions = ['normal', 'hypo', 'hyper'];
    if (!in_array($input['prediction'], $valid_predictions)) {
        throw new Exception('Invalid prediction value');
    }
    
    // Validate confidence score (should be between 0 and 100)
    $confidence_score = floatval($input['confidence_score']);
    if ($confidence_score < 0 || $confidence_score > 100) {
        throw new Exception('Invalid confidence score');
    }
    
    $user_id = $_SESSION['user_id'];
    $form_id = intval($input['form_id']);
    $prediction = $input['prediction'];
    
    // Verify that the form belongs to the current user
    $stmt = $pdo->prepare("SELECT form_id FROM healthA WHERE form_id = ? AND user_id = ?");
    $stmt->execute([$form_id, $user_id]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Form not found or access denied');
    }
    
    // Insert result into database
    $stmt = $pdo->prepare("
        INSERT INTO `Result` (form_id, user_id, prediction, c_score) 
        VALUES (?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([$form_id, $user_id, $prediction, $confidence_score]);
    
    if ($result) {
        $result_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Assessment result saved successfully',
            'result_id' => $result_id
        ]);
    } else {
        throw new Exception('Failed to save result to database');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
