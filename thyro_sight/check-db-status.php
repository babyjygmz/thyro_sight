<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Status Check</h1>";

// Check if config file exists
echo "<h2>1. Config File Check</h2>";
if (file_exists('config/database.php')) {
    echo "✅ config/database.php exists<br>";
} else {
    echo "❌ config/database.php missing<br>";
    exit;
}

// Try to include database config
echo "<h2>2. Database Connection</h2>";
try {
    require_once 'config/database.php';
    if ($pdo) {
        echo "✅ Database connection successful<br>";
        echo "Database: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "<br>";
    } else {
        echo "❌ Database connection failed - \$pdo is null<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    exit;
}

// Check what tables exist
echo "<h2>3. Current Tables</h2>";
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo "✅ Tables found: " . implode(', ', $tables) . "<br>";
    } else {
        echo "❌ No tables found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error listing tables: " . $e->getMessage() . "<br>";
}

// Check specific tables
echo "<h2>4. Specific Table Check</h2>";
$requiredTables = ['USER', 'healthA'];
foreach ($requiredTables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ $table table exists<br>";
        } else {
            echo "❌ $table table missing<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error checking $table: " . $e->getMessage() . "<br>";
    }
}

// Check if we can create tables
echo "<h2>5. Table Creation Test</h2>";
try {
    // Try to create a simple test table
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_table (id INT)");
    echo "✅ Can create tables<br>";
    
    // Clean up test table
    $pdo->exec("DROP TABLE test_table");
    echo "✅ Can drop tables<br>";
    
} catch (Exception $e) {
    echo "❌ Cannot create tables: " . $e->getMessage() . "<br>";
}

echo "<h2>6. Recommendations</h2>";
echo "If tables are missing, run: <a href='force-create-tables.php'>force-create-tables.php</a><br>";
echo "If that fails, check XAMPP MySQL service<br>";
?>
