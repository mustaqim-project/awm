<?php

$dir = __DIR__ . '/../lang';
$files = [];

function scan_dir_recursive($path, &$files) {
    foreach (scandir($path) as $item) {
        if ($item === '.' || $item === '..') continue;
        $full = $path . '/' . $item;
        if (is_dir($full)) {
            scan_dir_recursive($full, $files);
        } elseif (substr($item, -4) === '.php') {
            $files[] = $full;
        }
    }
}

scan_dir_recursive($dir, $files);

$foundTotal = 0;
foreach ($files as $file) {
    $lines = file($file);
    $keys = [];
    $duplicates = [];
    
    // Track depth to handle nested arrays
    $depth = 0;
    $path = [];
    
    foreach ($lines as $num => $line) {
        $lineNum = $num + 1;
        
        // Count open/close brackets
        $trimmed = trim($line);
        if (preg_match('/^\s*[\x27\x22]([a-zA-Z0-9_\-]+)[\x27\x22]\s*=>\s*\[/', $line, $m)) {
            $key = $m[1];
            $currentPath = implode('.', array_merge($path, [$key]));
            if (isset($keys[$currentPath])) {
                $duplicates[] = "Duplicate key '$currentPath' at line $lineNum (previous at line {$keys[$currentPath]})";
            } else {
                $keys[$currentPath] = $lineNum;
            }
            $path[] = $key;
            continue;
        }
        
        if (preg_match('/^\s*\]/', $line)) {
            array_pop($path);
            continue;
        }
        
        if (preg_match('/^\s*[\x27\x22]([a-zA-Z0-9_\-]+)[\x27\x22]\s*=>/', $line, $m)) {
            $key = $m[1];
            $currentPath = implode('.', array_merge($path, [$key]));
            if (isset($keys[$currentPath])) {
                $duplicates[] = "Duplicate key '$currentPath' at line $lineNum (previous at line {$keys[$currentPath]})";
            } else {
                $keys[$currentPath] = $lineNum;
            }
        }
    }
    
    if (!empty($duplicates)) {
        $relPath = str_replace(realpath(__DIR__ . '/..') . '/', '', realpath($file));
        echo "\n=== $relPath ===\n";
        foreach ($duplicates as $dup) {
            echo "  $dup\n";
            $foundTotal++;
        }
    }
}

echo "\nTotal duplicates found: $foundTotal\n";
