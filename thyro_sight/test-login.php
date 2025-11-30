<?php
// Test login process
session_start();

echo "<h2>Login Process Test</h2>";

// Check if we're already logged in
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✓ Already logged in as user ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>User Email: " . ($_SESSION['user_email'] ?? 'Not set') . "</p>";
    echo "<p>User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "</p>";
    
    // Test the get-user-data.php functionality
    echo "<h3>Testing get-user-data.php:</h3>";
    
    try {
        require_once 'config/database.php';
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT date_of_birth, gender FROM USER WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "<p style='color: green;'>✓ User data retrieved successfully!</p>";
            echo "<p>Birth Date: " . ($user['date_of_birth'] ? $user['date_of_birth'] : 'Not set') . "</p>";
            echo "<p>Gender: " . ($user['gender'] ? $user['gender'] : 'Not set') . "</p>";
            
            if ($user['date_of_birth']) {
                $birth = new DateTime($user['date_of_birth']);
                $today = new DateTime();
                $age = $today->diff($birth)->y;
                echo "<p>Calculated Age: " . $age . " years old</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ User data not found</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='logout-test.php'>Logout</a></p>";
    
} else {
    echo "<p style='color: orange;'>⚠ Not logged in</p>";
    
    // Show login form
    echo "<h3>Test Login Form:</h3>";
    echo "<form method='post' action='test-login.php'>";
    echo "<p>Email: <input type='email' name='email' value='test@example.com' required></p>";
    echo "<p>Password: <input type='password' name='password' value='test123' required></p>";
    echo "<p><input type='submit' value='Login'></p>";
    echo "</form>";
    
    // Process login if form submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<h3>Processing Login...</h3>";
        
        try {
            require_once 'config/database.php';
            global $pdo;
            
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password FROM USER WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                
                echo "<p style='color: green;'>✓ Login successful! User ID: " . $user['id'] . "</p>";
                echo "<p><a href='test-login.php'>Refresh page to see logged in status</a></p>";
                
            } else {
                echo "<p style='color: red;'>✗ Login failed - Invalid credentials</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
        }
    }
}

echo "<hr>";
echo "<h3>Current Session Info:</h3>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";
echo "<pre>Session Data: " . print_r($_SESSION, true) . "</pre>";
?>
