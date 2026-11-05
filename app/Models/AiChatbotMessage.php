<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AiChatbotMessage extends Model
{
    use HasFactory, HasUuid;

    public const SENDER_USER = 'user';
    public const SENDER_ASSISTANT = 'assistant';
    public const SENDER_SYSTEM = 'system';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $table = 'ai_chatbot_messages';

    protected $fillable = [
        'conversation_id',
        'sender',
        'content',
        'intent',
        'is_data_query',
        'permission_granted',
        'status',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_data_query' => 'boolean',
            'permission_granted' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<AiChatbotConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiChatbotConversation::class, 'conversation_id');
    }
}

