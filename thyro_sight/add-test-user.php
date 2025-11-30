<?php
// Add a test user to the database
require_once 'config/database.php';

echo "<h2>Adding Test User</h2>";

try {
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM USER WHERE email = ?");
    $stmt->execute(['test@example.com']);
    
    if ($stmt->fetch()) {
        echo "<p style='color: orange;'>⚠ Test user already exists</p>";
    } else {
        // Add test user
        $stmt = $pdo->prepare("INSERT INTO USER (first_name, last_name, email, password, date_of_birth, gender, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
        $birthDate = '1990-01-15';
        $gender = 'Male';
        
        $stmt->execute(['John', 'Doe', 'test@example.com', $hashedPassword, $birthDate, $gender, 1]);
        
        echo "<p style='color: green;'>✓ Test user added successfully!</p>";
        echo "<p><strong>Test User Details:</strong></p>";
        echo "<ul>";
        echo "<li>Email: test@example.com</li>";
        echo "<li>Password: test123</li>";
        echo "<li>Birth Date: " . $birthDate . "</li>";
        echo "<li>Gender: " . $gender . "</li>";
        echo "</ul>";
    }
    
    // Show all users in database
    echo "<h3>All Users in Database:</h3>";
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, date_of_birth, gender, is_verified FROM USER");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Birth Date</th><th>Gender</th><th>Verified</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['first_name'] . " " . $user['last_name'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . ($user['date_of_birth'] ? $user['date_of_birth'] : 'Not set') . "</td>";
            echo "<td>" . ($user['gender'] ? $user['gender'] : 'Not set') . "</td>";
            echo "<td>" . ($user['is_verified'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ No users found in database</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
