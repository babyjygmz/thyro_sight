<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Final Health Assessment Test</h1>";

// Simulate a logged-in user session
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
        echo "Database: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "<br>";
    } else {
        echo "❌ Database connection failed<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>3. Testing Tables</h2>";
try {
    // Check USER table
    $stmt = $pdo->query("SHOW TABLES LIKE 'USER'");
    if ($stmt->rowCount() > 0) {
        echo "✅ USER table exists<br>";
    } else {
        echo "❌ USER table missing<br>";
        exit;
    }
    
    // Check healthA table
    $stmt = $pdo->query("SHOW TABLES LIKE 'healthA'");
    if ($stmt->rowCount() > 0) {
        echo "✅ healthA table exists<br>";
    } else {
        echo "❌ healthA table missing<br>";
        exit;
    }
    
} catch (Exception $e) {
    echo "❌ Table check error: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>4. Testing User Data</h2>";
try {
    $stmt = $pdo->query("SELECT user_id, first_name, last_name FROM USER LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ Found user: " . $user['first_name'] . " " . $user['last_name'] . "<br>";
        echo "User ID: " . $user['user_id'] . "<br>";
    } else {
        echo "❌ No users found<br>";
        exit;
    }
    
} catch (Exception $e) {
    echo "❌ User check error: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>5. Simulating Health Assessment Submission</h2>";
try {
    // Simulate the exact data that would come from the form
    $formData = [
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
    
    echo "Form data collected: " . count($formData) . " fields<br>";
    
    // Convert Yes/No to 1/0
    $convertedData = [];
    foreach ($formData as $key => $value) {
        $convertedData[$key] = ($value === 'yes') ? 1 : 0;
    }
    
    // Get user ID from database
    $userId = $user['user_id'];
    
    // Check if assessment already exists
            $stmt = $pdo->prepare("SELECT form_id FROM healthA WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    if ($stmt->rowCount() > 0) {
        echo "⚠️ Assessment already exists, will update<br>";
        
        // Update existing assessment
        $sql = "UPDATE healthA SET 
                age = ?, gender = ?, 
                thyroxine = ?, advised_thyroxine = ?, antithyroid = ?, illness = ?, 
                pregnant = ?, surgery = ?, radioactive = ?, hypo_suspected = ?, 
                hyper_suspected = ?, lithium = ?, goitre = ?, tumor = ?, 
                hypopituitarism = ?, psychiatric = ?, 
                tsh = ?, t3 = ?, t4 = ?, t4_uptake = ?, fti = ?,
                status = 'completed', updated_at = CURRENT_TIMESTAMP
                WHERE user_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $convertedData['age'], $convertedData['gender'],
            $convertedData['thyroxine'], $convertedData['advised_thyroxine'], $convertedData['antithyroid'], $convertedData['illness'],
            $convertedData['pregnant'], $convertedData['surgery'], $convertedData['radioactive'], $convertedData['hypo_suspected'],
            $convertedData['hyper_suspected'], $convertedData['lithium'], $convertedData['goitre'], $convertedData['tumor'],
            $convertedData['hypopituitarism'], $convertedData['psychiatric'],
            $convertedData['tsh'], $convertedData['t3'], $convertedData['t4'], $convertedData['t4_uptake'], $convertedData['fti'],
            $userId
        ]);
        
    } else {
        echo "✅ No existing assessment, will insert new one<br>";
        
        // Insert new assessment
        $sql = "INSERT INTO healthA (
                user_id, age, gender, 
                thyroxine, advised_thyroxine, antithyroid, illness, 
                pregnant, surgery, radioactive, hypo_suspected, 
                hyper_suspected, lithium, goitre, tumor, 
                hypopituitarism, psychiatric, 
                tsh, t3, t4, t4_uptake, fti, 
                status, assessment_date
                ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', CURRENT_TIMESTAMP
                )";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $userId, $convertedData['age'], $convertedData['gender'],
            $convertedData['thyroxine'], $convertedData['advised_thyroxine'], $convertedData['antithyroid'], $convertedData['illness'],
            $convertedData['pregnant'], $convertedData['surgery'], $convertedData['radioactive'], $convertedData['hypo_suspected'],
            $convertedData['hyper_suspected'], $convertedData['lithium'], $convertedData['goitre'], $convertedData['tumor'],
            $convertedData['hypopituitarism'], $convertedData['psychiatric'],
            $convertedData['tsh'], $convertedData['t3'], $convertedData['t4'], $convertedData['t4_uptake'], $convertedData['fti']
        ]);
    }
    
    if ($result) {
        echo "✅ Health assessment data saved successfully!<br>";
        
        // Verify the data was saved
        $stmt = $pdo->prepare("SELECT * FROM healthA WHERE user_id = ?");
        $stmt->execute([$userId]);
        $assessment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($assessment) {
            echo "✅ Data verified in database<br>";
            echo "Assessment ID: " . $assessment['form_id'] . "<br>";
            echo "Status: " . $assessment['status'] . "<br>";
        }
        
    } else {
        echo "❌ Failed to save health assessment data<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error saving health assessment: " . $e->getMessage() . "<br>";
    echo "Error details: " . print_r($e->getTrace(), true) . "<br>";
}

echo "<h2>6. Summary</h2>";
echo "✅ Database connection: Working<br>";
echo "✅ Tables: Exist<br>";
echo "✅ User data: Available<br>";
echo "✅ Health assessment: Tested<br>";
echo "<br><a href='health-assessment.html'>Go to Health Assessment Form</a><br>";
?>
