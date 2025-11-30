<?php
// Database setup script - run this first to ensure database is ready
echo "<h2>Database Setup</h2>";

try {
    // Connect to MySQL without specifying database
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ MySQL connection successful</p>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE 'thydb'");
    if ($stmt->rowCount() == 0) {
        // Create database
        $pdo->exec("CREATE DATABASE thydb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        echo "<p style='color: green;'>✓ Database 'thydb' created successfully</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Database 'thydb' already exists</p>";
    }
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=localhost;dbname=thydb;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Connected to 'thydb' database</p>";
    
    // Check if USER table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'USER'");
    if ($stmt->rowCount() == 0) {
        // Create USER table
        $createTableSQL = "
        CREATE TABLE `USER` (
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
        
        $pdo->exec($createTableSQL);
        echo "<p style='color: green;'>✓ USER table created successfully</p>";
    } else {
        echo "<p style='color: blue;'>ℹ USER table already exists</p>";
    }
    
    // Check if there are any users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM USER");
    $result = $stmt->fetch();
    $userCount = $result['count'];
    
    echo "<p>Total users in database: <strong>$userCount</strong></p>";
    
    if ($userCount == 0) {
        // Add a test user
        $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO USER (first_name, last_name, email, password, date_of_birth, gender, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['John', 'Doe', 'test@example.com', $hashedPassword, '1990-01-15', 'Male', 1]);
        
        echo "<p style='color: green;'>✓ Test user added successfully!</p>";
        echo "<p><strong>Test User Details:</strong></p>";
        echo "<ul>";
        echo "<li>Email: test@example.com</li>";
        echo "<li>Password: test123</li>";
        echo "<li>Birth Date: 1990-01-15</li>";
        echo "<li>Gender: Male</li>";
        echo "</ul>";
    }
    
    // Show all users
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, date_of_birth, gender, is_verified FROM USER");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<h3>All Users in Database:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Birth Date</th><th>Gender</th><th>Verified</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['first_name'] . " " . $user['last_name'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . ($user['date_of_birth'] ? $user['date_of_birth'] : 'Not set') . "</td>";
            echo "<td>" . ($user['gender'] ? $user['gender'] : 'Not set') . "</td>";
            echo "<td>" . ($user['is_verified'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h3>Next Steps:</h3>";
    echo "<p>1. <a href='test-login.php'>Test Login</a> with test@example.com / test123</p>";
    echo "<p>2. <a href='health-assessment.html'>Go to Health Assessment</a> to see if data loads</p>";
    echo "<p>3. <a href='test-db.php'>Test Database Connection</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure:</p>";
    echo "<ul>";
    echo "<li>XAMPP is running</li>";
    echo "<li>MySQL service is started</li>";
    echo "<li>You have access to create databases</li>";
    echo "</ul>";
}
?>
