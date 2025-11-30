<?php
require_once 'config/database.php';

// Columns to remove from healtha table
$columnsToRemove = [
    'thyroxine',
    'advised_thyroxine',
    'antithyroid',
    'illness',
    'pregnant',
    'surgery',
    'radioactive',
    'hypo_suspected',
    'hyper_suspected',
    'lithium',
    'goitre',
    'tumor',
    'hypopituitarism',
    'psychiatric'
];

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "Starting to remove columns from healtha table...\n\n";
    
    foreach ($columnsToRemove as $column) {
        try {
            $sql = "ALTER TABLE healtha DROP COLUMN `$column`";
            $conn->exec($sql);
            echo "✓ Successfully removed column: $column\n";
        } catch (PDOException $e) {
            // Column might not exist, that's okay
            echo "⚠ Could not remove column '$column': " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Column removal process completed!\n";
    
    // Show remaining columns
    echo "\n📋 Remaining columns in healtha table:\n";
    $result = $conn->query("DESCRIBE healtha");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
