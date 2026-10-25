<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BillingPackage;
use App\Models\Business;
use App\Models\PaymentSettlement;
use App\Models\PlatformTransaction;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\AdminRbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminFinanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(AdminRbacSeeder::class);
        Storage::fake('public');
    }

    private function createAdmin(string $role): Admin
    {
        return Admin::factory()->create([
            'name' => "Admin {$role}",
            'email' => "{$role}_" . uniqid() . "@example.com",
            'password' => Hash::make('password123'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_super_admin_and_finance_admin_can_access_finance_hub(): void
    {
        $superAdmin = $this->createAdmin('super_admin');
        $financeAdmin = $this->createAdmin('finance_admin');
        $supportAdmin = $this->createAdmin('support_admin');

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.finance.index'))
            ->assertStatus(200)
            ->assertSee('Buku Kas &amp; Akuntansi Platform', false);

        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.finance.index'))
            ->assertStatus(200);

        // Support admin has no admin.finance.view permission
        $this->actingAs($supportAdmin, 'admin')
            ->get(route('admin.finance.index'))
            ->assertStatus(403);
    }

    public function test_can_record_manual_transaction_with_optional_image_attachment(): void
    {
        $financeAdmin = $this->createAdmin('finance_admin');
        $file = UploadedFile::fake()->image('bukti_kuitansi.jpg', 600, 600);

        $response = $this->actingAs($financeAdmin, 'admin')
            ->post(route('admin.finance.store'), [
                'type' => 'out',
                'category' => 'operasional',
                'title' => 'Biaya server production bulanan',
                'amount' => 750000,
                'transaction_date' => '2026-10-25',
                'payment_method' => 'bank_transfer',
                'notes' => 'Disetujui oleh Direksi',
                'attachment' => $file,
            ]);

        $response->assertRedirect(route('admin.finance.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('platform_transactions', [
            'type' => 'out',
            'category' => 'operasional',
            'title' => 'Biaya server production bulanan',
            'amount' => 750000,
            'source_type' => 'manual',
            'admin_id' => $financeAdmin->id,
        ]);

        $trx = PlatformTransaction::where('category', 'operasional')->firstOrFail();
        $this->assertNotNull($trx->attachment_path);
        Storage::disk('public')->assertExists($trx->attachment_path);
        $this->assertNotNull($trx->attachment_url);
    }

    public function test_can_update_manual_transaction(): void
    {
        $financeAdmin = $this->createAdmin('finance_admin');
        $trx = PlatformTransaction::create([
            'type' => 'out',
            'category' => 'operasional',
            'title' => 'Listrik kantor',
            'amount' => 500000,
            'transaction_date' => '2026-10-25',
            'source_type' => 'manual',
            'admin_id' => $financeAdmin->id,
        ]);

        $response = $this->actingAs($financeAdmin, 'admin')
            ->put(route('admin.finance.update', $trx), [
                'type' => 'out',
                'category' => 'operasional',
                'title' => 'Listrik kantor penyesuaian akhir bulan',
                'amount' => 620000,
                'transaction_date' => '2026-10-25',
                'payment_method' => 'cash',
            ]);

        $response->assertRedirect(route('admin.finance.index'));
        $this->assertDatabaseHas('platform_transactions', [
            'id' => $trx->id,
            'amount' => 620000,
            'title' => 'Listrik kantor penyesuaian akhir bulan',
        ]);
    }

    public function test_can_soft_delete_manual_transaction(): void
    {
        $financeAdmin = $this->createAdmin('finance_admin');
        $trx = PlatformTransaction::create([
            'type' => 'in',
            'category' => 'other_income',
            'title' => 'Cashback gateway',
            'amount' => 200000,
            'transaction_date' => '2026-10-25',
            'source_type' => 'manual',
            'admin_id' => $financeAdmin->id,
        ]);

        $response = $this->actingAs($financeAdmin, 'admin')
            ->delete(route('admin.finance.destroy', $trx));

        $response->assertRedirect(route('admin.finance.index'));
        $this->assertSoftDeleted('platform_transactions', [
            'id' => $trx->id,
        ]);
    }

    public function test_sync_integrates_billing_subscriptions_and_merchant_settlements(): void
    {
        $superAdmin = $this->createAdmin('super_admin');

        // Create tenant business & user
        $user = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner_finance@example.com',
            'password' => Hash::make('password123'),
        ]);

        $business = Business::create([
            'name' => 'Warung Kopi Jaya',
            'slug' => 'warung-kopi-jaya',
        ]);
        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_primary' => true,
        ]);

        $package = BillingPackage::create([
            'type' => BillingPackage::TYPE_SUBSCRIPTION,
            'code' => 'SCALE_PRO',
            'name' => 'Paket Scale Pro',
            'price' => 299000,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        // 1. Approved billing subscription payment
        $subscription = SubscriptionPayment::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'order_number' => 'SUB-202610-0042',
            'payment_type' => 'subscription',
            'plan_code' => 'scale_pro',
            'cycle' => 'monthly',
            'billing_package_id' => $package->id,
            'plan_name' => 'Scale Pro',
            'amount' => 299000,
            'total_payable' => 299000,
            'payment_method' => 'bca_va',
            'status' => SubscriptionPayment::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $superAdmin->id,
            'payment_proof_path' => 'billing/proofs/trx_sub_1.jpg',
        ]);

        // 2. Completed merchant settlement
        $settlement = PaymentSettlement::create([
            'business_id' => $business->id,
            'settlement_number' => 'SET-202610-0012',
            'settlement_date' => now()->toDateString(),
            'payment_channel' => 'manual_transfer',
            'gross_amount' => 1500000,
            'fee_amount' => 5000,
            'net_amount' => 1495000,
            'status' => PaymentSettlement::STATUS_COMPLETED,
            'transferred_at' => now(),
            'destination_bank' => 'BCA - 1234567890 (Toko Barokah)',
            'proof_image_path' => 'settlements/proofs/trx_set_1.jpg',
        ]);

        // Trigger Sync
        $response = $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.finance.sync'));

        $response->assertRedirect(route('admin.finance.index'));
        $response->assertSessionHas('success');

        // Check subscription synced as Kas Masuk (in)
        $this->assertDatabaseHas('platform_transactions', [
            'type' => 'in',
            'category' => 'subscription_revenue',
            'source_type' => 'subscription_payment',
            'source_id' => $subscription->id,
            'amount' => 299000,
            'attachment_path' => 'billing/proofs/trx_sub_1.jpg',
        ]);

        // Check settlement synced as Kas Keluar (out)
        $this->assertDatabaseHas('platform_transactions', [
            'type' => 'out',
            'category' => 'merchant_settlement',
            'source_type' => 'payment_settlement',
            'source_id' => $settlement->id,
            'amount' => 1495000,
            'attachment_path' => 'settlements/proofs/trx_set_1.jpg',
        ]);

        // Test idempotency: sync again shouldn't duplicate
        $countBefore = PlatformTransaction::count();
        $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.finance.sync'));
        $this->assertEquals($countBefore, PlatformTransaction::count());
    }

    public function test_can_export_csv_ledger(): void
    {
        $financeAdmin = $this->createAdmin('finance_admin');

        PlatformTransaction::create([
            'type' => 'in',
            'category' => 'other_income',
            'title' => 'Pendapatan Bunga Bank',
            'amount' => 100000,
            'transaction_date' => '2026-10-25',
            'source_type' => 'manual',
            'admin_id' => $financeAdmin->id,
        ]);

        $response = $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.finance.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type') ?? '');
        $this->assertStringContainsString('laporan_keuangan_platform_', $response->headers->get('Content-Disposition') ?? '');
    }
}
