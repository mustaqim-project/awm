<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class InboxThreadTakeover
 *
 * @property int $id
 * @property string $thread_key
 * @property string $channel
 * @property string $customer_id
 * @property bool $is_human_takeover
 * @property string|null $takeover_by_id
 * @property string|null $takeover_by_name
 * @property Carbon|null $takeover_at
 * @property Carbon|null $released_at
 * @property Carbon|null $last_ai_reply_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class InboxThreadTakeover extends Model
{
    use HasFactory;

    protected $table = 'inbox_thread_takeovers';

    protected $fillable = [
        'thread_key',
        'channel',
        'customer_id',
        'is_human_takeover',
        'takeover_by_id',
        'takeover_by_name',
        'takeover_at',
        'released_at',
        'last_ai_reply_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_human_takeover' => 'boolean',
            'takeover_at'       => 'datetime',
            'released_at'       => 'datetime',
            'last_ai_reply_at'  => 'datetime',
        ];
    }

    /**
     * Generate canonical thread_key based on channel, customer ID, or comment ID.
     */
    public static function resolveThreadKey(
        string $platform,
        ?string $fromId,
        ?string $sessionToken = null,
        ?string $parentCommentId = null,
        ?string $platformCommentId = null
    ): string {
        $raw = strtolower(trim($platform));
        $channel = match ($raw) {
            'instagram_dm', 'instagram' => 'instagram',
            'messenger', 'facebook'     => 'messenger',
            'live_chat'                 => 'live_chat',
            'whatsapp'                  => 'whatsapp',
            'facebook_comments'         => 'facebook_comments',
            'instagram_comments'        => 'instagram_comments',
            default                     => $raw,
        };

        if ($channel === 'live_chat') {
            $cid = (string) ($sessionToken ?: $fromId ?: 'live_visitor');
            return "live_chat::{$cid}";
        }

        if ($channel === 'whatsapp') {
            $cid = (string) ($fromId ?: 'unknown_wa');
            $clean = preg_replace('/[^0-9]/', '', $cid);
            if (str_starts_with($clean, '0')) {
                $clean = '62' . substr($clean, 1);
            }
            $target = ! empty($clean) ? $clean : $cid;
            return "whatsapp::{$target}";
        }

        if (in_array($channel, ['facebook_comments', 'instagram_comments'], true)) {
            $topCommentId = (string) ($parentCommentId ?: $platformCommentId ?: $fromId ?: 'comment');
            return "{$channel}::{$topCommentId}";
        }

        // Direct messages (Messenger, Instagram)
        $cid = (string) ($fromId ?: $sessionToken ?: $platformCommentId ?: 'direct');
        return "{$channel}::{$cid}";
    }

    /**
     * Check if a thread is currently taken over by a human agent.
     */
    public static function isTakenOver(string $threadKey): bool
    {
        $record = static::where('thread_key', $threadKey)->first();

        return (bool) ($record?->is_human_takeover ?? false);
    }

    /**
     * Get thread takeover record if exists.
     */
    public static function getTakeoverStatus(string $threadKey): ?self
    {
        return static::where('thread_key', $threadKey)->first();
    }

    /**
     * Mark a thread as taken over by a human agent.
     */
    public static function markTakeover(
        string $threadKey,
        string $channel,
        string $customerId,
        ?string $adminId = null,
        ?string $adminName = null
    ): self {
        return static::updateOrCreate(
            ['thread_key' => $threadKey],
            [
                'channel'           => $channel,
                'customer_id'       => $customerId,
                'is_human_takeover' => true,
                'takeover_by_id'    => $adminId,
                'takeover_by_name'  => $adminName ?: 'Admin COOCA',
                'takeover_at'       => now(),
                'released_at'       => null,
            ]
        );
    }

    /**
     * Release takeover of a thread so AI can resume auto-replies.
     */
    public static function releaseTakeover(string $threadKey): bool
    {
        $record = static::where('thread_key', $threadKey)->first();
        if (! $record) {
            return false;
        }

        $record->update([
            'is_human_takeover' => false,
            'released_at'       => now(),
        ]);

        return true;
    }

    /**
     * Record timestamp of AI reply for the thread.
     */
    public static function recordAiReply(string $threadKey, string $channel, string $customerId): self
    {
        $existing = static::where('thread_key', $threadKey)->first();
        if ($existing) {
            $existing->update(['last_ai_reply_at' => now()]);
            return $existing;
        }

        return static::create([
            'thread_key'        => $threadKey,
            'channel'           => $channel,
            'customer_id'       => $customerId,
            'is_human_takeover' => false,
            'last_ai_reply_at'  => now(),
        ]);
    }
}
