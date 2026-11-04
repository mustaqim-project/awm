<?php

declare(strict_types=1);

namespace App\Domain\Ai\Inspiration\Adapters;

use App\Domain\Ai\Inspiration\Contracts\ExternalProviderAdapterInterface;
use App\Domain\Ai\Inspiration\Contracts\InspirationAdapterInterface;
use App\Models\AiStudio\InspirationSource;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class PinterestAdapter implements ExternalProviderAdapterInterface, InspirationAdapterInterface
{
    public const SOURCE_CODE = 'pinterest';
    private const BASE_URL = 'https://api.pinterest.com/v5';

    public function __construct(
        private ?string $accessToken = null
    ) {}

    public function getProviderKey(): string
    {
        return self::SOURCE_CODE;
    }

    public function getSourceCode(): string
    {
        return self::SOURCE_CODE;
    }

    public function getDisplayName(): string
    {
        return 'Pinterest Official API v5';
    }

    public function isConfigured(): bool
    {
        return !empty($this->resolveAccessToken());
    }

    public function search(string $query, array $filters = []): array
    {
        $token = $this->resolveAccessToken();
        if (empty($token)) {
            $templates = (new \Database\Seeders\PinterestSaasPromotionInspirationSeeder())->getSaasPromotionItems();
            if (!empty($query)) {
                $q = strtolower($query);
                $templates = array_filter($templates, function ($item) use ($q) {
                    return str_contains(strtolower($item['title']), $q)
                        || str_contains(strtolower($item['caption'] ?? ''), $q)
                        || in_array($q, array_map('strtolower', $item['tags'] ?? []));
                });
            }
            return array_values($templates);
        }

        $res = $this->searchImages($query, $filters);
        return $res['items'] ?? [];
    }

    public function getItem(string $externalId): ?array
    {
        return $this->getAssetDetails($externalId);
    }

    public function searchImages(string $query, array $filters = []): array
    {
        $token = $this->resolveAccessToken();
        if (empty($token)) {
            return [
                'success' => false,
                'provider' => $this->getProviderKey(),
                'total_results' => 0,
                'page' => (int) ($filters['page'] ?? 1),
                'per_page' => (int) ($filters['per_page'] ?? 15),
                'items' => [],
                'error_message' => 'Pinterest API resmi v5 belum dikonfigurasi. Sesuai kebijakan kepatuhan COOCA, dilarang melakukan scraping.',
            ];
        }

        $pageSize = min(100, max(1, (int) ($filters['per_page'] ?? 25)));

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->get(self::BASE_URL . '/pins', [
                    'page_size' => $pageSize,
                ]);

            if (!$response->successful()) {
                Log::warning('[PinterestAdapter] API v5 error: ' . $response->status() . ' - ' . $response->body());
                return [
                    'success' => false,
                    'provider' => $this->getProviderKey(),
                    'total_results' => 0,
                    'page' => 1,
                    'per_page' => $pageSize,
                    'items' => [],
                    'error_message' => 'Pinterest API HTTP ' . $response->status(),
                ];
            }

            $json = $response->json();
            $normalized = $this->normalizeResponse($json);

            return [
                'success' => true,
                'provider' => $this->getProviderKey(),
                'total_results' => count($normalized),
                'page' => 1,
                'per_page' => $pageSize,
                'items' => $normalized,
            ];
        } catch (Throwable $e) {
            Log::error('[PinterestAdapter] Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'provider' => $this->getProviderKey(),
                'total_results' => 0,
                'page' => 1,
                'per_page' => $pageSize,
                'items' => [],
                'error_message' => 'Pinterest connection timeout: ' . $e->getMessage(),
            ];
        }
    }

    public function searchVideos(string $query, array $filters = []): array
    {
        return ['success' => false, 'error' => 'Pinterest Video Search not enabled without extended video scope.'];
    }

    public function getAssetDetails(string $assetId): ?array
    {
        $token = $this->resolveAccessToken();
        if (empty($token)) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->timeout(8)
                ->get(self::BASE_URL . "/pins/{$assetId}");

            if ($response->successful()) {
                $p = $response->json();
                $imgUrl = (string) ($p['media']['images']['originals']['url'] ?? $p['media']['images']['600x']['url'] ?? '');
                $w = (int) ($p['media']['images']['originals']['width'] ?? 1024);
                $h = (int) ($p['media']['images']['originals']['height'] ?? 1024);

                return [
                    'external_id' => (string) $p['id'],
                    'title' => (string) ($p['title'] ?: 'Pinterest Pin #' . $p['id']),
                    'caption' => (string) ($p['description'] ?? ''),
                    'image_url' => $imgUrl,
                    'thumbnail_url' => (string) ($p['media']['images']['150x150']['url'] ?? $imgUrl),
                    'width' => $w,
                    'height' => $h,
                    'aspect_ratio' => $this->calculateAspectRatio($w, $h),
                    'author_name' => (string) ($p['board_owner']['username'] ?? 'Pinterest Creator'),
                    'author_url' => 'https://www.pinterest.com/' . ($p['board_owner']['username'] ?? ''),
                    'source_url' => (string) ($p['link'] ?? 'https://www.pinterest.com/pin/' . $p['id']),
                    'license_name' => 'Pinterest Rights (Source Reference Only)',
                    'commercial_use_allowed' => false, // Reference only
                    'require_attribution' => true,
                    'attribution_text' => 'Pin by ' . ($p['board_owner']['username'] ?? 'User') . ' on Pinterest',
                    'tags' => [],
                    'raw_metadata' => $p,
                ];
            }
        } catch (Throwable) {
            // Ignore
        }

        return null;
    }

    public function validateConnection(): array
    {
        $token = $this->resolveAccessToken();
        if (empty($token)) {
            return [
                'success' => false,
                'status_code' => 401,
                'message' => 'Pinterest Access Token belum dikonfigurasi (Belum Terhubung).',
                'latency_ms' => 0,
            ];
        }

        $start = microtime(true);
        try {
            $res = Http::withToken($token)->timeout(6)->get(self::BASE_URL . '/user_account');
            $latency = (int) round((microtime(true) - $start) * 1000);

            if ($res->successful()) {
                return [
                    'success' => true,
                    'status_code' => $res->status(),
                    'message' => 'Koneksi Pinterest API v5 Resmi Terhubung & Valid.',
                    'latency_ms' => $latency,
                ];
            }

            return [
                'success' => false,
                'status_code' => $res->status(),
                'message' => 'Pinterest API v5 menolak token: HTTP ' . $res->status(),
                'latency_ms' => $latency,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'status_code' => 500,
                'message' => 'Koneksi ke Pinterest gagal: ' . $e->getMessage(),
                'latency_ms' => (int) round((microtime(true) - $start) * 1000),
            ];
        }
    }

    public function normalizeResponse(mixed $response): array
    {
        if (!is_array($response) || empty($response['items'])) {
            return [];
        }

        $items = [];
        foreach ($response['items'] as $p) {
            $imgUrl = (string) ($p['media']['images']['originals']['url'] ?? $p['media']['images']['600x']['url'] ?? '');
            $thumbUrl = (string) ($p['media']['images']['150x150']['url'] ?? $imgUrl);
            $w = (int) ($p['media']['images']['originals']['width'] ?? 1024);
            $h = (int) ($p['media']['images']['originals']['height'] ?? 1024);

            $items[] = [
                'external_id' => (string) $p['id'],
                'title' => !empty($p['title']) ? (string) $p['title'] : 'Pin Inspirasi #' . $p['id'],
                'caption' => (string) ($p['description'] ?? ''),
                'image_url' => $imgUrl,
                'thumbnail_url' => $thumbUrl,
                'width' => $w,
                'height' => $h,
                'aspect_ratio' => $this->calculateAspectRatio($w, $h),
                'author_name' => (string) ($p['board_owner']['username'] ?? 'Pinterest User'),
                'author_url' => 'https://www.pinterest.com/' . ($p['board_owner']['username'] ?? ''),
                'source_url' => (string) ($p['link'] ?? 'https://www.pinterest.com/pin/' . $p['id']),
                'license_name' => 'Pinterest Reference',
                'commercial_use_allowed' => false,
                'require_attribution' => true,
                'attribution_text' => 'Pin oleh ' . ($p['board_owner']['username'] ?? 'User') . ' di Pinterest',
                'tags' => [],
                'raw_metadata' => [
                    'board_id' => $p['board_id'] ?? null,
                ],
            ];
        }

        return $items;
    }

    public function getAttribution(string $assetId): array
    {
        $details = $this->getAssetDetails($assetId);

        return [
            'photographer_name' => $details['author_name'] ?? 'Pinterest Creator',
            'photographer_url' => $details['author_url'] ?? null,
            'source_name' => 'Pinterest',
            'source_url' => $details['source_url'] ?? 'https://www.pinterest.com',
            'attribution_text' => $details['attribution_text'] ?? 'Pin referensi bersumber dari Pinterest',
            'license_name' => 'Pinterest Copyright (Visual Reference Only)',
            'license_url' => 'https://policy.pinterest.com/terms-of-service',
        ];
    }

    public function getLicenseMetadata(string $assetId): array
    {
        return [
            'license' => 'Pinterest Standard Copyright',
            'license_url' => 'https://policy.pinterest.com/terms-of-service',
            'allow_commercial_use' => false, // Hanya acuan ide/komposisi
            'allow_redistribution' => false,
            'require_attribution' => true,
            'disclaimer' => 'Pin Pinterest adalah hak cipta pemilik asli dan hanya boleh digunakan sebagai inspirasi referensi visual, bukan untuk didistribusikan ulang atau dipublikasikan langsung.',
        ];
    }

    public function recordDownload(string $assetId): bool
    {
        return true;
    }

    public function validateUsagePolicy(): array
    {
        return [
            'allowed' => false,
            'reason' => 'Gambar Pinterest hanya diizinkan sebagai tautan ide dan referensi visual, dilarang diunduh sebagai aset komersial mentah.',
        ];
    }

    private function resolveAccessToken(): ?string
    {
        if (!empty($this->accessToken)) {
            return $this->accessToken;
        }

        try {
            $source = InspirationSource::where('code', 'pinterest')->first();
            $cred = $source?->credentials()->where('status', 'active')->first();
            if ($cred && !empty($cred->credentials_encrypted)) {
                $raw = Crypt::decryptString($cred->credentials_encrypted);
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && !empty($decoded['access_token'])) {
                    return (string) $decoded['access_token'];
                }
                if (!empty($raw) && !str_starts_with($raw, '{')) {
                    return $raw;
                }
            }
        } catch (Throwable) {
            // Fallback
        }

        return config('services.pinterest.access_token') ?: env('PINTEREST_ACCESS_TOKEN');
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
