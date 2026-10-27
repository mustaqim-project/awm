<?php

declare(strict_types=1);

namespace App\Domain\Marketplace;

use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceProductMapping;
use App\Models\MarketplaceSyncLog;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Support\Facades\Log;

class MarketplaceSyncService
{
    public function __construct(protected MarketplaceManagerService $manager) {}

    /**
     * Push price for a specific product mapping.
     */
    public function syncProductPrice(MarketplaceProductMapping $mapping): bool
    {
        $account = $mapping->account;
        if (! $account) {
            $account = MarketplaceAccount::where('business_id', $mapping->business_id)
                ->where('channel', $mapping->channel)
                ->first();
            if ($account) {
                $mapping->marketplace_account_id = $account->id;
                $mapping->save();
            }
        }

        if (! $account || ! $account->isConnected() || ! ($account->is_active ?? true)) {
            return false;
        }

        $adapter = $this->manager->driver($mapping->channel);
        $price   = $mapping->getEffectivePrice();

        try {
            $ok = $adapter->pushPrice($account, $mapping, $price);

            if ($ok) {
                $mapping->last_price_synced_at = now();
                $mapping->last_sync_error      = null;
                $mapping->sync_status          = MarketplaceProductMapping::STATUS_SYNCED;
                $mapping->save();

                MarketplaceSyncLog::log(
                    businessId: $mapping->business_id,
                    channel: $mapping->channel,
                    entityType: 'price',
                    action: 'push_price',
                    status: 'success',
                    entityId: $mapping->id,
                    payload: ['price' => $price, 'external_id' => $mapping->external_product_id],
                    marketplaceAccountId: $account->id
                );
            } else {
                $mapping->last_sync_error = 'Gagal sinkronisasi harga ke ' . $mapping->channel;
                $mapping->sync_status     = MarketplaceProductMapping::STATUS_FAILED;
                $mapping->save();
            }

            return $ok;
        } catch (\Throwable $e) {
            $mapping->last_sync_error = $e->getMessage();
            $mapping->sync_status     = MarketplaceProductMapping::STATUS_FAILED;
            $mapping->save();

            MarketplaceSyncLog::log(
                businessId: $mapping->business_id,
                channel: $mapping->channel,
                entityType: 'price',
                action: 'push_price',
                status: 'failed',
                entityId: $mapping->id,
                error_message: $e->getMessage(),
                marketplaceAccountId: $account->id
            );

            return false;
        }
    }

    /**
     * Resolve effective available stock considering inventory stock, safety buffer, and manual override.
     */
    public function resolveEffectiveStock(Product $product, ?MarketplaceProductMapping $mapping = null): int
    {
        if ($mapping && ! $mapping->sync_stock_auto && $mapping->custom_stock !== null) {
            return max(0, (int) $mapping->custom_stock);
        }

        if ($mapping && ! $mapping->sync_stock_auto && $mapping->channel_stock !== null) {
            return max(0, (int) $mapping->channel_stock);
        }

        $rawStock = (float) ($product->calculateEffectiveAvailableStock() ?? 0);

        if ($rawStock <= 0) {
            $rawStock = (float) InventoryStock::where('business_id', $product->business_id)
                ->where('product_id', $product->id)
                ->sum('quantity');
        }

        if ($rawStock <= 0 && isset($product->attributes['stock'])) {
            $rawStock = (float) $product->attributes['stock'];
        } elseif ($rawStock <= 0 && isset($product->stock)) {
            $rawStock = (float) $product->stock;
        }

        $buffer = (int) ($mapping?->stock_buffer ?? $mapping?->account?->stock_buffer ?? 0);

        return max(0, (int) floor($rawStock - $buffer));
    }

