<?php
// Fix empty user_id values in the database
session_start();
require_once 'config/database.php';

// Ensure only JSON is output
ob_clean();
header('Content-Type: application/json');

try {
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    $fixedUsers = [];
    $actions = [];
    
    // Temporarily disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Check for users with empty user_id
    $stmt = $pdo->query("SELECT * FROM USER WHERE user_id IS NULL OR user_id = ''");
    $emptyUsers = $stmt->fetchAll();
    
    if (!empty($emptyUsers)) {
        foreach ($emptyUsers as $user) {
            $email = $user['email'];
            
            // Find next available ID
            $stmt = $pdo->query("SELECT MAX(CAST(user_id AS UNSIGNED)) as max_id FROM USER WHERE user_id REGEXP '^[0-9]+$'");
            $result = $stmt->fetch();
            $nextId = ($result['max_id'] ?? 0) + 1;
            
            // Update user record
            $updateStmt = $pdo->prepare("UPDATE USER SET user_id = ? WHERE email = ?");
            $updateResult = $updateStmt->execute([$nextId, $email]);
            
            if ($updateResult) {
                $fixedUsers[] = [
                    'email' => $email,
                    'old_user_id' => $user['user_id'] ?? 'NULL',
                    'new_user_id' => $nextId,
                    'status' => 'fixed'
                ];
                $actions[] = "Fixed empty user_id for $email: NULL → $nextId";
            }
        }
    }
    
    // Check for users with complex user_ids
    $stmt = $pdo->query("SELECT * FROM USER WHERE user_id NOT REGEXP '^[0-9]+$'");
    $complexUsers = $stmt->fetchAll();
    
    foreach ($complexUsers as $user) {
        if ($user['user_id'] && $user['user_id'] !== 'NULL') {
            $email = $user['email'];
            
            // Find next available ID
            $stmt = $pdo->query("SELECT MAX(CAST(user_id AS UNSIGNED)) as max_id FROM USER WHERE user_id REGEXP '^[0-9]+$'");
            $result = $stmt->fetch();
            $nextId = ($result['max_id'] ?? 0) + 1;
            
            // Update user record
            $updateStmt = $pdo->prepare("UPDATE USER SET user_id = ? WHERE email = ?");
            $updateResult = $updateStmt->execute([$nextId, $email]);
            
            if ($updateResult) {
                $fixedUsers[] = [
                    'email' => $email,
                    'old_user_id' => $user['user_id'],
                    'new_user_id' => $nextId,
                    'status' => 'fixed'
                ];
                $actions[] = "Fixed complex user_id for $email: {$user['user_id']} → $nextId";
            }
        }
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Get all users after fixing
    $stmt = $pdo->query("SELECT user_id, email, first_name, last_name FROM USER ORDER BY CAST(user_id AS UNSIGNED) ASC");
    $allUsers = $stmt->fetchAll();
    
    $response = [
        'success' => true,
        'message' => 'User ID fixing completed - now using simple numeric IDs',
        'empty_users_found' => count($emptyUsers),
        'complex_users_found' => count($complexUsers),
        'total_users_fixed' => count(array_filter($fixedUsers, function($u) { return $u['status'] === 'fixed'; })),
        'fixed_users' => $fixedUsers,
        'all_users' => $allUsers,
        'actions' => $actions,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Re-enable foreign key checks even if there's an error
    if (isset($pdo)) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
