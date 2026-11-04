<?php

declare(strict_types=1);

namespace App\Domain\Ai\Inspiration\Services;

use App\Domain\Ai\Inspiration\Adapters\PexelsAdapter;
use App\Domain\Ai\Inspiration\Adapters\PixabayAdapter;
use App\Domain\Ai\Inspiration\Adapters\UnsplashAdapter;
use App\Domain\Ai\Inspiration\Adapters\PinterestAdapter;
use App\Domain\Ai\Inspiration\Contracts\ExternalProviderAdapterInterface;
use App\Models\AiStudio\InspirationAsset;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationUsageLog;
use App\Models\Business;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ExternalInspirationSearchService
{
    /**
     * @var array<string, ExternalProviderAdapterInterface>
     */
    private array $providers;

    public function __construct(
        PexelsAdapter $pexels,
        PixabayAdapter $pixabay,
        UnsplashAdapter $unsplash,
        PinterestAdapter $pinterest
    ) {
        $this->providers = [
            'pexels' => $pexels,
            'pixabay' => $pixabay,
            'unsplash' => $unsplash,
            'pinterest' => $pinterest,
        ];
    }

    /**
     * Dapatkan daftar provider terdaftar beserta status konfigurasinya.
     *
     * @return array<string, array{key: string, name: string, configured: bool}>
     */
    public function getRegisteredProviders(): array
    {
        $list = [];
        foreach ($this->providers as $key => $adapter) {
            $list[$key] = [
                'key' => $key,
                'name' => $adapter->getDisplayName(),
                'configured' => $adapter->isConfigured(),
            ];
        }

        return $list;
    }

    /**
     * Eksekusi pencarian terpadu lintas Katalog Kurasi COOCA, Provider Eksternal, dan Personal Library.
     *
     * @param array{
     *     source?: string,
     *     category?: ?string,
     *     industry?: ?string,
     *     aspect_ratio?: ?string,
     *     commercial_only?: bool,
     *     page?: int,
     *     per_page?: int
     * } $filters
     *
     * @return array{
     *     success: bool,
     *     query: string,
     *     source_filter: string,
     *     total: int,
     *     items: array<int, array<string, mixed>>,
     *     provider_statuses: array<string, array{status: string, message?: string}>
     * }
     */
    public function search(
        string $query,
        array $filters = [],
        ?Business $business = null,
        ?User $user = null
    ): array {
        $sourceFilter = (string) ($filters['source'] ?? 'all');
        $commercialOnly = (bool) ($filters['commercial_only'] ?? false);
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = min(60, max(6, (int) ($filters['per_page'] ?? 24)));

        $mergedItems = [];
        $providerStatuses = [];

        // 1. CARI DARI KATALOG KURASI COOCA (Jika source = 'all' atau 'curated')
        if (in_array($sourceFilter, ['all', 'curated'], true)) {
            try {
                $curatedQuery = InspirationItem::query()
                    ->with(['source', 'category', 'visualAnalysis', 'trendScore'])
                    ->where('status', 'active');

                if (trim($query) !== '') {
                    $q = trim($query);
                    $curatedQuery->where(function ($sub) use ($q): void {
                        $sub->where('title', 'like', "%{$q}%")
                            ->orWhere('caption', 'like', "%{$q}%")
                            ->orWhereJsonContains('tags', $q);
                    });
                }

                if (!empty($filters['category']) && $filters['category'] !== 'all') {
                    $cat = $filters['category'];
                    $curatedQuery->whereHas('category', function ($sub) use ($cat): void {
                        $sub->where('slug', $cat)->orWhere('id', $cat);
                    });
                }

                if (!empty($filters['industry']) && $filters['industry'] !== 'all') {
                    $ind = $filters['industry'];
                    $curatedQuery->where('industry_target', 'like', "%{$ind}%");
                }

                $curatedResults = $curatedQuery->orderByDesc('view_count')->limit(48)->get();

                foreach ($curatedResults as $item) {
                    $mergedItems[] = [
                        'id' => $item->id,
                        'external_id' => $item->external_id,
                        'source_type' => 'curated',
                        'source_code' => $item->source?->code ?? 'cooca',
                        'source_name' => $item->source?->name ?? 'Katalog COOCA',
                        'title' => $item->title,
                        'caption' => $item->caption,
                        'display_url' => $item->display_image_url,
                        'preview_url' => $item->display_image_url,
                        'image_url' => $item->display_image_url,
                        'thumbnail_url' => $item->thumbnail_url ?: $item->display_image_url,
                        'aspect_ratio' => $item->visualAnalysis?->aspect_ratio ?? '1:1',
                        'author_name' => 'COOCA Studio',
                        'author_url' => null,
                        'source_url' => $item->canonical_url ?? route('ai-studio.inspiration.show', $item->id),
                        'license_name' => 'COOCA Commercial License',
                        'commercial_use_allowed' => true,
                        'require_attribution' => false,
                        'attribution_text' => 'Koleksi Terkurasi COOCA Studio',
                        'tags' => $item->tags ?? [],
                        'prompt_hint' => $item->visualAnalysis?->extracted_style_prompt ?? $item->caption,
                    ];
                }

                $providerStatuses['curated'] = ['status' => 'success', 'count' => count($curatedResults)];
            } catch (Throwable $e) {
                Log::error('[ExternalInspirationSearchService] Curated search failed: ' . $e->getMessage());
                $providerStatuses['curated'] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        // 2. CARI DARI PERSONAL LIBRARY (Jika bisnis aktif dan filter = 'all' atau 'personal')
        if ($business !== null && in_array($sourceFilter, ['all', 'personal'], true)) {
            try {
                $assetQuery = InspirationAsset::query()
                    ->where('business_id', $business->id)
                    ->where('moderation_status', 'approved');

                if (trim($query) !== '') {
                    $q = trim($query);
                    $assetQuery->where(function ($sub) use ($q): void {
                        $sub->where('title', 'like', "%{$q}%")
                            ->orWhere('description', 'like', "%{$q}%");
                    });
                }

                $personalAssets = $assetQuery->latest()->limit(24)->get();
                foreach ($personalAssets as $pAsset) {
                    $pImg = $pAsset->resolved_image_url ?: $pAsset->image_url;
                    $mergedItems[] = [
                        'id' => $pAsset->id,
                        'external_id' => $pAsset->external_asset_id ?? $pAsset->id,
                        'source_type' => 'personal',
                        'source_code' => 'personal',
                        'source_name' => 'Koleksi Bisnis Anda',
                        'title' => $pAsset->title,
                        'caption' => $pAsset->description,
                        'display_url' => $pImg,
                        'preview_url' => $pImg,
                        'image_url' => $pImg,
                        'thumbnail_url' => $pAsset->thumbnail_url ?: $pImg,
                        'aspect_ratio' => $pAsset->aspect_ratio,
                        'author_name' => $business->name,
                        'author_url' => null,
                        'source_url' => $pAsset->source_url ?? '#',
                        'license_name' => $pAsset->license_name,
                        'commercial_use_allowed' => $pAsset->commercial_usage_allowed,
                        'require_attribution' => false,
                        'attribution_text' => 'Aset Pribadi Bisnis ' . $business->name,
                        'tags' => [],
                        'prompt_hint' => $pAsset->title,
                    ];
                }

                $providerStatuses['personal'] = ['status' => 'success', 'count' => count($personalAssets)];
            } catch (Throwable $e) {
                Log::error('[ExternalInspirationSearchService] Personal library search failed: ' . $e->getMessage());
                $providerStatuses['personal'] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        // 3. CARI DARI PROVIDER EKSTERNAL RESMI (Pexels, Pixabay, Unsplash, Pinterest)
        foreach ($this->providers as $code => $adapter) {
            if ($sourceFilter !== 'all' && $sourceFilter !== $code) {
                continue;
            }

            if (!$adapter->isConfigured()) {
                $providerStatuses[$code] = [
                    'status' => 'unconfigured',
                    'message' => "Kredensial {$adapter->getDisplayName()} belum dipasang.",
                ];
                continue;
            }

            try {
                $res = $adapter->searchImages($query, [
                    'page' => $page,
                    'per_page' => 15,
                ]);

                if ($res['success'] ?? false) {
                    $providerStatuses[$code] = ['status' => 'success', 'count' => count($res['items'])];
                    foreach ($res['items'] as $item) {
                        if ($commercialOnly && !($item['commercial_use_allowed'] ?? false)) {
                            continue;
                        }

                        $extImg = $item['image_url'] ?? $item['display_url'] ?? $item['preview_url'] ?? '';

                        $mergedItems[] = array_merge($item, [
                            'id' => $code . '_' . $item['external_id'],
                            'source_type' => 'external',
                            'source_code' => $code,
                            'source_name' => $adapter->getDisplayName(),
                            'display_url' => $extImg,
                            'preview_url' => $extImg,
                            'image_url' => $extImg,
                            'thumbnail_url' => $item['thumbnail_url'] ?? $extImg,
                            'prompt_hint' => $item['caption'] ?: $item['title'],
                        ]);
                    }
                } else {
                    $providerStatuses[$code] = [
                        'status' => 'failed',
                        'message' => $res['error_message'] ?? 'Provider gagal merespon.',
                    ];
                }
            } catch (Throwable $e) {
                Log::warning("[ExternalInspirationSearchService] Provider {$code} exception: " . $e->getMessage());
                $providerStatuses[$code] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        // 4. FILTER RASIO ASPEK (JIKA DIPILIH)
        if (!empty($filters['aspect_ratio']) && $filters['aspect_ratio'] !== 'all') {
            $targetRatio = (string) $filters['aspect_ratio'];
            $mergedItems = array_values(array_filter($mergedItems, function ($it) use ($targetRatio): bool {
                return ($it['aspect_ratio'] ?? '1:1') === $targetRatio;
            }));
        }

        // 5. TELEMETRI PENGGUNAAN
        if ($business !== null) {
            try {
                InspirationUsageLog::record(
                    businessId: $business->id,
                    userId: $user?->id,
                    action: InspirationUsageLog::ACTION_SEARCH,
                    source: $sourceFilter,
                    assetReference: $query,
                    metadata: [
                        'total_found' => count($mergedItems),
                        'filters' => $filters,
                    ]
                );
            } catch (Throwable) {
                // Non-blocking
            }
        }

        $errors = [];
        foreach ($providerStatuses as $pCode => $pStatus) {
            if (in_array($pStatus['status'] ?? '', ['error', 'failed'], true)) {
                $errors[$pCode] = $pStatus['message'] ?? 'Error';
            }
        }

        return [
            'success' => true,
            'query' => $query,
            'source_filter' => $sourceFilter,
            'total' => count($mergedItems),
            'items' => array_slice($mergedItems, ($page - 1) * $perPage, $perPage),
            'provider_statuses' => $providerStatuses,
            'errors' => $errors,
        ];
    }
}
