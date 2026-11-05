<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AiChatbotConversation extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_HELP_CENTER = 'help_center';
    public const TYPE_BUSINESS_ASSISTANT = 'business_assistant';

    protected $table = 'ai_chatbot_conversations';

    protected $fillable = [
        'business_id',
        'user_id',
        'type',
        'title',
    ];

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<AiChatbotMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(AiChatbotMessage::class, 'conversation_id')->orderBy('created_at', 'asc');
    }
}

