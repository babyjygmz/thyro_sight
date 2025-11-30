<?php
// ===================================================
// get_assessment_history_restructured.php
// Retrieves assessment history from 4 separate tables
// ===================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Get all assessments with results
    $stmt = $pdo->prepare("
        SELECT 
            h.form_id,
            h.age,
            h.gender,
            h.mode,
            h.status,
            h.assessment_date,
            h.created_at,
            r.result_id,
            r.prediction,
            r.c_score
        FROM healthA h
        LEFT JOIN Result r ON h.form_id = r.form_id
        WHERE h.user_id = ?
        ORDER BY h.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // For each assessment, get data from all 4 tables
    foreach ($assessments as &$assessment) {
        $form_id = $assessment['form_id'];
        
        // Get Medical History
        $stmt = $pdo->prepare("SELECT * FROM medhis WHERE form_id = ?");
        $stmt->execute([$form_id]);
        $assessment['medical_history'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get Family History
        $stmt = $pdo->prepare("SELECT * FROM famhis WHERE form_id = ?");
        $stmt->execute([$form_id]);
        $assessment['family_history'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get Current Symptoms
        $stmt = $pdo->prepare("SELECT * FROM cursym WHERE form_id = ?");
        $stmt->execute([$form_id]);
        $assessment['current_symptoms'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get Lab Results
        $stmt = $pdo->prepare("SELECT * FROM labres WHERE form_id = ?");
        $stmt->execute([$form_id]);
        $assessment['lab_results'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get SHAP data if exists
        $stmt = $pdo->prepare("SELECT * FROM shap_history WHERE form_id = ?");
        $stmt->execute([$form_id]);
        $assessment['shap_data'] = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'assessments' => $assessments,
        'count' => count($assessments)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
