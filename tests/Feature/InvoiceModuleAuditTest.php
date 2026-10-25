<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Domain\Commerce\InvoiceService;
use App\Exports\InvoiceReportExport;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class InvoiceModuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $staffWithoutPerms;
    private Customer $customer;
    private Unit $unit;
    private Product $product;
    private InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RbacSeeder::class);

        $this->invoiceService = new InvoiceService();

        $this->owner = User::create([
            'name' => 'Owner Invoices',
            'email' => 'owner.invoices@cooca.test',
            'phone' => '6281299991111',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'PT Distribusi Jaya Sentosa',
            'slug' => 'pt-distribusi-jaya-sentosa',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
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

        $this->staffWithoutPerms = User::create([
            'name' => 'Staf Gudang Tanpa Izin Invoices',
            'email' => 'gudang.invoices@cooca.test',
            'phone' => '6281299992222',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->business->users()->attach($this->staffWithoutPerms->id, [
            'id' => (string) Str::uuid(),
            'role' => 'warehouse',
        ]);
        $this->staffWithoutPerms->update(['active_business_id' => $this->business->id]);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'PT Mitra Dagang Sejahtera',
            'company_name' => 'MDS Group',
            'email' => 'contact@mds.test',
            'phone' => '081234567890',
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Kotak',
            'code' => 'box',
            'symbol' => 'box',
            'category' => 'quantity',
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Produk A',
            'sku' => 'SKU-A-001',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 100000,
            'hpp_cost' => 60000,
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('invoices.index'))->assertRedirect(route('login'));
        $this->get(route('invoices.create'))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_is_blocked(): void
    {
        $this->actingAs($this->staffWithoutPerms, 'web')
            ->get(route('invoices.index'))
            ->assertRedirect(route('portal'));

        $this->actingAs($this->staffWithoutPerms, 'web')
            ->get(route('invoices.create'))
            ->assertRedirect(route('portal'));
    }

    public function test_owner_can_access_invoices_hub(): void
    {
        Context::setBusiness($this->business);

        $response = $this->actingAs($this->owner, 'web')
            ->get(route('invoices.index'));

        $response->assertStatus(200);
        $response->assertSee('Faktur Penjualan', false);
    }

    public function test_invoice_creation_emits_audit_log(): void
    {
        Context::setBusiness($this->business);

        $payload = [
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-TEST-001',
            'sender_name' => 'PT Distribusi Jaya Sentosa',
            'recipient_name' => 'PT Mitra Dagang Sejahtera',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'payment_terms' => 'Net 30',
            'notes' => 'Catatan invoice pengiriman tahap 1',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'item_name' => 'Produk A',
                    'quantity' => 10,
                    'unit_id' => $this->unit->id,
                    'unit_price' => 100000,
                    'unit_hpp' => 60000,
                ],
            ],
        ];

        $response = $this->actingAs($this->owner, 'web')
            ->post(route('invoices.store'), $payload);

        $invoice = Invoice::where('business_id', $this->business->id)
            ->where('invoice_number', 'INV-TEST-001')
            ->firstOrFail();

        $response->assertRedirect(route('invoices.show', $invoice->id));

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'action' => 'invoices.invoice_created',
            'risk_level' => AuditLog::RISK_LOW,
        ]);
    }

    public function test_draft_invoice_update_emits_audit_log(): void
    {
        Context::setBusiness($this->business);

        $invoice = $this->invoiceService->createFromProducts(
            $this->business,
            $this->customer,
            [
                [
                    'product_id' => $this->product->id,
                    'item_name' => 'Produk A',
                    'quantity' => 5,
                    'unit_id' => $this->unit->id,
                    'unit_price' => 100000,
                ],
            ],
            ['status' => Invoice::STATUS_DRAFT]
        );

        $payload = [
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'item_name' => 'Produk A (Revisi)',
                    'quantity' => 8,
                    'unit_id' => $this->unit->id,
                    'unit_price' => 105000,
                ],
            ],
        ];

        $res = $this->actingAs($this->owner, 'web')
            ->put(route('invoices.update', $invoice->id), $payload);

        $res->assertRedirect(route('invoices.show', $invoice->id));

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'action' => 'invoices.invoice_updated',
            'risk_level' => AuditLog::RISK_LOW,
        ]);
    }

    public function test_recording_payment_emits_audit_log(): void
    {
        Context::setBusiness($this->business);

        $invoice = $this->invoiceService->createFromProducts(
            $this->business,
            $this->customer,
            [
                [
                    'product_id' => $this->product->id,
                    'item_name' => 'Produk A',
                    'quantity' => 2,
                    'unit_id' => $this->unit->id,
                    'unit_price' => 100000,
                ],
            ],
            ['status' => Invoice::STATUS_SENT]
        );

        $paymentPayload = [
            'amount' => 100000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference_number' => 'TRX-BCA-887766',
            'notes' => 'Uang muka termin 1',
        ];

        $res = $this->actingAs($this->owner, 'web')
            ->post(route('invoices.payments.store', $invoice->id), $paymentPayload);

        $res->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'action' => 'invoices.payment_recorded',
            'risk_level' => AuditLog::RISK_MEDIUM,
        ]);
    }

    public function test_voiding_and_deleting_draft_invoice_emit_audit_logs(): void
    {
        Context::setBusiness($this->business);

        // 1. Test Voiding Invoice
        $invoiceVoid = $this->invoiceService->createFromProducts(
            $this->business,
            $this->customer,
            [
                [
                    'product_id' => $this->product->id,
                    'item_name' => 'Produk A',
                    'quantity' => 1,
                    'unit_id' => $this->unit->id,
                    'unit_price' => 50000,
                ],
            ],
            ['status' => Invoice::STATUS_SENT]
        );

        $resVoid = $this->actingAs($this->owner, 'web')
            ->post(route('invoices.void', $invoiceVoid->id), ['reason' => 'Pelanggan membatalkan pesanan']);

        $resVoid->assertRedirect();
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoiceVoid->id,
            'action' => 'invoices.invoice_voided',
            'risk_level' => AuditLog::RISK_HIGH,
        ]);

        // 2. Test Deleting Draft Invoice
        $invoiceDraft = $this->invoiceService->createFromProducts(
            $this->business,
            $this->customer,
            [
                [
                    'product_id' => $this->product->id,
                    'item_name' => 'Produk A',
                    'quantity' => 1,
                    'unit_id' => $this->unit->id,
                    'unit_price' => 50000,
                ],
            ],
            ['status' => Invoice::STATUS_DRAFT]
        );

        $resDelete = $this->actingAs($this->owner, 'web')
            ->delete(route('invoices.destroy', $invoiceDraft->id));

        $resDelete->assertRedirect(route('invoices.index'));
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoiceDraft->id,
            'action' => 'invoices.invoice_deleted',
            'risk_level' => AuditLog::RISK_HIGH,
        ]);
        $this->assertSoftDeleted('invoices', ['id' => $invoiceDraft->id]);
    }

    public function test_cross_tenant_isolation_prevents_idor(): void
    {
        Context::setBusiness($this->business);

        $tenantB = Business::create([
            'name' => 'PT Kompetitor Tenant B',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $customerB = Customer::create([
            'business_id' => $tenantB->id,
            'name' => 'Pelanggan Tenant B',
        ]);

        $invoiceB = Invoice::create([
            'id' => (string) Str::uuid(),
            'business_id' => $tenantB->id,
            'customer_id' => $customerB->id,
            'invoice_number' => 'INV-TENANT-B-999',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => Invoice::STATUS_DRAFT,
            'subtotal' => 200000,
            'total_amount' => 200000,
            'balance_due' => 200000,
        ]);

        // Owner Tenant A attempts to access Tenant B's invoice -> Aborts 404 due to BelongsToBusiness global scope
        $this->actingAs($this->owner, 'web')
            ->get(route('invoices.show', $invoiceB->id))
            ->assertStatus(404);

        $this->actingAs($this->owner, 'web')
            ->get(route('invoices.edit', $invoiceB->id))
            ->assertStatus(404);

        $this->actingAs($this->owner, 'web')
            ->delete(route('invoices.destroy', $invoiceB->id))
            ->assertStatus(404);
    }

    public function test_formula_injection_defense_sanitizes_csv_export(): void
    {
        Context::setBusiness($this->business);

        $maliciousCustomer = Customer::create([
            'business_id' => $this->business->id,
            'name' => '=CMD(\'calc.exe\')!A0',
            'company_name' => '@SUM(1+1)*cmd',
        ]);

        $invoice = Invoice::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'customer_id' => $maliciousCustomer->id,
            'invoice_number' => '+INV-FORMULA-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => Invoice::STATUS_UNPAID,
            'payment_terms' => '-DDE("cmd";"/C calc";"__")',
            'subtotal' => 150000,
            'total_amount' => 150000,
            'balance_due' => 150000,
        ]);

        $csvResponse = $this->actingAs($this->owner, 'web')
            ->get(route('invoices.export-excel', ['format' => 'csv']));

        $csvResponse->assertStatus(200);
        $content = $csvResponse->streamedContent();

        // CWE-1236 Defense: Trigger characters '=', '@', '+', '-' prefixed with single quote (')
        $this->assertStringContainsString("'+INV-FORMULA-001", $content);
        $this->assertStringContainsString("'=CMD('calc.exe')!A0", $content);
        $this->assertStringContainsString("'@SUM(1+1)*cmd", $content);
        $this->assertStringContainsString("'-DDE(", $content);
    }
}
