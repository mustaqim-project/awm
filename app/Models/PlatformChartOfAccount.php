<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformChartOfAccount extends Model
{
    use HasFactory, HasUuid;

    public const TYPE_ASSET = 'asset';
    public const TYPE_LIABILITY = 'liability';
    public const TYPE_EQUITY = 'equity';
    public const TYPE_REVENUE = 'revenue';
    public const TYPE_EXPENSE = 'expense';

    public const BALANCE_DEBIT = 'debit';
    public const BALANCE_CREDIT = 'credit';

    protected $table = 'platform_chart_of_accounts';

    protected $fillable = [
        'code',
        'name',
        'type',
        'normal_balance',
        'category_group',
        'description',
        'is_system',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Type badge colors and labels for UI.
     */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_ASSET => 'Aset / Aktiva',
            self::TYPE_LIABILITY => 'Kewajiban / Hutang',
            self::TYPE_EQUITY => 'Ekuitas / Modal',
            self::TYPE_REVENUE => 'Pendapatan',
            self::TYPE_EXPENSE => 'Beban / Pengeluaran',
        ];
    }

    /**
     * Normal balance dictionary.
     */
    public static function defaultNormalBalance(string $type): string
    {
        return in_array($type, [self::TYPE_ASSET, self::TYPE_EXPENSE], true)
            ? self::BALANCE_DEBIT
            : self::BALANCE_CREDIT;
    }

    /**
     * Related journal entry lines.
     */
    public function journalLines(): HasMany
    {
        return $this->hasMany(PlatformJournalEntryLine::class, 'account_id');
    }

    /**
     * Calculate current net balance for this account.
     */
    public function calculateBalance(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->journalLines()
            ->whereHas('entry', function ($q) use ($startDate, $endDate): void {
                if ($startDate && $endDate) {
                    $q->whereBetween('entry_date', [$startDate, $endDate]);
                }
            });

        $totalDebit = (float) (clone $query)->where('type', 'debit')->sum('amount');
        $totalCredit = (float) (clone $query)->where('type', 'credit')->sum('amount');

        return $this->normal_balance === self::BALANCE_DEBIT
            ? $totalDebit - $totalCredit
            : $totalCredit - $totalDebit;
    }
}
