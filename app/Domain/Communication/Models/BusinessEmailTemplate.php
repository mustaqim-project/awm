<?php

declare(strict_types=1);

namespace App\Domain\Communication\Models;

use App\Models\Business;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BusinessEmailTemplate
 *
 * Template email yang dapat dikustomisasi isinya oleh user per bisnis.
 *
 * @property string $id
 * @property string $business_id
 * @property string $template_key
 * @property string $subject
 * @property string $body_html
 * @property bool $is_active
 */
class BusinessEmailTemplate extends Model
{
    use HasFactory, HasUuid, BelongsToBusiness;

    public const KEY_QUOTATION_SENT = 'quotation_sent';
    public const KEY_INVOICE_SENT = 'invoice_sent';
    public const KEY_INVOICE_REMINDER_UPCOMING = 'invoice_reminder_upcoming';
    public const KEY_INVOICE_REMINDER_DUE = 'invoice_reminder_due';
    public const KEY_INVOICE_REMINDER_OVERDUE = 'invoice_reminder_overdue';
    public const KEY_PURCHASE_ORDER_SENT = 'purchase_order_sent';

    protected $table = 'business_email_templates';

    protected $fillable = [
        'business_id',
        'template_key',
        'subject',
        'body_html',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
