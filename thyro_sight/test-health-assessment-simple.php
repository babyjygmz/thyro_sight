<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Health Assessment Test</h1>";

// Simulate a logged-in user
session_start();
$_SESSION['user_id'] = 1;

echo "<h2>1. Testing Database Connection</h2>";
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

echo "<h2>2. Testing Health Assessment Data Insert</h2>";
try {
    // Get user ID
    $stmt = $pdo->query("SELECT user_id FROM USER LIMIT 1");
    $userId = $stmt->fetchColumn();
    
    if (!$userId) {
        echo "❌ No user found in database<br>";
        exit;
    }
    
    echo "✅ Found user ID: $userId<br>";
    
    // Simulate form data
    $formData = [
        'age' => 25,
        'gender' => 'female',
        'thyroxine' => 0,
        'advised_thyroxine' => 0,
        'antithyroid' => 0,
        'illness' => 0,
        'pregnant' => 0,
        'surgery' => 0,
        'radioactive' => 0,
        'hypo_suspected' => 0,
        'hyper_suspected' => 0,
        'lithium' => 0,
        'goitre' => 0,
        'tumor' => 0,
        'hypopituitarism' => 0,
        'psychiatric' => 0,
        'tsh' => 0,
        't3' => 0,
        't4' => 0,
        't4_uptake' => 0,
        'fti' => 0
    ];
    
    echo "✅ Form data prepared<br>";
    
    // Check if assessment already exists
            $stmt = $pdo->prepare("SELECT form_id FROM healthA WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    if ($stmt->fetch()) {
        echo "⚠️ Assessment exists, updating...<br>";
        
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
            $formData['age'], $formData['gender'],
            $formData['thyroxine'], $formData['advised_thyroxine'], $formData['antithyroid'], $formData['illness'],
            $formData['pregnant'], $formData['surgery'], $formData['radioactive'], $formData['hypo_suspected'],
            $formData['hyper_suspected'], $formData['lithium'], $formData['goitre'], $formData['tumor'],
            $formData['hypopituitarism'], $formData['psychiatric'],
            $formData['tsh'], $formData['t3'], $formData['t4'], $formData['t4_uptake'], $formData['fti'],
            $userId
        ]);
        
    } else {
        echo "✅ No assessment exists, inserting new one...<br>";
        
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
            $userId, $formData['age'], $formData['gender'],
            $formData['thyroxine'], $formData['advised_thyroxine'], $formData['antithyroid'], $formData['illness'],
            $formData['pregnant'], $formData['surgery'], $formData['radioactive'], $formData['hypo_suspected'],
            $formData['hyper_suspected'], $formData['lithium'], $formData['goitre'], $formData['tumor'],
            $formData['hypopituitarism'], $formData['psychiatric'],
            $formData['tsh'], $formData['t3'], $formData['t4'], $formData['t4_uptake'], $formData['fti']
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
            echo "Age: " . $assessment['age'] . "<br>";
            echo "Gender: " . $assessment['gender'] . "<br>";
        }
        
    } else {
        echo "❌ Failed to save health assessment data<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>3. Test Complete</h2>";
echo "✅ Database operations tested successfully!<br>";
echo "<br><a href='health-assessment.html'>Test Health Assessment Form</a><br>";
?>
