<?php

declare(strict_types=1);

namespace App\Domain\Ai\Providers;

use App\Domain\Ai\Support\AiStructuredOutputParser;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

use App\Models\SystemSetting;

final class OpenAiCompatibleProvider implements AiProviderInterface
{
    private const DEFAULT_BASE_URL = 'https://api.openai.com/v1';

    /**
     * @var array<int, array{id: string, name: string, tier: string}>|null
     */
    private ?array $customModels = null;

    /**
     * @param array<int, array{id: string, name: string, tier: string}>|null $customModels
     */
    public function __construct(
        private readonly ?string $defaultEndpoint = null,
        ?array $customModels = null
    ) {
        $this->customModels = $customModels;
    }

    public function getProviderName(): string
    {
        return 'openai_compatible';
    }

    /**
     * Resolves the target base URL endpoint.
     */
    public function resolveEndpoint(?string $endpoint = null): string
    {
        $raw = trim((string) ($endpoint ?: ($this->defaultEndpoint ?: self::DEFAULT_BASE_URL)));
        return rtrim($raw, '/');
    }

    /**
     * The standard baseline seed models for the OpenAI Compatible tunnel.
     * Can be replaced, added, or deleted dynamically without hardcoding.
     *
     * @return array<int, array{id: string, name: string, tier: string}>
     */
    public static function getDefaultSeedModels(): array
    {
        return [
            ['id' => 'ag/gemini-3.8-flash-high', 'name' => 'Gemini 3.8 Flash High (Flagship Reasoning)', 'tier' => 'reasoning'],
            ['id' => 'ag/gemini-3.8-flash-medium', 'name' => 'Gemini 3.8 Flash Medium (Fast & Balanced)', 'tier' => 'balanced'],
            ['id' => 'ag/gemini-3.8-flash-low', 'name' => 'Gemini 3.8 Flash Low (Ultra Fast)', 'tier' => 'fast'],
            ['id' => 'ag/gemini-3.7-flash-high', 'name' => 'Gemini 3.7 Flash High (Deep Reasoning)', 'tier' => 'reasoning'],
            ['id' => 'cx/gpt-6-astra-xhigh', 'name' => 'GPT-6 Astra X-High (Heavy Reasoning)', 'tier' => 'reasoning'],
            ['id' => 'cx/gpt-6-astra', 'name' => 'GPT-6 Astra (Next-Gen Flagship)', 'tier' => 'balanced'],
            ['id' => 'cx/gpt-5.6-sol-xhigh', 'name' => 'GPT-5.6 Sol X-High', 'tier' => 'reasoning'],
            ['id' => 'cx/gpt-5.6-sol', 'name' => 'GPT-5.6 Sol (High Speed & Accurate)', 'tier' => 'fast'],
            ['id' => 'cx/gpt-5.6-terra', 'name' => 'GPT-5.6 Terra', 'tier' => 'balanced'],
            ['id' => 'cx/gpt-5.6-luna', 'name' => 'GPT-5.6 Luna', 'tier' => 'fast'],
            ['id' => 'cx/gpt-5.5-xhigh', 'name' => 'GPT-5.5 X-High', 'tier' => 'reasoning'],
            ['id' => 'cx/gpt-5.5', 'name' => 'GPT-5.5 Standard', 'tier' => 'balanced'],
            ['id' => 'cx/gpt-5.4', 'name' => 'GPT-5.4 Turbo', 'tier' => 'fast'],
        ];
    }

    /**
     * Get available models dynamically (checks memory, system settings, then default seeds).
     *
     * @return array<int, array{id: string, name: string, tier: string}>
     */
    public function getAvailableModels(): array
    {
        if ($this->customModels !== null) {
            return $this->customModels;
        }

        try {
            $raw = SystemSetting::get('ai_platform_available_models');
            if ($raw !== null && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && ! empty($decoded)) {
                    return $decoded;
                }
            }
        } catch (Throwable) {
            // Ignore database unavailability during early bootstrap
        }

