<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BillingPackage;
use App\Models\Business;
use App\Models\PaymentSettlement;
use App\Models\PlatformChartOfAccount;
use App\Models\PlatformJournalEntry;
use App\Models\PlatformTransaction;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\AdminRbacSeeder;
use Database\Seeders\PlatformCoaSeeder;
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
        $this->seed(PlatformCoaSeeder::class);
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

    public function test_super_admin_and_finance_admin_can_access_finance_hub_and_all_tabs(): void
    {
        $superAdmin = $this->createAdmin('super_admin');
        $financeAdmin = $this->createAdmin('finance_admin');
        $supportAdmin = $this->createAdmin('support_admin');

        // Cashflow Tab
        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.finance.index', ['tab' => 'cashflow']))
            ->assertStatus(200)
            ->assertSee('Buku Kas &amp; Akuntansi Platform', false);

        // General Journals Tab
        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.finance.index', ['tab' => 'journals']))
            ->assertStatus(200)
            ->assertSee('Jurnal Umum Berpasangan', false);

        // General Ledger Tab
        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.finance.index', ['tab' => 'ledger']))
            ->assertStatus(200)
            ->assertSee('Buku Besar Akun', false);

        // Trial Balance Tab
        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.finance.index', ['tab' => 'trial_balance']))
            ->assertStatus(200)
            ->assertSee('Neraca Saldo', false);

        // Balance Sheet & PnL Tab
        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.finance.index', ['tab' => 'balance_sheet']))
            ->assertStatus(200)
            ->assertSee('Laporan Keuangan Platform Resmi', false);

        // Chart of Accounts Tab
        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.finance.index', ['tab' => 'coa']))
            ->assertStatus(200)
            ->assertSee('Bagan Akun Perkiraan', false);

        // Support admin has no admin.finance.view permission
        $this->actingAs($supportAdmin, 'admin')
            ->get(route('admin.finance.index'))
            ->assertStatus(403);
    }

    public function test_can_record_manual_transaction_with_optional_image_attachment_and_auto_journal(): void
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

        $response->assertRedirect(route('admin.finance.index', ['tab' => 'cashflow']));
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

        // Verify automatic double-entry journal was generated and balanced
        $this->assertNotNull($trx->journal_entry_id);
        $journal = PlatformJournalEntry::findOrFail($trx->journal_entry_id);
        $this->assertEquals(750000, $journal->total_debit);
        $this->assertEquals(750000, $journal->total_credit);
        $this->assertTrue($journal->is_balanced);
        $this->assertEquals(2, $journal->lines()->count());
    }

    public function test_can_record_manual_double_entry_journal(): void
    {
        $financeAdmin = $this->createAdmin('finance_admin');

        $kasAccount = PlatformChartOfAccount::where('code', '1120')->firstOrFail();
        $modalAccount = PlatformChartOfAccount::where('code', '3110')->firstOrFail();

        // Balanced journal entry: Debit Kas Rp 5.000.000, Kredit Modal Rp 5.000.000
        $response = $this->actingAs($financeAdmin, 'admin')
            ->post(route('admin.finance.journals.store'), [
                'entry_date' => '2026-10-25',
                'description' => 'Suntikan modal operasional tambahan dari pendiri',
                'lines' => [
                    [
                        'account_id' => $kasAccount->id,
                        'type' => 'debit',
                        'amount' => 5000000,
                        'notes' => 'Penerimaan rekening giro BCA',
                    ],
                    [
                        'account_id' => $modalAccount->id,
                        'type' => 'credit',
                        'amount' => 5000000,
                        'notes' => 'Penyertaan modal pendiri',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.finance.index', ['tab' => 'journals']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('platform_journal_entries', [
            'description' => 'Suntikan modal operasional tambahan dari pendiri',
            'total_debit' => 5000000,
            'total_credit' => 5000000,
            'is_balanced' => 1,
        ]);
    }

    public function test_unbalanced_manual_journal_is_rejected(): void
    {
        $financeAdmin = $this->createAdmin('finance_admin');

        $kasAccount = PlatformChartOfAccount::where('code', '1120')->firstOrFail();
        $modalAccount = PlatformChartOfAccount::where('code', '3110')->firstOrFail();

        // Unbalanced journal: Debit 5.000.000 vs Kredit 4.000.000
        $response = $this->actingAs($financeAdmin, 'admin')
            ->post(route('admin.finance.journals.store'), [
                'entry_date' => '2026-10-25',
                'description' => 'Jurnal cacat tidak seimbang',
                'lines' => [
                    [
                        'account_id' => $kasAccount->id,
                        'type' => 'debit',
                        'amount' => 5000000,
                    ],
                    [
                        'account_id' => $modalAccount->id,
                        'type' => 'credit',
                        'amount' => 4000000,
                    ],
                ],
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('platform_journal_entries', [
            'description' => 'Jurnal cacat tidak seimbang',
        ]);
    }

    public function test_can_manage_chart_of_accounts(): void
    {
        $superAdmin = $this->createAdmin('super_admin');

        // 1. Create custom account
        $response = $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.finance.accounts.store'), [
                'code' => '1199',
                'name' => 'Kas Kecil Petugas Cabang',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'description' => 'Dana kas kecil operasional petty cash',
            ]);

        $response->assertRedirect(route('admin.finance.index', ['tab' => 'coa']));
        $this->assertDatabaseHas('platform_chart_of_accounts', [
            'code' => '1199',
            'name' => 'Kas Kecil Petugas Cabang',
            'is_system' => 0,
            'is_active' => 1,
        ]);

        $acc = PlatformChartOfAccount::where('code', '1199')->firstOrFail();

        // 2. Update account
        $this->actingAs($superAdmin, 'admin')
            ->put(route('admin.finance.accounts.update', $acc), [
                'name' => 'Kas Petty Cash Kantor',
                'normal_balance' => 'debit',
                'description' => 'Diperbarui',
            ])->assertRedirect(route('admin.finance.index', ['tab' => 'coa']));

        $this->assertDatabaseHas('platform_chart_of_accounts', [
            'id' => $acc->id,
            'name' => 'Kas Petty Cash Kantor',
        ]);

        // 3. Toggle account active status
        $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.finance.accounts.toggle', $acc))
            ->assertRedirect(route('admin.finance.index', ['tab' => 'coa']));

        $this->assertFalse($acc->fresh()->is_active);
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

        $response->assertRedirect(route('admin.finance.index', ['tab' => 'cashflow']));
        $this->assertDatabaseHas('platform_transactions', [
            'id' => $trx->id,
            'amount' => 620000,
            'title' => 'Listrik kantor penyesuaian akhir bulan',
        ]);
    }

    public function test_can_soft_delete_manual_transaction_and_cascade_journal(): void
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

        // Create journal
        app(\App\Services\PlatformAccountingService::class)->syncTransactionJournal($trx);
        $journalId = $trx->fresh()->journal_entry_id;
        $this->assertNotNull($journalId);

        $response = $this->actingAs($financeAdmin, 'admin')
            ->delete(route('admin.finance.destroy', $trx));

        $response->assertRedirect(route('admin.finance.index'));
        $this->assertSoftDeleted('platform_transactions', [
            'id' => $trx->id,
        ]);
        $this->assertSoftDeleted('platform_journal_entries', [
            'id' => $journalId,
        ]);
    }

    public function test_sync_integrates_billing_subscriptions_and_merchant_settlements_with_journals(): void
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

        // Verify journals created for both
        $subTrx = PlatformTransaction::where('source_id', $subscription->id)->firstOrFail();
        $this->assertNotNull($subTrx->journal_entry_id);
        $this->assertTrue($subTrx->journalEntry->is_balanced);

        $setTrx = PlatformTransaction::where('source_id', $settlement->id)->firstOrFail();
        $this->assertNotNull($setTrx->journal_entry_id);
        $this->assertTrue($setTrx->journalEntry->is_balanced);
    }

    public function test_can_export_csv_ledger_and_trial_balance(): void
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

        // 1. Export Cashflow
        $response = $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.finance.export', ['tab' => 'cashflow']));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type') ?? '');
        $this->assertStringContainsString('laporan_keuangan_platform_', $response->headers->get('Content-Disposition') ?? '');

        // 2. Export Trial Balance
        $responseTb = $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.finance.export', ['tab' => 'trial_balance']));

        $responseTb->assertStatus(200);
        $this->assertStringContainsString('text/csv', $responseTb->headers->get('Content-Type') ?? '');
    }
}
