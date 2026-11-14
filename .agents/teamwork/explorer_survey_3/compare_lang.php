<?php

$files = [
    "public_blog.php",
    "public_bos.php",
    "public_calculators.php",
    "public_contact.php",
    "public_content.php",
    "public_erp.php",
    "public_marketplace.php",
    "public_omnichannel.php",
    "public_resources.php",
    "public_templates.php"
];

function flattenKeys(array $array, string $prefix = ""): array {
    $result = [];
    foreach ($array as $key => $value) {
        $fullKey = $prefix === "" ? (string)$key : $prefix . "." . $key;
        if (is_array($value)) {
            $result = array_merge($result, flattenKeys($value, $fullKey));
        } else {
            $result[$fullKey] = $value;
        }
    }
    return $result;
}

foreach ($files as $file) {
    $en = include "lang/en/" . $file;
    $id = include "lang/id/" . $file;
    $enFlat = flattenKeys($en);
    $idFlat = flattenKeys($id);

    $missingInEn = array_diff_key($idFlat, $enFlat);
    $missingInId = array_diff_key($enFlat, $idFlat);

    echo "=== $file ===\n";
    echo "ID keys: " . count($idFlat) . " | EN keys: " . count($enFlat) . "\n";
    if (!empty($missingInEn)) {
        echo "  Missing in EN (" . count($missingInEn) . "):\n";
        foreach (array_slice(array_keys($missingInEn), 0, 15) as $k) {
            echo "    - $k\n";
        }
        if (count($missingInEn) > 15) echo "    ... and " . (count($missingInEn) - 15) . " more\n";
    }
    if (!empty($missingInId)) {
        echo "  Missing in ID (" . count($missingInId) . "):\n";
        foreach (array_slice(array_keys($missingInId), 0, 15) as $k) {
            echo "    - $k\n";
        }
        if (count($missingInId) > 15) echo "    ... and " . (count($missingInId) - 15) . " more\n";
    }
    if (empty($missingInEn) && empty($missingInId)) {
        echo "  PERFECT KEY MATCH (100%)\n";
    }
}
