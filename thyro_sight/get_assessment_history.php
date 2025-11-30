<?php
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

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Log the request for debugging
error_log("get_assessment_history.php accessed with method: " . $_SERVER['REQUEST_METHOD']);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get assessment history with results and lab values
    $query = "
        SELECT 
            h.form_id,
            h.assessment_date,
            h.age,
            h.gender,
            r.prediction as prediction,
            r.c_score as confidence_score,
            h.created_at,
            l.tsh,
            l.tsh_level,
            l.t3,
            l.t3_level,
            l.t4,
            l.t4_level,
            l.t4_uptake,
            l.t4_uptake_result,
            l.fti,
            l.fti_result
        FROM healthA h
        LEFT JOIN `Result` r ON h.form_id = r.form_id
        LEFT JOIN labres l ON h.form_id = l.form_id
        WHERE h.user_id = ?
        ORDER BY h.assessment_date DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process the data
    $processed_assessments = [];
    foreach ($assessments as $assessment) {
        // Confidence score is stored as decimal, keep precision
        $confidence_score = floatval($assessment['confidence_score'] ?? 0);
        
        // Debug log
        error_log("Form ID: " . $assessment['form_id'] . " - Raw c_score: " . $assessment['confidence_score'] . " - Processed: " . $confidence_score);
        
        $processed_assessments[] = [
            'form_id' => $assessment['form_id'],
            'assessment_date' => $assessment['assessment_date'],
            'age' => $assessment['age'],
            'gender' => $assessment['gender'],
            'prediction' => $assessment['prediction'] ?? 'pending',
            'confidence_score' => $confidence_score,
            'created_at' => $assessment['created_at'],
            'tsh' => $assessment['tsh'] ?? null,
            'tsh_level' => $assessment['tsh_level'] ?? null,
            't3' => $assessment['t3'] ?? null,
            't3_level' => $assessment['t3_level'] ?? null,
            't4' => $assessment['t4'] ?? null,
            't4_level' => $assessment['t4_level'] ?? null,
            't4_uptake' => $assessment['t4_uptake'] ?? null,
            't4_uptake_result' => $assessment['t4_uptake_result'] ?? null,
            'fti' => $assessment['fti'] ?? null,
            'fti_result' => $assessment['fti_result'] ?? null
        ];
    }

    $response = json_encode([
        'success' => true,
        'assessments' => $processed_assessments
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
