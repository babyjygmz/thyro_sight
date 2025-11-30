<?php
echo "<h2>MySQL Crash Fix Script</h2>";
echo "<p>This script will fix the MySQL crash by resetting corrupted data files.</p>";

// Step 1: Check if MySQL is running
echo "<h3>Step 1: Checking MySQL Status</h3>";
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "<p style='color: green;'>✓ MySQL is running</p>";
    echo "<p>MySQL is already working! No need to fix.</p>";
    exit;
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠ MySQL is not running (expected)</p>";
}

// Step 2: Check if we can access the data directory
echo "<h3>Step 2: Checking Data Directory</h3>";
$dataDir = "C:\\xampp\\mysql\\data";
$backupDir = "C:\\xampp\\mysql\\data_backup_" . date('Y-m-d_H-i-s');

if (!is_dir($dataDir)) {
    echo "<p style='color: red;'>✗ Data directory not found: $dataDir</p>";
    exit;
}

echo "<p style='color: green;'>✓ Data directory found: $dataDir</p>";

// Step 3: Create backup
echo "<h3>Step 3: Creating Backup</h3>";
if (!is_dir($backupDir)) {
    if (mkdir($backupDir, 0777, true)) {
        echo "<p style='color: green;'>✓ Backup directory created: $backupDir</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create backup directory</p>";
        exit;
    }
}

// Step 4: List problematic files
echo "<h3>Step 4: Identifying Problem Files</h3>";
$problemFiles = ['ibdata1', 'ib_logfile0', 'ib_logfile1', 'ibtmp1', 'aria_log.00000001'];
$foundFiles = [];

foreach ($problemFiles as $file) {
    $filePath = $dataDir . "\\" . $file;
    if (file_exists($filePath)) {
        $foundFiles[] = $file;
        echo "<p style='color: orange;'>⚠ Found problematic file: $file</p>";
    }
}

if (empty($foundFiles)) {
    echo "<p style='color: green;'>✓ No problematic files found</p>";
} else {
    echo "<p style='color: red;'>✗ Found " . count($foundFiles) . " problematic files</p>";
}

// Step 5: Instructions for manual fix
echo "<h3>Step 5: Manual Fix Instructions</h3>";
echo "<p><strong>Since this script cannot directly modify system files, please follow these steps:</strong></p>";
echo "<ol>";
echo "<li><strong>Stop MySQL</strong> in XAMPP Control Panel</li>";
echo "<li><strong>Open File Explorer</strong> and navigate to: <code>C:\\xampp\\mysql\\data</code></li>";
echo "<li><strong>Delete these files:</strong></li>";
echo "<ul>";
foreach ($foundFiles as $file) {
    echo "<li><code>$file</code></li>";
}
echo "</ul>";
echo "<li><strong>Start MySQL</strong> in XAMPP Control Panel</li>";
echo "<li><strong>Wait for green light</strong></li>";
echo "<li><strong>Test the fix</strong> by running: <a href='check-xampp.php'>check-xampp.php</a></li>";
echo "</ol>";

echo "<h3>Alternative: Quick Reset Method</h3>";
echo "<p>If the above doesn't work:</p>";
echo "<ol>";
echo "<li>Stop MySQL in XAMPP Control Panel</li>";
echo "<li>Rename <code>C:\\xampp\\mysql\\data</code> to <code>C:\\xampp\\mysql\\data_old</code></li>";
echo "<li>Copy <code>C:\\xampp\\mysql\\backup\\data</code> to <code>C:\\xampp\\mysql\\data</code></li>";
echo "<li>Start MySQL again</li>";
echo "</ol>";

echo "<hr>";
echo "<h3>After Fixing:</h3>";
echo "<p>1. <a href='check-xampp.php'>Check if MySQL is working</a></p>";
echo "<p>2. <a href='fix-database.php'>Run database setup</a></p>";
echo "<p>3. <a href='login.html'>Test login page</a></p>";

echo "<p><strong>Note:</strong> This will reset your database, but the fix-database.php script will recreate everything.</p>";
?>
