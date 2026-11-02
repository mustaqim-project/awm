<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\Business;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class GoodsReceiptAndPoReceiveTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private Business $otherBusiness;
    private User $ownerUser;
    private Location $warehouseLocation;
    private Supplier $supplier;
    private Unit $unit;
    private Product $product1;
    private Product $product2;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // 1. Setup Tenant Business & Secondary Business
        $this->business = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'PT Sumber Rezeki Logistik',
            'slug' => 'sumber-rezeki',
            'is_active' => true,
        ]);

        $this->otherBusiness = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Toko Sebelah Abadi',
            'slug' => 'toko-sebelah',
            'is_active' => true,
        ]);

        // 2. Setup Warehouse Location
        $this->warehouseLocation = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Gudang Pusat Cikarang',
            'is_primary' => true,
            'is_active' => true,
        ]);

        // 3. Setup Authenticated Owner User
        $this->ownerUser = User::create([
            'name' => 'Kepala Gudang Joko',
            'email' => 'joko@sumberrezeki.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business->users()->attach($this->ownerUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->ownerUser->update(['active_business_id' => $this->business->id]);

        // 4. Setup Master Data (Supplier, Unit, Products)
        $this->supplier = Supplier::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'PT Indofood CBP Makmur',
            'code' => 'SUP-INDOFOOD',
            'phone' => '081299887766',
            'email' => 'sales@indofood.test',
            'contact_person' => 'Pak Hendra',
            'address' => 'Kawasan Industri MM2100 Blok C',
        ]);

        $this->unit = Unit::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Karton',
            'code' => 'ctn',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_base' => true,
        ]);

        $this->product1 = Product::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Susu Steril Tujuh Kurma 189ml',
            'code' => 'PRD-KURMA-01',
            'barcode' => '8991002003001',
            'unit_id' => $this->unit->id,
            'base_cost' => 8500.0,
            'price' => 11000.0,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Biskuit Gandum Sehat 250g',
            'code' => 'PRD-BISKUIT-02',
            'barcode' => '8991002003002',
            'unit_id' => $this->unit->id,
            'base_cost' => 12000.0,
            'price' => 15500.0,
            'is_active' => true,
        ]);
    }

    public function test_get_pending_purchase_orders_returns_confirmed_pos_with_accurate_quantities(): void
    {
        Sanctum::actingAs($this->ownerUser);

        // Create confirmed PO with 2 items
        $po = PurchaseOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-202611-0001',
            'reference_number' => 'REF-SUPP-888',
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'order_date' => '2026-11-01',
            'expected_delivery_date' => '2026-11-05',
            'created_by' => $this->ownerUser->id,
        ]);

        $poItem1 = PurchaseOrderItem::create([
            'id' => (string) Str::uuid(),
            'purchase_order_id' => $po->id,
            'product_id' => $this->product1->id,
            'item_name' => $this->product1->name,
            'sku' => $this->product1->code,
            'quantity' => 50.0,
            'unit_id' => $this->unit->id,
            'unit_price' => 8500.0,
        ]);

        $poItem2 = PurchaseOrderItem::create([
            'id' => (string) Str::uuid(),
            'purchase_order_id' => $po->id,
            'product_id' => $this->product2->id,
            'item_name' => $this->product2->name,
            'sku' => $this->product2->code,
            'quantity' => 20.0,
            'unit_id' => $this->unit->id,
            'unit_price' => 12000.0,
        ]);

        // Also create a completed PO (should NOT be returned in pending)
        $completedPo = PurchaseOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-202611-0099',
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'status' => PurchaseOrder::STATUS_COMPLETED,
            'order_date' => '2026-10-20',
        ]);

        // Call API
        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->getJson('/api/v1/inventory/purchase-orders/pending');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.id', $po->id)
            ->assertJsonPath('data.0.po_number', 'PO-202611-0001')
            ->assertJsonPath('data.0.supplier.name', 'PT Indofood CBP Makmur')
            ->assertJsonPath('data.0.items_count', 2)
            ->assertJsonPath('data.0.total_ordered_qty', 70)
            ->assertJsonPath('data.0.total_received_qty', 0)
            ->assertJsonPath('data.0.total_remaining_qty', 70);
    }

    public function test_get_single_purchase_order_detail(): void
    {
        Sanctum::actingAs($this->ownerUser);

        $po = PurchaseOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-202611-0002',
            'reference_number' => 'REF-DO-123',
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'order_date' => '2026-11-02',
            'notes' => 'Periksa kondisi segel karton.',
            'created_by' => $this->ownerUser->id,
        ]);

        PurchaseOrderItem::create([
            'id' => (string) Str::uuid(),
            'purchase_order_id' => $po->id,
            'product_id' => $this->product1->id,
            'item_name' => $this->product1->name,
            'quantity' => 30.0,
            'unit_id' => $this->unit->id,
            'unit_price' => 8500.0,
        ]);

        // Test fetching by UUID
        $responseUuid = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->getJson("/api/v1/inventory/purchase-orders/{$po->id}");

        $responseUuid->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $po->id)
            ->assertJsonPath('data.po_number', 'PO-202611-0002')
            ->assertJsonPath('data.items.0.barcode', '8991002003001');

        // Test fetching by PO Number string
        $responseNumber = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->getJson("/api/v1/inventory/purchase-orders/{$po->po_number}");

        $responseNumber->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $po->id);
    }

    public function test_post_goods_receipt_atomic_transaction_and_batch_tracking(): void
    {
        Sanctum::actingAs($this->ownerUser);

        $po = PurchaseOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-202611-0003',
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'order_date' => '2026-11-02',
            'created_by' => $this->ownerUser->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'id' => (string) Str::uuid(),
            'purchase_order_id' => $po->id,
            'product_id' => $this->product1->id,
            'item_name' => $this->product1->name,
            'sku' => $this->product1->code,
            'quantity' => 20.0,
            'unit_id' => $this->unit->id,
            'unit_price' => 8500.0,
        ]);

        // Submit physical Goods Receipt (partial: 12 karton, 1 karton damaged, with Lot/Batch and Expiry)
        $payload = [
            'purchase_order_id' => $po->id,
            'location_id' => $this->warehouseLocation->id,
            'receipt_date' => '2026-11-02',
            'delivery_order_number' => 'DO-INDOFOOD-991',
            'notes' => 'Penerimaan batch susu kurma',
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $this->product1->id,
                    'quantity' => 12.0,
                    'damaged_quantity' => 1.0,
                    'unit_cost' => 8500.0,
                    'batch_number' => 'BATCH-KURMA-202611-A',
                    'expiry_date' => '2027-11-01',
                    'notes' => '1 karton robek bungkus',
                ],
            ],
        ];

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/inventory/goods-receipts', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.goods_receipt.status', 'completed')
            ->assertJsonPath('data.goods_receipt.po_status', PurchaseOrder::STATUS_PARTIALLY_INVOICED)
            ->assertJsonPath('data.goods_receipt.items.0.quantity', 12.0)
            ->assertJsonPath('data.goods_receipt.items.0.batch_number', 'BATCH-KURMA-202611-A')
            ->assertJsonPath('data.goods_receipt.items.0.expiry_date', '2027-11-01');

        // 1. Verify PO Status updated to partially_invoiced
        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_PARTIALLY_INVOICED, $po->status);

        // 2. Verify Stock Movement recorded
        $movement = StockMovement::where('business_id', $this->business->id)
            ->where('product_id', $this->product1->id)
            ->where('movement_type', StockMovement::TYPE_GOODS_RECEIPT)
            ->latest()
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(12.0, (float) $movement->quantity_change);
        $this->assertEquals(8500.0, (float) $movement->unit_cost);
        $this->assertEquals('BATCH-KURMA-202611-A', $movement->batch_number);

        // 3. Verify InventoryStock updated
        $stock = InventoryStock::where('business_id', $this->business->id)
            ->where('location_id', $this->warehouseLocation->id)
            ->where('product_id', $this->product1->id)
            ->first();

        $this->assertNotNull($stock);
        $this->assertEquals(12.0, (float) $stock->quantity);
        $this->assertEquals(8500.0, (float) $stock->avg_purchase_cost);

        // 4. Verify ProductBatch was created with real state
        $batch = ProductBatch::where('business_id', $this->business->id)
            ->where('product_id', $this->product1->id)
            ->where('batch_number', 'BATCH-KURMA-202611-A')
            ->first();

        $this->assertNotNull($batch);
        $this->assertEquals(12.0, (float) $batch->quantity);
        $this->assertEquals(8500.0, (float) $batch->unit_cost);
        $this->assertEquals('2027-11-01', $batch->expiry_date->format('Y-m-d'));
        $this->assertTrue($batch->is_active);

        // 5. Second Goods Receipt with remaining 8 karton and same batch increments batch quantity and completes PO
        $secondPayload = [
            'purchase_order_id' => $po->id,
            'location_id' => $this->warehouseLocation->id,
            'receipt_date' => '2026-11-02',
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 8.0,
                    'unit_cost' => 8500.0,
                    'batch_number' => 'BATCH-KURMA-202611-A',
                    'expiry_date' => '2027-11-01',
                ],
            ],
        ];

        $secondResponse = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/inventory/goods-receipts', $secondPayload);

        $secondResponse->assertCreated()
            ->assertJsonPath('data.goods_receipt.po_status', PurchaseOrder::STATUS_COMPLETED);

        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_COMPLETED, $po->status);

        // Batch quantity should now be 12 + 8 = 20
        $batch->refresh();
        $this->assertEquals(20.0, (float) $batch->quantity);

        // Stock quantity should now be 20
        $stock->refresh();
        $this->assertEquals(20.0, (float) $stock->quantity);
    }

    public function test_goods_receipt_rejects_over_receiving(): void
    {
        Sanctum::actingAs($this->ownerUser);

        $po = PurchaseOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-202611-0004',
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'order_date' => '2026-11-02',
        ]);

        PurchaseOrderItem::create([
            'id' => (string) Str::uuid(),
            'purchase_order_id' => $po->id,
            'product_id' => $this->product1->id,
            'item_name' => $this->product1->name,
            'quantity' => 10.0,
            'unit_id' => $this->unit->id,
            'unit_price' => 8500.0,
        ]);

        // Attempt receiving 15 items when only 10 were ordered
        $payload = [
            'purchase_order_id' => $po->id,
            'location_id' => $this->warehouseLocation->id,
            'receipt_date' => '2026-11-02',
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 15.0,
                    'unit_cost' => 8500.0,
                ],
            ],
        ];

        $response = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/inventory/goods-receipts', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_multi_tenant_isolation_cannot_access_or_receive_other_business_po(): void
    {
        Sanctum::actingAs($this->ownerUser);

        // PO belonging to otherBusiness
        $otherPo = PurchaseOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->otherBusiness->id,
            'po_number' => 'PO-OTHER-999',
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'order_date' => '2026-11-02',
        ]);

        // 1. Cannot fetch detail
        $getResponse = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->getJson("/api/v1/inventory/purchase-orders/{$otherPo->id}");

        $getResponse->assertNotFound();

        // 2. Cannot receive
        $postResponse = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/inventory/goods-receipts', [
            'purchase_order_id' => $otherPo->id,
            'location_id' => $this->warehouseLocation->id,
            'receipt_date' => '2026-11-02',
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 5.0,
                    'unit_cost' => 8500.0,
                ],
            ],
        ]);

        $postResponse->assertNotFound();
    }
}
