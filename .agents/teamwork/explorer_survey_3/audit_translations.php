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
            $result[$fullKey] = (string)$value;
        }
    }
    return $result;
}

echo "=== UNTRANSLATED STRINGS CHECK (EN identical to ID for length > 20) ===\n";
$allLangKeys = [];

foreach ($files as $file) {
    $prefix = str_replace('.php', '', $file);
    $en = include "lang/en/" . $file;
    $id = include "lang/id/" . $file;
    $enFlat = flattenKeys($en, $prefix);
    $idFlat = flattenKeys($id, $prefix);

    foreach ($idFlat as $k => $v) {
        $allLangKeys[$k] = true;
    }

    $identicalLong = [];
    foreach ($enFlat as $k => $enVal) {
        $idVal = $idFlat[$k] ?? '';
        if (strlen($enVal) > 20 && $enVal === $idVal) {
            // Check if it's not just a technical string / URL
            if (!str_starts_with($enVal, 'http') && !str_starts_with($enVal, '{')) {
                $identicalLong[$k] = $enVal;
            }
        }
    }

    if (!empty($identicalLong)) {
        echo "$file has " . count($identicalLong) . " identical values in EN and ID:\n";
        foreach (array_slice($identicalLong, 0, 5, true) as $k => $v) {
            echo "  [$k] => " . substr($v, 0, 60) . "...\n";
        }
    } else {
        echo "$file: All values translated or properly localized!\n";
    }
}

// Now let's check all Blade templates in scope for __() calls
$scopeDirs = [
    "resources/views/public/blog",
    "resources/views/public/business-operating-system",
    "resources/views/public/calculators",
    "resources/views/public/contact",
    "resources/views/public/content-automation",
    "resources/views/public/marketplace",
    "resources/views/public/omnichannel",
    "resources/views/public/omnichannel-erp",
    "resources/views/public/resources",
    "resources/views/public/templates"
];

$bladeFiles = [];
foreach ($scopeDirs as $dir) {
    if (is_dir($dir)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($it as $f) {
            if ($f->isFile() && str_ends_with($f->getFilename(), '.blade.php')) {
                $bladeFiles[] = str_replace('\\', '/', $f->getPathname());
            }
        }
    }
}

echo "\nTotal Blade files in scope: " . count($bladeFiles) . "\n";

$missingKeys = [];
$totalCalls = 0;
$usedKeys = [];

foreach ($bladeFiles as $bf) {
    $content = file_get_contents($bf);
    // match __('key') or @lang('key') or trans('key')
    if (preg_match_all("/(?:__|@lang|trans)\(\s*['\"]([^'\"]+)['\"]/u", $content, $matches)) {
        foreach ($matches[1] as $k) {
            $totalCalls++;
            $usedKeys[$k] = ($usedKeys[$k] ?? 0) + 1;
            // Check if it starts with any of our files
            $parts = explode('.', $k, 2);
            $langFile = $parts[0] . '.php';
            if (in_array($langFile, $files)) {
                if (!isset($allLangKeys[$k])) {
                    $missingKeys[$bf][] = $k;
                }
            }
        }
    }
}

echo "Total translation helper calls in scope views: $totalCalls\n";
if (!empty($missingKeys)) {
    echo "MISSING TRANSLATION KEYS CALLED IN VIEWS:\n";
    foreach ($missingKeys as $view => $mks) {
        echo "In $view:\n";
        foreach (array_unique($mks) as $mk) {
            echo "  - $mk\n";
        }
    }
} else {
    echo "No missing keys found for the surveyed lang namespaces!\n";
}
