<?php

declare(strict_types=1);

namespace App\Domain\Ai\Platform;

use App\Domain\Ai\Providers\AiProviderInterface;
use App\Domain\Ai\Providers\AiProviderManager;
use App\Domain\Ai\Providers\OpenAiCompatibleProvider;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

final class AiPlatformEngineService
{
    public const DEFAULT_PROVIDER = 'openai_compatible';
    public const DEFAULT_ENDPOINT = 'https://r4g77gv.abc-tunnel.us/v1';
    public const DEFAULT_MODEL = 'ag/gemini-3.8-flash-high';
    public const DEFAULT_STRATEGY = 'round_robin';
    public const DEFAULT_API_KEY = 'sk-59807b51bc76063d-ge0agt-97ac1590';

    /**
     * Supported AI Providers Catalog & Metadata (9Router Architecture).
     *
     * @var array<string, array<string, mixed>>
     */
    public const PROVIDERS_META = [
        'openai_compatible' => [
            'id' => 'openai_compatible',
            'name' => 'OpenAI Compatible (Custom Tunnel / vLLM / Proxy)',
            'short_name' => 'Tunnel / Proxy',
            'badge' => 'Tunnel / vLLM',
            'badge_color' => 'blue',
            'default_endpoint' => 'https://r4g77gv.abc-tunnel.us/v1',
            'key_prefix' => 'sk-',
            'default_model' => 'ag/gemini-3.8-flash-high',
            'description' => 'Server private AI, vLLM, LiteLLM, Ollama, Groq proxy, Cloudflare / Ngrok tunnel.',
            'format_notes' => 'Format sk-... dengan endpoint OpenAI v1-compliant.',
        ],
        'anthropic' => [
            'id' => 'anthropic',
            'name' => 'Anthropic Claude Official',
            'short_name' => 'Anthropic Claude',
            'badge' => 'Claude Direct',
            'badge_color' => 'amber',
            'default_endpoint' => 'https://api.anthropic.com/v1',
            'key_prefix' => 'sk-ant-',
            'default_model' => 'claude-3-7-sonnet-20250219',
            'description' => 'Mendukung Claude 3.7 Sonnet, Claude 3.5 Haiku, Claude 3 Opus resmi.',
            'format_notes' => 'Format sk-ant-api03-... dari console.anthropic.com.',
        ],
        'gemini' => [
            'id' => 'gemini',
            'name' => 'Google Gemini Official',
            'short_name' => 'Google Gemini',
            'badge' => 'Google AI Studio',
            'badge_color' => 'purple',
            'default_endpoint' => 'https://generativelanguage.googleapis.com/v1beta',
            'key_prefix' => 'AIzaSy',
            'default_model' => 'gemini-2.0-flash',
            'description' => 'Mendukung Gemini 2.0 Flash, Gemini 1.5 Pro via Google AI Studio API.',
            'format_notes' => 'Format AIzaSy... dari aistudio.google.com.',
        ],
        'openai' => [
            'id' => 'openai',
            'name' => 'OpenAI Official Direct',
            'short_name' => 'OpenAI Official',
            'badge' => 'OpenAI Direct',
            'badge_color' => 'emerald',
            'default_endpoint' => 'https://api.openai.com/v1',
            'key_prefix' => 'sk-proj-',
            'default_model' => 'gpt-4o-mini',
            'description' => 'Mendukung GPT-4o, GPT-4o-mini, o1, o3-mini dari platform.openai.com.',
            'format_notes' => 'Format sk-proj-... atau sk-... dari platform.openai.com.',
        ],
        'openrouter' => [
            'id' => 'openrouter',
            'name' => 'OpenRouter AI Gateway',
            'short_name' => 'OpenRouter Gateway',
            'badge' => 'OpenRouter',
            'badge_color' => 'cyan',
            'default_endpoint' => 'https://openrouter.ai/api/v1',
            'key_prefix' => 'sk-or-',
            'default_model' => 'deepseek/deepseek-r1',
            'description' => 'Gateway universal ke 200+ model AI (DeepSeek-R1, Llama 3.3, Qwen, Mistral).',
            'format_notes' => 'Format sk-or-v1-... dari openrouter.ai.',
        ],
        'ideogram' => [
            'id' => 'ideogram',
            'name' => 'Ideogram AI Official',
            'short_name' => 'Ideogram AI',
            'badge' => 'Ideogram v2',
            'badge_color' => 'rose',
            'default_endpoint' => 'https://api.ideogram.ai',
            'key_prefix' => 'ideo-',
            'default_model' => 'ideogram-v2',
            'description' => 'Mendukung Ideogram v2, typography poster, dan render komersial.',
            'format_notes' => 'API Key dari ideogram.ai/manage-api.',
        ],
        'stability' => [
            'id' => 'stability',
            'name' => 'Stability AI / Flux Pro',
            'short_name' => 'Stability / Flux',
            'badge' => 'Flux Pro',
            'badge_color' => 'indigo',
            'default_endpoint' => 'https://api.stability.ai/v2beta',
            'key_prefix' => 'sk-',
            'default_model' => 'stable-diffusion-3.5-large',
            'description' => 'Mendukung SD 3.5 Large, SD 3.5 Turbo, dan Flux Pro.',
            'format_notes' => 'API Key dari platform.stability.ai.',
        ],
    ];

    public function __construct(
        private readonly ?AiProviderManager $providerManager = null
    ) {}

    private function getManager(): AiProviderManager
    {
        return $this->providerManager ?? new AiProviderManager();
    }

