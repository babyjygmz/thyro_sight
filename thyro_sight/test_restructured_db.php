<?php
// ===================================================
// test_restructured_db.php
// Tests the new database structure
// ===================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Database Structure Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    table { border-collapse: collapse; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #4CAF50; color: white; }
    .section { margin: 30px 0; padding: 20px; background: #f5f5f5; border-radius: 8px; }
</style>";

if (!$pdo) {
    echo "<p class='error'>✗ Database connection failed</p>";
    exit;
}

echo "<p class='success'>✓ Database connection successful</p>";

// Test 1: Check if new tables exist
echo "<div class='section'>";
echo "<h2>Test 1: Check Table Existence</h2>";
$tables = ['healthA', 'medhis', 'famhis', 'cursym', 'labres', 'Result', 'shap_history'];
$allTablesExist = true;

echo "<table>";
echo "<tr><th>Table Name</th><th>Status</th><th>Row Count</th></tr>";

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];
        echo "<tr><td>$table</td><td class='success'>✓ Exists</td><td>$count rows</td></tr>";
    } catch (PDOException $e) {
        echo "<tr><td>$table</td><td class='error'>✗ Not Found</td><td>-</td></tr>";
        $allTablesExist = false;
    }
}
echo "</table>";

if ($allTablesExist) {
    echo "<p class='success'>✓ All required tables exist</p>";
} else {
    echo "<p class='error'>✗ Some tables are missing. Run create_new_tables.sql first.</p>";
}
echo "</div>";

// Test 2: Check table structure
echo "<div class='section'>";
echo "<h2>Test 2: Check Table Structures</h2>";

$expectedColumns = [
    'medhis' => ['medhis_id', 'form_id', 'user_id', 'diabetes', 'high_blood_pressure', 'high_cholesterol', 'anemia', 'depression_anxiety', 'heart_disease', 'menstrual_irregularities', 'autoimmune_diseases'],
    'famhis' => ['famhis_id', 'form_id', 'user_id', 'fh_hypothyroidism', 'fh_hyperthyroidism', 'fh_goiter', 'fh_thyroid_cancer'],
    'cursym' => ['cursym_id', 'form_id', 'user_id', 'sym_fatigue', 'sym_weight_change', 'sym_dry_skin', 'sym_hair_loss', 'sym_heart_rate', 'sym_digestion', 'sym_irregular_periods', 'sym_neck_swelling'],
    'labres' => ['labres_id', 'form_id', 'user_id', 'tsh', 't3', 't4', 't4_uptake', 'fti', 'tsh_level', 't3_level', 't4_level', 't4_uptake_result', 'fti_result']
];

foreach ($expectedColumns as $table => $columns) {
    echo "<h3>$table</h3>";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $actualColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<table>";
        echo "<tr><th>Column</th><th>Status</th></tr>";
        
        foreach ($columns as $col) {
            if (in_array($col, $actualColumns)) {
                echo "<tr><td>$col</td><td class='success'>✓ Exists</td></tr>";
            } else {
                echo "<tr><td>$col</td><td class='error'>✗ Missing</td></tr>";
            }
        }
        echo "</table>";
    } catch (PDOException $e) {
        echo "<p class='error'>Error checking $table: " . $e->getMessage() . "</p>";
    }
}
echo "</div>";

// Test 3: Check foreign key relationships
echo "<div class='section'>";
echo "<h2>Test 3: Check Foreign Key Constraints</h2>";

$fkTables = ['medhis', 'famhis', 'cursym', 'labres'];
echo "<table>";
echo "<tr><th>Table</th><th>Foreign Keys</th><th>Status</th></tr>";

foreach ($fkTables as $table) {
    try {
        $stmt = $pdo->query("
            SELECT 
                CONSTRAINT_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = '$table'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($fks) >= 2) {
            $fkInfo = [];
            foreach ($fks as $fk) {
                $fkInfo[] = $fk['COLUMN_NAME'] . ' → ' . $fk['REFERENCED_TABLE_NAME'];
            }
            echo "<tr><td>$table</td><td>" . implode('<br>', $fkInfo) . "</td><td class='success'>✓ OK</td></tr>";
        } else {
            echo "<tr><td>$table</td><td>-</td><td class='error'>✗ Missing FKs</td></tr>";
        }
    } catch (PDOException $e) {
        echo "<tr><td>$table</td><td>-</td><td class='error'>✗ Error</td></tr>";
    }
}
echo "</table>";
echo "</div>";

