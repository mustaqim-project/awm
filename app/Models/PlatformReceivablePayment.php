<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PlatformReceivablePayment extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'platform_receivable_payments';

    protected $fillable = [
        'receivable_id',
        'payment_number',
        'payment_date',
        'amount',
        'payment_method',
        'account_id',
        'attachment_path',
        'reference_number',
        'notes',
        'journal_entry_id',
        'admin_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $payment): void {
            $payment->receivable?->recalculateBalances();
        });

        static::deleted(function (self $payment): void {
            $payment->receivable?->recalculateBalances();
        });
    }

    public function receivable(): BelongsTo
    {
        return $this->belongsTo(PlatformReceivable::class, 'receivable_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(PlatformChartOfAccount::class, 'account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(PlatformJournalEntry::class, 'journal_entry_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function attachmentUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->attachment_path) {
                    return null;
                }
                if (filter_var($this->attachment_path, FILTER_VALIDATE_URL)) {
                    return $this->attachment_path;
                }
                return Storage::disk('public')->url($this->attachment_path);
            }
        );
    }
}
