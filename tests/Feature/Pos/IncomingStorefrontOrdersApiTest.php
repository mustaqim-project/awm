<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class IncomingStorefrontOrdersApiTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $cashier;
    private User $waiter;
    private Location $location;
    private PosTable $table;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // 1. Business
        $this->business = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Resto Rasa Nusantara',
            'slug' => 'resto-rasa-nusantara',
            'is_active' => true,
        ]);

        // 2. Location
        $this->location = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Resto Cabang Dago',
            'is_primary' => true,
            'is_active' => true,
        ]);

        // 3. Table
        $this->table = PosTable::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'table_number' => 'Meja 05',
            'capacity' => 4,
            'status' => PosTable::STATUS_OCCUPIED,
            'is_active' => true,
        ]);

        // 4. Cashier User (Owner role) & Staff Waiter User
        $this->cashier = User::create([
            'name' => 'Dewi Kasir',
            'email' => 'dewi@rasanusantara.com',
            'password' => bcrypt('secret123'),
        ]);
        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->cashier->update(['active_business_id' => $this->business->id]);

        $this->waiter = User::create([
            'name' => 'Bambang Waiter',
            'email' => 'waiter@rasanusantara.com',
            'password' => bcrypt('secret123'),
        ]);
        $this->business->users()->attach($this->waiter->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $unit = \App\Models\Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pcs',
            'code' => 'pcs',
            'symbol' => 'pcs',
            'category' => 'quantity',
        ]);

        // 5. Product
        $this->product = Product::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'output_unit_id' => $unit->id,
            'name' => 'Paket Nasi Liwet Komplit',
            'sku' => 'NLW-01',
            'price' => 45000.0,
            'cost_price' => 20000.0,
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->getJson('/api/v1/pos/incoming-orders')->assertStatus(401);
        $this->postJson('/api/v1/pos/orders/dummy-id/accept')->assertStatus(401);
        $this->postJson('/api/v1/pos/orders/dummy-id/reject', ['reason' => 'Habis'])->assertStatus(401);
    }

    public function test_can_fetch_incoming_orders_both_storefront_and_qr_table(): void
    {
        Sanctum::actingAs($this->cashier);

        // 1. Create a QR Table Order
        $qrOrder = PosOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->waiter->id,
            'order_number' => 'POS-QR-20261102-001',
            'order_date' => now(),
            'order_source' => PosOrder::SOURCE_QR_TABLE,
            'pos_table_id' => $this->table->id,
            'table_or_reference' => 'Meja 05',
            'customer_name_guest' => 'Andi Tamu',
            'status' => PosOrder::STATUS_PENDING,
            'total_amount' => 45000.0,
            'paid_amount' => 0.0,
        ]);

        PosOrderItem::create([
            'id' => (string) Str::uuid(),
            'pos_order_id' => $qrOrder->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1.0,
            'unit_price' => 45000.0,
            'total_price' => 45000.0,
        ]);

        // 2. Create a Storefront Online Order
        $commerceOrder = CommerceOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'order_number' => 'ORD-ONL-20261102-005',
            'tracking_token' => (string) Str::uuid(),
            'customer_name' => 'Siti Rahma',
            'customer_phone' => '081398765432',
            'fulfillment_type' => CommerceOrder::FULFILLMENT_COURIER_MANUAL,
            'shipping_courier_name' => 'GoSend Instant',
            'shipping_address' => 'Jl. Merdeka No. 45, Bandung',
            'total_amount' => 90000.0,
            'payment_status' => CommerceOrder::PAYMENT_PAID,
            'status' => CommerceOrder::STATUS_PROCESSING,
        ]);

        CommerceOrderItem::create([
            'id' => (string) Str::uuid(),
            'commerce_order_id' => $commerceOrder->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 2.0,
            'price' => 45000.0,
            'subtotal' => 90000.0,
        ]);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->getJson('/api/v1/pos/incoming-orders');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'total_incoming' => 2,
                'qr_table_count' => 1,
                'storefront_count' => 1,
            ],
        ]);

        $json = $response->json();
        $this->assertCount(1, $json['qr_table_orders']);
        $this->assertEquals('POS-QR-20261102-001', $json['qr_table_orders'][0]['order_number']);
        $this->assertEquals('Meja 05', $json['qr_table_orders'][0]['table_number']);
        $this->assertCount(1, $json['storefront_orders']);
        $this->assertEquals('ORD-ONL-20261102-005', $json['storefront_orders'][0]['order_number']);
        $this->assertEquals('Siti Rahma', $json['storefront_orders'][0]['customer_name']);
    }

    public function test_can_fetch_via_commerce_orders_incoming_alias(): void
    {
        Sanctum::actingAs($this->cashier);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->getJson('/api/v1/commerce/orders/incoming');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_tenant_isolation_does_not_leak_other_business_orders(): void
    {
        Sanctum::actingAs($this->cashier);

        $otherBiz = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Resto Lain',
            'slug' => 'resto-lain',
            'is_active' => true,
        ]);

        $otherUser = User::create([
            'name' => 'Other Staff',
            'email' => 'otherstaff@resto.com',
            'password' => bcrypt('secret123'),
        ]);

        PosOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $otherBiz->id,
            'user_id' => $otherUser->id,
            'order_number' => 'POS-OTHER-99',
            'order_date' => now(),
            'order_source' => PosOrder::SOURCE_QR_TABLE,
            'status' => PosOrder::STATUS_PENDING,
            'total_amount' => 50000.0,
        ]);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->getJson('/api/v1/pos/incoming-orders');

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('summary.total_incoming'));
    }

    public function test_can_accept_qr_table_order(): void
    {
        Sanctum::actingAs($this->cashier);

        $qrOrder = PosOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->waiter->id,
            'order_number' => 'POS-QR-ACC-01',
            'order_date' => now(),
            'order_source' => PosOrder::SOURCE_QR_TABLE,
            'pos_table_id' => $this->table->id,
            'status' => PosOrder::STATUS_PENDING,
            'total_amount' => 45000.0,
        ]);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/pos/orders/{$qrOrder->id}/accept");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pesanan #POS-QR-ACC-01 diterima dan diteruskan ke dapur.',
        ]);

        $this->assertEquals(PosOrder::STATUS_CONFIRMED, $qrOrder->fresh()->status);
        $this->assertEquals($this->cashier->id, $qrOrder->fresh()->user_id);
    }

    public function test_can_reject_qr_table_order_with_reason(): void
    {
        Sanctum::actingAs($this->cashier);

        $qrOrder = PosOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->waiter->id,
            'order_number' => 'POS-QR-REJ-01',
            'order_date' => now(),
            'order_source' => PosOrder::SOURCE_QR_TABLE,
            'pos_table_id' => $this->table->id,
            'status' => PosOrder::STATUS_PENDING,
            'total_amount' => 45000.0,
            'paid_amount' => 0.0,
        ]);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/pos/orders/{$qrOrder->id}/reject", [
            'reason' => 'Bahan baku habis',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pesanan #POS-QR-REJ-01 ditolak.',
        ]);

        $fresh = $qrOrder->fresh();
        $this->assertEquals(PosOrder::STATUS_REJECTED, $fresh->status);
        $this->assertEquals('Bahan baku habis', $fresh->rejection_reason);
        $this->assertEquals($this->cashier->id, $fresh->rejected_by);
    }

    public function test_reject_fails_without_mandatory_reason(): void
    {
        Sanctum::actingAs($this->cashier);

        $qrOrder = PosOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->waiter->id,
            'order_number' => 'POS-QR-REJ-02',
            'order_date' => now(),
            'order_source' => PosOrder::SOURCE_QR_TABLE,
            'status' => PosOrder::STATUS_PENDING,
            'total_amount' => 45000.0,
        ]);

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/pos/orders/{$qrOrder->id}/reject", [
            'reason' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reason']);
    }

    public function test_cannot_accept_or_reject_order_of_another_business(): void
    {
        Sanctum::actingAs($this->cashier);

        $otherBiz = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Resto Asing',
            'slug' => 'resto-asing',
            'is_active' => true,
        ]);

        $otherStaff = User::create([
            'name' => 'Other Staff 2',
            'email' => 'other2@resto.com',
            'password' => bcrypt('secret123'),
        ]);

        $otherOrder = PosOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $otherBiz->id,
            'user_id' => $otherStaff->id,
            'order_number' => 'POS-OTHER-002',
            'order_date' => now(),
            'order_source' => PosOrder::SOURCE_QR_TABLE,
            'status' => PosOrder::STATUS_PENDING,
            'total_amount' => 50000.0,
        ]);

        $resAccept = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/pos/orders/{$otherOrder->id}/accept");
        $resAccept->assertStatus(404);

        $resReject = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/pos/orders/{$otherOrder->id}/reject", [
            'reason' => 'Tolak',
        ]);
        $resReject->assertStatus(404);
    }
}
