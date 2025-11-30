<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the request
error_log("Health Assessment Request: " . $_SERVER['REQUEST_METHOD'] . " from " . $_SERVER['REMOTE_ADDR']);

try {
    require_once '../config/database.php';
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        error_log("Health Assessment Error: User not logged in");
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("Health Assessment Error: Invalid method " . $_SERVER['REQUEST_METHOD']);
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    // Check database connection
    if (!$pdo) {
        error_log("Health Assessment Error: Database connection failed");
        throw new Exception("Database connection failed");
    }

    error_log("Health Assessment: Processing for user " . $_SESSION['user_id']);

    // Get form data
    $user_id = $_SESSION['user_id'];
    $age = $_POST['age'] ?? null;
    $gender = $_POST['gender'] ?? null;
    
    error_log("Health Assessment: Basic info - age: $age, gender: $gender");
    
    // Medical History Section (convert yes/no to 1/0)
    $thyroxine = ($_POST['thyroxine'] === 'yes') ? 1 : 0;
    $advised_thyroxine = ($_POST['advised_thyroxine'] === 'yes') ? 1 : 0;
    $antithyroid = ($_POST['antithyroid'] === 'yes') ? 1 : 0;
    $illness = ($_POST['illness'] === 'yes') ? 1 : 0;
    $pregnant = ($_POST['pregnant'] === 'yes') ? 1 : 0;
    $surgery = ($_POST['surgery'] === 'yes') ? 1 : 0;
    $radioactive = ($_POST['radioactive'] === 'yes') ? 1 : 0;
    $hypo_suspected = ($_POST['hypo_suspected'] === 'yes') ? 1 : 0;
    $hyper_suspected = ($_POST['hyper_suspected'] === 'yes') ? 1 : 0;
    $lithium = ($_POST['lithium'] === 'yes') ? 1 : 0;
    $goitre = ($_POST['goitre'] === 'yes') ? 1 : 0;
    $tumor = ($_POST['tumor'] === 'yes') ? 1 : 0;
    $hypopituitarism = ($_POST['hypopituitarism'] === 'yes') ? 1 : 0;
    $psychiatric = ($_POST['psychiatric'] === 'yes') ? 1 : 0;
    
    // Lab Results Section (convert yes/no to 1/0)
    $tsh = ($_POST['tsh'] === 'yes') ? 1 : 0;
    $t3 = ($_POST['t3'] === 'yes') ? 1 : 0;
    $t4 = ($_POST['t4'] === 'yes') ? 1 : 0;
    $t4_uptake = ($_POST['t4_uptake'] === 'yes') ? 1 : 0;
    $fti = ($_POST['fti'] === 'yes') ? 1 : 0;
    
    // Lab Results Values (only if user answered Yes)
    $tsh_level = ($tsh === 1 && isset($_POST['tsh_level'])) ? (float)$_POST['tsh_level'] : null;
    $t3_level = ($t3 === 1 && isset($_POST['t3_level'])) ? (float)$_POST['t3_level'] : null;
    $t4_level = ($t4 === 1 && isset($_POST['t4_level'])) ? (float)$_POST['t4_level'] : null;
    $t4_uptake_result = ($t4_uptake === 1 && isset($_POST['t4_uptake_result'])) ? (float)$_POST['t4_uptake_result'] : null;
    $fti_result = ($fti === 1 && isset($_POST['fti_result'])) ? (float)$_POST['fti_result'] : null;
    
    error_log("Health Assessment: Medical history processed - thyroxine: $thyroxine, tsh: $tsh");
    
    // Check if healthA table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'healtha2test'");
    if ($tableCheck->rowCount() == 0) {
        error_log("Health Assessment Error: healthA table does not exist");
        throw new Exception("Health assessment table not found. Please contact administrator.");
    }
    
    // Check if assessment already exists for this user
    $checkStmt = $pdo->prepare("SELECT form_id FROM healtha2test WHERE user_id = ?");
    $checkStmt->execute([$user_id]);
    
    if ($checkStmt->fetch()) {
        error_log("Health Assessment: Updating existing assessment for user $user_id");
        // Update existing assessment
        $sql = "UPDATE healthA SET 
                age = ?, gender = ?,
                thyroxine = ?, advised_thyroxine = ?, antithyroid = ?, illness = ?, 
                pregnant = ?, surgery = ?, radioactive = ?, hypo_suspected = ?, 
                hyper_suspected = ?, lithium = ?, goitre = ?, tumor = ?, 
                hypopituitarism = ?, psychiatric = ?,
                tsh = ?, t3 = ?, t4 = ?, t4_uptake = ?, fti = ?,
                tsh_level = ?, t3_level = ?, t4_level = ?, t4_uptake_result = ?, fti_result = ?,
                status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE user_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $age, $gender,
            $thyroxine, $advised_thyroxine, $antithyroid, $illness,
            $pregnant, $surgery, $radioactive, $hypo_suspected,
            $hyper_suspected, $lithium, $goitre, $tumor,
            $hypopituitarism, $psychiatric,
            $tsh, $t3, $t4, $t4_uptake, $fti,
            $tsh_level, $t3_level, $t4_level, $t4_uptake_result, $fti_result,
            'completed', $user_id
        ]);
        
        if (!$result) {
            error_log("Health Assessment Error: Update failed - " . implode(", ", $stmt->errorInfo()));
            throw new Exception("Failed to update assessment");
        }
        
        $message = 'Health assessment updated successfully!';
    } else {
        error_log("Health Assessment: Creating new assessment for user $user_id");
        // Insert new assessment
        $sql = "INSERT INTO healtha2test (
                user_id, age, gender,
                thyroxine, advised_thyroxine, antithyroid, illness,
                pregnant, surgery, radioactive, hypo_suspected,
                hyper_suspected, lithium, goitre, tumor,
                hypopituitarism, psychiatric,
                tsh, t3, t4, t4_uptake, fti,
                tsh_level, t3_level, t4_level, t4_uptake_result, fti_result,
                status, assessment_date
                ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $user_id, $age, $gender,
            $thyroxine, $advised_thyroxine, $antithyroid, $illness,
            $pregnant, $surgery, $radioactive, $hypo_suspected,
            $hyper_suspected, $lithium, $goitre, $tumor,
            $hypopituitarism, $psychiatric,
            $tsh, $t3, $t4, $t4_uptake, $fti,
            $tsh_level, $t3_level, $t4_level, $t4_uptake_result, $fti_result,
            'completed', date('Y-m-d H:i:s')
        ]);
        
        if (!$result) {
            error_log("Health Assessment Error: Insert failed - " . implode(", ", $stmt->errorInfo()));
            throw new Exception("Failed to create assessment");
        }
        
        $message = 'Health assessment saved successfully!';
    }
    
    error_log("Health Assessment: Success for user $user_id");
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'redirect' => '../dashboard.html'
    ]);
    
} catch (PDOException $e) {
    error_log("Health Assessment PDO Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred. Please try again.',
        'debug' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Health Assessment Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => $e->getMessage()
    ]);
}
?>
