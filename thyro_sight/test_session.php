<?php
// Test session and database connection
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

$response = [
    'session_id' => session_id(),
    'session_status' => session_status(),
    'user_id_set' => isset($_SESSION['user_id']),
    'user_id_value' => $_SESSION['user_id'] ?? 'not_set',
    'database_connected' => $pdo ? true : false,
    'all_session_data' => $_SESSION,
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