    /**
     * Push stock for a specific product mapping.
     */
    public function syncProductStock(MarketplaceProductMapping $mapping): bool
    {
        $account = $mapping->account;
        if (! $account) {
            $account = MarketplaceAccount::where('business_id', $mapping->business_id)
                ->where('channel', $mapping->channel)
                ->first();
            if ($account) {
                $mapping->marketplace_account_id = $account->id;
                $mapping->save();
            }
        }
        $product = $mapping->product;

        if (! $account || ! $account->isConnected() || ! ($account->is_active ?? true) || ! $product) {
            return false;
        }

        $adapter = $this->manager->driver($mapping->channel);
        $stockToPush = $this->resolveEffectiveStock($product, $mapping);

        try {
            $ok = $adapter->pushStock($account, $mapping, $stockToPush);

            if ($ok) {
                $mapping->last_stock_synced_at = now();
                $mapping->last_sync_error      = null;
                $mapping->sync_status          = MarketplaceProductMapping::STATUS_SYNCED;
                $mapping->save();

                MarketplaceSyncLog::log(
                    businessId: $mapping->business_id,
                    channel: $mapping->channel,
                    entityType: 'stock',
                    action: 'push_stock',
                    status: 'success',
                    entityId: $mapping->id,
                    payload: ['stock' => $stockToPush, 'external_id' => $mapping->external_product_id],
                    marketplaceAccountId: $account->id
                );
            } else {
                $mapping->last_sync_error = 'Gagal sinkronisasi stok ke ' . $mapping->channel;
                $mapping->sync_status     = MarketplaceProductMapping::STATUS_FAILED;
                $mapping->save();
            }

            return $ok;
        } catch (\Throwable $e) {
            $mapping->last_sync_error = $e->getMessage();
            $mapping->sync_status     = MarketplaceProductMapping::STATUS_FAILED;
            $mapping->save();

            MarketplaceSyncLog::log(
                businessId: $mapping->business_id,
                channel: $mapping->channel,
                entityType: 'stock',
                action: 'push_stock',
                status: 'failed',
                entityId: $mapping->id,
                error_message: $e->getMessage(),
                marketplaceAccountId: $account->id
            );

            return false;
        }
    }

    /**
     * Sync price & stock for all channels mapped to a given product.
     */
    public function syncProductToAllChannels(Product $product): array
    {
        $mappings = MarketplaceProductMapping::where('product_id', $product->id)->get();
        $results  = [];

        foreach ($mappings as $mapping) {
            $priceOk = $mapping->sync_price_auto ? $this->syncProductPrice($mapping) : true;
            $stockOk = $mapping->sync_stock_auto ? $this->syncProductStock($mapping) : true;

            $results[$mapping->channel] = [
                'price' => $priceOk,
                'stock' => $stockOk,
            ];
        }

        return $results;
    }

