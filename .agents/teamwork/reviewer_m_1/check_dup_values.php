<?php
$files = ['lang/id/billing.php', 'lang/en/billing.php', 'lang/id/social_media.php', 'lang/en/social_media.php'];

foreach ($files as $file) {
    echo "=== $file ===\n";
    $lines = file($file);
    // Find duplicate keys
    $keys = [];
    foreach ($lines as $i => $line) {
        if (preg_match('/^\s*\'([^\']+)\'\s*=>\s*(.*?),?$/', $line, $m)) {
            $k = $m[1];
            $v = trim($m[2]);
            if (isset($keys[$k])) {
                echo "Key '$k':\n  Line " . ($keys[$k]['line'] + 1) . ": " . $keys[$k]['val'] . "\n  Line " . ($i + 1) . ": " . $v . "\n";
            } else {
                $keys[$k] = ['line' => $i, 'val' => $v];
            }
        }
    }
}
