<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'reviewer@cooca.id')->first();
$business = App\Models\Business::find($user->active_business_id);
\App\Support\Context::setBusiness($business);
\Illuminate\Support\Facades\Auth::login($user);
session(['active_business_id' => $business->id, 'auth_wa_otp_verified_user_id' => $user->id]);

$request = \Illuminate\Http\Request::create('/calculator', 'GET');
$request->setUserResolver(fn() => $user);
$response = $app->handle($request);

file_put_contents(__DIR__ . '/sample_calc.html', $response->getContent());
echo "HTML written to scripts/sample_calc.html (" . strlen($response->getContent()) . " bytes)\n";