    /**
     * Infer AI Provider from API Key prefix or key label.
     */
    public function inferProviderFromKeyOrName(string $key, string $name = ''): string
    {
        $lowerName = strtolower($name);
        $trimmedKey = trim($key);

        if (str_starts_with($trimmedKey, 'sk-ant-') || str_contains($lowerName, 'anthropic') || str_contains($lowerName, 'claude')) {
            return 'anthropic';
        }

        if (str_starts_with($trimmedKey, 'AIzaSy') || str_contains($lowerName, 'gemini') || str_contains($lowerName, 'google')) {
            return 'gemini';
        }

        if (str_starts_with($trimmedKey, 'sk-or-') || str_contains($lowerName, 'openrouter')) {
            return 'openrouter';
        }

        if (str_starts_with($trimmedKey, 'sk-proj-') || str_contains($lowerName, 'openai')) {
            return 'openai';
        }

        if (str_starts_with($trimmedKey, 'ideo-') || str_contains($lowerName, 'ideogram')) {
            return 'ideogram';
        }

        if (str_contains($lowerName, 'stability') || str_contains($lowerName, 'flux')) {
            return 'stability';
        }

        return 'openai_compatible';
    }

    /**
     * Get raw keys pool from secure storage (internal backend only).
     * Automatically migrates legacy keys to include 9Router provider and endpoint fields.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRawKeysPool(): array
    {
        $poolRaw = SystemSetting::get('ai_platform_keys_pool');
        if ($poolRaw !== null && $poolRaw !== '') {
            $decoded = json_decode($poolRaw, true);
            if (is_array($decoded) && ! empty($decoded)) {
                $needsResave = false;
                $normalized = [];

                foreach ($decoded as $idx => $k) {
                    if (! is_array($k)) {
                        continue;
                    }

                    $rawKey = (string) ($k['key'] ?? '');
                    $name = (string) ($k['name'] ?? ('Key #' . ($idx + 1)));

                    // Infer provider if not present
                    $provider = (string) ($k['provider'] ?? '');
                    if ($provider === '' || ! isset(self::PROVIDERS_META[$provider])) {
                        $provider = $this->inferProviderFromKeyOrName($rawKey, $name);
                        $needsResave = true;
                    }

                    $meta = self::PROVIDERS_META[$provider] ?? self::PROVIDERS_META[self::DEFAULT_PROVIDER];

                    $endpoint = (string) ($k['endpoint'] ?? '');
                    if ($endpoint === '') {
                        $endpoint = (string) ($meta['default_endpoint'] ?? self::DEFAULT_ENDPOINT);
                        $needsResave = true;
                    }

                    $model = (string) ($k['model'] ?? '');
                    if ($model === '') {
                        $model = (string) ($meta['default_model'] ?? self::DEFAULT_MODEL);
                        $needsResave = true;
                    }

                    $purpose = (string) ($k['purpose'] ?? '');
                    if ($purpose === '' || ! in_array($purpose, ['chat', 'image', 'all'], true)) {
                        $provLower = strtolower($provider);
                        $modLower = strtolower($model);
                        if (in_array($provLower, ['ideogram', 'stability'], true) || str_contains($modLower, 'dall-e') || str_contains($modLower, 'flux') || str_contains($modLower, 'sd') || str_contains($modLower, 'image')) {
                            $purpose = 'image';
                        } else {
                            $purpose = 'chat';
                        }
                        $needsResave = true;
                    }

                    $priority = (int) ($k['priority'] ?? 1);
                    $weight = (int) ($k['weight'] ?? 1);
                    $status = (string) ($k['status'] ?? 'healthy');

                    $normalized[] = [
                        'id' => (string) ($k['id'] ?? ('key_' . Str::random(10))),
                        'name' => $name,
                        'provider' => $provider,
                        'purpose' => $purpose,
                        'key' => $rawKey,
                        'endpoint' => $endpoint,
                        'model' => $model,
                        'priority' => max(1, min(3, $priority)),
                        'weight' => max(1, min(10, $weight)),
                        'is_active' => (bool) ($k['is_active'] ?? true),
                        'status' => $status,
                        'latency_ms' => (int) ($k['latency_ms'] ?? 0),
                        'total_requests' => (int) ($k['total_requests'] ?? 0),
                        'total_errors' => (int) ($k['total_errors'] ?? 0),
                        'last_used_at' => $k['last_used_at'] ?? null,
                        'last_tested_at' => $k['last_tested_at'] ?? null,
                        'last_error' => $k['last_error'] ?? null,
                    ];
                }

                if ($needsResave) {
                    SystemSetting::set('ai_platform_keys_pool', json_encode($normalized), 'ai_engine', true);
                }

                return $normalized;
            }
        }

        // Pre-populate with default tested key if pool is empty
        $keysPool = [
            [
                'id' => 'key_tunnel_primary',
                'name' => 'Tunnel ABC Primary (Ready)',
                'provider' => self::DEFAULT_PROVIDER,
                'purpose' => 'chat',
                'key' => self::DEFAULT_API_KEY,
                'endpoint' => self::DEFAULT_ENDPOINT,
                'model' => self::DEFAULT_MODEL,
                'priority' => 1,
                'weight' => 1,
                'is_active' => true,
                'status' => 'healthy',
                'latency_ms' => 0,
                'total_requests' => 0,
                'total_errors' => 0,
                'last_used_at' => null,
                'last_tested_at' => null,
                'last_error' => null,
            ],
        ];

        SystemSetting::set('ai_platform_keys_pool', json_encode($keysPool), 'ai_engine', true);
        SystemSetting::set('ai_platform_provider', self::DEFAULT_PROVIDER, 'ai_engine');
        SystemSetting::set('ai_platform_endpoint', self::DEFAULT_ENDPOINT, 'ai_engine');
        SystemSetting::set('ai_platform_model', self::DEFAULT_MODEL, 'ai_engine');
        SystemSetting::set('ai_platform_rotation_strategy', self::DEFAULT_STRATEGY, 'ai_engine');
        SystemSetting::set('ai_platform_status', 'connected', 'ai_engine');

        return $keysPool;
    }

    /**
     * Get aggregated provider statistics (9Router visual topology metrics).
     *
     * @return array<string, array<string, mixed>>
     */
    public function getProviderStats(): array
    {
        $keysPool = $this->getRawKeysPool();
        $stats = [];

        foreach (self::PROVIDERS_META as $providerId => $meta) {
            $matchingKeys = array_values(array_filter($keysPool, fn($k) => ($k['provider'] ?? '') === $providerId));
            $activeKeys = array_values(array_filter($matchingKeys, fn($k) => ($k['is_active'] ?? true) === true));
            $totalRequests = array_sum(array_column($matchingKeys, 'total_requests'));
            $totalErrors = array_sum(array_column($matchingKeys, 'total_errors'));

            $latencies = array_filter(array_column($matchingKeys, 'latency_ms'), fn($l) => (int) $l > 0);
            $avgLatency = count($latencies) > 0 ? (int) round(array_sum($latencies) / count($latencies)) : 0;

            $isConnected = count($activeKeys) > 0;
            $hasErrors = count(array_filter($activeKeys, fn($k) => ($k['status'] ?? '') === 'error')) > 0;

            $statusText = match (true) {
                ! $isConnected => 'not_connected',
                $hasErrors => 'degraded',
                default => 'healthy',
            };

            $stats[$providerId] = [
                'id' => $providerId,
                'name' => $meta['name'],
                'short_name' => $meta['short_name'],
                'badge' => $meta['badge'],
                'badge_color' => $meta['badge_color'],
                'default_endpoint' => $meta['default_endpoint'],
                'default_model' => $meta['default_model'],
                'description' => $meta['description'],
                'format_notes' => $meta['format_notes'],
                'total_keys' => count($matchingKeys),
                'active_keys' => count($activeKeys),
                'total_requests' => (int) $totalRequests,
                'total_errors' => (int) $totalErrors,
                'avg_latency_ms' => $avgLatency,
                'is_connected' => $isConnected,
                'status' => $statusText,
            ];
        }

        return $stats;
    }

