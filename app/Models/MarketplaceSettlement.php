<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceSettlement extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid, SoftDeletes;

    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_RECONCILED = 'reconciled';
    public const STATUS_FAILED     = 'failed';

    protected $fillable = [
        'business_id',
        'marketplace_account_id',
        'channel',
        'settlement_sn',
        'payout_date',
        'total_orders_count',
        'gross_sales',
        'total_discounts',
        'total_fees',
        'total_shipping_charges',
        'total_refunds',
        'total_adjustments',
        'net_payout_amount',
        'bank_name',
        'bank_account_number',
        'status',
        'journal_entry_id',
        'cash_account_id',
        'reconciled_at',
        'raw_payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payout_date'            => 'datetime',
            'reconciled_at'          => 'datetime',
            'gross_sales'            => 'float',
            'total_discounts'        => 'float',
            'total_fees'             => 'float',
            'total_shipping_charges' => 'float',
            'total_refunds'          => 'float',
            'total_adjustments'      => 'float',
            'net_payout_amount'      => 'float',
            'total_orders_count'     => 'integer',
            'raw_payload'            => 'array',
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
     * @return BelongsTo<JournalEntry, $this>
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    /**
     * @return BelongsTo<CashAccount, $this>
     */
    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class, 'cash_account_id');
    }

    /**
     * @return HasMany<MarketplaceOrder, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(MarketplaceOrder::class, 'marketplace_settlement_id');
    }
}
