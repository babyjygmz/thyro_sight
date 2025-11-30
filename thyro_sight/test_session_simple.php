<?php
// Simple session test
session_start();

echo "<h1>Session Test</h1>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";
echo "<p>Session Data:</p>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
echo "<p>Cookies:</p>";
echo "<pre>" . print_r($_COOKIE, true) . "</pre>";
echo "<p>Session Save Path: " . session_save_path() . "</p>";
echo "<p>Session Cookie Params:</p>";
echo "<pre>" . print_r(session_get_cookie_params(), true) . "</pre>";

// Try to set a test session value
$_SESSION['test_value'] = 'Hello from session test at ' . date('Y-m-d H:i:s');
echo "<p>Set test session value: " . $_SESSION['test_value'] . "</p>";
?>