    /**
     * Get safe platform AI engine configuration for UI display (Zero plaintext key exposure).
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $provider = SystemSetting::get('ai_platform_provider', self::DEFAULT_PROVIDER);
        $endpoint = SystemSetting::get('ai_platform_endpoint', self::DEFAULT_ENDPOINT);
        $model = SystemSetting::get('ai_platform_model', self::DEFAULT_MODEL);
        $strategy = SystemSetting::get('ai_platform_rotation_strategy', self::DEFAULT_STRATEGY);
        $status = SystemSetting::get('ai_platform_status', 'connected');
        $latency = (int) SystemSetting::get('ai_platform_latency_ms', '0');
        $testedAt = SystemSetting::get('ai_platform_tested_at');
        $lastError = SystemSetting::get('ai_platform_last_error');

        $keysPool = $this->getRawKeysPool();
        $availableModels = $this->getAvailableModels();
        $providerStats = $this->getProviderStats();

        // Masked keys representation for UI safety (No plaintext secrets)
        $maskedKeys = array_map(function ($k) {
            $rawKey = (string) ($k['key'] ?? '');
            $masked = $this->maskApiKey($rawKey);
            $providerKey = (string) ($k['provider'] ?? self::DEFAULT_PROVIDER);
            $meta = self::PROVIDERS_META[$providerKey] ?? self::PROVIDERS_META[self::DEFAULT_PROVIDER];

            return [
                'id' => (string) ($k['id'] ?? Str::uuid()->toString()),
                'name' => (string) ($k['name'] ?? 'API Key'),
                'provider' => $providerKey,
                'provider_meta' => $meta,
                'purpose' => (string) ($k['purpose'] ?? 'chat'),
                'masked_key' => $masked,
                'endpoint' => (string) ($k['endpoint'] ?? ($meta['default_endpoint'] ?? '')),
                'model' => (string) ($k['model'] ?? ($meta['default_model'] ?? '')),
                'priority' => (int) ($k['priority'] ?? 1),
                'weight' => (int) ($k['weight'] ?? 1),
                'is_active' => (bool) ($k['is_active'] ?? true),
                'status' => (string) ($k['status'] ?? 'healthy'),
                'latency_ms' => (int) ($k['latency_ms'] ?? 0),
                'total_requests' => (int) ($k['total_requests'] ?? 0),
                'total_errors' => (int) ($k['total_errors'] ?? 0),
                'last_used_at' => $k['last_used_at'] ?? null,
                'last_tested_at' => $k['last_tested_at'] ?? null,
                'last_error' => $k['last_error'] ?? null,
            ];
        }, $keysPool);

        $activeKeysCount = count(array_filter($keysPool, fn($k) => ($k['is_active'] ?? true) === true));
        $chatKeysCount = count(array_filter($keysPool, fn($k) => ($k['is_active'] ?? true) && in_array($k['purpose'] ?? 'chat', ['chat', 'all'], true)));
        $imageKeysCount = count(array_filter($keysPool, fn($k) => ($k['is_active'] ?? true) && in_array($k['purpose'] ?? 'chat', ['image', 'all'], true)));
        $connectedProvidersCount = count(array_filter($providerStats, fn($p) => $p['is_connected']));
        $totalRoutedRequests = array_sum(array_column($keysPool, 'total_requests'));
        $totalRoutedErrors = array_sum(array_column($keysPool, 'total_errors'));

        return [
            'provider' => $provider,
            'endpoint' => $endpoint,
            'model' => $model,
            'rotation_strategy' => $strategy,
            'status' => $status,
            'latency_ms' => $latency,
            'tested_at' => $testedAt,
            'last_error' => $lastError,
            'masked_keys' => $maskedKeys,
            'total_keys_count' => count($keysPool),
            'active_keys_count' => $activeKeysCount,
            'chat_keys_count' => $chatKeysCount,
            'image_keys_count' => $imageKeysCount,
            'available_models' => $availableModels,
            'providers_meta' => self::PROVIDERS_META,
            'providers_stats' => $providerStats,
            'connected_providers_count' => $connectedProvidersCount,
            'total_routed_requests' => $totalRoutedRequests,
            'total_routed_errors' => $totalRoutedErrors,
        ];
    }

    /**
     * Update platform engine basic settings.
     *
     * @param array<string, mixed> $data
     */
    public function updateConfig(array $data): void
    {
        if (isset($data['provider'])) {
            SystemSetting::set('ai_platform_provider', trim((string) $data['provider']), 'ai_engine');
        }
        if (isset($data['endpoint'])) {
            SystemSetting::set('ai_platform_endpoint', rtrim(trim((string) $data['endpoint']), '/'), 'ai_engine');
        }
        if (isset($data['model'])) {
            SystemSetting::set('ai_platform_model', trim((string) $data['model']), 'ai_engine');
        }
        if (isset($data['rotation_strategy'])) {
            SystemSetting::set('ai_platform_rotation_strategy', trim((string) $data['rotation_strategy']), 'ai_engine');
        }
    }

