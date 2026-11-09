<?php

require __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST 2: Empirical Blade Compilation Harness ===\n";

$targetViews = [
    'resources/views/app/invoices/index.blade.php',
    'resources/views/app/invoices/create.blade.php',
    'resources/views/app/invoices/edit.blade.php',
    'resources/views/app/invoices/show.blade.php',
    'resources/views/app/invoices/print.blade.php',
    'resources/views/app/labor-machines/index.blade.php',
];

$blade = $app['blade.compiler'];
$tempDir = __DIR__ . '/compiled_views';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}

$allPassed = true;

foreach ($targetViews as $viewRelPath) {
    $fullPath = realpath(__DIR__ . '/../../../' . $viewRelPath);
    if (!$fullPath || !file_exists($fullPath)) {
        echo "[FAIL] File not found: {$viewRelPath}\n";
        $allPassed = false;
        continue;
    }

    echo "Compiling {$viewRelPath}...\n";

    try {
        $source = file_get_contents($fullPath);
        $compiled = $blade->compileString($source);

        $compiledFile = $tempDir . '/' . md5($viewRelPath) . '.php';
        file_put_contents($compiledFile, $compiled);

        // Run php -l on compiled PHP output
        $cmd = 'php -l ' . escapeshellarg($compiledFile) . ' 2>&1';
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode === 0) {
            echo "[PASS] Compiled & linted successfully: {$viewRelPath}\n";
        } else {
            echo "[FAIL] Syntax error in compiled Blade: {$viewRelPath}\n";
            echo implode("\n", $output) . "\n";
            $allPassed = false;
        }
    } catch (\Throwable $e) {
        echo "[FAIL] Exception during compilation of {$viewRelPath}: " . $e->getMessage() . "\n";
        $allPassed = false;
    }
}

// Clean up tempDir
array_map('unlink', glob("{$tempDir}/*.*"));
rmdir($tempDir);

if ($allPassed) {
    echo "\n>>> ALL TARGETED BLADE VIEWS COMPILED WITH ZERO ERRORS! <<<\n";
    exit(0);
} else {
    echo "\n>>> BLADE COMPILATION FAILED! <<<\n";
    exit(1);
}
