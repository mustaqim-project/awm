<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'reviewer@cooca.id')->first();
$business = App\Models\Business::find($user->active_business_id);
\App\Support\Context::setBusiness($business);

if (App\Models\PosOrder::where('business_id', $business->id)->count() === 0) {
    $product = App\Models\Product::where('business_id', $business->id)->first();
    $customer = App\Models\Customer::where('business_id', $business->id)->first();
    $order = App\Models\PosOrder::create([
        'business_id' => $business->id,
        'user_id' => $user->id,
        'customer_id' => $customer ? $customer->id : null,
        'order_number' => 'ORD-202611-0001',
        'order_date' => now(),
        'status' => 'completed',
        'total_amount' => 125000,
        'subtotal' => 112613,
        'tax_amount' => 12387,
        'payment_method' => 'qris',
        'paid_at' => now(),
        'order_type' => 'dine_in',
        'table_number' => 'Meja 05',
    ]);
    if ($product) {
        App\Models\PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => $product->selling_price ?: 50000,
            'subtotal' => ($product->selling_price ?: 50000) * 2,
        ]);
    }
    echo "Created sample PosOrder: " . $order->id . "\n";
} else {
    echo "PosOrder already exists: " . App\Models\PosOrder::where('business_id', $business->id)->first()->id . "\n";
}
