<?php
echo "<h1>Test Restructured Database</h1>";

try {
    require_once 'config/database.php';
    
    if (!$pdo) {
        echo "❌ Database connection failed<br>";
        exit;
    }
    
    echo "✅ Database connected successfully<br>";
    
    // Test USER table structure
    echo "<h3>1. USER Table Structure</h3>";
    $stmt = $pdo->query("DESCRIBE USER");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}";
        if ($column['Key'] === 'PRI') echo " (PRIMARY KEY)";
        if ($column['Key'] === 'UNI') echo " (UNIQUE)";
        echo "<br>";
    }
    
    // Test healthA table structure
    echo "<h3>2. healthA Table Structure</h3>";
    $stmt = $pdo->query("DESCRIBE healthA");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}";
        if ($column['Key'] === 'PRI') echo " (PRIMARY KEY)";
        if ($column['Key'] === 'MUL') echo " (FOREIGN KEY)";
        echo "<br>";
    }
    
    // Test creating a user
    echo "<h3>3. Test User Creation</h3>";
    $testUserId = 'test_' . time();
    $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO USER (user_id, first_name, last_name, email, password, is_verified) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$testUserId, 'Test', 'User', 'test@example.com', $hashedPassword, 1]);
    
    if ($result) {
        echo "✅ Test user created: $testUserId<br>";
        
        // Test login (this was causing the error)
        echo "<h3>4. Test Login Query</h3>";
        $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email, password FROM USER WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "✅ Login query successful<br>";
            echo "User ID: {$user['user_id']}<br>";
            echo "Name: {$user['first_name']} {$user['last_name']}<br>";
            echo "Email: {$user['email']}<br>";
        } else {
            echo "❌ Login query failed<br>";
        }
        
        // Test health assessment
        echo "<h3>5. Test Health Assessment</h3>";
        $stmt = $pdo->prepare("INSERT INTO healthA (user_id, age, gender, thyroxine, advised_thyroxine, antithyroid, illness, pregnant, surgery, radioactive, hypo_suspected, hyper_suspected, lithium, goitre, tumor, hypopituitarism, psychiatric, tsh, t3, t4, t4_uptake, fti) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$testUserId, 25, 'female', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]);
        
        if ($result) {
            echo "✅ Health assessment created successfully<br>";
            
            // Get form_id
            $stmt = $pdo->prepare("SELECT form_id FROM healthA WHERE user_id = ?");
            $stmt->execute([$testUserId]);
            $form = $stmt->fetch();
            echo "✅ Form ID: {$form['form_id']}<br>";
        } else {
            echo "❌ Health assessment creation failed<br>";
        }
        
        // Clean up
        $pdo->exec("DELETE FROM healthA WHERE user_id = '$testUserId'");
        $pdo->exec("DELETE FROM USER WHERE user_id = '$testUserId'");
        echo "✅ Test data cleaned up<br>";
        
    } else {
        echo "❌ User creation failed<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><a href='health-assessment.html'>Go to Health Assessment Form</a><br>";
?>
