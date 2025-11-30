<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    // Calculate age from date_of_birth if available
    require_once '../config/database.php';
    $stmt = $pdo->prepare("SELECT date_of_birth, gender, first_name, last_name FROM USER WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        $age = null;
        if (!empty($user['date_of_birth'])) {
            $dob = new DateTime($user['date_of_birth']);
            $today = new DateTime();
            $age = $today->diff($dob)->y;
        }

        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'age' => $age,
                'gender' => $user['gender']
            ]
        ]);
        exit;
    }
}

// If not logged in
echo json_encode(['success' => false, 'message' => 'User not logged in']);
