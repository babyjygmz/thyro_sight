<?php
// Database configuration - uses environment variables for cloud deployment
// Try Railway's MySQL variables first, then custom variables, then localhost defaults
define('DB_HOST', getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'thydb');
define('DB_PORT', getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306');

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'thyrosight@gmail.com');
define('SMTP_PASSWORD', 'vqti cmzi msjx rylk'); // Gmail App Password
define('SMTP_FROM_EMAIL', 'thyrosight@gmail.com');
define('SMTP_FROM_NAME', 'ThyroSight');

// Initialize database connection
$pdo = null;

try {
    // Log connection attempt (without password)
    error_log("Attempting database connection to: " . DB_HOST . ":" . DB_PORT . " / " . DB_NAME . " as " . DB_USER);
    
    // Build connection string with port
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    // Connect to the database
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Verify connection is working
    $testStmt = $pdo->query("SELECT 1");
    if ($testStmt) {
        error_log("Database connection established successfully to " . DB_NAME);
    }
    
} catch(PDOException $e) {
    // Log detailed error for debugging
    error_log("PDO Database connection error: " . $e->getMessage());
    error_log("Connection details - Host: " . DB_HOST . ", Port: " . DB_PORT . ", DB: " . DB_NAME . ", User: " . DB_USER);
    $pdo = null;
} catch (Exception $e) {
    error_log("General error in database setup: " . $e->getMessage());
    $pdo = null;
}
?>
