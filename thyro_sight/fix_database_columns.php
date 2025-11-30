<?php
require_once 'config/database.php';

try {
    // Create database connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Fixing database column names...\n";
    
    // Check if the Result table exists and has the old column names
    $stmt = $pdo->query("DESCRIBE Result");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('FormID', $columns)) {
        echo "Renaming FormID to form_id...\n";
        $pdo->exec("ALTER TABLE Result CHANGE FormID form_id INT NOT NULL");
    }
    
    if (in_array('UserID', $columns)) {
        echo "Renaming UserID to user_id...\n";
        $pdo->exec("ALTER TABLE Result CHANGE UserID user_id VARCHAR(50) NOT NULL");
    }
    
    if (in_array('Prediction', $columns)) {
        echo "Renaming Prediction to prediction...\n";
        $pdo->exec("ALTER TABLE Result CHANGE Prediction prediction ENUM('normal', 'hypo', 'hyper') NOT NULL");
    }
    
    if (in_array('C_Score', $columns)) {
        echo "Renaming C_Score to c_score...\n";
        $pdo->exec("ALTER TABLE Result CHANGE C_Score c_score DECIMAL(5,2) NOT NULL");
    }
    
    if (in_array('ResultID', $columns)) {
        echo "Renaming ResultID to result_id...\n";
        $pdo->exec("ALTER TABLE Result CHANGE ResultID result_id INT AUTO_INCREMENT PRIMARY KEY");
    }
    
    echo "Database column names fixed successfully!\n";
    
    // Show the updated table structure
    echo "\nUpdated Result table structure:\n";
    $stmt = $pdo->query("DESCRIBE Result");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
