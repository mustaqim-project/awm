<?php

declare(strict_types=1);

namespace App\Domain\Marketplace;

use App\Models\Business;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceProductMapping;
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
            $order->buyer_name             = (string) ($data['buyer_name'] ?? ($order->buyer_name ?: 'Pelanggan Marketplace'));
            $order->buyer_phone            = $data['buyer_phone'] ?? $order->buyer_phone;
            $order->total_amount           = (float) ($data['total_amount'] ?? ($order->total_amount ?? 0));
            $order->channel_fee            = (float) ($data['channel_fee'] ?? ($order->channel_fee ?? 0));
            $order->order_status           = strtoupper((string) ($data['status'] ?? ($order->order_status ?: 'UNPAID')));

            // Retain existing items if incoming payload is empty
            if (! empty($data['items'])) {
                $order->items_summary = $data['items'];
            } elseif (empty($order->items_summary)) {
                $order->items_summary = [];
            }

            if (! empty($data['shipping_provider'])) {
                $order->shipping_provider = $data['shipping_provider'];
            }
            if (! empty($data['tracking_number'])) {
                $order->tracking_number = $data['tracking_number'];
            }

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
            $items = $data['items'] ?? ($payload['items'] ?? []);
            $shippingProvider = $data['shipping_carrier'] ?? ($payload['shipping_carrier'] ?? null);
            $trackingNo = $data['tracking_no'] ?? ($data['tracking_number'] ?? ($payload['tracking_no'] ?? null));
            $buyerName = $payload['buyer_name'] ?? null;
            $buyerPhone = $payload['buyer_phone'] ?? null;
            $totalAmount = (float) ($payload['total_amount'] ?? ($data['total_amount'] ?? 0));
            $status = (string) ($data['status'] ?? ($payload['status'] ?? ($payload['order_status'] ?? 'PROCESSED')));
            $rawPayload = $payload;

            // Enrich webhook notification with full live details (items, courier, tracking, address)
            try {
                $adapter = $this->manager->driver($channel);
                if (method_exists($adapter, 'getOrderDetail')) {
                    $details = $adapter->getOrderDetail($account, $orderId);
                    $det = $details[0] ?? $details;
                    if (! empty($det) && is_array($det)) {
                        $rawPayload = array_merge($rawPayload, $det);
                        if (! empty($det['order_status'])) {
                            $status = (string) $det['order_status'];
                        }
                        if (! empty($det['total_amount'])) {
                            $totalAmount = (float) $det['total_amount'];
                        }
                        if (! empty($det['recipient_address']['name'])) {
                            $buyerName = $det['recipient_address']['name'];
                        } elseif (! empty($det['buyer_username'])) {
                            $buyerName = $det['buyer_username'];
                        }
                        if (! empty($det['recipient_address']['phone'])) {
                            $buyerPhone = $det['recipient_address']['phone'];
                        }
                        if (! empty($det['shipping_carrier'])) {
                            $shippingProvider = $det['shipping_carrier'];
                        } elseif (! empty($det['package_list'][0]['shipping_carrier'])) {
                            $shippingProvider = $det['package_list'][0]['shipping_carrier'];
                        }
                        if (empty($trackingNo) && ! empty($det['package_list'][0]['tracking_number'])) {
                            $trackingNo = $det['package_list'][0]['tracking_number'];
                        }
                        if (! empty($det['item_list'])) {
                            $parsedItems = [];
                            foreach ($det['item_list'] as $it) {
                                $name = ! empty($it['item_name']) ? $it['item_name'] : (! empty($it['model_name']) ? $it['model_name'] : 'Produk Shopee');
                                $qty = (int) ($it['model_quantity_purchased'] ?? 1);
                                $price = (float) ($it['model_discounted_price'] ?? ($it['model_original_price'] ?? 0));
                                $parsedItems[] = [
                                    'item_id'    => $it['item_id'] ?? null,
                                    'item_name'  => $name,
                                    'name'       => $name,
                                    'model_name' => $it['model_name'] ?? null,
                                    'sku'        => ! empty($it['model_sku']) ? $it['model_sku'] : (! empty($it['item_sku']) ? $it['item_sku'] : null),
                                    'quantity'   => $qty,
                                    'price'      => $price,
                                    'subtotal'   => $qty * $price,
                                    'image_url'  => $it['image_info']['image_url'] ?? null,
                                ];
                            }
                            $items = $parsedItems;
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to enrich order detail on webhook: {$e->getMessage()}");
            }

            $this->upsertMarketplaceOrder($account, [
                'order_id'          => $orderId,
                'order_sn'          => $orderId,
                'buyer_name'        => $buyerName ?? ('Shopee Buyer (' . substr($orderId, -4) . ')'),
                'buyer_phone'       => $buyerPhone,
                'total_amount'      => $totalAmount,
                'status'            => $status,
                'shipping_provider' => $shippingProvider,
                'tracking_number'   => $trackingNo,
                'items'             => $items,
                'raw'               => $rawPayload,
            ]);
        }

        // Tracking number updates (Code 4: order_trackingno_push, Code 24: booking_trackingno_push)
        $trackingNo = $data['tracking_no'] ?? ($data['tracking_number'] ?? ($payload['tracking_no'] ?? null));
        if (! empty($orderSn) && ! empty($trackingNo)) {
            MarketplaceOrder::where('business_id', $account->business_id)
                ->where('channel', $channel)
                ->where('external_order_id', (string) $orderSn)
                ->update(['tracking_number' => (string) $trackingNo]);
        }

        // Product / Reserved Stock Change Push (Code 8: reserved_stock_change_push)
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

        // Product Violation / Banned Push (Code 6: banned_item_push, Code 16: violation_item_push)
        $itemId = $data['item_id'] ?? ($payload['item_id'] ?? null);
        $eventCode = (int) ($payload['code'] ?? ($payload['type'] ?? 0));
        if (! empty($itemId) && in_array($eventCode, [6, 16], true)) {
            MarketplaceProductMapping::where('business_id', $account->business_id)
                ->where('channel', $channel)
                ->where('external_product_id', (string) $itemId)
                ->update([
                    'is_active' => false,
                    'sync_status' => MarketplaceProductMapping::STATUS_FAILED,
                    'last_sync_error' => $data['reason'] ?? ($payload['reason'] ?? 'Produk dibatasi atau dinonaktifkan oleh Shopee'),
                ]);

            MarketplaceSyncLog::log(
                businessId: $account->business_id,
                channel: $channel,
                entityType: 'product',
                action: $eventCode === 6 ? 'banned_item_push' : 'violation_item_push',
                status: 'warning',
                payload: $payload,
                marketplaceAccountId: $account->id
            );
        }

        // Shopee Auto Correct Item Push (Code 17: shopee_auto_correct_item_push)
        if (! empty($itemId) && $eventCode === 17) {
            MarketplaceSyncLog::log(
                businessId: $account->business_id,
                channel: $channel,
                entityType: 'product',
                action: 'auto_correct_item_push',
                status: 'info',
                payload: $payload,
                marketplaceAccountId: $account->id
            );
        }

        // Booking & Fulfillment Updates (Code 23: booking_status, Code 25: booking_shipping_document, Code 30: package_fulfillment, Code 37: courier_delivery_binding)
        if (in_array($eventCode, [23, 25, 30, 37], true)) {
            MarketplaceSyncLog::log(
                businessId: $account->business_id,
                channel: $channel,
                entityType: 'fulfillment',
                action: match ($eventCode) {
                    23 => 'booking_status_push',
                    25 => 'booking_shipping_document_status_push',
                    30 => 'package_fulfillment_status_push',
                    37 => 'courier_delivery_binding_status_push',
                    default => 'fulfillment_push'
                },
                status: 'info',
                payload: $payload,
                marketplaceAccountId: $account->id
            );
        }
    }
}
