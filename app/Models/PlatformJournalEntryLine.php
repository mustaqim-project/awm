<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformJournalEntryLine extends Model
{
    use HasFactory, HasUuid;

    public const TYPE_DEBIT = 'debit';
    public const TYPE_CREDIT = 'credit';

    protected $table = 'platform_journal_entry_lines';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'type',
        'amount',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(PlatformJournalEntry::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(PlatformChartOfAccount::class, 'account_id');
    }

    public function isDebit(): bool
    {
        return $this->type === self::TYPE_DEBIT;
    }

    public function isCredit(): bool
    {
        return $this->type === self::TYPE_CREDIT;
    }
}
