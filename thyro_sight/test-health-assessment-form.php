<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Health Assessment Form Submission</h1>";

// Start session and simulate login
session_start();
$_SESSION['user_id'] = 1;

echo "<h2>1. Session Status</h2>";
echo "User ID: " . $_SESSION['user_id'] . "<br>";
echo "Session ID: " . session_id() . "<br>";

echo "<h2>2. Testing Database Connection</h2>";
try {
    require_once 'config/database.php';
    if ($pdo) {
        echo "✅ Database connected<br>";
    } else {
        echo "❌ Database connection failed<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>3. Testing Direct Form Submission</h2>";
try {
    // Simulate the exact POST data that would come from the form
    $_POST = [
        'age' => '25',
        'gender' => 'female',
        'thyroxine' => 'no',
        'advised_thyroxine' => 'no',
        'antithyroid' => 'no',
        'illness' => 'no',
        'pregnant' => 'no',
        'surgery' => 'no',
        'radioactive' => 'no',
        'hypo_suspected' => 'no',
        'hyper_suspected' => 'no',
        'lithium' => 'no',
        'goitre' => 'no',
        'tumor' => 'no',
        'hypopituitarism' => 'no',
        'psychiatric' => 'no',
        'tsh' => 'no',
        't3' => 'no',
        't4' => 'no',
        't4_uptake' => 'no',
        'fti' => 'no'
    ];
    
    echo "✅ Form data simulated<br>";
    
    // Simulate POST request method
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Change to auth directory to fix path issues
    $currentDir = getcwd();
    chdir('auth');
    
    // Capture output from health assessment script
    ob_start();
    include 'health-assessment.php';
    $output = ob_get_clean();
    
    // Change back to original directory
    chdir($currentDir);
    
    echo "✅ Health assessment script executed<br>";
    echo "Output: " . $output . "<br>";
    
    // Parse the JSON response
    $response = json_decode($output, true);
    if ($response) {
        if ($response['success']) {
            echo "✅ Form submission successful!<br>";
            echo "Message: " . $response['message'] . "<br>";
            echo "Redirect: " . $response['redirect'] . "<br>";
        } else {
            echo "❌ Form submission failed<br>";
            echo "Message: " . $response['message'] . "<br>";
            if (isset($response['debug'])) {
                echo "Debug: " . $response['debug'] . "<br>";
            }
        }
    } else {
        echo "⚠️ Could not parse response as JSON<br>";
        echo "Raw output: " . $output . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Test Complete</h2>";
echo "✅ Health assessment form test completed!<br>";
echo "<br><a href='health-assessment.html'>Go to Health Assessment Form</a><br>";
?>
