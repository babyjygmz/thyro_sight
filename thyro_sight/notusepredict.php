<?php
// ================================================
// predict.php — Integrated AI Gateway (RF + SVM + GB + CNN)
// ================================================
header('Content-Type: application/json');
require_once 'config/database.php';
session_start();

try {
    // -------------------------------------------------
    // Read JSON body
    // -------------------------------------------------
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($input)) throw new Exception("Empty input received.");

    // -------------------------------------------------
// Logging: Record received input for debugging
// -------------------------------------------------
$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}
$logFile = $logDir . '/predict_log_' . date('Y-m-d') . '.log';

$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'session_user' => $_SESSION['user_id'] ?? null,
    'raw_input' => $input
];

file_put_contents(
    $logFile,
    json_encode($logData, JSON_PRETTY_PRINT) . PHP_EOL,
    FILE_APPEND
);


    // -------------------------------------------------
    // Connect to MySQL
    // -------------------------------------------------
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // -------------------------------------------------
    // Insert base health record
    // -------------------------------------------------
    $stmt = $pdo->prepare("
        INSERT INTO healthA (
            user_id, age, gender,
            thyroxine, advised_thyroxine, antithyroid, illness, pregnant, surgery, radioactive,
            hypo_suspected, hyper_suspected, lithium, goitre, tumor, hypopituitarism, psychiatric,
            diabetes, highbloodpressure, highcholesterol, anemia, depressionanxiety, heartdisease,
            menstrualirregularities, autoimmunediseases,
            fh_hypothyroidism, fh_hyperthyroidism, fh_goiter, fh_thyroidcancer,
            sym_fatigue, sym_weightchange, sym_dryskin, sym_hairloss, sym_heartrate,
            sym_digestion, sym_irregularperiods, sym_neckswelling,
            tsh, t3, t4, t4_uptake, fti,
            tsh_level, t3_level, t4_level, t4_uptake_result, fti_result,
            image_prediction, status, created_at, updated_at
        ) VALUES (
            :user_id, :age, :gender,
            :thyroxine, :advised_thyroxine, :antithyroid, :illness, :pregnant, :surgery, :radioactive,
            :hypo_suspected, :hyper_suspected, :lithium, :goitre, :tumor, :hypopituitarism, :psychiatric,
            :diabetes, :highbloodpressure, :highcholesterol, :anemia, :depressionanxiety, :heartdisease,
            :menstrualirregularities, :autoimmunediseases,
            :fh_hypothyroidism, :fh_hyperthyroidism, :fh_goiter, :fh_thyroidcancer,
            :sym_fatigue, :sym_weightchange, :sym_dryskin, :sym_hairloss, :sym_heartrate,
            :sym_digestion, :sym_irregularperiods, :sym_neckswelling,
            :tsh, :t3, :t4, :t4_uptake, :fti,
            :tsh_level, :t3_level, :t4_level, :t4_uptake_result, :fti_result,
            :image_prediction, 'completed', NOW(), NOW()
        )
    ");

    $stmt->execute([
        ':user_id' => $_SESSION['user_id'] ?? 0,
        ':age' => $input['age'] ?? null,
        ':gender' => $input['gender'] ?? null,
        ':thyroxine' => $input['thyroxine'] ?? 0,
        ':advised_thyroxine' => $input['advised_thyroxine'] ?? 0,
        ':antithyroid' => $input['antithyroid'] ?? 0,
        ':illness' => $input['illness'] ?? 0,
        ':pregnant' => $input['pregnant'] ?? 0,
        ':surgery' => $input['surgery'] ?? 0,
        ':radioactive' => $input['radioactive'] ?? 0,
        ':hypo_suspected' => $input['hypo_suspected'] ?? 0,
        ':hyper_suspected' => $input['hyper_suspected'] ?? 0,
        ':lithium' => $input['lithium'] ?? 0,
        ':goitre' => $input['goitre'] ?? 0,
        ':tumor' => $input['tumor'] ?? 0,
        ':hypopituitarism' => $input['hypopituitarism'] ?? 0,
        ':psychiatric' => $input['psychiatric'] ?? 0,
        ':diabetes' => $input['diabetes'] ?? 0,
        ':highbloodpressure' => $input['highbloodpressure'] ?? 0,
        ':highcholesterol' => $input['highcholesterol'] ?? 0,
        ':anemia' => $input['anemia'] ?? 0,
        ':depressionanxiety' => $input['depressionanxiety'] ?? 0,
        ':heartdisease' => $input['heartdisease'] ?? 0,
        ':menstrualirregularities' => $input['menstrualirregularities'] ?? 0,
        ':autoimmunediseases' => $input['autoimmunediseases'] ?? 0,
        ':fh_hypothyroidism' => $input['fh_hypothyroidism'] ?? 0,
        ':fh_hyperthyroidism' => $input['fh_hyperthyroidism'] ?? 0,
        ':fh_goiter' => $input['fh_goiter'] ?? 0,
        ':fh_thyroidcancer' => $input['fh_thyroidcancer'] ?? 0,
        ':sym_fatigue' => $input['sym_fatigue'] ?? 0,
        ':sym_weightchange' => $input['sym_weightchange'] ?? 0,
        ':sym_dryskin' => $input['sym_dryskin'] ?? 0,
        ':sym_hairloss' => $input['sym_hairloss'] ?? 0,
        ':sym_heartrate' => $input['sym_heartrate'] ?? 0,
        ':sym_digestion' => $input['sym_digestion'] ?? 0,
        ':sym_irregularperiods' => $input['sym_irregularperiods'] ?? 0,
        ':sym_neckswelling' => $input['sym_neckswelling'] ?? 0,
        ':tsh' => $input['tsh'] ?? null,
        ':t3' => $input['t3'] ?? null,
        ':t4' => $input['t4'] ?? null,
        ':t4_uptake' => $input['t4_uptake'] ?? null,
        ':fti' => $input['fti'] ?? null,
        ':tsh_level' => $input['tsh'] ?? null,
        ':t3_level' => $input['t3'] ?? null,
        ':t4_level' => $input['t4'] ?? null,
        ':t4_uptake_result' => $input['t4_uptake'] ?? null,
        ':fti_result' => $input['fti'] ?? null,
        ':image_prediction' => $input['image_prediction'] ?? null
    ]);

    $form_id = $pdo->lastInsertId();
    $input['form_id'] = $form_id;

    // -------------------------------------------------
    // Handle CNN Image Prediction (optional)
    // -------------------------------------------------
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file = $_FILES['image']['tmp_name'];
        $name = $_FILES['image']['name'];
        $cfile = new CURLFile($file, mime_content_type($file), $name);

        $ch = curl_init('http://127.0.0.1:5001/predict_image');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => $cfile]);
        $cnnResponse = curl_exec($ch);
        curl_close($ch);

        $cnnResult = json_decode($cnnResponse, true);
        if (!empty($cnnResult['success'])) {
            $input['image_prediction'] = $cnnResult['prediction'];
            $input['image_confidence'] = $cnnResult['confidence'];
        }
    }

    // -------------------------------------------------
    // Prepare payload for Flask API
    // -------------------------------------------------
    $flaskPayload = $input;
    $flaskPayload['user_id'] = $_SESSION['user_id'] ?? 0;
    $flaskPayload['form_id'] = $form_id;

    $expected_features = [
        'age','gender','tsh','t3','t4','t4_uptake','fti',
        'thyroxine','advised_thyroxine','antithyroid','illness','pregnant','surgery','radioactive',
        'hypo_suspected','hyper_suspected','lithium','goitre','tumor','hypopituitarism','psychiatric',
        'diabetes','highbloodpressure','highcholesterol','anemia','depressionanxiety','heartdisease',
        'menstrualirregularities','autoimmunediseases','fh_hypothyroidism','fh_hyperthyroidism',
        'fh_goiter','fh_thyroidcancer','sym_fatigue','sym_weightchange','sym_dryskin','sym_hairloss',
        'sym_heartrate','sym_digestion','sym_irregularperiods','sym_neckswelling','image_prediction'
    ];
    foreach($expected_features as $feat){
        if(!isset($flaskPayload[$feat])) $flaskPayload[$feat] = 0;
    }

    // Cast numeric fields
    foreach (['age','tsh','t3','t4','t4_uptake','fti'] as $f) {
        $flaskPayload[$f] = isset($flaskPayload[$f]) ? floatval($flaskPayload[$f]) : 0;
    }

    // -------------------------------------------------
    // Send to Flask API (RF + SVM + GB)
    // -------------------------------------------------
    $ch = curl_init('http://127.0.0.1:5000/predict');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($flaskPayload));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $flaskData = json_decode($response, true);

        // Optional: fetch model comparison metrics
        $ch2 = curl_init('http://127.0.0.1:5000/metrics_comparison');
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $compResp = curl_exec($ch2);
        curl_close($ch2);

        $comparison_metrics = json_decode($compResp, true)['metrics'] ?? [];
        $flaskData['comparison_metrics'] = $comparison_metrics;

        echo json_encode($flaskData);
    } else {
        echo json_encode(['success' => false, 'message' => 'Flask server error', 'code' => $httpCode]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
