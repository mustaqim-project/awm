<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$files = [
    'resources/views/public/business_landing.blade.php',
    'resources/views/landing.blade.php',
    'resources/views/public/about.blade.php',
    'resources/views/public/demo.blade.php',
    'resources/views/public/pricing.blade.php',
    'resources/views/public/privacy.blade.php',
    'resources/views/public/security.blade.php',
    'resources/views/public/sitemap.blade.php',
    'resources/views/public/support.blade.php',
    'resources/views/public/terms.blade.php',
    'resources/views/public/landing_commercial/kasir_android.blade.php',
    'resources/views/public/landing_commercial/kasir_gratis.blade.php',
    'resources/views/public/landing_commercial/kasir_offline.blade.php',
    'resources/views/public/discovery/index.blade.php',
    'resources/views/public/solutions/fnb.blade.php',
    'resources/views/public/solutions/laundry.blade.php',
    'resources/views/public/solutions/manufacturing.blade.php',
    'resources/views/public/solutions/retail.blade.php',
    'resources/views/public/solutions/services.blade.php',
    'resources/views/public/solutions/show.blade.php',
    'resources/views/public/solutions/workshop.blade.php',
    'resources/views/layouts/public_marketing.blade.php',
];

$compiler = app('blade.compiler');
foreach ($files as $file) {
    $compiled = $compiler->compileString(file_get_contents($file));
    $tmp = tempnam(sys_get_temp_dir(), 'blade_test_');
    file_put_contents($tmp, $compiled);
    exec("php -l " . escapeshellarg($tmp), $out, $code);
    unlink($tmp);
    if ($code !== 0) {
        echo "FAIL: $file\n" . implode("\n", $out) . "\n";
    } else {
        echo "OK: $file\n";
    }
    $out = [];
}
