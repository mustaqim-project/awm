<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlatformJournalEntry extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    public const REF_SUBSCRIPTION = 'subscription_payment';
    public const REF_SETTLEMENT   = 'payment_settlement';
    public const REF_TRANSACTION  = 'platform_transaction';
    public const REF_MANUAL       = 'manual_journal';

    protected $table = 'platform_journal_entries';

    protected $fillable = [
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'total_debit',
        'total_credit',
        'is_balanced',
        'admin_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'total_debit' => 'float',
            'total_credit' => 'float',
            'is_balanced' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->entry_number)) {
                $datePrefix = $model->entry_date ? Carbon::parse($model->entry_date)->format('Ym') : now()->format('Ym');
                $count = static::where('entry_number', 'like', "JRN-{$datePrefix}-%")->count() + 1;
                $model->entry_number = sprintf('JRN-%s-%04d', $datePrefix, $count);
            }
        });
    }

    /**
     * Responsible admin.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Related journal entry lines (Debit / Credit).
     */
    public function lines(): HasMany
    {
        return $this->hasMany(PlatformJournalEntryLine::class, 'journal_entry_id');
    }

    /**
     * Check if total debit equals total credit.
     */
    public function verifyBalance(): bool
    {
        $this->total_debit = (float) $this->lines()->where('type', 'debit')->sum('amount');
        $this->total_credit = (float) $this->lines()->where('type', 'credit')->sum('amount');
        $this->is_balanced = abs($this->total_debit - $this->total_credit) < 0.01;
        $this->save();

        return $this->is_balanced;
    }
}
