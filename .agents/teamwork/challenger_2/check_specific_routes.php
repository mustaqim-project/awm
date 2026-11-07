<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$targetRoutes = [
    'analytics.index',
    'approvals.inbox',
    'approvals.history',
    'approval-rules.index',
    'billing.limits',
    'billing.checkout',
    'billing.history',
    'docs.index'
];

$router = app('router');
$routesByName = $router->getRoutes()->getRoutesByName();

echo str_pad("Route Name", 25) . str_pad("Method", 10) . str_pad("URI", 35) . "Action / Controller\n";
echo str_repeat("-", 100) . "\n";

foreach ($targetRoutes as $name) {
    if (isset($routesByName[$name])) {
        $r = $routesByName[$name];
        $methods = implode('|', array_diff($r->methods(), ['HEAD']));
        $uri = $r->uri();
        $action = $r->getActionName();
        echo str_pad($name, 25) . str_pad($methods, 10) . str_pad($uri, 35) . $action . "\n";
    } else {
        echo str_pad($name, 25) . "NOT FOUND\n";
    }
}
