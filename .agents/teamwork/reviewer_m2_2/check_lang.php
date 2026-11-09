<?php

$id = require __DIR__ . '/../../../lang/id/labor_machines.php';
$en = require __DIR__ . '/../../../lang/en/labor_machines.php';

echo "ID keys count: " . count($id) . "\n";
echo "EN keys count: " . count($en) . "\n";

$diff1 = array_diff_key($id, $en);
$diff2 = array_diff_key($en, $id);

if (!empty($diff1)) {
    echo "Keys in ID but missing in EN:\n";
    print_r($diff1);
} else {
    echo "All ID keys present in EN.\n";
}

if (!empty($diff2)) {
    echo "Keys in EN but missing in ID:\n";
    print_r($diff2);
} else {
    echo "All EN keys present in ID.\n";
}

// Now check what keys are referenced in index.blade.php
$bladeContent = file_get_contents(__DIR__ . '/../../../resources/views/app/labor-machines/index.blade.php');
preg_match_all("/__\(['\"]labor_machines\.([a-zA-Z0-9_-]+)['\"]/", $bladeContent, $matches);
$usedKeys = array_unique($matches[1]);
echo "Keys referenced in Blade: " . count($usedKeys) . "\n";

$missingInId = [];
$missingInEn = [];
foreach ($usedKeys as $key) {
    if (!array_key_exists($key, $id)) {
        $missingInId[] = $key;
    }
    if (!array_key_exists($key, $en)) {
        $missingInEn[] = $key;
    }
}

if (!empty($missingInId)) {
    echo "Keys used in Blade but missing in lang/id/labor_machines.php:\n";
    print_r($missingInId);
} else {
    echo "All Blade keys found in ID lang file.\n";
}

if (!empty($missingInEn)) {
    echo "Keys used in Blade but missing in lang/en/labor_machines.php:\n";
    print_r($missingInEn);
} else {
    echo "All Blade keys found in EN lang file.\n";
}
