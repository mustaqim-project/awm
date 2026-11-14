<?php

require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

$request = \Illuminate\Http\Request::create('/sitemap', 'GET');
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() === 500) {
    if (isset($response->exception)) {
        echo "Exception: " . get_class($response->exception) . "\n";
        echo "Message: " . $response->exception->getMessage() . "\n";
        echo "Trace:\n" . $response->exception->getTraceAsString() . "\n";
    } else {
        echo substr($response->getContent(), 0, 2000);
    }
} else {
    echo "Success! Length: " . strlen($response->getContent()) . "\n";
}