        return self::getDefaultSeedModels();
    }

    /**
     * Dynamically override available models at runtime.
     *
     * @param array<int, array{id: string, name: string, tier: string}> $models
     */
    public function setAvailableModels(array $models): void
    {
        $this->customModels = $models;
    }

    /**
     * Sanitize API key by stripping quotes, whitespace, Bearer prefixes.
     */
    public function sanitizeApiKey(string $apiKey): string
    {
        $key = trim($apiKey, " \t\n\r\0\x0B\"'");
        if (str_starts_with(strtolower($key), 'bearer ')) {
            $key = trim(substr($key, 7));
        }

        return trim($key);
    }

    /**
     * Test connection to the OpenAI compatible endpoint.
     *
     * @return array{success: bool, message: string, latency_ms?: int, available_models?: array}
     */
    public function testConnection(string $apiKey, string $model, ?string $endpoint = null): array
    {
        $sanitizedKey = $this->sanitizeApiKey($apiKey);
        if ($sanitizedKey === '') {
            return [
                'success' => false,
                'message' => 'API Key kosong atau format tidak valid.',
            ];
        }

        $targetEndpoint = $this->resolveEndpoint($endpoint);
        $targetModel = trim($model) ?: 'ag/gemini-3.8-flash-high';
        $startTime = microtime(true);

        try {
            $response = Http::withToken($sanitizedKey)
                ->timeout(15)
                ->post($targetEndpoint . '/chat/completions', [
                    'model' => $targetModel,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Ping. Respond with single word "OK".'],
                    ],
                    'max_tokens' => 20,
                    'stream' => false,
                ]);

            $latency = (int) round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                // Also attempt auto-discovering models if endpoint supports /models
                $discovered = $this->fetchAvailableModels($sanitizedKey, $targetEndpoint);

                return [
                    'success' => true,
                    'message' => "Koneksi ke endpoint ({$targetModel}) berhasil terverifikasi! (Latency: {$latency}ms)",
                    'latency_ms' => $latency,
                    'available_models' => $discovered,
                ];
            }

            $status = $response->status();
            $err = $response->json('error.message') ?? $response->body();

            if ($status === 401 || str_contains(strtolower((string) $err), 'invalid api key')) {
                return [
                    'success' => false,
                    'message' => "Kunci API tidak valid (401 Unauthorized) pada endpoint {$targetEndpoint}.",
                    'latency_ms' => $latency,
                ];
            }

            if ($status === 429 || str_contains(strtolower((string) $err), 'rate limit') || str_contains(strtolower((string) $err), 'quota')) {
                return [
                    'success' => false,
                    'message' => 'Endpoint mengembalikan 429: Batas kuota atau rate limit API Key telah tercapai.',
                    'latency_ms' => $latency,
                ];
            }

            return [
                'success' => false,
                'message' => "Gagal terhubung ke {$targetEndpoint} (HTTP {$status}): " . $err,
                'latency_ms' => $latency,
            ];
        } catch (Throwable $e) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'success' => false,
                'message' => "Koneksi ke {$targetEndpoint} error/timeout: " . $e->getMessage(),
                'latency_ms' => $latency,
            ];
        }
    }

    /**
     * Fetch all available models from /models endpoint dynamically.
     *
     * @return array<int, array{id: string, name: string, tier: string}>
     */
    public function fetchAvailableModels(string $apiKey, ?string $endpoint = null): array
    {
        $sanitizedKey = $this->sanitizeApiKey($apiKey);
        $targetEndpoint = $this->resolveEndpoint($endpoint);

        try {
            $response = Http::withToken($sanitizedKey)
                ->timeout(10)
                ->get($targetEndpoint . '/models');

            if (! $response->successful()) {
                return $this->getAvailableModels();
            }

            $json = $response->json();
            $data = $json['data'] ?? $json['models'] ?? [];

            if (! is_array($data) || empty($data)) {
                return $this->getAvailableModels();
            }

            $models = [];
            foreach ($data as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $id = (string) ($item['id'] ?? '');
                if ($id === '') {
                    continue;
                }

                $lower = strtolower($id);
                $tier = 'balanced';
                if (str_contains($lower, 'reason') || str_contains($lower, 'xhigh') || str_contains($lower, 'o1') || str_contains($lower, 'o3') || str_contains($lower, 'high')) {
                    $tier = 'reasoning';
                } elseif (str_contains($lower, 'mini') || str_contains($lower, 'fast') || str_contains($lower, 'low') || str_contains($lower, 'flash') || str_contains($lower, 'sol')) {
                    $tier = 'fast';
                }

                $models[] = [
                    'id' => $id,
                    'name' => (string) ($item['name'] ?? $id),
                    'tier' => $tier,
                ];
            }

            return ! empty($models) ? $models : $this->getAvailableModels();
        } catch (Throwable) {
            return $this->getAvailableModels();
        }
    }

    /**
     * Send chat completion request.
     *
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed> $options
     * @return array{content: string, raw: array<string, mixed>, usage: array<string, int>}
     */
    public function chat(string $apiKey, string $model, array $messages, array $options = []): array
    {
        $targetEndpoint = $this->resolveEndpoint($options['endpoint'] ?? null);
        $sanitizedKey = $this->sanitizeApiKey($apiKey);

        $payload = [
            'model' => $model ?: 'ag/gemini-3.8-flash-high',
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.2,
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'stream' => false,
        ];

        $response = Http::withToken($sanitizedKey)
            ->timeout($options['timeout'] ?? 35)
            ->post($targetEndpoint . '/chat/completions', $payload);

        if (! $response->successful()) {
            throw new RuntimeException("OpenAI Compatible Error ({$response->status()}): " . ($response->json('error.message') ?? $response->body()));
        }

        $json = $response->json();
        $content = (string) ($json['choices'][0]['message']['content'] ?? '');

        return [
            'content' => $content,
            'raw' => is_array($json) ? $json : [],
            'usage' => [
                'prompt_tokens' => (int) ($json['usage']['prompt_tokens'] ?? 0),
                'completion_tokens' => (int) ($json['usage']['completion_tokens'] ?? 0),
                'total_tokens' => (int) ($json['usage']['total_tokens'] ?? 0),
            ],
        ];
    }

    /**
     * Send structured prompt expecting validated JSON output.
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function structuredPrompt(string $apiKey, string $model, string $systemPrompt, string $userPrompt, array $options = []): array
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt . "\nOutput MUST be valid RFC-8259 JSON only. No markdown formatting, no backticks, no comments."],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $targetEndpoint = $this->resolveEndpoint($options['endpoint'] ?? null);
        $sanitizedKey = $this->sanitizeApiKey($apiKey);

        $payload = [
            'model' => $model ?: 'ag/gemini-3.8-flash-high',
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.1,
            'max_tokens' => $options['max_tokens'] ?? 3000,
            'stream' => false,
        ];

        // Attempt response_format if not disabled
        if (! ($options['disable_response_format'] ?? false)) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::withToken($sanitizedKey)
            ->timeout($options['timeout'] ?? 35)
            ->post($targetEndpoint . '/chat/completions', $payload);

        if (! $response->successful()) {
            // Some models don't support response_format type json_object, retry once without it
            if ($response->status() === 400 && isset($payload['response_format'])) {
                unset($payload['response_format']);
                $response = Http::withToken($sanitizedKey)
                    ->timeout($options['timeout'] ?? 35)
                    ->post($targetEndpoint . '/chat/completions', $payload);
            }

            if (! $response->successful()) {
                throw new RuntimeException("OpenAI Compatible Error: " . ($response->json('error.message') ?? $response->body()));
            }
        }

        $content = (string) ($response->json('choices.0.message.content') ?? '{}');

        return AiStructuredOutputParser::parse($content);
    }

    /**
     * Generate plain text completion for services requiring generateText (e.g. CustomerSupportAiService).
     *
     * @param array<string, mixed> $options
     * @return array{text: string, usage: array<string, int>}
     */
    public function generateText(string $prompt, array $options = []): array
    {
        $apiKey = (string) ($options['api_key'] ?? '');
        $model = (string) ($options['model'] ?? 'ag/gemini-3.8-flash-high');

        $result = $this->chat($apiKey, $model, [
            ['role' => 'user', 'content' => $prompt],
        ], $options);

        return [
            'text' => $result['content'],
            'usage' => $result['usage'],
        ];
    }
}
