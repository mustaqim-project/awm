<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Marketplace\MarketplaceFinanceService;
use App\Domain\Marketplace\MarketplaceOrderService;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\JournalEntry;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceSettlement;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MarketplaceFinancialIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private AutoJournalService $journalService;
    private MarketplaceFinanceService $financeService;
    private MarketplaceOrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner Marketplace Finance',
            'email'             => 'owner_mp_fin_' . Str::random(6) . '@cooca.test',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'     => 'COOCA Marketplace Omnichannel Store',
            'currency' => 'IDR',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->journalService = app(AutoJournalService::class);
        $this->financeService = app(MarketplaceFinanceService::class);
        $this->orderService   = app(MarketplaceOrderService::class);
    }

    public function test_multi_account_creation_and_isolation(): void
    {
        // 1. Shopee Toko Retail
        $shopeeRetail = MarketplaceAccount::create([
            'business_id'  => $this->business->id,
            'channel'      => MarketplaceAccount::CHANNEL_SHOPEE,
            'shop_id'      => 'shp_retail_101',
            'shop_name'    => 'Shopee Toko Retail',
            'status'       => MarketplaceAccount::STATUS_CONNECTED,
            'access_token' => 'dummy_token_shopee_retail',
        ]);

        // 2. Shopee Toko Grosir
        $shopeeGrosir = MarketplaceAccount::create([
            'business_id'  => $this->business->id,
            'channel'      => MarketplaceAccount::CHANNEL_SHOPEE,
            'shop_id'      => 'shp_grosir_102',
            'shop_name'    => 'Shopee Toko Grosir',
            'status'       => MarketplaceAccount::STATUS_CONNECTED,
            'access_token' => 'dummy_token_shopee_grosir',
        ]);

        // 3. TikTok Shop Official
        $tiktokOfficial = MarketplaceAccount::create([
            'business_id'  => $this->business->id,
            'channel'      => MarketplaceAccount::CHANNEL_TIKTOK,
            'shop_id'      => 'tts_official_201',
            'shop_name'    => 'TikTok Shop Official',
            'status'       => MarketplaceAccount::STATUS_CONNECTED,
            'access_token' => 'dummy_token_tiktok',
        ]);

        $this->assertCount(3, MarketplaceAccount::where('business_id', $this->business->id)->get());
        $this->assertEquals('Shopee Toko Retail', $shopeeRetail->shop_name);
        $this->assertEquals('Shopee Toko Grosir', $shopeeGrosir->shop_name);
        $this->assertEquals('TikTok Shop Official', $tiktokOfficial->shop_name);
    }

    public function test_marketplace_order_ingestion_with_itemized_financials_and_net_calculation(): void
    {
        $account = MarketplaceAccount::create([
            'business_id'  => $this->business->id,
            'channel'      => MarketplaceAccount::CHANNEL_SHOPEE,
            'shop_id'      => 'shp_fin_001',
            'shop_name'    => 'Toko Berkah Shopee',
            'status'       => MarketplaceAccount::STATUS_CONNECTED,
            'access_token' => 'valid_token',
        ]);

        $orderData = [
            'order_id'              => '241115SHP999',
            'order_sn'              => '241115SHP999',
            'buyer_name'            => 'Budi Santoso',
            'buyer_phone'           => '081299998888',
            'gross_amount'          => 200000.0,
            'seller_discount'       => 10000.0,
            'platform_discount'     => 15000.0,
            'total_amount'          => 190000.0,
            'commission_fee'        => 12000.0,
            'service_fee'           => 6000.0,
            'transaction_fee'       => 4000.0,
            'shipping_fee_buyer'    => 18000.0,
            'shipping_fee_seller'   => 0.0,
            'shipping_subsidy'      => 10000.0,
            'status'                => 'PROCESSED',
            'items'                 => [
                ['name' => 'Kemeja Batik Premium', 'quantity' => 2, 'price' => 100000.0, 'subtotal' => 200000.0],
            ],
        ];

        $order = $this->orderService->upsertMarketplaceOrder($account, $orderData);

        $this->assertInstanceOf(MarketplaceOrder::class, $order);
        $this->assertEquals(200000.0, $order->gross_amount);
        $this->assertEquals(10000.0, $order->seller_discount);
        $this->assertEquals(22000.0, $order->getTotalFees()); // 12000 + 6000 + 4000
        
        // Expected Net: Gross (200k) - Seller Disc (10k) - Fees (22k) + Subsidy (10k) = 178k
        $this->assertEquals(178000.0, $order->getCalculatedNetSettlement());
    }

    public function test_double_entry_auto_journal_for_marketplace_order_and_escrow(): void
    {
        $account = MarketplaceAccount::create([
            'business_id'  => $this->business->id,
            'channel'      => MarketplaceAccount::CHANNEL_SHOPEE,
            'shop_id'      => 'shp_journal_test',
            'shop_name'    => 'Shopee Journal Store',
            'status'       => MarketplaceAccount::STATUS_CONNECTED,
            'access_token' => 'valid_token',
        ]);

        $order = MarketplaceOrder::create([
            'business_id'            => $this->business->id,
            'marketplace_account_id' => $account->id,
            'channel'                => 'shopee',
            'external_order_id'      => 'ORD-JRN-001',
            'external_order_sn'      => 'ORD-JRN-001',
            'buyer_name'             => 'Dewi Lestari',
            'gross_amount'           => 300000.0,
            'seller_discount'        => 20000.0,
            'platform_discount'      => 0.0,
            'total_amount'           => 280000.0,
            'commission_fee'         => 18000.0,
            'service_fee'            => 9000.0,
            'transaction_fee'        => 6000.0,
            'net_settlement_amount'  => 247000.0,
            'order_status'           => 'COMPLETED',
            'settlement_status'      => MarketplaceOrder::SETTLEMENT_SETTLED,
            'placed_at'              => now(),
            'settled_at'             => now(),
        ]);

        // 1. Trigger Order Journal (Phase 1)
        $orderJournal = $this->journalService->recordMarketplaceOrderJournal($order);

        $this->assertNotNull($orderJournal);
        $this->assertEquals($orderJournal->id, $order->fresh()->journal_entry_id);
        $this->assertEquals(300000.0, $orderJournal->total_debit);
        $this->assertEquals(300000.0, $orderJournal->total_credit);
        $this->assertEquals($orderJournal->total_debit, $orderJournal->total_credit, 'Order journal must strictly balance (Debit == Credit).');

        // Check Debit Piutang Marketplace (1-1007)
        $piutangLine = $orderJournal->lines()->whereHas('account', fn($q) => $q->where('code', '1-1007'))->first();
        $this->assertNotNull($piutangLine);
        $this->assertEquals(280000.0, (float) $piutangLine->amount);
        $this->assertEquals('debit', $piutangLine->type);

        // Check Credit Pendapatan Penjualan Marketplace (4-4004)
        $revenueLine = $orderJournal->lines()->whereHas('account', fn($q) => $q->where('code', '4-4004'))->first();
        $this->assertNotNull($revenueLine);
        $this->assertEquals(300000.0, (float) $revenueLine->amount);
        $this->assertEquals('credit', $revenueLine->type);

        // 2. Trigger Escrow Settlement Journal (Phase 2)
        $settleJournal = $this->journalService->recordMarketplaceEscrowSettlementJournal($order);

        $this->assertNotNull($settleJournal);
        $this->assertEquals($settleJournal->id, $order->fresh()->settlement_journal_entry_id);
        $this->assertEquals(33000.0, $settleJournal->total_debit); // 18k + 9k + 6k
        $this->assertEquals(33000.0, $settleJournal->total_credit);
        $this->assertEquals($settleJournal->total_debit, $settleJournal->total_credit, 'Settlement journal must strictly balance.');
    }

    public function test_marketplace_payout_disbursement_journal_and_bank_reconciliation(): void
    {
        $account = MarketplaceAccount::create([
            'business_id'  => $this->business->id,
            'channel'      => MarketplaceAccount::CHANNEL_TIKTOK,
            'shop_id'      => 'tts_payout_test',
            'shop_name'    => 'TikTok Payout Store',
            'status'       => MarketplaceAccount::STATUS_CONNECTED,
            'access_token' => 'valid_token',
        ]);

        $bankAccount = CashAccount::create([
            'business_id'    => $this->business->id,
            'name'           => 'BCA Bisnis Utama',
            'type'           => 'bank',
            'account_number' => '1234567890',
            'is_active'      => true,
        ]);

        $settlement = MarketplaceSettlement::create([
            'business_id'            => $this->business->id,
            'marketplace_account_id' => $account->id,
            'channel'                => 'tiktok_shop',
            'settlement_sn'          => 'TTS-PAYOUT-BATCH-888',
            'payout_date'            => now(),
            'total_orders_count'     => 5,
            'gross_sales'            => 1000000.0,
            'total_fees'             => 100000.0,
            'net_payout_amount'      => 900000.0,
            'bank_name'              => 'BCA',
            'bank_account_number'    => '1234567890',
            'status'                 => MarketplaceSettlement::STATUS_COMPLETED,
        ]);

        $payoutJournal = $this->journalService->recordMarketplacePayoutJournal($settlement, $bankAccount);

        $this->assertNotNull($payoutJournal);
        $this->assertEquals($payoutJournal->id, $settlement->fresh()->journal_entry_id);
        $this->assertEquals(900000.0, $payoutJournal->total_debit);
        $this->assertEquals(900000.0, $payoutJournal->total_credit);
        $this->assertEquals($payoutJournal->total_debit, $payoutJournal->total_credit);

        // Debit: Bank, Credit: Piutang Marketplace
        $debitBank = $payoutJournal->lines()->where('type', 'debit')->first();
        $creditPiutang = $payoutJournal->lines()->where('type', 'credit')->first();

        $this->assertEquals(900000.0, (float) $debitBank->amount);
        $this->assertEquals(900000.0, (float) $creditPiutang->amount);
    }

    public function test_marketplace_finance_service_summary_and_aggregation(): void
    {
        $account = MarketplaceAccount::create([
            'business_id'  => $this->business->id,
            'channel'      => MarketplaceAccount::CHANNEL_SHOPEE,
            'shop_id'      => 'shp_summary_test',
            'shop_name'    => 'Shopee Summary Store',
            'status'       => MarketplaceAccount::STATUS_CONNECTED,
            'access_token' => 'valid_token',
        ]);

        MarketplaceOrder::create([
            'business_id'            => $this->business->id,
            'marketplace_account_id' => $account->id,
            'channel'                => 'shopee',
            'external_order_id'      => 'ORD-SUM-01',
            'gross_amount'           => 500000.0,
            'seller_discount'        => 25000.0,
            'commission_fee'         => 30000.0,
            'service_fee'            => 15000.0,
            'transaction_fee'        => 10000.0,
            'net_settlement_amount'  => 420000.0,
            'order_status'           => 'COMPLETED',
            'settlement_status'      => MarketplaceOrder::SETTLEMENT_SETTLED,
            'placed_at'              => now(),
            'settled_at'             => now(),
        ]);

        MarketplaceOrder::create([
            'business_id'            => $this->business->id,
            'marketplace_account_id' => $account->id,
            'channel'                => 'shopee',
            'external_order_id'      => 'ORD-SUM-02',
            'gross_amount'           => 300000.0,
            'seller_discount'        => 0.0,
            'commission_fee'         => 18000.0,
            'service_fee'            => 9000.0,
            'transaction_fee'        => 6000.0,
            'net_settlement_amount'  => 267000.0,
            'order_status'           => 'READY_TO_SHIP',
            'settlement_status'      => MarketplaceOrder::SETTLEMENT_ESCROW_HOLDING,
            'placed_at'              => now(),
        ]);

        $summary = $this->financeService->getFinancialSummary($this->business);

        $this->assertEquals(2, $summary['total_orders']);
        $this->assertEquals(800000.0, $summary['gross_sales']);
        $this->assertEquals(25000.0, $summary['seller_discounts']);
        $this->assertEquals(88000.0, $summary['total_marketplace_fees']); // (30+15+10) + (18+9+6)
        $this->assertEquals(687000.0, $summary['net_settlement']); // 420k + 267k
        $this->assertEquals(267000.0, $summary['escrow_holding']); // ORD-SUM-02
        $this->assertEquals(420000.0, $summary['disbursed_amount']); // ORD-SUM-01
    }

    public function test_journal_creation_is_idempotent(): void
    {
        $account = MarketplaceAccount::create([
            'business_id'  => $this->business->id,
            'channel'      => MarketplaceAccount::CHANNEL_SHOPEE,
            'shop_id'      => 'shp_idempotent',
            'shop_name'    => 'Shopee Idempotent Store',
            'status'       => MarketplaceAccount::STATUS_CONNECTED,
            'access_token' => 'valid_token',
        ]);

        $order = MarketplaceOrder::create([
            'business_id'            => $this->business->id,
            'marketplace_account_id' => $account->id,
            'channel'                => 'shopee',
            'external_order_id'      => 'ORD-IDEMP-01',
            'gross_amount'           => 150000.0,
            'seller_discount'        => 0.0,
            'total_amount'           => 150000.0,
            'commission_fee'         => 9000.0,
            'net_settlement_amount'  => 141000.0,
            'order_status'           => 'COMPLETED',
            'settlement_status'      => MarketplaceOrder::SETTLEMENT_SETTLED,
            'placed_at'              => now(),
        ]);

        // Call twice
        $journal1 = $this->journalService->recordMarketplaceOrderJournal($order);
        $journal2 = $this->journalService->recordMarketplaceOrderJournal($order);

        $this->assertEquals($journal1->id, $journal2->id);
        $this->assertEquals(1, JournalEntry::where('reference_type', JournalEntry::REF_MARKETPLACE_ORDER)->where('reference_id', $order->id)->count());
    }
}
