<?php
echo "<h1>Debug Login Process</h1>";

// Simulate the exact login request
$email = "babyjoygomez0103@gmail.com";
$password = "12345678";

echo "Testing login with:<br>";
echo "Email: $email<br>";
echo "Password: $password<br><br>";

try {
    require_once 'config/database.php';
    
    if (!$pdo) {
        echo "❌ Database connection failed<br>";
        exit;
    }
    
    echo "✅ Database connected successfully<br>";
    
    // Step 1: Check if user exists
    echo "<h3>Step 1: User Lookup</h3>";
    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email, password FROM USER WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ User found<br>";
        echo "User ID: " . $user['user_id'] . "<br>";
        echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "Password hash: " . substr($user['password'], 0, 20) . "...<br><br>";
        
        // Step 2: Verify password
        echo "<h3>Step 2: Password Verification</h3>";
        if (password_verify($password, $user['password'])) {
            echo "✅ Password verified successfully<br><br>";
            
            // Step 3: Create session
            echo "<h3>Step 3: Session Creation</h3>";
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            echo "✅ Session created<br>";
            echo "Session ID: " . session_id() . "<br>";
            echo "User ID in session: " . $_SESSION['user_id'] . "<br>";
            echo "User email in session: " . $_SESSION['user_email'] . "<br>";
            echo "User name in session: " . $_SESSION['user_name'] . "<br><br>";
            
            // Step 4: Test the actual login.php response
            echo "<h3>Step 4: Testing login.php Response</h3>";
            
            // Simulate what login.php should return
            $response = [
                'success' => true,
                'message' => 'Login successful!',
                'user' => [
                    'user_id' => $user['user_id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email']
                ]
            ];
            
            echo "✅ Expected response:<br>";
            echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre><br>";
            
            // Step 5: Test redirect
            echo "<h3>Step 5: Redirect Test</h3>";
            echo "✅ Login successful! Should redirect to homepage.html<br>";
            echo "Current session data confirms user is logged in<br>";
            
        } else {
            echo "❌ Password verification failed<br>";
            echo "This means the password hash doesn't match<br>";
        }
        
    } else {
        echo "❌ User not found<br>";
        echo "This means the email doesn't exist in the database<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><h3>Next Steps:</h3>";
echo "1. <a href='login.html'>Try Login Again</a><br>";
echo "2. <a href='homepage.html'>Go to Homepage</a> (if logged in)<br>";
echo "3. <a href='health-assessment.html'>Go to Health Assessment</a> (if logged in)<br>";
?>
