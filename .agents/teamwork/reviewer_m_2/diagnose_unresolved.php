<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$base_dir = dirname(__DIR__, 3);
$view_dirs = [
    $base_dir . '/resources/views/app',
    $base_dir . '/resources/views/admin'
];

$lang_files = glob($base_dir . '/lang/id/*.php');
$all_keys = [];

function flatten_keys($arr, $prefix = '') {
    $keys = [];
    foreach ($arr as $k => $v) {
        $p = $prefix === '' ? (string)$k : "$prefix.$k";
        if (is_array($v)) {
            $keys = array_merge($keys, flatten_keys($v, $p));
        } else {
            $keys[$p] = $v;
        }
    }
    return $keys;
}

$raw_dictionaries = [];
foreach ($lang_files as $lf) {
    $domain = basename($lf, '.php');
    $arr = require $lf;
    $raw_dictionaries[$domain] = $arr;
    if (is_array($arr)) {
        $flat = flatten_keys($arr);
        foreach ($flat as $sub => $val) {
            $all_keys["$domain.$sub"] = $val;
        }
    }
}

$unresolved = [];

foreach ($view_dirs as $vdir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($vdir));
    foreach ($it as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $path = $file->getPathname();
            $rel = str_replace($base_dir . DIRECTORY_SEPARATOR, '', $path);
            $content = file_get_contents($path);

            if (preg_match_all('/(?:__|@lang)\(\s*[\'"]([a-zA-Z0-9_\-\.\/]+)[\'"]\s*[\),]/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[1] as $idx => $match) {
                    $ref_key = $match[0];
                    $offset = $match[1];
                    if (!str_contains($ref_key, '.')) {
                        continue;
                    }
                    if (!isset($all_keys[$ref_key])) {
                        $line = substr_count(substr($content, 0, $offset), "\n") + 1;
                        $unresolved[$ref_key][] = "$rel:$line";
                    }
                }
            }
        }
    }
}

echo "=== UNRESOLVED KEYS DETAILED AUDIT (" . count($unresolved) . " keys) ===\n\n";

foreach ($unresolved as $key => $locs) {
    // Find possible locations in raw_dictionaries
    $parts = explode('.', $key);
    $domain = $parts[0];
    $leaf = end($parts);
    
    $candidates = [];
    if (isset($raw_dictionaries[$domain]) && is_array($raw_dictionaries[$domain])) {
        $flat_dom = flatten_keys($raw_dictionaries[$domain]);
        foreach ($flat_dom as $k => $v) {
            if ($k === $leaf || str_ends_with($k, '.' . $leaf) || str_contains($k, $leaf)) {
                $candidates[] = "$domain.$k";
            }
        }
    }
    
    echo "KEY: $key\n";
    echo "  Locations: " . implode(', ', array_unique($locs)) . "\n";
    if (!empty($candidates)) {
        echo "  Possible intended path in $domain.php: " . implode(', ', array_slice($candidates, 0, 3)) . "\n";
    } else {
        echo "  MISSING ENTIRELY from $domain.php\n";
    }
    echo "\n";
}
