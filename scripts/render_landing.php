<?php

declare(strict_types=1);

putenv('APP_ENV=testing');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
putenv('CACHE_STORE=array');
putenv('SESSION_DRIVER=array');
$_ENV['APP_ENV'] = 'testing';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';
$_ENV['CACHE_STORE'] = 'array';
$_ENV['SESSION_DRIVER'] = 'array';

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run migrations on in-memory sqlite if needed
try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
} catch (\Throwable $e) {
    // Ignore migration errors if any
}

app('translator')->addLines(['storefront.tracking' => 'Lacak Pesanan'], 'id');
app('translator')->addLines(['storefront.tracking' => 'Track Order'], 'en');

$html = view('landing')->render();
file_put_contents(__DIR__ . '/rendered_landing.html', $html);
echo "Rendered " . strlen($html) . " bytes to rendered_landing.html\n";
