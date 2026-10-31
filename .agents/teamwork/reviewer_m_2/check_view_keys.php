<?php
error_reporting(E_ALL);

$base_dir = dirname(__DIR__, 3);
$view_dirs = [
    $base_dir . '/resources/views/app',
    $base_dir . '/resources/views/admin'
];

// 1. Load all language keys into a lookup set
$lang_files = glob($base_dir . '/lang/id/*.php');
$all_keys = [];

function flatten_keys($arr, $prefix = '') {
    $keys = [];
    foreach ($arr as $k => $v) {
        $p = $prefix === '' ? (string)$k : "$prefix.$k";
        if (is_array($v)) {
            $keys = array_merge($keys, flatten_keys($v, $p));
        } else {
            $keys[$p] = true;
        }
    }
    return $keys;
}

foreach ($lang_files as $lf) {
    $domain = basename($lf, '.php');
    $arr = require $lf;
    if (is_array($arr)) {
        $flat = flatten_keys($arr);
        foreach (array_keys($flat) as $sub) {
            $all_keys["$domain.$sub"] = true;
        }
    }
}

echo "Total dictionary keys indexed: " . count($all_keys) . "\n";

// 2. Scan views for i18n key references
$unresolved = [];
$total_refs = 0;
$syntax_issues = [];

foreach ($view_dirs as $vdir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($vdir));
    foreach ($it as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $path = $file->getPathname();
            $rel = str_replace($base_dir . DIRECTORY_SEPARATOR, '', $path);
            $content = file_get_contents($path);

            // Check nested Blade braces {{ ... {{ ... }} ... }}
            if (preg_match('/\{\{[^}]*\{\{/', $content, $m)) {
                $syntax_issues[] = ['file' => $rel, 'type' => 'nested_braces', 'snippet' => substr($m[0], 0, 80)];
            }

            // Find all __('...') or @lang('...')
            if (preg_match_all('/(?:__|@lang)\(\s*[\'"]([a-zA-Z0-9_\-\.\/]+)[\'"]\s*[\),]/', $content, $matches)) {
                foreach ($matches[1] as $ref_key) {
                    $total_refs++;
                    // Ignore dynamic expressions or keys without domain dot
                    if (!str_contains($ref_key, '.')) {
                        continue;
                    }
                    if (!isset($all_keys[$ref_key])) {
                        // Could be a root namespace or dynamic key
                        $unresolved[$ref_key][] = $rel;
                    }
                }
            }
        }
    }
}

echo "Total i18n references scanned: $total_refs\n";
echo "Syntax issues detected: " . count($syntax_issues) . "\n";
echo "Unresolved keys count: " . count($unresolved) . "\n";

if (!empty($syntax_issues)) {
    print_r($syntax_issues);
}

if (!empty($unresolved)) {
    echo "Sample unresolved keys:\n";
    $i = 0;
    foreach ($unresolved as $k => $files) {
        echo "  - $k in " . implode(', ', array_unique($files)) . "\n";
        if (++$i >= 20) break;
    }
}
