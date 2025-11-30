<?php
// Simple test endpoint to verify PHP is working
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Test endpoint is working',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION
]);
?>
