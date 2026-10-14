<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Marketplace;

use App\Domain\Marketplace\MarketplaceManagerService;
use App\Http\Controllers\Controller;
use App\Jobs\Marketplace\ProcessMarketplaceWebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarketplaceWebhookController extends Controller
{
    public function __construct(protected MarketplaceManagerService $manager) {}

    /**
     * Handle incoming webhook requests from marketplaces.
     */
    public function handle(string $provider, Request $request): JsonResponse
    {
        $normalizedChannel = match (strtolower($provider)) {
            'shopee'                                    => 'shopee',
            'tiktok', 'tiktok_shop', 'tiktok-tokopedia' => 'tiktok_shop',
            'tokopedia'                                 => 'tokopedia',
            default                                     => abort(404),
        };

        try {
            $adapter   = $this->manager->driver($normalizedChannel);
            $rawBody   = (string) $request->getContent();
            $headers   = $request->headers->all();

            $eventData = $adapter->handleWebhook($request, $rawBody, $headers);

            if (! ($eventData['is_valid'] ?? false)) {
                Log::warning("Invalid marketplace webhook signature from {$provider}", ['headers' => $headers]);
                return response()->json([
                    'code'    => 401,
                    'success' => false,
                    'error'   => 'Invalid signature',
                    'message' => 'Invalid signature',
                ], 401);
            }

            // Dispatch background job to process webhook asynchronously
            ProcessMarketplaceWebhookJob::dispatch($normalizedChannel, $eventData);

            // TikTok Shop requires code: 0 to consider webhook delivered successfully
            return response()->json([
                'code'    => 0,
                'success' => true,
                'message' => 'Webhook received',
            ], 200);
        } catch (\Throwable $e) {
            Log::error("Error handling marketplace webhook [{$provider}]: " . $e->getMessage());
            return response()->json([
                'code'    => 500,
                'success' => false,
                'error'   => $e->getMessage(),
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
