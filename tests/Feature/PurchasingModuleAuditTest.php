<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\CashAccount;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Location;
use App\Models\Material;
use App\Models\Permission;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturn;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PurchasingModuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private Business $otherBusiness;
    private User $owner;
    private User $otherOwner;
    private User $staffWithoutPerms;
    private User $staffPurchasingViewer;
    private User $staffPurchasingManager;
    private Location $location;
    private Supplier $supplier;
    private Unit $unit;
    private Product $product;
    private CashAccount $cashAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Purchasing',
            'email' => 'owner.purchasing@cooca.test',
            'phone' => '6281288880001',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'PT Pengadaan Jaya Abadi',
            'slug' => 'pt-pengadaan-jaya-abadi',
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
            'name' => 'Owner Lain',
            'email' => 'owner.lain.po@cooca.test',
            'phone' => '6281288880002',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->otherBusiness = Business::create([
            'name' => 'CV Mitra Sejahtera',
            'slug' => 'cv-mitra-sejahtera',
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

        // Staff users
        $roleEmpty = Role::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Staff Non Izin',
            'slug' => 'staff-non-izin',
        ]);
        $this->staffWithoutPerms = User::create([
            'name' => 'Staff No Permission',
            'email' => 'staff.none.po@cooca.test',
            'phone' => '6281288880003',
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
            'name' => 'Staff Purchasing Viewer',
            'slug' => 'staff-purchasing-viewer',
        ]);
        $viewPerms = Permission::whereIn('slug', ['purchasing.view'])->pluck('id');
        $roleViewer->permissions()->attach($viewPerms);

        $this->staffPurchasingViewer = User::create([
            'name' => 'Staff Viewer PO',
            'email' => 'staff.view.po@cooca.test',
            'phone' => '6281288880004',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->business->users()->attach($this->staffPurchasingViewer->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $roleViewer->id,
        ]);
        $this->staffPurchasingViewer->update(['active_business_id' => $this->business->id]);

        // Role Manager (PO, Receiving, Bills, Returns)
        $roleManager = Role::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Purchasing Manager',
            'slug' => 'purchasing-manager',
        ]);
        $mgrPerms = Permission::whereIn('slug', [
            'purchasing.view',
            'purchasing.manage',
            'receiving.manage',
            'purchasing.bills',
            'purchase.returns',
        ])->pluck('id');
        $roleManager->permissions()->attach($mgrPerms);

        $this->staffPurchasingManager = User::create([
            'name' => 'Manager Purchasing',
            'email' => 'mgr.po@cooca.test',
            'phone' => '6281288880005',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->business->users()->attach($this->staffPurchasingManager->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $roleManager->id,
        ]);
        $this->staffPurchasingManager->update(['active_business_id' => $this->business->id]);

        // Master records
        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Pusat Jakarta',
            'code' => 'GDG-JKT',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'PT Pemasok Kopi Makmur',
            'contact_person' => 'Budi Santoso',
            'phone' => '08123456789',
        ]);

        $this->unit = Unit::create([
            'name' => 'Kilogram',
            'code' => 'KG',
            'category' => Unit::CATEGORY_WEIGHT,
            'is_base' => true,
            'business_id' => null,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi Arabika',
            'code' => 'KOP-ARA-01',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 120000,
            'base_cost' => 70000,
        ]);

        $this->cashAccount = CashAccount::create([
            'business_id' => $this->business->id,
            'name' => 'Kas Operasional Pembelian',
            'code' => 'KAS-OPS',
            'type' => 'cash',
            'is_active' => true,
        ]);
    }

    public function test_purchase_orders_index_requires_authentication(): void
    {
        $response = $this->get(route('purchase-orders.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_purchase_orders_index_requires_permission(): void
    {
        $response = $this->actingAs($this->staffWithoutPerms)->get(route('purchase-orders.index'));
        $response->assertRedirect(route('portal'));

        $jsonResponse = $this->actingAs($this->staffWithoutPerms)->getJson(route('purchase-orders.index'));
        $jsonResponse->assertStatus(403);
    }

    public function test_user_with_permission_can_view_purchase_orders_index(): void
    {
        PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_number' => 'PO-2026-0001',
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'subtotal' => 700000,
            'total_amount' => 700000,
        ]);

        $response = $this->actingAs($this->staffPurchasingViewer)->get(route('purchase-orders.index'));
        $response->assertStatus(200);
        $response->assertSee('PO-2026-0001');
    }

    public function test_purchase_order_store_creates_po_and_audit_log(): void
    {
        $payload = [
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'item_name' => $this->product->name,
                    'sku' => $this->product->code,
                    'quantity' => 10,
                    'unit_id' => $this->unit->id,
                    'unit_price' => 70000,
                ],
            ],
        ];

        $response = $this->actingAs($this->staffPurchasingManager)->post(route('purchase-orders.store'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_orders', [
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'total_amount' => 700000,
        ]);

        $po = PurchaseOrder::where('business_id', $this->business->id)->latest()->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'user_id' => $this->staffPurchasingManager->id,
            'auditable_type' => PurchaseOrder::class,
            'auditable_id' => $po->id,
            'action' => 'po.created',
        ]);
    }

    public function test_purchase_order_show_tenant_isolation_prevents_idor(): void
    {
        $otherSupplier = Supplier::create([
            'business_id' => $this->otherBusiness->id,
            'name' => 'Supplier Bisnis Lain',
        ]);

        $otherPo = PurchaseOrder::create([
            'business_id' => $this->otherBusiness->id,
            'po_number' => 'PO-LAIN-001',
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'supplier_id' => $otherSupplier->id,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($this->owner)->get(route('purchase-orders.show', $otherPo));
        $this->assertContains($response->status(), [403, 404]);
    }

    public function test_purchase_order_confirm_state_machine(): void
    {
        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_number' => 'PO-CONF-001',
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($this->owner)->post(route('purchase-orders.confirm', $po));
        $response->assertRedirect();

        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_CONFIRMED, $po->status);
    }

    public function test_goods_receipt_requires_receiving_permission(): void
    {
        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_number' => 'PO-RCV-001',
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($this->staffPurchasingViewer)->get(route('purchasing.receipts.create', $po));
        $response->assertRedirect(route('portal'));

        $jsonResponse = $this->actingAs($this->staffPurchasingViewer)->getJson(route('purchasing.receipts.create', $po));
        $jsonResponse->assertStatus(403);
    }

    public function test_goods_receipt_store_updates_stock_and_audit_log(): void
    {
        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_number' => 'PO-RCV-002',
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_CONFIRMED,
            'total_amount' => 700000,
        ]);

        PurchaseOrderItem::create([
            'business_id' => $this->business->id,
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'item_name' => $this->product->name,
            'sku' => $this->product->code,
            'quantity' => 10,
            'unit_id' => $this->unit->id,
            'unit_price' => 70000,
            'total_price' => 700000,
        ]);

        $payload = [
            'location_id' => $this->location->id,
            'receipt_number' => 'GR-2026-0001',
            'receipt_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'item_name' => $this->product->name,
                    'quantity' => 10,
                    'unit_cost' => 70000,
                ],
            ],
        ];

        $response = $this->actingAs($this->staffPurchasingManager)->post(route('purchasing.receipts.store', $po), $payload);
        $response->assertRedirect(route('purchase-orders.show', $po));

        $this->assertDatabaseHas('goods_receipts', [
            'business_id' => $this->business->id,
            'purchase_order_id' => $po->id,
            'receipt_number' => 'GR-2026-0001',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'user_id' => $this->staffPurchasingManager->id,
            'auditable_type' => GoodsReceipt::class,
            'action' => 'goods_receipt.created',
            'risk_level' => AuditLog::RISK_LOW,
        ]);
    }

    public function test_instant_stock_in_solo_mode_records_movement_and_cash(): void
    {
        $payload = [
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_cost' => 70000,
            'supplier_id' => $this->supplier->id,
            'cash_account_id' => $this->cashAccount->id,
            'notes' => 'Beli tunai di pasar induk',
        ];

        $response = $this->actingAs($this->owner)->post(route('purchasing.instant-stock-in'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('stock_movements', [
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id' => $this->product->id,
            'movement_type' => StockMovement::TYPE_GOODS_RECEIPT,
            'quantity_change' => 5,
            'unit_cost' => 70000,
        ]);
    }

    public function test_supplier_invoices_index_requires_bills_permission(): void
    {
        $response = $this->actingAs($this->staffPurchasingViewer)->get(route('purchasing.bills.index'));
        $response->assertRedirect(route('portal'));

        $jsonResponse = $this->actingAs($this->staffPurchasingViewer)->getJson(route('purchasing.bills.index'));
        $jsonResponse->assertStatus(403);

        $mgrResponse = $this->actingAs($this->staffPurchasingManager)->get(route('purchasing.bills.index'));
        $mgrResponse->assertStatus(200);
    }

    public function test_supplier_invoice_payment_requires_supervisor_pin_for_large_amounts(): void
    {
        $invoice = SupplierInvoice::create([
            'business_id' => $this->business->id,
            'supplier_id' => $this->supplier->id,
            'invoice_number' => 'INV-SUPP-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'total_amount' => 10000000,
            'balance_due' => 10000000,
            'status' => 'unpaid',
        ]);

        // 1. Payment >= 5.000.000 without PIN fails with validation error
        $payloadWithoutPin = [
            'amount' => 6000000,
            'payment_method' => 'bank_transfer',
            'cash_account_id' => $this->cashAccount->id,
            'supervisor_pin' => '',
        ];

        $response = $this->actingAs($this->staffPurchasingManager)->post(route('purchasing.bills.payments.store', $invoice), $payloadWithoutPin);
        $response->assertSessionHasErrors('supervisor_pin');

        // 2. Payment with valid supervisor PIN succeeds
        $payloadWithValidPin = [
            'amount' => 6000000,
            'payment_method' => 'bank_transfer',
            'cash_account_id' => $this->cashAccount->id,
            'supervisor_pin' => '123456',
        ];

        $responseValid = $this->actingAs($this->staffPurchasingManager)->post(route('purchasing.bills.payments.store', $invoice), $payloadWithValidPin);
        $responseValid->assertRedirect(route('purchasing.bills.show', $invoice));

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'user_id' => $this->staffPurchasingManager->id,
            'auditable_type' => SupplierInvoice::class,
            'auditable_id' => $invoice->id,
            'action' => 'supplier_invoice.payment_recorded',
            'risk_level' => AuditLog::RISK_MEDIUM,
        ]);
    }

    public function test_purchase_return_requires_return_permission(): void
    {
        $response = $this->actingAs($this->staffPurchasingViewer)->get(route('purchase.returns.index'));
        $response->assertRedirect(route('portal'));

        $jsonResponse = $this->actingAs($this->staffPurchasingViewer)->getJson(route('purchase.returns.index'));
        $jsonResponse->assertStatus(403);

        $mgrResponse = $this->actingAs($this->staffPurchasingManager)->get(route('purchase.returns.index'));
        $mgrResponse->assertStatus(200);
    }

    public function test_purchase_return_tenant_isolation_prevents_idor(): void
    {
        $otherLocation = Location::create([
            'business_id' => $this->otherBusiness->id,
            'name' => 'Gudang Cabang Lain',
            'code' => 'GDG-LAIN',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        $otherReceipt = GoodsReceipt::create([
            'business_id' => $this->otherBusiness->id,
            'location_id' => $otherLocation->id,
            'supplier_id' => $this->supplier->id,
            'receipt_number' => 'GR-OTHER-001',
            'receipt_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $otherReturn = PurchaseReturn::create([
            'business_id' => $this->otherBusiness->id,
            'location_id' => $otherLocation->id,
            'supplier_id' => $this->supplier->id,
            'goods_receipt_id' => $otherReceipt->id,
            'return_number' => 'RET-OTHER-001',
            'return_date' => now()->toDateString(),
            'status' => 'draft',
            'reason' => 'Barang rusak di bisnis lain',
        ]);

        $response = $this->actingAs($this->owner)->get(route('purchase.returns.show', $otherReturn));
        $this->assertContains($response->status(), [403, 404]);
    }

    public function test_purchase_return_approval_and_completion_logs_audit(): void
    {
        $receipt = GoodsReceipt::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'supplier_id' => $this->supplier->id,
            'receipt_number' => 'GR-RET-001',
            'receipt_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $return = PurchaseReturn::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'supplier_id' => $this->supplier->id,
            'goods_receipt_id' => $receipt->id,
            'return_number' => 'RET-APP-001',
            'return_date' => now()->toDateString(),
            'status' => 'draft',
            'reason' => 'Barang kadaluarsa',
        ]);

        // Approve with PIN
        $responseApprove = $this->actingAs($this->staffPurchasingManager)->post(route('purchase.returns.approve', $return), [
            'pin' => '123456',
        ]);
        $responseApprove->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'auditable_type' => PurchaseReturn::class,
            'auditable_id' => $return->id,
            'action' => 'purchase_return.approved',
            'risk_level' => AuditLog::RISK_MEDIUM,
        ]);
    }
}
