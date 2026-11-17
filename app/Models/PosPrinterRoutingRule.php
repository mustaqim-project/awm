<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosPrinterRoutingRule extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const DOC_CUSTOMER_RECEIPT = 'customer_receipt';
    public const DOC_KITCHEN_TICKET = 'kitchen_ticket';
    public const DOC_BAR_TICKET = 'bar_ticket';
    public const DOC_PASTRY_TICKET = 'pastry_ticket';
    public const DOC_SPK_SERVICE = 'spk_service';
    public const DOC_PACKING_SLIP = 'packing_slip';
    public const DOC_BILL_SUMMARY = 'bill_summary';
    public const DOC_SHIFT_REPORT = 'shift_report';
    public const DOC_INVOICE_OFFICE = 'invoice_office';

    public const DEPT_FOOD = 'food';
    public const DEPT_BEVERAGE = 'beverage';
    public const DEPT_SERVICE = 'service';
    public const DEPT_SPAREPART = 'sparepart';
    public const DEPT_RETAIL = 'retail';
    public const DEPT_GENERAL = 'general';

    protected $fillable = [
        'business_id',
        'location_id',
        'pos_register_id',
        'printer_id',
        'fallback_printer_id',
        'document_type',
        'category_id',
        'department',
        'order_type',
        'copies',
        'is_active',
        'priority',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'copies' => 'integer',
            'is_active' => 'boolean',
            'priority' => 'integer',
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
     * Register relationship.
     *
     * @return BelongsTo<PosRegister, $this>
     */
    public function register(): BelongsTo
    {
        return $this->belongsTo(PosRegister::class, 'pos_register_id');
    }

    /**
     * Target printer relationship.
     *
     * @return BelongsTo<PosPrinter, $this>
     */
    public function printer(): BelongsTo
    {
        return $this->belongsTo(PosPrinter::class, 'printer_id');
    }

    /**
     * Fallback printer relationship.
     *
     * @return BelongsTo<PosPrinter, $this>
     */
    public function fallbackPrinter(): BelongsTo
    {
        return $this->belongsTo(PosPrinter::class, 'fallback_printer_id');
    }

    /**
     * Product category relationship.
     *
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Scope for active rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific document type.
     */
    public function scopeForDocument($query, string $docType)
    {
        return $query->where('document_type', $docType);
    }
}
