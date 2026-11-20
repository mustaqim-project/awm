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
 * Class InboundEmail
 *
 * Catatan email masuk yang terdeteksi dari kotak masuk IMAP bisnis.
 *
 * @property string $id
 * @property string $business_id
 * @property string|null $business_smtp_account_id
 * @property string|null $message_id
 * @property string $from_email
 * @property string|null $from_name
 * @property string|null $to_email
 * @property string $subject
 * @property string|null $body_text
 * @property string|null $body_html
 * @property string|null $related_type
 * @property string|null $related_id
 * @property bool $has_attachments
 * @property array|null $attachments_info
 * @property string $processing_status
 * @property \Carbon\Carbon $received_at
 */
class InboundEmail extends Model
{
    use HasFactory, HasUuid, BelongsToBusiness;

    public const STATUS_RECEIVED = 'received';
    public const STATUS_MATCHED = 'matched';
    public const STATUS_ACTION_TAKEN = 'action_taken';
    public const STATUS_IGNORED = 'ignored';

    protected $table = 'inbound_emails';

    protected $fillable = [
        'business_id',
        'business_smtp_account_id',
        'message_id',
        'from_email',
        'from_name',
        'to_email',
        'subject',
        'body_text',
        'body_html',
        'related_type',
        'related_id',
        'has_attachments',
        'attachments_info',
        'processing_status',
        'received_at',
    ];

    protected $appends = [
        'from_address',
        'detected_document_type',
        'detected_document_id',
        'detected_document_number',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'has_attachments' => 'boolean',
            'attachments_info' => 'array',
            'received_at' => 'datetime',
        ];
    }

    public function getFromAddressAttribute(): string
    {
        return $this->from_email ?? '';
    }

    public function getDetectedDocumentTypeAttribute(): ?string
    {
        return $this->related_type;
    }

    public function getDetectedDocumentIdAttribute(): ?string
    {
        return $this->related_id;
    }

    public function getDetectedDocumentNumberAttribute(): ?string
    {
        if (! $this->related_type || ! $this->related_id) {
            return null;
        }

        return match ($this->related_type) {
            'quotation' => \App\Models\Quotation::find($this->related_id)?->quotation_number,
            'invoice' => \App\Models\Invoice::find($this->related_id)?->invoice_number,
            'purchase_order' => \App\Models\PurchaseOrder::find($this->related_id)?->po_number,
            default => null,
        };
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function smtpAccount(): BelongsTo
    {
        return $this->belongsTo(BusinessSmtpAccount::class, 'business_smtp_account_id');
    }
}
