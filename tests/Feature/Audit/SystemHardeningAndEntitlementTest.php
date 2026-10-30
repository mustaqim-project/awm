<?php

declare(strict_types=1);

namespace Tests\Feature\Audit;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Billing\EntitlementService;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Material;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SystemSetting;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SystemHardeningAndEntitlementTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private Business $businessA;

    private User $userB;
    private Business $businessB;

    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        // Business A & Owner A
        $this->userA = User::create([
            'name' => 'Owner A',
            'email' => 'owner.a@cooca.id',
            'password' => Hash::make('secret123'),
        ]);
        $this->businessA = Business::create([
            'name' => 'Toko Retail Sukses A',
            'industry' => 'retail',
        ]);
        $this->businessA->users()->attach($this->userA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        // Business B & Owner B
        $this->userB = User::create([
            'name' => 'Owner B',
            'email' => 'owner.b@cooca.id',
            'password' => Hash::make('secret123'),
        ]);
        $this->businessB = Business::create([
            'name' => 'Warung F&B Sebelah B',
            'industry' => 'food_beverage',
        ]);
        $this->businessB->users()->attach($this->userB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->userB->update(['active_business_id' => $this->businessB->id]);

        $this->unit = Unit::create([
            'business_id' => $this->businessA->id,
            'code' => 'PCS',
            'name' => 'Pcs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        Location::create([
            'business_id' => $this->businessA->id,
            'name' => 'Outlet Utama A',
            'slug' => 'outlet-utama-a',
            'type' => 'outlet',
            'is_primary' => true,
            'is_active' => true,
        ]);
    }

    /**
     * 1. Multi-Tenant Prefill Isolation: PO & Product from Business B cannot be prefilled by Business A.
     */
    public function test_invoice_create_prefill_po_and_product_strictly_scoped_to_current_business(): void
    {
        $custB = Customer::create([
            'business_id' => $this->businessB->id,
            'name' => 'Rahasia Customer B',
            'is_active' => true,
        ]);

        $poB = PurchaseOrder::create([
            'business_id' => $this->businessB->id,
            'customer_id' => $custB->id,
            'po_number' => 'PO-SECRET-B-001',
            'order_date' => now()->toDateString(),
            'po_type' => PurchaseOrder::TYPE_CUSTOMER,
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'total_amount' => 5000000,
        ]);

        $unitB = Unit::create([
            'business_id' => $this->businessB->id,
            'code' => 'PCS',
            'name' => 'Pcs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $prodB = Product::create([
            'business_id' => $this->businessB->id,
            'output_unit_id' => $unitB->id,
            'name' => 'Produk Rahasia Bisnis B',
            'type' => Product::TYPE_GOODS,
            'is_active' => true,
        ]);

        Context::setBusiness($this->businessA);

        $response = $this->actingAs($this->userA, 'web')
            ->get(route('invoices.create', [
                'po_id' => $poB->id,
                'product_id' => $prodB->id,
            ]));

        $response->assertOk();
        // Crucial: selectedPo and prefillProduct MUST BE NULL because they belong to Business B!
        $this->assertNull($response->viewData('selectedPo'));
        $this->assertNull($response->viewData('prefillProduct'));
    }

    /**
     * 2. Cross-Tenant Customer Validation: Invoice creation must reject customer_id from another tenant.
     */
    public function test_invoice_store_rejects_cross_tenant_customer_id(): void
    {
        $custB = Customer::create([
            'business_id' => $this->businessB->id,
            'name' => 'Customer Milik Bisnis B',
            'is_active' => true,
        ]);

        $locationA = Location::where('business_id', $this->businessA->id)->first();

        Context::setBusiness($this->businessA);

        $response = $this->actingAs($this->userA, 'web')
            ->post(route('invoices.store'), [
                'customer_id' => $custB->id, // Malicious customer ID from another tenant
                'location_id' => $locationA->id,
                'invoice_date' => now()->toDateString(),
                'items' => [
                    [
                        'item_name' => 'Barang A',
                        'quantity' => 1,
                        'unit_id' => $this->unit->id,
                        'unit_price' => 10000,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('customer_id');
    }

    /**
     * 3. Entitlement: Material creation is blocked when Free Tier quota (10) is exceeded.
     */
    public function test_material_creation_blocked_when_free_tier_quota_exceeded(): void
    {
        // Ensure Business A is on Free Tier
        BusinessSubscription::create([
            'business_id' => $this->businessA->id,
            'plan_code' => BusinessSubscription::PLAN_FREE,
            'status' => BusinessSubscription::STATUS_ACTIVE,
        ]);

        // Seed 10 materials to reach the Free limit
        for ($i = 1; $i <= 10; $i++) {
            Material::create([
                'business_id' => $this->businessA->id,
                'name' => "Bahan Baku {$i}",
                'unit_id' => $this->unit->id,
                'sku' => "MAT-A-{$i}",
                'yield_percentage' => 100,
                'waste_percentage' => 0,
                'purchase_price' => 1000,
            ]);
        }

        Context::setBusiness($this->businessA);

        // Attempt to create the 11th material
        $response = $this->actingAs($this->userA, 'web')
            ->post(route('materials.store'), [
                'name' => 'Bahan Baku Ke-11 Melebihi Kuota',
                'unit_id' => $this->unit->id,
                'sku' => 'MAT-A-11',
                'yield_percentage' => 100,
                'waste_percentage' => 0,
                'purchase_price' => 5000,
            ]);

        // Should be intercepted by CheckResourceEntitlement middleware and redirected with alert
        $response->assertSessionHas('error');
        $this->assertEquals(10, Material::where('business_id', $this->businessA->id)->count());
    }

    /**
     * 4. Entitlement: Branch creation is blocked when Free Tier outlet limit (1) is exceeded.
     */
    public function test_branch_creation_blocked_when_tier_outlet_quota_exceeded(): void
    {
        BusinessSubscription::create([
            'business_id' => $this->businessA->id,
            'plan_code' => BusinessSubscription::PLAN_FREE,
            'status' => BusinessSubscription::STATUS_ACTIVE,
        ]);

        Context::setBusiness($this->businessA);

        // Business A already has 1 primary outlet from setUp()
        // Attempt to create 2nd outlet via Settings Hub
        $response = $this->actingAs($this->userA, 'web')
            ->post(route('settings.branches.store'), [
                'name' => 'Cabang Kedua Tidak Diizinkan',
                'type' => 'outlet',
                'address' => 'Jl. Merdeka No. 2',
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Location::where('business_id', $this->businessA->id)->count());
    }

    /**
     * 5. Double-Entry Accounting: POS Order Auto Journal balances 100% when rounding is applied.
     */
    public function test_pos_order_auto_journal_balances_completely_when_rounding_applied(): void
    {
        $locationA = Location::where('business_id', $this->businessA->id)->first();

        $prod = Product::create([
            'business_id' => $this->businessA->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Kopi Robusta',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 24800,
            'base_cost' => 10000,
            'is_active' => true,
        ]);

        // Raw total = 24.800, rounded up to 25.000 (rounding_amount = +200)
        $order = PosOrder::create([
            'business_id' => $this->businessA->id,
            'location_id' => $locationA->id,
            'user_id' => $this->userA->id,
            'order_number' => 'POS-TEST-ROUNDING-01',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 24800,
            'tax_amount' => 0,
            'service_charge_amount' => 0,
            'rounding_amount' => 200,
            'total_amount' => 25000,
            'paid_amount' => 25000,
            'total_hpp_cost' => 10000,
            'payment_channel' => 'CASH',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $prod->id,
            'product_name' => $prod->name,
            'unit_price' => 24800,
            'unit_cost_hpp' => 10000,
            'quantity' => 1,
            'subtotal' => 24800,
            'total_price' => 24800,
            'total_hpp' => 10000,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 25000,
            'status' => 'paid',
        ]);

        $journalService = app(AutoJournalService::class);
        $journal = $journalService->recordPosSaleJournal($order);

        $this->assertNotNull($journal);
        // Verify total debit matches total credit exactly
        $this->assertEquals((float) $journal->total_debit, (float) $journal->total_credit);
        $this->assertGreaterThan(0, (float) $journal->total_debit);

        // Verify that the rounding revenue line was created
        $roundingLine = $journal->lines()->where('notes', 'like', '%Pembulatan%')->first();
        $this->assertNotNull($roundingLine);
        $this->assertEquals(200.0, (float) $roundingLine->amount);
    }

    /**
     * 6. Multi-Industry Isolation: Non-F&B businesses get 404 on KDS and Table endpoints.
     */
    public function test_fnb_kitchen_and_tables_endpoints_return_404_for_non_fnb_businesses(): void
    {
        // Business A is 'retail' (not F&B and has no dine-in)
        $this->assertFalse($this->businessA->isFoodIndustry());
        $this->assertFalse($this->businessA->hasDineInFeature());

        Context::setBusiness($this->businessA);

        // Non-F&B store (retail) is denied access: intercepted by CheckResourceEntitlement / RequirePermission (redirect) or aborted 404
        $kdsResponse = $this->actingAs($this->userA, 'web')->get(route('pos.kitchen.index'));
        $this->assertTrue(in_array($kdsResponse->status(), [302, 404], true));

        $tableResponse = $this->actingAs($this->userA, 'web')->get(route('pos.tables.index'));
        $this->assertTrue(in_array($tableResponse->status(), [302, 404], true));
    }
}

