<?php
error_reporting(E_ALL);

$id_files = glob(__DIR__ . '/../../../lang/id/*.php');
$en_files = glob(__DIR__ . '/../../../lang/en/*.php');

function flatten($arr, $prefix = '') {
    $out = [];
    foreach ($arr as $k => $v) {
        $p = $prefix === '' ? (string)$k : "$prefix.$k";
        if (is_array($v)) {
            $out = array_merge($out, flatten($v, $p));
        } else {
            $out[$p] = $v;
        }
    }
    return $out;
}

$total_id = 0;
$total_en = 0;
$missing_in_en = [];
$missing_in_id = [];
$empty_values = [];

foreach ($id_files as $f_id) {
    $fname = basename($f_id);
    $f_en = dirname(__DIR__, 3) . '/lang/en/' . $fname;
    if (!file_exists($f_en)) {
        $missing_in_en[$fname] = ['ENTIRE_FILE_MISSING'];
        continue;
    }
    $arr_id = require $f_id;
    $arr_en = require $f_en;

    $flat_id = flatten($arr_id);
    $flat_en = flatten($arr_en);

    $total_id += count($flat_id);
    $total_en += count($flat_en);

    $diff_en = array_diff_key($flat_id, $flat_en);
    if (!empty($diff_en)) {
        $missing_in_en[$fname] = array_keys($diff_en);
    }
    $diff_id = array_diff_key($flat_en, $flat_id);
    if (!empty($diff_id)) {
        $missing_in_id[$fname] = array_keys($diff_id);
    }
    foreach ($flat_id as $k => $v) {
        if ($v === '' || $v === null) {
            $empty_values[$fname . ' (ID)'][] = $k;
        }
    }
    foreach ($flat_en as $k => $v) {
        if ($v === '' || $v === null) {
            $empty_values[$fname . ' (EN)'][] = $k;
        }
    }
}

echo "Total ID keys: $total_id\n";
echo "Total EN keys: $total_en\n";
echo "Missing in EN: " . count($missing_in_en) . " files\n";
echo "Missing in ID: " . count($missing_in_id) . " files\n";
echo "Files with empty/null values: " . count($empty_values) . "\n";

if (!empty($empty_values)) {
    echo "Empty value samples:\n";
    foreach (array_slice($empty_values, 0, 5, true) as $fk => $keys) {
        echo "  $fk: " . implode(', ', array_slice($keys, 0, 3)) . "\n";
    }
}

if (!empty($missing_in_en) || !empty($missing_in_id)) {
    echo "MISMATCH FOUND:\n";
    print_r(compact('missing_in_en', 'missing_in_id'));
    exit(1);
} else {
    echo "Parity check: 100.00% PERFECT PARITY!\n";
    exit(0);
}
