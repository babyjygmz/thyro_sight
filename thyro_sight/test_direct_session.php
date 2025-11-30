<?php
// Direct session test - no CORS, no fetch, just direct PHP
session_start();

echo "<h1>Direct Session Test</h1>";

// Check current session
echo "<h2>Current Session:</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Data: " . print_r($_SESSION, true) . "</p>";

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green; font-size: 18px;'>✅ USER IS LOGGED IN!</p>";
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>User Email: " . ($_SESSION['user_email'] ?? 'Not set') . "</p>";
    echo "<p>User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "</p>";
    
    // Test redirect to health assessment
    echo "<hr>";
    echo "<h2>Test Navigation:</h2>";
    echo "<p><a href='health-assessment.html' style='color: blue; font-size: 16px;'>→ Go to Health Assessment</a></p>";
    echo "<p><a href='dashboard.html' style='color: blue; font-size: 16px;'>→ Go to Dashboard</a></p>";
    echo "<p><a href='history.html' style='color: blue; font-size: 16px;'>→ Go to History</a></p>";
    
} else {
    echo "<p style='color: red; font-size: 18px;'>❌ USER IS NOT LOGGED IN</p>";
    
    // Test login
    echo "<hr>";
    echo "<h2>Test Login:</h2>";
    echo "<p><a href='login.html' style='color: blue; font-size: 16px;'>→ Go to Login Page</a></p>";
}

echo "<hr>";
echo "<h2>Debug Info:</h2>";
echo "<p>Cookies: " . print_r($_COOKIE, true) . "</p>";
echo "<p>Session Save Path: " . session_save_path() . "</p>";
echo "<p>Session Cookie Params: " . print_r(session_get_cookie_params(), true) . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>HTTP Host: " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p>Remote Addr: " . $_SERVER['REMOTE_ADDR'] . "</p>";

// Test setting a session value
$_SESSION['test_timestamp'] = date('Y-m-d H:i:s');
echo "<p>Set test timestamp: " . $_SESSION['test_timestamp'] . "</p>";
?>
