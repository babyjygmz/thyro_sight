<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Session Test</h1>";

echo "<h2>Current Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Session Status:</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Name: " . session_name() . "<br>";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "<br>";

echo "<h2>User Authentication Check:</h2>";
if (isset($_SESSION['user_id'])) {
    echo "✅ User is logged in with ID: " . $_SESSION['user_id'] . "<br>";
} else {
    echo "❌ User is NOT logged in<br>";
    echo "Session variables available: " . implode(', ', array_keys($_SESSION)) . "<br>";
}

echo "<h2>Test Login (Simulate):</h2>";
if (!isset($_SESSION['user_id'])) {
    echo "<form method='post'>";
    echo "<input type='text' name='test_user_id' placeholder='Enter test user ID' value='1'>";
    echo "<input type='submit' name='test_login' value='Simulate Login'>";
    echo "</form>";
    
    if (isset($_POST['test_login'])) {
        $_SESSION['user_id'] = $_POST['test_user_id'];
        echo "<script>location.reload();</script>";
    }
} else {
    echo "<form method='post'>";
    echo "<input type='submit' name='test_logout' value='Clear Session'>";
    echo "</form>";
    
    if (isset($_POST['test_logout'])) {
        session_destroy();
        echo "<script>location.reload();</script>";
    }
}

echo "<h2>Next Steps:</h2>";
if (isset($_SESSION['user_id'])) {
    echo "✅ Session is working. Now test the health assessment form.<br>";
    echo "<a href='health-assessment.html'>Go to Health Assessment</a><br>";
} else {
    echo "❌ Session issue detected. Check login system.<br>";
}
?>
