<?php
echo "<h1>Test New Database Structure</h1>";

try {
    require_once 'config/database.php';
    
    if (!$pdo) {
        echo "❌ Database connection failed<br>";
        exit;
    }
    
    echo "✅ Database connected successfully<br>";
    
    // Test USER table structure
    echo "<h3>1. Testing USER Table Structure</h3>";
    $stmt = $pdo->query("DESCRIBE USER");
    $columns = $stmt->fetchAll();
    
    echo "USER table columns:<br>";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}";
        if ($column['Key'] === 'PRI') echo " (PRIMARY KEY)";
        if ($column['Key'] === 'UNI') echo " (UNIQUE)";
        echo "<br>";
    }
    
    // Test healthA table structure
    echo "<h3>2. Testing healthA Table Structure</h3>";
    $stmt = $pdo->query("DESCRIBE healthA");
    $columns = $stmt->fetchAll();
    
    echo "healthA table columns:<br>";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}";
        if ($column['Key'] === 'PRI') echo " (PRIMARY KEY)";
        if ($column['Key'] === 'MUL') echo " (FOREIGN KEY)";
        echo "<br>";
    }
    
    // Test foreign key constraints
    echo "<h3>3. Testing Foreign Key Constraints</h3>";
    $stmt = $pdo->query("SELECT 
        CONSTRAINT_NAME,
        TABLE_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'thydb' 
    AND REFERENCED_TABLE_NAME IS NOT NULL");
    
    $constraints = $stmt->fetchAll();
    
    if (empty($constraints)) {
        echo "❌ No foreign key constraints found<br>";
    } else {
        echo "✅ Foreign key constraints found:<br>";
        foreach ($constraints as $constraint) {
            echo "- {$constraint['CONSTRAINT_NAME']}: {$constraint['TABLE_NAME']}.{$constraint['COLUMN_NAME']} → {$constraint['REFERENCED_TABLE_NAME']}.{$constraint['REFERENCED_COLUMN_NAME']}<br>";
        }
    }
    
    // Test inserting a user
    echo "<h3>4. Testing User Insert</h3>";
    $testUserId = 'test_user_' . time();
    $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO USER (user_id, first_name, last_name, email, password, is_verified) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$testUserId, 'Test', 'User', 'test@example.com', $hashedPassword, 1]);
    
    if ($result) {
        echo "✅ Test user created successfully with ID: $testUserId<br>";
        
        // Test inserting health assessment
        echo "<h3>5. Testing Health Assessment Insert</h3>";
        $stmt = $pdo->prepare("INSERT INTO healthA (user_id, age, gender, thyroxine, advised_thyroxine, antithyroid, illness, pregnant, surgery, radioactive, hypo_suspected, hyper_suspected, lithium, goitre, tumor, hypopituitarism, psychiatric, tsh, t3, t4, t4_uptake, fti) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$testUserId, 25, 'female', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]);
        
        if ($result) {
            echo "✅ Health assessment created successfully<br>";
            
            // Get the form_id
            $stmt = $pdo->prepare("SELECT form_id FROM healthA WHERE user_id = ?");
            $stmt->execute([$testUserId]);
            $form = $stmt->fetch();
            echo "✅ Form ID: {$form['form_id']}<br>";
        } else {
            echo "❌ Health assessment creation failed<br>";
        }
        
        // Clean up test data
        $pdo->exec("DELETE FROM healthA WHERE user_id = '$testUserId'");
        $pdo->exec("DELETE FROM USER WHERE user_id = '$testUserId'");
        echo "✅ Test data cleaned up<br>";
    } else {
        echo "❌ Test user creation failed<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><a href='health-assessment.html'>Go to Health Assessment Form</a><br>";
?>
