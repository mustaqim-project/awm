<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceOrder extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const SETTLEMENT_PENDING           = 'pending';
    public const SETTLEMENT_ESCROW_HOLDING     = 'escrow_holding';
    public const SETTLEMENT_SETTLED            = 'settled';
    public const SETTLEMENT_PAYOUT_COMPLETED   = 'payout_completed';
    public const SETTLEMENT_DISPUTED           = 'disputed';
    public const SETTLEMENT_REFUNDED           = 'refunded';

    protected $fillable = [
        'business_id',
        'marketplace_account_id',
        'channel',
        'external_order_id',
        'external_order_sn',
        'buyer_name',
        'buyer_phone',
        'gross_amount',
        'seller_discount',
        'platform_discount',
        'voucher_code',
        'voucher_amount',
        'total_amount',
        'channel_fee',
        'commission_fee',
        'service_fee',
        'transaction_fee',
        'shipping_fee_buyer',
        'shipping_fee_seller',
        'shipping_subsidy',
        'refund_amount',
        'adjustment_amount',
        'net_settlement_amount',
        'settlement_status',
        'settled_at',
        'payout_reference',
        'marketplace_settlement_id',
        'journal_entry_id',
        'settlement_journal_entry_id',
        'order_status',
        'items_summary',
        'shipping_provider',
        'tracking_number',
        'synced_order_id',
        'raw_payload',
        'placed_at',
        'synced_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gross_amount'          => 'float',
            'seller_discount'       => 'float',
            'platform_discount'     => 'float',
            'voucher_amount'        => 'float',
            'total_amount'          => 'float',
            'channel_fee'           => 'float',
            'commission_fee'        => 'float',
            'service_fee'           => 'float',
            'transaction_fee'       => 'float',
            'shipping_fee_buyer'    => 'float',
            'shipping_fee_seller'   => 'float',
            'shipping_subsidy'      => 'float',
            'refund_amount'         => 'float',
            'adjustment_amount'     => 'float',
            'net_settlement_amount' => 'float',
            'items_summary'         => 'array',
            'raw_payload'           => 'array',
            'placed_at'             => 'datetime',
            'synced_at'             => 'datetime',
            'settled_at'            => 'datetime',
        ];
    }

    public function getChannelLabel(): string
    {
        return match ($this->channel) {
            'shopee'       => 'Shopee',
            'tiktok_shop'  => 'TikTok Shop',
            'tokopedia'    => 'Tokopedia',
            default        => ucfirst((string) $this->channel),
        };
    }

    public function getTotalFees(): float
    {
        $sum = (float) $this->commission_fee + (float) $this->service_fee + (float) $this->transaction_fee;
        if ($sum <= 0 && (float) $this->channel_fee > 0) {
            return (float) $this->channel_fee;
        }
        return $sum;
    }

    public function getCalculatedNetSettlement(): float
    {
        if ((float) $this->net_settlement_amount > 0) {
            return (float) $this->net_settlement_amount;
        }

        $gross = (float) $this->gross_amount > 0 ? (float) $this->gross_amount : (float) $this->total_amount;
        $sellerDisc = (float) $this->seller_discount;
        $fees = $this->getTotalFees();
        $refund = (float) $this->refund_amount;
        $adjustments = (float) $this->adjustment_amount;
        $subsidy = (float) $this->shipping_subsidy;

        return max(0.0, ($gross - $sellerDisc - $fees - $refund + $adjustments + $subsidy));
    }

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * @return BelongsTo<MarketplaceAccount, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class, 'marketplace_account_id');
    }

    /**
     * @return BelongsTo<MarketplaceSettlement, $this>
     */
    public function settlement(): BelongsTo
    {
        return $this->belongsTo(MarketplaceSettlement::class, 'marketplace_settlement_id');
    }

    /**
     * @return BelongsTo<JournalEntry, $this>
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    /**
     * @return BelongsTo<JournalEntry, $this>
     */
    public function settlementJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'settlement_journal_entry_id');
    }
}
