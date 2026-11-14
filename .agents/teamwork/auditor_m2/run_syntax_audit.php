<?php

declare(strict_types=1);

$files = [
    'resources/views/public/business_landing.blade.php',
    'resources/views/layouts/public_marketing.blade.php',
    'resources/views/public/about.blade.php',
    'resources/views/public/privacy.blade.php',
    'resources/views/public/terms.blade.php',
    'resources/views/public/pricing.blade.php',
    'resources/views/public/demo.blade.php',
    'resources/views/public/security.blade.php',
    'resources/views/public/sitemap.blade.php',
    'resources/views/public/support.blade.php',
    'resources/views/landing.blade.php',
    'resources/views/public/landing_commercial/kasir_android.blade.php',
    'resources/views/public/landing_commercial/kasir_offline.blade.php',
    'resources/views/public/landing_commercial/kasir_gratis.blade.php',
    'resources/views/public/discovery/index.blade.php',
    'lang/id/public_discovery.php',
    'resources/views/public/solutions/show.blade.php',
    'resources/views/public/solutions/fnb.blade.php',
    'resources/views/public/solutions/laundry.blade.php',
    'resources/views/public/solutions/manufacturing.blade.php',
    'resources/views/public/solutions/retail.blade.php',
    'resources/views/public/solutions/services.blade.php',
    'resources/views/public/solutions/workshop.blade.php',
    'bootstrap/app.php'
];

$errors = 0;
foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "MISSING: $file\n";
        $errors++;
        continue;
    }
    $output = [];
    $returnVar = 0;
    exec('php -l ' . escapeshellarg($file), $output, $returnVar);
    if ($returnVar !== 0) {
        echo "SYNTAX ERROR in $file: " . implode(' ', $output) . "\n";
        $errors++;
    } else {
        echo "PASS: $file\n";
    }
}

echo "Total files checked: " . count($files) . ", Errors: $errors\n";
exit($errors > 0 ? 1 : 0);