// Test 4: Check data consistency
echo "<div class='section'>";
echo "<h2>Test 4: Data Consistency Check</h2>";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM healthA");
    $healthACount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($healthACount > 0) {
        echo "<p class='info'>Found $healthACount assessment(s) in healthA</p>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM medhis");
        $medhisCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM famhis");
        $famhisCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM cursym");
        $cursymCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM labres");
        $labresCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo "<table>";
        echo "<tr><th>Table</th><th>Count</th><th>Match</th></tr>";
        echo "<tr><td>healthA</td><td>$healthACount</td><td>-</td></tr>";
        echo "<tr><td>medhis</td><td>$medhisCount</td><td>" . ($medhisCount == $healthACount ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td></tr>";
        echo "<tr><td>famhis</td><td>$famhisCount</td><td>" . ($famhisCount == $healthACount ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td></tr>";
        echo "<tr><td>cursym</td><td>$cursymCount</td><td>" . ($cursymCount == $healthACount ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td></tr>";
        echo "<tr><td>labres</td><td>$labresCount</td><td>" . ($labresCount == $healthACount ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td></tr>";
        echo "</table>";
        
        if ($medhisCount == $healthACount && $famhisCount == $healthACount && 
            $cursymCount == $healthACount && $labresCount == $healthACount) {
            echo "<p class='success'>✓ All tables have consistent record counts</p>";
        } else {
            echo "<p class='error'>✗ Record counts don't match. Some data may be missing.</p>";
        }
    } else {
        echo "<p class='info'>No assessments found yet. This is normal for a new installation.</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>Error checking consistency: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 5: Sample query test
echo "<div class='section'>";
echo "<h2>Test 5: Sample Query Test</h2>";

try {
    $stmt = $pdo->query("
        SELECT 
            h.form_id,
            h.age,
            h.gender,
            m.diabetes,
            f.fh_hypothyroidism,
            c.sym_fatigue,
            l.tsh_level
        FROM healthA h
        LEFT JOIN medhis m ON h.form_id = m.form_id
        LEFT JOIN famhis f ON h.form_id = f.form_id
        LEFT JOIN cursym c ON h.form_id = c.form_id
        LEFT JOIN labres l ON h.form_id = l.form_id
        LIMIT 5
    ");
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($results) > 0) {
        echo "<p class='success'>✓ JOIN query successful</p>";
        echo "<table>";
        echo "<tr><th>Form ID</th><th>Age</th><th>Gender</th><th>Diabetes</th><th>FH Hypo</th><th>Fatigue</th><th>TSH Level</th></tr>";
        foreach ($results as $row) {
            echo "<tr>";
            echo "<td>" . $row['form_id'] . "</td>";
            echo "<td>" . $row['age'] . "</td>";
            echo "<td>" . $row['gender'] . "</td>";
            echo "<td>" . ($row['diabetes'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . ($row['fh_hypothyroidism'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . ($row['sym_fatigue'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . ($row['tsh_level'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>No data to display yet. Submit an assessment to test.</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Query failed: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Summary
echo "<div class='section'>";
echo "<h2>Summary</h2>";
echo "<p><strong>Database Structure Status:</strong></p>";
echo "<ul>";
echo "<li>Tables: " . ($allTablesExist ? "<span class='success'>✓ All present</span>" : "<span class='error'>✗ Some missing</span>") . "</li>";
echo "<li>Foreign Keys: Check results above</li>";
echo "<li>Data Consistency: Check results above</li>";
echo "</ul>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If tables are missing, run: <code>create_new_tables.sql</code></li>";
echo "<li>If you have old data, run: <code>php migrate_to_restructured_db.php</code></li>";
echo "<li>Update backend to use: <code>submit_health_assessment_restructured.php</code></li>";
echo "<li>Test by submitting a new assessment</li>";
echo "</ol>";
echo "</div>";

echo "<p style='margin-top: 40px; color: #666;'><em>Test completed at " . date('Y-m-d H:i:s') . "</em></p>";
?>
