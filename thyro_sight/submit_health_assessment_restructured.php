<?php
// ===================================================
// submit_health_assessment_restructured.php
// Supports 4 separate tables: medhis, famhis, cursym, labres
// ===================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

// --------------------------------------------------
// 1 Basic Checks
// --------------------------------------------------
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// --------------------------------------------------
// Helper function
// --------------------------------------------------
if (!function_exists('boolValSafe')) {
    function boolValSafe($v) {
        if (!isset($v) || $v === null || $v === '') return 0;
        if (is_bool($v)) return $v ? 1 : 0;
        if (is_numeric($v)) return intval($v) ? 1 : 0;
        $s = strtolower(trim((string)$v));
        return in_array($s, ['1','true','yes','y','on'], true) ? 1 : 0;
    }
}

try {
    // --------------------------------------------------
    // 2 Decode Input + Log
    // --------------------------------------------------
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) throw new Exception('Invalid JSON data received.');

    $logDir = __DIR__ . '/logs';
    if (!file_exists($logDir)) mkdir($logDir, 0777, true);
    $logFile = $logDir . '/submit_assessment_' . date('Y-m-d') . '.log';
    file_put_contents($logFile, json_encode([
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $_SESSION['user_id'],
        'received_input' => $input
    ], JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);

    // --------------------------------------------------
    // 3 Validate Data
    // --------------------------------------------------
    $required_fields = ['form_data', 'prediction', 'c_score'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field])) throw new Exception("Missing field: $field");
    }

    $valid_predictions = ['normal', 'hypo', 'hyper'];
    if (!in_array($input['prediction'], $valid_predictions)) {
        throw new Exception("Invalid prediction value: " . $input['prediction']);
    }

    $confidence_score = floatval($input['c_score']);
    if ($confidence_score < 0 || $confidence_score > 100) {
        throw new Exception('Invalid confidence score range');
    }

    // --------------------------------------------------
    // 4 Extract + Normalize Data
    // --------------------------------------------------
    $user_id = $_SESSION['user_id'];
    $form_data = $input['form_data'];
    $prediction = strtolower($input['prediction']);
    $mode = ucfirst(strtolower($input['mode'] ?? 'Hybrid'));
    $shap_values = isset($input['shap_values']) ? json_encode($input['shap_values']) : null;

    // --------------------------------------------------
    // 5 Begin Transaction
    // --------------------------------------------------
    $pdo->beginTransaction();

    // --------------------------------------------------
    // 5.1 Insert into healthA (Main Assessment Record)
    // --------------------------------------------------
    $age = intval($form_data['age'] ?? 0);
    $gender = ($form_data['gender'] == 1 || strtolower($form_data['gender']) == 'male') ? 'male'
            : (($form_data['gender'] == 0 || strtolower($form_data['gender']) == 'female') ? 'female' : 'other');

    $stmt = $pdo->prepare("
        INSERT INTO healthA (user_id, age, gender, mode, status, created_at)
        VALUES (?, ?, ?, ?, 'completed', NOW())
    ");
    $stmt->execute([$user_id, $age, $gender, $mode]);
    $form_id = $pdo->lastInsertId();

    // --------------------------------------------------
    // 5.2 Insert into medhis (Medical History)
    // --------------------------------------------------
    $diabetes = boolValSafe($form_data['diabetes'] ?? $form_data['Diabetes'] ?? null);
    $high_blood_pressure = boolValSafe($form_data['high_blood_pressure'] ?? $form_data['highbloodpressure'] ?? $form_data['HighBloodPressure'] ?? null);
    $high_cholesterol = boolValSafe($form_data['high_cholesterol'] ?? $form_data['highcholesterol'] ?? $form_data['HighCholesterol'] ?? null);
    $anemia = boolValSafe($form_data['anemia'] ?? $form_data['Anemia'] ?? null);
    $depression_anxiety = boolValSafe($form_data['depression_anxiety'] ?? $form_data['depressionanxiety'] ?? $form_data['DepressionAnxiety'] ?? null);
    $heart_disease = boolValSafe($form_data['heart_disease'] ?? $form_data['heartdisease'] ?? $form_data['HeartDisease'] ?? null);
    $menstrual_irregularities = boolValSafe($form_data['menstrual_irregularities'] ?? $form_data['menstrualirregularities'] ?? $form_data['MenstrualIrregularities'] ?? null);
    $autoimmune_diseases = boolValSafe($form_data['autoimmune_diseases'] ?? $form_data['autoimmunediseases'] ?? $form_data['AutoimmuneDiseases'] ?? null);

    $stmt = $pdo->prepare("
        INSERT INTO medhis (
            form_id, user_id, diabetes, high_blood_pressure, high_cholesterol, 
            anemia, depression_anxiety, heart_disease, menstrual_irregularities, autoimmune_diseases
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $form_id, $user_id, $diabetes, $high_blood_pressure, $high_cholesterol,
        $anemia, $depression_anxiety, $heart_disease, $menstrual_irregularities, $autoimmune_diseases
    ]);

    // --------------------------------------------------
    // 5.3 Insert into famhis (Family History)
    // --------------------------------------------------
    $fh_hypothyroidism = boolValSafe($form_data['fh_hypothyroidism'] ?? $form_data['FH_Hypothyroidism'] ?? null);
    $fh_hyperthyroidism = boolValSafe($form_data['fh_hyperthyroidism'] ?? $form_data['FH_Hyperthyroidism'] ?? null);
    $fh_goiter = boolValSafe($form_data['fh_goiter'] ?? $form_data['FH_Goiter'] ?? null);
    $fh_thyroid_cancer = boolValSafe($form_data['fh_thyroid_cancer'] ?? $form_data['FH_ThyroidCancer'] ?? null);

    $stmt = $pdo->prepare("
        INSERT INTO famhis (form_id, user_id, fh_hypothyroidism, fh_hyperthyroidism, fh_goiter, fh_thyroid_cancer)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$form_id, $user_id, $fh_hypothyroidism, $fh_hyperthyroidism, $fh_goiter, $fh_thyroid_cancer]);

    // --------------------------------------------------
    // 5.4 Insert into cursym (Current Symptoms)
    // --------------------------------------------------
    $sym_fatigue = boolValSafe($form_data['sym_fatigue'] ?? $form_data['Sym_Fatigue'] ?? null);
    $sym_weight_change = boolValSafe($form_data['sym_weight_change'] ?? $form_data['sym_weightchange'] ?? $form_data['Sym_WeightChange'] ?? null);
    $sym_dry_skin = boolValSafe($form_data['sym_dry_skin'] ?? $form_data['sym_dryskin'] ?? $form_data['Sym_DrySkin'] ?? null);
    $sym_hair_loss = boolValSafe($form_data['sym_hair_loss'] ?? $form_data['sym_hairloss'] ?? $form_data['Sym_HairLoss'] ?? null);
    $sym_heart_rate = boolValSafe($form_data['sym_heart_rate'] ?? $form_data['sym_heartrate'] ?? $form_data['Sym_HeartRate'] ?? null);
    $sym_digestion = boolValSafe($form_data['sym_digestion'] ?? $form_data['Sym_Digestion'] ?? null);
    $sym_irregular_periods = boolValSafe($form_data['sym_irregular_periods'] ?? $form_data['sym_irregularperiods'] ?? $form_data['Sym_IrregularPeriods'] ?? null);
    $sym_neck_swelling = boolValSafe($form_data['sym_neck_swelling'] ?? $form_data['sym_neckswelling'] ?? $form_data['Sym_NeckSwelling'] ?? null);

    $stmt = $pdo->prepare("
        INSERT INTO cursym (
            form_id, user_id, sym_fatigue, sym_weight_change, sym_dry_skin, sym_hair_loss,
            sym_heart_rate, sym_digestion, sym_irregular_periods, sym_neck_swelling
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $form_id, $user_id, $sym_fatigue, $sym_weight_change, $sym_dry_skin, $sym_hair_loss,
        $sym_heart_rate, $sym_digestion, $sym_irregular_periods, $sym_neck_swelling
    ]);

    // --------------------------------------------------
    // 5.5 Insert into labres (Lab Results)
    // --------------------------------------------------
    $tsh = boolValSafe($form_data['tsh'] ?? null);
    $t3 = boolValSafe($form_data['t3'] ?? null);
    $t4 = boolValSafe($form_data['t4'] ?? null);
    $t4_uptake = boolValSafe($form_data['t4-uptake'] ?? $form_data['t4_uptake'] ?? null);
    $fti = boolValSafe($form_data['fti'] ?? null);

    $tsh_level = floatval($form_data['tshValue'] ?? $form_data['tsh_value'] ?? 0);
    $t3_level = floatval($form_data['t3Value'] ?? $form_data['t3_value'] ?? 0);
    $t4_level = floatval($form_data['t4Value'] ?? $form_data['t4_value'] ?? 0);
    $t4_uptake_result = floatval($form_data['t4-uptakeValue'] ?? $form_data['t4_uptakeValue'] ?? $form_data['t4_uptake_value'] ?? 0);
    $fti_result = floatval($form_data['ftiValue'] ?? $form_data['fti_value'] ?? 0);

    $stmt = $pdo->prepare("
        INSERT INTO labres (
            form_id, user_id, tsh, t3, t4, t4_uptake, fti,
            tsh_level, t3_level, t4_level, t4_uptake_result, fti_result
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $form_id, $user_id, $tsh, $t3, $t4, $t4_uptake, $fti,
        $tsh_level, $t3_level, $t4_level, $t4_uptake_result, $fti_result
    ]);

    // --------------------------------------------------
    // 5.6 Insert into Result table
    // --------------------------------------------------
    $stmt = $pdo->prepare("INSERT INTO Result (form_id, user_id, prediction, c_score, mode) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$form_id, $user_id, $prediction, $confidence_score, $mode]);
    $result_id = $pdo->lastInsertId();

    // --------------------------------------------------
    // 5.7 Insert into shap_history table
    // --------------------------------------------------
    if ($shap_values) {
        $stmt = $pdo->prepare("
            INSERT INTO shap_history (user_id, form_id, prediction_label, confidence, shap_factors, mode)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $form_id, $prediction, $confidence_score, $shap_values, $mode]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Health assessment saved successfully across all tables.',
        'form_id' => $form_id,
        'result_id' => $result_id,
        'mode' => $mode
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    file_put_contents(__DIR__ . '/logs/error_' . date('Y-m-d') . '.log',
        '[' . date('H:i:s') . '] ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    file_put_contents(__DIR__ . '/logs/db_error_' . date('Y-m-d') . '.log',
        '[' . date('H:i:s') . '] ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
