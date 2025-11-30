<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Database Test</h1>";

echo "<h2>1. Check if config file exists</h2>";
if (file_exists('config/database.php')) {
    echo "✅ config/database.php exists<br>";
} else {
    echo "❌ config/database.php missing<br>";
    exit;
}

echo "<h2>2. Try to include database config</h2>";
try {
    require_once 'config/database.php';
    echo "✅ Database config included successfully<br>";
} catch (Exception $e) {
    echo "❌ Error including database config: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>3. Check if \$pdo variable exists</h2>";
if (isset($pdo)) {
    echo "✅ \$pdo variable exists<br>";
} else {
    echo "❌ \$pdo variable not set<br>";
    exit;
}

echo "<h2>4. Test basic database connection</h2>";
try {
    $result = $pdo->query("SELECT 1 as test");
    $test = $result->fetch();
    echo "✅ Basic query successful: " . $test['test'] . "<br>";
} catch (Exception $e) {
    echo "❌ Basic query failed: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>5. Check current database</h2>";
try {
    $dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "✅ Current database: " . ($dbName ?: 'None selected') . "<br>";
} catch (Exception $e) {
    echo "❌ Could not get database name: " . $e->getMessage() . "<br>";
}

echo "<h2>6. List all tables</h2>";
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo "✅ Tables found: " . implode(', ', $tables) . "<br>";
    } else {
        echo "⚠️ No tables found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Could not list tables: " . $e->getMessage() . "<br>";
}

echo "<h2>7. Summary</h2>";
if (isset($pdo) && $pdo) {
    echo "✅ Database connection is working!<br>";
    echo "Next: Check if healthA table exists<br>";
} else {
    echo "❌ Database connection failed<br>";
}
?>
