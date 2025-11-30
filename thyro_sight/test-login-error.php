<?php
echo "<h1>Test Login Error</h1>";

// Simulate the login process
$email = "babyjoygomez0103@gmail.com";
$password = "12345678";

echo "Testing login for: $email<br>";
echo "Password: $password<br><br>";

try {
    require_once 'config/database.php';
    
    if (!$pdo) {
        echo "❌ Database connection failed<br>";
        exit;
    }
    
    echo "✅ Database connected successfully<br>";
    
    // Check if user exists
    echo "<h3>1. Checking if user exists</h3>";
    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email, password FROM USER WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ User found in database<br>";
        echo "User ID: " . $user['user_id'] . "<br>";
        echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "Password hash: " . substr($user['password'], 0, 20) . "...<br><br>";
        
        // Test password verification
        echo "<h3>2. Testing password verification</h3>";
        if (password_verify($password, $user['password'])) {
            echo "✅ Password is correct!<br>";
            
            // Test session creation
            echo "<h3>3. Testing session creation</h3>";
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            echo "✅ Session created successfully<br>";
            echo "Session ID: " . session_id() . "<br>";
            echo "User ID in session: " . $_SESSION['user_id'] . "<br>";
            echo "User email in session: " . $_SESSION['user_email'] . "<br>";
            echo "User name in session: " . $_SESSION['user_name'] . "<br>";
            
        } else {
            echo "❌ Password verification failed<br>";
            echo "This means the password hash doesn't match<br>";
        }
        
    } else {
        echo "❌ User not found in database<br>";
        echo "This means the email doesn't exist or there's a database issue<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><a href='login.html'>Go to Login Page</a><br>";
echo "<a href='fix-user-ids-simple.php'>Fix User IDs</a><br>";
?>
