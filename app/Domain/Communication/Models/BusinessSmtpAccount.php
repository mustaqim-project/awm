<?php

declare(strict_types=1);

namespace App\Domain\Communication\Models;

use App\Models\Business;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

/**
 * Class BusinessSmtpAccount
 *
 * Akun SMTP dan IMAP kustom per bisnis untuk pengiriman penawaran, penagihan, dan PO,
 * serta deteksi email masuk secara mandiri per tenant.
 *
 * @property string $id
 * @property string $business_id
 * @property string $channel_type // default, quotation, invoice, purchase_order
 * @property bool $is_active
 * @property string $mail_driver
 * @property string $mail_host
 * @property int $mail_port
 * @property string|null $mail_encryption
 * @property string $mail_username
 * @property string $mail_password
 * @property string $from_name
 * @property string $from_address
 * @property string|null $reply_to_address
 * @property bool $enable_inbound_reading
 * @property string|null $imap_host
 * @property int $imap_port
 * @property string|null $imap_encryption
 * @property string|null $imap_username
 * @property string|null $imap_password
 * @property \Carbon\Carbon|null $last_inbound_check_at
 * @property string|null $last_status
 * @property string|null $last_error_message
 * @property \Carbon\Carbon|null $last_tested_at
 */
class BusinessSmtpAccount extends Model
{
    use HasFactory, HasUuid, BelongsToBusiness;

    public const CHANNEL_DEFAULT = 'default';
    public const CHANNEL_QUOTATION = 'quotation';
    public const CHANNEL_INVOICE = 'invoice';
    public const CHANNEL_PURCHASE_ORDER = 'purchase_order';

    protected $table = 'business_smtp_accounts';

    protected $fillable = [
        'business_id',
        'channel_type',
        'is_active',
        'mail_driver',
        'mail_host',
        'mail_port',
        'mail_encryption',
        'mail_username',
        'mail_password',
        'from_name',
        'from_address',
        'reply_to_address',
        'enable_inbound_reading',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_username',
        'imap_password',
        'last_inbound_check_at',
        'last_status',
        'last_error_message',
        'last_tested_at',
    ];

    protected $hidden = [
        'mail_password',
        'imap_password',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mail_port' => 'integer',
            'imap_port' => 'integer',
            'is_active' => 'boolean',
            'enable_inbound_reading' => 'boolean',
            'last_inbound_check_at' => 'datetime',
            'last_tested_at' => 'datetime',
        ];
    }

    public function setMailPasswordAttribute($value): void
    {
        if (! empty($value)) {
            $this->attributes['mail_password'] = Crypt::encryptString((string) $value);
        }
    }

    public function getMailPasswordAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function setImapPasswordAttribute($value): void
    {
        if (! empty($value)) {
            $this->attributes['imap_password'] = Crypt::encryptString((string) $value);
        } else {
            $this->attributes['imap_password'] = null;
        }
    }

    public function getImapPasswordAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function inboundEmails(): HasMany
    {
        return $this->hasMany(InboundEmail::class, 'business_smtp_account_id');
    }
}
