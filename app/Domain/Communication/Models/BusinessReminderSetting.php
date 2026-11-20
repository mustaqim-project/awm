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
 * Class BusinessReminderSetting
 *
 * Pengaturan otomatisasi pengingat tagihan faktur per bisnis.
 *
 * @property string $id
 * @property string $business_id
 * @property bool $auto_remind_invoices
 * @property array $channels
 * @property bool $remind_h_minus_3
 * @property bool $remind_due_date
 * @property bool $remind_h_plus_3
 * @property bool $remind_h_plus_7
 * @property bool $remind_weekly_overdue
 * @property int $preferred_send_hour
 */
class BusinessReminderSetting extends Model
{
    use HasFactory, HasUuid, BelongsToBusiness;

    protected $table = 'business_reminder_settings';

    protected $fillable = [
        'business_id',
        'auto_remind_invoices',
        'channels',
        'remind_h_minus_3',
        'remind_due_date',
        'remind_h_plus_3',
        'remind_h_plus_7',
        'remind_weekly_overdue',
        'preferred_send_hour',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'auto_remind_invoices' => 'boolean',
            'channels' => 'array',
            'remind_h_minus_3' => 'boolean',
            'remind_due_date' => 'boolean',
            'remind_h_plus_3' => 'boolean',
            'remind_h_plus_7' => 'boolean',
            'remind_weekly_overdue' => 'boolean',
            'preferred_send_hour' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
