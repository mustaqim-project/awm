<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\SocialMediaComment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $sessionToken;
    public array $messageData;

    /**
     * Create a new event instance.
     */
    public function __construct(SocialMediaComment $comment)
    {
        $this->sessionToken = (string) ($comment->session_token ?: $comment->from_id);

        $created = $comment->created_time ?? $comment->created_at ?? now();

        $this->messageData = [
            'id'            => (string) $comment->id,
            'session_token' => $this->sessionToken,
            'from_name'     => (string) ($comment->is_from_page ? 'Cooca Indonesia (Customer Support)' : $comment->from_name),
            'from_email'    => (string) ($comment->from_email ?? ''),
            'from_phone'    => (string) ($comment->from_phone ?? ''),
            'message'       => (string) $comment->message,
            'media_url'     => $comment->media_url,
            'media_type'    => $comment->media_type,
            'is_from_page'  => (bool) $comment->is_from_page,
            'is_cooca'      => (bool) $comment->is_from_page,
            'status'        => (string) ($comment->status ?? 'unread'),
            'time'          => $created->format('H:i'),
            'time_relative' => $created->diffForHumans(null, true),
            'date'          => $created->format('d M Y'),
            'channel'       => 'live_chat',
            'created_at'    => $created->toIso8601String(),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('livechat.' . $this->sessionToken),
            new Channel('admin.inbox'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data that should be broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->messageData;
    }
}
