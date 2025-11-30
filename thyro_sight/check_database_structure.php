<?php
require_once 'config/database.php';

try {
    // Create database connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Database Structure Check</h2>";
    echo "<h3>Current Database: " . DB_NAME . "</h3>";
    
    // Check if Result table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'Result'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Result table exists</p>";
        
        // Get actual table structure
        echo "<h3>Result Table Structure:</h3>";
        $stmt = $pdo->query("DESCRIBE Result");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for any tables with similar names
        echo "<h3>Tables with 'result' in name:</h3>";
        $stmt = $pdo->query("SHOW TABLES LIKE '%result%'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            echo "<p>• " . htmlspecialchars($table) . "</p>";
        }
        
        // Try to insert a test record to see exact error
        echo "<h3>Testing Insert Operation:</h3>";
        try {
            $stmt = $pdo->prepare("INSERT INTO Result (form_id, user_id, prediction, c_score) VALUES (999, 'test', 'normal', 85.5)");
            $stmt->execute();
            echo "<p>✅ Test insert successful - columns exist</p>";
            
            // Clean up test record
            $pdo->exec("DELETE FROM Result WHERE form_id = 999");
            echo "<p>✅ Test record cleaned up</p>";
            
        } catch (Exception $e) {
            echo "<p>❌ Test insert failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
    } else {
        echo "<p>❌ Result table does not exist</p>";
        
        // Check what tables do exist
        echo "<h3>Available tables:</h3>";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            echo "<p>• " . htmlspecialchars($table) . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
