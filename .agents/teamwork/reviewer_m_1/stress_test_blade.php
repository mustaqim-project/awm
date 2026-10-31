<?php
$root = dirname(dirname(dirname(__DIR__)));
$dirs = [$root . '/resources/views/app', $root . '/resources/views/admin'];

$nested_blade = [];
$alpine_syntax = [];
$total_files = 0;

foreach ($dirs as $dir) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($files as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $total_files++;
            $path = str_replace('\\', '/', $file->getPathname());
            $lines = file($path);
            
            foreach ($lines as $line_num => $line) {
                $num = $line_num + 1;
                
                // Check nested {{ ... {{ ... }} ... }}
                if (preg_match('/\{\{[^}]*\{\{/', $line)) {
                    $nested_blade[] = [
                        'file' => $path,
                        'line' => $num,
                        'snippet' => trim($line)
                    ];
                }
                
                // Check nested {!! ... {{ ... !!}
                if (preg_match('/\{!![^!]*\{\{/', $line)) {
                    $nested_blade[] = [
                        'file' => $path,
                        'line' => $num,
                        'snippet' => trim($line)
                    ];
                }

                // Check Alpine attribute with unescaped Blade like :placeholder="{{ __('...') }}"
                // When using Alpine :attr, it expects a JS expression, so raw {{ ... }} without quotes can break JS
                if (preg_match('/:[a-zA-Z0-9_\-]+\s*=\s*["\']\s*\{\{\s*__\(/', $line)) {
                    $alpine_syntax[] = [
                        'file' => $path,
                        'line' => $num,
                        'snippet' => trim($line)
                    ];
                }
            }
        }
    }
}

echo "Total Blade files scanned: $total_files\n";
echo "Nested Blade tags found: " . count($nested_blade) . "\n";
foreach ($nested_blade as $n) {
    echo "  - {$n['file']}:{$n['line']} => {$n['snippet']}\n";
}

echo "Potentially risky Alpine bindings found: " . count($alpine_syntax) . "\n";
foreach ($alpine_syntax as $a) {
    echo "  - {$a['file']}:{$a['line']} => {$a['snippet']}\n";
}
