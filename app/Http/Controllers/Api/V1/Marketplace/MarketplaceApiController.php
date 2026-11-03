<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Marketplace;

use App\Domain\Marketplace\MarketplaceChatService;
use App\Domain\Marketplace\MarketplaceManagerService;
use App\Domain\Marketplace\MarketplaceOrderService;
use App\Domain\Marketplace\MarketplaceSyncService;
use App\Http\Controllers\Controller;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceConversation;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceProductMapping;
use App\Models\MarketplaceSyncLog;
use App\Models\Product;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;

final class MarketplaceApiController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:marketplace.view', only: ['overview', 'products', 'orders', 'chat', 'chatDetails']),
            new Middleware('require.permission:marketplace.manage', only: [
                'syncAll',
                'pullOrders',
                'shipOrder',
                'sendChatMessage',
                'markChatRead',
            ]),
        ];
    }

    public function __construct(
        protected MarketplaceManagerService $manager,
        protected MarketplaceSyncService $syncService,
        protected MarketplaceOrderService $orderService,
        protected MarketplaceChatService $chatService
    ) {}

    /**
     * Marketplace Overview Cockpit (Accounts, Sync Status, Order Metrics)
     */
    public function overview(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $accounts = MarketplaceAccount::where('business_id', $business->id)->get();
        $totalMappings = MarketplaceProductMapping::where('business_id', $business->id)->count();
        $totalOrders = MarketplaceOrder::where('business_id', $business->id)->count();
        $pendingShipment = MarketplaceOrder::where('business_id', $business->id)
            ->whereIn('order_status', ['READY_TO_SHIP', 'IN_PROCESS', 'PAID'])
            ->count();
        $totalErrors = MarketplaceSyncLog::where('business_id', $business->id)->where('status', 'failed')->count();

        return response()->json([
            'success' => true,
            'metrics' => [
                'total_accounts' => $accounts->count(),
                'active_accounts' => $accounts->where('status', MarketplaceAccount::STATUS_CONNECTED)->count(),
                'total_mappings' => $totalMappings,
                'total_orders' => $totalOrders,
                'pending_shipment' => $pendingShipment,
                'total_sync_errors' => $totalErrors,
            ],
            'accounts' => $accounts,
        ]);
    }

    /**
     * Product Mappings with Channel Pricing & Stock Auto-Sync Comparison
     */
    public function products(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $channel = (string) $request->query('channel', 'all');

        $query = Product::where('business_id', $business->id)
            ->with(['marketplaceMappings', 'category', 'stocks'])
            ->latest();

        $products = $query->paginate(25);

        return response()->json([
            'success' => true,
            'total' => $products->total(),
            'products' => $products->items(),
        ]);
    }

    /**
     * Trigger Instant Stock & Price Sync Across Channels
     */
    public function syncAll(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        try {
            $this->syncService->syncAllForBusiness($business);

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi stok dan harga multi-channel berhasil dijalankan.',
            ]);
        } catch (\Throwable $e) {
            Log::error('[MarketplaceApiController] Sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal menyinkronkan stok: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Marketplace Orders List & Shipping Management
     */
    public function orders(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = MarketplaceOrder::where('business_id', $business->id)
            ->with('account')
            ->latest();

        if ($request->filled('channel') && $request->input('channel') !== 'all') {
            $query->where('channel', $request->input('channel'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('order_status', $request->input('status'));
        }

        $orders = $query->paginate(20);

        return response()->json([
            'success' => true,
            'total' => $orders->total(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'orders' => $orders->items(),
        ]);
    }

    /**
     * Pull Latest Orders from Connected Marketplace Stores
     */
    public function pullOrders(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        try {
            $this->orderService->pullOrdersForBusiness($business);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan terbaru berhasil ditarik dari semua marketplace.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menarik pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ship Order (Update Tracking Number / Request Pickup)
     */
    public function shipOrder(Request $request, int $id): JsonResponse
    {
        $business = Context::requireBusiness();
        $order = MarketplaceOrder::where('business_id', $business->id)->findOrFail($id);

        $validated = $request->validate([
            'tracking_number' => 'nullable|string|max:100',
            'shipping_provider' => 'nullable|string|max:100',
        ]);

        $order->update([
            'order_status' => 'SHIPPED',
            'tracking_number' => $validated['tracking_number'] ?? $order->tracking_number,
            'shipping_provider' => $validated['shipping_provider'] ?? $order->shipping_provider,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil diperbarui menjadi Telah Dikirim.',
            'order' => $order,
        ]);
    }

    /**
     * Omnichannel Marketplace Live Chat Conversations
     */
    public function chat(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $channel = $request->query('channel');

        $query = MarketplaceConversation::where('business_id', $business->id)
            ->with(['account'])
            ->orderBy('last_message_at', 'desc');

        if (!empty($channel) && $channel !== 'all') {
            $query->where('channel', $channel);
        }

        $conversations = $query->paginate(25);

        return response()->json([
            'success' => true,
            'conversations' => $conversations->items(),
            'unread_total' => (int) MarketplaceConversation::where('business_id', $business->id)->sum('unread_count'),
        ]);
    }

    /**
     * Live Chat Details with Buyer Order Context
     */
    public function chatDetails(Request $request, int $id): JsonResponse
    {
        $business = Context::requireBusiness();
        $conversation = MarketplaceConversation::where('business_id', $business->id)->findOrFail($id);

        $conversation->load(['messages', 'account']);
        $this->chatService->markAsRead($conversation);

        // Fetch Buyer's Associated Orders for Order Context Panel
        $buyerOrders = MarketplaceOrder::where('business_id', $business->id)
            ->where(function ($q) use ($conversation) {
                if (!empty($conversation->buyer_user_id)) {
                    $q->where('buyer_username', $conversation->buyer_username)
                      ->orWhere('external_order_id', $conversation->buyer_user_id);
                } else {
                    $q->where('buyer_username', $conversation->buyer_username);
                }
            })
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
            'buyer_orders' => $buyerOrders,
        ]);
    }

    /**
     * Send Chat Message to Buyer
     */
    public function sendChatMessage(Request $request, int $id): JsonResponse
    {
        $business = Context::requireBusiness();
        $conversation = MarketplaceConversation::where('business_id', $business->id)->findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'type' => 'nullable|string|in:TEXT,IMAGE',
        ]);

        try {
            $type = $validated['type'] ?? 'TEXT';
            $message = $this->chatService->sendMessage($conversation, $type, $validated['message']);

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mark Chat as Read
     */
    public function markChatRead(Request $request, int $id): JsonResponse
    {
        $business = Context::requireBusiness();
        $conversation = MarketplaceConversation::where('business_id', $business->id)->findOrFail($id);

        $this->chatService->markAsRead($conversation);

        return response()->json([
            'success' => true,
            'message' => 'Percakapan telah ditandai dibaca.',
        ]);
    }
}

