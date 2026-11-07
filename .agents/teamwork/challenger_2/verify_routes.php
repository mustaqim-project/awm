<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$files = [
    'resources/views/app/analytics/index.blade.php',
    'resources/views/app/approvals/inbox.blade.php',
    'resources/views/app/approvals/history.blade.php',
    'resources/views/app/approvals/rules.blade.php',
    'resources/views/app/billing/limits.blade.php',
    'resources/views/app/docs/index.blade.php',
    'resources/views/app/docs/partials/sec_analytics.blade.php',
    'resources/views/app/docs/partials/sec_approvals.blade.php',
    'resources/views/app/docs/partials/sec_billing.blade.php'
];

$referencedRoutes = [];
foreach ($files as $file) {
    $fullPath = __DIR__ . '/../../../' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        if (preg_match_all("/route\(\s*['\"]([^'\"]+)['\"]/i", $content, $matches)) {
            foreach ($matches[1] as $r) {
                if (!isset($referencedRoutes[$r])) {
                    $referencedRoutes[$r] = [];
                }
                $referencedRoutes[$r][] = $file;
            }
        }
    }
}

echo "Total distinct referenced routes: " . count($referencedRoutes) . "\n";

$router = app('router');
$allNamedRoutes = array_keys($router->getRoutes()->getRoutesByName());

$missing = [];
$found = [];

foreach ($referencedRoutes as $routeName => $occurringFiles) {
    $hasRoute = Illuminate\Support\Facades\Route::has($routeName);
    if ($hasRoute) {
        $found[$routeName] = array_unique($occurringFiles);
    } else {
        $missing[$routeName] = array_unique($occurringFiles);
    }
}

echo "\n--- VERIFIED ROUTES (" . count($found) . ") ---\n";
foreach ($found as $r => $f) {
    echo "  [OK] $r (found in " . implode(', ', $f) . ")\n";
}

if (!empty($missing)) {
    echo "\n--- MISSING ROUTES (" . count($missing) . ") ---\n";
    foreach ($missing as $r => $f) {
        echo "  [FAIL] $r (missing in " . implode(', ', $f) . ")\n";
    }
    exit(1);
} else {
    echo "\nAll referenced routes resolve successfully in Route::has()!\n";
    exit(0);
}
