<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PlatformPayable extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    public const STATUS_UNPAID         = 'unpaid';
    public const STATUS_PARTIALLY_PAID = 'partially_paid';
    public const STATUS_PAID           = 'paid';
    public const STATUS_OVERDUE        = 'overdue';
    public const STATUS_CANCELLED      = 'cancelled';

    public const CAT_INFRASTRUCTURE    = 'infrastructure';
    public const CAT_AI_API            = 'ai_api';
    public const CAT_WHATSAPP_GATEWAY  = 'whatsapp_gateway';
    public const CAT_OFFICE_RENT       = 'office_rent';
    public const CAT_LEGAL_TAX         = 'legal_tax';
    public const CAT_SALARY            = 'salary';
    public const CAT_MARKETING         = 'marketing';
    public const CAT_OTHER             = 'other';

    protected $table = 'platform_payables';

    protected $fillable = [
        'bill_number',
        'vendor_name',
        'vendor_category',
        'reference_number',
        'bill_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'status',
        'title',
        'expense_account_id',
        'notes',
        'attachment_path',
        'journal_entry_id',
        'admin_id',
    ];

    protected $casts = [
        'bill_date'    => 'date',
        'due_date'     => 'date',
        'subtotal'     => 'decimal:2',
        'tax_amount'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'balance_due'  => 'decimal:2',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(PlatformPayablePayment::class, 'payable_id')->orderBy('payment_date', 'desc');
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(PlatformChartOfAccount::class, 'expense_account_id');
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

    public function getAgingBucketAttribute(): string
    {
        if ($this->status === self::STATUS_PAID || (float) $this->balance_due <= 0.01) {
            return 'paid';
        }

        $now = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($this->due_date)->startOfDay();

        if ($dueDate->gte($now)) {
            return 'current';
        }

        $days = $dueDate->diffInDays($now);

        if ($days <= 30) {
            return '1-30';
        }
        if ($days <= 60) {
            return '31-60';
        }
        if ($days <= 90) {
            return '61-90';
        }

        return '>90';
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === self::STATUS_PAID || (float) $this->balance_due <= 0.01) {
            return false;
        }

        return Carbon::parse($this->due_date)->startOfDay()->lt(Carbon::now()->startOfDay());
    }

    public function recalculateBalances(): void
    {
        $paid = (float) $this->payments()->sum('amount');
        $total = (float) $this->total_amount;
        $balance = max(0.00, $total - $paid);

        $this->paid_amount = $paid;
        $this->balance_due = $balance;

        if ($this->status !== self::STATUS_CANCELLED) {
            if ($balance <= 0.01) {
                $this->status = self::STATUS_PAID;
            } elseif ($paid > 0) {
                $this->status = self::STATUS_PARTIALLY_PAID;
            } elseif ($this->is_overdue) {
                $this->status = self::STATUS_OVERDUE;
            } else {
                $this->status = self::STATUS_UNPAID;
            }
        }

        $this->save();
    }
}
