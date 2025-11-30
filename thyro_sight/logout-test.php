<?php
session_start();

echo "<h2>Logout Test</h2>";

if (isset($_SESSION['user_id'])) {
    echo "<p>Logging out user ID: " . $_SESSION['user_id'] . "</p>";
    
    // Clear session
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    echo "<p style='color: green;'>✓ Logout successful!</p>";
    echo "<p><a href='test-login.php'>Go back to login test</a></p>";
} else {
    echo "<p style='color: orange;'>⚠ No user logged in</p>";
    echo "<p><a href='test-login.php'>Go to login test</a></p>";
}
?>
