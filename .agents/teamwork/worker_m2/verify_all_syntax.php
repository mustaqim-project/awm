<?php
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
    'lang/id/public_discovery.php',
];

$errors = 0;
foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "MISSING: $file\n";
        $errors++;
        continue;
    }
    exec("php -l " . escapeshellarg($file), $output, $code);
    if ($code !== 0) {
        echo "FAIL ($code): $file\n" . implode("\n", $output) . "\n";
        $errors++;
    } else {
        echo "PASS: $file\n";
    }
    $output = [];
}

echo "Total errors: $errors\n";
exit($errors > 0 ? 1 : 0);
