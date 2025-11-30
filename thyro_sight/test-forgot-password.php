<?php
// Test forgot password functionality step by step
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Forgot Password Functionality Test</h2>";

try {
    // Step 1: Check if required files exist
    echo "<h3>Step 1: File Check</h3>";
    
    $requiredFiles = [
        'config/database.php' => 'Database configuration',
        'config/mailer.php' => 'Mailer configuration',
        'auth/forgot-password.php' => 'Forgot password handler'
    ];
    
    foreach ($requiredFiles as $file => $description) {
        if (file_exists($file)) {
            echo "<p style='color: green;'>✓ $description file exists</p>";
        } else {
            echo "<p style='color: red;'>✗ $description file missing: $file</p>";
        }
    }
    
    // Step 2: Test database connection
    echo "<h3>Step 2: Database Connection</h3>";
    if (!file_exists('config/database.php')) {
        throw new Exception('Database config file not found');
    }
    
    require_once 'config/database.php';
    
    if (!$pdo) {
        throw new Exception('Database connection not available');
    }
    echo "<p style='color: green;'>✓ Database connection established</p>";
    
    // Step 3: Check USER table structure
    echo "<h3>Step 3: USER Table Structure</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'USER'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>✗ USER table does not exist</p>";
        exit;
    }
    echo "<p style='color: green;'>✓ USER table exists</p>";
    
    // Check required columns for forgot password
    $stmt = $pdo->query("DESCRIBE USER");
    $columns = $stmt->fetchAll();
    
    $requiredColumns = ['id', 'email', 'first_name', 'last_name', 'otp', 'otp_expiry'];
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
    
    // Step 4: Test mailer configuration
    echo "<h3>Step 4: Mailer Configuration</h3>";
    if (!file_exists('config/mailer.php')) {
        echo "<p style='color: red;'>✗ Mailer config file not found</p>";
    } else {
        require_once 'config/mailer.php';
        
        // Check if PHPMailer is available
        if (isPHPMailerAvailable()) {
            echo "<p style='color: green;'>✓ PHPMailer is available</p>";
        } else {
            echo "<p style='color: red;'>✗ PHPMailer is not available</p>";
        }
        
        // Validate email configuration
        $emailErrors = validateEmailConfig();
        if (empty($emailErrors)) {
            echo "<p style='color: green;'>✓ Email configuration is valid</p>";
        } else {
            echo "<p style='color: red;'>✗ Email configuration errors:</p>";
            echo "<ul>";
            foreach ($emailErrors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        }
    }
    
    // Step 5: Test forgot password handler
    echo "<h3>Step 5: Forgot Password Handler Test</h3>";
    if (!file_exists('auth/forgot-password.php')) {
        echo "<p style='color: red;'>✗ Forgot password handler not found</p>";
    } else {
        echo "<p style='color: green;'>✓ Forgot password handler exists</p>";
        
        // Test with a sample email
        $testEmail = 'test@example.com';
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM USER WHERE email = ?");
        $stmt->execute([$testEmail]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p style='color: green;'>✓ Test user found: {$user['first_name']} {$user['last_name']}</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Test user not found, creating one...</p>";
            
            // Create a test user
            $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO USER (first_name, last_name, email, password, date_of_birth, gender, is_verified) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute(['Test', 'User', $testEmail, $hashedPassword, '1990-01-01', 'male', 1]);
            
            if ($result) {
                echo "<p style='color: green;'>✓ Test user created successfully</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to create test user</p>";
            }
        }
    }
    
    // Step 6: Check existing users
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
echo "<p>2. Open <a href='test-db.php'>test-db.php</a> to test database connection</p>";
echo "<p>3. Open <a href='test-forgot-password.php'>test-forgot-password.php</a> to test forgot password functionality</p>";
echo "<p>4. Try the forgot password form on <a href='forgot-password.html'>forgot-password.html</a></p>";
echo "<p>5. Check browser console for any JavaScript errors</p>";
echo "<p>6. Check PHP error logs for backend issues</p>";
?>
