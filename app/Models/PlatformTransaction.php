<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlatformTransaction extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';

    public const CAT_SUBSCRIPTION_REVENUE = 'subscription_revenue';
    public const CAT_AI_TOKEN_REVENUE     = 'ai_token_revenue';
    public const CAT_SETUP_FEE             = 'setup_fee';
    public const CAT_OTHER_INCOME          = 'other_income';

    public const CAT_MERCHANT_SETTLEMENT   = 'merchant_settlement';
    public const CAT_INFRASTRUCTURE        = 'infrastructure';
    public const CAT_AI_API_COST           = 'ai_api_cost';
    public const CAT_WHATSAPP_GATEWAY      = 'whatsapp_gateway';
    public const CAT_MARKETING             = 'marketing';
    public const CAT_SALARY                = 'salary';
    public const CAT_OPERATIONAL           = 'operational';
    public const CAT_OTHER_EXPENSE         = 'other_expense';

    protected $table = 'platform_transactions';

    protected $fillable = [
        'transaction_number',
        'type',
        'category',
        'title',
        'amount',
        'transaction_date',
        'payment_method',
        'notes',
        'attachment_path',
        'source_type',
        'source_id',
        'admin_id',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->transaction_number)) {
                $datePrefix = $model->transaction_date ? \Carbon\Carbon::parse($model->transaction_date)->format('Ym') : now()->format('Ym');
                $count = static::where('transaction_number', 'like', "TRX-{$datePrefix}-%")->count() + 1;
                $model->transaction_number = sprintf('TRX-%s-%04d', $datePrefix, $count);
            }
        });
    }

    /**
     * @var list<string>
     */
    protected $appends = [
        'attachment_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'transaction_date' => 'date',
        ];
    }

    /**
     * Get human-readable label dictionary for categories.
     *
     * @return array<string, string>
     */
    public static function categoryLabels(): array
    {
        return [
            self::CAT_SUBSCRIPTION_REVENUE => 'Pendapatan Langganan SaaS',
            self::CAT_AI_TOKEN_REVENUE     => 'Penjualan Kuota Token AI',
            self::CAT_SETUP_FEE             => 'Biaya Onboarding & Setup',
            self::CAT_OTHER_INCOME          => 'Penerimaan Kas Lainnya',

            self::CAT_MERCHANT_SETTLEMENT   => 'Pencairan Saldo Merchant',
            self::CAT_INFRASTRUCTURE        => 'Server, VPS & Cloud Hosting',
            self::CAT_AI_API_COST           => 'Biaya API LLM (Gemini / Claude)',
            self::CAT_WHATSAPP_GATEWAY      => 'Meta WhatsApp Cloud API',
            self::CAT_MARKETING             => 'Pemasaran & Kampanye Iklan',
            self::CAT_SALARY                => 'Gaji & Insentif Tim Internal',
            self::CAT_OPERATIONAL           => 'Operasional & Legalitas Platform',
            self::CAT_OTHER_EXPENSE         => 'Pengeluaran Kas Lainnya',
        ];
    }

    /**
     * Check if transaction is cash inflow (kas masuk).
     */
    public function isInflow(): bool
    {
        return $this->type === self::TYPE_IN;
    }

    /**
     * Check if transaction is cash outflow (kas keluar).
     */
    public function isOutflow(): bool
    {
        return $this->type === self::TYPE_OUT;
    }

    /**
     * Optional attachment image URL.
     */
    protected function attachmentUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attachment_path ? asset('storage/' . $this->attachment_path) : null,
        );
    }

    /**
     * Responsible administrator.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Associated subscription payment (if synced).
     */
    public function subscriptionPayment(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPayment::class, 'source_id');
    }

    /**
     * Associated merchant payment settlement (if synced).
     */
    public function paymentSettlement(): BelongsTo
    {
        return $this->belongsTo(PaymentSettlement::class, 'source_id');
    }
}
