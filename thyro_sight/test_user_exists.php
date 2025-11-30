<?php
echo "<h1>Test User Exists in Database</h1>";

try {
    require_once 'config/database.php';
    
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Check if USER table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'USER'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ USER table exists</p>";
        
        // Count total users
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM USER");
        $total = $stmt->fetch()['total'];
        echo "<p>Total users in database: " . $total . "</p>";
        
        // Show all users (without passwords)
        $stmt = $pdo->query("SELECT user_id, first_name, last_name, email FROM USER LIMIT 10");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) > 0) {
            echo "<h3>Users in database:</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Name</th><th>Email</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['user_id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>❌ No users found in database</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ USER table does not exist</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h2>Test Login Again:</h2>";
echo "<p><a href='test_login_process.php'>→ Go back to Login Process Test</a></p>";
?>
