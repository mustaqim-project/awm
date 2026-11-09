<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\PlatformChartOfAccount;
use App\Models\PlatformJournalEntry;
use App\Models\PlatformTransaction;
use App\Models\SubscriptionPayment;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\PlatformAccountingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFinancePlatformTaxTest extends TestCase
{
    use RefreshDatabase;

    private Admin $superAdmin;
    private Business $business;
    private User $user;
    private EntitlementService $entitlementService;
    private PlatformAccountingService $accountingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\AdminRbacSeeder::class);
        $this->seed(\Database\Seeders\PlatformCoaSeeder::class);

        $this->superAdmin = Admin::factory()->create([
            'email' => 'finance-admin@cooca.id',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Mitra Sejahtera Owner',
            'email' => 'owner@mitrasejahtera.id',
            'password' => \Illuminate\Support\Facades\Hash::make('secret123'),
        ]);

        $this->business = Business::create([
            'name' => 'PT Mitra Sejahtera UMKM',
            'slug' => 'mitra-sejahtera-umkm',
        ]);
        $this->business->users()->attach($this->user->id, [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'role' => 'owner',
            'is_primary' => true,
        ]);

        $this->entitlementService = app(EntitlementService::class);
        $this->accountingService = app(PlatformAccountingService::class);
    }

    public function test_admin_can_update_platform_tax_settings_and_persist_to_system_setting(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.finance.tax-settings.update'), [
                'platform_tax_enabled' => '1',
                'platform_tax_rate' => '11.0',
                'platform_tax_inclusive' => '0',
                'platform_tax_name' => 'PPN 11%',
                'platform_tax_apply_subscription' => '1',
                'platform_tax_apply_topup' => '1',
                'platform_tax_company_npwp' => '01.999.888.7-012.000',
                'platform_tax_company_name' => 'PT Cooca Digital Solusindo',
            ]);

        $response->assertRedirect(route('admin.finance.index', ['tab' => 'tax']));
        $response->assertSessionHas('success');

        $this->assertSame('1', SystemSetting::get('platform_tax_enabled'));
        $this->assertSame('11.0', SystemSetting::get('platform_tax_rate'));
        $this->assertSame('0', SystemSetting::get('platform_tax_inclusive'));
        $this->assertSame('PPN 11%', SystemSetting::get('platform_tax_name'));
        $this->assertSame('1', SystemSetting::get('platform_tax_apply_subscription'));
        $this->assertSame('1', SystemSetting::get('platform_tax_apply_topup'));
        $this->assertSame('01.999.888.7-012.000', SystemSetting::get('platform_tax_company_npwp'));
    }

    public function test_subscription_order_calculates_exclusive_ppn_when_tax_enabled(): void
    {
        SystemSetting::set('platform_tax_enabled', '1');
        SystemSetting::set('platform_tax_rate', '11.0');
        SystemSetting::set('platform_tax_inclusive', '0');
        SystemSetting::set('platform_tax_apply_subscription', '1');

        $taxCalc = $this->entitlementService->calculatePlatformTax(100000.0, 'subscription');

        $this->assertEquals(11.0, $taxCalc['tax_rate']);
        $this->assertEquals(11000.0, $taxCalc['tax_amount']);
        $this->assertEquals(100000.0, $taxCalc['subtotal']);
        $this->assertEquals(111000.0, $taxCalc['total_payable']);
        $this->assertFalse($taxCalc['is_inclusive']);

        $order = $this->entitlementService->createPaymentOrder(
            business: $this->business,
            user: $this->user,
            tier: BusinessSubscription::TIER_STANDARD,
            cycle: 'monthly',
            paymentMethod: SubscriptionPayment::METHOD_QRIS
        );

        $this->assertTrue($order->hasTax());
        $this->assertGreaterThan(0, (float) $order->tax_amount);
        $this->assertEquals($order->subtotal + $order->tax_amount + $order->unique_code, $order->total_payable);
    }

    public function test_subscription_order_calculates_inclusive_ppn_when_tax_inclusive(): void
    {
        SystemSetting::set('platform_tax_enabled', '1');
        SystemSetting::set('platform_tax_rate', '11.0');
        SystemSetting::set('platform_tax_inclusive', '1');
        SystemSetting::set('platform_tax_apply_subscription', '1');

        $grossPrice = 111000.0;
        $taxCalc = $this->entitlementService->calculatePlatformTax($grossPrice, 'subscription');

        $this->assertEquals(11.0, $taxCalc['tax_rate']);
        $this->assertEquals(100000.0, $taxCalc['subtotal']);
        $this->assertEquals(11000.0, $taxCalc['tax_amount']);
        $this->assertEquals(111000.0, $taxCalc['total_payable']);
        $this->assertTrue($taxCalc['is_inclusive']);
    }

    public function test_topup_order_calculates_ppn_when_tax_enabled(): void
    {
        SystemSetting::set('platform_tax_enabled', '1');
        SystemSetting::set('platform_tax_rate', '11.0');
        SystemSetting::set('platform_tax_inclusive', '0');
        SystemSetting::set('platform_tax_apply_topup', '1');
        SystemSetting::set('ai_token_topup_price', '50000');

        $topupOrder = $this->entitlementService->createTokenTopupOrder(
            business: $this->business,
            user: $this->user,
            paymentMethod: SubscriptionPayment::METHOD_QRIS
        );

        $this->assertTrue($topupOrder->hasTax());
        $this->assertEquals(50000.0, (float) $topupOrder->subtotal);
        $this->assertEquals(5500.0, (float) $topupOrder->tax_amount);
        $this->assertEquals(55500.0, (float) $topupOrder->total_payable);
    }

    public function test_sync_approved_subscription_with_tax_creates_balanced_double_entry_journal(): void
    {
        $payment = SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'SUB-202611-TEST01',
            'plan_code' => 'standard_monthly',
            'package_name' => 'Standard Monthly',
            'cycle' => 'monthly',
            'amount' => 100000.0,
            'subtotal' => 100000.0,
            'tax_rate' => 11.0,
            'tax_amount' => 11000.0,
            'is_tax_inclusive' => false,
            'unique_code' => 0,
            'total_payable' => 111000.0,
            'payment_method' => 'bca',
            'status' => SubscriptionPayment::STATUS_APPROVED,
            'approved_at' => Carbon::now(),
        ]);

        $trx = PlatformTransaction::create([
            'transaction_number' => 'TRX-202611-0001',
            'type' => PlatformTransaction::TYPE_IN,
            'category' => PlatformTransaction::CAT_SUBSCRIPTION_REVENUE,
            'title' => 'Langganan Standard (PT Mitra Sejahtera UMKM)',
            'amount' => 111000.0,
            'transaction_date' => Carbon::now()->toDateString(),
            'payment_method' => 'BCA',
            'source_type' => 'subscription_payment',
            'source_id' => $payment->id,
            'admin_id' => $this->superAdmin->id,
        ]);

        $journal = $this->accountingService->syncTransactionJournal($trx);

        $this->assertInstanceOf(PlatformJournalEntry::class, $journal);
        $this->assertTrue($journal->is_balanced);
        $this->assertEquals(111000.0, (float) $journal->total_debit);
        $this->assertEquals(111000.0, (float) $journal->total_credit);

        // Verify journal lines
        $lines = $journal->lines()->with('account')->get();
        $this->assertCount(3, $lines);

        $debitCash = $lines->firstWhere('type', 'debit');
        $this->assertSame('1120', $debitCash->account->code);
        $this->assertEquals(111000.0, (float) $debitCash->amount);

        $creditRevenue = $lines->where('type', 'credit')->firstWhere('account.code', '4110');
        $this->assertNotNull($creditRevenue);
        $this->assertEquals(100000.0, (float) $creditRevenue->amount);

        $creditVat = $lines->where('type', 'credit')->firstWhere('account.code', '2140');
        $this->assertNotNull($creditVat);
        $this->assertEquals(11000.0, (float) $creditVat->amount);
    }

    public function test_admin_can_record_tax_settlement_to_kas_negara(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.finance.tax-settle'), [
                'amount' => '110000',
                'payment_date' => Carbon::now()->toDateString(),
                'reference_number' => 'NTPN-8877665544332211',
                'notes' => 'Pelunasan SPT Masa PPN November 2026',
            ]);

        $response->assertRedirect(route('admin.finance.index', ['tab' => 'tax']));
        $response->assertSessionHas('success');

        $trx = PlatformTransaction::where('title', 'like', '%NTPN: NTPN-8877665544332211%')->first();
        $this->assertNotNull($trx);
        $this->assertEquals(110000.0, (float) $trx->amount);
        $this->assertSame(PlatformTransaction::TYPE_OUT, $trx->type);

        $journal = PlatformJournalEntry::find($trx->journal_entry_id);
        $this->assertNotNull($journal);
        $this->assertTrue($journal->is_balanced);

        $lines = $journal->lines()->with('account')->get();
        $debitVat = $lines->firstWhere('type', 'debit');
        $this->assertSame('2140', $debitVat->account->code);
        $this->assertEquals(110000.0, (float) $debitVat->amount);

        $creditCash = $lines->firstWhere('type', 'credit');
        $this->assertSame('1120', $creditCash->account->code);
        $this->assertEquals(110000.0, (float) $creditCash->amount);
    }

    public function test_admin_finance_dashboard_displays_platform_tax_summary(): void
    {
        SystemSetting::set('platform_tax_enabled', '1');
        SystemSetting::set('platform_tax_rate', '11.0');

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->get(route('admin.finance.index', ['tab' => 'tax']));

        $response->assertOk();
        $response->assertViewHas('platformTaxSummary');
        $response->assertSee('Pengaturan Pajak Pertambahan Nilai (PPN) Platform');
        $response->assertSee('Rekapitulasi SPT Masa PPN');
        $response->assertSee('Saldo Hutang PPN (COA 2140)');
    }
}
