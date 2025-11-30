<?php
/**
 * Migration Script: Update healthA Table Structure
 * Adds missing columns for new health assessment questions
 * 
 * This script adds 20 new columns to capture:
 * - Other Medical History (8 fields)
 * - Family History (4 fields)
 * - Current Symptoms (8 fields)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Health Table Migration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2563eb; border-bottom: 3px solid #2563eb; padding-bottom: 10px; }
        h2 { color: #059669; margin-top: 30px; }
        .success { color: #059669; background: #d1fae5; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #1d4ed8; background: #dbeafe; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #d97706; background: #fef3c7; padding: 10px; border-radius: 5px; margin: 10px 0; }
        ul { line-height: 1.8; }
        .column-name { font-family: monospace; background: #f3f4f6; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔧 Health Assessment Table Migration</h1>";
echo "<p><strong>Purpose:</strong> Add missing columns to healthA table for complete health assessment data capture.</p>";

if (!$pdo) {
    echo "<div class='error'>❌ <strong>Error:</strong> Database connection failed. Please check your database configuration.</div>";
    echo "</div></body></html>";
    exit;
}

try {
    echo "<h2>📋 Step 1: Checking Current Table Structure</h2>";
    
    // Check if healthA table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'healthA'");
    if ($stmt->rowCount() == 0) {
        echo "<div class='error'>❌ <strong>Error:</strong> healthA table does not exist. Please run the database setup first.</div>";
        echo "</div></body></html>";
        exit;
    }
    
    echo "<div class='success'>✅ healthA table exists</div>";
    
    // Get current columns
    $stmt = $pdo->query("DESCRIBE healthA");
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div class='info'>📊 Current table has <strong>" . count($existingColumns) . "</strong> columns</div>";
    
    // Define new columns to add
    $newColumns = [
        // Other Medical History (8 fields)
        'diabetes' => "TINYINT(1) DEFAULT 0 COMMENT 'Has diabetes: 1=Yes, 0=No'",
        'high_blood_pressure' => "TINYINT(1) DEFAULT 0 COMMENT 'Has high blood pressure: 1=Yes, 0=No'",
        'high_cholesterol' => "TINYINT(1) DEFAULT 0 COMMENT 'Has high cholesterol: 1=Yes, 0=No'",
        'anemia' => "TINYINT(1) DEFAULT 0 COMMENT 'Has anemia: 1=Yes, 0=No'",
        'depression_anxiety' => "TINYINT(1) DEFAULT 0 COMMENT 'Has depression/anxiety: 1=Yes, 0=No'",
        'heart_disease' => "TINYINT(1) DEFAULT 0 COMMENT 'Has heart disease: 1=Yes, 0=No'",
        'menstrual_irregularities' => "TINYINT(1) DEFAULT 0 COMMENT 'Has menstrual irregularities: 1=Yes, 0=No'",
        'autoimmune_diseases' => "TINYINT(1) DEFAULT 0 COMMENT 'Has autoimmune diseases: 1=Yes, 0=No'",
        
        // Family History (4 fields)
        'fh_hypothyroidism' => "TINYINT(1) DEFAULT 0 COMMENT 'Family history of hypothyroidism: 1=Yes, 0=No'",
        'fh_hyperthyroidism' => "TINYINT(1) DEFAULT 0 COMMENT 'Family history of hyperthyroidism: 1=Yes, 0=No'",
        'fh_goiter' => "TINYINT(1) DEFAULT 0 COMMENT 'Family history of goiter: 1=Yes, 0=No'",
        'fh_thyroid_cancer' => "TINYINT(1) DEFAULT 0 COMMENT 'Family history of thyroid cancer: 1=Yes, 0=No'",
        
        // Current Symptoms (8 fields)
        'sym_fatigue' => "TINYINT(1) DEFAULT 0 COMMENT 'Symptom: Fatigue or weakness: 1=Yes, 0=No'",
        'sym_weight_change' => "TINYINT(1) DEFAULT 0 COMMENT 'Symptom: Unexplained weight gain/loss: 1=Yes, 0=No'",
        'sym_dry_skin' => "TINYINT(1) DEFAULT 0 COMMENT 'Symptom: Dry skin: 1=Yes, 0=No'",
        'sym_hair_loss' => "TINYINT(1) DEFAULT 0 COMMENT 'Symptom: Hair thinning or loss: 1=Yes, 0=No'",
        'sym_heart_rate' => "TINYINT(1) DEFAULT 0 COMMENT 'Symptom: Slow or fast heart rate: 1=Yes, 0=No'",
        'sym_digestion' => "TINYINT(1) DEFAULT 0 COMMENT 'Symptom: Constipation or diarrhea: 1=Yes, 0=No'",
        'sym_irregular_periods' => "TINYINT(1) DEFAULT 0 COMMENT 'Symptom: Irregular periods: 1=Yes, 0=No'",
        'sym_neck_swelling' => "TINYINT(1) DEFAULT 0 COMMENT 'Symptom: Swelling in neck or goiter: 1=Yes, 0=No'"
    ];
    
    echo "<h2>🔍 Step 2: Analyzing Required Changes</h2>";
    
    $columnsToAdd = [];
    $columnsAlreadyExist = [];
    
    foreach ($newColumns as $columnName => $columnDefinition) {
        if (in_array($columnName, $existingColumns)) {
            $columnsAlreadyExist[] = $columnName;
        } else {
            $columnsToAdd[] = $columnName;
        }
    }
    
    if (count($columnsAlreadyExist) > 0) {
        echo "<div class='warning'>⚠️ <strong>" . count($columnsAlreadyExist) . "</strong> columns already exist (will skip):</div>";
        echo "<ul>";
        foreach ($columnsAlreadyExist as $col) {
            echo "<li><span class='column-name'>$col</span></li>";
        }
        echo "</ul>";
    }
    
    if (count($columnsToAdd) == 0) {
        echo "<div class='success'>✅ <strong>All columns already exist!</strong> No migration needed.</div>";
        echo "</div></body></html>";
        exit;
    }
    
    echo "<div class='info'>📝 <strong>" . count($columnsToAdd) . "</strong> new columns will be added:</div>";
    echo "<ul>";
    foreach ($columnsToAdd as $col) {
        echo "<li><span class='column-name'>$col</span></li>";
    }
    echo "</ul>";
    
    echo "<h2>⚙️ Step 3: Adding New Columns</h2>";
    
    $pdo->beginTransaction();
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($columnsToAdd as $columnName) {
        try {
            $columnDefinition = $newColumns[$columnName];
            $sql = "ALTER TABLE healthA ADD COLUMN `$columnName` $columnDefinition";
            $pdo->exec($sql);
            echo "<div class='success'>✅ Added column: <span class='column-name'>$columnName</span></div>";
            $successCount++;
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Failed to add column: <span class='column-name'>$columnName</span><br>Error: " . $e->getMessage() . "</div>";
            $errors[] = $columnName . ": " . $e->getMessage();
            $errorCount++;
        }
    }
    
    if ($errorCount > 0) {
        $pdo->rollBack();
        echo "<div class='error'><strong>❌ Migration Failed!</strong><br>Rolled back all changes due to errors.</div>";
        echo "<h3>Errors encountered:</h3><ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    } else {
        $pdo->commit();
        echo "<h2>✅ Step 4: Migration Completed Successfully!</h2>";
        echo "<div class='success'>";
        echo "<strong>Summary:</strong><br>";
        echo "• Total columns added: <strong>$successCount</strong><br>";
        echo "• Total columns skipped (already exist): <strong>" . count($columnsAlreadyExist) . "</strong><br>";
        echo "• Errors: <strong>$errorCount</strong>";
        echo "</div>";
        
        // Verify final structure
        $stmt = $pdo->query("DESCRIBE healthA");
        $finalColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<div class='info'>📊 Final table structure: <strong>" . count($finalColumns) . "</strong> columns total</div>";
        
        echo "<h2>🎉 Next Steps</h2>";
        echo "<div class='info'>";
        echo "<ol>";
        echo "<li>The healthA table has been successfully updated</li>";
        echo "<li>The submit_health_assessment.php file needs to be updated to save these new fields</li>";
        echo "<li>Test the health assessment form to ensure all data is being saved</li>";
        echo "</ol>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<div class='error'>❌ <strong>Unexpected Error:</strong><br>" . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
