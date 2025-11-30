<?php
require_once 'config/database.php';

try {
    // Create database connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Cleaning Up Duplicate Tables</h2>";
    
    // Check if both tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'Result'");
    $resultExists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'result'");
    $resultLowerExists = $stmt->rowCount() > 0;
    
    if ($resultExists && $resultLowerExists) {
        echo "<p>Found both 'Result' and 'result' tables</p>";
        
        // Check which table has the correct structure
        $stmt = $pdo->query("DESCRIBE `result`");
        $lowerColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('c_score', $lowerColumns)) {
            echo "<p>✅ Lowercase 'result' table has correct columns - keeping it</p>";
            echo "<p>❌ Uppercase 'Result' table has wrong columns - dropping it</p>";
            $pdo->exec("DROP TABLE `Result`");
            echo "<p>✅ Dropped uppercase 'Result' table</p>";
        } else {
            echo "<p>❌ Lowercase 'result' table has wrong columns - dropping it</p>";
            echo "<p>✅ Uppercase 'Result' table has correct columns - keeping it</p>";
            $pdo->exec("DROP TABLE `result`");
            echo "<p>✅ Dropped lowercase 'result' table</p>";
        }
        
    } elseif ($resultExists) {
        echo "<p>✅ Only 'Result' table exists</p>";
    } elseif ($resultLowerExists) {
        echo "<p>✅ Only 'result' table exists</p>";
    } else {
        echo "<p>❌ No result tables found</p>";
    }
    
    // Show final table structure
    echo "<h3>Final Table Structure:</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE '%result%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "<p><strong>Table: " . htmlspecialchars($table) . "</strong></p>";
        $stmt = $pdo->query("DESCRIBE `$table`");
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
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
