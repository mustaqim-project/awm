<?php

declare(strict_types=1);

namespace App\Domain\Marketplace;

use App\Models\Business;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceSyncLog;
use Illuminate\Support\Facades\DB;

class MarketplaceOrderService
{
    public function __construct(protected MarketplaceManagerService $manager) {}

    /**
     * Pull recent orders from marketplace and store them in database.
     */
    public function pullAndSyncOrders(MarketplaceAccount $account): array
    {
        if (! $account->isConnected()) {
            return [];
        }

        $adapter   = $this->manager->driver($account->channel);
        $rawOrders = $adapter->pullOrders($account);
        $synced    = [];

        foreach ($rawOrders as $ord) {
            $order = $this->upsertMarketplaceOrder($account, $ord);
            if ($order) {
                $synced[] = $order;
            }
        }

        MarketplaceSyncLog::log(
            businessId: $account->business_id,
            channel: $account->channel,
            entityType: 'order',
            action: 'pull_order',
            status: 'success',
            payload: ['count' => count($synced)],
            marketplaceAccountId: $account->id
        );

        return $synced;
    }

    /**
     * Upsert a marketplace order from webhook or pull response.
     */
    public function upsertMarketplaceOrder(MarketplaceAccount $account, array $data): MarketplaceOrder
    {
        return DB::transaction(function () use ($account, $data) {
            $order = MarketplaceOrder::firstOrNew([
                'business_id'       => $account->business_id,
                'channel'           => $account->channel,
                'external_order_id' => (string) $data['order_id'],
            ]);

            $order->marketplace_account_id = $account->id;
            $order->external_order_sn      = (string) ($data['order_sn'] ?? $data['order_id']);
            $order->buyer_name             = (string) ($data['buyer_name'] ?? 'Pelanggan Marketplace');
            $order->buyer_phone            = $data['buyer_phone'] ?? null;
            $order->total_amount           = (float) ($data['total_amount'] ?? 0);
            $order->channel_fee            = (float) ($data['channel_fee'] ?? 0);
            $order->order_status           = strtoupper((string) ($data['status'] ?? 'UNPAID'));
            $order->items_summary          = $data['items'] ?? [];
            $order->shipping_provider      = $data['shipping_provider'] ?? null;
            $order->tracking_number        = $data['tracking_number'] ?? null;
            $order->raw_payload            = $data['raw'] ?? $data;
            $order->synced_at              = now();
            if (! $order->exists) {
                $order->placed_at = now();
            }
            $order->save();

            return $order;
        });
    }

    /**
     * Process incoming webhook event.
     */
    public function processWebhook(string $channel, array $eventData): void
    {
        $shopId = (string) ($eventData['shop_id'] ?? '');
        if (empty($shopId)) {
            return;
        }

        $account = MarketplaceAccount::where('channel', $channel)
            ->where('shop_id', $shopId)
            ->first();

        // Fallback for sandbox/test store
        if (! $account) {
            $account = MarketplaceAccount::where('channel', $channel)->first();
        }

        if (! $account) {
            return;
        }

        $payload = $eventData['payload'] ?? [];
        $data    = $payload['data'] ?? [];

        MarketplaceSyncLog::log(
            businessId: $account->business_id,
            channel: $channel,
            entityType: 'webhook',
            action: 'webhook_received',
            status: 'success',
            payload: $payload,
            marketplaceAccountId: $account->id
        );

        // Shopee sends order serial number inside $data['ordersn'] or $payload['order_sn']
        $orderSn = $data['ordersn'] ?? ($payload['order_sn'] ?? ($payload['order_id'] ?? null));
        if (! empty($orderSn)) {
            $orderId = (string) $orderSn;
            $this->upsertMarketplaceOrder($account, [
                'order_id'     => $orderId,
                'order_sn'     => $orderId,
                'buyer_name'   => $payload['buyer_name'] ?? ('Shopee Buyer (' . substr($orderId, -4) . ')'),
                'total_amount' => (float) ($payload['total_amount'] ?? ($data['total_amount'] ?? 0)),
                'status'       => (string) ($data['status'] ?? ($payload['status'] ?? ($payload['order_status'] ?? 'PROCESSED'))),
                'items'        => $data['items'] ?? ($payload['items'] ?? []),
                'raw'          => $payload,
            ]);
        }

        // Product / Reserved Stock Change Push (Code 5: reserved_stock_change_push)
        if (! empty($data['item_id']) && ! empty($data['changed_values'])) {
            MarketplaceSyncLog::log(
                businessId: $account->business_id,
                channel: $channel,
                entityType: 'stock',
                action: 'reserved_stock_change',
                status: 'success',
                payload: $data,
                marketplaceAccountId: $account->id
            );
        }
    }
}
