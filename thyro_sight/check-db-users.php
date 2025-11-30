<?php
echo "<h1>Check Database Users</h1>";

try {
    require_once 'config/database.php';
    
    if (!$pdo) {
        echo "❌ Database connection failed<br>";
        exit;
    }
    
    echo "✅ Database connected successfully<br>";
    
    // Check current USER table contents
    echo "<h3>1. Current USER Table Contents</h3>";
    $stmt = $pdo->query("SELECT * FROM USER");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "❌ No users found in USER table<br>";
    } else {
        echo "✅ Found " . count($users) . " user(s) in USER table:<br><br>";
        
        foreach ($users as $user) {
            echo "<strong>User Details:</strong><br>";
            echo "- user_id: '" . ($user['user_id'] ?? 'NULL') . "'<br>";
            echo "- first_name: " . $user['first_name'] . "<br>";
            echo "- last_name: " . $user['last_name'] . "<br>";
            echo "- email: " . $user['email'] . "<br>";
            echo "- is_verified: " . $user['is_verified'] . "<br>";
            echo "- created_at: " . $user['created_at'] . "<br>";
            echo "<br>";
        }
    }
    
    // Check if any users have empty user_id
    echo "<h3>2. Checking for Empty user_id Values</h3>";
    $stmt = $pdo->query("SELECT * FROM USER WHERE user_id IS NULL OR user_id = ''");
    $emptyUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($emptyUsers)) {
        echo "✅ All users have valid user_id values<br>";
    } else {
        echo "⚠️ Found " . count($emptyUsers) . " user(s) with empty user_id:<br>";
        
        foreach ($emptyUsers as $user) {
            echo "- Email: " . $user['email'] . " (user_id is empty)<br>";
        }
        
        echo "<br><strong>Fixing empty user_id values...</strong><br>";
        
        // Fix empty user_id values
        foreach ($emptyUsers as $user) {
            $newUserId = 'user_' . time() . '_' . rand(1000, 9999);
            
            $stmt = $pdo->prepare("UPDATE USER SET user_id = ? WHERE email = ?");
            $result = $stmt->execute([$newUserId, $user['email']]);
            
            if ($result) {
                echo "✅ Fixed user_id for " . $user['email'] . " → " . $newUserId . "<br>";
            } else {
                echo "❌ Failed to fix user_id for " . $user['email'] . "<br>";
            }
        }
    }
    
    // Verify the fix
    echo "<h3>3. Verifying Fix</h3>";
    $stmt = $pdo->query("SELECT user_id, first_name, last_name, email FROM USER");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Updated USER table contents:<br>";
    foreach ($users as $user) {
        echo "- " . $user['first_name'] . " " . $user['last_name'] . " (ID: " . $user['user_id'] . ")<br>";
    }
    
    // Test if we can now use these users
    echo "<h3>4. Testing User Authentication</h3>";
    $testEmail = "babyjoygomez0103@gmail.com";
    
    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email, password FROM USER WHERE email = ?");
    $stmt->execute([$testEmail]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ Login query successful for " . $testEmail . "<br>";
        echo "User ID: " . $user['user_id'] . "<br>";
        echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "<br>";
        
        // Test if we can use this user for health assessment
        echo "<h3>5. Testing Health Assessment with Fixed User</h3>";
        
        // Check if health assessment exists
        $stmt = $pdo->prepare("SELECT form_id FROM healthA WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        
        if ($stmt->fetch()) {
            echo "✅ Health assessment already exists for this user<br>";
        } else {
            echo "ℹ️ No health assessment exists yet for this user<br>";
        }
        
    } else {
        echo "❌ Login query failed for " . $testEmail . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><a href='health-assessment.html'>Go to Health Assessment Form</a><br>";
echo "<a href='test-restructured-db.php'>Test Restructured Database</a><br>";
?>
