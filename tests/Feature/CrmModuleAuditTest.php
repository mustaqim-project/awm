<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerPointHistory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Voucher;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CrmModuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $staffViewOnly;
    private User $staffManager;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        // Bisnis Utama
        $this->business = Business::create(['name' => 'Bisnis CRM Utama']);

        // Owner (Omnipotent)
        $this->owner = User::create([
            'name' => 'Owner CRM Utama',
            'email' => 'owner.crm@audit-test.local',
            'email_verified_at' => now(),
            'phone' => '081234567890',
            'phone_verified_at' => now(),
            'password' => 'password123',
        ]);

        $ownerRole = Role::where('slug', 'owner')->first();
        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        // Staff dengan permission crm.view saja
        $this->staffViewOnly = User::create([
            'name' => 'Staff CRM View',
            'email' => 'staff.view@audit-test.local',
            'email_verified_at' => now(),
            'phone' => '081234567891',
            'phone_verified_at' => now(),
            'password' => 'password123',
        ]);

        $roleView = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Custom CRM Viewer',
            'slug' => 'custom_crm_viewer',
            'description' => 'Staff view only CRM',
        ]);
        $viewPerm = Permission::where('slug', 'crm.view')->first();
        if ($viewPerm) {
            $roleView->permissions()->sync([$viewPerm->id]);
        }
        $this->business->users()->attach($this->staffViewOnly->id, [
            'id' => (string) Str::uuid(),
            'role' => 'custom_crm_viewer',
            'role_id' => $roleView->id,
        ]);
        $this->staffViewOnly->update(['active_business_id' => $this->business->id]);

        // Staff dengan permission crm.view & crm.manage
        $this->staffManager = User::create([
            'name' => 'Staff CRM Manage',
            'email' => 'staff.manage@audit-test.local',
            'email_verified_at' => now(),
            'phone' => '081234567892',
            'phone_verified_at' => now(),
            'password' => 'password123',
        ]);

        $roleManage = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Custom CRM Manager',
            'slug' => 'custom_crm_manager',
            'description' => 'Staff manage CRM',
        ]);
        $managePerms = Permission::whereIn('slug', ['crm.view', 'crm.manage'])->pluck('id');
        $roleManage->permissions()->sync($managePerms);
        $this->business->users()->attach($this->staffManager->id, [
            'id' => (string) Str::uuid(),
            'role' => 'custom_crm_manager',
            'role_id' => $roleManage->id,
        ]);
        $this->staffManager->update(['active_business_id' => $this->business->id]);
    }

    private function createOtherTenant(): array
    {
        $otherUser = User::create([
            'name' => 'Owner Lain',
            'email' => 'other.owner@audit-test.local',
            'email_verified_at' => now(),
            'phone' => '089999999999',
            'phone_verified_at' => now(),
            'password' => 'password123',
        ]);

        $otherBiz = Business::create(['name' => 'Bisnis Tenant Lain']);
        $ownerRole = Role::where('slug', 'owner')->first();
        $otherBiz->users()->attach($otherUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);
        $otherUser->update(['active_business_id' => $otherBiz->id]);

        $otherCustomer = Customer::create([
            'business_id' => $otherBiz->id,
            'name' => 'Pelanggan Toko Sebelah',
            'phone' => '088888888888',
            'current_credit_balance' => 500000,
        ]);

        $otherVoucher = Voucher::create([
            'business_id' => $otherBiz->id,
            'code' => 'SEBELAH10',
            'name' => 'Diskon Toko Sebelah',
            'discount_type' => 'fixed',
            'discount_value' => 10000,
            'is_active' => true,
        ]);

        return [$otherUser, $otherBiz, $otherCustomer, $otherVoucher];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('crm.members.index'))->assertRedirect(route('login'));
        $this->get(route('crm.vouchers.index'))->assertRedirect(route('login'));
    }

    public function test_permission_gating_crm_view_and_manage(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Siti Pelanggan',
            'phone' => '0877777777',
            'current_credit_balance' => 300000,
        ]);

        // 1. Staff dengan crm.view dapat melihat members & vouchers
        $this->actingAs($this->staffViewOnly)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('crm.members.index'))
            ->assertOk();

        $this->actingAs($this->staffViewOnly)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('crm.vouchers.index'))
            ->assertOk();

        // 2. Staff tanpa crm.manage DITOLAK saat mencoba bayar piutang / buat voucher
        // Via JSON request -> 403 Forbidden
        $this->actingAs($this->staffViewOnly)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('crm.customers.credit-payment', $customer), [
                'amount' => 50000,
            ])
            ->assertForbidden();

        // Via Web request -> Redirect ke portal
        $this->actingAs($this->staffViewOnly)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('crm.vouchers.store'), [
                'code' => 'TESTFAIL',
                'name' => 'Voucher Gagal',
                'discount_type' => 'fixed',
                'discount_value' => 5000,
            ])
            ->assertRedirect(route('portal'));

        // 3. Staff dengan crm.manage BERHASIL membuat voucher
        $resManage = $this->actingAs($this->staffManager)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('crm.vouchers.store'), [
                'code' => 'DISKONMANAGE',
                'name' => 'Voucher Sukses Manager',
                'discount_type' => 'percentage',
                'discount_value' => 15,
            ]);
        $resManage->assertRedirect();
        $this->assertDatabaseHas('vouchers', [
            'business_id' => $this->business->id,
            'code' => 'DISKONMANAGE',
        ]);
    }

    public function test_idor_protection_customer_points_and_credit_payment(): void
    {
        [, , $otherCustomer, $otherVoucher] = $this->createOtherTenant();

        // 1. Mencoba melihat point history customer tenant lain -> 404 (karena BusinessScope) atau 403
        $res1 = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('crm.customers.points', $otherCustomer));
        $this->assertContains($res1->status(), [403, 404]);

        // 2. Mencoba mencatat pembayaran kasbon customer tenant lain -> 404 atau 403
        $res2 = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('crm.customers.credit-payment', $otherCustomer), [
                'amount' => 100000,
            ]);
        $this->assertContains($res2->status(), [403, 404]);

        // 3. Mencoba toggle status voucher tenant lain -> 404 atau 403
        $res3 = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('crm.vouchers.toggle', $otherVoucher));
        $this->assertContains($res3->status(), [403, 404]);
    }

    public function test_audit_log_created_on_credit_payment(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Hendra Kasbon',
            'phone' => '0855555555',
            'current_credit_balance' => 250000,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('crm.customers.credit-payment', $customer), [
                'amount' => 150000,
                'notes' => 'Pelunasan sebagian via transfer',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'customer.credit_payment_recorded',
            'auditable_type' => Customer::class,
            'auditable_id' => $customer->id,
        ]);
    }

    public function test_audit_log_created_on_voucher_create_and_toggle(): void
    {
        // 1. Create voucher
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('crm.vouchers.store'), [
                'code' => 'AUDITLOG10',
                'name' => 'Voucher Audit Trail',
                'discount_type' => 'percentage',
                'discount_value' => 10,
            ]);

        $response->assertRedirect();

        $voucher = Voucher::where('code', 'AUDITLOG10')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'voucher.created',
            'auditable_type' => Voucher::class,
            'auditable_id' => $voucher->id,
        ]);

        // 2. Toggle voucher
        $toggleRes = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('crm.vouchers.toggle', $voucher));

        $toggleRes->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'voucher.status_toggled',
            'auditable_type' => Voucher::class,
            'auditable_id' => $voucher->id,
        ]);
    }

    public function test_credit_payment_cannot_exceed_current_balance(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Joko Saldo Kecil',
            'phone' => '0866666666',
            'current_credit_balance' => 50000,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('crm.customers.credit-payment', $customer), [
                'amount' => 100000, // Melebihi 50.000
            ]);

        $response->assertSessionHasErrors('amount');
        $this->assertEquals(50000, (float) $customer->fresh()->current_credit_balance);
    }

    public function test_credit_payment_fails_when_balance_zero(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Rini Lunas',
            'phone' => '0877777770',
            'current_credit_balance' => 0,
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('crm.customers.credit-payment', $customer), [
                'amount' => 10000,
            ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_voucher_code_uniqueness_scoped_to_business(): void
    {
        Voucher::create([
            'business_id' => $this->business->id,
            'code' => 'DUPLIKAT',
            'name' => 'Voucher Pertama',
            'discount_type' => 'fixed',
            'discount_value' => 5000,
            'is_active' => true,
        ]);

        // Coba buat voucher dengan kode yang sama di bisnis yang sama -> harus gagal validasi
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('crm.vouchers.store'), [
                'code' => 'DUPLIKAT',
                'name' => 'Voucher Kedua',
                'discount_type' => 'fixed',
                'discount_value' => 10000,
            ]);

        $response->assertSessionHasErrors('code');
    }
}
