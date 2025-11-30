<?php
echo "<h1>Test Actual Login Form Submission</h1>";

// Simulate the exact POST request that the login form sends
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['email'] = 'babyjoygomez0103@gmail.com';
$_POST['password'] = '12345678';

echo "Simulating login form submission:<br>";
echo "Email: " . $_POST['email'] . "<br>";
echo "Password: " . $_POST['password'] . "<br>";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "<br><br>";

echo "<h3>Now including auth/login.php to see what happens:</h3>";
echo "<hr>";

// Capture the output
ob_start();

try {
    // Change to auth directory to fix path issues
    $currentDir = getcwd();
    chdir('auth');
    
    // Include the login script
    include 'login.php';
    
    // Get the output
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<h3>Response from login.php:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Try to parse as JSON
    $jsonData = json_decode($output, true);
    if ($jsonData) {
        echo "<h3>Parsed JSON Response:</h3>";
        echo "<pre>" . print_r($jsonData, true) . "</pre>";
        
        if (isset($jsonData['success'])) {
            if ($jsonData['success']) {
                echo "✅ Login successful!<br>";
                echo "Message: " . $jsonData['message'] . "<br>";
            } else {
                echo "❌ Login failed<br>";
                echo "Message: " . $jsonData['message'] . "<br>";
            }
        }
    } else {
        echo "<h3>Response is not valid JSON:</h3>";
        echo "This might be causing the login error<br>";
    }
    
    // Restore directory
    chdir($currentDir);
    
} catch (Exception $e) {
    echo "❌ Error including login.php: " . $e->getMessage() . "<br>";
    ob_end_clean();
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "1. <a href='login.html'>Try Login Again</a><br>";
echo "2. <a href='debug-login.php'>Run Debug Script</a><br>";
echo "3. <a href='homepage.html'>Go to Homepage</a><br>";
?>
