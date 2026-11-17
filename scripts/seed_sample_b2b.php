<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'reviewer@cooca.id')->first();
$business = App\Models\Business::find($user->active_business_id);
\App\Support\Context::setBusiness($business);

// Check if Quotation exists
if (class_exists('App\Models\Quotation')) {
    if (App\Models\Quotation::where('business_id', $business->id)->count() === 0) {
        $customer = App\Models\Customer::where('business_id', $business->id)->first();
        $q = App\Models\Quotation::create([
            'business_id' => $business->id,
            'customer_id' => $customer ? $customer->id : null,
            'number' => 'SPH-202611-001',
            'quotation_number' => 'SPH-202611-001',
            'date' => now(),
            'quotation_date' => now(),
            'expiry_date' => now()->addDays(14),
            'status' => 'draft',
            'subtotal' => 15000000,
            'total_amount' => 16650000,
            'tax_amount' => 1650000,
            'notes' => 'Penawaran paket katering korporat',
        ]);
        echo "Created quotation: " . $q->id . "\n";
    } else {
        echo "Quotation exists: " . App\Models\Quotation::where('business_id', $business->id)->first()->id . "\n";
    }
}

// Check if SalesOrder exists
if (class_exists('App\Models\SalesOrder')) {
    if (App\Models\SalesOrder::where('business_id', $business->id)->count() === 0) {
        $customer = App\Models\Customer::where('business_id', $business->id)->first();
        $so = App\Models\SalesOrder::create([
            'business_id' => $business->id,
            'customer_id' => $customer ? $customer->id : null,
            'order_number' => 'SO-202611-001',
            'so_number' => 'SO-202611-001',
            'order_date' => now(),
            'delivery_date' => now()->addDays(7),
            'status' => 'confirmed',
            'subtotal' => 25000000,
            'total_amount' => 27750000,
            'tax_amount' => 2750000,
            'notes' => 'Pesanan katering korporat bulanan',
        ]);
        echo "Created sales order: " . $so->id . "\n";
    } else {
        echo "SalesOrder exists: " . App\Models\SalesOrder::where('business_id', $business->id)->first()->id . "\n";
    }
}
