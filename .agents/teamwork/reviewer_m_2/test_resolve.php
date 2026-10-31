<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

app()->setLocale('id');
echo "ID: " . __('storefront.reservations.status_confirmed') . "\n";
echo "ID with backoffice: " . __('storefront.backoffice.reservations.status_confirmed') . "\n";

app()->setLocale('en');
echo "EN: " . __('storefront.reservations.status_confirmed') . "\n";
echo "EN with backoffice: " . __('storefront.backoffice.reservations.status_confirmed') . "\n";
