<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class PosWhatsAppReceiptApiTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $cashier;
    private Location $location;
    private Customer $customer;
    private PosOrder $order;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // 1. Business
        $this->business = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Kopi Senja Utama',
            'slug' => 'kopi-senja-utama',
            'is_active' => true,
        ]);

        // 2. Location
        $this->location = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Outlet Senja',
            'is_primary' => true,
            'is_active' => true,
        ]);

        // 3. User with Owner role
        $this->cashier = User::create([
            'name' => 'Rian Kasir',
            'email' => 'rian@kopisenja.com',
            'password' => bcrypt('secret123'),
        ]);
        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->cashier->update(['active_business_id' => $this->business->id]);

        // 4. Customer
        $this->customer = Customer::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Bambang Sudirman',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $unit = \App\Models\Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pcs',
            'code' => 'pcs',
            'symbol' => 'pcs',
            'category' => 'quantity',
        ]);

        // 5. Product & Completed PosOrder
        $product = Product::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'output_unit_id' => $unit->id,
            'name' => 'Kopi Tubruk Robusta',
            'sku' => 'KOP-TUB-01',
            'price' => 18000.0,
            'cost_price' => 8000.0,
            'is_active' => true,
        ]);

        $this->order = PosOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-20261102-009',
            'order_date' => now(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 36000.0,
            'total_amount' => 36000.0,
            'paid_amount' => 50000.0,
            'change_amount' => 14000.0,
        ]);

        PosOrderItem::create([
            'id' => (string) Str::uuid(),
            'pos_order_id' => $this->order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2.0,
            'unit_price' => 18000.0,
            'subtotal' => 36000.0,
            'total_price' => 36000.0,
        ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->postJson("/api/v1/pos/orders/{$this->order->id}/send-whatsapp-receipt");
        $response->assertStatus(401);
    }

    public function test_can_generate_and_dispatch_whatsapp_receipt_with_customer_phone(): void
    {
        Sanctum::actingAs($this->cashier);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/pos/orders/{$this->order->id}/send-whatsapp-receipt");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'phone' => '081234567890',
        ]);

        $json = $response->json();
        $this->assertNotEmpty($json['whatsapp_url']);
        $this->assertStringContainsString('https://wa.me/6281234567890', $json['whatsapp_url']);
        $this->assertStringContainsString('STRUK', rawurldecode($json['whatsapp_url']));
        $this->assertStringContainsString('Kopi Senja Utama', rawurldecode($json['whatsapp_url']));
    }

    public function test_can_specify_custom_phone_number(): void
    {
        Sanctum::actingAs($this->cashier);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/pos/orders/{$this->order->id}/send-whatsapp-receipt", [
            'phone' => '087811223344',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'phone' => '087811223344',
        ]);

        $json = $response->json();
        $this->assertStringContainsString('https://wa.me/6287811223344', $json['whatsapp_url']);
    }

    public function test_cannot_send_receipt_for_order_of_another_business(): void
    {
        Sanctum::actingAs($this->cashier);

        $otherBiz = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Bisnis Lain',
            'slug' => 'bisnis-lain',
            'is_active' => true,
        ]);

        $otherUser = User::create([
            'name' => 'User Lain',
            'email' => 'lain@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $otherOrder = PosOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $otherBiz->id,
            'user_id' => $otherUser->id,
            'order_number' => 'ORD-OTHER-001',
            'order_date' => now(),
            'status' => PosOrder::STATUS_COMPLETED,
            'total_amount' => 50000.0,
        ]);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/pos/orders/{$otherOrder->id}/send-whatsapp-receipt");

        $response->assertStatus(404);
    }
}
