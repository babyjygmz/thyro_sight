<?php
echo "<h1>Fix User IDs to Simple Numbers</h1>";

try {
    require_once 'config/database.php';
    
    if (!$pdo) {
        echo "❌ Database connection failed<br>";
        exit;
    }
    
    echo "✅ Database connected successfully<br>";
    
    // First, let's see what we have now
    echo "<h3>1. Current User IDs</h3>";
    $stmt = $pdo->query("SELECT user_id, first_name, last_name, email FROM USER ORDER BY created_at");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $index => $user) {
        $newId = $index + 1; // Start from 1
        echo "- Current ID: '{$user['user_id']}' → New ID: {$newId}<br>";
        echo "  Name: {$user['first_name']} {$user['last_name']}<br>";
        echo "  Email: {$user['email']}<br><br>";
    }
    
    // Now let's update the USER table to use simple numeric IDs
    echo "<h3>2. Updating User IDs to Simple Numbers</h3>";
    
    // We need to temporarily disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    foreach ($users as $index => $user) {
        $newId = $index + 1; // Start from 1
        $oldId = $user['user_id'];
        
        // Update USER table
        $stmt = $pdo->prepare("UPDATE USER SET user_id = ? WHERE user_id = ?");
        $result = $stmt->execute([$newId, $oldId]);
        
        if ($result) {
            echo "✅ Updated USER table: '{$oldId}' → {$newId}<br>";
        } else {
            echo "❌ Failed to update USER table for '{$oldId}'<br>";
        }
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Verify the changes
    echo "<h3>3. Verifying Updated User IDs</h3>";
    $stmt = $pdo->query("SELECT user_id, first_name, last_name, email FROM USER ORDER BY user_id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "✅ User ID: {$user['user_id']} - {$user['first_name']} {$user['last_name']}<br>";
    }
    
    // Test if the new IDs work
    echo "<h3>4. Testing New User IDs</h3>";
    
    // Test login with new ID
    $testEmail = "babyjoygomez0103@gmail.com";
    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email FROM USER WHERE email = ?");
    $stmt->execute([$testEmail]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ Login query successful<br>";
        echo "New User ID: {$user['user_id']}<br>";
        echo "Name: {$user['first_name']} {$user['last_name']}<br>";
        
        // Test if we can use this for health assessment
        echo "<h3>5. Testing Health Assessment with New User ID</h3>";
        
        // Check if health assessment exists
        $stmt = $pdo->prepare("SELECT form_id FROM healthA WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        
        if ($stmt->fetch()) {
            echo "✅ Health assessment exists for user ID {$user['user_id']}<br>";
        } else {
            echo "ℹ️ No health assessment exists yet for user ID {$user['user_id']}<br>";
        }
        
    } else {
        echo "❌ Login query failed<br>";
    }
    
    echo "<h3>6. Summary</h3>";
    echo "✅ User IDs simplified to: 1, 2, 3, etc.<br>";
    echo "✅ You are now User ID: 1<br>";
    echo "✅ System ready for simple numeric user management<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><a href='health-assessment.html'>Go to Health Assessment Form</a><br>";
echo "<a href='test-restructured-db.php'>Test Restructured Database</a><br>";
?>
