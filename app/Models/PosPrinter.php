<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosPrinter extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_LAN = 'lan';
    public const TYPE_WIFI = 'wifi';
    public const TYPE_USB = 'usb';
    public const TYPE_BLUETOOTH = 'bluetooth';
    public const TYPE_WINDOWS = 'windows';
    public const TYPE_SERIAL = 'serial';
    public const TYPE_AGENT = 'agent';

    public const USAGE_CASHIER_RECEIPT = 'cashier_receipt';
    public const USAGE_KITCHEN_ORDER = 'kitchen_order';
    public const USAGE_BAR_ORDER = 'bar_order';
    public const USAGE_PASTRY_ORDER = 'pastry_order';
    public const USAGE_SPK_SERVICE = 'spk_service';
    public const USAGE_PACKING_SLIP = 'packing_slip';
    public const USAGE_BILL_SUMMARY = 'bill_summary';
    public const USAGE_SHIFT_REPORT = 'shift_report';
    public const USAGE_INVOICE_OFFICE = 'invoice_office';
    public const USAGE_LABEL = 'label';

    public const STATION_CASHIER = 'cashier';
    public const STATION_KITCHEN = 'kitchen';
    public const STATION_BAR = 'bar';
    public const STATION_PASTRY = 'pastry';
    public const STATION_PACKING = 'packing';
    public const STATION_SPK = 'spk_service';
    public const STATION_OFFICE = 'office';
    public const STATION_CHECKER = 'checker';

    public const CAP_PRINT_TEXT = 'print_text';
    public const CAP_PRINT_IMAGE = 'print_image';
    public const CAP_PRINT_LOGO = 'print_logo';
    public const CAP_BARCODE = 'barcode';
    public const CAP_QR_CODE = 'qr_code';
    public const CAP_CUT = 'cut';
    public const CAP_CASH_DRAWER = 'cash_drawer';
    public const CAP_OPEN_DRAWER = 'open_drawer';
    public const CAP_BEEP = 'beep';

    protected $fillable = [
        'business_id',
        'location_id',
        'name',
        'alias',
        'station',
        'fallback_printer_id',
        'connection_type',
        'interface_address',
        'port',
        'paper_width',
        'copies',
        'auto_cut',
        'open_cash_drawer',
        'print_density',
        'paper_type',
        'character_set',
        'is_active',
        'is_default',
        'capabilities',
        'assigned_usages',
        'assigned_category_ids',
        'last_status',
        'last_status_checked_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'copies' => 'integer',
            'auto_cut' => 'boolean',
            'open_cash_drawer' => 'boolean',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'capabilities' => 'array',
            'assigned_usages' => 'array',
            'assigned_category_ids' => 'array',
            'last_status_checked_at' => 'datetime',
        ];
    }

    /**
     * Location relationship.
     *
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Print jobs relationship.
     *
     * @return HasMany<PosPrintJob, $this>
     */
    public function printJobs(): HasMany
    {
        return $this->hasMany(PosPrintJob::class, 'printer_id');
    }

    /**
     * Fallback printer relationship (auto failover when printer is offline).
     *
     * @return BelongsTo<PosPrinter, $this>
     */
    public function fallbackPrinter(): BelongsTo
    {
        return $this->belongsTo(PosPrinter::class, 'fallback_printer_id');
    }

    /**
     * Routing rules relationship.
     *
     * @return HasMany<PosPrinterRoutingRule, $this>
     */
    public function routingRules(): HasMany
    {
        return $this->hasMany(PosPrinterRoutingRule::class, 'printer_id');
    }

    /**
     * Resolve effective fallback printer when offline.
     */
    public function resolveEffectiveFallback(): ?self
    {
        if ($this->fallback_printer_id) {
            $fallback = $this->fallbackPrinter;
            if ($fallback && $fallback->is_active && $fallback->id !== $this->id) {
                return $fallback;
            }
        }

        // Default to active cashier printer of same location or business
        if ($this->station !== self::STATION_CASHIER) {
            return self::where('business_id', $this->business_id)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('station', self::STATION_CASHIER)
                      ->orWhereJsonContains('assigned_usages', self::USAGE_CASHIER_RECEIPT)
                      ->orWhere('is_default', true);
                })
                ->where('id', '!=', $this->id)
                ->first();
        }

        return null;
    }

    public function isCashier(): bool
    {
        return $this->station === self::STATION_CASHIER || $this->supportsUsage(self::USAGE_CASHIER_RECEIPT);
    }

    public function isKitchen(): bool
    {
        return $this->station === self::STATION_KITCHEN || $this->supportsUsage(self::USAGE_KITCHEN_ORDER);
    }

    public function isBar(): bool
    {
        return $this->station === self::STATION_BAR || $this->supportsUsage(self::USAGE_BAR_ORDER);
    }

    public function isSpk(): bool
    {
        return $this->station === self::STATION_SPK || $this->supportsUsage(self::USAGE_SPK_SERVICE);
    }

    /**
     * Check if printer supports specific capability.
     */
    public function hasCapability(string $capability): bool
    {
        $caps = $this->capabilities ?? [
            self::CAP_PRINT_TEXT,
            self::CAP_BARCODE,
            self::CAP_QR_CODE,
            self::CAP_CUT,
            self::CAP_CASH_DRAWER,
        ];

        return in_array($capability, $caps, true);
    }

    /**
     * Check if printer is assigned for specific usage.
     */
    public function supportsUsage(string $usage): bool
    {
        $usages = $this->assigned_usages ?? [self::USAGE_CASHIER_RECEIPT];
        return in_array($usage, $usages, true);
    }

    /**
     * Check if printer is assigned for a given product category ID (KDS/KOT routing).
     */
    public function supportsCategory(?string $categoryId): bool
    {
        if ($categoryId === null) {
            return false;
        }

        $cats = $this->assigned_category_ids ?? [];
        if (empty($cats)) {
            return true; // If no specific categories filtered, accept all
        }

        return in_array($categoryId, $cats, true);
    }

    /**
     * Get maximum text character columns based on paper width.
     */
    public function getMaxColumns(): int
    {
        return $this->paper_width === '58mm' ? 32 : 48;
    }

    /**
     * Scope for active printers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific location / outlet.
     */
    public function scopeForLocation($query, ?string $locationId)
    {
        if ($locationId) {
            return $query->where(function ($q) use ($locationId) {
                $q->where('location_id', $locationId)
                  ->orWhereNull('location_id');
            });
        }

        return $query;
    }
}
