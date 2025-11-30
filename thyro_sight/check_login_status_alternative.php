<?php
// Alternative login check that accepts session ID as parameter
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if session ID was passed as parameter
$sessionId = $_GET['session_id'] ?? null;

if ($sessionId) {
    // Try to start session with specific ID
    session_id($sessionId);
    session_start();
    
    echo json_encode([
        'method' => 'session_id_parameter',
        'session_id' => $sessionId,
        'logged_in' => isset($_SESSION['user_id']),
        'user_id' => $_SESSION['user_id'] ?? null,
        'message' => isset($_SESSION['user_id']) ? 'User is logged in' : 'User not logged in'
    ]);
} else {
    // Try normal session
    session_start();
    
    echo json_encode([
        'method' => 'normal_session',
        'session_id' => session_id(),
        'logged_in' => isset($_SESSION['user_id']),
        'user_id' => $_SESSION['user_id'] ?? null,
        'message' => isset($_SESSION['user_id']) ? 'User is logged in' : 'User not logged in',
        'session_data' => $_SESSION,
        'cookies' => $_COOKIE
    ]);
}
?>
