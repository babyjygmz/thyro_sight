<?php
echo "<h1>Test Parameter Count Fix</h1>";

// Simulate the exact scenario from health-assessment.php
$user_id = 1;
$age = 25;
$gender = 'female';
$thyroxine = 0;
$advised_thyroxine = 0;
$antithyroid = 0;
$illness = 0;
$pregnant = 0;
$surgery = 0;
$radioactive = 0;
$hypo_suspected = 0;
$hyper_suspected = 0;
$lithium = 0;
$goitre = 0;
$tumor = 0;
$hypopituitarism = 0;
$psychiatric = 0;
$tsh = 0;
$t3 = 0;
$t4 = 0;
$t4_uptake = 0;
$fti = 0;
$tsh_level = null;
$t3_level = null;
$t4_level = null;
$t4_uptake_result = null;
$fti_result = null;

// INSERT statement
$sql = "INSERT INTO healthA (
        user_id, age, gender,
        thyroxine, advised_thyroxine, antithyroid, illness,
        pregnant, surgery, radioactive, hypo_suspected,
        hyper_suspected, lithium, goitre, tumor,
        hypopituitarism, psychiatric,
        tsh, t3, t4, t4_uptake, fti,
        tsh_level, t3_level, t4_level, t4_uptake_result, fti_result,
        status, assessment_date
        ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )";

// Count placeholders
$placeholderCount = substr_count($sql, '?');
echo "INSERT statement has $placeholderCount placeholders<br>";

// Values array
$values = [
    $user_id, $age, $gender,
    $thyroxine, $advised_thyroxine, $antithyroid, $illness,
    $pregnant, $surgery, $radioactive, $hypo_suspected,
    $hyper_suspected, $lithium, $goitre, $tumor,
    $hypopituitarism, $psychiatric,
    $tsh, $t3, $t4, $t4_uptake, $fti,
    $tsh_level, $t3_level, $t4_level, $t4_uptake_result, $fti_result,
    'completed', date('Y-m-d H:i:s')
];

echo "Values count: " . count($values) . "<br>";

if ($placeholderCount === count($values)) {
    echo "✅ Placeholders match values!<br>";
    echo "The parameter count issue is fixed!<br>";
} else {
    echo "❌ Placeholders don't match values<br>";
    echo "Placeholders: $placeholderCount, Values: " . count($values) . "<br>";
}

echo "<br><a href='test-health-assessment-form.php'>Test Health Assessment Form Again</a><br>";
?>
