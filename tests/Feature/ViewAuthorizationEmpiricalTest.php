<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LaborRate;
use App\Models\Machine;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Empirical Adversarial Role-Based View Testing for Milestone 2.
 * Verifies that action buttons (Save, Edit, Delete, Add Machine, Add Labor Rate)
 * are strictly hidden from unauthorized users and present/styled for authorized users.
 */
final class ViewAuthorizationEmpiricalTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $manager;
    private User $viewerOnly;
    private User $unauthorizedStaff;

    private Customer $customer;
    private Unit $unit;
    private Product $product;
    private Invoice $draftInvoice;
    private LaborRate $laborRate;
    private Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();

        // 1. Seed complete RBAC matrix
        $this->seed(RbacSeeder::class);

        // 2. Setup Business
        $this->business = Business::create([
            'name' => 'PT Manufaktur Cipta Kreasi',
            'slug' => 'pt-manufaktur-cipta-kreasi',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'disabled_modules' => [],
        ]);

        $subscription = BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // 3. Create Custom View-Only Role (only has invoices.view and labor_machines.view)
        $viewOnlyRole = Role::create([
            'business_id' => $this->business->id,
            'slug' => 'module_viewer',
            'name' => 'Module Viewer',
            'description' => 'Can only view invoices and labor machines without mutation permissions',
        ]);

        $invoicesViewPerm = Permission::where('slug', 'invoices.view')->firstOrFail();
        $laborViewPerm = Permission::where('slug', 'labor_machines.view')->firstOrFail();

        $viewOnlyRole->permissions()->attach($invoicesViewPerm->id, ['id' => (string) Str::uuid()]);
        $viewOnlyRole->permissions()->attach($laborViewPerm->id, ['id' => (string) Str::uuid()]);

        // 4. Create Users
        $this->owner = $this->createUserWithRole($this->business, 'owner', 'owner.kreasi@cooca.test');
        $this->manager = $this->createUserWithRole($this->business, 'manager', 'manager.kreasi@cooca.test');
        $this->viewerOnly = $this->createUserWithRole($this->business, 'module_viewer', 'viewer.kreasi@cooca.test');
        $this->unauthorizedStaff = $this->createUserWithRole($this->business, 'warehouse', 'warehouse.kreasi@cooca.test');

        // Set default context to business
        Context::setBusiness($this->business);

        // 5. Seed Test Data
        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'CV Pembeli Makmur',
            'company_name' => 'Makmur Group',
            'email' => 'pembeli@makmur.test',
            'phone' => '08123456789',
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pieces',
            'code' => 'pcs',
            'symbol' => 'pcs',
            'category' => 'quantity',
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Widget Mesin CNC',
            'code' => 'PRD-CNC-01',
            'output_unit_id' => $this->unit->id,
            'selling_price' => 150000,
            'base_cost' => 90000,
            'is_active' => true,
        ]);

        $this->draftInvoice = Invoice::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-TEST-0001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => Invoice::STATUS_DRAFT,
            'subtotal' => 300000,
            'total_amount' => 300000,
            'balance_due' => 300000,
            'paid_amount' => 0,
        ]);

        $this->laborRate = LaborRate::create([
            'business_id' => $this->business->id,
            'name' => 'Teknisi Perakitan Presisi',
            'basis' => 'hourly',
            'rate_amount' => 50000.0,
            'working_days_per_month' => 22,
            'working_hours_per_day' => 8.0,
            'utilization_rate' => 80.0,
            'is_subcontractor' => false,
        ]);

        $this->machine = Machine::create([
            'business_id' => $this->business->id,
            'name' => 'Mesin CNC Milling 4-Axis',
            'purchase_price' => 250000000.0,
            'residual_value' => 25000000.0,
            'useful_life_hours' => 25000.0,
            'power_kw' => 5.5,
            'electricity_cost_per_kwh' => 1500.0,
            'maintenance_cost_per_hour' => 25000.0,
        ]);
    }

    private function createUserWithRole(Business $business, string $role, string $email): User
    {
        $user = User::create([
            'name' => 'User ' . ucfirst($role),
            'email' => $email,
            'password' => 'password123',
            'active_business_id' => $business->id,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => $role,
        ]);

        return $user;
    }

    // =========================================================================
    // 1. LABOR & MACHINES VIEW AUTHORIZATION TESTING
    // =========================================================================

    public function test_labor_machines_view_shows_action_buttons_for_authorized_manager_and_owner(): void
    {
        // 1. Test as Owner
        $responseOwner = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('labor-machines.index'));

        $responseOwner->assertStatus(200);
        $htmlOwner = $responseOwner->getContent();

        // Verify primary header add buttons are present
        $this->assertStringContainsString('showLaborModal = true', $htmlOwner, 'Owner should see Add Labor Rate button modal trigger.');
        $this->assertStringContainsString('showMachineModal = true', $htmlOwner, 'Owner should see Add Machine button modal trigger.');
        $this->assertStringContainsString('bg-[#007AFF]', $htmlOwner, 'Primary button should be styled with solid #007AFF.');

        // Verify desktop table Edit & Delete action buttons are present
        $this->assertStringContainsString('openEditLabor', $htmlOwner, 'Owner should see openEditLabor button.');
        $this->assertStringContainsString('openEditMachine', $htmlOwner, 'Owner should see openEditMachine button.');
        $this->assertStringContainsString(route('labor-rates.destroy', $this->laborRate->id), $htmlOwner, 'Owner should see Delete Labor Rate action link.');
        $this->assertStringContainsString(route('machines.destroy', $this->machine->id), $htmlOwner, 'Owner should see Delete Machine action link.');

        // Verify minimum 44px touch targets on buttons
        $this->assertStringContainsString('min-h-[44px]', $htmlOwner, 'Buttons must adhere to Apple HIG min-h-[44px].');

        // 2. Test as Manager (has labor_machines.manage via RbacSeeder)
        $responseManager = $this->actingAs($this->manager, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('labor-machines.index'));

        $responseManager->assertStatus(200);
        $htmlManager = $responseManager->getContent();

        $this->assertStringContainsString('showLaborModal = true', $htmlManager, 'Manager should see Add Labor Rate button.');
        $this->assertStringContainsString('showMachineModal = true', $htmlManager, 'Manager should see Add Machine button.');
        $this->assertStringContainsString('openEditLabor', $htmlManager, 'Manager should see Edit Labor button.');
        $this->assertStringContainsString('openEditMachine', $htmlManager, 'Manager should see Edit Machine button.');
        $this->assertStringContainsString('openDelete', $htmlManager, 'Manager should see Delete trigger.');
    }

    public function test_labor_machines_view_hides_action_buttons_for_view_only_staff(): void
    {
        // Test as Viewer (has labor_machines.view, but NOT labor_machines.manage)
        $response = $this->actingAs($this->viewerOnly, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('labor-machines.index'));

        $response->assertStatus(200);
        $html = $response->getContent();

        // 1. Header Add buttons MUST be completely absent
        $this->assertStringNotContainsString('showLaborModal = true', $html, 'Viewer MUST NOT see Add Labor Rate button trigger.');
        $this->assertStringNotContainsString('showMachineModal = true', $html, 'Viewer MUST NOT see Add Machine button trigger.');
        $this->assertStringNotContainsString('Tambah Tarif Upah', $html, 'Viewer MUST NOT see "Tambah Tarif Upah" text.');
        $this->assertStringNotContainsString('Tambah Mesin Baru', $html, 'Viewer MUST NOT see "Tambah Mesin Baru" text.');

        // 2. Table & Mobile Edit/Delete actions MUST be completely absent
        $this->assertStringNotContainsString('@click="openEditLabor', $html, 'Viewer MUST NOT see openEditLabor trigger button.');
        $this->assertStringNotContainsString('@click="openEditMachine', $html, 'Viewer MUST NOT see openEditMachine trigger button.');
        $this->assertStringNotContainsString(route('labor-rates.destroy', $this->laborRate->id), $html, 'Viewer MUST NOT see Labor Rate destroy route.');
        $this->assertStringNotContainsString(route('machines.destroy', $this->machine->id), $html, 'Viewer MUST NOT see Machine destroy route.');

        // 3. Fallback placeholder "-" must be present in the actions column for viewer
        $this->assertStringContainsString('<span class="text-black/30 dark:text-white/30 text-[12px]">-</span>', $html, 'Viewer should see placeholder dash in actions column.');
    }

    // =========================================================================
    // 2. INVOICES INDEX VIEW AUTHORIZATION TESTING
    // =========================================================================

    public function test_invoices_index_view_shows_action_buttons_for_owner(): void
    {
        $response = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('invoices.index'));

        $response->assertStatus(200);
        $html = $response->getContent();

        // Create New button present
        $this->assertStringContainsString(route('invoices.create'), $html, 'Owner should see Create New Invoice link.');
        $this->assertStringContainsString('bg-[#007AFF]', $html, 'Create New Invoice link should be styled as solid primary.');

        // Export button present
        $this->assertStringContainsString(route('invoices.export-excel'), $html, 'Owner should see Export Excel/CSV link.');

        // Edit link present
        $this->assertStringContainsString(route('invoices.edit', $this->draftInvoice->id), $html, 'Owner should see Edit Invoice link.');

        // Delete action trigger present
        $this->assertStringContainsString('openDelete', $html, 'Owner should see Delete action trigger.');
    }

    public function test_invoices_index_view_hides_action_buttons_for_view_only_staff(): void
    {
        // Viewer has invoices.view, but lacks invoices.create, invoices.edit, invoices.delete, invoices.export
        $response = $this->actingAs($this->viewerOnly, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('invoices.index'));

        $response->assertStatus(200);
        $html = $response->getContent();

        // 1. Create New button MUST be hidden
        $this->assertStringNotContainsString(route('invoices.create'), $html, 'Viewer MUST NOT see Create New Invoice link.');

        // 2. Export button MUST be hidden
        $this->assertStringNotContainsString(route('invoices.export-excel'), $html, 'Viewer MUST NOT see Export link.');

        // 3. Edit link MUST be hidden
        $this->assertStringNotContainsString(route('invoices.edit', $this->draftInvoice->id), $html, 'Viewer MUST NOT see Edit link.');

        // 4. Delete trigger for invoice MUST be hidden
        $this->assertStringNotContainsString('<span>' . __('sales.invoices.btn_delete') . '</span>', $html, 'Viewer MUST NOT see Delete trigger.');

        // 5. Allowed read-only links (Show & PDF) MUST still be accessible
        $this->assertStringContainsString(route('invoices.show', $this->draftInvoice->id), $html, 'Viewer should see Detail Show link.');
        $this->assertStringContainsString(route('invoices.pdf', $this->draftInvoice->id), $html, 'Viewer should see PDF link.');
    }

    // =========================================================================
    // 3. INVOICES CREATE & EDIT VIEW SAVE BUTTON HIERARCHY & AUTH TESTING
    // =========================================================================

    public function test_invoices_create_save_buttons_are_present_and_properly_styled_for_authorized_user(): void
    {
        $this->actingAs($this->owner, 'web');
        Context::setBusiness($this->business);

        $view = view('app.invoices.create', [
            'business' => $this->business,
            'selectedPo' => null,
            'prefillProduct' => null,
            'prefillQty' => 0,
            'prefillPrice' => 0,
            'prefillCost' => 0,
            'customers' => collect([$this->customer]),
            'products' => collect([$this->product]),
            'units' => collect([$this->unit]),
            'locations' => collect([]),
            'availablePurchaseOrders' => collect([]),
        ]);

        $html = $view->render();

        // Top Save Button: Solid Primary #007AFF
        $this->assertStringContainsString('bg-[#007AFF] hover:bg-[#0071E3]', $html, 'Top Save button must be solid #007AFF.');
        $this->assertStringContainsString('min-h-[44px]', $html, 'Save button must meet min-h-[44px].');

        // Bottom Save Button: Secondary Tonal #007AFF/10
        $this->assertStringContainsString('bg-[#007AFF]/10 hover:bg-[#007AFF]/20 border border-[#007AFF]/20', $html, 'Bottom Save button must be tonal secondary.');
    }

    public function test_invoices_create_save_buttons_are_hidden_for_unauthorized_user(): void
    {
        Context::flush();
        $this->actingAs($this->viewerOnly, 'web');
        Context::setBusiness($this->business);

        $view = view('app.invoices.create', [
            'business' => $this->business,
            'selectedPo' => null,
            'prefillProduct' => null,
            'prefillQty' => 0,
            'prefillPrice' => 0,
            'prefillCost' => 0,
            'customers' => collect([$this->customer]),
            'products' => collect([$this->product]),
            'units' => collect([$this->unit]),
            'locations' => collect([]),
            'availablePurchaseOrders' => collect([]),
        ]);

        $html = $view->render();

        // Neither top nor bottom Save button should be rendered
        $this->assertStringNotContainsString('<span>' . __('sales.invoices.btn_save_invoice') . '</span>', $html, 'Unauthorized user MUST NOT see Save button.');
    }

    public function test_invoices_edit_save_buttons_are_present_and_properly_styled_for_authorized_user(): void
    {
        $this->actingAs($this->owner, 'web');
        Context::setBusiness($this->business);

        $view = view('app.invoices.edit', [
            'business' => $this->business,
            'invoice' => $this->draftInvoice,
            'customers' => collect([$this->customer]),
            'products' => collect([$this->product]),
            'units' => collect([$this->unit]),
            'locations' => collect([]),
        ]);

        $html = $view->render();

        // Top Save Button: Solid Primary #007AFF
        $this->assertStringContainsString('bg-[#007AFF] hover:bg-[#0071E3]', $html, 'Top Save changes button must be solid #007AFF.');
        $this->assertStringContainsString('min-h-[44px]', $html, 'Save changes button must meet min-h-[44px].');

        // Bottom Save Button: Secondary Tonal #007AFF/10
        $this->assertStringContainsString('bg-[#007AFF]/10 hover:bg-[#007AFF]/20 border border-[#007AFF]/20', $html, 'Bottom Save changes button must be tonal secondary.');
    }

    public function test_invoices_edit_save_buttons_are_hidden_for_unauthorized_user(): void
    {
        Context::flush();
        $this->actingAs($this->viewerOnly, 'web');
        Context::setBusiness($this->business);

        $view = view('app.invoices.edit', [
            'business' => $this->business,
            'invoice' => $this->draftInvoice,
            'customers' => collect([$this->customer]),
            'products' => collect([$this->product]),
            'units' => collect([$this->unit]),
            'locations' => collect([]),
        ]);

        $html = $view->render();

        // Neither top nor bottom Save changes button should be rendered
        $this->assertStringNotContainsString('<span>' . __('sales.invoices.btn_save_changes') . '</span>', $html, 'Unauthorized user MUST NOT see Save changes button.');
    }

    // =========================================================================
    // 4. INVOICES SHOW ACTION BUTTONS GATING TESTING
    // =========================================================================

    public function test_invoices_show_action_buttons_are_gated_by_role(): void
    {
        // 1. Owner views draft invoice
        $responseOwner = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('invoices.show', $this->draftInvoice->id));

        $responseOwner->assertStatus(200);
        $htmlOwner = $responseOwner->getContent();

        // Owner sees Rilis Draf, Edit Faktur, Hapus Draf
        $this->assertStringContainsString('<form id="form-confirm-release"', $htmlOwner, 'Owner should see confirm release form.');
        $this->assertStringContainsString(route('invoices.edit', $this->draftInvoice->id), $htmlOwner, 'Owner should see Edit action link.');
        $this->assertStringContainsString('<form id="form-confirm-delete"', $htmlOwner, 'Owner should see confirm delete form.');

        // 2. Viewer views draft invoice
        $responseViewer = $this->actingAs($this->viewerOnly, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('invoices.show', $this->draftInvoice->id));

        $responseViewer->assertStatus(200);
        $htmlViewer = $responseViewer->getContent();

        // Viewer MUST NOT see Rilis Draf, Edit Faktur, or Hapus Draf
        $this->assertStringNotContainsString('<form id="form-confirm-release"', $htmlViewer, 'Viewer MUST NOT see confirm release form.');
        $this->assertStringNotContainsString(route('invoices.edit', $this->draftInvoice->id), $htmlViewer, 'Viewer MUST NOT see Edit action link.');
        $this->assertStringNotContainsString('<form id="form-confirm-delete"', $htmlViewer, 'Viewer MUST NOT see confirm delete form.');

        // Viewer CAN see Print & PDF
        $this->assertStringContainsString(route('invoices.print', $this->draftInvoice->id), $htmlViewer, 'Viewer should see Print action.');
        $this->assertStringContainsString(route('invoices.pdf', $this->draftInvoice->id), $htmlViewer, 'Viewer should see PDF action.');
    }
}
