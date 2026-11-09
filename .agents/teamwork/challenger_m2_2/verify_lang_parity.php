<?php

$id = require __DIR__ . '/../../../lang/id/labor_machines.php';
$en = require __DIR__ . '/../../../lang/en/labor_machines.php';

echo "=== Localization Key Parity Check ===" . PHP_EOL;
echo "ID keys count: " . count($id) . PHP_EOL;
echo "EN keys count: " . count($en) . PHP_EOL;

$diffIdEn = array_diff_key($id, $en);
$diffEnId = array_diff_key($en, $id);

echo "Keys in ID but missing in EN: " . count($diffIdEn) . PHP_EOL;
if (count($diffIdEn) > 0) {
    print_r($diffIdEn);
}

echo "Keys in EN but missing in ID: " . count($diffEnId) . PHP_EOL;
if (count($diffEnId) > 0) {
    print_r($diffEnId);
}

echo PHP_EOL . "=== Blade Reference Check ===" . PHP_EOL;
$viewFile = __DIR__ . '/../../../resources/views/app/labor-machines/index.blade.php';
$viewContent = file_get_contents($viewFile);

preg_match_all("/__\(\s*['\"]labor_machines\.([a-zA-Z0-9_]+)['\"]\s*[\),]/", $viewContent, $matches);
$usedKeys = array_unique($matches[1]);
sort($usedKeys);

echo "Total unique labor_machines keys in view: " . count($usedKeys) . PHP_EOL;

$missingInId = [];
$missingInEn = [];
$emptyInId = [];
$emptyInEn = [];

foreach ($usedKeys as $key) {
    if (!array_key_exists($key, $id)) {
        $missingInId[] = $key;
    } elseif (trim((string)$id[$key]) === '') {
        $emptyInId[] = $key;
    }

    if (!array_key_exists($key, $en)) {
        $missingInEn[] = $key;
    } elseif (trim((string)$en[$key]) === '') {
        $emptyInEn[] = $key;
    }
}

echo "Referenced keys missing in ID: " . count($missingInId) . PHP_EOL;
if (count($missingInId) > 0) {
    print_r($missingInId);
}

echo "Referenced keys missing in EN: " . count($missingInEn) . PHP_EOL;
if (count($missingInEn) > 0) {
    print_r($missingInEn);
}

echo "Referenced keys empty in ID: " . count($emptyInId) . PHP_EOL;
if (count($emptyInId) > 0) {
    print_r($emptyInId);
}

echo "Referenced keys empty in EN: " . count($emptyInEn) . PHP_EOL;
if (count($emptyInEn) > 0) {
    print_r($emptyInEn);
}

// Check unused keys
$unused = array_diff(array_keys($id), $usedKeys);
echo "Unused keys (defined in lang but not currently in view): " . count($unused) . PHP_EOL;

if (count($diffIdEn) === 0 && count($diffEnId) === 0 && count($missingInId) === 0 && count($missingInEn) === 0 && count($emptyInId) === 0 && count($emptyInEn) === 0) {
    echo PHP_EOL . ">>> RESULT: PERFECT LOCALIZATION PARITY & COVERAGE (PASS) <<<" . PHP_EOL;
} else {
    echo PHP_EOL . ">>> RESULT: LOCALIZATION ISSUES FOUND (FAIL) <<<" . PHP_EOL;
}
