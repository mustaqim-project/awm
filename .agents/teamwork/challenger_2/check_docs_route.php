<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'docs.index: ' . (Illuminate\Support\Facades\Route::has('docs.index') ? 'YES' : 'NO') . PHP_EOL;

$docsRoutes = [];
foreach (Illuminate\Support\Facades\Route::getRoutes() as $r) {
    if (strpos($r->uri(), 'docs') !== false || strpos($r->getName() ?? '', 'docs') !== false) {
        $docsRoutes[] = ($r->getName() ?? '[unnamed]') . ' -> ' . $r->uri();
    }
}
print_r($docsRoutes);
