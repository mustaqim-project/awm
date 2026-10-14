<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceMessage extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'marketplace_conversation_id',
        'external_message_id',
        'sender_role',
        'sender_user_id',
        'message_type',
        'raw_content',
        'content_text',
        'media_url',
        'is_read_by_agent',
        'is_synced_to_marketplace',
        'marketplace_created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read_by_agent'       => 'boolean',
            'is_synced_to_marketplace' => 'boolean',
            'marketplace_created_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(MarketplaceConversation::class, 'marketplace_conversation_id');
    }
}
