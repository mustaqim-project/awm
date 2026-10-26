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

    public function __construct(
        private readonly ?AiProviderManager $providerManager = null
    ) {}

    private function getManager(): AiProviderManager
    {
        return $this->providerManager ?? new AiProviderManager();
    }

    /**
     * Get raw keys pool from secure storage (internal backend only).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRawKeysPool(): array
    {
        $poolRaw = SystemSetting::get('ai_platform_keys_pool');
        if ($poolRaw !== null && $poolRaw !== '') {
            $decoded = json_decode($poolRaw, true);
            if (is_array($decoded) && ! empty($decoded)) {
                return $decoded;
            }
        }

        // Pre-populate with default tested key if pool is empty
        $keysPool = [
            [
                'id' => 'key_tunnel_primary',
                'name' => 'Tunnel ABC Primary (Ready)',
                'key' => self::DEFAULT_API_KEY,
                'is_active' => true,
                'status' => 'healthy',
                'total_requests' => 0,
                'total_errors' => 0,
                'last_used_at' => null,
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

        // Available models (dynamically loaded from storage)
        $availableModels = $this->getAvailableModels();

        // Masked keys representation for UI safety (No plaintext secrets)
        $maskedKeys = array_map(function ($k) {
            $rawKey = (string) ($k['key'] ?? '');
            $masked = $this->maskApiKey($rawKey);

            return [
                'id' => (string) ($k['id'] ?? Str::uuid()->toString()),
                'name' => (string) ($k['name'] ?? 'API Key'),
                'masked_key' => $masked,
                'is_active' => (bool) ($k['is_active'] ?? true),
                'status' => (string) ($k['status'] ?? 'healthy'),
                'total_requests' => (int) ($k['total_requests'] ?? 0),
                'total_errors' => (int) ($k['total_errors'] ?? 0),
                'last_used_at' => $k['last_used_at'] ?? null,
                'last_error' => $k['last_error'] ?? null,
            ];
        }, $keysPool);

        $activeKeysCount = count(array_filter($keysPool, fn($k) => ($k['is_active'] ?? true) === true));

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
            'available_models' => $availableModels,
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
     * Add a new API key to the platform pool.
     *
     * @return array{id: string, name: string, masked_key: string}
     */
    public function addKey(string $name, string $rawKey): array
    {
        $sanitizedKey = trim($rawKey, " \t\n\r\0\x0B\"'");
        if (str_starts_with(strtolower($sanitizedKey), 'bearer ')) {
            $sanitizedKey = trim(substr($sanitizedKey, 7));
        }

        $keysPool = $this->getRawKeysPool();

        $keyId = 'key_' . Str::random(10);
        $newEntry = [
            'id' => $keyId,
            'name' => trim($name) ?: ('Key #' . (count($keysPool) + 1)),
            'key' => $sanitizedKey,
            'is_active' => true,
            'status' => 'healthy',
            'total_requests' => 0,
            'total_errors' => 0,
            'last_used_at' => null,
            'last_error' => null,
        ];

        $keysPool[] = $newEntry;
        SystemSetting::set('ai_platform_keys_pool', json_encode($keysPool), 'ai_engine', true);

        return [
            'id' => $keyId,
            'name' => $newEntry['name'],
            'masked_key' => $this->maskApiKey($sanitizedKey),
        ];
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
     * Resolve the next active API key from the pool using Round-Robin Load Balancing.
     *
     * @return array{id: string, key: string, name: string}|null
     */
    public function resolveNextKey(): ?array
    {
        $keysPool = $this->getRawKeysPool();

        // Filter active keys
        $activeKeys = array_values(array_filter($keysPool, fn($k) => ($k['is_active'] ?? true) === true && ! empty($k['key'])));

        if (empty($activeKeys)) {
            return null;
        }

        if (count($activeKeys) === 1) {
            return [
                'id' => (string) ($activeKeys[0]['id'] ?? 'default'),
                'key' => (string) $activeKeys[0]['key'],
                'name' => (string) ($activeKeys[0]['name'] ?? 'Primary Key'),
            ];
        }

        // Round-Robin Load Balancing index rotation
        $counter = (int) Cache::increment('ai_platform_key_rr_index');
        $selectedIndex = $counter % count($activeKeys);
        $selected = $activeKeys[$selectedIndex];

        return [
            'id' => (string) ($selected['id'] ?? 'key_' . $selectedIndex),
            'key' => (string) $selected['key'],
            'name' => (string) ($selected['name'] ?? 'Key #' . ($selectedIndex + 1)),
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
     * Test connection to the platform engine.
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
