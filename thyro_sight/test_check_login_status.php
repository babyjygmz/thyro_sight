<?php
// Test check_login_status.php directly
echo "<h1>Test check_login_status.php Directly</h1>";

// First, check current session
session_start();
echo "<h2>Current Session Before Call:</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Data: " . print_r($_SESSION, true) . "</p>";

if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ Session has user_id: " . $_SESSION['user_id'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ Session missing user_id</p>";
}

echo "<hr>";

// Now simulate what check_login_status.php does
echo "<h2>Simulating check_login_status.php:</h2>";

// Set session configuration to match check_login_status.php
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
ini_set('session.cookie_secure', false);
ini_set('session.cookie_httponly', true);

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ User is logged in!</p>";
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>User Email: " . ($_SESSION['user_email'] ?? 'Not set') . "</p>";
    echo "<p>User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "</p>";
    
    // Simulate the JSON response
    $response = [
        'logged_in' => true,
        'user_id' => $_SESSION['user_id'],
        'message' => 'User is logged in'
    ];
    
    echo "<h3>JSON Response (what check_login_status.php would return):</h3>";
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
    
} else {
    echo "<p style='color: red;'>❌ User is not logged in</p>";
    
    $response = [
        'logged_in' => false,
        'message' => 'User not logged in'
    ];
    
    echo "<h3>JSON Response (what check_login_status.php would return):</h3>";
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
}

echo "<hr>";
echo "<h2>Debug Info:</h2>";
echo "<p>Cookies: " . print_r($_COOKIE, true) . "</p>";
echo "<p>Session Cookie Params: " . print_r(session_get_cookie_params(), true) . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>HTTP Host: " . $_SERVER['HTTP_HOST'] . "</p>";

echo "<hr>";
echo "<h2>Test Links:</h2>";
echo "<p><a href='health-assessment.html'>→ Go to Health Assessment</a></p>";
echo "<p><a href='test_session_from_health_assessment.php'>→ Test Session from Health Assessment Context</a></p>";
?>
