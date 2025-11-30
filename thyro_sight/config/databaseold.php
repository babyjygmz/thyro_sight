<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'thyroid_user');
define('DB_PASS', 'Thyroid@2025!');
define('DB_NAME', 'thydb');

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'thyrosight@gmail.com');
define('SMTP_PASSWORD', 'vqti cmzi msjx rylk'); // Gmail App Password
define('SMTP_FROM_EMAIL', 'thyrosight@gmail.com');
define('SMTP_FROM_NAME', 'ThyroSight');

$pdo = null;

try {
    // Connect to MySQL
    $tempPdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $tempPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $stmt = $tempPdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($stmt->rowCount() == 0) {
        $tempPdo->exec("CREATE DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    }

    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // --- USER Table ---
    $stmt = $pdo->query("SHOW TABLES LIKE 'USER'");
    if ($stmt->rowCount() == 0) {
        $createUserSQL = "CREATE TABLE `USER` (
            `user_id` varchar(50) NOT NULL COMMENT 'Unique user identifier',
            `first_name` varchar(50) NOT NULL,
            `last_name` varchar(50) NOT NULL,
            `email` varchar(100) NOT NULL,
            `password` varchar(255) NOT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `date_of_birth` date DEFAULT NULL,
            `gender` enum('male','female','other') DEFAULT NULL,
            `otp` varchar(6) DEFAULT NULL,
            `otp_expiry` datetime DEFAULT NULL,
            `is_verified` tinyint(1) DEFAULT 0,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`user_id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $pdo->exec($createUserSQL);

        $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO USER (user_id, first_name, last_name, email, password, date_of_birth, gender, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['1', 'John', 'Doe', 'test@example.com', $hashedPassword, '1990-01-15', 'male', 1]);
    }

    // --- healthA Table ---
    $stmt = $pdo->query("SHOW TABLES LIKE 'healthA'");
    if ($stmt->rowCount() == 0) {
        $createHealthASQL = "CREATE TABLE `healthA` (
            `form_id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` varchar(50) NOT NULL COMMENT 'Foreign key to USER table',
            `age` INT(11) DEFAULT NULL COMMENT 'User age',
            `gender` ENUM('male','female','other') DEFAULT NULL COMMENT 'User gender',
            `thyroxine` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `advised_thyroxine` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `antithyroid` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `illness` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `pregnant` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `surgery` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `radioactive` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `hypo_suspected` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `hyper_suspected` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `lithium` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `goitre` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `tumor` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `hypopituitarism` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `psychiatric` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `tsh` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `t3` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `t4` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `t4_uptake` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `fti` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
            `tsh_level` FLOAT DEFAULT NULL,
            `t3_level` FLOAT DEFAULT NULL,
            `t4_level` FLOAT DEFAULT NULL,
            `t4_uptake_result` FLOAT DEFAULT NULL,
            `fti_result` FLOAT DEFAULT NULL,
            `assessment_date` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT 'Date when assessment was completed',
            `status` enum('completed','pending','incomplete') DEFAULT 'pending',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`form_id`),
            KEY `user_id` (`user_id`),
            CONSTRAINT `healthA_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `USER`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $pdo->exec($createHealthASQL);
    }

    // Test connection
    $pdo->query("SELECT 1");
    error_log("Database connection established successfully");

} catch(PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    $pdo = null;
} catch(Exception $e) {
    error_log("General error in database setup: " . $e->getMessage());
    $pdo = null;
}
?>
