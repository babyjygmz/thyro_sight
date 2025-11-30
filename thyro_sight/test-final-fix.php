<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Final Fix</h1>";

// Simulate a logged-in user
session_start();
$_SESSION['user_id'] = 1;

echo "<h2>1. Testing Health Assessment Submission</h2>";

try {
    // Simulate form submission
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
    
    // Change to auth directory to fix path issues
    $currentDir = getcwd();
    chdir('auth');
    
    // Include and test the health assessment script
    ob_start();
    include 'health-assessment.php';
    $output = ob_get_clean();
    
    // Change back to original directory
    chdir($currentDir);
    
    echo "✅ Health assessment script executed<br>";
    echo "Output: " . $output . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>2. Test Complete</h2>";
echo "If you see success above, the health assessment form should now work!<br>";
echo "<br><a href='health-assessment.html'>Test Health Assessment Form</a><br>";
?>
