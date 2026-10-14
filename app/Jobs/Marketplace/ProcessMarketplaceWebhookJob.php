<?php

declare(strict_types=1);

namespace App\Jobs\Marketplace;

use App\Domain\Marketplace\MarketplaceChatService;
use App\Domain\Marketplace\MarketplaceOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMarketplaceWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public string $channel,
        public array $eventData
    ) {}

    public function handle(MarketplaceOrderService $orderService, MarketplaceChatService $chatService): void
    {
        $eventType = (int) ($this->eventData['event'] ?? ($this->eventData['payload']['type'] ?? 0));

        // Event 13 (New conversation) and Event 14 (New message)
        if (in_array($eventType, [13, 14], true) || ! empty($this->eventData['payload']['data']['conversation_id'])) {
            $chatService->processChatWebhook($this->channel, $this->eventData);
            return;
        }

        // Default order/fulfillment webhook processing
        $orderService->processWebhook($this->channel, $this->eventData);
    }
}

