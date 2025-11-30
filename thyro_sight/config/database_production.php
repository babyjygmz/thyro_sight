<?php
// Production Database configuration (uses environment variables)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'thydb');
define('DB_PORT', getenv('DB_PORT') ?: '3306');

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'thyrosight@gmail.com');
define('SMTP_PASSWORD', 'vqti cmzi msjx rylk');
define('SMTP_FROM_EMAIL', 'thyrosight@gmail.com');
define('SMTP_FROM_NAME', 'ThyroSight');

// Initialize database connection
$pdo = null;

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    error_log("Database connection established successfully");
    
} catch(PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    $pdo = null;
}
?>
