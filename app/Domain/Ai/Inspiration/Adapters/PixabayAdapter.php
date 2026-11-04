<?php

declare(strict_types=1);

namespace App\Domain\Ai\Inspiration\Adapters;

use App\Domain\Ai\Inspiration\Contracts\ExternalProviderAdapterInterface;
use App\Models\AiStudio\InspirationSource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class PixabayAdapter implements ExternalProviderAdapterInterface
{
    private const BASE_URL = 'https://pixabay.com/api/';
    private const CACHE_TTL_SECONDS = 86400; // Wajib minimal 24 jam sesuai ketentuan resmi Pixabay API

    public function __construct(
        private ?string $apiKey = null
    ) {}

    public function getProviderKey(): string
    {
        return 'pixabay';
    }

    public function getDisplayName(): string
    {
        return 'Pixabay Official API';
    }

    public function isConfigured(): bool
    {
        return !empty($this->resolveApiKey());
    }

    public function searchImages(string $query, array $filters = []): array
    {
        $key = $this->resolveApiKey();
        if (empty($key)) {
            return [
                'success' => false,
                'provider' => $this->getProviderKey(),
                'total_results' => 0,
                'page' => (int) ($filters['page'] ?? 1),
                'per_page' => (int) ($filters['per_page'] ?? 15),
                'items' => [],
                'error_message' => 'Pixabay API key belum dikonfigurasi. Silakan pasang di Admin Provider Manager.',
            ];
        }

        $page = (int) ($filters['page'] ?? 1);
        $perPage = min(100, max(3, (int) ($filters['per_page'] ?? 20)));
        $cleanQuery = trim($query) !== '' ? trim($query) : 'business product';
        $imageType = (string) ($filters['image_type'] ?? 'photo');

        // CACHE ENFORCEMENT: 24 jam wajib per API terms Pixabay
        $cacheKey = 'pixabay_api_search_' . md5($cleanQuery . '_' . $page . '_' . $perPage . '_' . $imageType);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($key, $cleanQuery, $page, $perPage, $imageType): array {
            try {
                $response = Http::timeout(10)->get(self::BASE_URL, [
                    'key' => $key,
                    'q' => urlencode($cleanQuery),
                    'page' => $page,
                    'per_page' => $perPage,
                    'image_type' => $imageType,
                    'safesearch' => 'true',
                ]);

                if (!$response->successful()) {
                    Log::warning('[PixabayAdapter] API error: ' . $response->status() . ' - ' . $response->body());
                    return [
                        'success' => false,
                        'provider' => $this->getProviderKey(),
                        'total_results' => 0,
                        'page' => $page,
                        'per_page' => $perPage,
                        'items' => [],
                        'error_message' => 'Pixabay API HTTP ' . $response->status(),
                    ];
                }

                $json = $response->json();
                $normalized = $this->normalizeResponse($json);

                return [
                    'success' => true,
                    'provider' => $this->getProviderKey(),
                    'total_results' => (int) ($json['totalHits'] ?? count($normalized)),
                    'page' => $page,
                    'per_page' => $perPage,
                    'items' => $normalized,
                ];
            } catch (Throwable $e) {
                Log::error('[PixabayAdapter] Exception: ' . $e->getMessage());
                return [
                    'success' => false,
                    'provider' => $this->getProviderKey(),
                    'total_results' => 0,
                    'page' => $page,
                    'per_page' => $perPage,
                    'items' => [],
                    'error_message' => 'Pixabay connection timeout: ' . $e->getMessage(),
                ];
            }
        });
    }

    public function searchVideos(string $query, array $filters = []): array
    {
        $key = $this->resolveApiKey();
        if (empty($key)) {
            return ['success' => false, 'error' => 'API Key not configured'];
        }

        try {
            $response = Http::timeout(10)->get('https://pixabay.com/api/videos/', [
                'key' => $key,
                'q' => urlencode($query),
                'page' => (int) ($filters['page'] ?? 1),
                'per_page' => (int) ($filters['per_page'] ?? 10),
            ]);

            return $response->json() ?? [];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getAssetDetails(string $assetId): ?array
    {
        $key = $this->resolveApiKey();
        if (empty($key)) {
            return null;
        }

        $cacheKey = "pixabay_asset_detail_{$assetId}";
        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($key, $assetId): ?array {
            try {
                $response = Http::timeout(8)->get(self::BASE_URL, [
                    'key' => $key,
                    'id' => $assetId,
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $hits = $json['hits'] ?? [];
                    if (!empty($hits[0])) {
                        $p = $hits[0];
                        $w = (int) ($p['imageWidth'] ?? 1024);
                        $h = (int) ($p['imageHeight'] ?? 1024);

                        return [
                            'external_id' => (string) $p['id'],
                            'title' => (string) ($p['tags'] ?: 'Inspirasi Pixabay #' . $p['id']),
                            'caption' => (string) ($p['tags'] ?? ''),
                            'image_url' => (string) ($p['largeImageURL'] ?? $p['webformatURL'] ?? ''),
                            'thumbnail_url' => (string) ($p['webformatURL'] ?? $p['previewURL'] ?? ''),
                            'width' => $w,
                            'height' => $h,
                            'aspect_ratio' => $this->calculateAspectRatio($w, $h),
                            'author_name' => (string) ($p['user'] ?? 'Pixabay Creator'),
                            'author_url' => 'https://pixabay.com/users/' . ($p['user'] ?? '') . '-' . ($p['user_id'] ?? '') . '/',
                            'source_url' => (string) ($p['pageURL'] ?? 'https://pixabay.com'),
                            'license_name' => 'Pixabay Content License (Free Commercial Use)',
                            'commercial_use_allowed' => true,
                            'require_attribution' => true,
                            'attribution_text' => 'Gambar oleh ' . ($p['user'] ?? 'Kreator') . ' dari Pixabay',
                            'tags' => array_map('trim', explode(',', (string) ($p['tags'] ?? ''))),
                            'raw_metadata' => $p,
                        ];
                    }
                }
            } catch (Throwable) {
                // Return null on failure
            }

            return null;
        });
    }

    public function validateConnection(): array
    {
        $key = $this->resolveApiKey();
        if (empty($key)) {
            return [
                'success' => false,
                'status_code' => 401,
                'message' => 'API Key belum dikonfigurasi.',
                'latency_ms' => 0,
            ];
        }

        $start = microtime(true);
        try {
            $res = Http::timeout(6)->get(self::BASE_URL, [
                'key' => $key,
                'q' => 'test',
                'per_page' => 3,
            ]);

            $latency = (int) round((microtime(true) - $start) * 1000);

            if ($res->successful()) {
                return [
                    'success' => true,
                    'status_code' => $res->status(),
                    'message' => 'Koneksi Pixabay API Resmi Terhubung & Valid.',
                    'latency_ms' => $latency,
                ];
            }

            return [
                'success' => false,
                'status_code' => $res->status(),
                'message' => 'Pixabay menolak kredensial: HTTP ' . $res->status(),
                'latency_ms' => $latency,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'status_code' => 500,
                'message' => 'Koneksi ke Pixabay gagal: ' . $e->getMessage(),
                'latency_ms' => (int) round((microtime(true) - $start) * 1000),
            ];
        }
    }

    public function normalizeResponse(mixed $response): array
    {
        if (!is_array($response) || empty($response['hits'])) {
            return [];
        }

        $items = [];
        foreach ($response['hits'] as $p) {
            $w = (int) ($p['imageWidth'] ?? 1024);
            $h = (int) ($p['imageHeight'] ?? 1024);
            $tagsList = array_filter(array_map('trim', explode(',', (string) ($p['tags'] ?? ''))));

            $items[] = [
                'external_id' => (string) $p['id'],
                'title' => !empty($p['tags']) ? ucfirst($p['tags']) : 'Foto Pixabay #' . $p['id'],
                'caption' => (string) ($p['tags'] ?? ''),
                'image_url' => (string) ($p['largeImageURL'] ?? $p['webformatURL'] ?? ''),
                'thumbnail_url' => (string) ($p['webformatURL'] ?? $p['previewURL'] ?? ''),
                'width' => $w,
                'height' => $h,
                'aspect_ratio' => $this->calculateAspectRatio($w, $h),
                'author_name' => (string) ($p['user'] ?? 'Pixabay Creator'),
                'author_url' => 'https://pixabay.com/users/' . ($p['user'] ?? '') . '-' . ($p['user_id'] ?? '') . '/',
                'source_url' => (string) ($p['pageURL'] ?? 'https://pixabay.com'),
                'source_provider' => 'pixabay',
                'attribution' => [
                    'photographer_name' => (string) ($p['user'] ?? 'Pixabay Creator'),
                    'photographer_url' => 'https://pixabay.com/users/' . ($p['user'] ?? '') . '-' . ($p['user_id'] ?? '') . '/',
                    'source_page_url' => (string) ($p['pageURL'] ?? 'https://pixabay.com'),
                ],
                'license' => [
                    'policy' => 'Pixabay Content License (Cache min 24h applied)',
                    'commercial_use' => true,
                    'attribution_required' => true,
                ],
                'license_name' => 'Pixabay Content License',
                'commercial_use_allowed' => true,
                'require_attribution' => true,
                'attribution_text' => 'Gambar oleh ' . ($p['user'] ?? 'Kreator') . ' dari Pixabay',
                'tags' => array_values($tagsList),
                'raw_metadata' => [
                    'views' => $p['views'] ?? 0,
                    'downloads' => $p['downloads'] ?? 0,
                    'likes' => $p['likes'] ?? 0,
                ],
            ];
        }

        return $items;
    }

    public function getAttribution(string $assetId): array
    {
        $details = $this->getAssetDetails($assetId);

        return [
            'photographer_name' => $details['author_name'] ?? 'Pixabay Contributor',
            'photographer_url' => $details['author_url'] ?? null,
            'source_name' => 'Pixabay',
            'source_url' => $details['source_url'] ?? 'https://pixabay.com',
            'attribution_text' => $details['attribution_text'] ?? 'Gambar bersumber dari Pixabay',
            'license_name' => 'Pixabay Content License',
            'license_url' => 'https://pixabay.com/service/license-summary/',
        ];
    }

    public function getLicenseMetadata(string $assetId): array
    {
        return [
            'license' => 'Pixabay Content License',
            'license_url' => 'https://pixabay.com/service/license-summary/',
            'allow_commercial_use' => true,
            'allow_redistribution' => false,
            'require_attribution' => true,
            'disclaimer' => 'Gambar Pixabay bebas digunakan secara komersial tanpa membayar royalti. Dilarang mendistribusikan ulang atau menjual gambar secara mandiri.',
        ];
    }

    public function recordDownload(string $assetId): bool
    {
        return true;
    }

    public function validateUsagePolicy(): array
    {
        return [
            'allowed' => true,
            'reason' => 'Penggunaan sebagai inspirasi gaya visual & referensi komposisi sesuai dengan Pixabay Terms.',
        ];
    }

    private function resolveApiKey(): ?string
    {
        if (!empty($this->apiKey)) {
            return $this->apiKey;
        }

        try {
            $source = InspirationSource::where('code', 'pixabay')->first();
            $cred = $source?->credentials()->where('status', 'active')->first();
            if ($cred && !empty($cred->credentials_encrypted)) {
                $raw = Crypt::decryptString($cred->credentials_encrypted);
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && !empty($decoded['api_key'])) {
                    return (string) $decoded['api_key'];
                }
                if (!empty($raw) && !str_starts_with($raw, '{')) {
                    return $raw;
                }
            }
        } catch (Throwable) {
            // Fallback
        }

        return config('services.pixabay.api_key') ?: env('PIXABAY_API_KEY');
    }

    private function calculateAspectRatio(int $w, int $h): string
    {
        if ($w <= 0 || $h <= 0) return '1:1';
        $ratio = $w / $h;

        if ($ratio > 1.4) return '16:9';
        if ($ratio > 1.1) return '4:3';
        if ($ratio >= 0.9 && $ratio <= 1.1) return '1:1';
        if ($ratio >= 0.7) return '4:5';
        return '9:16';
    }
}
