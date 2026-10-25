<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\EmployeeLoan;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Role;
use App\Models\User;
use App\Support\Security\CsvSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class HrmModuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $staffWithoutPerms;
    private Role $staffRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Owner HRM',
            'email' => 'owner.hrm@cooca.test',
            'phone' => '6281288881111',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'PT Resto Prima Kuliner',
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

        $entitlement = app(EntitlementService::class);
        $sub = $entitlement->getSubscription($this->business);
        $sub->update([
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
        ]);

        $this->staffWithoutPerms = User::create([
            'name' => 'Kasir Tanpa Izin HRM',
            'email' => 'kasir.hrm@cooca.test',
            'phone' => '6281288882222',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->business->users()->attach($this->staffWithoutPerms->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
        ]);
        $this->staffWithoutPerms->update(['active_business_id' => $this->business->id]);

        $this->staffRole = Role::firstOrCreate(
            ['slug' => 'staff-operasional'],
            ['name' => 'Staf Operasional', 'business_id' => $this->business->id]
        );
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('hrm.index'))->assertRedirect(route('login'));
        $this->get(route('hrm.payrolls.index'))->assertRedirect(route('login'));
        $this->get(route('hrm.payrolls.create'))->assertRedirect(route('login'));
    }

    public function test_user_without_users_view_is_blocked_with_forbidden(): void
    {
        $this->actingAs($this->staffWithoutPerms, 'web')
            ->get(route('hrm.index'))
            ->assertRedirect(route('portal'));

        $this->actingAs($this->staffWithoutPerms, 'web')
            ->get(route('hrm.payrolls.index'))
            ->assertRedirect(route('portal'));
    }

    public function test_authorized_owner_can_access_hrm_hub(): void
    {
        $response = $this->actingAs($this->owner, 'web')
            ->get(route('hrm.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen SDM &amp; Penggajian', false);
    }

    public function test_employee_creation_generates_audit_log(): void
    {
        $payload = [
            'name' => 'Dewi Sartika',
            'email' => 'dewi.sartika@cooca.test',
            'password' => 'password123',
            'role_id' => $this->staffRole->id,
            'job_title' => 'Supervisor Dapur',
            'employment_type' => 'permanent',
            'join_date' => '2026-01-15',
            'base_salary' => 6000000,
            'daily_rate' => 0,
            'fixed_allowances' => 500000,
            'variable_allowances' => 250000,
            'tax_ptkp_status' => 'TK/0',
            'nik_ktp' => '3271012345678901',
            'npwp' => '09.123.456.7-123.000',
            'bpjs_tk_enabled' => 1,
            'bpjs_tk_number' => 'TK-998877',
            'bpjs_kes_enabled' => 1,
            'bpjs_kes_number' => 'KES-112233',
            'bank_name' => 'BCA',
            'bank_account_number' => '5420123456',
            'bank_account_holder' => 'Dewi Sartika',
            'whatsapp_number' => '081234567890',
        ];

        $response = $this->actingAs($this->owner, 'web')
            ->post(route('hrm.employees.store'), $payload);

        $response->assertRedirect(route('hrm.index', ['tab' => 'employees']));
        $this->assertDatabaseHas('users', ['email' => 'dewi.sartika@cooca.test']);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'action' => 'hrm.employee_created',
        ]);
    }

    public function test_loan_creation_and_cancellation_generates_audit_logs(): void
    {
        $staffUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@cooca.test',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $this->business->users()->attach($staffUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
        ]);

        $payload = [
            'user_id' => $staffUser->id,
            'loan_date' => now()->toDateString(),
            'amount' => 2000000,
            'tenor_months' => 4,
            'purpose' => 'Biaya pengobatan darurat',
        ];

        $res = $this->actingAs($this->owner, 'web')
            ->post(route('hrm.loans.store'), $payload);

        $res->assertRedirect(route('hrm.index', ['tab' => 'loans']));

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'hrm.loan_created',
        ]);

        $loan = EmployeeLoan::where('business_id', $this->business->id)->firstOrFail();

        $cancelRes = $this->actingAs($this->owner, 'web')
            ->post(route('hrm.loans.cancel', $loan->id));

        $cancelRes->assertRedirect(route('hrm.index', ['tab' => 'loans']));

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'hrm.loan_cancelled',
        ]);
    }

    public function test_cross_tenant_isolation_protects_hrm_entities(): void
    {
        $tenantB = Business::create([
            'name' => 'Resto Cabang Kompetitor B',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $staffB = User::create([
            'name' => 'Staf Tenant B',
            'email' => 'stafb@competitor.test',
            'password' => 'password',
        ]);
        $tenantB->users()->attach($staffB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
        ]);

        $loanB = EmployeeLoan::create([
            'id' => (string) Str::uuid(),
            'business_id' => $tenantB->id,
            'user_id' => $staffB->id,
            'loan_number' => 'KSB-TENANT-B-001',
            'loan_date' => now()->toDateString(),
            'amount' => 1500000,
            'tenor_months' => 3,
            'monthly_installment' => 500000,
            'remaining_balance' => 1500000,
            'status' => EmployeeLoan::STATUS_ACTIVE,
            'approved_by' => $staffB->id,
        ]);

        // Owner Tenant A attempts to cancel Tenant B's loan -> Must abort 404
        $response = $this->actingAs($this->owner, 'web')
            ->post(route('hrm.loans.cancel', $loanB->id));

        $response->assertStatus(404);
    }

    public function test_formula_injection_defense_sanitizes_payroll_csv_export(): void
    {
        $maliciousStaff = User::create([
            'name' => '=CMD(\'calc.exe\')!A0',
            'email' => 'evil@formula.test',
            'password' => 'password',
            'bank_name' => '@SUM(1+1)*cmd',
            'bank_account_number' => '+628123456789',
        ]);
        $this->business->users()->attach($maliciousStaff->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
        ]);

        $payroll = Payroll::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'payroll_number' => 'PAY-2026-TEST',
            'period_month' => 9,
            'period_year' => 2026,
            'title' => 'Penggajian September 2026',
            'status' => Payroll::STATUS_APPROVED,
            'total_take_home_pay' => 5000000,
            'total_gross_pay' => 5500000,
            'total_deductions' => 500000,
            'total_company_cost' => 6000000,
            'total_employees_count' => 1,
            'processed_by' => $this->owner->id,
        ]);

        PayrollItem::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'payroll_id' => $payroll->id,
            'user_id' => $maliciousStaff->id,
            'employee_name' => '=CMD(\'calc.exe\')!A0',
            'job_title' => '-EXEC(\'calc\')',
            'employment_type' => 'permanent',
            'tax_ptkp_status' => 'TK/0',
            'base_salary' => 5000000,
            'take_home_pay' => 5000000,
            'gross_salary' => 5500000,
            'total_deductions' => 500000,
            'total_company_cost' => 6000000,
            'bank_name' => '@SUM(1+1)*cmd',
            'bank_account_number' => '+628123456789',
        ]);

        $csvResponse = $this->actingAs($this->owner, 'web')
            ->get(route('hrm.payrolls.export', $payroll->id));

        $csvResponse->assertStatus(200);
        $content = $csvResponse->streamedContent();

        // Formula prefix character MUST be escaped with a leading single quote (')
        $this->assertStringContainsString("'=CMD('calc.exe')!A0", $content);
        $this->assertStringContainsString("'-EXEC('calc')", $content);

        // Bank CSV Export also sanitized
        $bankCsvResponse = $this->actingAs($this->owner, 'web')
            ->get(route('hrm.payrolls.export-bank', $payroll->id));

        $bankCsvResponse->assertStatus(200);
        $bankContent = $bankCsvResponse->streamedContent();

        $this->assertStringContainsString("'+628123456789", $bankContent);
        $this->assertStringContainsString("'@SUM(1+1)*cmd", $bankContent);
    }
}
