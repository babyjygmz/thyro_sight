<?php
// ===================================================
// migrate_to_restructured_db.php
// Migrates data from old healthA table to new structure
// ===================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';

if (!$pdo) {
    die("Database connection failed\n");
}

echo "Starting migration...\n\n";

try {
    $pdo->beginTransaction();
    
    // Check if old healthA table has the old structure
    $stmt = $pdo->query("SHOW COLUMNS FROM healthA LIKE 'diabetes'");
    $hasOldStructure = $stmt->rowCount() > 0;
    
    if (!$hasOldStructure) {
        echo "Old structure not found. Migration may have already been completed.\n";
        exit;
    }
    
    // Get all records from old healthA table
    $stmt = $pdo->query("SELECT * FROM healthA");
    $oldRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($oldRecords) . " records to migrate.\n\n";
    
    // Create temporary table with new structure
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS healthA_new (
            form_id INT(11) NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            age INT(11) DEFAULT NULL,
            gender enum('male','female','other') DEFAULT NULL,
            assessment_date timestamp DEFAULT CURRENT_TIMESTAMP,
            mode VARCHAR(20) DEFAULT 'Hybrid',
            status enum('completed','pending','incomplete') DEFAULT 'pending',
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (form_id),
            KEY user_id (user_id),
            CONSTRAINT healthA_new_ibfk_1 FOREIGN KEY (user_id) REFERENCES USER (user_id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    
    foreach ($oldRecords as $record) {
        $form_id = $record['form_id'];
        $user_id = $record['user_id'];
        
        echo "Migrating form_id: $form_id\n";
        
        // Insert into new healthA
        $stmt = $pdo->prepare("
            INSERT INTO healthA_new (form_id, user_id, age, gender, assessment_date, mode, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $form_id,
            $user_id,
            $record['age'] ?? null,
            $record['gender'] ?? null,
            $record['assessment_date'] ?? $record['created_at'],
            $record['mode'] ?? 'Hybrid',
            $record['status'] ?? 'completed',
            $record['created_at'],
            $record['updated_at'] ?? $record['created_at']
        ]);
        
        // Insert into medhis
        $stmt = $pdo->prepare("
            INSERT INTO medhis (form_id, user_id, diabetes, high_blood_pressure, high_cholesterol, 
                anemia, depression_anxiety, heart_disease, menstrual_irregularities, autoimmune_diseases)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $form_id, $user_id,
            $record['diabetes'] ?? 0,
            $record['high_blood_pressure'] ?? 0,
            $record['high_cholesterol'] ?? 0,
            $record['anemia'] ?? 0,
            $record['depression_anxiety'] ?? 0,
            $record['heart_disease'] ?? 0,
            $record['menstrual_irregularities'] ?? 0,
            $record['autoimmune_diseases'] ?? 0
        ]);
        
        // Insert into famhis
        $stmt = $pdo->prepare("
            INSERT INTO famhis (form_id, user_id, fh_hypothyroidism, fh_hyperthyroidism, fh_goiter, fh_thyroid_cancer)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $form_id, $user_id,
            $record['fh_hypothyroidism'] ?? 0,
            $record['fh_hyperthyroidism'] ?? 0,
            $record['fh_goiter'] ?? 0,
            $record['fh_thyroid_cancer'] ?? 0
        ]);
        
        // Insert into cursym
        $stmt = $pdo->prepare("
            INSERT INTO cursym (form_id, user_id, sym_fatigue, sym_weight_change, sym_dry_skin, sym_hair_loss,
                sym_heart_rate, sym_digestion, sym_irregular_periods, sym_neck_swelling)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $form_id, $user_id,
            $record['sym_fatigue'] ?? 0,
            $record['sym_weight_change'] ?? 0,
            $record['sym_dry_skin'] ?? 0,
            $record['sym_hair_loss'] ?? 0,
            $record['sym_heart_rate'] ?? 0,
            $record['sym_digestion'] ?? 0,
            $record['sym_irregular_periods'] ?? 0,
            $record['sym_neck_swelling'] ?? 0
        ]);
        
        // Insert into labres
        $stmt = $pdo->prepare("
            INSERT INTO labres (form_id, user_id, tsh, t3, t4, t4_uptake, fti,
                tsh_level, t3_level, t4_level, t4_uptake_result, fti_result)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $form_id, $user_id,
            $record['tsh'] ?? 0,
            $record['t3'] ?? 0,
            $record['t4'] ?? 0,
            $record['t4_uptake'] ?? 0,
            $record['fti'] ?? 0,
            $record['tsh_level'] ?? null,
            $record['t3_level'] ?? null,
            $record['t4_level'] ?? null,
            $record['t4_uptake_result'] ?? null,
            $record['fti_result'] ?? null
        ]);
    }
    
    // Backup old table and replace with new
    echo "\nBacking up old healthA table...\n";
    $pdo->exec("RENAME TABLE healthA TO healthA_backup");
    
    echo "Activating new healthA table...\n";
    $pdo->exec("RENAME TABLE healthA_new TO healthA");
    
    $pdo->commit();
    
    echo "\n✓ Migration completed successfully!\n";
    echo "✓ Old table backed up as 'healthA_backup'\n";
    echo "✓ New structure is now active\n";
    echo "\nYou can drop the backup table after verifying the migration:\n";
    echo "DROP TABLE healthA_backup;\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    echo "Database rolled back to previous state.\n";
}
?>
