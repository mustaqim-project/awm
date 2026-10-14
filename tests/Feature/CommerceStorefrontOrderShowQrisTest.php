<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CommerceStorefrontOrderShowQrisTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Location $location;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(DefaultUnitSeeder::class);

        $this->user = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner@coocabiz.com',
            'phone' => '081122334455',
            'password' => bcrypt('password123'),
        ]);
        $this->user->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Multi Sektor Usaha Mandiri',
            'slug' => 'multi-sektor',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Workshop / Outlet Utama',
            'is_primary' => true,
        ]);

        $pcs = Unit::where('code', 'pcs')->first() ?? Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pieces',
            'code' => 'pcs',
            'is_standard' => true,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $pcs->id,
            'name' => 'Layanan / Produk Standar 20 Sektor',
            'sku' => 'SEKTOR-001',
            'type' => 'goods',
            'base_cost' => 50000,
            'selling_price' => 100000,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'reserved_quantity' => 2,
        ]);
    }

    public function test_commerce_order_status_labels_are_neutral_and_universal(): void
    {
        $order = new CommerceOrder(['status' => CommerceOrder::STATUS_PROCESSING]);
        $this->assertSame('Diproses (Sedang Dikerjakan)', $order->status_label);

        $order->status = CommerceOrder::STATUS_READY;
        $this->assertSame('Siap (Siap Diambil / Kirim)', $order->status_label);

        $order->status = CommerceOrder::STATUS_PENDING_PAYMENT;
        $this->assertSame('Menunggu Pembayaran', $order->status_label);

        $order->status = CommerceOrder::STATUS_PAID;
        $this->assertSame('Lunas', $order->status_label);

        $order->status = CommerceOrder::STATUS_COMPLETED;
        $this->assertSame('Selesai', $order->status_label);

        $order->status = CommerceOrder::STATUS_CANCELLED;
        $this->assertSame('Dibatalkan', $order->status_label);
    }

    public function test_merchant_can_view_order_show_page_with_qris_and_neutral_terminology(): void
    {
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-20261014-0001',
            'tracking_token' => Str::random(64),
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'fulfillment_type' => 'pickup',
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'payment_status' => CommerceOrder::PAYMENT_UNPAID,
            'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'gateway_reference' => 'DEV-T1234567890',
            'gateway_pay_code' => 'QRIS-MERCHANT-9988',
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'subtotal' => 200000,
            'total_amount' => 200000,
            'gateway_fee' => 1400,
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price' => 100000,
            'quantity' => 2,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->user->id,
            ])
            ->get("/storefront/orders/{$order->id}");

        $response->assertOk();
        $response->assertSee('Pembayaran QRIS');
        $response->assertSee('QRIS Standar Nasional');
        $response->assertSee('Diproses (Sedang Dikerjakan / Disiapkan)');
        $response->assertSee('Siap (Siap Diambil / Siap Kirim)');
        $response->assertSee('Konfirmasi Pembayaran QRIS Manual');
        $response->assertDontSee('Rekening Standar Toko');
        $response->assertDontSee('Sedang Disiapkan/Dimasak');
    }

    public function test_merchant_can_manually_confirm_qris_payment_and_stock_is_committed(): void
    {
        $order = CommerceOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-20261014-0002',
            'tracking_token' => Str::random(64),
            'order_type' => CommerceOrder::TYPE_DIRECT_CHECKOUT,
            'fulfillment_type' => 'pickup',
            'status' => CommerceOrder::STATUS_PENDING_PAYMENT,
            'payment_status' => CommerceOrder::PAYMENT_UNPAID,
            'payment_gateway' => CommerceOrder::GATEWAY_TRIPAY,
            'payment_channel' => 'QRIS',
            'customer_name' => 'Siti Rahma',
            'customer_phone' => '081399887766',
            'subtotal' => 200000,
            'total_amount' => 200000,
        ]);

        CommerceOrderItem::create([
            'commerce_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price' => 100000,
            'quantity' => 2,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession([
                'active_business_id' => $this->business->id,
                'auth_wa_otp_verified_user_id' => $this->user->id,
            ])
            ->post("/storefront/orders/{$order->id}/verify-payment");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(CommerceOrder::STATUS_PAID, $order->status);
        $this->assertEquals(CommerceOrder::PAYMENT_PAID, $order->payment_status);
        $this->assertNotNull($order->paid_at);

        // Verify stock has been committed
        $stock = InventoryStock::where('product_id', $this->product->id)->first();
        $this->assertEquals(8, (float) $stock->quantity);
        $this->assertEquals(0, (float) $stock->reserved_quantity);
    }
}
