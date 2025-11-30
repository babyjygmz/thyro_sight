<?php
// Test signup process step by step
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Signup Process Test</h2>";

try {
    // Step 1: Check if database config exists
    echo "<h3>Step 1: Database Configuration</h3>";
    if (!file_exists('config/database.php')) {
        throw new Exception('Database configuration file not found');
    }
    echo "<p style='color: green;'>✓ Database config file exists</p>";
    
    // Step 2: Include database config
    require_once 'config/database.php';
    echo "<p style='color: green;'>✓ Database config loaded</p>";
    
    // Step 3: Check database connection
    echo "<h3>Step 2: Database Connection</h3>";
    if (!$pdo) {
        throw new Exception('Database connection not available');
    }
    echo "<p style='color: green;'>✓ Database connection established</p>";
    
    // Step 4: Check if USER table exists
    echo "<h3>Step 3: USER Table Check</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'USER'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>✗ USER table does not exist</p>";
        exit;
    }
    echo "<p style='color: green;'>✓ USER table exists</p>";
    
    // Step 5: Check table structure
    echo "<h3>Step 4: Table Structure</h3>";
    $stmt = $pdo->query("DESCRIBE USER");
    $columns = $stmt->fetchAll();
    
    $requiredColumns = ['first_name', 'last_name', 'email', 'password', 'date_of_birth', 'gender'];
    $missingColumns = [];
    
    foreach ($requiredColumns as $required) {
        $found = false;
        foreach ($columns as $column) {
            if ($column['Field'] === $required) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $missingColumns[] = $required;
        }
    }
    
    if (empty($missingColumns)) {
        echo "<p style='color: green;'>✓ All required columns exist</p>";
    } else {
        echo "<p style='color: red;'>✗ Missing columns: " . implode(', ', $missingColumns) . "</p>";
    }
    
    // Step 6: Test data insertion
    echo "<h3>Step 5: Test Data Insertion</h3>";
    
    // Test data
    $testData = [
        'firstName' => 'Test',
        'lastName' => 'User',
        'email' => 'test' . time() . '@example.com',
        'password' => 'testpassword123',
        'dateOfBirth' => '1990-01-01',
        'gender' => 'male'
    ];
    
    echo "<p>Testing with data:</p>";
    echo "<ul>";
    foreach ($testData as $key => $value) {
        echo "<li><strong>$key:</strong> $value</li>";
    }
    echo "</ul>";
    
    try {
        // Hash password
        $hashedPassword = password_hash($testData['password'], PASSWORD_DEFAULT);
        
        // Insert test user
        $stmt = $pdo->prepare("
            INSERT INTO USER (first_name, last_name, email, password, date_of_birth, gender, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $testData['firstName'],
            $testData['lastName'],
            $testData['email'],
            $hashedPassword,
            $testData['dateOfBirth'],
            $testData['gender']
        ]);
        
        if ($result) {
            $userId = $pdo->lastInsertId();
            echo "<p style='color: green;'>✓ Test user created successfully with ID: $userId</p>";
            
            // Clean up - delete test user
            $stmt = $pdo->prepare("DELETE FROM USER WHERE id = ?");
            $stmt->execute([$userId]);
            echo "<p style='color: blue;'>ℹ Test user cleaned up</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create test user</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error creating test user: " . $e->getMessage() . "</p>";
    }
    
    // Step 7: Check for existing users
    echo "<h3>Step 6: Existing Users</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM USER");
    $result = $stmt->fetch();
    echo "<p>Total users in database: <strong>" . $result['count'] . "</strong></p>";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT id, first_name, last_name, email FROM USER LIMIT 3");
        $users = $stmt->fetchAll();
        echo "<p>Sample users:</p>";
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li>ID: {$user['id']}, Name: {$user['first_name']} {$user['last_name']}, Email: {$user['email']}</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<p>1. Make sure XAMPP is running (Apache and MySQL)</p>";
echo "<p>2. Open <a href='test-db.php'>test-db.php</a> in your browser to test database connection</p>";
echo "<p>3. Try the signup form again</p>";
echo "<p>4. Check browser console for any JavaScript errors</p>";
?>