    /**
     * Add a new API key to the platform pool with full 9Router provider attribution.
     *
     * @return array{id: string, name: string, provider: string, masked_key: string}
     */
    public function addKey(
        string $name,
        string $rawKey,
        ?string $provider = null,
        ?string $endpoint = null,
        ?string $model = null,
        int $priority = 1,
        int $weight = 1,
        ?string $purpose = null
    ): array {
        $sanitizedKey = trim($rawKey, " \t\n\r\0\x0B\"'");
        if (str_starts_with(strtolower($sanitizedKey), 'bearer ')) {
            $sanitizedKey = trim(substr($sanitizedKey, 7));
        }

        $keysPool = $this->getRawKeysPool();

        $inferredProvider = $provider && isset(self::PROVIDERS_META[$provider])
            ? $provider
            : $this->inferProviderFromKeyOrName($sanitizedKey, $name);

        $meta = self::PROVIDERS_META[$inferredProvider] ?? self::PROVIDERS_META[self::DEFAULT_PROVIDER];

        // Infer purpose if not specified: 'chat' | 'image' | 'all'
        if (empty($purpose) || ! in_array($purpose, ['chat', 'image', 'all'], true)) {
            $provLower = strtolower($inferredProvider);
            $modLower = strtolower((string) $model);
            if (in_array($provLower, ['ideogram', 'stability'], true) || str_contains($modLower, 'dall-e') || str_contains($modLower, 'flux') || str_contains($modLower, 'sd') || str_contains($modLower, 'image')) {
                $purpose = 'image';
            } else {
                $purpose = 'chat';
            }
        }

        $keyId = 'key_' . Str::random(10);
        $newEntry = [
            'id' => $keyId,
            'name' => trim($name) ?: ('Key #' . (count($keysPool) + 1)),
            'provider' => $inferredProvider,
            'purpose' => $purpose,
            'key' => $sanitizedKey,
            'endpoint' => ! empty($endpoint) ? rtrim(trim($endpoint), '/') : ($meta['default_endpoint'] ?? ''),
            'model' => ! empty($model) ? trim($model) : ($meta['default_model'] ?? ''),
            'priority' => max(1, min(3, $priority)),
            'weight' => max(1, min(10, $weight)),
            'is_active' => true,
            'status' => 'healthy',
            'latency_ms' => 0,
            'total_requests' => 0,
            'total_errors' => 0,
            'last_used_at' => null,
            'last_tested_at' => null,
            'last_error' => null,
        ];

        $keysPool[] = $newEntry;
        SystemSetting::set('ai_platform_keys_pool', json_encode($keysPool), 'ai_engine', true);

        return [
            'id' => $keyId,
            'name' => $newEntry['name'],
            'provider' => $inferredProvider,
            'purpose' => $purpose,
            'masked_key' => $this->maskApiKey($sanitizedKey),
        ];
    }

    /**
     * Update an existing API key in the pool.
     *
     * @param array<string, mixed> $data
     */
    public function updateKey(string $keyId, array $data): bool
    {
        $keysPool = $this->getRawKeysPool();
        $found = false;

        foreach ($keysPool as &$item) {
            if (($item['id'] ?? '') === $keyId) {
                if (isset($data['name']) && trim((string) $data['name']) !== '') {
                    $item['name'] = trim((string) $data['name']);
                }
                if (isset($data['provider']) && isset(self::PROVIDERS_META[$data['provider']])) {
                    $item['provider'] = (string) $data['provider'];
                }
                if (! empty($data['key'])) {
                    $sanitized = trim((string) $data['key'], " \t\n\r\0\x0B\"'");
                    if (str_starts_with(strtolower($sanitized), 'bearer ')) {
                        $sanitized = trim(substr($sanitized, 7));
                    }
                    if ($sanitized !== '') {
                        $item['key'] = $sanitized;
                    }
                }
                if (isset($data['endpoint'])) {
                    $item['endpoint'] = rtrim(trim((string) $data['endpoint']), '/');
                }
                if (isset($data['model'])) {
                    $item['model'] = trim((string) $data['model']);
                }
                if (isset($data['priority'])) {
                    $item['priority'] = max(1, min(3, (int) $data['priority']));
                }
                if (isset($data['weight'])) {
                    $item['weight'] = max(1, min(10, (int) $data['weight']));
                }
                if (isset($data['purpose']) && in_array($data['purpose'], ['chat', 'image', 'all'], true)) {
                    $item['purpose'] = $data['purpose'];
                }
                if (isset($data['is_active'])) {
                    $item['is_active'] = (bool) $data['is_active'];
                }

                $found = true;
                break;
            }
        }
        unset($item);

        if ($found) {
            SystemSetting::set('ai_platform_keys_pool', json_encode($keysPool), 'ai_engine', true);
        }

        return $found;
    }

