<?php
$dirs = ['lang/id', 'lang/en'];

$suspicious = [];
foreach ($dirs as $dir) {
    foreach (glob("$dir/*.php") as $f) {
        $lines = file($f);
        foreach ($lines as $i => $line) {
            $num = $i + 1;
            // Check for JS operators or keywords inside translation values
            if (preg_match('/\'[^\']+\'\s*=>\s*\'(.*)\',?$/', trim($line), $m)) {
                $val = $m[1];
                if (preg_match('/(===|!==|\$event|@click|x-data|x-text|x-show|\bconsole\.log\b|\bwindow\.|animate-pulse|\{\{\s*\$)/', $val)) {
                    $suspicious[] = [
                        'file' => $f,
                        'line' => $num,
                        'content' => trim($line)
                    ];
                }
            }
        }
    }
}

echo "Suspicious translation values found: " . count($suspicious) . "\n";
foreach ($suspicious as $s) {
    echo "{$s['file']}:{$s['line']} => {$s['content']}\n";
}
