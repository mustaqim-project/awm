<?php

declare(strict_types=1);

namespace App\Domain\Marketplace;

use App\Domain\Accounting\AutoJournalService;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceSettlement;
use App\Models\MarketplaceSyncLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarketplaceFinanceService
{
    public function __construct(
        protected MarketplaceManagerService $manager,
        protected AutoJournalService $journalService
    ) {}

    /**
     * Compute aggregated financial metrics across marketplace channels and accounts.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getFinancialSummary(Business $business, array $filters = []): array
    {
        $query = MarketplaceOrder::where('business_id', $business->id);

        $this->applyFilters($query, $filters);

        $orders = (clone $query)->get();

        $grossSales = 0.0;
        $sellerDiscounts = 0.0;
        $platformDiscounts = 0.0;
        $commissionFees = 0.0;
        $serviceFees = 0.0;
        $transactionFees = 0.0;
        $shippingSeller = 0.0;
        $shippingSubsidies = 0.0;
        $refunds = 0.0;
        $adjustments = 0.0;
        $netSettlement = 0.0;
        $totalItemsSold = 0;
        $escrowHolding = 0.0;
        $disbursedAmount = 0.0;

        foreach ($orders as $ord) {
            $gross = (float) $ord->gross_amount > 0 ? (float) $ord->gross_amount : (float) $ord->total_amount;
            $sDisc = (float) $ord->seller_discount;
            $pDisc = (float) $ord->platform_discount;
            $comm  = (float) $ord->commission_fee;
            $serv  = (float) $ord->service_fee;
            $trans = (float) $ord->transaction_fee;

            // Fallback if fees are aggregated in channel_fee
            if (($comm + $serv + $trans) <= 0 && (float) $ord->channel_fee > 0) {
                $comm = (float) $ord->channel_fee;
            }

            $shipS = (float) $ord->shipping_fee_seller;
            $shipSub = (float) $ord->shipping_subsidy;
            $ref = (float) $ord->refund_amount;
            $adj = (float) $ord->adjustment_amount;

            $net = $ord->getCalculatedNetSettlement();

            $grossSales += $gross;
            $sellerDiscounts += $sDisc;
            $platformDiscounts += $pDisc;
            $commissionFees += $comm;
            $serviceFees += $serv;
            $transactionFees += $trans;
            $shippingSeller += $shipS;
            $shippingSubsidies += $shipSub;
            $refunds += $ref;
            $adjustments += $adj;
            $netSettlement += $net;

            if (is_array($ord->items_summary)) {
                foreach ($ord->items_summary as $item) {
                    $totalItemsSold += (int) ($item['quantity'] ?? 1);
                }
            }

            if (in_array($ord->settlement_status, [MarketplaceOrder::SETTLEMENT_SETTLED, MarketplaceOrder::SETTLEMENT_PAYOUT_COMPLETED], true)) {
                $disbursedAmount += $net;
            } else {
                $escrowHolding += $net;
            }
        }

        $totalMarketplaceFees = $commissionFees + $serviceFees + $transactionFees;

        // Settled Payouts query
        $settlementQuery = MarketplaceSettlement::where('business_id', $business->id);
        if (! empty($filters['channel'])) {
            $settlementQuery->where('channel', $filters['channel']);
        }
        if (! empty($filters['marketplace_account_id'])) {
            $settlementQuery->where('marketplace_account_id', $filters['marketplace_account_id']);
        }
        $actualPayoutBatches = (clone $settlementQuery)->where('status', MarketplaceSettlement::STATUS_COMPLETED)->sum('net_payout_amount');

        return [
            'total_orders'           => $orders->count(),
            'total_items_sold'       => $totalItemsSold,
            'gross_sales'            => round($grossSales, 2),
            'seller_discounts'       => round($sellerDiscounts, 2),
            'platform_discounts'     => round($platformDiscounts, 2),
            'total_discounts'        => round($sellerDiscounts + $platformDiscounts, 2),
            'total_marketplace_fees' => round($totalMarketplaceFees, 2),
            'commission_fees'        => round($commissionFees, 2),
            'service_fees'           => round($serviceFees, 2),
            'transaction_fees'       => round($transactionFees, 2),
            'shipping_seller'        => round($shippingSeller, 2),
            'shipping_subsidies'     => round($shippingSubsidies, 2),
            'refunds'                => round($refunds, 2),
            'adjustments'            => round($adjustments, 2),
            'net_settlement'         => round($netSettlement, 2),
            'escrow_holding'         => round($escrowHolding, 2),
            'disbursed_amount'       => round($disbursedAmount, 2),
            'actual_bank_payouts'    => (float) $actualPayoutBatches,
            'reconciliation_diff'    => round(abs($disbursedAmount - (float) $actualPayoutBatches), 2),
        ];
    }

    /**
     * Compute financial metrics per channel (Shopee, TikTok Shop, Tokopedia).
     *
     * @param array<string, mixed> $filters
     * @return array<string, array<string, mixed>>
     */
    public function getChannelBreakdown(Business $business, array $filters = []): array
    {
        $channels = ['shopee', 'tiktok_shop', 'tokopedia'];
        $breakdown = [];

        foreach ($channels as $chan) {
            $chanFilters = array_merge($filters, ['channel' => $chan]);
            $breakdown[$chan] = $this->getFinancialSummary($business, $chanFilters);
        }

        return $breakdown;
    }

    /**
     * Sync financial and escrow data for a single marketplace order.
     */
    public function syncOrderFinancials(MarketplaceOrder $order): bool
    {
        $account = $order->account;
        if (! $account || ! $account->isConnected()) {
            return false;
        }

        try {
            $adapter = $this->manager->driver($account->channel);
            if (! method_exists($adapter, 'getEscrowDetail')) {
                return false;
            }

            $orderRef = $order->external_order_sn ?: $order->external_order_id;
            $details = $adapter->getEscrowDetail($account, $orderRef);

            if (empty($details)) {
                return false;
            }

            $order->gross_amount          = (float) ($details['gross_amount'] ?? $order->gross_amount);
            $order->seller_discount       = (float) ($details['seller_discount'] ?? $order->seller_discount);
            $order->platform_discount     = (float) ($details['platform_discount'] ?? $order->platform_discount);
            $order->voucher_code          = $details['voucher_code'] ?? $order->voucher_code;
            $order->voucher_amount        = (float) ($details['voucher_amount'] ?? $order->voucher_amount);
            $order->commission_fee        = (float) ($details['commission_fee'] ?? $order->commission_fee);
            $order->service_fee           = (float) ($details['service_fee'] ?? $order->service_fee);
            $order->transaction_fee       = (float) ($details['transaction_fee'] ?? $order->transaction_fee);
            $order->channel_fee           = (float) ($details['channel_fee'] ?? $order->channel_fee);
            $order->shipping_fee_buyer    = (float) ($details['shipping_fee_buyer'] ?? $order->shipping_fee_buyer);
            $order->shipping_fee_seller   = (float) ($details['shipping_fee_seller'] ?? $order->shipping_fee_seller);
            $order->shipping_subsidy      = (float) ($details['shipping_subsidy'] ?? $order->shipping_subsidy);
            $order->refund_amount         = (float) ($details['refund_amount'] ?? $order->refund_amount);
            $order->adjustment_amount     = (float) ($details['adjustment_amount'] ?? $order->adjustment_amount);
            $order->net_settlement_amount = (float) ($details['net_settlement_amount'] ?? $order->net_settlement_amount);

            if (! empty($details['settlement_status'])) {
                $order->settlement_status = (string) $details['settlement_status'];
            }
            if ($order->settlement_status === MarketplaceOrder::SETTLEMENT_SETTLED && ! $order->settled_at) {
                $order->settled_at = now();
            }

            $order->saveQuietly();

            // Auto-Journaling Trigger (Double Entry Bookkeeping)
            if (! $order->journal_entry_id) {
                $this->journalService->recordMarketplaceOrderJournal($order);
            }

            if ($order->settlement_status === MarketplaceOrder::SETTLEMENT_SETTLED && ! $order->settlement_journal_entry_id) {
                $this->journalService->recordMarketplaceEscrowSettlementJournal($order);
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning("syncOrderFinancials error for Order {$order->id}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Ingest settlement batches from a marketplace account and trigger double-entry accounting.
     *
     * @param array<string, mixed> $params
     * @return array<int, MarketplaceSettlement>
     */
    public function syncAccountSettlements(MarketplaceAccount $account, array $params = []): array
    {
        if (! $account->isConnected()) {
            return [];
        }

        $adapter = $this->manager->driver($account->channel);
        if (! method_exists($adapter, 'getSettlements')) {
            return [];
        }

        $rawSettlements = $adapter->getSettlements($account, $params);
        $savedBatches   = [];

        foreach ($rawSettlements as $raw) {
            $settleSn = (string) ($raw['settlement_sn'] ?? ('SETTLE-' . uniqid()));

            $settlement = MarketplaceSettlement::firstOrNew([
                'business_id'   => $account->business_id,
                'channel'       => $account->channel,
                'settlement_sn' => $settleSn,
            ]);

            $details = $raw['details'] ?? $raw;

            $settlement->marketplace_account_id = $account->id;
            $settlement->payout_date            = isset($raw['payout_date']) ? Carbon::parse($raw['payout_date']) : now();
            $settlement->gross_sales            = (float) ($details['gross_amount'] ?? ($raw['gross_sales'] ?? 0));
            $settlement->total_discounts        = (float) ($details['seller_discount'] ?? ($raw['total_discounts'] ?? 0));
            $settlement->total_fees             = (float) ($details['channel_fee'] ?? ($raw['total_fees'] ?? 0));
            $settlement->total_shipping_charges = (float) ($details['shipping_fee_seller'] ?? ($raw['total_shipping_charges'] ?? 0));
            $settlement->total_refunds          = (float) ($details['refund_amount'] ?? ($raw['total_refunds'] ?? 0));
            $settlement->total_adjustments      = (float) ($details['adjustment_amount'] ?? ($raw['total_adjustments'] ?? 0));
            $settlement->net_payout_amount      = (float) ($details['net_settlement_amount'] ?? ($raw['net_payout_amount'] ?? 0));
            $settlement->status                 = MarketplaceSettlement::STATUS_COMPLETED;
            $settlement->raw_payload            = $raw;
            $settlement->save();

            // Link matching order if order_sn is provided
            if (! empty($raw['order_sn'])) {
                $order = MarketplaceOrder::where('business_id', $account->business_id)
                    ->where('channel', $account->channel)
                    ->where('external_order_sn', (string) $raw['order_sn'])
                    ->first();

                if ($order) {
                    $order->marketplace_settlement_id = $settlement->id;
                    $order->settlement_status         = MarketplaceOrder::SETTLEMENT_PAYOUT_COMPLETED;
                    $order->saveQuietly();
                    $settlement->total_orders_count = 1;
                    $settlement->saveQuietly();
                }
            }

            // Post Payout Journal
            if (! $settlement->journal_entry_id) {
                $this->journalService->recordMarketplacePayoutJournal($settlement);
            }

            $savedBatches[] = $settlement;
        }

        MarketplaceSyncLog::log(
            businessId: $account->business_id,
            channel: $account->channel,
            entityType: 'settlement',
            action: 'pull_settlements',
            status: 'success',
            payload: ['count' => count($savedBatches)],
            marketplaceAccountId: $account->id
        );

        return $savedBatches;
    }

    /**
     * Apply date and filter parameters to query.
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }

        if (! empty($filters['marketplace_account_id'])) {
            $query->where('marketplace_account_id', $filters['marketplace_account_id']);
        }

        if (! empty($filters['settlement_status'])) {
            $query->where('settlement_status', $filters['settlement_status']);
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('placed_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('placed_at', '<=', $filters['end_date']);
        }
    }
}
