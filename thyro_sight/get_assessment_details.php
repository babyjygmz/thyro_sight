<?php
// ==========================================================
// get_assessment_details.php
// Fetch detailed health assessment with SHAP explanations
// Compatible with shap_history (JSON-based) structure
// ==========================================================

// Enable error reporting for debugging but don't display errors (they break JSON)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

session_start();
require_once 'config/database.php';

// Clear any output that might have been generated
ob_clean();

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Log access
error_log("get_assessment_details.php accessed with method: " . $_SERVER['REQUEST_METHOD']);

// --- Check login ---
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// --- Check form_id ---
if (!isset($_GET['form_id'])) {
    echo json_encode(['success' => false, 'message' => 'Form ID not provided']);
    exit;
}

$user_id = $_SESSION['user_id'];
$form_id = $_GET['form_id'];

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ------------------------------------------
    // Fetch health assessment and prediction data with lab results
    // ------------------------------------------
    $query = "
        SELECT 
            h.*,
            r.prediction AS prediction,
            r.c_score AS confidence_score,
            l.tsh,
            l.tsh_level,
            l.t3,
            l.t3_level,
            l.t4,
            l.t4_level,
            l.t4_uptake,
            l.t4_uptake_result,
            l.fti,
            l.fti_result,
            m.diabetes,
            m.high_blood_pressure,
            m.high_cholesterol,
            m.anemia,
            m.depression_anxiety,
            m.heart_disease,
            m.menstrual_irregularities,
            m.autoimmune_diseases,
            f.fh_hypothyroidism,
            f.fh_hyperthyroidism,
            f.fh_goiter,
            f.fh_thyroid_cancer,
            s.sym_fatigue,
            s.sym_weight_change,
            s.sym_dry_skin,
            s.sym_hair_loss,
            s.sym_heart_rate,
            s.sym_digestion,
            s.sym_irregular_periods,
            s.sym_neck_swelling
        FROM healthA h
        LEFT JOIN Result r ON h.form_id = r.form_id
        LEFT JOIN labres l ON h.form_id = l.form_id
        LEFT JOIN medhis m ON h.form_id = m.form_id
        LEFT JOIN famhis f ON h.form_id = f.form_id
        LEFT JOIN cursym s ON h.form_id = s.form_id
        WHERE h.form_id = ? AND h.user_id = ?
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$form_id, $user_id]);
    $assessment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assessment) {
        echo json_encode([
            'success' => false,
            'message' => 'Assessment not found or access denied'
        ]);
        exit;
    }

    // ------------------------------------------
    // Fetch SHAP factors (from shap_history JSON)
    // ------------------------------------------
    $factors = [];
    $factorQuery = "SELECT shap_factors FROM shap_history WHERE form_id = ? LIMIT 1";
    $factorStmt = $pdo->prepare($factorQuery);
    $factorStmt->execute([$form_id]);
    $factorRow = $factorStmt->fetch(PDO::FETCH_ASSOC);

    if ($factorRow && !empty($factorRow['shap_factors'])) {
        $decoded = json_decode($factorRow['shap_factors'], true);
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                $factors[] = [
                    'name' => $item['name'] ?? '',
                    'impact' => floatval($item['impact'] ?? 0),
                    'type' => $item['type'] ?? 'neutral',
                    'description' => $item['description'] ?? ''
                ];
            }
        }
    } else {
        // Optional fallback if SHAP data missing
        $factors = [
            ['name' => 'tsh', 'impact' => 0, 'type' => 'neutral', 'description' => 'No SHAP data available']
        ];
    }

    // ------------------------------------------
    // Structure the response for frontend
    // ------------------------------------------
    $processed_assessment = [
        'form_id' => $assessment['form_id'],
        'age' => $assessment['age'],
        'gender' => ucfirst($assessment['gender']),
        'prediction' => $assessment['prediction'] ?? 'pending',
        'confidence_score' => floatval($assessment['confidence_score'] ?? 0),
        'assessment_date' => $assessment['assessment_date'],

        // Lab Results Flags
        'tsh' => $assessment['tsh'] ? 'Yes' : 'No',
        't3' => $assessment['t3'] ? 'Yes' : 'No',
        't4' => $assessment['t4'] ? 'Yes' : 'No',
        't4_uptake' => $assessment['t4_uptake'] ? 'Yes' : 'No',
        'fti' => $assessment['fti'] ? 'Yes' : 'No',

        // Lab Results Values
        'tsh_level' => $assessment['tsh_level'] ?? 0,
        't3_level' => $assessment['t3_level'] ?? 0,
        't4_level' => $assessment['t4_level'] ?? 0,
        't4_uptake_result' => $assessment['t4_uptake_result'] ?? 0,
        'fti_result' => $assessment['fti_result'] ?? 0,

        // Other Medical History (8 fields)
        'diabetes' => $assessment['diabetes'] ? 'Yes' : 'No',
        'high_blood_pressure' => $assessment['high_blood_pressure'] ? 'Yes' : 'No',
        'high_cholesterol' => $assessment['high_cholesterol'] ? 'Yes' : 'No',
        'anemia' => $assessment['anemia'] ? 'Yes' : 'No',
        'depression_anxiety' => $assessment['depression_anxiety'] ? 'Yes' : 'No',
        'heart_disease' => $assessment['heart_disease'] ? 'Yes' : 'No',
        'menstrual_irregularities' => $assessment['menstrual_irregularities'] ? 'Yes' : 'No',
        'autoimmune_diseases' => $assessment['autoimmune_diseases'] ? 'Yes' : 'No',

        // Family History (4 fields)
        'fh_hypothyroidism' => $assessment['fh_hypothyroidism'] ? 'Yes' : 'No',
        'fh_hyperthyroidism' => $assessment['fh_hyperthyroidism'] ? 'Yes' : 'No',
        'fh_goiter' => $assessment['fh_goiter'] ? 'Yes' : 'No',
        'fh_thyroid_cancer' => $assessment['fh_thyroid_cancer'] ? 'Yes' : 'No',

        // Current Symptoms (8 fields)
        'sym_fatigue' => $assessment['sym_fatigue'] ? 'Yes' : 'No',
        'sym_weight_change' => $assessment['sym_weight_change'] ? 'Yes' : 'No',
        'sym_dry_skin' => $assessment['sym_dry_skin'] ? 'Yes' : 'No',
        'sym_hair_loss' => $assessment['sym_hair_loss'] ? 'Yes' : 'No',
        'sym_heart_rate' => $assessment['sym_heart_rate'] ? 'Yes' : 'No',
        'sym_digestion' => $assessment['sym_digestion'] ? 'Yes' : 'No',
        'sym_irregular_periods' => $assessment['sym_irregular_periods'] ? 'Yes' : 'No',
        'sym_neck_swelling' => $assessment['sym_neck_swelling'] ? 'Yes' : 'No',

        'mode' => $assessment['mode'] ?? 'Hybrid',
        'created_at' => $assessment['created_at'],

        // SHAP Explanation Factors
        'factors' => $factors
    ];

    // ------------------------------------------
    // Send JSON response
    // ------------------------------------------
    $response = json_encode([
        'success' => true,
        'assessment' => $processed_assessment
    ]);
    
    ob_clean();
    echo $response;

} catch (PDOException $e) {
    $response = json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    
    ob_clean();
    echo $response;
} catch (Exception $e) {
    $response = json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    
    ob_clean();
    echo $response;
}

ob_end_flush();
?>
