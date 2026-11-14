<?php
$id = require __DIR__ . '/../../../lang/id/public_solutions.php';
$en = require __DIR__ . '/../../../lang/en/public_solutions.php';

$missingInEn = array_diff_key($id, $en);
$missingInId = array_diff_key($en, $id);

echo "Total keys in ID: " . count($id) . "\n";
echo "Total keys in EN: " . count($en) . "\n";
echo "Missing in EN: " . count($missingInEn) . "\n";
if (!empty($missingInEn)) {
    echo "  " . implode(', ', array_keys($missingInEn)) . "\n";
}
echo "Missing in ID: " . count($missingInId) . "\n";
if (!empty($missingInId)) {
    echo "  " . implode(', ', array_keys($missingInId)) . "\n";
}
