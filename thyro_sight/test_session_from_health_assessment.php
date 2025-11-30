<?php
// Test session from health assessment context
session_start();

echo "<h1>Session Test from Health Assessment Context</h1>";

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
} else {
    echo "<p style='color: red; font-size: 18px;'>❌ USER IS NOT LOGGED IN</p>";
}

echo "<hr>";
echo "<h2>Debug Info:</h2>";
echo "<p>Cookies: " . print_r($_COOKIE, true) . "</p>";
echo "<p>Session Save Path: " . session_save_path() . "</p>";
echo "<p>Session Cookie Params: " . print_r(session_get_cookie_params(), true) . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>HTTP Host: " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p>Remote Addr: " . $_SERVER['REMOTE_ADDR'] . "</p>";
echo "<p>HTTP Referer: " . ($_SERVER['HTTP_REFERER'] ?? 'Not set') . "</p>";

echo "<hr>";
echo "<h2>Test Links:</h2>";
echo "<p><a href='health-assessment.html'>→ Go to Health Assessment</a></p>";
echo "<p><a href='test_direct_session.php'>→ Go to Direct Session Test</a></p>";
echo "<p><a href='test_login_process.php'>→ Go to Login Process Test</a></p>";

// Test if we can access the session from a different path
echo "<hr>";
echo "<h2>Session Path Test:</h2>";

// Try to get session from a different path
$sessionPath = session_save_path();
$sessionName = session_name();
$sessionId = session_id();

echo "<p>Current Session ID: " . $sessionId . "</p>";
echo "<p>Session Name: " . $sessionName . "</p>";
echo "<p>Session Save Path: " . $sessionPath . "</p>";

// Check if session file exists
$sessionFile = $sessionPath . '/sess_' . $sessionId;
if (file_exists($sessionFile)) {
    echo "<p style='color: green;'>✅ Session file exists: " . $sessionFile . "</p>";
    echo "<p>Session file size: " . filesize($sessionFile) . " bytes</p>";
    echo "<p>Session file contents: " . file_get_contents($sessionFile) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Session file not found: " . $sessionFile . "</p>";
}
?>
