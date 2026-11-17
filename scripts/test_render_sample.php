<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'reviewer@cooca.id')->first();
if (!$user) {
    echo "Reviewer user not found!\n";
    exit(1);
}

$business = App\Models\Business::find($user->active_business_id);
if (!$business) {
    $business = $user->businesses()->first();
}

\App\Support\Context::setBusiness($business);
\Illuminate\Support\Facades\Auth::login($user);
session([
    'active_business_id' => $business->id,
    'auth_wa_otp_verified_user_id' => $user->id,
]);

$testRoutes = [
    '/pos' => 'pos_terminal',
    '/pos/shifts' => 'pos_shifts',
    '/calculator' => 'calculator',
    '/products' => 'products',
    '/approvals' => 'approvals_inbox',
    '/settings/audit-logs' => 'audit_logs',
    '/finance/cash-bank' => 'cash_bank',
    '/hrm' => 'hrm_dashboard'
];

foreach ($testRoutes as $uri => $name) {
    $request = \Illuminate\Http\Request::create($uri, 'GET');
    $request->setUserResolver(fn() => $user);
    $response = $app->handle($request);
    $status = $response->getStatusCode();
    $length = strlen($response->getContent());
    echo "Route {$uri}: status {$status}, length {$length} bytes\n";
}
