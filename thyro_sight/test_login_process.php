<?php
session_start();

echo "<h1>Login Process Test</h1>";

// Check current session state
echo "<h2>Current Session State:</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Data: " . print_r($_SESSION, true) . "</p>";

// Check if user_id exists
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ User is logged in with ID: " . $_SESSION['user_id'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ No user_id in session</p>";
}

// Check if user_email exists
if (isset($_SESSION['user_email'])) {
    echo "<p style='color: green;'>✅ User email: " . $_SESSION['user_email'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ No user_email in session</p>";
}

// Check if user_name exists
if (isset($_SESSION['user_name'])) {
    echo "<p style='color: green;'>✅ User name: " . $_SESSION['user_name'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ No user_name in session</p>";
}

echo "<hr>";

// Test form to simulate login
echo "<h2>Test Login Form:</h2>";
echo "<form method='POST' action=''>";
echo "<p>Email: <input type='email' name='email' placeholder='test@example.com'></p>";
echo "<p>Password: <input type='password' name='password' placeholder='password'></p>";
echo "<input type='submit' value='Test Login'>";
echo "</form>";

// Process login if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Login Attempt Results:</h2>";
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<p>Attempting login with email: " . htmlspecialchars($email) . "</p>";
    
    if (empty($email) || empty($password)) {
        echo "<p style='color: red;'>❌ Email and password are required</p>";
    } else {
        // Try to connect to database and verify login
        try {
            require_once 'config/database.php';
            
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if user exists
            $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email, password FROM USER WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                echo "<p style='color: red;'>❌ User not found</p>";
            } else {
                echo "<p style='color: green;'>✅ User found in database</p>";
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    echo "<p style='color: green;'>✅ Password verified successfully</p>";
                    
                    // Set session
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    
                    echo "<p style='color: green;'>✅ Session variables set</p>";
                    echo "<p>New session data: " . print_r($_SESSION, true) . "</p>";
                    
                    // Redirect to refresh the page
                    echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
                    echo "<p>Page will reload in 2 seconds to show updated session...</p>";
                    
                } else {
                    echo "<p style='color: red;'>❌ Password verification failed</p>";
                }
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

echo "<hr>";
echo "<h2>Debug Info:</h2>";
echo "<p>Request Method: " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p>POST Data: " . print_r($_POST, true) . "</p>";
echo "<p>All Cookies: " . print_r($_COOKIE, true) . "</p>";
?>
