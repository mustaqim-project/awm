<?php

declare(strict_types=1);

namespace App\Domain\Ai\Inspiration\Adapters;

use App\Domain\Ai\Inspiration\Contracts\ExternalProviderAdapterInterface;
use App\Models\AiStudio\InspirationSource;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class UnsplashAdapter implements ExternalProviderAdapterInterface
{
    private const BASE_URL = 'https://api.unsplash.com';
    private const APP_NAME = 'cooca_ai_studio';

    public function __construct(
        private ?string $accessKey = null
    ) {}

    public function getProviderKey(): string
    {
        return 'unsplash';
    }

    public function getDisplayName(): string
    {
        return 'Unsplash Official API';
    }

    public function isConfigured(): bool
    {
        return !empty($this->resolveAccessKey());
    }

    public function searchImages(string $query, array $filters = []): array
    {
        $key = $this->resolveAccessKey();
        if (empty($key)) {
            return [
                'success' => false,
                'provider' => $this->getProviderKey(),
                'total_results' => 0,
                'page' => (int) ($filters['page'] ?? 1),
                'per_page' => (int) ($filters['per_page'] ?? 15),
                'items' => [],
                'error_message' => 'Unsplash Access Key belum dikonfigurasi. Tinjau API Terms & pasang di Admin Provider Manager.',
            ];
        }

        $page = (int) ($filters['page'] ?? 1);
        $perPage = min(30, max(1, (int) ($filters['per_page'] ?? 15)));
        $orientation = (string) ($filters['orientation'] ?? '');

        $queryParams = [
            'query' => trim($query) !== '' ? trim($query) : 'branding business catalog',
            'page' => $page,
            'per_page' => $perPage,
        ];

        if (in_array($orientation, ['landscape', 'portrait', 'squarish'], true)) {
            $queryParams['orientation'] = $orientation;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Client-ID ' . $key,
                'Accept-Version' => 'v1',
            ])->timeout(10)->get(self::BASE_URL . '/search/photos', $queryParams);

            if (!$response->successful()) {
                Log::warning('[UnsplashAdapter] API error: ' . $response->status() . ' - ' . $response->body());
                return [
                    'success' => false,
                    'provider' => $this->getProviderKey(),
                    'total_results' => 0,
                    'page' => $page,
                    'per_page' => $perPage,
                    'items' => [],
                    'error_message' => 'Unsplash API HTTP ' . $response->status(),
                ];
            }

            $json = $response->json();
            $normalized = $this->normalizeResponse($json);

            return [
                'success' => true,
                'provider' => $this->getProviderKey(),
                'total_results' => (int) ($json['total'] ?? count($normalized)),
                'page' => $page,
                'per_page' => $perPage,
                'items' => $normalized,
            ];
        } catch (Throwable $e) {
            Log::error('[UnsplashAdapter] Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'provider' => $this->getProviderKey(),
                'total_results' => 0,
                'page' => $page,
                'per_page' => $perPage,
                'items' => [],
                'error_message' => 'Unsplash connection timeout: ' . $e->getMessage(),
            ];
        }
    }

    public function searchVideos(string $query, array $filters = []): array
    {
        return ['success' => false, 'error' => 'Unsplash API does not support video search.'];
    }

    public function getAssetDetails(string $assetId): ?array
    {
        $key = $this->resolveAccessKey();
        if (empty($key)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Client-ID ' . $key,
                'Accept-Version' => 'v1',
            ])->timeout(8)->get(self::BASE_URL . "/photos/{$assetId}");

            if ($response->successful()) {
                $p = $response->json();
                $w = (int) ($p['width'] ?? 1024);
                $h = (int) ($p['height'] ?? 1024);
                $photographer = $p['user']['name'] ?? 'Unsplash Contributor';
                $photographerProfile = ($p['user']['links']['html'] ?? 'https://unsplash.com') . '?utm_source=' . self::APP_NAME . '&utm_medium=referral';

                return [
                    'external_id' => (string) $p['id'],
                    'title' => (string) ($p['description'] ?: $p['alt_description'] ?: 'Foto Inspirasi Unsplash'),
                    'caption' => (string) ($p['alt_description'] ?? ''),
                    'image_url' => (string) ($p['urls']['regular'] ?? $p['urls']['full'] ?? ''),
                    'thumbnail_url' => (string) ($p['urls']['small'] ?? $p['urls']['thumb'] ?? ''),
                    'width' => $w,
                    'height' => $h,
                    'aspect_ratio' => $this->calculateAspectRatio($w, $h),
                    'author_name' => $photographer,
                    'author_url' => $photographerProfile,
                    'source_url' => ($p['links']['html'] ?? 'https://unsplash.com') . '?utm_source=' . self::APP_NAME . '&utm_medium=referral',
                    'license_name' => 'Unsplash License',
                    'commercial_use_allowed' => true,
                    'require_attribution' => true,
                    'attribution_text' => "Photo by {$photographer} on Unsplash",
                    'tags' => array_column($p['tags'] ?? [], 'title'),
                    'raw_metadata' => [
                        'download_location' => $p['links']['download_location'] ?? null,
                    ],
                ];
            }
        } catch (Throwable) {
            // Ignore
        }

        return null;
    }

    public function validateConnection(): array
    {
        $key = $this->resolveAccessKey();
        if (empty($key)) {
            return [
                'success' => false,
                'status_code' => 401,
                'message' => 'Unsplash Access Key belum dikonfigurasi.',
                'latency_ms' => 0,
            ];
        }

        $start = microtime(true);
        try {
            $res = Http::withHeaders([
                'Authorization' => 'Client-ID ' . $key,
                'Accept-Version' => 'v1',
            ])->timeout(6)->get(self::BASE_URL . '/photos/random');

            $latency = (int) round((microtime(true) - $start) * 1000);

            if ($res->successful()) {
                return [
                    'success' => true,
                    'status_code' => $res->status(),
                    'message' => 'Koneksi Unsplash API Resmi Terhubung & Valid.',
                    'latency_ms' => $latency,
                ];
            }

            return [
                'success' => false,
                'status_code' => $res->status(),
                'message' => 'Unsplash menolak kredensial: HTTP ' . $res->status(),
                'latency_ms' => $latency,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'status_code' => 500,
                'message' => 'Koneksi ke Unsplash gagal: ' . $e->getMessage(),
                'latency_ms' => (int) round((microtime(true) - $start) * 1000),
            ];
        }
    }

    public function normalizeResponse(mixed $response): array
    {
        if (!is_array($response) || empty($response['results'])) {
            return [];
        }

        $items = [];
        foreach ($response['results'] as $p) {
            $w = (int) ($p['width'] ?? 1024);
            $h = (int) ($p['height'] ?? 1024);
            $photographer = $p['user']['name'] ?? 'Unsplash Contributor';
            $photographerProfile = ($p['user']['links']['html'] ?? 'https://unsplash.com') . '?utm_source=' . self::APP_NAME . '&utm_medium=referral';

            $items[] = [
                'external_id' => (string) $p['id'],
                'title' => !empty($p['alt_description']) ? ucfirst($p['alt_description']) : 'Foto Unsplash #' . $p['id'],
                'caption' => (string) ($p['description'] ?? $p['alt_description'] ?? ''),
                'image_url' => (string) ($p['urls']['regular'] ?? $p['urls']['full'] ?? ''),
                'thumbnail_url' => (string) ($p['urls']['small'] ?? $p['urls']['thumb'] ?? ''),
                'width' => $w,
                'height' => $h,
                'aspect_ratio' => $this->calculateAspectRatio($w, $h),
                'author_name' => $photographer,
                'author_url' => $photographerProfile,
                'source_url' => ($p['links']['html'] ?? 'https://unsplash.com') . '?utm_source=' . self::APP_NAME . '&utm_medium=referral',
                'source_provider' => 'unsplash',
                'attribution' => [
                    'photographer_name' => $photographer,
                    'photographer_url' => $photographerProfile,
                    'source_page_url' => ($p['links']['html'] ?? 'https://unsplash.com') . '?utm_source=' . self::APP_NAME . '&utm_medium=referral',
                ],
                'license' => [
                    'policy' => 'Unsplash License (Free Commercial Use)',
                    'commercial_use' => true,
                    'attribution_required' => true,
                ],
                'license_name' => 'Unsplash License',
                'commercial_use_allowed' => true,
                'require_attribution' => true,
                'attribution_text' => "Photo by {$photographer} on Unsplash",
                'tags' => array_column($p['tags'] ?? [], 'title'),
                'raw_metadata' => [
                    'download_location' => $p['links']['download_location'] ?? null,
                    'likes' => $p['likes'] ?? 0,
                    'color' => $p['color'] ?? null,
                ],
            ];
        }

        return $items;
    }

    public function getAttribution(string $assetId): array
    {
        $details = $this->getAssetDetails($assetId);

        return [
            'photographer_name' => $details['author_name'] ?? 'Unsplash Creator',
            'photographer_url' => $details['author_url'] ?? null,
            'source_name' => 'Unsplash',
            'source_url' => $details['source_url'] ?? 'https://unsplash.com',
            'attribution_text' => $details['attribution_text'] ?? 'Photo from Unsplash',
            'license_name' => 'Unsplash License',
            'license_url' => 'https://unsplash.com/license',
        ];
    }

    public function getLicenseMetadata(string $assetId): array
    {
        return [
            'license' => 'Unsplash License',
            'license_url' => 'https://unsplash.com/license',
            'allow_commercial_use' => true,
            'allow_redistribution' => false,
            'require_attribution' => true,
            'disclaimer' => 'Semua foto di Unsplash dapat diunduh dan digunakan gratis untuk keperluan komersial dan non-komersial. Wajib mencantumkan atribusi fotografer dan tautan Unsplash.',
        ];
    }

    /**
     * Wajib mengeksekusi endpoint download tracking Unsplash saat gambar diunduh atau diproses.
     */
    public function recordDownload(string $assetId): bool
    {
        $key = $this->resolveAccessKey();
        if (empty($key)) {
            return false;
        }

        try {
            $url = str_starts_with($assetId, 'http') ? $assetId : self::BASE_URL . "/photos/{$assetId}/download";
            $res = Http::withHeaders([
                'Authorization' => 'Client-ID ' . $key,
            ])->timeout(5)->get($url);

            return $res->successful();
        } catch (Throwable) {
            // Non-blocking
        }

        return false;
    }

    public function validateUsagePolicy(): array
    {
        return [
            'allowed' => true,
            'reason' => 'Penggunaan sebagai inspirasi gaya visual & referensi komposisi sesuai dengan Unsplash API Guidelines (Hotlink Only).',
        ];
    }

    private function resolveAccessKey(): ?string
    {
        if (!empty($this->accessKey)) {
            return $this->accessKey;
        }

        try {
            $source = InspirationSource::where('code', 'unsplash')->first();
            $cred = $source?->credentials()->where('status', 'active')->first();
            if ($cred && !empty($cred->credentials_encrypted)) {
                $raw = Crypt::decryptString($cred->credentials_encrypted);
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && !empty($decoded['access_key'])) {
                    return (string) $decoded['access_key'];
                }
                if (!empty($raw) && !str_starts_with($raw, '{')) {
                    return $raw;
                }
            }
        } catch (Throwable) {
            // Fallback
        }

        return config('services.unsplash.access_key') ?: env('UNSPLASH_ACCESS_KEY');
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
