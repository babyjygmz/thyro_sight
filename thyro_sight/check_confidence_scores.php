<?php
// Check confidence scores in database
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Please login first");
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Confidence Scores for User: $user_id</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Form ID</th><th>Prediction</th><th>c_score (Raw)</th><th>c_score (Float)</th><th>Created At</th></tr>";

    $query = "
        SELECT 
            r.form_id,
            r.prediction,
            r.c_score,
            h.created_at
        FROM Result r
        JOIN healthA h ON r.form_id = h.form_id
        WHERE r.user_id = ?
        ORDER BY h.created_at DESC
        LIMIT 10
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $raw_score = $row['c_score'];
        $float_score = floatval($raw_score);
        $formatted_score = number_format($float_score, 1);
        
        echo "<tr>";
        echo "<td>{$row['form_id']}</td>";
        echo "<td>{$row['prediction']}</td>";
        echo "<td>{$raw_score}</td>";
        echo "<td>{$formatted_score}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }

    echo "</table>";

    echo "<h3>Database Column Type:</h3>";
    $stmt = $pdo->query("DESCRIBE Result c_score");
    $column_info = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($column_info);
    echo "</pre>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
