<?php
// Check and create database tables if needed
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    $tables = [];
    
    // Check USER table
    $stmt = $pdo->query("SHOW TABLES LIKE 'USER'");
    $tables['USER'] = $stmt->rowCount() > 0;
    
    // Check healthA table
    $stmt = $pdo->query("SHOW TABLES LIKE 'healthA'");
    $tables['healthA'] = $stmt->rowCount() > 0;
    
    // Check Result table
    $stmt = $pdo->query("SHOW TABLES LIKE 'Result'");
    $tables['Result'] = $stmt->rowCount() > 0;
    
    // Create Result table if it doesn't exist
    if (!$tables['Result']) {
        $createResultTableSQL = "
        CREATE TABLE `Result` (
          `ResultID` INT AUTO_INCREMENT PRIMARY KEY,
          `FormID` INT NOT NULL,
          `UserID` VARCHAR(50) NOT NULL,
          `Prediction` ENUM('normal', 'hypo', 'hyper') NOT NULL,
          `C_Score` DECIMAL(5,2) NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`FormID`) REFERENCES `healthA`(`form_id`) ON DELETE CASCADE,
          FOREIGN KEY (`UserID`) REFERENCES `USER`(`user_id`) ON DELETE CASCADE,
          INDEX `idx_user_results` (`UserID`),
          INDEX `idx_form_results` (`FormID`),
          INDEX `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        
        $pdo->exec($createResultTableSQL);
        $tables['Result'] = true;
    }
    
    // Check table structures
    $tableInfo = [];
    
    if ($tables['USER']) {
        $stmt = $pdo->query("DESCRIBE USER");
        $tableInfo['USER'] = $stmt->fetchAll();
    }
    
    if ($tables['healthA']) {
        $stmt = $pdo->query("DESCRIBE healthA");
        $tableInfo['healthA'] = $stmt->fetchAll();
    }
    
    if ($tables['Result']) {
        $stmt = $pdo->query("DESCRIBE Result");
        $tableInfo['Result'] = $stmt->fetchAll();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database tables checked',
        'tables_exist' => $tables,
        'table_structures' => $tableInfo,
        'database_name' => DB_NAME,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
