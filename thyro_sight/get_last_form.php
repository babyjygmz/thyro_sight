<?php
header('Content-Type: application/json');

try {
    // ✅ Include your centralized database connection
    require_once 'config/database.php'; // $conn should be available

    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // Optional: get user_id dynamically (from session or GET parameter)
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 1;

    // Prepare and execute query to get latest form_id
    $stmt = $conn->prepare("SELECT form_id FROM healthA WHERE user_id = ? ORDER BY form_id DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($form_id);
    $stmt->fetch();
    $stmt->close();

    if ($form_id) {
        echo json_encode([
            "success" => true,
            "form_id" => $form_id
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No forms found for this user."
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
