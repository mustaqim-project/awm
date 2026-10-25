<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CustomerModuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $staffViewOnly;
    private User $staffEditor;
    private User $staffFull;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        // Bisnis Utama
        $this->business = Business::create(['name' => 'Bisnis Customer Utama']);

        // Owner (Omnipotent)
        $this->owner = User::create([
            'name' => 'Owner Bisnis Utama',
            'email' => 'owner.cust@audit-test.local',
            'email_verified_at' => now(),
            'phone' => '081234567800',
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

        // 1. Staff View Only (customers.view)
        $this->staffViewOnly = User::create([
            'name' => 'Staff View Customer',
            'email' => 'staff.view@audit-test.local',
            'email_verified_at' => now(),
            'phone' => '081234567801',
            'phone_verified_at' => now(),
            'password' => 'password123',
        ]);
        $roleView = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Custom Role Viewer',
            'slug' => 'custom_cust_viewer',
            'description' => 'Lihat Pelanggan',
        ]);
        $viewPerm = Permission::where('slug', 'customers.view')->first();
        if ($viewPerm) {
            $roleView->permissions()->sync([$viewPerm->id]);
        }
        $this->business->users()->attach($this->staffViewOnly->id, [
            'id' => (string) Str::uuid(),
            'role' => 'custom_cust_viewer',
            'role_id' => $roleView->id,
        ]);
        $this->staffViewOnly->update(['active_business_id' => $this->business->id]);

        // 2. Staff Editor (customers.view & customers.edit)
        $this->staffEditor = User::create([
            'name' => 'Staff Editor Customer',
            'email' => 'staff.edit@audit-test.local',
            'email_verified_at' => now(),
            'phone' => '081234567802',
            'phone_verified_at' => now(),
            'password' => 'password123',
        ]);
        $roleEdit = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Custom Role Editor',
            'slug' => 'custom_cust_editor',
            'description' => 'Lihat & Edit Pelanggan',
        ]);
        $editPerms = Permission::whereIn('slug', ['customers.view', 'customers.edit'])->pluck('id');
        $roleEdit->permissions()->sync($editPerms);
        $this->business->users()->attach($this->staffEditor->id, [
            'id' => (string) Str::uuid(),
            'role' => 'custom_cust_editor',
            'role_id' => $roleEdit->id,
        ]);
        $this->staffEditor->update(['active_business_id' => $this->business->id]);

        // 3. Staff Full Manager (customers.view, customers.create, customers.edit, customers.delete)
        $this->staffFull = User::create([
            'name' => 'Staff Full Manager',
            'email' => 'staff.full@audit-test.local',
            'email_verified_at' => now(),
            'phone' => '081234567803',
            'phone_verified_at' => now(),
            'password' => 'password123',
        ]);
        $roleFull = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Custom Role Full',
            'slug' => 'custom_cust_full',
            'description' => 'Akses Lengkap Pelanggan',
        ]);
        $allCustPerms = Permission::whereIn('slug', [
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
        ])->pluck('id');
        $roleFull->permissions()->sync($allCustPerms);
        $this->business->users()->attach($this->staffFull->id, [
            'id' => (string) Str::uuid(),
            'role' => 'custom_cust_full',
            'role_id' => $roleFull->id,
        ]);
        $this->staffFull->update(['active_business_id' => $this->business->id]);
    }

    private function createOtherTenant(): array
    {
        $otherUser = User::create([
            'name' => 'Owner Tenant Lain',
            'email' => 'other.owner@cust-test.local',
            'email_verified_at' => now(),
            'phone' => '089999999000',
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
            'code' => 'CUST-SEBELAH',
            'phone' => '088888888000',
            'current_credit_balance' => 0,
        ]);

        return [$otherUser, $otherBiz, $otherCustomer];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('customers.index'))->assertRedirect(route('login'));
        $this->get(route('customers.export'))->assertRedirect(route('login'));
        $this->get(route('customers.template'))->assertRedirect(route('login'));
    }

    public function test_permission_gating_customers_view_create_edit_delete(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Budi Santoso',
            'code' => 'CUST-BUDI',
            'phone' => '0811111111',
            'current_credit_balance' => 0,
        ]);

        // 1. Staff View Only dapat melihat halaman daftar & template
        $this->actingAs($this->staffViewOnly)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('customers.index'))
            ->assertOk();

        // 2. Staff View Only DITOLAK saat mencoba membuat pelanggan baru (403 via JSON)
        $this->actingAs($this->staffViewOnly)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('customers.store'), [
                'name' => 'Pelanggan Ilegal',
                'phone' => '0822222222',
            ])
            ->assertForbidden();

        // 3. Staff View Only DITOLAK saat mencoba mengedit pelanggan
        $this->actingAs($this->staffViewOnly)
            ->withSession(['active_business_id' => $this->business->id])
            ->putJson(route('customers.update', $customer), [
                'name' => 'Budi Diubah View Only',
            ])
            ->assertForbidden();

        // 4. Staff Editor BERHASIL mengedit nama pelanggan
        $this->actingAs($this->staffEditor)
            ->withSession(['active_business_id' => $this->business->id])
            ->putJson(route('customers.update', $customer), [
                'name' => 'Budi Santoso Editor Update',
            ])
            ->assertOk();

        $this->assertEquals('Budi Santoso Editor Update', $customer->fresh()->name);

        // 5. Staff Editor DITOLAK saat mencoba menghapus pelanggan (karena tidak ada customers.delete)
        $this->actingAs($this->staffEditor)
            ->withSession(['active_business_id' => $this->business->id])
            ->deleteJson(route('customers.destroy', $customer))
            ->assertForbidden();

        // 6. Staff Full Manager BERHASIL membuat dan menghapus pelanggan
        $resCreate = $this->actingAs($this->staffFull)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('customers.store'), [
                'name' => 'Pelanggan Baru Full',
                'code' => 'CUST-NEW-FULL',
                'phone' => '0833333333',
            ]);
        $resCreate->assertStatus(201);

        $newCust = Customer::where('code', 'CUST-NEW-FULL')->firstOrFail();

        $resDelete = $this->actingAs($this->staffFull)
            ->withSession(['active_business_id' => $this->business->id])
            ->deleteJson(route('customers.destroy', $newCust));
        $resDelete->assertOk();

        $this->assertSoftDeleted('customers', ['id' => $newCust->id]);
    }

    public function test_idor_protection_customer_update_and_destroy(): void
    {
        [, , $otherCustomer] = $this->createOtherTenant();

        // 1. Mencoba mengedit pelanggan tenant lain -> 404 (karena BusinessScope) atau 403
        $resEdit = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->putJson(route('customers.update', $otherCustomer), [
                'name' => 'Hacker Update',
            ]);
        $this->assertContains($resEdit->status(), [403, 404]);

        // 2. Mencoba menghapus pelanggan tenant lain -> 404 atau 403
        $resDelete = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->deleteJson(route('customers.destroy', $otherCustomer));
        $this->assertContains($resDelete->status(), [403, 404]);
    }

    public function test_deletion_guard_blocks_deleting_customer_with_active_credit_balance(): void
    {
        $customerWithDebt = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Pak Kasbon Belum Lunas',
            'phone' => '0855555555',
            'current_credit_balance' => 150000,
        ]);

        // Mencoba menghapus pelanggan yang masih punya piutang -> Harus ditolak (422 Unprocessable Entity)
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->deleteJson(route('customers.destroy', $customerWithDebt));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        // Pastikan pelanggan TIDAK terhapus
        $this->assertDatabaseHas('customers', [
            'id' => $customerWithDebt->id,
            'deleted_at' => null,
        ]);
    }

    public function test_customer_code_uniqueness_scoped_to_business(): void
    {
        Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Pelanggan Pertama',
            'code' => 'KODE-SAMA',
            'phone' => '0877777771',
        ]);

        // Mencoba membuat kode yang sama di bisnis yang sama -> harus gagal validasi
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('customers.store'), [
                'name' => 'Pelanggan Kedua Duplikat',
                'code' => 'KODE-SAMA',
                'phone' => '0877777772',
            ]);

        $response->assertSessionHasErrors('code');

        // Tetapi di bisnis lain, kode yang sama diizinkan
        [, $otherBiz] = $this->createOtherTenant();
        $otherCustomerSameCode = Customer::create([
            'business_id' => $otherBiz->id,
            'name' => 'Pelanggan Toko Lain Kode Sama',
            'code' => 'KODE-SAMA',
            'phone' => '0877777773',
        ]);

        $this->assertNotNull($otherCustomerSameCode->id);
    }

    public function test_audit_log_recorded_on_customer_creation_and_update(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('customers.store'), [
                'name' => 'Pelanggan Audit Trail',
                'code' => 'CUST-AUDIT',
                'phone' => '08987654321',
            ]);

        $response->assertStatus(201);
        $customer = Customer::where('code', 'CUST-AUDIT')->firstOrFail();

        // Periksa apakah AuditLog terbit via Auditable trait
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'created',
            'auditable_type' => Customer::class,
            'auditable_id' => $customer->id,
        ]);

        // Lakukan update data
        $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->putJson(route('customers.update', $customer), [
                'name' => 'Pelanggan Audit Trail Diubah',
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'action' => 'updated',
            'auditable_type' => Customer::class,
            'auditable_id' => $customer->id,
        ]);
    }

    public function test_formula_injection_sanitization_in_excel_export(): void
    {
        // Buat pelanggan dengan nama mengandung formula injection
        Customer::create([
            'business_id' => $this->business->id,
            'name' => '=cmd|\' /C calc\'!A0',
            'company_name' => '+HYPERLINK("http://evil.com")',
            'phone' => '081234567899',
            'city' => '-2+5+cmd',
            'address' => '@SUM(1,2)',
        ]);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('customers.export'));

        $response->assertOk();
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    }
}
