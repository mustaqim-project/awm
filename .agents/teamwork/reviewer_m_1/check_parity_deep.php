<?php
error_reporting(E_ALL);
$id_dir = 'lang/id';
$en_dir = 'lang/en';

function flatten_arr($arr, $prefix = '') {
    $items = [];
    if (!is_array($arr)) return $items;
    foreach ($arr as $k => $v) {
        $new_key = $prefix !== '' ? "$prefix.$k" : (string)$k;
        if (is_array($v)) {
            $items = array_merge($items, flatten_arr($v, $new_key));
        } else {
            $items[$new_key] = $v;
        }
    }
    return $items;
}

$id_files = glob("$id_dir/*.php");
$en_files = glob("$en_dir/*.php");

$missing_in_en = [];
$missing_in_id = [];
$empty_values = [];
$todo_values = [];
$total_id = 0;
$total_en = 0;

foreach ($id_files as $f_id) {
    $base = basename($f_id);
    $f_en = "$en_dir/$base";
    if (!file_exists($f_en)) {
        $missing_in_en[$base] = ['FILE_MISSING'];
        continue;
    }
    $arr_id = include $f_id;
    $arr_en = include $f_en;
    
    $flat_id = flatten_arr($arr_id);
    $flat_en = flatten_arr($arr_en);
    
    $total_id += count($flat_id);
    $total_en += count($flat_en);
    
    $diff_en = array_diff_key($flat_id, $flat_en);
    $diff_id = array_diff_key($flat_en, $flat_id);
    
    if (!empty($diff_en)) {
        $missing_in_en[$base] = array_keys($diff_en);
    }
    if (!empty($diff_id)) {
        $missing_in_id[$base] = array_keys($diff_id);
    }
    
    foreach ($flat_id as $k => $v) {
        if ($v === '' || $v === null) {
            $empty_values[] = ["file" => $f_id, "key" => $k];
        }
        if (is_string($v) && (stripos($v, 'TODO') !== false || stripos($v, 'TBD') !== false)) {
            $todo_values[] = ["file" => $f_id, "key" => $k, "val" => $v];
        }
    }
    foreach ($flat_en as $k => $v) {
        if ($v === '' || $v === null) {
            $empty_values[] = ["file" => $f_en, "key" => $k];
        }
        if (is_string($v) && (stripos($v, 'TODO') !== false || stripos($v, 'TBD') !== false)) {
            $todo_values[] = ["file" => $f_en, "key" => $k, "val" => $v];
        }
    }
}

echo json_encode([
    'total_id_keys' => $total_id,
    'total_en_keys' => $total_en,
    'missing_in_en_count' => count($missing_in_en),
    'missing_in_en' => $missing_in_en,
    'missing_in_id_count' => count($missing_in_id),
    'missing_in_id' => $missing_in_id,
    'empty_values_count' => count($empty_values),
    'empty_values' => array_slice($empty_values, 0, 10),
    'todo_values_count' => count($todo_values),
    'todo_values' => $todo_values
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
