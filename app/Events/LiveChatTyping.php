<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveChatTyping implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $sessionToken;
    public bool $isAgent;

    /**
     * Create a new typing event instance.
     */
    public function __construct(string $sessionToken, bool $isAgent = false)
    {
        $this->sessionToken = $sessionToken;
        $this->isAgent = $isAgent;
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
        return 'agent.typing';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'session_token' => $this->sessionToken,
            'is_agent'      => $this->isAgent,
            'timestamp'     => now()->toIso8601String(),
        ];
    }
}
