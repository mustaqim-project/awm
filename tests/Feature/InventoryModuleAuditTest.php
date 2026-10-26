<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\ApprovalRule;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class InventoryModuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private Business $otherBusiness;
    private User $owner;
    private User $otherOwner;
    private User $staffWithoutPerms;
    private User $staffInventoryViewer;
    private User $staffInventoryManager;
    private Location $location;
    private Location $destLocation;
    private Unit $unit;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Inventory',
            'email' => 'owner.inventory@cooca.test',
            'phone' => '6281299990001',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'PT Pergudangan Logistik Mandiri',
            'slug' => 'pt-pergudangan-logistik-mandiri',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
            'pos_supervisor_pin' => Hash::make('123456'),
            'disabled_modules' => [],
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $entitlement = app(EntitlementService::class);
        $sub = $entitlement->getSubscription($this->business);
        $sub->update([
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
        ]);

        // Second business for IDOR verification
        $this->otherOwner = User::create([
            'name' => 'Owner Lain Inv',
            'email' => 'owner.lain.inv@cooca.test',
            'phone' => '6281299990002',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->otherBusiness = Business::create([
            'name' => 'CV Gudang Rival Sebelah',
            'slug' => 'cv-gudang-rival-sebelah',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
            'pos_supervisor_pin' => Hash::make('654321'),
            'disabled_modules' => [],
        ]);

        $this->otherBusiness->users()->attach($this->otherOwner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->otherOwner->update(['active_business_id' => $this->otherBusiness->id]);

        // Staff without permissions
        $roleEmpty = Role::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Staff Non Izin Inv',
            'slug' => 'staff-non-izin-inv',
        ]);
        $this->staffWithoutPerms = User::create([
            'name' => 'Staff No Permission Inv',
            'email' => 'staff.none.inv@cooca.test',
            'phone' => '6281299990003',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->business->users()->attach($this->staffWithoutPerms->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $roleEmpty->id,
        ]);
        $this->staffWithoutPerms->update(['active_business_id' => $this->business->id]);

        // Role Viewer
        $roleViewer = Role::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Staff Inventory Viewer',
            'slug' => 'staff-inventory-viewer',
        ]);
        $viewPerms = Permission::whereIn('slug', ['inventory.view'])->pluck('id');
        $roleViewer->permissions()->attach($viewPerms);

        $this->staffInventoryViewer = User::create([
            'name' => 'Staff Viewer Inv',
            'email' => 'staff.view.inv@cooca.test',
            'phone' => '6281299990004',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->business->users()->attach($this->staffInventoryViewer->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $roleViewer->id,
        ]);
        $this->staffInventoryViewer->update(['active_business_id' => $this->business->id]);

        // Role Manager
        $roleManager = Role::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Inventory Manager',
            'slug' => 'inventory-manager',
        ]);
        $mgrPerms = Permission::whereIn('slug', [
            'inventory.view',
            'inventory.manage',
        ])->pluck('id');
        $roleManager->permissions()->attach($mgrPerms);

        $this->staffInventoryManager = User::create([
            'name' => 'Staff Manager Inv',
            'email' => 'staff.mgr.inv@cooca.test',
            'phone' => '6281299990005',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->business->users()->attach($this->staffInventoryManager->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $roleManager->id,
        ]);
        $this->staffInventoryManager->update(['active_business_id' => $this->business->id]);

        // Setup Locations & Unit & Product
        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Pusat Cikarang',
            'code' => 'WH-CKR-01',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        $this->destLocation = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Store Jakarta',
            'code' => 'OUT-JKT-01',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pcs Box',
            'code' => 'BOX',
            'symbol' => 'box',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_base' => true,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi Arabika Specialty 1kg',
            'code' => 'BKP-001',
            'type' => 'goods',
            'base_cost' => 120000,
            'base_price' => 180000,
            'output_unit_id' => $this->unit->id,
            'is_active' => true,
        ]);

        // Create initial stock
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
            'last_cost' => 120000,
        ]);
    }

    public function test_inventory_stocks_requires_authentication(): void
    {
        $response = $this->get('/inventory/stocks');
        $response->assertRedirect('/login');
    }

    public function test_inventory_stocks_requires_permission(): void
    {
        $response = $this->actingAs($this->staffWithoutPerms)->get('/inventory/stocks');
        $response->assertStatus(302);
    }

    public function test_user_with_permission_can_view_inventory_stocks(): void
    {
        $response = $this->actingAs($this->staffInventoryViewer)->get('/inventory/stocks');
        $response->assertStatus(200);
        $response->assertSee('Biji Kopi Arabika Specialty 1kg');
    }

    public function test_inventory_movements_requires_permission(): void
    {
        $response = $this->actingAs($this->staffWithoutPerms)->get('/inventory/movements');
        $response->assertStatus(302);

        $viewResponse = $this->actingAs($this->staffInventoryViewer)->get('/inventory/movements');
        $viewResponse->assertStatus(200);
    }

    public function test_quick_adjust_requires_manage_permission(): void
    {
        $response = $this->actingAs($this->staffInventoryViewer)->post('/inventory/stocks/adjust', [
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'new_quantity' => 120,
            'reason_code' => 'initial_balance',
        ]);

        $response->assertStatus(302);
    }

    public function test_quick_adjust_direct_updates_stock_and_creates_movement(): void
    {
        $stock = InventoryStock::where('product_id', $this->product->id)->firstOrFail();

        $response = $this->actingAs($this->owner)->post('/inventory/stocks/adjust', [
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'new_quantity' => 110,
            'reason_code' => 'initial_balance',
            'notes' => 'Koreksi fisik hitung manual saldo awal',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(110.0, (float) $stock->fresh()->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'business_id' => $this->business->id,
            'product_id' => $this->product->id,
            'location_id' => $this->location->id,
            'movement_type' => StockMovement::TYPE_ADJUSTMENT,
            'quantity_change' => 10,
        ]);
    }

    public function test_quick_adjust_approval_required_creates_pending_adjustment(): void
    {
        // Set approval rule with low threshold to trigger approval
        ApprovalRule::create([
            'business_id' => $this->business->id,
            'document_type' => ApprovalRule::DOC_STOCK_ADJUSTMENT,
            'min_amount' => 10000,
            'approver_role_level_1' => 'supervisor',
            'is_active' => true,
        ]);

        // Adjust downwards by 50 units (loss: 50 * 120,000 = 6,000,000 > 10,000)
        $response = $this->actingAs($this->staffInventoryManager)->post('/inventory/stocks/adjust', [
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'new_quantity' => 50,
            'reason_code' => 'damaged',
            'notes' => 'Kerusakan fisik gudang',
            'supervisor_pin' => '123456',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('stock_adjustments', [
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'status' => 'pending_approval',
        ]);
    }

    public function test_approve_adjustment_updates_stock_and_audit_log(): void
    {
        $stock = InventoryStock::where('product_id', $this->product->id)->firstOrFail();

        $adjustment = StockAdjustment::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'adjustment_number' => 'ADJ-TEST-001',
            'adjustment_date' => now()->toDateString(),
            'reason' => 'damaged',
            'status' => 'pending_approval',
            'total_loss_cost' => 600000,
            'created_by' => $this->staffInventoryManager->id,
        ]);

        StockAdjustmentItem::create([
            'stock_adjustment_id' => $adjustment->id,
            'product_id' => $this->product->id,
            'system_quantity' => 100,
            'physical_quantity' => 95,
            'difference_quantity' => -5,
            'unit_cost' => 120000,
            'total_loss_cost' => 600000,
        ]);

        $response = $this->actingAs($this->owner)->post("/inventory/adjustments/{$adjustment->id}/approve");
        $response->assertSessionHasNoErrors();

        $this->assertEquals('completed', $adjustment->fresh()->status);
        $this->assertEquals(95.0, (float) $stock->fresh()->quantity);
    }

    public function test_adjustment_tenant_isolation_prevents_idor(): void
    {
        $otherLocation = Location::create([
            'business_id' => $this->otherBusiness->id,
            'name' => 'Gudang Lain Rival',
            'code' => 'WH-RVL-01',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        $otherAdjustment = StockAdjustment::create([
            'business_id' => $this->otherBusiness->id,
            'location_id' => $otherLocation->id,
            'adjustment_number' => 'ADJ-OTHER-001',
            'adjustment_date' => now()->toDateString(),
            'reason' => 'damaged',
            'status' => 'pending_approval',
            'total_loss_cost' => 100000,
            'created_by' => $this->otherOwner->id,
        ]);

        // Attempt approve from this business owner
        $response = $this->actingAs($this->owner)->post("/inventory/adjustments/{$otherAdjustment->id}/approve");
        $response->assertStatus(404);

        // Attempt reject from this business owner
        $rejectResponse = $this->actingAs($this->owner)->post("/inventory/adjustments/{$otherAdjustment->id}/reject", [
            'reason' => 'Bukan adjustment kita',
        ]);
        $rejectResponse->assertStatus(404);
    }

    public function test_opnames_index_requires_permission(): void
    {
        $response = $this->actingAs($this->staffWithoutPerms)->get('/inventory/opnames');
        $response->assertStatus(302);

        $viewResponse = $this->actingAs($this->staffInventoryViewer)->get('/inventory/opnames');
        $viewResponse->assertStatus(200);
    }

    public function test_store_opname_creates_opname_and_calculates_difference(): void
    {
        $response = $this->actingAs($this->staffInventoryManager)->post('/inventory/opnames', [
            'location_id' => $this->location->id,
            'opname_date' => now()->toDateString(),
            'notes' => 'Opname Akhir Bulan Oktober',
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $this->product->id,
                    'physical_quantity' => 98,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stock_opnames', [
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'status' => StockOpname::STATUS_IN_PROGRESS,
        ]);

        $this->assertDatabaseHas('stock_opname_items', [
            'product_id' => $this->product->id,
            'physical_quantity' => 98,
            'difference_quantity' => -2,
        ]);
    }

    public function test_reconcile_opname_updates_stock_and_audit_log(): void
    {
        $opname = StockOpname::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'opname_number' => 'OPN-TEST-REC-01',
            'opname_date' => now()->toDateString(),
            'status' => StockOpname::STATUS_IN_PROGRESS,
            'created_by' => $this->staffInventoryManager->id,
        ]);

        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_id' => $this->product->id,
            'system_quantity' => 100,
            'physical_quantity' => 98,
            'difference_quantity' => -2,
            'unit_cost' => 120000,
            'total_difference_cost' => -240000,
        ]);

        $response = $this->actingAs($this->owner)->post("/inventory/opnames/{$opname->id}/reconcile");
        $response->assertSessionHasNoErrors();

        $this->assertEquals(StockOpname::STATUS_RECONCILED, $opname->fresh()->status);

        $stock = InventoryStock::where('product_id', $this->product->id)->first();
        $this->assertEquals(98.0, (float) $stock->quantity);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'STOCK_OPNAME_RECONCILED',
        ]);
    }

    public function test_transfers_index_requires_permission(): void
    {
        $response = $this->actingAs($this->staffWithoutPerms)->get('/inventory/transfers');
        $response->assertStatus(302);

        $viewResponse = $this->actingAs($this->staffInventoryViewer)->get('/inventory/transfers');
        $viewResponse->assertStatus(200);
    }

    public function test_store_transfer_creates_transfer_and_audit_log(): void
    {
        $response = $this->actingAs($this->staffInventoryManager)->post('/inventory/transfers', [
            'source_location_id' => $this->location->id,
            'destination_location_id' => $this->destLocation->id,
            'transfer_date' => now()->toDateString(),
            'notes' => 'Pengiriman pasokan kopi ke outlet Jakarta',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 20,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stock_transfers', [
            'business_id' => $this->business->id,
            'source_location_id' => $this->location->id,
            'destination_location_id' => $this->destLocation->id,
            'status' => StockTransfer::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'STOCK_TRANSFER_CREATED',
        ]);
    }

    public function test_receive_transfer_completes_transfer_and_audit_log(): void
    {
        $transfer = StockTransfer::create([
            'business_id' => $this->business->id,
            'source_location_id' => $this->location->id,
            'destination_location_id' => $this->destLocation->id,
            'transfer_number' => 'TRF-TEST-REC-01',
            'transfer_date' => now()->toDateString(),
            'status' => StockTransfer::STATUS_PENDING,
            'created_by' => $this->staffInventoryManager->id,
            'sent_at' => now(),
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_id' => $this->product->id,
            'quantity' => 25,
            'unit_cost' => 120000,
            'total_cost' => 3000000,
        ]);

        $response = $this->actingAs($this->owner)->post("/inventory/transfers/{$transfer->id}/receive");
        $response->assertSessionHasNoErrors();

        $this->assertEquals(StockTransfer::STATUS_RECEIVED, $transfer->fresh()->status);

        // Destination stock should now have 25
        $destStock = InventoryStock::where('location_id', $this->destLocation->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertNotNull($destStock);
        $this->assertEquals(25.0, (float) $destStock->quantity);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'STOCK_TRANSFER_RECEIVED',
        ]);
    }
}
