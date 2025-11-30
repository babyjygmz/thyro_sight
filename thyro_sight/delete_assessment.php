<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config/database.php';

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Only allow POST or DELETE methods
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form_id from POST data or query string
$form_id = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_id = $_POST['form_id'] ?? null;
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $form_id = $_GET['form_id'] ?? null;
}

if (!$form_id || !is_numeric($form_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid form ID']);
    exit;
}

$form_id = intval($form_id);

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // First, verify that the assessment belongs to the logged-in user
    $check_query = "SELECT user_id FROM healthA WHERE form_id = ?";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute([$form_id]);
    $assessment = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assessment) {
        echo json_encode(['success' => false, 'message' => 'Assessment not found']);
        exit;
    }

    if ($assessment['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized to delete this assessment']);
        exit;
    }

    // Start transaction to ensure data integrity
    $pdo->beginTransaction();

    // Delete from Result table first (due to foreign key constraint)
    $delete_result_query = "DELETE FROM `Result` WHERE form_id = ?";
    $delete_result_stmt = $pdo->prepare($delete_result_query);
    $delete_result_stmt->execute([$form_id]);

    // Delete from healthA table
    $delete_health_query = "DELETE FROM healthA WHERE form_id = ? AND user_id = ?";
    $delete_health_stmt = $pdo->prepare($delete_health_query);
    $delete_health_stmt->execute([$form_id, $user_id]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Assessment deleted successfully'
    ]);

} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
