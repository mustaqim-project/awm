<?php

putenv('APP_ENV=testing');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');

require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run migrations in memory
\Illuminate\Support\Facades\Artisan::call('migrate');

$httpKernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::create('/sitemap', 'GET');
$response = $httpKernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() === 500) {
    if (isset($response->exception)) {
        echo "Exception: " . get_class($response->exception) . "\n";
        echo "Message: " . $response->exception->getMessage() . "\n";
        echo "File: " . $response->exception->getFile() . ":" . $response->exception->getLine() . "\n";
    } else {
        echo substr($response->getContent(), 0, 1000);
    }
} else {
    echo "OK! Response size: " . strlen($response->getContent()) . "\n";
}
