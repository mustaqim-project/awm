<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Domain\Inventory\StockService;
use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class StockTransferDispatchAndReceiveTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private Business $otherBusiness;
    private User $ownerUser;
    private Location $sourceWarehouse;
    private Location $destinationOutlet;
    private Product $product;
    private Unit $unit;
    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->stockService = new StockService();

        // 1. Setup Tenant Business
        $this->business = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'PT Mitra Distribusi Kopi',
            'slug' => 'mitra-kopi',
            'is_active' => true,
            'allow_negative_stock' => false,
        ]);

        $this->otherBusiness = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Bisnis Sebelah Mandiri',
            'slug' => 'bisnis-sebelah',
            'is_active' => true,
        ]);

        // 2. Setup Source and Destination Locations
        $this->sourceWarehouse = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Gudang Pusat Logistik',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->destinationOutlet = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Outlet Cabang Dago Bandung',
            'is_primary' => false,
            'is_active' => true,
        ]);

        // 3. Setup Authenticated User
        $this->ownerUser = User::create([
            'name' => 'Admin Logistik Anton',
            'email' => 'anton@mitrakopi.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business->users()->attach($this->ownerUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->ownerUser->update(['active_business_id' => $this->business->id]);

        // 4. Setup Master Product & Unit
        $this->unit = Unit::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Kilogram',
            'code' => 'kg',
            'category' => Unit::CATEGORY_WEIGHT,
            'is_base' => true,
        ]);

        $this->product = Product::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi Arabika Gayo 1kg',
            'code' => 'COF-GAYO-1KG',
            'barcode' => '8998877665544',
            'unit_id' => $this->unit->id,
            'base_cost' => 125000.0,
            'price' => 175000.0,
            'is_active' => true,
        ]);

        // Seed initial stock of 100 kg at source location
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->sourceWarehouse->id,
            productId: $this->product->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 100.0,
            unitCost: 125000.0,
            notes: 'Stok awal gudang'
        );
    }

    public function test_get_transfers_with_filters(): void
    {
        Sanctum::actingAs($this->ownerUser);

        // Create 2 transfers: 1 in_transit, 1 pending
        $trf1 = StockTransfer::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'source_location_id' => $this->sourceWarehouse->id,
            'destination_location_id' => $this->destinationOutlet->id,
            'transfer_number' => 'TRF-20261102-001',
            'transfer_date' => '2026-11-02',
            'status' => StockTransfer::STATUS_IN_TRANSIT,
            'sent_at' => now(),
            'created_by' => $this->ownerUser->id,
        ]);

        StockTransferItem::create([
            'id' => (string) Str::uuid(),
            'stock_transfer_id' => $trf1->id,
            'product_id' => $this->product->id,
            'quantity' => 10.0,
            'unit_cost' => 125000.0,
            'total_cost' => 1250000.0,
        ]);

        $trf2 = StockTransfer::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'source_location_id' => $this->sourceWarehouse->id,
            'destination_location_id' => $this->destinationOutlet->id,
            'transfer_number' => 'TRF-20261102-002',
            'transfer_date' => '2026-11-02',
            'status' => StockTransfer::STATUS_PENDING,
            'created_by' => $this->ownerUser->id,
        ]);

        // 1. Filter by status = in_transit
        $responseInTransit = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->getJson('/api/v1/inventory/transfers?status=in_transit');

        $responseInTransit->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('transfers.0.id', $trf1->id)
            ->assertJsonPath('transfers.0.status', 'in_transit');

        // 2. Filter by type = inbound at destination outlet
        $responseInbound = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->getJson("/api/v1/inventory/transfers?type=inbound&location_id={$this->destinationOutlet->id}");

        $responseInbound->assertOk()
            ->assertJsonPath('pagination.total', 2);
    }

    public function test_2_step_transfer_lifecycle_dispatch_and_receive(): void
    {
        Sanctum::actingAs($this->ownerUser);

        // Step 1: Dispatch new stock transfer of 25 kg from source to destination
        $dispatchPayload = [
            'source_location_id' => $this->sourceWarehouse->id,
            'destination_location_id' => $this->destinationOutlet->id,
            'transfer_date' => '2026-11-02',
            'notes' => 'Pengiriman reguler armada box',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 25.0,
                ],
            ],
        ];

        $dispatchResponse = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/inventory/transfers/dispatch', $dispatchPayload);

        $dispatchResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('transfer.status', StockTransfer::STATUS_IN_TRANSIT);

        $transferId = $dispatchResponse->json('transfer.id');
        $transfer = StockTransfer::findOrFail($transferId);

        $this->assertEquals(StockTransfer::STATUS_IN_TRANSIT, $transfer->status);
        $this->assertNotNull($transfer->sent_at);

        // Verify source stock was immediately deducted (100 - 25 = 75 kg)
        $sourceStock = InventoryStock::where('business_id', $this->business->id)
            ->where('location_id', $this->sourceWarehouse->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertEquals(75.0, (float) $sourceStock->quantity);

        // Verify destination stock is NOT yet added (remains 0)
        $destStockBefore = InventoryStock::where('business_id', $this->business->id)
            ->where('location_id', $this->destinationOutlet->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertTrue($destStockBefore === null || (float) $destStockBefore->quantity === 0.0);

        // Verify TYPE_TRANSFER_OUT movement recorded
        $outMovement = StockMovement::where('business_id', $this->business->id)
            ->where('location_id', $this->sourceWarehouse->id)
            ->where('product_id', $this->product->id)
            ->where('movement_type', StockMovement::TYPE_TRANSFER_OUT)
            ->first();

        $this->assertNotNull($outMovement);
        $this->assertEquals(-25.0, (float) $outMovement->quantity_change);

        // Step 2: Receive transfer at destination outlet
        $receiveResponse = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/inventory/transfers/{$transfer->id}/receive");

        $receiveResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('transfer.status', StockTransfer::STATUS_RECEIVED);

        $transfer->refresh();
        $this->assertEquals(StockTransfer::STATUS_RECEIVED, $transfer->status);
        $this->assertNotNull($transfer->received_at);
        $this->assertEquals($this->ownerUser->id, $transfer->received_by);

        // Verify destination stock is now +25 kg
        $destStockAfter = InventoryStock::where('business_id', $this->business->id)
            ->where('location_id', $this->destinationOutlet->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertNotNull($destStockAfter);
        $this->assertEquals(25.0, (float) $destStockAfter->quantity);

        // Verify source stock is NOT double-deducted (still exactly 75 kg)
        $sourceStock->refresh();
        $this->assertEquals(75.0, (float) $sourceStock->quantity);

        // Verify TYPE_TRANSFER_IN movement recorded
        $inMovement = StockMovement::where('business_id', $this->business->id)
            ->where('location_id', $this->destinationOutlet->id)
            ->where('product_id', $this->product->id)
            ->where('movement_type', StockMovement::TYPE_TRANSFER_IN)
            ->first();

        $this->assertNotNull($inMovement);
        $this->assertEquals(25.0, (float) $inMovement->quantity_change);
    }

    public function test_receive_is_idempotent_and_does_not_duplicate_stock(): void
    {
        Sanctum::actingAs($this->ownerUser);

        // Create and dispatch transfer
        $dispatchResponse = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/inventory/transfers/dispatch', [
            'source_location_id' => $this->sourceWarehouse->id,
            'destination_location_id' => $this->destinationOutlet->id,
            'transfer_date' => '2026-11-02',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 15.0,
                ],
            ],
        ]);

        $transferId = $dispatchResponse->json('transfer.id');

        // First receive
        $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->postJson("/api/v1/inventory/transfers/{$transferId}/receive")
            ->assertOk();

        $destStock = InventoryStock::where('business_id', $this->business->id)
            ->where('location_id', $this->destinationOutlet->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(15.0, (float) $destStock->quantity);

        $movementCountBefore = StockMovement::where('reference_id', $transferId)->count();

        // Second receive (should be idempotent)
        $secondResponse = $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->postJson("/api/v1/inventory/transfers/{$transferId}/receive")
            ->assertOk();

        $destStock->refresh();
        // Quantity must NOT change to 30! Must remain 15.
        $this->assertEquals(15.0, (float) $destStock->quantity);

        $movementCountAfter = StockMovement::where('reference_id', $transferId)->count();
        $this->assertEquals($movementCountBefore, $movementCountAfter);
    }

    public function test_dispatch_existing_pending_transfer_by_id(): void
    {
        Sanctum::actingAs($this->ownerUser);

        // Create pending transfer via store
        $storeResponse = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/inventory/transfers', [
            'source_location_id' => $this->sourceWarehouse->id,
            'destination_location_id' => $this->destinationOutlet->id,
            'transfer_date' => '2026-11-02',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 20.0,
                ],
            ],
        ]);

        $storeResponse->assertCreated()
            ->assertJsonPath('transfer.status', StockTransfer::STATUS_PENDING);

        $transferId = $storeResponse->json('transfer.id');

        // Now dispatch using transfer_id
        $dispatchResponse = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/inventory/transfers/dispatch', [
            'transfer_id' => $transferId,
        ]);

        $dispatchResponse->assertCreated()
            ->assertJsonPath('transfer.status', StockTransfer::STATUS_IN_TRANSIT);

        $transfer = StockTransfer::findOrFail($transferId);
        $this->assertEquals(StockTransfer::STATUS_IN_TRANSIT, $transfer->status);
    }

    public function test_tenant_isolation_cannot_dispatch_or_receive_other_business_transfers(): void
    {
        Sanctum::actingAs($this->ownerUser);

        // Setup other business location & transfer
        $otherLocation = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->otherBusiness->id,
            'name' => 'Lokasi Bisnis Lain',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $otherTransfer = StockTransfer::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->otherBusiness->id,
            'source_location_id' => $otherLocation->id,
            'destination_location_id' => $otherLocation->id,
            'transfer_number' => 'TRF-OTHER-001',
            'transfer_date' => '2026-11-02',
            'status' => StockTransfer::STATUS_PENDING,
        ]);

        // 1. Dispatch other business transfer should 404
        $dispatchResponse = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson('/api/v1/inventory/transfers/dispatch', [
            'transfer_id' => $otherTransfer->id,
        ]);
        $dispatchResponse->assertNotFound();

        // 2. Receive other business transfer should 404
        $receiveResponse = $this->withHeaders([
            'X-Business-Id' => $this->business->id,
        ])->postJson("/api/v1/inventory/transfers/{$otherTransfer->id}/receive");
        $receiveResponse->assertNotFound();
    }
}
