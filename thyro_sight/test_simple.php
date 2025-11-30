<?php
// Simple test to verify PHP and JSON are working
header('Content-Type: application/json');

$response = [
    'success' => true,
    'message' => 'Simple test successful',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION
];

echo json_encode($response);
?>