    /**
     * Trigger batch sync for all mapped products of a business/account.
     */
    public function syncAllForAccount(MarketplaceAccount $account): array
    {
        if (! $account->isConnected() || ! ($account->is_active ?? true)) {
            return [
                'total'   => 0,
                'success' => 0,
                'failed'  => 0,
            ];
        }

        $mappings = MarketplaceProductMapping::where('business_id', $account->business_id)
            ->where(function ($q) use ($account) {
                $q->where('marketplace_account_id', $account->id)
                  ->orWhere('channel', $account->channel);
            })
            ->where('is_active', true)
            ->get();

        $successCount = 0;
        $failCount    = 0;

        foreach ($mappings as $mapping) {
            if (empty($mapping->marketplace_account_id)) {
                $mapping->marketplace_account_id = $account->id;
                $mapping->save();
            }

            $okPrice = $mapping->sync_price_auto ? $this->syncProductPrice($mapping) : true;
            $okStock = $mapping->sync_stock_auto ? $this->syncProductStock($mapping) : true;

            if ($okPrice && $okStock) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $account->last_synced_at = now();
        $account->save();

        return [
            'total'   => $mappings->count(),
            'success' => $successCount,
            'failed'  => $failCount,
        ];
    }

    /**
     * Upload and publish a new product listing with all its gallery images directly from COOCA to a marketplace channel.
     *
     * @param array<string, mixed> $options
     * @return array{success: bool, channel_name?: string, mapping?: MarketplaceProductMapping, external_product_id?: string, message?: string}
     */
    public function publishProductToChannel(Product $product, string $channel, array $options = []): array
    {
        $normalizedChannel = match (strtolower($channel)) {
            'shopee'                      => 'shopee',
            'tiktok', 'tiktok-tokopedia' => 'tiktok_shop',
            'tokopedia'                   => 'tokopedia',
            default                       => $channel,
        };

        $account = MarketplaceAccount::where('business_id', $product->business_id)
            ->where('channel', $normalizedChannel)
            ->where('status', MarketplaceAccount::STATUS_CONNECTED)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            return [
                'success' => false,
                'message' => "Akun marketplace {$channel} belum terhubung atau sedang tidak aktif.",
            ];
        }

        // GUARDRAIL 1: Sektor Jasa / Layanan Fisik
        if ($product->isService()) {
            return [
                'success' => false,
                'message' => "Produk berjenis jasa/layanan fisik (\"{$product->name}\") tidak dapat diterbitkan ke saluran ekspedisi marketplace.",
            ];
        }

        // GUARDRAIL 2: Sektor Apotek / Farmasi - BPOM RI Hard-Lock
        if ($product->business?->isPharmacy() && $product->isRestrictedPharmacyProduct()) {
            return [
                'success' => false,
                'message' => "Produk \"{$product->name}\" tergolong obat keras / resep dokter yang dilarang diperjualbelikan di marketplace umum berdasarkan regulasi BPOM RI.",
            ];
        }

        // GUARDRAIL 3: Anti-Margin Bleed Guard
        $syncPriceAuto   = ! empty($options['sync_price_auto'] ?? true);
        $priceMultiplier = (float) ($options['price_multiplier'] ?? 1.00);
        $channelPrice    = ! empty($options['channel_price']) ? (float) $options['channel_price'] : null;

        $effectivePrice = $syncPriceAuto 
            ? round((float) $product->selling_price * $priceMultiplier)
            : (float) ($channelPrice ?? $product->selling_price);

        if ($effectivePrice <= 0) {
            return [
                'success' => false,
                'message' => 'Harga jual saluran tidak boleh Rp 0 atau kosong untuk mencegah risiko kerugian pesanan gratis.',
            ];
        }

        $baseCost = (float) ($product->base_cost ?? 0.0);

        if ($baseCost > 0 && $effectivePrice < $baseCost && empty($options['allow_below_cost'])) {
            $diffFormatted = number_format($baseCost - $effectivePrice, 0, ',', '.');
            $costFormatted = number_format($baseCost, 0, ',', '.');
            return [
                'success' => false,
                'message' => "Harga jual saluran (Rp " . number_format($effectivePrice, 0, ',', '.') . ") berada di bawah modal dasar HPP (Rp {$costFormatted}). Potensi kerugian Rp {$diffFormatted}/unit.",
            ];
        }

        $effectiveStock = isset($options['custom_stock']) && $options['custom_stock'] !== null && $options['custom_stock'] !== ''
            ? (int) $options['custom_stock']
            : $this->resolveEffectiveStock($product);

        // Resolve category from hierarchy: 1. Explicit options -> 2. Category parent -> 3. Smart suggestion
        $resolvedCategoryId   = $options['category_id'] ?? $options['marketplace_category_id'] ?? $product->category?->marketplace_category_id;
        $resolvedCategoryName = $options['category_name'] ?? $options['marketplace_category_name'] ?? $product->category?->marketplace_category_name;

        if (empty($resolvedCategoryId)) {
            $suggested = MarketplaceCategoryRegistry::suggestCategory($product);
            $resolvedCategoryId   = $suggested['id'];
            $resolvedCategoryName = $suggested['name'];
        }

        $adapter = $this->manager->driver($normalizedChannel);

        // MENCEGAH DUPLIKASI DATA DI SELLER CENTER TIKTOK SHOP / MARKETPLACE:
        // Jika produk sudah memiliki listing aktif (external_product_id) dan bukan dipaksa 'force_new_listing',
        // lakukan update harga & stok pada listing yang sudah ada, JANGAN buat produk baru lagi!
        $existingMapping = MarketplaceProductMapping::where('business_id', $product->business_id)
            ->where('product_id', $product->id)
            ->where('channel', $normalizedChannel)
            ->first();

        $isRealExternalId = ! empty($existingMapping->external_product_id) 
            && ! str_starts_with((string) $existingMapping->external_product_id, 'SP')
            && ! str_starts_with((string) $existingMapping->external_product_id, 'TKP');

        if ($existingMapping && $isRealExternalId && empty($options['force_new_listing'])) {
            $existingMapping->marketplace_account_id = $account->id;
            $existingMapping->channel_price         = $channelPrice;
            $existingMapping->sync_price_auto       = $syncPriceAuto;
            $existingMapping->price_multiplier      = $priceMultiplier;
            $existingMapping->custom_stock          = isset($options['custom_stock']) && $options['custom_stock'] !== '' ? (int) $options['custom_stock'] : null;
            $existingMapping->sync_stock_auto       = ! empty($options['sync_stock_auto'] ?? true);
            $existingMapping->stock_buffer          = (int) ($options['stock_buffer'] ?? 0);
            $existingMapping->is_active             = true;
            $existingMapping->sync_status           = MarketplaceProductMapping::STATUS_SYNCED;
            $existingMapping->save();

            $priceOk = $this->syncProductPrice($existingMapping);
            $stockOk = $this->syncProductStock($existingMapping);

            return [
                'success'             => true,
                'channel_name'        => $adapter->getName(),
                'mapping'             => $existingMapping,
                'external_product_id' => $existingMapping->external_product_id,
                'message'             => "Produk sudah terdaftar di {$adapter->getName()} (Item ID: {$existingMapping->external_product_id}). Harga & stok listing yang ada berhasil diperbarui (mencegah duplikat baru di seller center).",
                'is_updated'          => true,
            ];
        }

        try {
            $publishResult = $adapter->publishProduct($account, $product, array_merge($options, [
                'price'         => $effectivePrice,
                'stock'         => $effectiveStock,
                'sku'           => $product->code ?: ('SKU-' . $product->id),
                'category_id'   => $resolvedCategoryId,
                'category_name' => $resolvedCategoryName,
            ]));

            if (empty($publishResult['success']) || empty($publishResult['external_product_id'])) {
                return [
                    'success' => false,
                    'message' => $publishResult['message'] ?? 'Gagal memproses penerbitan produk ke marketplace.',
                ];
            }

            $externalId  = (string) $publishResult['external_product_id'];
            $externalSku = $publishResult['external_sku_code'] ?? $product->code;

            $mapping = MarketplaceProductMapping::updateOrCreate(
                [
                    'business_id' => $product->business_id,
                    'product_id'  => $product->id,
                    'channel'     => $normalizedChannel,
                ],
                [
                    'marketplace_account_id' => $account->id,
                    'external_product_id'   => $externalId,
                    'external_sku_id'       => $publishResult['external_sku_id'] ?? null,
                    'external_sku_code'     => $externalSku,
                    'external_product_name' => $product->name,
                    'channel_price'         => $channelPrice,
                    'sync_price_auto'       => $syncPriceAuto,
                    'price_multiplier'      => $priceMultiplier,
                    'custom_stock'          => isset($options['custom_stock']) && $options['custom_stock'] !== '' ? (int) $options['custom_stock'] : null,
                    'sync_stock_auto'       => ! empty($options['sync_stock_auto'] ?? true),
                    'stock_buffer'          => (int) ($options['stock_buffer'] ?? 0),
                    'sync_status'           => MarketplaceProductMapping::STATUS_SYNCED,
                    'last_price_synced_at'  => now(),
                    'last_stock_synced_at'  => now(),
                    'last_sync_error'       => null,
                    'is_active'             => true,
                    'raw_metadata'          => array_merge(
                        $publishResult['raw_response'] ?? [],
                        [
                            'category_id'   => $resolvedCategoryId,
                            'category_name' => $resolvedCategoryName,
                        ]
                    ),
                ]
            );


            MarketplaceSyncLog::log(
                businessId: $product->business_id,
                channel: $normalizedChannel,
                entityType: 'product',
                action: 'publish_product',
                status: 'success',
                entityId: $mapping->id,
                payload: [
                    'product_id'          => $product->id,
                    'external_product_id' => $externalId,
                    'price'               => $effectivePrice,
                    'stock'               => $effectiveStock,
                    'images_count'        => $product->images->count() + ($product->image ? 1 : 0),
                ],
                marketplaceAccountId: $account->id
            );

            return [
                'success'             => true,
                'channel_name'        => $adapter->getName(),
                'mapping'             => $mapping,
                'external_product_id' => $externalId,
                'message'             => $publishResult['message'] ?? "Produk \"{$product->name}\" berhasil diterbitkan ke {$adapter->getName()}.",
            ];
        } catch (\Throwable $e) {
            MarketplaceSyncLog::log(
                businessId: $product->business_id,
                channel: $normalizedChannel,
                entityType: 'product',
                action: 'publish_product',
                status: 'failed',
                entityId: $product->id,
                error_message: $e->getMessage(),
                marketplaceAccountId: $account->id
            );

            return [
                'success' => false,
                'message' => "Gagal menerbitkan produk ke {$adapter->getName()}: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Deactivate / delist a product from a marketplace channel, or all channels if $channel === 'all'.
     */
    public function deactivateProductOnChannel(Product $product, string $channel): array
    {
        if (strtolower($channel) === 'all') {
            return $this->deactivateProductOnAllChannels($product);
        }

        $normalizedChannel = match (strtolower($channel)) {
            'shopee'                      => 'shopee',
            'tiktok', 'tiktok-tokopedia' => 'tiktok_shop',
            'tokopedia'                   => 'tokopedia',
            default                       => $channel,
        };

        $mapping = MarketplaceProductMapping::where('business_id', $product->business_id)
            ->where('product_id', $product->id)
            ->where('channel', $normalizedChannel)
            ->first();

        if (! $mapping || empty($mapping->external_product_id)) {
            return [
                'success' => false,
                'message' => 'Produk ini belum memiliki listing terhubung di channel tersebut.',
            ];
        }

        $account = $mapping->account ?? MarketplaceAccount::where('business_id', $product->business_id)
            ->where('channel', $normalizedChannel)
            ->first();

        if (! $account) {
            $mapping->is_active = false;
            $mapping->sync_status = MarketplaceProductMapping::STATUS_PAUSED;
            $mapping->save();

            return [
                'success' => true,
                'message' => 'Akun marketplace tidak terhubung, listing berhasil dinonaktifkan di sistem COOCA.',
            ];
        }

        $adapter = $this->manager->driver($normalizedChannel);

        // 1. If adapter supports deactivateProduct (e.g. TikTok Shop API, Shopee unlist_item, Tokopedia set-status)
        if (method_exists($adapter, 'deactivateProduct')) {
            $res = $adapter->deactivateProduct($account, $mapping->external_product_id);
            if (! empty($res['success'])) {
                $mapping->is_active = false;
                $mapping->sync_status = MarketplaceProductMapping::STATUS_PAUSED;
                $mapping->save();

                MarketplaceSyncLog::log(
                    businessId: $product->business_id,
                    channel: $normalizedChannel,
                    entityType: 'product',
                    action: 'deactivate_product',
                    status: 'success',
                    entityId: $mapping->id,
                    payload: ['external_product_id' => $mapping->external_product_id],
                    marketplaceAccountId: $account->id
                );

                return [
                    'success' => true,
                    'message' => "Listing \"{$product->name}\" berhasil dinonaktifkan dari etalase {$adapter->getName()}.",
                ];
            }

            return $res;
        }

        // 2. Fallback: Set stock to 0 to make it unavailable/out-of-stock
        $mapping->is_active = false;
        $mapping->custom_stock = 0;
        $mapping->sync_stock_auto = false;
        $mapping->save();
        $this->syncProductStock($mapping);

        return [
            'success' => true,
            'message' => "Sinkronisasi dimatikan dan stok saluran diset 0 unit (Habis).",
        ];
    }

    /**
     * Deactivate / delist product from all mapped marketplace channels.
     */
    public function deactivateProductOnAllChannels(Product $product): array
    {
        $mappings = MarketplaceProductMapping::where('business_id', $product->business_id)
            ->where('product_id', $product->id)
            ->whereNotNull('external_product_id')
            ->get();

        if ($mappings->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Produk ini belum memiliki listing terhubung di marketplace manapun.',
            ];
        }

        $successCount = 0;
        $failedCount  = 0;
        $channelNames = [];

        foreach ($mappings as $mapping) {
            $res = $this->deactivateProductOnChannel($product, $mapping->channel);
            if (! empty($res['success'])) {
                $successCount++;
                $channelNames[] = $mapping->getChannelLabel();
            } else {
                $failedCount++;
            }
        }

        $channelsList = implode(', ', array_unique($channelNames));
        return [
            'success'       => $successCount > 0,
            'message'       => $successCount > 0
                ? "Produk \"{$product->name}\" berhasil dinonaktifkan dari etalase {$channelsList}."
                : 'Gagal menonaktifkan produk dari saluran marketplace.',
            'success_count' => $successCount,
            'failed_count'  => $failedCount,
        ];
    }

    /**
     * Activate a product back onto a marketplace channel, or all channels if $channel === 'all'.
     */
    public function activateProductOnChannel(Product $product, string $channel): array
    {
        if (strtolower($channel) === 'all') {
            return $this->activateProductOnAllChannels($product);
        }

        $normalizedChannel = match (strtolower($channel)) {
            'shopee'                      => 'shopee',
            'tiktok', 'tiktok-tokopedia' => 'tiktok_shop',
            'tokopedia'                   => 'tokopedia',
            default                       => $channel,
        };

        $mapping = MarketplaceProductMapping::where('business_id', $product->business_id)
            ->where('product_id', $product->id)
            ->where('channel', $normalizedChannel)
            ->first();

        if (! $mapping || empty($mapping->external_product_id)) {
            return [
                'success' => false,
                'message' => 'Produk ini belum memiliki listing terhubung di channel tersebut.',
            ];
        }

        $account = $mapping->account ?? MarketplaceAccount::where('business_id', $product->business_id)
            ->where('channel', $normalizedChannel)
            ->first();

        if (! $account) {
            $mapping->is_active = true;
            $mapping->sync_status = MarketplaceProductMapping::STATUS_SYNCED;
            $mapping->save();

            return [
                'success' => true,
                'message' => 'Akun marketplace belum terhubung, status listing diaktifkan di sistem COOCA.',
            ];
        }

        $adapter = $this->manager->driver($normalizedChannel);

        if (method_exists($adapter, 'activateProduct')) {
            $res = $adapter->activateProduct($account, $mapping->external_product_id);
            if (! empty($res['success'])) {
                $mapping->is_active = true;
                $mapping->sync_status = MarketplaceProductMapping::STATUS_SYNCED;
                $mapping->save();

                // Re-sync price and stock
                $this->syncProductPrice($mapping);
                $this->syncProductStock($mapping);

                MarketplaceSyncLog::log(
                    businessId: $product->business_id,
                    channel: $normalizedChannel,
                    entityType: 'product',
                    action: 'activate_product',
                    status: 'success',
                    entityId: $mapping->id,
                    payload: ['external_product_id' => $mapping->external_product_id],
                    marketplaceAccountId: $account->id
                );

                return [
                    'success' => true,
                    'message' => "Listing \"{$product->name}\" berhasil diaktifkan kembali di etalase {$adapter->getName()}.",
                ];
            }

            return $res;
        }

        $mapping->is_active = true;
        $mapping->sync_stock_auto = true;
        $mapping->save();
        $this->syncProductStock($mapping);

        return [
            'success' => true,
            'message' => "Listing diaktifkan dan stok kembali disinkronkan dari gudang.",
        ];
    }

    /**
     * Activate product back onto all mapped marketplace channels.
     */
    public function activateProductOnAllChannels(Product $product): array
    {
        $mappings = MarketplaceProductMapping::where('business_id', $product->business_id)
            ->where('product_id', $product->id)
            ->whereNotNull('external_product_id')
            ->get();

        if ($mappings->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Produk ini belum memiliki listing terhubung di marketplace manapun.',
            ];
        }

        $successCount = 0;
        $failedCount  = 0;
        $channelNames = [];

        foreach ($mappings as $mapping) {
            $res = $this->activateProductOnChannel($product, $mapping->channel);
            if (! empty($res['success'])) {
                $successCount++;
                $channelNames[] = $mapping->getChannelLabel();
            } else {
                $failedCount++;
            }
        }

        $channelsList = implode(', ', array_unique($channelNames));
        return [
            'success'       => $successCount > 0,
            'message'       => $successCount > 0
                ? "Listing \"{$product->name}\" berhasil diaktifkan kembali di etalase {$channelsList}."
                : 'Gagal mengaktifkan produk di saluran marketplace.',
            'success_count' => $successCount,
            'failed_count'  => $failedCount,
        ];
    }

    /**
     * Pull/Import products from a connected marketplace channel into COOCA.
     *
     * @return array{success: bool, message: string, total_fetched: int, imported_new: int, linked_existing: int}
     */
    public function pullProductsFromChannel(MarketplaceAccount $account, array $params = []): array
    {
        if (! $account->isConnected() || ! ($account->is_active ?? true)) {
            return [
                'success' => false,
                'message' => 'Akun marketplace tidak aktif atau belum terhubung.',
                'total_fetched' => 0,
                'imported_new' => 0,
                'linked_existing' => 0,
            ];
        }

        $businessId = $account->business_id;
        $adapter = $this->manager->driver($account->channel);

        try {
            $result = $adapter->getProducts($account, $params);
            $items = $result['products'] ?? [];
        } catch (\Throwable $e) {
            Log::error("Failed to pull products from {$account->channel}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil data produk dari marketplace: ' . $e->getMessage(),
                'total_fetched' => 0,
                'imported_new' => 0,
                'linked_existing' => 0,
            ];
        }

        if (empty($items)) {
            return [
                'success' => true,
                'message' => "Tidak ada produk aktif ditemukan di toko {$adapter->getName()}.",
                'total_fetched' => 0,
                'imported_new' => 0,
                'linked_existing' => 0,
            ];
        }

        $importedNew = 0;
        $linkedExisting = 0;

        $defaultUnit = Unit::where('code', 'pcs')->first() ?? Unit::first();
        $defaultCategory = ProductCategory::firstOrCreate(
            ['business_id' => $businessId, 'name' => 'Marketplace'],
            ['slug' => 'marketplace']
        );
        $primaryLocation = Location::where('business_id', $businessId)->first();

        foreach ($items as $item) {
            $externalId = (string) $item['id'];
            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '' || $externalId === '') {
                continue;
            }

            $sku = ! empty($item['sku']) ? trim((string) $item['sku']) : null;
            $price = (float) ($item['price'] ?? 0);
            $stock = max(0, (int) ($item['stock'] ?? 0));

            // 1. Check if mapping already exists
            $existingMapping = MarketplaceProductMapping::where('business_id', $businessId)
                ->where('channel', $account->channel)
                ->where('external_product_id', $externalId)
                ->first();

            if ($existingMapping && $existingMapping->product) {
                $existingMapping->channel_price = $price;
                $existingMapping->channel_stock = $stock;
                $existingMapping->sync_status = MarketplaceProductMapping::STATUS_SYNCED;
                $existingMapping->last_price_synced_at = now();
                $existingMapping->last_stock_synced_at = now();
                $existingMapping->save();

                $linkedExisting++;
                continue;
            }

            // 2. Check if product already exists in COOCA by SKU or Name
            $product = Product::withoutGlobalScopes()
                ->where('business_id', $businessId)
                ->where(function ($q) use ($sku, $name): void {
                    if ($sku) {
                        $q->where('code', $sku)->orWhere('name', $name);
                    } else {
                        $q->where('name', $name);
                    }
                })->first();

            if (! $product) {
                $code = $sku ?: ('MP-' . strtoupper(substr($account->channel, 0, 2)) . '-' . substr($externalId, -6));

                $product = Product::create([
                    'business_id'         => $businessId,
                    'type'                => Product::TYPE_GOODS,
                    'category_id'         => $defaultCategory?->id,
                    'output_unit_id'      => $defaultUnit?->id,
                    'code'                => $code,
                    'name'                => $name,
                    'description'         => "Diimpor dari katalog toko {$adapter->getName()}",
                    'base_cost'           => (float) round($price * 0.7),
                    'selling_price'       => $price,
                    'stock'               => $stock,
                    'min_stock'           => 0,
                    'weight'              => 200.0,
                    'is_active'           => true,
                    'show_in_website'     => true,
                    'show_in_pos'         => true,
                    'show_in_sales_order' => true,
                ]);

                if ($primaryLocation && $stock > 0) {
                    InventoryStock::updateOrCreate(
                        [
                            'business_id' => $businessId,
                            'location_id' => $primaryLocation->id,
                            'product_id'  => $product->id,
                        ],
                        [
                            'quantity'          => $stock,
                            'reserved_quantity' => 0,
                            'avg_purchase_cost' => (float) round($price * 0.7),
                            'last_cost'         => (float) round($price * 0.7),
                        ]
                    );
                }

                $importedNew++;
            } else {
                $linkedExisting++;
            }

            // 3. Create or update mapping
            MarketplaceProductMapping::updateOrCreate(
                [
                    'business_id' => $businessId,
                    'product_id'  => $product->id,
                    'channel'     => $account->channel,
                ],
                [
                    'marketplace_account_id' => $account->id,
                    'external_product_id'    => $externalId,
                    'channel_price'          => $price,
                    'channel_stock'          => $stock,
                    'sync_price_auto'        => true,
                    'sync_stock_auto'        => true,
                    'is_active'              => true,
                    'sync_status'            => MarketplaceProductMapping::STATUS_SYNCED,
                    'last_price_synced_at'   => now(),
                    'last_stock_synced_at'   => now(),
                ]
            );
        }

        MarketplaceSyncLog::log(
            businessId: $businessId,
            channel: $account->channel,
            entityType: 'product',
            action: 'pull_products',
            status: 'success',
            payload: [
                'total_fetched'   => count($items),
                'imported_new'    => $importedNew,
                'linked_existing' => $linkedExisting,
            ],
            marketplaceAccountId: $account->id
        );

        return [
            'success'         => true,
            'message'         => "Berhasil menarik produk dari {$adapter->getName()}: {$importedNew} produk baru ditambahkan ke COOCA, {$linkedExisting} produk berhasil ditautkan.",
            'total_fetched'   => count($items),
            'imported_new'    => $importedNew,
            'linked_existing' => $linkedExisting,
        ];
    }
}

