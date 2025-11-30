<?php
session_start();

echo "<h1>Check User Profile Data</h1>";

if (!isset($_SESSION['user_id'])) {
    echo "❌ User not logged in<br>";
    echo "<a href='login.html'>Go to Login</a>";
    exit;
}

echo "✅ User logged in<br>";
echo "User ID: " . $_SESSION['user_id'] . "<br>";
echo "Session ID: " . session_id() . "<br><br>";

try {
    require_once 'config/database.php';
    
    if (!$pdo) {
        echo "❌ Database connection failed<br>";
        exit;
    }
    
    echo "✅ Database connected successfully<br><br>";
    
    // Check user data
    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email, date_of_birth, gender FROM USER WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<h3>User Profile Data:</h3>";
        echo "User ID: " . $user['user_id'] . "<br>";
        echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "Date of Birth: " . ($user['date_of_birth'] ?: 'NOT SET') . "<br>";
        echo "Gender: " . ($user['gender'] ?: 'NOT SET') . "<br><br>";
        
        // Calculate age if birthdate exists
        if ($user['date_of_birth']) {
            $birth = new DateTime($user['date_of_birth']);
            $today = new DateTime();
            $age = $today->diff($birth)->y;
            echo "Calculated Age: " . $age . " years old<br><br>";
        } else {
            echo "❌ Date of Birth is missing - this will cause the 'System error' message<br><br>";
        }
        
        if (!$user['gender']) {
            echo "❌ Gender is missing - this will cause the 'System error' message<br><br>";
        }
        
        // Check if profile is complete
        $isComplete = $user['date_of_birth'] && $user['gender'];
        if ($isComplete) {
            echo "✅ Profile is complete - health assessment should work!<br>";
        } else {
            echo "❌ Profile is incomplete - need to add missing data<br>";
        }
        
    } else {
        echo "❌ User not found in database<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>Next Steps:</h3>";
echo "1. <a href='health-assessment.html'>Try Health Assessment Again</a><br>";
echo "2. <a href='dashboard.html'>Go to Dashboard</a><br>";
echo "3. <a href='homepage.html'>Go to Homepage</a><br>";
?>
