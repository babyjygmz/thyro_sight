<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Direct Table Creation</h1>";

try {
    // Connect directly to database
    echo "<h2>1. Connecting to Database</h2>";
    $pdo = new PDO("mysql:host=localhost;dbname=thydb;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to thydb database<br>";
    
    // Check if tables exist
    echo "<h2>2. Checking Existing Tables</h2>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Current tables: " . (count($tables) > 0 ? implode(', ', $tables) : 'None') . "<br>";
    
    // Drop existing tables if they exist
    echo "<h2>3. Dropping Existing Tables</h2>";
    $pdo->exec("DROP TABLE IF EXISTS healthA");
    $pdo->exec("DROP TABLE IF EXISTS USER");
    echo "✅ Dropped existing tables<br>";
    
    // Create USER table
    echo "<h2>4. Creating USER Table</h2>";
    $createUserSQL = "
    CREATE TABLE `USER` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` varchar(50) NOT NULL COMMENT 'Unique user identifier',
      `first_name` varchar(50) NOT NULL,
      `last_name` varchar(50) NOT NULL,
      `email` varchar(100) NOT NULL,
      `password` varchar(255) NOT NULL,
      `phone` varchar(20) DEFAULT NULL,
      `date_of_birth` date DEFAULT NULL,
      `gender` enum('male','female','other') DEFAULT NULL,
      `otp` varchar(6) DEFAULT NULL,
      `otp_expiry` datetime DEFAULT NULL,
      `is_verified` tinyint(1) DEFAULT 0,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`),
      UNIQUE KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ";
    
    $pdo->exec($createUserSQL);
    echo "✅ USER table created<br>";
    
    // Create healthA table
    echo "<h2>5. Creating healthA Table</h2>";
    $createHealthASQL = "
    CREATE TABLE `healthA` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `user_id` INT(11) NOT NULL,
      `age` INT(11) DEFAULT NULL COMMENT 'User age',
      `gender` ENUM('male','female','other') DEFAULT NULL COMMENT 'User gender',
      
      -- Medical History Section (1=Yes, 0=No)
      `thyroxine` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `advised_thyroxine` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `antithyroid` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `illness` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `pregnant` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `surgery` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `radioactive` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `hypo_suspected` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `hyper_suspected` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `lithium` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `goitre` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `tumor` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `hypopituitarism` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `psychiatric` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',

      -- Lab Results Section (1=Yes, 0=No)
      `tsh` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `t3` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `t4` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `t4_uptake` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',
      `fti` TINYINT(1) NOT NULL COMMENT '1=Yes, 0=No',

      -- Lab Results Values (stored as float)
      `tsh_level` FLOAT DEFAULT NULL COMMENT 'TSH level value',
      `t3_level` FLOAT DEFAULT NULL COMMENT 'T3 level value',
      `t4_level` FLOAT DEFAULT NULL COMMENT 'T4 level value',
      `t4_uptake_result` FLOAT DEFAULT NULL COMMENT 'T4 Uptake result',
      `fti_result` FLOAT DEFAULT NULL COMMENT 'FTI result value',

      -- Assessment metadata
      `assessment_date` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT 'Date when assessment was completed',
      `status` enum('completed','pending','incomplete') DEFAULT 'pending' COMMENT 'Assessment status',
      
      -- Timestamps
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      KEY `assessment_date` (`assessment_date`),
      KEY `status` (`status`),
      CONSTRAINT `healthA_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `USER` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ";
    
    $pdo->exec($createHealthASQL);
    echo "✅ healthA table created<br>";
    
    // Add test user
    echo "<h2>6. Adding Test User</h2>";
    $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
    $testUserId = 'user_' . time();
    
    $stmt = $pdo->prepare("INSERT INTO USER (user_id, first_name, last_name, email, password, date_of_birth, gender, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$testUserId, 'John', 'Doe', 'test@example.com', $hashedPassword, '1990-01-15', 'male', 1]);
    
    $userId = $pdo->lastInsertId();
    echo "✅ Test user created with ID: $userId<br>";
    
    // Test healthA insert
    echo "<h2>7. Testing healthA Insert</h2>";
    $stmt = $pdo->prepare("INSERT INTO healthA (user_id, age, gender, thyroxine, advised_thyroxine, antithyroid, illness, pregnant, surgery, radioactive, hypo_suspected, hyper_suspected, lithium, goitre, tumor, hypopituitarism, psychiatric, tsh, t3, t4, t4_uptake, fti) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $result = $stmt->execute([
        $userId, 30, 'male', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
    ]);
    
    if ($result) {
        echo "✅ Test healthA insert successful<br>";
        $healthId = $pdo->lastInsertId();
        echo "✅ Health assessment record created with ID: $healthId<br>";
        
        // Clean up test data
        $pdo->exec("DELETE FROM healthA WHERE id = $healthId");
        echo "✅ Test data cleaned up<br>";
    } else {
        echo "❌ Test insert failed<br>";
    }
    
    // Verify tables
    echo "<h2>8. Final Verification</h2>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Tables in database: " . implode(', ', $tables) . "<br>";
    
    echo "<h2>9. Success!</h2>";
    echo "✅ All tables created successfully!<br>";
    echo "✅ Database is ready for health assessment data<br>";
    echo "<br><a href='health-assessment.html'>Test Health Assessment Form</a><br>";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
    echo "<br>Make sure:<br>";
    echo "1. XAMPP is running (Apache + MySQL)<br>";
    echo "2. MySQL service is started<br>";
    echo "3. thydb database exists<br>";
} catch (Exception $e) {
    echo "❌ General Error: " . $e->getMessage() . "<br>";
}
?>
