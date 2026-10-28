<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Adapters;

use App\Domain\Ai\Studio\Contracts\ImageGenerationProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OpenAiImageAdapter implements ImageGenerationProviderInterface
{
    public function __construct(
        protected ?string $apiKey = null,
        protected ?string $baseUrl = null,
        protected bool $forceMock = false
    ) {
        $this->apiKey = $apiKey ?? (string) config('services.openai.api_key', env('OPENAI_API_KEY', ''));
        $this->baseUrl = $baseUrl ?? (string) config('services.openai.base_url', 'https://api.openai.com/v1');
    }

    /**
     * Nama unik provider.
     */
    public function getProviderName(): string
    {
        return 'openai';
    }

    /**
     * Periksa apakah rasio aspek didukung.
     */
    public function supportsAspectRatio(string $aspectRatio): bool
    {
        return in_array($aspectRatio, ['1:1', '4:5', '9:16', '16:9'], true);
    }

    /**
     * Eksekusi generasi gambar ke OpenAI DALL-E 3 API (atau mock mode di sandbox/testing).
     *
     * @param array{
     *     positive_prompt: string,
     *     negative_prompt?: ?string,
     *     aspect_ratio?: string,
     *     width?: int,
     *     height?: int,
     *     batch_count?: int,
     *     seed?: ?int,
     *     style_preset?: ?string,
     *     reference_images?: array<int, string>
     * } $payload
     *
     * @return array{
     *     status: string,
     *     provider: string,
     *     model_name: string,
     *     provider_job_id: ?string,
     *     images: array<int, array{
     *         url: string,
     *         width: int,
     *         height: int,
     *         content_type?: string,
     *         seed?: int
     *     }>,
     *     latency_ms: int,
     *     error_message: ?string,
     *     raw_response?: array<string, mixed>
     * }
     */
    public function generate(array $payload): array
    {
        $startTime = microtime(true);
        $prompt = trim($payload['positive_prompt'] ?? '');
        $aspectRatio = (string) ($payload['aspect_ratio'] ?? '1:1');
        $model = 'dall-e-3';

        // 1. Jika API key kosong atau running di mock/testing mode, gunakan respon mockup terisolasi
        if ($this->forceMock || empty($this->apiKey) || app()->environment('testing')) {
            return $this->generateMockResponse($prompt, $aspectRatio, $startTime);
        }

        // 2. Pemetaan ukuran OpenAI resmi
        $size = match ($aspectRatio) {
            '9:16', '4:5' => '1024x1792',
            '16:9' => '1792x1024',
            default => '1024x1024',
        };

        [$width, $height] = explode('x', $size);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(60)
                ->post(rtrim($this->baseUrl, '/') . '/images/generations', [
                    'model' => $model,
                    'prompt' => $prompt,
                    'n' => 1, // DALL-E 3 hanya mendukung n=1 per call
                    'size' => $size,
                    'quality' => 'standard',
                    'response_format' => 'url',
                ]);

            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                $imageUrl = (string) ($data['data'][0]['url'] ?? '');
                $jobId = 'openai_' . Str::uuid();

                return [
                    'status' => 'succeeded',
                    'provider' => 'openai',
                    'model_name' => $model,
                    'provider_job_id' => $jobId,
                    'images' => [
                        [
                            'url' => $imageUrl,
                            'width' => (int) $width,
                            'height' => (int) $height,
                            'content_type' => 'image/png',
                        ],
                    ],
                    'latency_ms' => $latencyMs,
                    'error_message' => null,
                    'raw_response' => $data,
                ];
            }

            $errorMessage = $response->json('error.message') ?? "OpenAI API returned HTTP {$response->status()}";
            Log::warning('OpenAI Image generation failed', [
                'status' => $response->status(),
                'error' => $errorMessage,
            ]);

            return [
                'status' => 'failed',
                'provider' => 'openai',
                'model_name' => $model,
                'provider_job_id' => null,
                'images' => [],
                'latency_ms' => $latencyMs,
                'error_message' => $errorMessage,
                'raw_response' => $response->json() ?? [],
            ];
        } catch (Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            Log::error('OpenAI Image Adapter exception: ' . $e->getMessage());

            return [
                'status' => 'failed',
                'provider' => 'openai',
                'model_name' => $model,
                'provider_job_id' => null,
                'images' => [],
                'latency_ms' => $latencyMs,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Respon sintetik berkualitas tinggi untuk lingkungan pengujian dan dev offline.
     *
     * @return array<string, mixed>
     */
    protected function generateMockResponse(string $prompt, string $aspectRatio, float $startTime): array
    {
        $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

        [$width, $height] = match ($aspectRatio) {
            '9:16', '4:5' => [1024, 1792],
            '16:9' => [1792, 1024],
            default => [1024, 1024],
        };

        $jobId = 'mock_openai_' . Str::uuid();
        $safePromptSlug = Str::slug(substr($prompt, 0, 30));
        $mockUrl = "https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w={$width}&h={$height}&fit=crop&q=80#ai_studio_{$safePromptSlug}";

        return [
            'status' => 'succeeded',
            'provider' => 'openai',
            'model_name' => 'dall-e-3-mock',
            'provider_job_id' => $jobId,
            'images' => [
                [
                    'url' => $mockUrl,
                    'width' => $width,
                    'height' => $height,
                    'content_type' => 'image/jpeg',
                ],
            ],
            'latency_ms' => max(150, $latencyMs),
            'error_message' => null,
            'raw_response' => ['mock' => true],
        ];
    }
}
