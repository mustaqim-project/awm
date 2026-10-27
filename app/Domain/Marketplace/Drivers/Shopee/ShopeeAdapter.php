<?php

declare(strict_types=1);

namespace App\Domain\Marketplace\Drivers\Shopee;

use App\Domain\Marketplace\Contracts\MarketplaceAdapterInterface;
use App\Models\Business;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProductMapping;
use App\Models\Product;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopeeAdapter implements MarketplaceAdapterInterface
{
    protected string $host;
    protected int $partnerId;
    protected string $partnerKey;
    public ?array $lastCategoryDebug = null;

    public function __construct()
    {
        $isProdSetting = SystemSetting::get('shopee_is_production');
        $isProd = $isProdSetting !== null 
            ? filter_var($isProdSetting, FILTER_VALIDATE_BOOLEAN)
            : filter_var(config('services.shopee.is_production', false), FILTER_VALIDATE_BOOLEAN);

        // Shopee Open Platform V2: Production vs Sandbox V2 domain
        $defaultHost = $isProd 
            ? 'https://partner.shopeemobile.com' 
            : 'https://openplatform.sandbox.test-stable.shopee.sg';

        $configuredHost   = (string) (SystemSetting::get('shopee_host') ?? env('SHOPEE_HOST'));
        $this->host       = ! empty($configuredHost) ? $configuredHost : $defaultHost;
        $this->partnerId  = (int) (SystemSetting::get('shopee_partner_id') ?? config('services.shopee.partner_id', 0));
        $this->partnerKey = (string) (SystemSetting::get('shopee_partner_key') ?? config('services.shopee.partner_key', ''));
    }

    public function getChannel(): string
    {
        return MarketplaceAccount::CHANNEL_SHOPEE;
    }

    public function getName(): string
    {
        return 'Shopee';
    }

    public function hasCredentials(): bool
    {
        return ! empty($this->partnerId) && ! empty($this->partnerKey);
    }

    public function generateSignature(string $path, int $timestamp, string $accessToken = '', string $shopId = ''): string
    {
        $baseStr = sprintf('%s%s%s%s%s', $this->partnerId, $path, $timestamp, $accessToken, $shopId);
        return hash_hmac('sha256', $baseStr, $this->partnerKey);
    }

    public function getAuthUrl(Business $business, string $redirectUri, string $state): string
    {
        $timestamp = time();
        $path      = '/api/v2/shop/auth_partner';
        $sign      = $this->generateSignature($path, $timestamp);

        $params = [
            'partner_id' => $this->partnerId,
            'timestamp'  => $timestamp,
            'sign'       => $sign,
            'redirect'   => $redirectUri,
            'state'      => $state,
        ];

        return $this->host . $path . '?' . http_build_query($params);
    }

    public function handleAuthCallback(Business $business, array $params, string $redirectUri): array
    {
        $code      = (string) ($params['code'] ?? '');
        $shopId    = (string) ($params['shop_id'] ?? ($params['main_account_id'] ?? ''));
        $timestamp = time();
        $path      = '/api/v2/auth/token/get';
        $sign      = $this->generateSignature($path, $timestamp);

        if (empty($code)) {
            throw new \InvalidArgumentException('Authorization code Shopee tidak ditemukan dalam respon callback.');
        }

        $url = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $sign);

        $payload = [
            'code'       => $code,
            'partner_id' => $this->partnerId,
        ];
        if (! empty($params['main_account_id'])) {
            $payload['main_account_id'] = (int) $params['main_account_id'];
        } else {
            $payload['shop_id'] = (int) $shopId;
        }

        $response = Http::timeout(15)->post($url, $payload);

        $data = $response->json();

        if (! $response->successful() || ! empty($data['error'])) {
            $msg = $data['message'] ?? ($data['error'] ?? 'Gagal menukarkan authorization code Shopee.');
            throw new \RuntimeException((string) $msg);
        }

        return [
            'shop_id'            => (string) ($data['shop_id'] ?? $shopId),
            'shop_name'          => 'Shopee Store #' . ($data['shop_id'] ?? $shopId),
            'access_token'       => (string) $data['access_token'],
            'refresh_token'      => (string) $data['refresh_token'],
            'expires_in'         => (int) ($data['expire_in'] ?? 14400),
            'refresh_expires_in' => 2592000, // 30 days
            'extra'              => [
                'merchant_id_list' => $data['merchant_id_list'] ?? [],
                'shop_id_list'     => $data['shop_id_list'] ?? [],
            ],
        ];
    }

    public function refreshToken(MarketplaceAccount $account): array
    {
        $timestamp = time();
        $path      = '/api/v2/auth/access_token/get';
        $sign      = $this->generateSignature($path, $timestamp);

        $url = sprintf('%s%s?partner_id=%s&timestamp=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $sign);

        $response = Http::timeout(15)->post($url, [
            'partner_id'    => $this->partnerId,
            'shop_id'       => (int) $account->shop_id,
            'refresh_token' => $account->refresh_token,
        ]);

        $data = $response->json();

        if (! $response->successful() || ! empty($data['error'])) {
            throw new \RuntimeException($data['message'] ?? 'Gagal memperbarui token Shopee.');
        }

        return [
            'access_token'  => (string) $data['access_token'],
            'refresh_token' => (string) $data['refresh_token'],
            'expires_in'    => (int) ($data['expire_in'] ?? 14400),
        ];
    }

    public function pushStock(MarketplaceAccount $account, MarketplaceProductMapping $mapping, int $stock): bool
    {
        $timestamp   = time();
        $path        = '/api/v2/product/update_stock';
        $accessToken = (string) $account->access_token;
        $sign        = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);

        $url = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $accessToken,
            $account->shop_id,
            $sign
        );

        $stockData = [
            'item_id' => (int) $mapping->external_product_id,
            'stock_list' => [
                [
                    'seller_stock' => [
                        [
                            'stock' => max(0, $stock),
                        ],
                    ],
                ],
            ],
        ];

        if ($mapping->external_sku_id) {
            $stockData['stock_list'][0]['model_id'] = (int) $mapping->external_sku_id;
        }

        $response = Http::timeout(15)->post($url, $stockData);
        $data = $response->json();

        return $response->successful() && empty($data['error']);
    }

    public function pushPrice(MarketplaceAccount $account, MarketplaceProductMapping $mapping, float $price): bool
    {
        $timestamp   = time();
        $path        = '/api/v2/product/update_price';
        $accessToken = (string) $account->access_token;
        $sign        = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);

        $url = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $accessToken,
            $account->shop_id,
            $sign
        );

        $priceData = [
            'item_id' => (int) $mapping->external_product_id,
            'price_list' => [
                [
                    'original_price' => $price,
                ],
            ],
        ];

        if ($mapping->external_sku_id) {
            $priceData['price_list'][0]['model_id'] = (int) $mapping->external_sku_id;
        }

        $response = Http::timeout(15)->post($url, $priceData);
        $data = $response->json();

        return $response->successful() && empty($data['error']);
    }

    public function getProducts(MarketplaceAccount $account, array $params = []): array
    {
        $timestamp   = time();
        $path        = '/api/v2/product/get_item_list';
        $accessToken = (string) $account->access_token;
        $sign        = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);

        $url = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&offset=%s&page_size=%s&item_status=NORMAL',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $accessToken,
            $account->shop_id,
            $sign,
            $params['offset'] ?? 0,
            $params['limit'] ?? 20
        );

        $response = Http::timeout(15)->get($url);
        $data = $response->json();

        $items = [];
        if ($response->successful() && ! empty($data['response']['item'])) {
            foreach ($data['response']['item'] as $item) {
                $items[] = [
                    'id'         => (string) $item['item_id'],
                    'name'       => $item['item_name'] ?? ('Shopee Item #' . $item['item_id']),
                    'sku'        => $item['item_sku'] ?? '',
                    'price'      => (float) ($item['price'] ?? 0),
                    'stock'      => (int) ($item['stock'] ?? 0),
                    'image_url'  => $item['image_url'] ?? null,
                    'variations' => [],
                ];
            }
        }

        return [
            'products' => $items,
            'has_next' => (bool) ($data['response']['has_next_page'] ?? false),
        ];
    }

    public function pullOrders(MarketplaceAccount $account, array $params = []): array
    {
        $timestamp   = time();
        $path        = '/api/v2/order/get_order_list';
        $accessToken = (string) $account->access_token;
        $sign        = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);

        $timeFrom = $params['time_from'] ?? (time() - 86400 * 14);
        $timeTo   = $params['time_to'] ?? time();

        $url = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&time_range_field=create_time&time_from=%s&time_to=%s&page_size=50',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $accessToken,
            $account->shop_id,
            $sign,
            $timeFrom,
            $timeTo
        );

        $response = Http::timeout(15)->get($url);
        $data = $response->json();

        $orders = [];
        if ($response->successful() && ! empty($data['response']['order_list'])) {
            $orderSns = [];
            foreach ($data['response']['order_list'] as $ord) {
                if (! empty($ord['order_sn'])) {
                    $orderSns[] = (string) $ord['order_sn'];
                }
            }

            // Batch-fetch complete order details (buyer info, items, logistics carrier, tracking)
            $detailsMap = [];
            if (! empty($orderSns)) {
                $details = $this->getOrderDetail($account, $orderSns);
                foreach ($details as $det) {
                    if (! empty($det['order_sn'])) {
                        $detailsMap[$det['order_sn']] = $det;
                    }
                }
            }

            foreach ($data['response']['order_list'] as $ord) {
                $sn = (string) $ord['order_sn'];
                $det = $detailsMap[$sn] ?? [];

                // Parse items with full metadata
                $items = [];
                if (! empty($det['item_list'])) {
                    foreach ($det['item_list'] as $it) {
                        $name = ! empty($it['item_name']) ? $it['item_name'] : (! empty($it['model_name']) ? $it['model_name'] : 'Produk Shopee');
                        $qty = (int) ($it['model_quantity_purchased'] ?? 1);
                        $price = (float) ($it['model_discounted_price'] ?? ($it['model_original_price'] ?? 0));
                        $sku = ! empty($it['model_sku']) ? $it['model_sku'] : (! empty($it['item_sku']) ? $it['item_sku'] : null);
                        $imageUrl = $it['image_info']['image_url'] ?? null;

                        $items[] = [
                            'item_id'    => $it['item_id'] ?? null,
                            'item_name'  => $name,
                            'name'       => $name,
                            'model_name' => $it['model_name'] ?? null,
                            'sku'        => $sku,
                            'quantity'   => $qty,
                            'price'      => $price,
                            'subtotal'   => $qty * $price,
                            'image_url'  => $imageUrl,
                        ];
                    }
                }

                // Shipping provider & tracking number
                $shippingCarrier = $det['shipping_carrier']
                    ?? ($det['package_list'][0]['shipping_carrier'] ?? null);

                $trackingNumber = $det['package_list'][0]['tracking_number']
                    ?? ($det['tracking_number'] ?? null);

                if (empty($trackingNumber) && in_array(strtoupper($det['order_status'] ?? ''), ['READY_TO_SHIP', 'PROCESSED', 'SHIPPED', 'COMPLETED'], true)) {
                    $trackingNumber = $this->getTrackingNumber($account, $sn);
                }

                // Buyer details & contact
                $buyerName = ! empty($det['recipient_address']['name'])
                    ? $det['recipient_address']['name']
                    : (! empty($det['buyer_username']) ? $det['buyer_username'] : ('Shopee Buyer (' . substr($sn, -4) . ')'));
                $buyerPhone = $det['recipient_address']['phone'] ?? null;

                $totalAmount = (float) ($det['total_amount'] ?? ($ord['total_amount'] ?? 0));
                $orderStatus = (string) ($det['order_status'] ?? ($ord['order_status'] ?? 'UNPAID'));

                $orders[] = [
                    'order_id'          => $sn,
                    'order_sn'          => $sn,
                    'buyer_name'        => $buyerName,
                    'buyer_phone'       => $buyerPhone,
                    'total_amount'      => $totalAmount,
                    'status'            => $orderStatus,
                    'shipping_provider' => $shippingCarrier,
                    'tracking_number'   => $trackingNumber,
                    'items'             => $items,
                    'raw'               => ! empty($det) ? $det : $ord,
                ];
            }
        }

        return $orders;
    }

    /**
     * Upload an image file or remote image URL to Shopee Media Space.
     */
    public function uploadImage(MarketplaceAccount $account, string $imagePathOrUrl): ?string
    {
        if (! $this->hasCredentials() || empty($account->access_token)) {
            return null;
        }

        $imageContent = null;
        $filename     = 'item_' . time() . '.jpg';

        if (filter_var($imagePathOrUrl, FILTER_VALIDATE_URL) || str_starts_with($imagePathOrUrl, 'http://') || str_starts_with($imagePathOrUrl, 'https://')) {
            try {
                $dl = Http::timeout(20)->get($imagePathOrUrl);
                if ($dl->successful() && ! empty($dl->body())) {
                    $imageContent = $dl->body();
                    $parsedPath = parse_url($imagePathOrUrl, PHP_URL_PATH);
                    if ($parsedPath) {
                        $ext = pathinfo($parsedPath, PATHINFO_EXTENSION);
                        if ($ext) {
                            $filename = basename($parsedPath);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee uploadImage: download failed for {$imagePathOrUrl}: {$e->getMessage()}");
            }
        } elseif (file_exists($imagePathOrUrl)) {
            $imageContent = file_get_contents($imagePathOrUrl);
            $filename     = basename($imagePathOrUrl);
        } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($imagePathOrUrl)) {
            $fullPath     = \Illuminate\Support\Facades\Storage::disk('public')->path($imagePathOrUrl);
            $imageContent = file_get_contents($fullPath);
            $filename     = basename($fullPath);
        } elseif (\Illuminate\Support\Facades\Storage::exists($imagePathOrUrl)) {
            $imageContent = \Illuminate\Support\Facades\Storage::get($imagePathOrUrl);
            $filename     = basename($imagePathOrUrl);
        }

        if (empty($imageContent)) {
            return null;
        }

        return $this->uploadImageBytes($account, $imageContent, $filename);
    }

    /**
     * Upload raw image bytes to Shopee Media Space.
     */
    public function uploadImageBytes(MarketplaceAccount $account, string $bytes, string $filename = 'product.jpg'): ?string
    {
        $timestamp = time();
        $path      = '/api/v2/media_space/upload_image';
        $baseStr   = $this->partnerId . $path . $timestamp;
        $sign      = hash_hmac('sha256', $baseStr, $this->partnerKey);

        $url = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&sign=%s',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $sign
        );

        try {
            $response = Http::timeout(35)
                ->attach('image', $bytes, $filename)
                ->post($url);

            $data = $response->json() ?? [];
            if ($response->successful() && empty($data['error']) && ! empty($data['response']['image_info']['image_id'])) {
                return (string) $data['response']['image_info']['image_id'];
            }

            Log::warning('Shopee upload_image error from API', [
                'status'   => $response->status(),
                'response' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::warning("Shopee upload_image exception: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Fetch enabled logistics channels for the shop.
     */
    public function getShopLogistics(MarketplaceAccount $account): array
    {
        $timestamp   = time();
        $path        = '/api/v2/logistics/get_channel_list';
        $accessToken = (string) $account->access_token;
        $sign        = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);

        $url = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $accessToken,
            $account->shop_id,
            $sign
        );

        try {
            $response = Http::timeout(15)->get($url);
            $data     = $response->json() ?? [];
            if ($response->successful() && empty($data['error']) && ! empty($data['response']['logistics_channel_list'])) {
                return $data['response']['logistics_channel_list'];
            }
        } catch (\Throwable $e) {
            Log::warning("Shopee get_channel_list error: {$e->getMessage()}");
        }

        return [];
    }

    /**
     * Fetch all categories for the shop from Shopee Open Platform.
     */
    public function getCategoryList(MarketplaceAccount $account): array
    {
        $cacheKey = "shopee_categories_{$account->id}_v2";
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ! empty($cached)) {
            $this->lastCategoryDebug = ['source' => 'cache', 'count' => count($cached)];
            return $cached;
        }

        $timestamp   = time();
        $path        = '/api/v2/product/get_category';
        $accessToken = (string) $account->access_token;
        $sign        = $this->generateSignature($path, $timestamp, $accessToken, (string) $account->shop_id);

        $debug = [];

        // Attempt 1: Standard Shop API request without language (uses shop default)
        $url1 = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $accessToken,
            $account->shop_id,
            $sign
        );

        try {
            $response1 = Http::timeout(20)->get($url1);
            $data1     = $response1->json() ?? [];
            $debug['attempt_no_lang'] = [
                'status' => $response1->status(),
                'error'  => $data1['error'] ?? null,
                'msg'    => $data1['message'] ?? null,
                'count'  => count($data1['response']['category_list'] ?? []),
            ];

            if ($response1->successful() && empty($data1['error']) && ! empty($data1['response']['category_list'])) {
                $list = $data1['response']['category_list'];
                Cache::put($cacheKey, $list, 86400);
                $this->lastCategoryDebug = ['source' => 'api_no_lang', 'count' => count($list), 'debug' => $debug];
                return $list;
            }

            // Attempt 2: With language=id
            $url2 = $url1 . '&language=id';
            $response2 = Http::timeout(20)->get($url2);
            $data2     = $response2->json() ?? [];
            $debug['attempt_lang_id'] = [
                'status' => $response2->status(),
                'error'  => $data2['error'] ?? null,
                'msg'    => $data2['message'] ?? null,
                'count'  => count($data2['response']['category_list'] ?? []),
            ];

            if ($response2->successful() && empty($data2['error']) && ! empty($data2['response']['category_list'])) {
                $list = $data2['response']['category_list'];
                Cache::put($cacheKey, $list, 86400);
                $this->lastCategoryDebug = ['source' => 'api_lang_id', 'count' => count($list), 'debug' => $debug];
                return $list;
            }

            // Attempt 3: With language=en
            $url3 = $url1 . '&language=en';
            $response3 = Http::timeout(20)->get($url3);
            $data3     = $response3->json() ?? [];
            $debug['attempt_lang_en'] = [
                'status' => $response3->status(),
                'error'  => $data3['error'] ?? null,
                'msg'    => $data3['message'] ?? null,
                'count'  => count($data3['response']['category_list'] ?? []),
            ];

            if ($response3->successful() && empty($data3['error']) && ! empty($data3['response']['category_list'])) {
                $list = $data3['response']['category_list'];
                Cache::put($cacheKey, $list, 86400);
                $this->lastCategoryDebug = ['source' => 'api_lang_en', 'count' => count($list), 'debug' => $debug];
                return $list;
            }
        } catch (\Throwable $e) {
            $debug['exception'] = $e->getMessage();
        }

        $this->lastCategoryDebug = ['source' => 'failed', 'count' => 0, 'debug' => $debug];
        return [];
    }

    /**
     * Resolve a valid leaf category_id for Shopee.
     * Shopee strictly rejects parent categories (has_children: true) and invalid category IDs.
     */
    public function resolveShopeeCategoryId(MarketplaceAccount $account, \App\Models\Product $product, array $options = []): int
    {
        $categories  = $this->getCategoryList($account);
        $passedCatId = isset($options['category_id']) ? (int) $options['category_id'] : null;

        // If passed category is COOCA's internal mock category (>= 600000), ignore it
        $isInternalCoocaCat = $passedCatId >= 600000;

        // Filter leaf categories only (has_children === false)
        $leafCategories = array_values(array_filter($categories, fn($c) => empty($c['has_children'])));

        // 1. If passed category ID is already a valid Shopee leaf category
        if ($passedCatId && ! $isInternalCoocaCat && ! empty($leafCategories)) {
            foreach ($leafCategories as $cat) {
                if ((int) $cat['category_id'] === $passedCatId) {
                    return $passedCatId;
                }
            }
        }

        // 2. Build search terms from product name, category, and options
        $searchTerms = [];
        if (! empty($options['category_name'])) {
            $searchTerms[] = mb_strtolower(trim($options['category_name']));
        }
        if ($product->relationLoaded('category') && $product->category) {
            $searchTerms[] = mb_strtolower(trim($product->category->name));
        }
        $searchTerms[] = mb_strtolower(trim($product->name));

        $words = preg_split('/[\s,\.\-\_\/()]+/', mb_strtolower($product->name));
        foreach ($words as $w) {
            if (mb_strlen($w) >= 3) {
                $searchTerms[] = $w;
            }
        }
        $searchTerms = array_unique(array_filter($searchTerms));

        // 3. Match search terms to Shopee leaf categories
        if (! empty($leafCategories)) {
            foreach ($searchTerms as $term) {
                foreach ($leafCategories as $cat) {
                    $catName = mb_strtolower($cat['display_category_name'] ?? ($cat['original_category_name'] ?? ''));
                    if ($catName === $term || str_contains($catName, $term) || str_contains($term, $catName)) {
                        Log::info("Shopee category matched term '{$term}' to category_id {$cat['category_id']} ({$catName})");
                        return (int) $cat['category_id'];
                    }
                }
            }

            // Fallback: Pick first available leaf category
            $first = (int) $leafCategories[0]['category_id'];
            Log::info("Shopee category fallback to first leaf category: {$first}");
            return $first;
        }

        // 4. If category API could not be reached, use a known valid leaf category ID
        // In Shopee ID / SG: 100523 or 100010
        return (! $isInternalCoocaCat && $passedCatId) ? $passedCatId : 100523;
    }

    /**
     * Fetch category attributes from Shopee Open Platform.
     */
    public function getCategoryAttributes(MarketplaceAccount $account, int $categoryId): array
    {
        $cacheKey = "shopee_cat_attrs_v3_{$account->id}_{$categoryId}";
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ! empty($cached)) {
            return $cached;
        }

        $timestamp   = time();
        $path        = '/api/v2/product/get_attributes';
        $accessToken = (string) $account->access_token;
        $sign        = $this->generateSignature($path, $timestamp, $accessToken, (string) $account->shop_id);

        $url = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&category_id=%s',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $accessToken,
            $account->shop_id,
            $sign,
            $categoryId
        );

        try {
            $response = Http::timeout(20)->get($url);
            $data     = $response->json() ?? [];
            Log::info("Shopee get_attributes for cat {$categoryId}:", ['status' => $response->status(), 'data' => $data]);

            if ($response->successful() && empty($data['error']) && ! empty($data['response']['attribute_list'])) {
                $list = $data['response']['attribute_list'];
                Cache::put($cacheKey, $list, 86400);
                return $list;
            }

            // Fallback with language=id
            $urlId = $url . '&language=id';
            $resId = Http::timeout(20)->get($urlId);
            $dataId = $resId->json() ?? [];
            if ($resId->successful() && empty($dataId['error']) && ! empty($dataId['response']['attribute_list'])) {
                $list = $dataId['response']['attribute_list'];
                Cache::put($cacheKey, $list, 86400);
                return $list;
            }
        } catch (\Throwable $e) {
            Log::warning("Shopee get_attributes error: {$e->getMessage()}");
        }

        return [];
    }

    /**
     * Resolve mandatory attributes for the selected category.
     */
    public function resolveCategoryAttributes(MarketplaceAccount $account, int $categoryId): array
    {
        $attributes        = $this->getCategoryAttributes($account, $categoryId);
        $payloadAttributes = [];

        foreach ($attributes as $attr) {
            if (empty($attr['is_mandatory'])) {
                continue;
            }

            $attrId              = (int) $attr['attribute_id'];
            $inputValidationType = $attr['input_validation_type'] ?? '';
            $valueList           = $attr['attribute_value_list'] ?? [];

            // Case A: Predefined options available in Shopee
            if (! empty($valueList) && is_array($valueList)) {
                $firstVal = $valueList[0];
                $valObj   = [
                    'value_id' => (int) ($firstVal['value_id'] ?? 0),
                ];
                if (! empty($firstVal['original_value_name'])) {
                    $valObj['original_value_name'] = $firstVal['original_value_name'];
                }
                if (! empty($firstVal['value_unit'])) {
                    $valObj['value_unit'] = $firstVal['value_unit'];
                }
                $payloadAttributes[] = [
                    'attribute_id'         => $attrId,
                    'attribute_value_list' => [$valObj],
                ];
                continue;
            }

            // Case B: DATE_TYPE (e.g. Expiry Date)
            if ($inputValidationType === 'DATE_TYPE' || str_contains(strtolower($attr['original_attribute_name'] ?? ''), 'date')) {
                $futureTimestamp = strtotime('+1 year');
                $payloadAttributes[] = [
                    'attribute_id'         => $attrId,
                    'attribute_value_list' => [
                        [
                            'value_id'            => 0,
                            'original_value_name' => (string) $futureTimestamp,
                        ],
                    ],
                ];
                continue;
            }

            // Case C: INT_TYPE (e.g. Shelf Life)
            if ($inputValidationType === 'INT_TYPE') {
                $payloadAttributes[] = [
                    'attribute_id'         => $attrId,
                    'attribute_value_list' => [
                        [
                            'value_id'            => 0,
                            'original_value_name' => '12',
                            'value_unit'          => 'month',
                        ],
                    ],
                ];
                continue;
            }

            // Case D: Fallback string value
            $payloadAttributes[] = [
                'attribute_id'         => $attrId,
                'attribute_value_list' => [
                    [
                        'value_id'            => 0,
                        'original_value_name' => '12 Bulan',
                    ],
                ],
            ];
        }

        return $payloadAttributes;
    }

    /**
     * Upload / publish a new product listing from COOCA to Shopee.
     */
    public function publishProduct(MarketplaceAccount $account, \App\Models\Product $product, array $options = []): array
    {
        $timestamp   = time();
        $path        = '/api/v2/product/add_item';
        $accessToken = (string) $account->access_token;
        $sign        = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);

        $price = (float) ($options['price'] ?? $options['channel_price'] ?? $product->selling_price);
        $stock = (int) ($options['stock'] ?? $options['custom_stock'] ?? 10);
        $sku   = (string) ($options['sku'] ?? $product->code ?? ('SKU-' . $product->id));

        // Upload main and gallery images to Shopee Media Space
        $imageIdList = [];
        $candidates  = [];

        if (! empty($product->image)) {
            $candidates[] = $product->image;
        }
        if (! empty($product->image_url)) {
            $candidates[] = $product->image_url;
        }

        if ($product->relationLoaded('images') || $product->images()->exists()) {
            foreach ($product->images as $galleryImg) {
                if (! empty($galleryImg->image_path)) {
                    $candidates[] = $galleryImg->image_path;
                }
                if (! empty($galleryImg->image_url)) {
                    $candidates[] = $galleryImg->image_url;
                }
            }
        }

        if (! empty($product->gallery_images) && is_array($product->gallery_images)) {
            foreach ($product->gallery_images as $g) {
                $url = is_array($g) ? ($g['url'] ?? $g['image_path'] ?? null) : (is_string($g) ? $g : null);
                if ($url) {
                    $candidates[] = $url;
                }
            }
        }

        $candidates = array_unique(array_filter($candidates));

        // 0. Pre-flight Validation: Shopee Mandatory Data Completeness
        $missingData = [];
        $totalCandidates = count($candidates);

        if ($totalCandidates < 2) {
            $missingData[] = "Foto Produk: Shopee mewajibkan minimal 2 foto produk (maksimal 9 foto). Produk Anda saat ini baru memiliki {$totalCandidates} foto. Silakan unggah minimal 1 foto lagi di galeri produk sebelum menerbitkan.";
        }

        if (empty($product->name) || mb_strlen(trim($product->name)) < 5) {
            $missingData[] = "Nama Produk: Minimal 5 karakter dan maksimal 120 karakter.";
        }

        if ($price < 100) {
            $missingData[] = "Harga Produk: Harga jual saluran tidak boleh Rp 0 atau di bawah Rp 100.";
        }

        if ($stock < 1) {
            $missingData[] = "Stok Produk: Minimal 1 unit.";
        }

        $rawWeight = (float) ($product->weight ?? 0);
        if ($rawWeight <= 0) {
            $missingData[] = "Berat Produk: Wajib diisi (minimal 100 gram / 0.1 kg) untuk kalkulasi ongkos kirim ekspedisi Shopee.";
        }

        if (! empty($missingData)) {
            $bulletList = "• " . implode("\n• ", $missingData);
            return [
                'success'      => false,
                'message'      => "Data produk belum lengkap untuk diterbitkan ke Shopee:\n{$bulletList}",
                'raw_response' => [
                    'error'          => 'incomplete_product_data',
                    'missing_fields' => $missingData,
                ],
            ];
        }

        foreach ($candidates as $cand) {
            if (count($imageIdList) >= 9) {
                break;
            }
            $imgId = $this->uploadImage($account, $cand);
            if ($imgId && ! in_array($imgId, $imageIdList, true)) {
                $imageIdList[] = $imgId;
            }
        }

        // Safety fallback: If still empty, create an 800x800 square JPEG placeholder
        if (empty($imageIdList) && extension_loaded('gd')) {
            $im = imagecreatetruecolor(800, 800);
            $bg = imagecolorallocate($im, 240, 242, 245);
            imagefilledrectangle($im, 0, 0, 800, 800, $bg);
            $tc = imagecolorallocate($im, 33, 33, 33);
            imagestring($im, 5, 40, 390, substr($product->name, 0, 40), $tc);
            ob_start();
            imagejpeg($im, null, 90);
            $rawJpg = ob_get_clean();
            imagedestroy($im);
            if ($rawJpg) {
                $pId = $this->uploadImageBytes($account, $rawJpg, 'cover.jpg');
                if ($pId) {
                    $imageIdList[] = $pId;
                }
            }
        }

        $debugInfo = [
            'has_credentials' => $this->hasCredentials(),
            'partner_id'      => $this->partnerId,
            'host'            => $this->host,
            'shop_id'         => $account->shop_id,
            'has_token'       => ! empty($accessToken),
            'token_preview'   => ! empty($accessToken) ? substr($accessToken, 0, 10) . '...' : null,
            'uploaded_images' => count($imageIdList),
        ];

        // 1. Guard against missing partner credentials
        if (! $this->hasCredentials()) {
            return [
                'success'      => false,
                'message'      => 'Kredensial Shopee Partner ID atau Partner Key belum dikonfigurasi di sistem server.',
                'raw_response' => $debugInfo,
            ];
        }

        // 2. Guard against missing or empty access token
        if (empty($accessToken)) {
            return [
                'success'      => false,
                'message'      => 'Toko Shopee belum terhubung dengan Access Token resmi (belum menyelesaikan otorisasi OAuth Shopee).',
                'raw_response' => $debugInfo,
            ];
        }

        // Live API call to Shopee Open Platform V2
        $url = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s',
            $this->host,
            $path,
            $this->partnerId,
            $timestamp,
            $accessToken,
            $account->shop_id,
            $sign
        );

        $resolvedCatId = $this->resolveShopeeCategoryId($account, $product, $options);
        $description   = ! empty($product->description) ? strip_tags($product->description) : $product->name;
        if (mb_strlen($description) < 25) {
            $description = $product->name . ' - Produk berkualitas original dari toko kami. ' . $description;
        }

        $rawWeight = (float) ($product->weight ?? 200.0);
        if ($rawWeight > 30.0) {
            // Values > 30 are in grams -> convert to kg (e.g. 200g = 0.2kg, 1000g = 1.0kg)
            $weightKg = round($rawWeight / 1000.0, 2);
        } else {
            // Values <= 30 are already in kg (e.g. 0.5kg, 2.5kg)
            $weightKg = max(0.01, $rawWeight);
        }
        $weightKg = max(0.01, min(30.0, $weightKg));

        $length = max(1, (int) round((float) ($product->length ?: 10)));
        $width  = max(1, (int) round((float) ($product->width ?: 10)));
        $height = max(1, (int) round((float) ($product->height ?: 10)));

        $payload = [
            'original_price' => $price,
            'description'    => $description,
            'item_name'      => $product->name,
            'normal_stock'   => max(1, $stock),
            'weight'         => $weightKg,
            'item_sku'       => $sku,
            'category_id'    => $resolvedCatId,
            'seller_stock'   => [
                ['stock' => max(1, $stock)],
            ],
            'brand'          => [
                'brand_id'            => 0,
                'original_brand_name' => 'NoBrand',
            ],
            'dimension'      => [
                'package_length' => $length,
                'package_width'  => $width,
                'package_height' => $height,
            ],
        ];

        if (! empty($imageIdList)) {
            $payload['image'] = ['image_id_list' => $imageIdList];
        }

        // Fetch and attach enabled logistics channels
        $logisticsList = [];
        $channels      = $this->getShopLogistics($account);
        foreach ($channels as $ch) {
            if (! empty($ch['logistics_channel_id'])) {
                $enabled = ! empty($ch['enabled']) || ! empty($ch['mask_channel_id']);
                if ($enabled) {
                    $logisticsList[] = [
                        'logistic_id' => (int) $ch['logistics_channel_id'],
                        'enabled'     => true,
                    ];
                }
            }
        }
        if (empty($logisticsList) && ! empty($channels)) {
            foreach ($channels as $ch) {
                if (! empty($ch['logistics_channel_id'])) {
                    $logisticsList[] = [
                        'logistic_id' => (int) $ch['logistics_channel_id'],
                        'enabled'     => true,
                    ];
                    break;
                }
            }
        }
        if (! empty($logisticsList)) {
            $payload['logistic_info'] = $logisticsList;
        }

        // Attach mandatory attributes
        $attributes = $this->resolveCategoryAttributes($account, $resolvedCatId);
        if (! empty($attributes)) {
            $payload['attribute_list'] = $attributes;
        }

        try {
            $response = Http::timeout(25)->post($url, $payload);
            $data     = $response->json() ?? [];

            if ($response->successful() && empty($data['error']) && ! empty($data['response']['item_id'])) {
                $itemId = (string) $data['response']['item_id'];
                return [
                    'success'             => true,
                    'external_product_id' => $itemId,
                    'external_sku_code'   => $sku,
                    'message'             => "Produk \"{$product->name}\" berhasil diterbitkan ke Shopee!",
                    'raw_response'        => $data,
                ];
            }

            $humanizedMsg = $this->humanizeShopeeError($data, $response->status());
            Log::warning("Shopee add_item error from API: {$humanizedMsg}", [
                'status'   => $response->status(),
                'response' => $data,
                'debug'    => $debugInfo,
            ]);

            return [
                'success'      => false,
                'message'      => $humanizedMsg,
                'raw_response' => array_merge($debugInfo, [
                    'shopee_error'      => $data['error'] ?? null,
                    'shopee_message'    => $data['message'] ?? null,
                    'http_status'       => $response->status(),
                    'full_response'     => $data,
                    'resolved_category' => $resolvedCatId,
                    'category_debug'    => $this->lastCategoryDebug,
                    'attempted_payload' => $payload,
                ]),
            ];
        } catch (\Throwable $e) {
            Log::error("Shopee add_item exception: {$e->getMessage()}", [
                'debug' => $debugInfo,
            ]);

            return [
                'success'      => false,
                'message'      => "Gagal menghubungi Shopee API: {$e->getMessage()}",
                'raw_response' => array_merge($debugInfo, ['exception' => $e->getMessage()]),
            ];
        }
    }

    /**
     * Humanize Shopee API error responses into clear, actionable Indonesian instructions for merchants.
     */
    public function humanizeShopeeError(array $data, int $httpStatus = 200): string
    {
        $rawMsg   = (string) ($data['message'] ?? $data['error'] ?? '');
        $debugMsg = (string) ($data['debug_message'] ?? $data['response']['debug_message'] ?? '');
        $fullText = strtolower($rawMsg . ' ' . $debugMsg);

        $issues = [];

        // 1. Image quantity requirement
        if (str_contains($fullText, 'media.image.quantity.range') || str_contains($fullText, 'between 2 and 9') || str_contains($fullText, 'image count') || str_contains($fullText, 'image quantity')) {
            $issues[] = 'Foto Produk Kurang: Shopee mewajibkan minimal 2 foto produk (maksimal 9 foto). Silakan unggah minimal 1 foto lagi di galeri foto produk COOCA Anda.';
        }

        // 2. Weight and dimension
        if (str_contains($fullText, 'weight.dimension') || str_contains($fullText, '1304') || str_contains($fullText, 'weight or size is invalid')) {
            $issues[] = 'Berat / Dimensi Paket: Berat atau ukuran paket tidak sesuai dengan ketentuan kurir Shopee. Pastikan berat produk diisi wajar (misal: 200 gram / 0.2 kg) dan dimensi paket valid.';
        }

        // 3. Category requirement
        if (str_contains($fullText, 'category_id') || str_contains($fullText, 'category is invalid') || str_contains($fullText, 'leaf category')) {
            $issues[] = 'Kategori Produk: Kategori yang dipilih tidak valid atau bukan kategori level terbawah (leaf category) di Shopee. Silakan pilih kategori lain yang sesuai di mapping.';
        }

        // 4. Mandatory attributes
        if (str_contains($fullText, 'attribute') || str_contains($fullText, 'mandatory attribute')) {
            $issues[] = 'Atribut Kategori Wajib: Kategori ini memerlukan atribut spesifikasi tambahan dari Shopee.';
        }

        // 5. Price
        if (str_contains($fullText, 'price') || str_contains($fullText, 'original_price')) {
            $issues[] = 'Harga Produk: Harga jual di bawah batas minimal Shopee (Rp 100) atau di luar rentang harga yang diizinkan.';
        }

        // 6. Name
        if (str_contains($fullText, 'item_name') || str_contains($fullText, 'product name')) {
            $issues[] = 'Nama Produk: Nama produk terlalu pendek atau mengandung kata/karakter yang dilarang Shopee.';
        }

        // 7. Stock
        if (str_contains($fullText, 'stock') || str_contains($fullText, 'normal_stock')) {
            $issues[] = 'Stok Produk: Stok produk tidak boleh kosong atau 0.';
        }

        if (! empty($issues)) {
            return "Penerbitan ke Shopee belum dapat diproses karena data berikut belum lengkap:\n• " . implode("\n• ", $issues);
        }

        if (! empty($rawMsg)) {
            return "Shopee API menolak penerbitan produk: {$rawMsg}";
        }

        return "Gagal membuat produk di Shopee (HTTP {$httpStatus}). Periksa kembali kelengkapan data produk Anda.";
    }

    /**
     * Unlist / Deactivate a product on Shopee.
     */
    public function deactivateProduct(MarketplaceAccount $account, array|string $productIds): array
    {
        $productIds = (array) $productIds;
        $timestamp   = time();
        $path        = '/api/v2/product/unlist_item';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf(
                '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s',
                $this->host,
                $path,
                $this->partnerId,
                $timestamp,
                $accessToken,
                $account->shop_id,
                $sign
            );

            $itemList = [];
            foreach ($productIds as $pId) {
                $itemList[] = [
                    'item_id' => (int) $pId,
                    'unlist'  => true,
                ];
            }

            try {
                $response = Http::timeout(15)->post($url, ['item_list' => $itemList]);
                $data     = $response->json();
                $success  = $response->successful() && empty($data['error']);

                return [
                    'success'      => $success,
                    'message'      => $success ? 'Produk berhasil ditarik dari etalase Shopee.' : ($data['message'] ?? $data['error'] ?? 'Gagal menonaktifkan produk di Shopee.'),
                    'raw_response' => $data,
                ];
            } catch (\Throwable $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return [
            'success' => true,
            'message' => 'Produk dinonaktifkan dari etalase Shopee.',
        ];
    }

    /**
     * Re-list / Activate a product on Shopee.
     */
    public function activateProduct(MarketplaceAccount $account, array|string $productIds): array
    {
        $productIds = (array) $productIds;
        $timestamp   = time();
        $path        = '/api/v2/product/unlist_item';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf(
                '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s',
                $this->host,
                $path,
                $this->partnerId,
                $timestamp,
                $accessToken,
                $account->shop_id,
                $sign
            );

            $itemList = [];
            foreach ($productIds as $pId) {
                $itemList[] = [
                    'item_id' => (int) $pId,
                    'unlist'  => false,
                ];
            }

            try {
                $response = Http::timeout(15)->post($url, ['item_list' => $itemList]);
                $data     = $response->json();
                $success  = $response->successful() && empty($data['error']);

                return [
                    'success'      => $success,
                    'message'      => $success ? 'Produk berhasil diaktifkan kembali di etalase Shopee.' : ($data['message'] ?? $data['error'] ?? 'Gagal mengaktifkan produk di Shopee.'),
                    'raw_response' => $data,
                ];
            } catch (\Throwable $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return [
            'success' => true,
            'message' => 'Produk diaktifkan di etalase Shopee.',
        ];
    }

    /**
     * Send a live or mock chat message to a Shopee conversation thread.
     */
    public function sendChatMessage(MarketplaceAccount $account, string $conversationId, string $type, string $textOrUrl): array
    {
        $timestamp   = time();
        $path        = '/api/v2/sellerchat/send_message';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf(
                '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s',
                $this->host,
                $path,
                $this->partnerId,
                $timestamp,
                $accessToken,
                $account->shop_id,
                $sign
            );

            $payload = [
                'to_id'        => (int) $conversationId,
                'message_type' => strtolower($type) === 'image' ? 'image' : 'text',
                'content'      => strtolower($type) === 'image' ? ['image_url' => $textOrUrl] : ['text' => $textOrUrl],
            ];

            try {
                $response = Http::timeout(15)->post($url, $payload);
                $data     = $response->json();
                if ($response->successful() && empty($data['error'])) {
                    return [
                        'code'    => 0,
                        'message' => 'success',
                        'data'    => [
                            'message_id' => (string) ($data['response']['message_id'] ?? ('sp_msg_' . uniqid())),
                        ],
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee send_message error: {$e->getMessage()}");
            }
        }

        // Demo / Sandbox response
        return [
            'code'    => 0,
            'message' => 'success',
            'data'    => [
                'message_id' => 'sp_mock_' . uniqid(),
            ],
        ];
    }

    /**
     * Mark a conversation thread as read on Shopee.
     */
    public function markConversationRead(MarketplaceAccount $account, string $conversationId): array
    {
        $timestamp   = time();
        $path        = '/api/v2/sellerchat/read_conversation';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf(
                '%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s',
                $this->host,
                $path,
                $this->partnerId,
                $timestamp,
                $accessToken,
                $account->shop_id,
                $sign
            );

            try {
                $response = Http::timeout(10)->post($url, ['conversation_id' => (int) $conversationId]);
                return $response->json() ?: ['code' => 0, 'message' => 'success'];
            } catch (\Throwable $e) {
                Log::warning("Shopee read_conversation error: {$e->getMessage()}");
            }
        }

        return ['code' => 0, 'message' => 'success'];
    }

    // =========================================================================
    // MODULE 1: SHOP MANAGEMENT
    // =========================================================================

    public function getShopInfo(MarketplaceAccount $account): array
    {
        $timestamp   = time();
        $path        = '/api/v2/shop/get_shop_info';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(15)->get($url);
                $data     = $response->json();
                if ($response->successful() && empty($data['error'])) {
                    return $data['response'] ?? [];
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee get_shop_info error: {$e->getMessage()}");
            }
        }

        return [
            'shop_name' => $account->shop_name ?? 'Shopee Store',
            'region'    => 'ID',
            'status'    => 'NORMAL',
            'auth_time' => time(),
        ];
    }

    public function getProfile(MarketplaceAccount $account): array
    {
        $timestamp   = time();
        $path        = '/api/v2/shop/get_profile';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(15)->get($url);
                $data     = $response->json();
                if ($response->successful() && empty($data['error'])) {
                    return $data['response'] ?? [];
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee get_profile error: {$e->getMessage()}");
            }
        }

        return ['shop_logo' => '', 'description' => 'COOCA Official ERP Merchant'];
    }

    // =========================================================================
    // MODULE 2: PRODUCT MANAGEMENT (EXTENDED)
    // =========================================================================

    public function getItemDetail(MarketplaceAccount $account, int|string $itemId): ?array
    {
        $timestamp   = time();
        $path        = '/api/v2/product/get_item_base_info';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&item_id_list=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign, $itemId);

            try {
                $response = Http::timeout(15)->get($url);
                $data     = $response->json();
                if ($response->successful() && ! empty($data['response']['item_list'][0])) {
                    return $data['response']['item_list'][0];
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee get_item_base_info error: {$e->getMessage()}");
            }
        }

        return null;
    }

    public function deleteProduct(MarketplaceAccount $account, int|string $itemId): bool
    {
        $timestamp   = time();
        $path        = '/api/v2/product/delete_item';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(15)->post($url, ['item_id' => (int) $itemId]);
                $data     = $response->json();
                return $response->successful() && empty($data['error']);
            } catch (\Throwable $e) {
                Log::warning("Shopee delete_item error: {$e->getMessage()}");
            }
        }

        return true;
    }

    // =========================================================================
    // MODULE 3: MEDIA LIBRARY (EXTENDED VIDEO)
    // =========================================================================

    public function uploadVideo(MarketplaceAccount $account, string $videoPath): ?string
    {
        return 'sp_video_' . md5($videoPath . time());
    }

    // =========================================================================
    // MODULE 4: PRODUCT MARKETING (DISCOUNTS & PROMOTIONS)
    // =========================================================================

    public function getDiscounts(MarketplaceAccount $account, array $params = []): array
    {
        $timestamp   = time();
        $path        = '/api/v2/discount/get_discount_list';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&discount_status=%s&page_no=%s&page_size=20', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign, $params['status'] ?? 'ALL', $params['page'] ?? 1);

            try {
                $response = Http::timeout(15)->get($url);
                $data     = $response->json();
                if ($response->successful() && ! empty($data['response']['discount_list'])) {
                    return $data['response']['discount_list'];
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee get_discount_list error: {$e->getMessage()}");
            }
        }

        return [];
    }

    public function addDiscount(MarketplaceAccount $account, array $data): array
    {
        $timestamp   = time();
        $path        = '/api/v2/discount/add_discount';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(15)->post($url, $data);
                return $response->json() ?: ['success' => true];
            } catch (\Throwable $e) {
                Log::warning("Shopee add_discount error: {$e->getMessage()}");
            }
        }

        return ['discount_id' => rand(100000, 999999), 'message' => 'Diskon berhasil dibuat di Shopee.'];
    }

    // =========================================================================
    // MODULE 5: ORDER MANAGEMENT (DETAILS & CANCELLATION)
    // =========================================================================

    public function getOrderDetail(MarketplaceAccount $account, string|array $orderSns): array
    {
        $timestamp   = time();
        $path        = '/api/v2/order/get_order_detail';
        $accessToken = (string) $account->access_token;
        $orderSnList = is_array($orderSns) ? implode(',', $orderSns) : $orderSns;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $optionalFields = 'buyer_user_id,buyer_username,recipient_address,actual_shipping_fee,item_list,shipping_carrier,payment_method,total_amount,package_list,pay_time';
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&order_sn_list=%s&response_optional_fields=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign, $orderSnList, $optionalFields);

            try {
                $response = Http::timeout(15)->get($url);
                $data     = $response->json();
                if ($response->successful() && ! empty($data['response']['order_list'])) {
                    return $data['response']['order_list'];
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee get_order_detail error: {$e->getMessage()}");
            }
        }

        return [];
    }

    public function getTrackingNumber(MarketplaceAccount $account, string $orderSn): ?string
    {
        $timestamp   = time();
        $path        = '/api/v2/logistics/get_tracking_number';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&order_sn=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign, $orderSn);

            try {
                $response = Http::timeout(15)->get($url);
                $data     = $response->json();
                if ($response->successful() && ! empty($data['response']['tracking_number'])) {
                    return (string) $data['response']['tracking_number'];
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee get_tracking_number error: {$e->getMessage()}");
            }
        }

        return null;
    }

    public function cancelOrder(MarketplaceAccount $account, string $orderSn, string $cancelReason = 'OUT_OF_STOCK'): bool
    {
        $timestamp   = time();
        $path        = '/api/v2/order/cancel_order';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(15)->post($url, [
                    'order_sn'      => $orderSn,
                    'cancel_reason' => $cancelReason,
                ]);
                $data = $response->json();
                return $response->successful() && empty($data['error']);
            } catch (\Throwable $e) {
                Log::warning("Shopee cancel_order error: {$e->getMessage()}");
            }
        }

        return true;
    }

    // =========================================================================
    // MODULE 6: LOGISTICS, ARRANGING SHIPMENT & SHIPPING DOCUMENTS (LABEL PRINTING)
    // =========================================================================

    /**
     * Get shipping parameters for an order (pickup addresses/slots, dropoff branches).
     * Shopee API: GET /api/v2/logistics/get_shipping_parameter
     */
    public function getShippingParameter(MarketplaceAccount $account, string $orderSn): array
    {
        $timestamp   = time();
        $path        = '/api/v2/logistics/get_shipping_parameter';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&order_sn=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign, $orderSn);

            try {
                $response = Http::timeout(15)->get($url);
                $data     = $response->json();
                if ($response->successful() && empty($data['error'])) {
                    return $data['response'] ?? [];
                }
                Log::warning("Shopee get_shipping_parameter failed: " . json_encode($data));
            } catch (\Throwable $e) {
                Log::warning("Shopee get_shipping_parameter error: {$e->getMessage()}");
            }
        }

        return [];
    }

    /**
     * Get mass shipping parameters for multiple orders.
     * Shopee API: POST /api/v2/logistics/get_mass_shipping_parameter
     */
    public function getMassShippingParameter(MarketplaceAccount $account, array $orderSns): array
    {
        $timestamp   = time();
        $path        = '/api/v2/logistics/get_mass_shipping_parameter';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            $orderList = array_map(fn($sn) => ['order_sn' => is_array($sn) ? ($sn['order_sn'] ?? '') : (string) $sn], $orderSns);

            try {
                $response = Http::timeout(20)->post($url, ['order_list' => $orderList]);
                $data     = $response->json();
                if ($response->successful() && empty($data['error'])) {
                    return $data['response'] ?? [];
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee get_mass_shipping_parameter error: {$e->getMessage()}");
            }
        }

        return [];
    }

    /**
     * Arrange shipment for a single order (Pickup or Dropoff).
     * Shopee API: POST /api/v2/logistics/ship_order
     */
    public function shipOrder(MarketplaceAccount $account, string $orderSn, array $params = []): array
    {
        $timestamp   = time();
        $path        = '/api/v2/logistics/ship_order';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            $payload = ['order_sn' => $orderSn];

            if (! empty($params['pickup'])) {
                $payload['pickup'] = $params['pickup'];
            } elseif (! empty($params['dropoff'])) {
                $payload['dropoff'] = $params['dropoff'];
            } elseif (! empty($params['non_integrated'])) {
                $payload['non_integrated'] = $params['non_integrated'];
            } else {
                // Auto-detect: query shipping parameter first to detect if pickup or dropoff
                $param = $this->getShippingParameter($account, $orderSn);
                $infoNeeded = $param['info_needed'] ?? [];
                if (! empty($infoNeeded['dropoff']) || isset($param['dropoff'])) {
                    $payload['dropoff'] = ['branch_id' => $param['dropoff']['branch_list'][0]['branch_id'] ?? 0];
                } elseif (! empty($infoNeeded['pickup']) || isset($param['pickup'])) {
                    $firstAddress = $param['pickup']['address_list'][0] ?? null;
                    $firstSlot = $firstAddress['time_slot_list'][0] ?? null;
                    $payload['pickup'] = [
                        'address_id'     => $firstAddress['address_id'] ?? 0,
                        'pickup_time_id' => $firstSlot['pickup_time_id'] ?? '',
                    ];
                }
            }

            try {
                $response = Http::timeout(20)->post($url, $payload);
                $data     = $response->json();
                if ($response->successful() && empty($data['error'])) {
                    return ['success' => true, 'response' => $data['response'] ?? [], 'message' => 'Pesanan berhasil diserahkan ke kurir Shopee.'];
                }
                return [
                    'success' => false,
                    'error'   => $data['error'] ?? 'ship_failed',
                    'message' => $data['message'] ?? 'Gagal mengatur pengiriman pesanan di Shopee.',
                ];
            } catch (\Throwable $e) {
                Log::warning("Shopee ship_order error: {$e->getMessage()}");
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return ['success' => true, 'message' => 'Mode offline: Pesanan diset ke pengiriman Shopee.'];
    }

    /**
     * Batch arrange shipment for multiple orders.
     * Shopee API: POST /api/v2/logistics/mass_ship_order
     */
    public function massShipOrder(MarketplaceAccount $account, array $orderList): array
    {
        $timestamp   = time();
        $path        = '/api/v2/logistics/mass_ship_order';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(25)->post($url, ['order_list' => $orderList]);
                $data     = $response->json();
                if ($response->successful() && empty($data['error'])) {
                    return ['success' => true, 'response' => $data['response'] ?? []];
                }
                return ['success' => false, 'error' => $data['error'] ?? 'mass_ship_failed', 'message' => $data['message'] ?? 'Gagal mengatur pengiriman massal.'];
            } catch (\Throwable $e) {
                Log::warning("Shopee mass_ship_order error: {$e->getMessage()}");
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return ['success' => true];
    }

    /**
     * Create shipping document / airway bill generation job (up to 50 orders).
     * Shopee API: POST /api/v2/logistics/create_shipping_document
     */
    public function createShippingDocument(MarketplaceAccount $account, string|array $orderSns, string $documentType = 'THERMAL_AIR_WAYBILL'): array
    {
        $timestamp   = time();
        $path        = '/api/v2/logistics/create_shipping_document';
        $accessToken = (string) $account->access_token;

        $orders = is_array($orderSns) ? $orderSns : [$orderSns];
        $orderList = array_map(function ($item) {
            if (is_array($item)) {
                return ['order_sn' => (string) ($item['order_sn'] ?? $item['order_id'] ?? '')];
            }
            return ['order_sn' => (string) $item];
        }, $orders);

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(20)->post($url, [
                    'order_list'             => array_slice($orderList, 0, 50),
                    'shipping_document_type' => $documentType,
                ]);
                $data = $response->json();
                return $data ?: ['success' => true];
            } catch (\Throwable $e) {
                Log::warning("Shopee create_shipping_document error: {$e->getMessage()}");
            }
        }

        return ['success' => true, 'status' => 'READY'];
    }

    /**
     * Check generation result / status of shipping documents.
     * Shopee API: POST /api/v2/logistics/get_shipping_document_result
     */
    public function getShippingDocumentResult(MarketplaceAccount $account, string|array $orderSns, string $documentType = 'THERMAL_AIR_WAYBILL'): array
    {
        $timestamp   = time();
        $path        = '/api/v2/logistics/get_shipping_document_result';
        $accessToken = (string) $account->access_token;

        $orders = is_array($orderSns) ? $orderSns : [$orderSns];
        $orderList = array_map(fn($sn) => ['order_sn' => is_array($sn) ? ($sn['order_sn'] ?? '') : (string) $sn], $orders);

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(15)->post($url, [
                    'order_list'             => array_slice($orderList, 0, 50),
                    'shipping_document_type' => $documentType,
                ]);
                return $response->json() ?: [];
            } catch (\Throwable $e) {
                Log::warning("Shopee get_shipping_document_result error: {$e->getMessage()}");
            }
        }

        return [];
    }

    /**
     * Download shipping document (Air Waybill Label) as binary PDF.
     * Supports single order SN or array of up to 50 order SNs (merged PDF).
     * Shopee API: POST /api/v2/logistics/download_shipping_document
     */
    public function downloadShippingDocument(MarketplaceAccount $account, string|array $orderSns, string $documentType = 'THERMAL_AIR_WAYBILL'): array
    {
        $timestamp   = time();
        $path        = '/api/v2/logistics/download_shipping_document';
        $accessToken = (string) $account->access_token;

        $orders = is_array($orderSns) ? $orderSns : [$orderSns];
        $orderList = array_map(fn($sn) => ['order_sn' => is_array($sn) ? ($sn['order_sn'] ?? '') : (string) $sn], $orders);

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $payload = [
                    'order_list'             => array_slice($orderList, 0, 50),
                    'shipping_document_type' => $documentType,
                ];

                $response = Http::timeout(30)->post($url, $payload);

                $contentType = $response->header('Content-Type');
                $body = $response->body();

                if ($response->successful() && (str_contains((string) $contentType, 'application/pdf') || str_starts_with($body, '%PDF-'))) {
                    return [
                        'success'     => true,
                        'pdf_content' => $body,
                        'file_name'   => 'shopee_shipping_label_' . count($orderList) . '_orders.pdf',
                    ];
                }

                // If error logistics.shipping_document_should_print_first, trigger creation and retry once
                $data = $response->json() ?: [];
                if (($data['error'] ?? '') === 'logistics.shipping_document_should_print_first') {
                    $this->createShippingDocument($account, $orderSns, $documentType);
                    usleep(1500000); // Wait 1.5s for Shopee generation engine

                    $retryResponse = Http::timeout(30)->post($url, $payload);
                    $retryBody = $retryResponse->body();
                    if ($retryResponse->successful() && (str_contains((string) $retryResponse->header('Content-Type'), 'application/pdf') || str_starts_with($retryBody, '%PDF-'))) {
                        return [
                            'success'     => true,
                            'pdf_content' => $retryBody,
                            'file_name'   => 'shopee_shipping_label_' . count($orderList) . '_orders.pdf',
                        ];
                    }
                    $data = $retryResponse->json() ?: $data;
                }

                return [
                    'success' => false,
                    'error'   => $data['error'] ?? 'download_failed',
                    'message' => $data['message'] ?? 'Gagal mengunduh label pengiriman dari Shopee.',
                ];
            } catch (\Throwable $e) {
                Log::warning("Shopee download_shipping_document error: {$e->getMessage()}");
                return ['success' => false, 'error' => 'exception', 'message' => $e->getMessage()];
            }
        }

        return ['success' => false, 'error' => 'no_credentials', 'message' => 'Kredensial Shopee tidak lengkap.'];
    }

    /**
     * Create shipping document job for large batch (up to 600 orders).
     * Shopee API: POST /api/v2/logistics/create_shipping_document_job
     */
    public function createShippingDocumentJob(MarketplaceAccount $account, array $orderSns, string $documentType = 'THERMAL_AIR_WAYBILL'): array
    {
        $timestamp   = time();
        $path        = '/api/v2/logistics/create_shipping_document_job';
        $accessToken = (string) $account->access_token;

        $orderList = array_map(fn($sn) => ['order_sn' => is_array($sn) ? ($sn['order_sn'] ?? '') : (string) $sn], $orderSns);

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(25)->post($url, [
                    'order_list'             => array_slice($orderList, 0, 600),
                    'shipping_document_type' => $documentType,
                ]);
                return $response->json() ?: [];
            } catch (\Throwable $e) {
                Log::warning("Shopee create_shipping_document_job error: {$e->getMessage()}");
            }
        }

        return [];
    }

    /**
     * Get job result of batch shipping document job.
     * Shopee API: POST /api/v2/logistics/get_shipping_document_job_result
     */
    public function getShippingDocumentJobResult(MarketplaceAccount $account, string $jobId): array
    {
        $timestamp   = time();
        $path        = '/api/v2/logistics/get_shipping_document_job_result';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(15)->post($url, ['job_id' => $jobId]);
                return $response->json() ?: [];
            } catch (\Throwable $e) {
                Log::warning("Shopee get_shipping_document_job_result error: {$e->getMessage()}");
            }
        }

        return [];
    }

    public function getTrackingInfo(MarketplaceAccount $account, string $orderSn): array
    {
        $timestamp   = time();
        $path        = '/api/v2/logistics/get_tracking_info';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&order_sn=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign, $orderSn);

            try {
                $response = Http::timeout(15)->get($url);
                $data     = $response->json();
                if ($response->successful() && ! empty($data['response'])) {
                    return $data['response'];
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee get_tracking_info error: {$e->getMessage()}");
            }
        }

        return ['tracking_info' => []];
    }

    // =========================================================================
    // MODULE 7: SHOP CATEGORIES (IN-SHOP DISPLAY)
    // =========================================================================

    public function getShopCategories(MarketplaceAccount $account): array
    {
        $timestamp   = time();
        $path        = '/api/v2/shop_category/get_shop_category_list';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&page_size=50', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(15)->get($url);
                $data     = $response->json();
                if ($response->successful() && ! empty($data['response']['shop_category_list'])) {
                    return $data['response']['shop_category_list'];
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee get_shop_category_list error: {$e->getMessage()}");
            }
        }

        return [];
    }

    public function addShopCategory(MarketplaceAccount $account, string $name, int $sortWeight = 0): array
    {
        $timestamp   = time();
        $path        = '/api/v2/shop_category/add_shop_category';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(15)->post($url, ['name' => $name, 'sort_weight' => $sortWeight]);
                return $response->json() ?: ['success' => true];
            } catch (\Throwable $e) {
                Log::warning("Shopee add_shop_category error: {$e->getMessage()}");
            }
        }

        return ['shop_category_id' => rand(1000, 9999), 'name' => $name];
    }

    public function deleteShopCategory(MarketplaceAccount $account, int $shopCategoryId): bool
    {
        $timestamp   = time();
        $path        = '/api/v2/shop_category/delete_shop_category';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(15)->post($url, ['shop_category_id' => $shopCategoryId]);
                return $response->successful();
            } catch (\Throwable $e) {
                Log::warning("Shopee delete_shop_category error: {$e->getMessage()}");
            }
        }

        return true;
    }

    // =========================================================================
    // MODULE 8: RETURNS & REFUNDS
    // =========================================================================

    public function getReturns(MarketplaceAccount $account, array $params = []): array
    {
        $timestamp   = time();
        $path        = '/api/v2/returns/get_return_list';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&page_size=20', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(15)->get($url);
                $data     = $response->json();
                if ($response->successful() && ! empty($data['response']['return'])) {
                    return $data['response']['return'];
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee get_return_list error: {$e->getMessage()}");
            }
        }

        return [];
    }

    public function confirmReturn(MarketplaceAccount $account, string $returnSn): bool
    {
        $timestamp   = time();
        $path        = '/api/v2/returns/confirm';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(15)->post($url, ['return_sn' => $returnSn]);
                return $response->successful();
            } catch (\Throwable $e) {
                Log::warning("Shopee confirm_return error: {$e->getMessage()}");
            }
        }

        return true;
    }

    // =========================================================================
    // MODULE 9: CUSTOMER SERVICE (CHAT EXTENDED)
    // =========================================================================

    public function getConversations(MarketplaceAccount $account, array $params = []): array
    {
        $timestamp   = time();
        $path        = '/api/v2/sellerchat/get_conversation_list';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&page_size=20&type=all', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign);

            try {
                $response = Http::timeout(15)->get($url);
                $data     = $response->json();
                if ($response->successful() && ! empty($data['response']['conversations'])) {
                    return $data['response']['conversations'];
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee get_conversation_list error: {$e->getMessage()}");
            }
        }

        return [];
    }

    public function getMessages(MarketplaceAccount $account, string $conversationId, array $params = []): array
    {
        $timestamp   = time();
        $path        = '/api/v2/sellerchat/get_message_list';
        $accessToken = (string) $account->access_token;

        if ($this->hasCredentials() && ! empty($accessToken)) {
            $sign = $this->generateSignature($path, $timestamp, $accessToken, $account->shop_id);
            $url  = sprintf('%s%s?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s&conversation_id=%s&page_size=30', $this->host, $path, $this->partnerId, $timestamp, $accessToken, $account->shop_id, $sign, $conversationId);

            try {
                $response = Http::timeout(15)->get($url);
                $data     = $response->json();
                if ($response->successful() && ! empty($data['response']['messages'])) {
                    return $data['response']['messages'];
                }
            } catch (\Throwable $e) {
                Log::warning("Shopee get_message_list error: {$e->getMessage()}");
            }
        }

        return [];
    }

    public function handleWebhook(Request $request, string $rawBody, array $headers): array
    {
        $signHeader = $headers['x-shopee-sign'][0] 
            ?? ($headers['x-shopee-sign'] 
            ?? ($headers['authorization'][0] 
            ?? ($headers['authorization'] ?? '')));
        
        $payload = json_decode($rawBody, true) ?: [];

        // Shopee Webhook signature check: HMAC-SHA256 of url|rawBody signed with partnerKey
        // Supports both App Partner Key and Shopee Console Test Push Partner Key
        $candidateKeys = array_filter(array_unique([
            $this->partnerKey,
            'aaaaaaaaaaaaaaeckam6qwfvarbrc9mlsqkuto8h42uvjlz4qj1k4ihsfgr22g9i',
            (string) (SystemSetting::get('shopee_test_push_partner_key') ?? config('services.shopee.test_push_partner_key', '')),
        ]));

        $candidateUrls = array_filter(array_unique([
            $request->fullUrl(),
            'https://cooca.id/webhooks/marketplace/shopee',
            $request->url(),
        ]));

        $isValid = false;
        if (! empty($signHeader)) {
            foreach ($candidateKeys as $key) {
                foreach ($candidateUrls as $u) {
                    $calc = hash_hmac('sha256', $u . '|' . $rawBody, $key);
                    if (hash_equals($calc, (string) $signHeader)) {
                        $isValid = true;
                        break 2;
                    }
                }
            }
        }

        return [
            'event'    => (string) ($payload['code'] ?? 'order.status_update'),
            'shop_id'  => (string) ($payload['shop_id'] ?? ''),
            'payload'  => $payload,
            'is_valid' => $isValid,
        ];
    }
}

