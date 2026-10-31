<?php
error_reporting(E_ALL);

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

echo "Total distinct unresolved keys: " . count($unresolved) . "\n\n";
foreach ($unresolved as $key => $locs) {
    echo "$key (" . count($locs) . " occurrences):\n";
    foreach (array_unique($locs) as $l) {
        echo "  - $l\n";
    }
}
