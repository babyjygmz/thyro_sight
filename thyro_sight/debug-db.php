<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Debug Information</h1>";

// Test 1: Basic PHP and PDO
echo "<h2>1. PHP and PDO Status</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO MySQL Extension: " . (extension_loaded('pdo_mysql') ? '✅ Loaded' : '❌ Not Loaded') . "<br>";

// Test 2: Database Connection
echo "<h2>2. Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    if ($pdo) {
        echo "✅ Database connection successful<br>";
        echo "Database: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "<br>";
        echo "MySQL Version: " . $pdo->query('SELECT VERSION()')->fetchColumn() . "<br>";
    } else {
        echo "❌ Database connection failed - \$pdo is null<br>";
    }
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "<br>";
}

// Test 3: Check if tables exist
echo "<h2>3. Table Existence Check</h2>";
if (isset($pdo) && $pdo) {
    try {
        // Check USER table
        $stmt = $pdo->query("SHOW TABLES LIKE 'USER'");
        $userExists = $stmt->rowCount() > 0;
        echo "USER table: " . ($userExists ? '✅ Exists' : '❌ Missing') . "<br>";
        
        // Check healthA table
        $stmt = $pdo->query("SHOW TABLES LIKE 'healthA'");
        $healthAExists = $stmt->rowCount() > 0;
        echo "healthA table: " . ($healthAExists ? '✅ Exists' : '❌ Missing') . "<br>";
        
        // List all tables
        echo "<br><strong>All tables in database:</strong><br>";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            echo "- $table<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Table check error: " . $e->getMessage() . "<br>";
    }
}

// Test 4: Check table structure if they exist
echo "<h2>4. Table Structure Check</h2>";
if (isset($pdo) && $pdo) {
    try {
        if ($userExists) {
            echo "<strong>USER table structure:</strong><br>";
            $stmt = $pdo->query("DESCRIBE USER");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $col) {
                echo "- {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']}<br>";
            }
        }
        
        if ($healthAExists) {
            echo "<br><strong>healthA table structure:</strong><br>";
            $stmt = $pdo->query("DESCRIBE healthA");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $col) {
                echo "- {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']}<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Structure check error: " . $e->getMessage() . "<br>";
    }
}

// Test 5: Check for any data
echo "<h2>5. Data Check</h2>";
if (isset($pdo) && $pdo) {
    try {
        if ($userExists) {
            $userCount = $pdo->query("SELECT COUNT(*) FROM USER")->fetchColumn();
            echo "Users in USER table: $userCount<br>";
        }
        
        if ($healthAExists) {
            $healthCount = $pdo->query("SELECT COUNT(*) FROM healthA")->fetchColumn();
            echo "Records in healthA table: $healthCount<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Data check error: " . $e->getMessage() . "<br>";
    }
}

echo "<h2>6. Recommendations</h2>";
if (!isset($pdo) || !$pdo) {
    echo "❌ <strong>Fix database connection first</strong><br>";
    echo "- Check XAMPP is running (Apache + MySQL)<br>";
    echo "- Verify database credentials in config/database.php<br>";
} elseif (!$userExists || !$healthAExists) {
    echo "❌ <strong>Missing tables - import thydb.sql</strong><br>";
    echo "- Go to phpMyAdmin<br>";
    echo "- Select/create thydb database<br>";
    echo "- Import the updated thydb.sql file<br>";
} else {
    echo "✅ <strong>Database looks good!</strong><br>";
    echo "- Check if user is logged in (session)<br>";
    echo "- Verify form field names match PHP script<br>";
}
?>
