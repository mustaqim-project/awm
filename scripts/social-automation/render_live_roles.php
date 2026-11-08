<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'demo@cooca.id')->first();
if (!$user) {
    echo "No demo user found\n";
    exit(1);
}

auth()->login($user);
$business = $user->businesses()->first() ?? App\Models\Business::first();
if ($business) {
    session(['current_business_id' => $business->id]);
    App\Support\Context::setBusiness($business);
}

// Ensure session middleware is set up
$request = Illuminate\Http\Request::create('/roles', 'GET');
$request->setLaravelSession(app('session.store'));
app('session.store')->start();
app('session.store')->put('current_business_id', $business?->id);

$response = $app->handle($request);
echo "Status: " . $response->getStatusCode() . " Length: " . strlen($response->getContent()) . "\n";

$outPath = __DIR__ . '/../../public/admin/social-media-assets/live_roles_view.html';
file_put_contents($outPath, $response->getContent());
echo "Saved HTML to $outPath\n";
