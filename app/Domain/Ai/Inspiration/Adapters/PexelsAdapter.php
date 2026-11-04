<?php

declare(strict_types=1);

namespace App\Domain\Ai\Inspiration\Adapters;

use App\Domain\Ai\Inspiration\Contracts\ExternalProviderAdapterInterface;
use App\Models\AiStudio\InspirationSource;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class PexelsAdapter implements ExternalProviderAdapterInterface
{
    private const BASE_URL = 'https://api.pexels.com/v1';

    public function __construct(
        private ?string $apiKey = null
    ) {}

    public function getProviderKey(): string
    {
        return 'pexels';
    }

    public function getDisplayName(): string
    {
        return 'Pexels Official API';
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
                'error_message' => 'Pexels API key belum dikonfigurasi. Silakan pasang di Admin Provider Manager.',
            ];
        }

        $page = (int) ($filters['page'] ?? 1);
        $perPage = min(80, max(1, (int) ($filters['per_page'] ?? 15)));
        $orientation = (string) ($filters['orientation'] ?? '');

        $queryParams = [
            'query' => trim($query) !== '' ? trim($query) : 'business product branding',
            'page' => $page,
            'per_page' => $perPage,
        ];

        if (in_array($orientation, ['landscape', 'portrait', 'square'], true)) {
            $queryParams['orientation'] = $orientation;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $key,
            ])->timeout(10)->get(self::BASE_URL . '/search', $queryParams);

            if (!$response->successful()) {
                Log::warning('[PexelsAdapter] API error: ' . $response->status() . ' - ' . $response->body());
                return [
                    'success' => false,
                    'provider' => $this->getProviderKey(),
                    'total_results' => 0,
                    'page' => $page,
                    'per_page' => $perPage,
                    'items' => [],
                    'error_message' => 'Pexels API HTTP ' . $response->status() . ': ' . ($response->json('error') ?? 'Gagal memuat gambar.'),
                ];
            }

            $json = $response->json();
            $normalized = $this->normalizeResponse($json);

            return [
                'success' => true,
                'provider' => $this->getProviderKey(),
                'total_results' => (int) ($json['total_results'] ?? count($normalized)),
                'page' => (int) ($json['page'] ?? $page),
                'per_page' => (int) ($json['per_page'] ?? $perPage),
                'items' => $normalized,
            ];
        } catch (Throwable $e) {
            Log::error('[PexelsAdapter] Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'provider' => $this->getProviderKey(),
                'total_results' => 0,
                'page' => $page,
                'per_page' => $perPage,
                'items' => [],
                'error_message' => 'Pexels connection timeout: ' . $e->getMessage(),
            ];
        }
    }

    public function searchVideos(string $query, array $filters = []): array
    {
        $key = $this->resolveApiKey();
        if (empty($key)) {
            return ['success' => false, 'error' => 'API Key not configured'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $key,
            ])->timeout(10)->get('https://api.pexels.com/videos/search', [
                'query' => $query,
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

        try {
            $response = Http::withHeaders([
                'Authorization' => $key,
            ])->timeout(8)->get(self::BASE_URL . "/photos/{$assetId}");

            if ($response->successful()) {
                $p = $response->json();
                $w = (int) ($p['width'] ?? 1024);
                $h = (int) ($p['height'] ?? 1024);

                return [
                    'external_id' => (string) $p['id'],
                    'title' => (string) ($p['alt'] ?: 'Foto Inspirasi Pexels'),
                    'caption' => (string) ($p['alt'] ?? ''),
                    'image_url' => (string) ($p['src']['large2x'] ?? $p['src']['large'] ?? $p['src']['original'] ?? ''),
                    'thumbnail_url' => (string) ($p['src']['medium'] ?? $p['src']['tiny'] ?? ''),
                    'width' => $w,
                    'height' => $h,
                    'aspect_ratio' => $this->calculateAspectRatio($w, $h),
                    'author_name' => (string) ($p['photographer'] ?? 'Pexels Creator'),
                    'author_url' => (string) ($p['photographer_url'] ?? ''),
                    'source_url' => (string) ($p['url'] ?? ''),
                    'license_name' => 'Pexels License (Free Commercial Use)',
                    'commercial_use_allowed' => true,
                    'require_attribution' => true,
                    'attribution_text' => 'Photo by ' . ($p['photographer'] ?? 'Creator') . ' on Pexels',
                    'tags' => [],
                    'raw_metadata' => $p,
                ];
            }
        } catch (Throwable) {
            // Return null on failure
        }

        return null;
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
            $res = Http::withHeaders(['Authorization' => $key])
                ->timeout(6)
                ->get(self::BASE_URL . '/search', ['query' => 'product', 'per_page' => 1]);

            $latency = (int) round((microtime(true) - $start) * 1000);

            if ($res->successful()) {
                return [
                    'success' => true,
                    'status_code' => $res->status(),
                    'message' => 'Koneksi Pexels API Resmi Terhubung & Valid.',
                    'latency_ms' => $latency,
                ];
            }

            return [
                'success' => false,
                'status_code' => $res->status(),
                'message' => 'Pexels menolak kredensial: ' . ($res->json('error') ?? 'HTTP ' . $res->status()),
                'latency_ms' => $latency,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'status_code' => 500,
                'message' => 'Koneksi ke Pexels gagal: ' . $e->getMessage(),
                'latency_ms' => (int) round((microtime(true) - $start) * 1000),
            ];
        }
    }

    public function normalizeResponse(mixed $response): array
    {
        if (!is_array($response) || empty($response['photos'])) {
            return [];
        }

        $items = [];
        foreach ($response['photos'] as $p) {
            $w = (int) ($p['width'] ?? 1024);
            $h = (int) ($p['height'] ?? 1024);

            $items[] = [
                'external_id' => (string) $p['id'],
                'title' => !empty($p['alt']) ? (string) $p['alt'] : 'Foto Inspirasi Pexels #' . $p['id'],
                'caption' => (string) ($p['alt'] ?? ''),
                'image_url' => (string) ($p['src']['large2x'] ?? $p['src']['large'] ?? $p['src']['original'] ?? ''),
                'thumbnail_url' => (string) ($p['src']['medium'] ?? $p['src']['small'] ?? $p['src']['tiny'] ?? ''),
                'width' => $w,
                'height' => $h,
                'aspect_ratio' => $this->calculateAspectRatio($w, $h),
                'author_name' => (string) ($p['photographer'] ?? 'Pexels Contributor'),
                'author_url' => (string) ($p['photographer_url'] ?? ''),
                'source_url' => (string) ($p['url'] ?? 'https://www.pexels.com'),
                'source_provider' => 'pexels',
                'attribution' => [
                    'photographer_name' => (string) ($p['photographer'] ?? 'Pexels Creator'),
                    'photographer_url' => (string) ($p['photographer_url'] ?? ''),
                    'source_page_url' => (string) ($p['url'] ?? 'https://www.pexels.com'),
                ],
                'license' => [
                    'policy' => 'Pexels Commercial License',
                    'commercial_use' => true,
                    'attribution_required' => true,
                ],
                'license_name' => 'Pexels Free to Use',
                'commercial_use_allowed' => true,
                'require_attribution' => true,
                'attribution_text' => 'Foto oleh ' . ($p['photographer'] ?? 'Kontributor') . ' di Pexels',
                'tags' => [],
                'raw_metadata' => [
                    'avg_color' => $p['avg_color'] ?? null,
                    'photographer_id' => $p['photographer_id'] ?? null,
                ],
            ];
        }

        return $items;
    }

    public function getAttribution(string $assetId): array
    {
        $details = $this->getAssetDetails($assetId);

        return [
            'photographer_name' => $details['author_name'] ?? 'Pexels Creator',
            'photographer_url' => $details['author_url'] ?? null,
            'source_name' => 'Pexels',
            'source_url' => $details['source_url'] ?? 'https://www.pexels.com',
            'attribution_text' => $details['attribution_text'] ?? 'Foto bersumber dari Pexels (Free License)',
            'license_name' => 'Pexels License',
            'license_url' => 'https://www.pexels.com/license/',
        ];
    }

    public function getLicenseMetadata(string $assetId): array
    {
        return [
            'license' => 'Pexels License',
            'license_url' => 'https://www.pexels.com/license/',
            'allow_commercial_use' => true,
            'allow_redistribution' => false, // Standalone resale strictly prohibited
            'require_attribution' => true,
            'disclaimer' => 'Semua foto di Pexels bebas digunakan untuk keperluan komersial dan non-komersial. Dilarang menjual kembali foto tanpa modifikasi.',
        ];
    }

    public function recordDownload(string $assetId): bool
    {
        // Pexels tidak mewajibkan tracking endpoint khusus via API
        return true;
    }

    public function validateUsagePolicy(): array
    {
        return [
            'allowed' => true,
            'reason' => 'Penggunaan sebagai inspirasi gaya visual & referensi komposisi sesuai dengan Kebijakan Pexels.',
        ];
    }

    private function resolveApiKey(): ?string
    {
        if (!empty($this->apiKey)) {
            return $this->apiKey;
        }

        // Cek dari database encrypted credentials
        try {
            $source = InspirationSource::where('code', 'pexels')->first();
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
            // Fallback to env/config
        }

        return config('services.pexels.api_key') ?: env('PEXELS_API_KEY');
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
