<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Domain\Ai\Inspiration\Adapters\CivitaiAdapter;
use App\Domain\Ai\Inspiration\Adapters\CopasPromptAdapter;
use App\Domain\Ai\Inspiration\Contracts\InspirationAdapterInterface;
use App\Domain\Ai\Inspiration\Services\InspirationHarvesterService;
use App\Models\Admin;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationSource;
use App\Models\Business;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class InspirationHarvesterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
    }

    public function test_copasprompt_and_civitai_adapters_implement_interface(): void
    {
        $copas = app(CopasPromptAdapter::class);
        $civitai = app(CivitaiAdapter::class);

        $this->assertInstanceOf(InspirationAdapterInterface::class, $copas);
        $this->assertInstanceOf(InspirationAdapterInterface::class, $civitai);

        $this->assertSame('copasprompt', $copas->getSourceCode());
        $this->assertSame('civitai', $civitai->getSourceCode());
    }

    public function test_copasprompt_returns_valid_item_structure_with_fallback(): void
    {
        Http::fake([
            'copasprompt.id/*' => Http::response('<html><body>Server Error</body></html>', 500),
        ]);

        $adapter = new CopasPromptAdapter();
        $items = $adapter->search('', ['limit' => 5]);

        $this->assertNotEmpty($items);
        $first = $items[0];
        $this->assertArrayHasKey('external_id', $first);
        $this->assertArrayHasKey('title', $first);
        $this->assertArrayHasKey('canonical_url', $first);
        $this->assertArrayHasKey('aspect_ratio', $first);
        $this->assertArrayHasKey('raw_prompt', $first);
        $this->assertTrue($first['allow_commercial_use']);
    }

    public function test_civitai_returns_valid_item_structure_with_fallback(): void
    {
        Http::fake([
            'civitai.com/api/v1/images*' => Http::response(['items' => []], 500),
        ]);

        $adapter = new CivitaiAdapter();
        $items = $adapter->search('', ['limit' => 5]);

        $this->assertNotEmpty($items);
        $first = $items[0];
        $this->assertArrayHasKey('external_id', $first);
        $this->assertArrayHasKey('title', $first);
        $this->assertArrayHasKey('raw_prompt', $first);
        $this->assertArrayHasKey('negative_prompt', $first);
        $this->assertArrayHasKey('ai_model', $first);
        $this->assertTrue($first['allow_commercial_use']);
    }

    public function test_harvester_service_syncs_both_sources_into_database(): void
    {
        /** @var InspirationHarvesterService $harvester */
        $harvester = app(InspirationHarvesterService::class);

        $result = $harvester->harvest('all', 3);

        $this->assertGreaterThan(0, $result['total_synced']);
        $this->assertArrayHasKey('copasprompt', $result['sources']);
        $this->assertArrayHasKey('civitai', $result['sources']);
        $this->assertSame('success', $result['sources']['copasprompt']['status']);
        $this->assertSame('success', $result['sources']['civitai']['status']);

        // Pastikan tabel database terisi dengan analisis visual dan skor tren
        $copasSource = InspirationSource::where('code', 'copasprompt')->first();
        $civitaiSource = InspirationSource::where('code', 'civitai')->first();

        $this->assertNotNull($copasSource);
        $this->assertNotNull($civitaiSource);

        $this->assertTrue(InspirationItem::where('source_id', $copasSource->id)->exists());
        $this->assertTrue(InspirationItem::where('source_id', $civitaiSource->id)->exists());

        $sampleItem = InspirationItem::with(['visualAnalysis', 'trendScore'])->first();
        $this->assertNotNull($sampleItem);
        $this->assertNotNull($sampleItem->visualAnalysis);
        $this->assertNotNull($sampleItem->trendScore);
    }

    public function test_artisan_command_syncs_inspiration_catalog(): void
    {
        $this->artisan('ai-studio:sync-inspiration', ['--source' => 'all', '--limit' => 2])
            ->assertExitCode(0);

        $this->artisan('ai-studio:sync-inspiration', ['--source' => 'copasprompt', '--limit' => 2])
            ->assertExitCode(0);

        $this->artisan('ai-studio:sync-inspiration', ['--source' => 'civitai', '--limit' => 2])
            ->assertExitCode(0);
    }

    public function test_admin_can_trigger_sync_all_and_single_source(): void
    {
        $admin = Admin::create([
            'name' => 'Super Administrator',
            'email' => 'admin-test@cooca.id',
            'password' => Hash::make('secret123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Pastikan sumber terdaftar
        /** @var InspirationHarvesterService $harvester */
        $harvester = app(InspirationHarvesterService::class);
        $harvester->harvest('all', 1);

        $source = InspirationSource::where('code', 'copasprompt')->firstOrFail();

        // Test sync single source endpoint
        $resSingle = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.ai-studio.sources.sync', $source->id), ['limit' => 2]);

        $resSingle->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        // Test sync all sources endpoint
        $resAll = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.ai-studio.sources.sync-all'), ['limit' => 2]);

        $resAll->assertOk()
            ->assertJson([
                'success' => true,
            ]);
    }
}
