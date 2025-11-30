<?php
// Fix foreign key constraint issues
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Temporarily disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $actions = [];
    
    // Check if Result table exists and has foreign key constraints
    $stmt = $pdo->query("SHOW TABLES LIKE 'Result'");
    if ($stmt->rowCount() > 0) {
        // Get foreign key constraints
        $stmt = $pdo->query("
            SELECT 
                CONSTRAINT_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
            AND TABLE_NAME = 'Result' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $constraints = $stmt->fetchAll();
        
        // Drop existing foreign key constraints
        foreach ($constraints as $constraint) {
            $dropSQL = "ALTER TABLE `Result` DROP FOREIGN KEY `" . $constraint['CONSTRAINT_NAME'] . "`";
            $pdo->exec($dropSQL);
            $actions[] = "Dropped constraint: " . $constraint['CONSTRAINT_NAME'];
        }
        
        // Recreate foreign key constraints with correct table names
        $addConstraintsSQL = "
        ALTER TABLE `Result` 
        ADD CONSTRAINT `result_ibfk_1` FOREIGN KEY (`FormID`) REFERENCES `healthA`(`form_id`) ON DELETE CASCADE,
        ADD CONSTRAINT `result_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `USER`(`user_id`) ON DELETE CASCADE
        ";
        
        $pdo->exec($addConstraintsSQL);
        $actions[] = "Recreated foreign key constraints";
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Verify the fix
    $stmt = $pdo->query("
        SELECT 
            TABLE_NAME,
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
        AND TABLE_NAME = 'Result' 
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $newConstraints = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'message' => 'Foreign key constraints fixed',
        'actions_performed' => $actions,
        'new_constraints' => $newConstraints,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Re-enable foreign key checks even if there's an error
    if (isset($pdo)) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
