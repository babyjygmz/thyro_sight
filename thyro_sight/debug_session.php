<?php
session_start();

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');

echo json_encode([
    'session_id' => session_id(),
    'session_name' => session_name(),
    'session_status' => session_status(),
    'session_data' => $_SESSION,
    'cookies' => $_COOKIE,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Not set',
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Not set',
    'http_host' => $_SERVER['HTTP_HOST'] ?? 'Not set',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Not set',
    'php_version' => PHP_VERSION,
    'session_save_path' => session_save_path(),
    'session_cookie_params' => session_get_cookie_params()
]);
?>
