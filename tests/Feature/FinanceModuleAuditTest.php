<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Import\DataExchangeService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Support\Security\CsvSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FinanceModuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $staffWithoutPerms;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner Keuangan',
            'email' => 'owner.fin@cooca.test',
            'phone' => '6281299991111',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Toko Audit Finansial Prima',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
            'disabled_modules' => [],
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'role' => 'owner',
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        $this->staffWithoutPerms = User::create([
            'name' => 'Staf Magang',
            'email' => 'magang@cooca.test',
            'phone' => '6281299992222',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->business->users()->attach($this->staffWithoutPerms->id, [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'role' => 'cashier',
        ]);
        $this->staffWithoutPerms->update(['active_business_id' => $this->business->id]);

        (new AutoJournalService())->ensureStandardAccounts($this->business);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('finance.cash-bank.index'))->assertRedirect(route('login'));
        $this->get(route('finance.coa.index'))->assertRedirect(route('login'));
        $this->get(route('finance.expenses.index'))->assertRedirect(route('login'));
        $this->get(route('finance.receivables'))->assertRedirect(route('login'));
        $this->get(route('finance.payables'))->assertRedirect(route('login'));
        $this->get(route('finance.settlements.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_finance_permissions_is_blocked_with_forbidden(): void
    {
        // Web requests redirect to portal with flash error
        $this->actingAs($this->staffWithoutPerms, 'web')
            ->get(route('finance.cash-bank.index'))
            ->assertRedirect(route('portal'));

        $this->actingAs($this->staffWithoutPerms, 'web')
            ->get(route('finance.coa.index'))
            ->assertRedirect(route('portal'));

        $this->actingAs($this->staffWithoutPerms, 'web')
            ->get(route('finance.expenses.index'))
            ->assertRedirect(route('portal'));

        // JSON requests return 403 Forbidden with FORBIDDEN_PERMISSION
        $this->actingAs($this->staffWithoutPerms, 'web')
            ->getJson(route('finance.cash-bank.index'))
            ->assertForbidden();

        $this->actingAs($this->staffWithoutPerms, 'web')
            ->getJson(route('finance.coa.index'))
            ->assertForbidden();
    }

    public function test_owner_can_access_all_finance_and_accounting_hubs(): void
    {
        $this->actingAs($this->owner, 'web')
            ->get(route('finance.cash-bank.index'))
            ->assertOk();

        $this->actingAs($this->owner, 'web')
            ->get(route('finance.coa.index'))
            ->assertOk();

        $this->actingAs($this->owner, 'web')
            ->get(route('finance.expenses.index'))
            ->assertOk();

        $this->actingAs($this->owner, 'web')
            ->get(route('finance.receivables'))
            ->assertOk();

        $this->actingAs($this->owner, 'web')
            ->get(route('finance.payables'))
            ->assertOk();

        $this->actingAs($this->owner, 'web')
            ->get(route('finance.settlements.index'))
            ->assertOk();
    }

    public function test_creating_custom_coa_records_audit_log(): void
    {
        $response = $this->actingAs($this->owner, 'web')->post(route('finance.coa.store'), [
            'code' => '6199',
            'name' => 'Beban Lain-Lain Operasional',
            'type' => ChartOfAccount::TYPE_EXPENSE,
            'normal_balance' => 'debit',
        ]);

        $response->assertRedirect(route('finance.coa.index'));

        $account = ChartOfAccount::where('business_id', $this->business->id)
            ->where('code', '6199')
            ->first();

        $this->assertNotNull($account);
        $this->assertEquals('Beban Lain-Lain Operasional', $account->name);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'accounting.coa_created',
            'auditable_type' => ChartOfAccount::class,
            'auditable_id' => $account->id,
        ]);
    }

    public function test_creating_cash_account_records_audit_log(): void
    {
        $response = $this->actingAs($this->owner, 'web')->post(route('finance.cash-bank.accounts.store'), [
            'name' => 'Kas Operasional Outlet 2',
            'type' => 'cash',
            'opening_balance' => 0,
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $account = CashAccount::where('business_id', $this->business->id)
            ->where('name', 'Kas Operasional Outlet 2')
            ->first();

        $this->assertNotNull($account);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'finance.cash_account_created',
            'auditable_type' => CashAccount::class,
            'auditable_id' => $account->id,
        ]);
    }

    public function test_recording_expense_records_audit_log_and_outflow(): void
    {
        $cashAccount = CashAccount::create([
            'business_id' => $this->business->id,
            'name' => 'Kas Laci Utama',
            'type' => 'cash',
            'current_balance' => 5000000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner, 'web')->post(route('finance.expenses.store'), [
            'expense_date' => now()->toDateString(),
            'category' => 'utilities',
            'amount' => 150000,
            'payment_method' => 'cash',
            'cash_account_id' => $cashAccount->id,
            'description' => 'Pembayaran Token Listrik PLN',
        ]);

        $response->assertRedirect();

        $expense = Expense::where('business_id', $this->business->id)
            ->where('amount', 150000)
            ->first();

        $this->assertNotNull($expense);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'finance.expense_created',
            'auditable_type' => Expense::class,
            'auditable_id' => $expense->id,
        ]);
    }

    public function test_multi_tenant_isolation_blocks_cross_business_modification(): void
    {
        $otherBusiness = Business::create([
            'name' => 'Toko Sebelah',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
            'disabled_modules' => [],
        ]);
        $otherAccount = CashAccount::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Kas Toko Sebelah',
            'type' => 'cash',
            'current_balance' => 1000000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner, 'web')->put(route('finance.cash-bank.accounts.update', $otherAccount), [
            'name' => 'Hacked Name',
            'type' => 'cash',
        ]);

        $this->assertTrue(in_array($response->status(), [403, 404], true), "Expected 403 or 404 for cross-tenant CashAccount update, received {$response->status()}");

        $otherCoa = ChartOfAccount::create([
            'business_id' => $otherBusiness->id,
            'code' => '8888',
            'name' => 'Akun Rahasia Sebelah',
            'type' => ChartOfAccount::TYPE_ASSET,
            'normal_balance' => 'debit',
            'is_system' => false,
            'is_active' => true,
        ]);

        $responseCoa = $this->actingAs($this->owner, 'web')->delete(route('finance.coa.destroy', $otherCoa));
        $this->assertTrue(in_array($responseCoa->status(), [403, 404], true), "Expected 403 or 404 for cross-tenant COA deletion, received {$responseCoa->status()}");
    }

    public function test_formula_injection_is_sanitized_during_excel_exports(): void
    {
        $maliciousCode = '=cmd|"/C calc"!A0';
        $sanitized = CsvSanitizer::sanitize($maliciousCode);
        $this->assertStringStartsWith("'", $sanitized);

        $plusFormula = '+10+20';
        $this->assertStringStartsWith("'", CsvSanitizer::sanitize($plusFormula));

        $atFormula = '@SUM(A1:A10)';
        $this->assertStringStartsWith("'", CsvSanitizer::sanitize($atFormula));

        $normalText = 'Faktur Penjualan Normal';
        $this->assertEquals($normalText, CsvSanitizer::sanitize($normalText));
    }
}
