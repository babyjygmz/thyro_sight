<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Force Create Database Tables</h1>";

try {
    // Include database config
    require_once 'config/database.php';
    
    if (!$pdo) {
        echo "❌ Database connection failed<br>";
        exit;
    }
    
    echo "✅ Database connected successfully<br>";
    
    // Force recreate USER table
    echo "<h2>1. Creating USER table...</h2>";
    try {
        $pdo->exec("DROP TABLE IF EXISTS healthA");
        $pdo->exec("DROP TABLE IF EXISTS USER");
        
        $createUserTableSQL = "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        
        $pdo->exec($createUserTableSQL);
        echo "✅ USER table created successfully<br>";
        
        // Add test user
        $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
        $testUserId = 'user_' . time();
        $stmt = $pdo->prepare("INSERT INTO USER (user_id, first_name, last_name, email, password, date_of_birth, gender, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$testUserId, 'John', 'Doe', 'test@example.com', $hashedPassword, '1990-01-15', 'male', 1]);
        echo "✅ Test user created with ID: $testUserId<br>";
        
    } catch (Exception $e) {
        echo "❌ Error creating USER table: " . $e->getMessage() . "<br>";
        exit;
    }
    
    // Create healthA table
    echo "<h2>2. Creating healthA table...</h2>";
    try {
        $createHealthATableSQL = "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        
        $pdo->exec($createHealthATableSQL);
        echo "✅ healthA table created successfully<br>";
        
    } catch (Exception $e) {
        echo "❌ Error creating healthA table: " . $e->getMessage() . "<br>";
        exit;
    }
    
    // Verify tables exist
    echo "<h2>3. Verifying tables...</h2>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found: " . implode(', ', $tables) . "<br>";
    
    // Test insert into healthA
    echo "<h2>4. Testing healthA table...</h2>";
    try {
        $testUserId = $pdo->query("SELECT id FROM USER LIMIT 1")->fetchColumn();
        echo "Test user ID: $testUserId<br>";
        
        // FIXED: Correct parameter count - 21 columns, 21 values
        $stmt = $pdo->prepare("INSERT INTO healthA (user_id, age, gender, thyroxine, advised_thyroxine, antithyroid, illness, pregnant, surgery, radioactive, hypo_suspected, hyper_suspected, lithium, goitre, tumor, hypopituitarism, psychiatric, tsh, t3, t4, t4_uptake, fti) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $result = $stmt->execute([
            $testUserId, 30, 'male', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
        ]);
        
        if ($result) {
            echo "✅ Test insert into healthA successful<br>";
        } else {
            echo "❌ Test insert failed<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error testing healthA table: " . $e->getMessage() . "<br>";
    }
    
    echo "<h2>5. Summary</h2>";
    echo "✅ All tables created successfully!<br>";
    echo "✅ Database is ready for health assessment data<br>";
    echo "<br><a href='health-assessment.html'>Test Health Assessment Form</a><br>";
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "<br>";
}
?>
