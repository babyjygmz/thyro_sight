<?php
echo "<h1>Count SQL Parameters</h1>";

// INSERT statement from health-assessment.php
$insertSQL = "INSERT INTO healthA (
        user_id, age, gender,
        thyroxine, advised_thyroxine, antithyroid, illness,
        pregnant, surgery, radioactive, hypo_suspected,
        hyper_suspected, lithium, goitre, tumor,
        hypopituitarism, psychiatric,
        tsh, t3, t4, t4_uptake, fti,
        tsh_level, t3_level, t4_level, t4_uptake_result, fti_result,
        status, assessment_date
        ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )";

// Count placeholders
$placeholderCount = substr_count($insertSQL, '?');
echo "INSERT statement has $placeholderCount placeholders<br>";

// Count columns
$columns = [
    'user_id', 'age', 'gender',
    'thyroxine', 'advised_thyroxine', 'antithyroid', 'illness',
    'pregnant', 'surgery', 'radioactive', 'hypo_suspected',
    'hyper_suspected', 'lithium', 'goitre', 'tumor',
    'hypopituitarism', 'psychiatric',
    'tsh', 't3', 't4', 't4_uptake', 'fti',
    'tsh_level', 't3_level', 't4_level', 't4_uptake_result', 'fti_result',
    'status', 'assessment_date'
];

echo "Columns count: " . count($columns) . "<br>";
echo "Columns: " . implode(', ', $columns) . "<br>";

// Values array from execute
$values = [
    '$user_id', '$age', '$gender',
    '$thyroxine', '$advised_thyroxine', '$antithyroid', '$illness',
    '$pregnant', '$surgery', '$radioactive', '$hypo_suspected',
    '$hyper_suspected', '$lithium', '$goitre', '$tumor',
    '$hypopituitarism', '$psychiatric',
    '$tsh', '$t3', '$t4', '$t4_uptake', '$fti',
    '$tsh_level', '$t3_level', '$t4_level', '$t4_uptake_result', '$fti_result',
    "'completed'", "date('Y-m-d H:i:s')"
];

echo "Values count: " . count($values) . "<br>";
echo "Values: " . implode(', ', $values) . "<br>";

if ($placeholderCount === count($columns)) {
    echo "✅ Placeholders match columns<br>";
} else {
    echo "❌ Placeholders don't match columns<br>";
}

if ($placeholderCount === count($values)) {
    echo "✅ Placeholders match values<br>";
} else {
    echo "❌ Placeholders don't match values<br>";
}
?>
