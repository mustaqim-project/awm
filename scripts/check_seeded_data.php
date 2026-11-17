<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'reviewer@cooca.id')->first();
$business = App\Models\Business::find($user->active_business_id);
\App\Support\Context::setBusiness($business);

echo "Business: " . $business->name . " (ID: " . $business->id . ")\n";

$models = [
    'Product' => App\Models\Product::class,
    'PosOrder' => class_exists('App\Models\PosOrder') ? App\Models\PosOrder::class : null,
    'Invoice' => class_exists('App\Models\Invoice') ? App\Models\Invoice::class : null,
    'SalesOrder' => class_exists('App\Models\SalesOrder') ? App\Models\SalesOrder::class : null,
    'Quotation' => class_exists('App\Models\Quotation') ? App\Models\Quotation::class : null,
    'PurchaseOrder' => class_exists('App\Models\PurchaseOrder') ? App\Models\PurchaseOrder::class : null,
    'Supplier' => class_exists('App\Models\Supplier') ? App\Models\Supplier::class : null,
    'Customer' => class_exists('App\Models\Customer') ? App\Models\Customer::class : null,
    'Material' => class_exists('App\Models\Material') ? App\Models\Material::class : null,
    'Warehouse' => class_exists('App\Models\Warehouse') ? App\Models\Warehouse::class : null,
    'Employee' => class_exists('App\Models\Employee') ? App\Models\Employee::class : null,
];

foreach ($models as $name => $cls) {
    if ($cls) {
        $count = $cls::count();
        $first = $cls::first();
        $id = $first ? $first->id : 'none';
        echo "$name: $count (first: $id)\n";
    } else {
        echo "$name: class not found\n";
    }
}
