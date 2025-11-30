<?php
echo "<h2>Database Fix Script</h2>";
echo "<p>This script will fix all database issues automatically.</p>";

// Step 1: Check if MySQL is running
echo "<h3>Step 1: Checking MySQL Service</h3>";
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ MySQL is running</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ MySQL is NOT running!</p>";
    echo "<p><strong>Please start MySQL in XAMPP Control Panel first!</strong></p>";
    echo "<p>1. Open XAMPP Control Panel</p>";
    echo "<p>2. Click 'Start' next to MySQL</p>";
    echo "<p>3. Wait for green light</p>";
    echo "<p>4. Refresh this page</p>";
    exit;
}

// Step 2: Create database if it doesn't exist
echo "<h3>Step 2: Creating Database</h3>";
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS thydb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "<p style='color: green;'>✓ Database 'thydb' is ready</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Failed to create database: " . $e->getMessage() . "</p>";
    exit;
}

// Step 3: Connect to the database
echo "<h3>Step 3: Connecting to Database</h3>";
try {
    $dbPdo = new PDO("mysql:host=localhost;dbname=thydb;charset=utf8mb4", "root", "");
    $dbPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Connected to 'thydb' database</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Failed to connect to database: " . $e->getMessage() . "</p>";
    exit;
}

// Step 4: Create USER table if it doesn't exist
echo "<h3>Step 4: Creating USER Table</h3>";
try {
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS `USER` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `first_name` varchar(50) NOT NULL,
      `last_name` varchar(50) NOT NULL,
      `email` varchar(100) NOT NULL UNIQUE,
      `password` varchar(255) NOT NULL,
      `phone` varchar(20) DEFAULT NULL,
      `date_of_birth` date DEFAULT NULL,
      `gender` enum('Male','Female','Other') DEFAULT NULL,
      `otp` varchar(6) DEFAULT NULL,
      `otp_expiry` datetime DEFAULT NULL,
      `is_verified` tinyint(1) DEFAULT 0,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    
    $dbPdo->exec($createTableSQL);
    echo "<p style='color: green;'>✓ USER table is ready</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Failed to create table: " . $e->getMessage() . "</p>";
    exit;
}

// Step 5: Check if test user exists, if not create one
echo "<h3>Step 5: Creating Test User</h3>";
try {
    $stmt = $dbPdo->query("SELECT COUNT(*) as count FROM USER WHERE email = 'test@example.com'");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Create test user
        $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
        $stmt = $dbPdo->prepare("INSERT INTO USER (first_name, last_name, email, password, date_of_birth, gender, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['John', 'Doe', 'test@example.com', $hashedPassword, '1990-01-15', 'Male', 1]);
        echo "<p style='color: green;'>✓ Test user created successfully</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Test user already exists</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Failed to create test user: " . $e->getMessage() . "</p>";
    exit;
}

// Step 6: Verify everything is working
echo "<h3>Step 6: Final Verification</h3>";
try {
    $stmt = $dbPdo->query("SELECT COUNT(*) as count FROM USER");
    $result = $stmt->fetch();
    $userCount = $result['count'];
    
    echo "<p style='color: green;'>✓ Database verification successful</p>";
    echo "<p>Total users in database: <strong>$userCount</strong></p>";
    
    if ($userCount > 0) {
        echo "<p style='color: green;'>🎉 Database is now working perfectly!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Final verification failed: " . $e->getMessage() . "</p>";
    exit;
}

echo "<hr>";
echo "<h3>✅ Database Fixed Successfully!</h3>";
echo "<p>You can now:</p>";
echo "<ol>";
echo "<li><a href='login.html'>Go to Login Page</a> - The database error should be gone!</li>";
echo "<li><a href='health-assessment.html'>Go to Health Assessment</a> - User data will auto-populate!</li>";
echo "<li>Login with test@example.com / test123</li>";
echo "</ol>";

echo "<p><strong>If you still see database errors, please:</strong></p>";
echo "<ol>";
echo "<li>Make sure XAMPP Control Panel is open</li>";
echo "<li>Verify MySQL service is running (green light)</li>";
echo "<li>Refresh the login page</li>";
echo "</ol>";
?>
