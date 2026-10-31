<?php
/**
 * Forensic Deep Inspection Script for auditor_m_2
 * Audits all files in lang/id and lang/en for integrity violations,
 * placeholder strings, empty values, parameter parity, and dictionary parity.
 */

$idDir = __DIR__ . '/../../../lang/id';
$enDir = __DIR__ . '/../../../lang/en';

$idFiles = glob("$idDir/*.php");
$enFiles = glob("$enDir/*.php");

$report = [
    'id_file_count' => count($idFiles),
    'en_file_count' => count($enFiles),
    'file_mismatch' => [],
    'total_id_leaves' => 0,
    'total_en_leaves' => 0,
    'empty_values' => [],
    'dummy_placeholders' => [],
    'verbatim_key_leak' => [],
    'param_mismatches' => [],
    'unpaired_keys' => [],
];

$idFileNames = array_map('basename', $idFiles);
$enFileNames = array_map('basename', $enFiles);

$report['file_mismatch'] = [
    'only_in_id' => array_values(array_diff($idFileNames, $enFileNames)),
    'only_in_en' => array_values(array_diff($enFileNames, $idFileNames)),
];

function flattenArray($arr, $prefix = '') {
    $out = [];
    foreach ($arr as $k => $v) {
        $fullKey = $prefix === '' ? (string)$k : "$prefix.$k";
        if (is_array($v)) {
            $out = array_merge($out, flattenArray($v, $fullKey));
        } else {
            $out[$fullKey] = (string)$v;
        }
    }
    return $out;
}

function extractParams($str) {
    if (preg_match_all('/:[a-zA-Z0-9_]+/', $str, $matches)) {
        $params = $matches[0];
        sort($params);
        return array_unique($params);
    }
    return [];
}

$dummyRegex = '/\b(TODO|FIXME|TBD|dummy|placeholder|lorem ipsum|xxx|test_value|asdf)\b/i';

foreach ($idFiles as $idFilePath) {
    $fileName = basename($idFilePath);
    $enFilePath = "$enDir/$fileName";
    
    if (!file_exists($enFilePath)) {
        continue;
    }
    
    try {
        $idData = require $idFilePath;
        $enData = require $enFilePath;
    } catch (\Throwable $e) {
        $report['syntax_errors'][] = "$fileName: " . $e->getMessage();
        continue;
    }
    
    if (!is_array($idData) || !is_array($enData)) {
        $report['non_array_files'][] = $fileName;
        continue;
    }
    
    $idFlat = flattenArray($idData);
    $enFlat = flattenArray($enData);
    
    $report['total_id_leaves'] += count($idFlat);
    $report['total_en_leaves'] += count($enFlat);
    
    // Check missing keys between the two
    $idKeys = array_keys($idFlat);
    $enKeys = array_keys($enFlat);
    
    $diffIdEn = array_diff($idKeys, $enKeys);
    $diffEnId = array_diff($enKeys, $idKeys);
    
    if (!empty($diffIdEn)) {
        $report['unpaired_keys']["$fileName: ID has, EN lacks"] = array_slice($diffIdEn, 0, 10);
    }
    if (!empty($diffEnId)) {
        $report['unpaired_keys']["$fileName: EN has, ID lacks"] = array_slice($diffEnId, 0, 10);
    }
    
    // Check values
    foreach ($idFlat as $k => $val) {
        $valTrim = trim($val);
        if ($valTrim === '') {
            $report['empty_values'][] = "lang/id/$fileName: $k";
        }
        if (preg_match($dummyRegex, $valTrim)) {
            $report['dummy_placeholders'][] = "lang/id/$fileName: $k => '$valTrim'";
        }
        // Check if value is just the last segment of the key
        $parts = explode('.', $k);
        $lastPart = end($parts);
        if (strlen($lastPart) > 3 && strtolower($valTrim) === strtolower($lastPart)) {
            $report['verbatim_key_leak'][] = "lang/id/$fileName: $k => '$valTrim'";
        }
        
        // Parameter parity check
        if (isset($enFlat[$k])) {
            $idParams = extractParams($val);
            $enParams = extractParams($enFlat[$k]);
            if ($idParams !== $enParams) {
                $report['param_mismatches'][] = [
                    'file' => $fileName,
                    'key' => $k,
                    'id_val' => $val,
                    'en_val' => $enFlat[$k],
                    'id_params' => $idParams,
                    'en_params' => $enParams,
                ];
            }
        }
    }
    
    foreach ($enFlat as $k => $val) {
        $valTrim = trim($val);
        if ($valTrim === '') {
            $report['empty_values'][] = "lang/en/$fileName: $k";
        }
        if (preg_match($dummyRegex, $valTrim)) {
            $report['dummy_placeholders'][] = "lang/en/$fileName: $k => '$valTrim'";
        }
    }
}

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
