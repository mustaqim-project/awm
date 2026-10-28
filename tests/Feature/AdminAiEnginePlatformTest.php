<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Ai\Platform\AiPlatformEngineService;
use App\Domain\Ai\Providers\AiProviderManager;
use App\Models\Admin;
use App\Models\Business;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class AdminAiEnginePlatformTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->admin = Admin::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@cooca.id',
            'password' => 'supersecret123',
            'role' => 'superadmin',
            'is_active' => true,
        ]);
    }

    public function test_ai_tokens_page_renders_with_engine_command_center_and_masked_keys(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.ai-tokens.index'));

        $response->assertOk();
        $response->assertSee('Platform AI Engine & Token Console');
        $response->assertSee('Platform AI Engine &amp; Multi-Key Pool', false);
        $response->assertSee('Multi-Key Pool');
        $response->assertSee('Round-Robin Load Balancing');
        $response->assertSee('https://r4g77gv.abc-tunnel.us/v1');
        $response->assertSee('ag/gemini-3.8-flash-high');

        // Security check: Plaintext API key must never be printed to HTML
        $response->assertDontSee('sk-59807b51bc76063d-ge0agt-97ac1590');
    }

    public function test_update_engine_configuration_persists_settings_cleanly(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.ai-tokens.engine.update'), [
                'provider' => 'openai_compatible',
                'endpoint' => 'https://r4g77gv.abc-tunnel.us/v1',
                'model' => 'ag/gemini-3.8-flash-high',
                'rotation_strategy' => 'round_robin',
            ]);

        $response->assertRedirect(route('admin.ai-tokens.index', ['tab' => 'engine']));
        $response->assertSessionHas('success');

        $this->assertSame('openai_compatible', SystemSetting::get('ai_platform_provider'));
        $this->assertSame('https://r4g77gv.abc-tunnel.us/v1', SystemSetting::get('ai_platform_endpoint'));
        $this->assertSame('ag/gemini-3.8-flash-high', SystemSetting::get('ai_platform_model'));
        $this->assertSame('round_robin', SystemSetting::get('ai_platform_rotation_strategy'));
    }

    public function test_add_key_to_pool_encrypts_and_masks_for_display(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.ai-tokens.engine.keys.add'), [
                'key_name' => 'Tunnel Key Backup #2',
                'api_key' => 'sk-test-secret-backup-key-9988776655',
            ]);

        $response->assertRedirect(route('admin.ai-tokens.index', ['tab' => 'engine']));

        $service = app(AiPlatformEngineService::class);
        $rawPool = $service->getRawKeysPool();
        $config = $service->getConfig();

        $this->assertCount(2, $rawPool);
        $this->assertSame('Tunnel Key Backup #2', $rawPool[1]['name']);
        $this->assertTrue($rawPool[1]['is_active']);
        $this->assertCount(2, $config['masked_keys']);
    }

    public function test_toggle_and_delete_key_management_in_pool(): void
    {
        $service = app(AiPlatformEngineService::class);
        $added = $service->addKey('Secondary Key', 'sk-secondary-test-key-12345');
        $keyId = $added['id'];

        // Toggle to inactive
        $toggleResponse = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.ai-tokens.engine.keys.toggle', $keyId));

        $toggleResponse->assertRedirect();
        $rawAfterToggle = $service->getRawKeysPool();
        $foundKey = collect($rawAfterToggle)->firstWhere('id', $keyId);
        $this->assertFalse($foundKey['is_active']);

        // Delete key
        $deleteResponse = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.ai-tokens.engine.keys.delete', $keyId));

        $deleteResponse->assertRedirect();
        $rawAfterDelete = $service->getRawKeysPool();
        $this->assertNull(collect($rawAfterDelete)->firstWhere('id', $keyId));
    }

    public function test_round_robin_rotation_strategy_distributes_requests_sequentially(): void
    {
        $service = app(AiPlatformEngineService::class);
        $service->addKey('Key Beta', 'sk-key-beta-22222');
        $service->addKey('Key Gamma', 'sk-key-gamma-33333');

        $resolvedFirst = $service->resolveNextKey();
        $resolvedSecond = $service->resolveNextKey();
        $resolvedThird = $service->resolveNextKey();

        $this->assertNotNull($resolvedFirst);
        $this->assertNotNull($resolvedSecond);
        $this->assertNotNull($resolvedThird);

        // All 3 resolved keys in round-robin sequence must be valid and rotate
        $this->assertNotSame($resolvedFirst['key'], $resolvedSecond['key']);
    }

    public function test_test_engine_endpoint_measures_latency_via_http_fake(): void
    {
        Http::fake([
            'https://r4g77gv.abc-tunnel.us/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl-test-123',
                'object' => 'chat.completion',
                'choices' => [
                    ['message' => ['role' => 'assistant', 'content' => 'OK']],
                ],
                'usage' => ['total_tokens' => 20],
            ], 200),
            'https://r4g77gv.abc-tunnel.us/v1/models' => Http::response([
                'data' => [
                    ['id' => 'ag/gemini-3.8-flash-high', 'name' => 'Gemini 3.8 Flash High'],
                    ['id' => 'cx/gpt-6-astra', 'name' => 'GPT-6 Astra'],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.ai-tokens.engine.test'), [
                'provider' => 'openai_compatible',
                'endpoint' => 'https://r4g77gv.abc-tunnel.us/v1',
                'model' => 'ag/gemini-3.8-flash-high',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertArrayHasKey('latency_ms', $response->json());
    }

    public function test_detect_models_fetches_models_from_tunnel_models_endpoint(): void
    {
        Http::fake([
            'https://r4g77gv.abc-tunnel.us/v1/models' => Http::response([
                'data' => [
                    ['id' => 'ag/gemini-3.8-flash-high', 'name' => 'Gemini 3.8 Flash High'],
                    ['id' => 'cx/gpt-6-astra', 'name' => 'GPT-6 Astra'],
                    ['id' => 'cx/gpt-5.6-sol', 'name' => 'GPT-5.6 Sol'],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.ai-tokens.engine.detect-models'), [
                'provider' => 'openai_compatible',
                'endpoint' => 'https://r4g77gv.abc-tunnel.us/v1',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'count' => 3,
        ]);
    }

    public function test_ai_provider_manager_resolves_platform_engine_when_tenant_has_no_byoai(): void
    {
        $manager = app(AiProviderManager::class);

        // When business is null (e.g. system background task / platform query)
        $resolved = $manager->resolveForBusiness(null);

        $this->assertSame('openai_compatible', $resolved['provider']->getProviderName());
        $this->assertSame('ag/gemini-3.8-flash-high', $resolved['model']);
        $this->assertSame('https://r4g77gv.abc-tunnel.us/v1', $resolved['endpoint']);
        $this->assertNotEmpty($resolved['api_key']);
    }

    public function test_superadmin_can_add_custom_model_dynamically_without_hardcoding(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.ai-tokens.engine.models.add'), [
                'model_id' => 'cx/gpt-custom-nebula',
                'model_name' => 'GPT Custom Nebula Flagship',
                'tier' => 'reasoning',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $savedModels = json_decode((string) SystemSetting::get('ai_platform_available_models'), true);
        $this->assertTrue(collect($savedModels)->contains('id', 'cx/gpt-custom-nebula'));
    }

    public function test_superadmin_can_delete_model_from_pool(): void
    {
        // First add model
        $service = new AiPlatformEngineService();
        $service->addCustomModel('test/to-delete-model', 'Delete Me');

        $response = $this->actingAs($this->admin, 'admin')
            ->deleteJson(route('admin.ai-tokens.engine.models.delete', ['modelId' => 'test/to-delete-model']));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $savedModels = json_decode((string) SystemSetting::get('ai_platform_available_models'), true);
        $this->assertFalse(collect($savedModels)->contains('id', 'test/to-delete-model'));
    }

    public function test_superadmin_can_reset_models_to_tunnel_defaults(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.ai-tokens.engine.models.reset-default'));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $savedModels = json_decode((string) SystemSetting::get('ai_platform_available_models'), true);
        $this->assertCount(13, $savedModels);
        $this->assertTrue(collect($savedModels)->contains('id', 'ag/gemini-3.7-flash-high'));
        $this->assertTrue(collect($savedModels)->contains('id', 'cx/gpt-6-astra-xhigh'));
        $this->assertTrue(collect($savedModels)->contains('id', 'cx/gpt-5.4'));
    }

    public function test_chatbot_strictly_ignores_keys_designated_for_image_generation(): void
    {
        $service = app(AiPlatformEngineService::class);
        $pool = [
            [
                'id' => 'key_image_studio',
                'name' => 'API Key Khusus DALL-E 3',
                'provider' => 'openai',
                'purpose' => 'image', // Khusus gambar!
                'model' => 'dall-e-3',
                'key' => 'sk-image-only-secret-key-12345',
                'endpoint' => 'https://api.openai.com/v1',
                'is_active' => true,
                'status' => 'healthy',
                'priority' => 1,
                'weight' => 1,
            ],
            [
                'id' => 'key_chatbot_llm',
                'name' => 'API Key Khusus Chatbot',
                'provider' => 'openai',
                'purpose' => 'chat', // Khusus chatbot!
                'model' => 'gpt-4o',
                'key' => 'sk-chatbot-only-secret-key-67890',
                'endpoint' => 'https://api.openai.com/v1',
                'is_active' => true,
                'status' => 'healthy',
                'priority' => 1,
                'weight' => 1,
            ],
        ];

        SystemSetting::set('ai_platform_keys_pool', json_encode($pool), 'ai_engine', true);

        // Chatbot resolution (resolveNextKey) must strictly pick key_chatbot_llm and ignore key_image_studio
        for ($i = 0; $i < 3; $i++) {
            $resolved = $service->resolveNextKey();
            $this->assertNotNull($resolved);
            $this->assertSame('key_chatbot_llm', $resolved['id']);
            $this->assertSame('sk-chatbot-only-secret-key-67890', $resolved['key']);
        }
    }

    public function test_superadmin_can_add_and_update_key_with_explicit_purpose(): void
    {
        // 1. Add key with purpose 'image'
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.ai-tokens.engine.keys.add'), [
                'key_name' => 'Midjourney / DALL-E Dedicated Key',
                'api_key' => 'sk-dedicated-image-studio-key-12345678',
                'provider' => 'openai',
                'purpose' => 'image',
                'model' => 'dall-e-3',
            ]);

        $response->assertRedirect(route('admin.ai-tokens.index', ['tab' => 'engine']));

        $service = app(AiPlatformEngineService::class);
        $rawPool = $service->getRawKeysPool();
        $addedKey = collect($rawPool)->firstWhere('name', 'Midjourney / DALL-E Dedicated Key');

        $this->assertNotNull($addedKey);
        $this->assertSame('image', $addedKey['purpose']);

        // 2. Update key purpose to 'chat'
        $updateResponse = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.ai-tokens.engine.keys.update', $addedKey['id']), [
                'key_name' => 'Midjourney / DALL-E Dedicated Key (Converted to Chat)',
                'provider' => 'openai',
                'purpose' => 'chat',
                'model' => 'gpt-4o',
            ]);

        $updateResponse->assertRedirect(route('admin.ai-tokens.index', ['tab' => 'engine']));

        $updatedPool = $service->getRawKeysPool();
        $updatedKey = collect($updatedPool)->firstWhere('id', $addedKey['id']);

        $this->assertNotNull($updatedKey);
        $this->assertSame('chat', $updatedKey['purpose']);
        $this->assertSame('gpt-4o', $updatedKey['model']);
    }
}

