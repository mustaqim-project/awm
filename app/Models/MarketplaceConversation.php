<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceConversation extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $fillable = [
        'business_id',
        'marketplace_account_id',
        'channel',
        'shop_id',
        'shop_cipher',
        'external_conversation_id',
        'participant_id',
        'participant_name',
        'participant_avatar',
        'unread_count',
        'last_message_content',
        'last_message_type',
        'last_message_at',
        'status',
        'raw_metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unread_count'    => 'integer',
            'last_message_at' => 'datetime',
            'raw_metadata'    => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class, 'marketplace_account_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MarketplaceMessage::class, 'marketplace_conversation_id')->orderBy('marketplace_created_at', 'asc');
    }
}
