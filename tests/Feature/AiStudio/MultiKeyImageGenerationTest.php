<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Domain\Ai\Platform\AiPlatformEngineService;
use App\Domain\Ai\Studio\Adapters\OpenAiImageAdapter;
use App\Models\Business;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MultiKeyImageGenerationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private AiPlatformEngineService $engineService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        $this->user = User::create([
            'name' => 'Admin Merchant',
            'email' => 'admin-ai@cooca.id',
            'password' => Hash::make('secret123'),
        ]);

        $this->business = Business::create([
            'name' => 'Toko Herbal Multi-Key',
            'ai_credits_balance' => 500,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->engineService = new AiPlatformEngineService;
    }

    /**
     * Memverifikasi bahwa kunci API generasi gambar dibaca langsung dari database Setting (ai_platform_keys_pool).
     */
    public function test_image_adapter_resolves_keys_from_setting_pool(): void
    {
        $pool = [
            [
                'id' => 'key_openai_1',
                'name' => 'OpenAI Official Key 1',
                'provider' => 'openai',
                'key' => 'sk-proj-test-key-alpha-1234567890',
                'endpoint' => 'https://api.openai.com/v1',
                'model' => 'dall-e-3',
                'is_active' => true,
                'status' => 'healthy',
                'priority' => 1,
                'weight' => 1,
            ],
        ];

        SystemSetting::set('ai_platform_keys_pool', json_encode($pool), 'ai_engine', true);

        $resolved = $this->engineService->resolveNextImageKey('openai');

        $this->assertNotNull($resolved);
        $this->assertSame('key_openai_1', $resolved['id']);
        $this->assertSame('sk-proj-test-key-alpha-1234567890', $resolved['key']);
        $this->assertSame('https://api.openai.com/v1', $resolved['endpoint']);
    }

    /**
     * Memverifikasi bahwa beberapa kunci API di Setting bekerja bersamaan secara konkuren (Round-Robin load balancing).
     */
    public function test_image_keys_pool_rotates_round_robin_for_concurrent_jobs(): void
    {
        $pool = [
            [
                'id' => 'key_pool_a',
                'name' => 'Akun OpenAI A (Primary)',
                'provider' => 'openai',
                'key' => 'sk-proj-key-a-1111111111',
                'endpoint' => 'https://api.openai.com/v1',
                'model' => 'dall-e-3',
                'is_active' => true,
                'status' => 'healthy',
                'priority' => 1,
                'weight' => 1,
            ],
            [
                'id' => 'key_pool_b',
                'name' => 'Akun OpenAI B (Secondary)',
                'provider' => 'openai',
                'key' => 'sk-proj-key-b-2222222222',
                'endpoint' => 'https://api.openai.com/v1',
                'model' => 'dall-e-3',
                'is_active' => true,
                'status' => 'healthy',
                'priority' => 1,
                'weight' => 1,
            ],
            [
                'id' => 'key_pool_c',
                'name' => 'Tunnel Custom C (Proxy)',
                'provider' => 'openai_compatible',
                'key' => 'sk-tunnel-c-3333333333',
                'endpoint' => 'https://tunnel-proxy.us/v1',
                'model' => 'dall-e-3',
                'is_active' => true,
                'status' => 'healthy',
                'priority' => 1,
                'weight' => 1,
            ],
        ];

        SystemSetting::set('ai_platform_keys_pool', json_encode($pool), 'ai_engine', true);

        // Simulasi 6 pemanggilan antrean konkuren
        $keyA = $this->engineService->resolveNextImageKey();
        $keyB = $this->engineService->resolveNextImageKey();
        $keyC = $this->engineService->resolveNextImageKey();
        $keyD = $this->engineService->resolveNextImageKey(); // Kembali ke A

        $this->assertNotNull($keyA);
        $this->assertNotNull($keyB);
        $this->assertNotNull($keyC);
        $this->assertNotNull($keyD);

        // Setiap pemanggilan bergantian secara adil (Round-Robin)
        $this->assertNotEquals($keyA['id'], $keyB['id']);
        $this->assertNotEquals($keyB['id'], $keyC['id']);
        $this->assertEquals($keyA['id'], $keyD['id']);
    }

    /**
     * Memverifikasi bahwa kunci yang dinonaktifkan di Setting tidak akan digunakan oleh generator.
     */
    public function test_inactive_keys_are_bypassed_by_image_generator(): void
    {
        $pool = [
            [
                'id' => 'key_active',
                'name' => 'Key Aktif',
                'provider' => 'openai',
                'key' => 'sk-proj-active-key',
                'endpoint' => 'https://api.openai.com/v1',
                'is_active' => true,
                'status' => 'healthy',
            ],
            [
                'id' => 'key_disabled',
                'name' => 'Key Nonaktif (Habis Kuota)',
                'provider' => 'openai',
                'key' => 'sk-proj-disabled-key',
                'endpoint' => 'https://api.openai.com/v1',
                'is_active' => false, // Nonaktif!
                'status' => 'error',
            ],
        ];

        SystemSetting::set('ai_platform_keys_pool', json_encode($pool), 'ai_engine', true);

        for ($i = 0; $i < 5; $i++) {
            $resolved = $this->engineService->resolveNextImageKey();
            $this->assertSame('key_active', $resolved['id']);
        }
    }
}
