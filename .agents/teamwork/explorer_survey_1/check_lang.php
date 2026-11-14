<?php
$id = require __DIR__ . '/../../../lang/id/public_marketing.php';
$en = require __DIR__ . '/../../../lang/en/public_marketing.php';
echo "Total ID keys: " . count($id) . PHP_EOL;
echo "Total EN keys: " . count($en) . PHP_EOL;
$missingInEn = array_diff_key($id, $en);
$missingInId = array_diff_key($en, $id);
echo "Missing in EN: " . count($missingInEn) . PHP_EOL;
if (!empty($missingInEn)) {
    print_r(array_keys($missingInEn));
}
echo "Missing in ID: " . count($missingInId) . PHP_EOL;
if (!empty($missingInId)) {
    print_r(array_keys($missingInId));
}