    /**
     * Toggle active state of a key in the pool.
     */
    public function toggleKey(string $keyId, ?bool $isActive = null): bool
    {
        $keysPool = $this->getRawKeysPool();
        $found = false;

        foreach ($keysPool as &$item) {
            if (($item['id'] ?? '') === $keyId) {
                $item['is_active'] = $isActive !== null ? $isActive : ! ($item['is_active'] ?? true);
                $found = true;
                break;
            }
        }
        unset($item);

        if ($found) {
            SystemSetting::set('ai_platform_keys_pool', json_encode($keysPool), 'ai_engine', true);
        }

        return $found;
    }

    /**
     * Delete a key from the pool.
     */
    public function deleteKey(string $keyId): bool
    {
        $keysPool = $this->getRawKeysPool();
        $initialCount = count($keysPool);

        $keysPool = array_values(array_filter($keysPool, fn($k) => ($k['id'] ?? '') !== $keyId));

        if (count($keysPool) !== $initialCount) {
            SystemSetting::set('ai_platform_keys_pool', json_encode($keysPool), 'ai_engine', true);
            return true;
        }

        return false;
    }

    /**
     * Test a single API Key directly (Live Ping).
     *
     * @return array{success: bool, message: string, latency_ms: int, status: string, key_id: string}
     */
    public function testSingleKey(string $keyId): array
    {
        $keysPool = $this->getRawKeysPool();
        $targetKey = null;
        $targetIndex = null;

        foreach ($keysPool as $idx => $k) {
            if (($k['id'] ?? '') === $keyId) {
                $targetKey = $k;
                $targetIndex = $idx;
                break;
            }
        }

        if (! $targetKey || empty($targetKey['key'])) {
            return [
                'success' => false,
                'message' => 'API Key tidak ditemukan dalam pool atau bernilai kosong.',
                'latency_ms' => 0,
                'status' => 'error',
                'key_id' => $keyId,
            ];
        }

        $providerName = (string) ($targetKey['provider'] ?? self::DEFAULT_PROVIDER);
        $rawKey = (string) $targetKey['key'];
        $endpoint = (string) ($targetKey['endpoint'] ?? '');
        $model = (string) ($targetKey['model'] ?? '');

        $startTime = microtime(true);
        try {
            if ($providerName === 'openai_compatible') {
                $effectiveEndpoint = $endpoint ?: SystemSetting::get('ai_platform_endpoint', self::DEFAULT_ENDPOINT);
                $effectiveModel = $model ?: SystemSetting::get('ai_platform_model', self::DEFAULT_MODEL);
                $providerInstance = new OpenAiCompatibleProvider($effectiveEndpoint);
                $result = $providerInstance->testConnection($rawKey, $effectiveModel, $effectiveEndpoint);
            } else {
                $manager = $this->getManager();
                $providerInstance = $manager->getProvider($providerName);
                $meta = self::PROVIDERS_META[$providerName] ?? [];
                $effectiveModel = $model ?: ($meta['default_model'] ?? 'gpt-4o-mini');
                $result = $providerInstance->testConnection($rawKey, $effectiveModel);
            }

            $latency = isset($result['latency_ms']) ? (int) $result['latency_ms'] : (int) round((microtime(true) - $startTime) * 1000);
            $isSuccess = (bool) ($result['success'] ?? false);

            // Update key in pool
            $keysPool[$targetIndex]['status'] = $isSuccess ? 'healthy' : 'error';
            $keysPool[$targetIndex]['latency_ms'] = $latency;
            $keysPool[$targetIndex]['last_tested_at'] = now()->toDateTimeString();
            $keysPool[$targetIndex]['last_error'] = $isSuccess ? null : ($result['message'] ?? 'Error');

            SystemSetting::set('ai_platform_keys_pool', json_encode($keysPool), 'ai_engine', true);

            return [
                'success' => $isSuccess,
                'message' => $result['message'] ?? ($isSuccess ? 'Koneksi berhasil diverifikasi!' : 'Koneksi gagal.'),
                'latency_ms' => $latency,
                'status' => $keysPool[$targetIndex]['status'],
                'key_id' => $keyId,
            ];
        } catch (Throwable $e) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            $keysPool[$targetIndex]['status'] = 'error';
            $keysPool[$targetIndex]['latency_ms'] = $latency;
            $keysPool[$targetIndex]['last_tested_at'] = now()->toDateTimeString();
            $keysPool[$targetIndex]['last_error'] = $e->getMessage();

            SystemSetting::set('ai_platform_keys_pool', json_encode($keysPool), 'ai_engine', true);

            return [
                'success' => false,
                'message' => 'Gagal menguji API Key: ' . $e->getMessage(),
                'latency_ms' => $latency,
                'status' => 'error',
                'key_id' => $keyId,
            ];
        }
    }

    /**
     * Get available models dynamically from storage, with fallback to default tunnel seeds.
     *
     * @return array<int, array{id: string, name: string, tier: string}>
     */
    public function getAvailableModels(): array
    {
        $modelsRaw = SystemSetting::get('ai_platform_available_models');
        if ($modelsRaw !== null && $modelsRaw !== '') {
            $decodedModels = json_decode($modelsRaw, true);
            if (is_array($decodedModels) && ! empty($decodedModels)) {
                return $decodedModels;
            }
        }

        $seeds = OpenAiCompatibleProvider::getDefaultSeedModels();
        SystemSetting::set('ai_platform_available_models', json_encode($seeds), 'ai_engine');

        return $seeds;
    }

    /**
     * Add or update a model in the dynamic models pool ("bongkar pasang").
     *
     * @return array<int, array{id: string, name: string, tier: string}>
     */
    public function addCustomModel(string $modelId, ?string $name = null, string $tier = 'balanced'): array
    {
        $cleanId = trim($modelId);
        if ($cleanId === '') {
            throw new \InvalidArgumentException('Model ID tidak boleh kosong.');
        }

        $validTiers = ['reasoning', 'balanced', 'fast'];
        $cleanTier = in_array(strtolower($tier), $validTiers, true) ? strtolower($tier) : 'balanced';
        $cleanName = trim((string) $name);
        if ($cleanName === '') {
            $cleanName = $cleanId;
        }

        $models = $this->getAvailableModels();
        $found = false;

        foreach ($models as &$m) {
            if (($m['id'] ?? '') === $cleanId) {
                $m['name'] = $cleanName;
                $m['tier'] = $cleanTier;
                $found = true;
                break;
            }
        }
        unset($m);

        if (! $found) {
            $models[] = [
                'id' => $cleanId,
                'name' => $cleanName,
                'tier' => $cleanTier,
            ];
        }

        SystemSetting::set('ai_platform_available_models', json_encode($models), 'ai_engine');

        return $models;
    }

    /**
     * Delete a model from the dynamic models pool ("bongkar pasang").
     *
     * @return array<int, array{id: string, name: string, tier: string}>
     */
    public function deleteCustomModel(string $modelId): array
    {
        $cleanId = trim($modelId);
        $models = $this->getAvailableModels();

        $models = array_values(array_filter($models, fn($m) => ($m['id'] ?? '') !== $cleanId));

        if (empty($models)) {
            $models = OpenAiCompatibleProvider::getDefaultSeedModels();
        }

        SystemSetting::set('ai_platform_available_models', json_encode($models), 'ai_engine');

        // If the deleted model was currently active, switch to first available model
        $currentActiveModel = SystemSetting::get('ai_platform_model', self::DEFAULT_MODEL);
        if ($currentActiveModel === $cleanId) {
            $newActive = $models[0]['id'] ?? self::DEFAULT_MODEL;
            SystemSetting::set('ai_platform_model', $newActive, 'ai_engine');
        }

        return $models;
    }

    /**
     * Reset models pool to the 13 baseline default seeds.
     *
     * @return array<int, array{id: string, name: string, tier: string}>
     */
    public function resetModelsToDefault(): array
    {
        $seeds = OpenAiCompatibleProvider::getDefaultSeedModels();
        SystemSetting::set('ai_platform_available_models', json_encode($seeds), 'ai_engine');

        return $seeds;
    }

    /**
     * Resolve the next active API key from the pool using 9Router Smart Routing (Round-Robin or 3-Tier Priority Failover).
     *
     * @return array{id: string, key: string, name: string, provider: string, endpoint: string, model: string}|null
     */
    public function resolveNextKey(?string $preferredProvider = null, ?string $preferredModel = null): ?array
    {
        $keysPool = $this->getRawKeysPool();

        // Filter active keys strictly for Chatbot / Text LLM (exclude keys designated strictly for image generation)
        $activeKeys = array_values(array_filter($keysPool, function ($k) {
            $isActive = ($k['is_active'] ?? true) === true && ! empty($k['key']);
            $purpose = (string) ($k['purpose'] ?? 'chat');
            return $isActive && $purpose !== 'image';
        }));

        if (empty($activeKeys)) {
            return null;
        }

        // If preferred provider requested, filter by provider if available
        if ($preferredProvider !== null && $preferredProvider !== '') {
            $matchingProviderKeys = array_values(array_filter($activeKeys, fn($k) => ($k['provider'] ?? '') === $preferredProvider));
            if (! empty($matchingProviderKeys)) {
                $activeKeys = $matchingProviderKeys;
            }
        }

        $strategy = SystemSetting::get('ai_platform_rotation_strategy', self::DEFAULT_STRATEGY);

        if ($strategy === 'failover') {
            // Sort by priority ASC (Priority 1 first), then prefer healthy keys
            usort($activeKeys, function ($a, $b) {
                $pA = (int) ($a['priority'] ?? 1);
                $pB = (int) ($b['priority'] ?? 1);
                if ($pA !== $pB) {
                    return $pA <=> $pB;
                }
                $statA = ($a['status'] ?? 'healthy') === 'healthy' ? 0 : 1;
                $statB = ($b['status'] ?? 'healthy') === 'healthy' ? 0 : 1;
                return $statA <=> $statB;
            });

            // Group into lowest priority tier available
            $lowestPriority = (int) ($activeKeys[0]['priority'] ?? 1);
            $tierKeys = array_values(array_filter($activeKeys, fn($k) => ((int) ($k['priority'] ?? 1)) === $lowestPriority));

            // Prefer healthy keys in this tier
            $healthyInTier = array_values(array_filter($tierKeys, fn($k) => ($k['status'] ?? 'healthy') === 'healthy'));
            $selectionPool = ! empty($healthyInTier) ? $healthyInTier : $tierKeys;

            $counter = (int) Cache::increment('ai_platform_key_failover_index');
            $selectedIndex = $counter % count($selectionPool);
            $selected = $selectionPool[$selectedIndex];
        } else {
            // Round-Robin across all active keys
            $counter = (int) Cache::increment('ai_platform_key_rr_index');
            $selectedIndex = $counter % count($activeKeys);
            $selected = $activeKeys[$selectedIndex];
        }

        $providerName = (string) ($selected['provider'] ?? self::DEFAULT_PROVIDER);
        $meta = self::PROVIDERS_META[$providerName] ?? self::PROVIDERS_META[self::DEFAULT_PROVIDER];

        return [
            'id' => (string) ($selected['id'] ?? 'key_' . $selectedIndex),
            'name' => (string) ($selected['name'] ?? 'Primary Key'),
            'provider' => $providerName,
            'key' => (string) $selected['key'],
            'endpoint' => (string) (! empty($selected['endpoint']) ? $selected['endpoint'] : ($meta['default_endpoint'] ?? SystemSetting::get('ai_platform_endpoint', self::DEFAULT_ENDPOINT))),
            'model' => (string) (! empty($selected['model']) ? $selected['model'] : ($meta['default_model'] ?? SystemSetting::get('ai_platform_model', self::DEFAULT_MODEL))),
        ];
    }

    /**
     * Dapatkan seluruh kunci API yang aktif dan khusus memiliki kapabilitas untuk Generasi Gambar (DALL-E, Ideogram, Flux, SD, dsb).
     * Kunci yang khusus diperuntukkan bagi chatbot ('chat') TIDAK PERNAH dikembalikan di sini.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActiveImageKeys(?string $preferredProvider = null): array
    {
        $keysPool = $this->getRawKeysPool();

        // Filter active keys strictly for AI Image Generation
        $activeKeys = array_values(array_filter($keysPool, function ($k) {
            $isActive = ($k['is_active'] ?? true) === true && ! empty($k['key']);
            if (! $isActive) {
                return false;
            }

            $purpose = (string) ($k['purpose'] ?? '');

            // Kunci yang secara eksplisit khusus 'chat' TIDAK BOLEH digunakan image generation
            if ($purpose === 'chat') {
                return false;
            }

            // Kunci yang secara eksplisit 'image' atau 'all' diizinkan
            if ($purpose === 'image' || $purpose === 'all') {
                return true;
            }

            // Fallback untuk data yang belum memiliki kolom purpose
            $p = strtolower((string) ($k['provider'] ?? ''));
            $m = strtolower((string) ($k['model'] ?? ''));

            return in_array($p, ['ideogram', 'stability'], true)
                || str_contains($m, 'dall-e')
                || str_contains($m, 'image')
                || str_contains($m, 'flux')
                || str_contains($m, 'sd');
        }));

        if (empty($activeKeys)) {
            return [];
        }

        if ($preferredProvider !== null && $preferredProvider !== '') {
            $matched = array_values(array_filter($activeKeys, fn($k) => strtolower((string) ($k['provider'] ?? '')) === strtolower($preferredProvider)));
            if (! empty($matched)) {
                return $matched;
            }
        }

        return $activeKeys;
    }

    /**
     * Resolusi kunci API generasi gambar berikutnya dari pool database (Round-Robin concurrency & Failover).
     *
     * @return array{id: string, key: string, name: string, provider: string, endpoint: string, model: string}|null
     */
    public function resolveNextImageKey(?string $preferredProvider = null): ?array
    {
        $keys = $this->getActiveImageKeys($preferredProvider);

        if (empty($keys)) {
            return null;
        }

        $strategy = SystemSetting::get('ai_platform_rotation_strategy', self::DEFAULT_STRATEGY);

        if ($strategy === 'failover') {
            usort($keys, function ($a, $b) {
                $pA = (int) ($a['priority'] ?? 1);
                $pB = (int) ($b['priority'] ?? 1);
                if ($pA !== $pB) {
                    return $pA <=> $pB;
                }
                $statA = ($a['status'] ?? 'healthy') === 'healthy' ? 0 : 1;
                $statB = ($b['status'] ?? 'healthy') === 'healthy' ? 0 : 1;

                return $statA <=> $statB;
            });

            $lowestPriority = (int) ($keys[0]['priority'] ?? 1);
            $tierKeys = array_values(array_filter($keys, fn($k) => ((int) ($k['priority'] ?? 1)) === $lowestPriority));
            $healthyInTier = array_values(array_filter($tierKeys, fn($k) => ($k['status'] ?? 'healthy') === 'healthy'));
            $selectionPool = ! empty($healthyInTier) ? $healthyInTier : $tierKeys;

            $counter = (int) Cache::increment('ai_platform_image_key_failover_index');
            $selectedIndex = $counter % count($selectionPool);
            $selected = $selectionPool[$selectedIndex];
        } else {
            // Round-robin balancing antar multiple keys untuk proses gambar konkuren
            $counter = (int) Cache::increment('ai_platform_image_key_rr_index');
            $selectedIndex = $counter % count($keys);
            $selected = $keys[$selectedIndex];
        }

        $providerName = (string) ($selected['provider'] ?? self::DEFAULT_PROVIDER);
        $meta = self::PROVIDERS_META[$providerName] ?? self::PROVIDERS_META[self::DEFAULT_PROVIDER];

        return [
            'id' => (string) ($selected['id'] ?? 'key_' . $selectedIndex),
            'name' => (string) ($selected['name'] ?? 'Image Generation Key'),
            'provider' => $providerName,
            'key' => (string) $selected['key'],
            'endpoint' => (string) (! empty($selected['endpoint']) ? $selected['endpoint'] : ($meta['default_endpoint'] ?? 'https://api.openai.com/v1')),
            'model' => (string) (! empty($selected['model']) ? $selected['model'] : 'dall-e-3'),
        ];
    }

    /**
     * Record execution metrics for a specific key in the pool.
     */
    public function recordKeyUsage(string $keyId, bool $isSuccess, ?string $error = null): void
    {
        $keysPool = $this->getRawKeysPool();

        foreach ($keysPool as &$item) {
            if (($item['id'] ?? '') === $keyId) {
                $item['total_requests'] = ((int) ($item['total_requests'] ?? 0)) + 1;
                $item['last_used_at'] = now()->toDateTimeString();

                if (! $isSuccess) {
                    $item['total_errors'] = ((int) ($item['total_errors'] ?? 0)) + 1;
                    $item['status'] = 'degraded';
                    $item['last_error'] = $error ? Str::limit($error, 200) : 'Error';
                } else {
                    $item['status'] = 'healthy';
                    $item['last_error'] = null;
                }
                break;
            }
        }
        unset($item);

        SystemSetting::set('ai_platform_keys_pool', json_encode($keysPool), 'ai_engine', true);
    }

    /**
     * Test connection to the platform engine (Global Live Ping).
     *
     * @return array{success: bool, message: string, latency_ms: int, available_models?: array}
     */
    public function testConnection(?string $provider = null, ?string $endpoint = null, ?string $apiKey = null, ?string $model = null): array
    {
        $config = $this->getConfig();
        $targetProvider = $provider ?: $config['provider'];
        $targetEndpoint = $endpoint ?: $config['endpoint'];
        $targetModel = $model ?: $config['model'];

        // If no explicit api key, resolve from pool
        $targetKey = $apiKey;
        if (empty($targetKey)) {
            $resolved = $this->resolveNextKey();
            $targetKey = $resolved['key'] ?? '';
            if (! empty($resolved['provider']) && empty($provider)) {
                $targetProvider = $resolved['provider'];
            }
            if (! empty($resolved['endpoint']) && empty($endpoint)) {
                $targetEndpoint = $resolved['endpoint'];
            }
            if (! empty($resolved['model']) && empty($model)) {
                $targetModel = $resolved['model'];
            }
        }

        if (empty($targetKey)) {
            return [
                'success' => false,
                'message' => 'Tidak ada API Key aktif yang tersedia di pool. Tambahkan API Key terlebih dahulu.',
                'latency_ms' => 0,
            ];
        }

        if ($targetProvider === 'openai_compatible') {
            $providerInstance = new OpenAiCompatibleProvider($targetEndpoint);
            $result = $providerInstance->testConnection($targetKey, $targetModel, $targetEndpoint);
        } else {
            $manager = $this->getManager();
            $providerInstance = $manager->getProvider($targetProvider);
            $result = $providerInstance->testConnection($targetKey, $targetModel);
        }

        // Persist test result
        SystemSetting::set('ai_platform_status', $result['success'] ? 'connected' : 'error', 'ai_engine');
        SystemSetting::set('ai_platform_tested_at', now()->format('Y-m-d H:i:s'), 'ai_engine');
        if (isset($result['latency_ms'])) {
            SystemSetting::set('ai_platform_latency_ms', (string) $result['latency_ms'], 'ai_engine');
        }
        if (! $result['success']) {
            SystemSetting::set('ai_platform_last_error', $result['message'], 'ai_engine');
        } else {
            SystemSetting::set('ai_platform_last_error', '', 'ai_engine');
            if (! empty($result['available_models'])) {
                SystemSetting::set('ai_platform_available_models', json_encode($result['available_models']), 'ai_engine');
            }
        }

        return $result;
    }

    /**
     * Detect models from endpoint dynamically.
     *
     * @return array<int, array{id: string, name: string, tier: string}>
     */
    public function detectModels(?string $provider = null, ?string $endpoint = null, ?string $apiKey = null): array
    {
        $config = $this->getConfig();
        $targetProvider = $provider ?: $config['provider'];
        $targetEndpoint = $endpoint ?: $config['endpoint'];

        $targetKey = $apiKey;
        if (empty($targetKey)) {
            $resolved = $this->resolveNextKey();
            $targetKey = $resolved['key'] ?? '';
            if (! empty($resolved['provider']) && empty($provider)) {
                $targetProvider = $resolved['provider'];
            }
            if (! empty($resolved['endpoint']) && empty($endpoint)) {
                $targetEndpoint = $resolved['endpoint'];
            }
        }

        if (empty($targetKey)) {
            return $config['available_models'];
        }

        if ($targetProvider === 'openai_compatible') {
            $providerInstance = new OpenAiCompatibleProvider($targetEndpoint);
            $models = $providerInstance->fetchAvailableModels($targetKey, $targetEndpoint);
        } else {
            $manager = $this->getManager();
            $models = $manager->discoverModels($targetProvider, $targetKey);
        }

        if (! empty($models)) {
            SystemSetting::set('ai_platform_available_models', json_encode($models), 'ai_engine');
        }

        return $models;
    }

    /**
     * Mask API key for UI display (e.g. sk-5980...97ac1590).
     */
    public function maskApiKey(string $key): string
    {
        $trimmed = trim($key);
        $len = strlen($trimmed);
        if ($len <= 8) {
            return '••••••••';
        }
        if ($len <= 16) {
            return substr($trimmed, 0, 4) . '••••' . substr($trimmed, -4);
        }

        return substr($trimmed, 0, 7) . '••••••••' . substr($trimmed, -8);
    }
}
