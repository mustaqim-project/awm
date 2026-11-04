<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Adapters;

use App\Domain\Ai\Platform\AiPlatformEngineService;
use App\Domain\Ai\Studio\Contracts\ImageGenerationProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GrokImageAdapter implements ImageGenerationProviderInterface
{
    private const DEFAULT_ENDPOINT = 'https://api.x.ai/v1';

    public function __construct(
        protected ?string $apiKey = null,
        protected ?string $baseUrl = null,
        protected ?AiPlatformEngineService $engineService = null
    ) {
        $this->engineService = $engineService ?? app(AiPlatformEngineService::class);
    }

    public function getProviderName(): string
    {
        return 'grok';
    }

    public function supportsAspectRatio(string $aspectRatio): bool
    {
        return in_array($aspectRatio, ['1:1', '4:5', '9:16', '16:9', '4:3'], true);
    }

    public function generate(array $payload): array
    {
        $startTime = microtime(true);
        $keyData = $this->resolveActiveKey();

        if (empty($keyData['key'])) {
            return [
                'status' => 'failed',
                'provider' => 'grok',
                'model_name' => $payload['model_name'] ?? 'grok-2-image',
                'provider_job_id' => null,
                'images' => [],
                'latency_ms' => 0,
                'error_message' => 'xAI Grok API key belum dikonfigurasi pada AI Platform Engine.',
            ];
        }

        $endpoint = rtrim($keyData['endpoint'] ?: self::DEFAULT_ENDPOINT, '/');
        $modelName = $payload['model_name'] ?: 'grok-2-image';
        $aspectRatio = (string) ($payload['aspect_ratio'] ?? '1:1');
        $size = $this->resolveDimensions($aspectRatio, (int) ($payload['width'] ?? 1024), (int) ($payload['height'] ?? 1024));

        $requestBody = [
            'model' => $modelName,
            'prompt' => $payload['positive_prompt'],
            'n' => (int) ($payload['batch_count'] ?? 1),
            'size' => "{$size['width']}x{$size['height']}",
            'response_format' => 'url',
        ];

        // Jika ada referensi gambar
        if (!empty($payload['reference_image_path'])) {
            $requestBody['reference_image'] = $payload['reference_image_path'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $keyData['key'],
                'Content-Type' => 'application/json',
            ])->timeout(60)->post("{$endpoint}/images/generations", $requestBody);

            $latency = (int) round((microtime(true) - $startTime) * 1000);

            if (!$response->successful()) {
                $errBody = $response->json();
                $errMessage = $errBody['error']['message'] ?? $errBody['message'] ?? ('xAI Grok API HTTP ' . $response->status());
                Log::warning("[GrokImageAdapter] API call failed: {$errMessage}");

                return [
                    'status' => 'failed',
                    'provider' => 'grok',
                    'model_name' => $modelName,
                    'provider_job_id' => null,
                    'images' => [],
                    'latency_ms' => $latency,
                    'error_message' => $errMessage,
                    'raw_response' => $errBody ?: ['raw' => $response->body()],
                ];
            }

            $json = $response->json();
            $images = [];

            if (!empty($json['data']) && is_array($json['data'])) {
                foreach ($json['data'] as $item) {
                    $url = $item['url'] ?? null;
                    if (!empty($url)) {
                        $images[] = [
                            'url' => (string) $url,
                            'width' => $size['width'],
                            'height' => $size['height'],
                            'content_type' => 'image/png',
                        ];
                    }
                }
            }

            return [
                'status' => !empty($images) ? 'succeeded' : 'failed',
                'provider' => 'grok',
                'model_name' => $modelName,
                'provider_job_id' => (string) ($json['id'] ?? Str::uuid()->toString()),
                'images' => $images,
                'latency_ms' => $latency,
                'error_message' => empty($images) ? 'Grok tidak mengembalikan gambar valid.' : null,
                'raw_response' => $json,
            ];
        } catch (Throwable $e) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            Log::error("[GrokImageAdapter] Exception: " . $e->getMessage());

            return [
                'status' => 'failed',
                'provider' => 'grok',
                'model_name' => $modelName,
                'provider_job_id' => null,
                'images' => [],
                'latency_ms' => $latency,
                'error_message' => 'Grok connection error: ' . $e->getMessage(),
            ];
        }
    }

    protected function resolveActiveKey(): array
    {
        if (!empty($this->apiKey)) {
            return [
                'key' => $this->apiKey,
                'endpoint' => $this->baseUrl ?: self::DEFAULT_ENDPOINT,
            ];
        }

        // Resolusi dari engine service multi-key pool
        if ($this->engineService !== null) {
            $keyData = $this->engineService->resolveNextImageKey('xai');
            if (!empty($keyData['key'])) {
                return $keyData;
            }
        }

        return [
            'key' => config('services.xai.api_key') ?: env('XAI_API_KEY') ?: env('GROK_API_KEY'),
            'endpoint' => config('services.xai.endpoint') ?: self::DEFAULT_ENDPOINT,
        ];
    }

    protected function resolveDimensions(string $ratio, int $preferredW, int $preferredH): array
    {
        return match ($ratio) {
            '16:9' => ['width' => 1792, 'height' => 1024],
            '9:16' => ['width' => 1024, 'height' => 1792],
            '4:5' => ['width' => 1024, 'height' => 1280],
            '4:3' => ['width' => 1344, 'height' => 1024],
            default => ['width' => 1024, 'height' => 1024],
        };
    }
}
