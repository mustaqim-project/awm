<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceProductMapping extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_SYNCED = 'synced';
    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DISABLED = 'disabled';

    protected $fillable = [
        'business_id',
        'product_id',
        'marketplace_account_id',
        'channel',
        'external_product_id',
        'external_sku_id',
        'external_sku_code',
        'external_product_name',
        'channel_price',
        'price_multiplier',
        'sync_price_auto',
        'channel_stock',
        'custom_stock',
        'stock_buffer',
        'sync_stock_auto',
        'is_active',
        'sync_status',
        'last_price_synced_at',
        'last_stock_synced_at',
        'last_sync_error',
        'raw_metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel_price'        => 'float',
            'price_multiplier'     => 'float',
            'sync_price_auto'      => 'boolean',
            'channel_stock'        => 'integer',
            'custom_stock'         => 'integer',
            'stock_buffer'         => 'integer',
            'sync_stock_auto'      => 'boolean',
            'is_active'            => 'boolean',
            'last_price_synced_at' => 'datetime',
            'last_stock_synced_at' => 'datetime',
            'raw_metadata'         => 'array',
        ];
    }

    /**
     * Get effective price for this channel.
     * If auto sync is on or channel_price is null, fallback to COOCA product's selling price with multiplier.
     */
    public function getEffectivePrice(): float
    {
        if (! $this->sync_price_auto && $this->channel_price !== null && $this->channel_price > 0) {
            return (float) $this->channel_price;
        }

        $basePrice = (float) ($this->product?->selling_price ?? 0);
        $multiplier = (float) ($this->price_multiplier ?? $this->account?->price_multiplier ?? 1.00);

        return round($basePrice * $multiplier, 2);
    }

    /**
     * Get effective stock allocated to this marketplace channel after safety buffer subtraction.
     */
    public function getEffectiveStock(?int $physicalStock = null): int
    {
        if (! $this->sync_stock_auto && $this->custom_stock !== null) {
            return max(0, (int) $this->custom_stock);
        }

        $stock = $physicalStock !== null ? $physicalStock : (int) ($this->product?->stock ?? 0);
        $buffer = (int) ($this->stock_buffer ?? 0);

        return max(0, $stock - $buffer);
    }

    /**
     * Check if the effective channel price is below the base cost (HPP).
     */
    public function isBelowCost(): bool
    {
        $baseCost = (float) ($this->product?->base_cost ?? 0);
        return $baseCost > 0 && $this->getEffectivePrice() < $baseCost;
    }

    /**
     * Calculate nominal loss per unit if price is below base cost (HPP).
     */
    public function getCostDeficit(): float
    {
        $baseCost = (float) ($this->product?->base_cost ?? 0);
        $effectivePrice = $this->getEffectivePrice();
        return max(0.0, $baseCost - $effectivePrice);
    }

    /**
     * Check if margin becomes negative after marketplace platform fee deductions.
     */
    public function isMarginNegativeAfterFees(float $feeRate = 0.08): bool
    {
        $baseCost = (float) ($this->product?->base_cost ?? 0);
        if ($baseCost <= 0) {
            return false;
        }
        $netPayout = $this->getEffectivePrice() * (1 - $feeRate);
        return $netPayout < $baseCost;
    }

    /**
     * Calculate estimated net margin after marketplace platform fees.
     */
    public function getNetMargin(float $feeRate = 0.08): float
    {
        $effectivePrice = $this->getEffectivePrice();
        $adminFee = $effectivePrice * $feeRate;
        $baseCost = (float) ($this->product?->base_cost ?? 0);
        return ($effectivePrice - $adminFee) - $baseCost;
    }

    /**
     * Calculate estimated net margin percentage.
     */
    public function getMarginPercent(float $feeRate = 0.08): float
    {
        $effectivePrice = $this->getEffectivePrice();
        if ($effectivePrice <= 0) {
            return 0.0;
        }
        return round(($this->getNetMargin($feeRate) / $effectivePrice) * 100, 1);
    }

    public function getChannelLabel(): string
    {
        return match ($this->channel) {
            MarketplaceAccount::CHANNEL_SHOPEE   => 'Shopee',
            MarketplaceAccount::CHANNEL_TIKTOK   => 'TikTok Shop',
            MarketplaceAccount::CHANNEL_TOKOPEDIA => 'Tokopedia',
            default                              => ucfirst((string) $this->channel),
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
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<MarketplaceAccount, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class, 'marketplace_account_id');
    }
}
