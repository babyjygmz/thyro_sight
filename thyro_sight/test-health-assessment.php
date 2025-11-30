<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Health Assessment Database Test</h1>";

// Test database connection
echo "<h2>1. Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    if ($pdo) {
        echo "✅ Database connection successful<br>";
        echo "Database: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "<br>";
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "<br>";
}

// Test USER table
echo "<h2>2. USER Table Test</h2>";
try {
    if ($pdo) {
        $stmt = $pdo->query("DESCRIBE USER");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✅ USER table exists with " . count($columns) . " columns<br>";
        echo "<strong>Columns:</strong><br>";
        foreach ($columns as $col) {
            echo "- {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']}<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ USER table error: " . $e->getMessage() . "<br>";
}

// Test healthA table
echo "<h2>3. healthA Table Test</h2>";
try {
    if ($pdo) {
        $stmt = $pdo->query("DESCRIBE healthA");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✅ healthA table exists with " . count($columns) . " columns<br>";
        echo "<strong>Columns:</strong><br>";
        foreach ($columns as $col) {
            echo "- {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']}<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ healthA table error: " . $e->getMessage() . "<br>";
}

// Test foreign key constraint
echo "<h2>4. Foreign Key Test</h2>";
try {
    if ($pdo) {
        $stmt = $pdo->query("
            SELECT 
                CONSTRAINT_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'healthA' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($constraints) {
            echo "✅ Foreign key constraints found:<br>";
            foreach ($constraints as $constraint) {
                echo "- {$constraint['CONSTRAINT_NAME']}: {$constraint['COLUMN_NAME']} → {$constraint['REFERENCED_TABLE_NAME']}.{$constraint['REFERENCED_COLUMN_NAME']}<br>";
            }
        } else {
            echo "❌ No foreign key constraints found<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Foreign key test error: " . $e->getMessage() . "<br>";
}

// Test sample data insertion (simulation)
echo "<h2>5. Sample Data Test</h2>";
try {
    if ($pdo) {
        // Check if we have any users
        $userCount = $pdo->query("SELECT COUNT(*) FROM USER")->fetchColumn();
        echo "Users in database: {$userCount}<br>";
        
        if ($userCount > 0) {
            // Get first user ID for testing
            $userId = $pdo->query("SELECT id FROM USER LIMIT 1")->fetchColumn();
            echo "Testing with user ID: {$userId}<br>";
            
            // Check if healthA record exists for this user
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM healthA WHERE user_id = ?");
            $stmt->execute([$userId]);
            $assessmentCount = $stmt->fetchColumn();
            echo "Health assessments for user {$userId}: {$assessmentCount}<br>";
            
            if ($assessmentCount == 0) {
                echo "✅ No existing assessment found - ready for new data<br>";
            } else {
                echo "✅ Assessment already exists for this user<br>";
            }
        } else {
            echo "⚠️ No users found - create a user first to test health assessment<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Sample data test error: " . $e->getMessage() . "<br>";
}

echo "<h2>6. Test Summary</h2>";
echo "If all tests pass, the health assessment system is ready to use!<br>";
echo "Next steps:<br>";
echo "1. Import the updated thydb.sql file<br>";
echo "2. Create a user account<br>";
echo "3. Complete the health assessment form<br>";
echo "4. Data will be stored in the healthA table<br>";
?>
