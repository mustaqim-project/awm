<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Domain\Ai\Studio\Billing\CreditLedgerService;
use App\Domain\Ai\Studio\Jobs\ProcessImageGenerationJob;
use App\Domain\Ai\Studio\Orchestration\GenerationOrchestrator;
use App\Domain\Ai\Studio\Storage\AssetStorageManager;
use App\Models\AiStudio\BusinessInspirationGeneration;
use App\Models\AiStudio\BusinessInspirationProject;
use App\Models\AiStudio\BusinessInspirationPromptVersion;
use App\Models\Business;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

final class GenerationIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private GenerationOrchestrator $orchestrator;
    private CreditLedgerService $ledgerService;
    private AssetStorageManager $storageManager;
    private Business $business;
    private User $user;
    private Product $product;
    private BusinessInspirationProject $project;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        Storage::fake('public');

        $this->ledgerService = new CreditLedgerService;
        $this->storageManager = new AssetStorageManager;
        $this->orchestrator = new GenerationOrchestrator($this->ledgerService);

        $this->user = User::create([
            'name' => 'Kopi Boss',
            'email' => 'kopiboss@example.com',
            'password' => 'secret123',
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Tubruk Nusantara',
            'ai_credits_balance' => 0,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pouch',
            'code' => 'pouch',
            'symbol' => 'pouch',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Arabika',
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Gayo Wine Process',
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'description' => 'Kemasan stand-up pouch 250gr hitam doff dengan valve aromatik',
            'price' => 85000,
            'stock' => 100,
        ]);

        $this->project = BusinessInspirationProject::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'name' => 'Kampanye Promo Instagram Feed Kopi Gayo',
            'status' => 'draft',
        ]);
    }

    public function test_orchestrator_dispatches_generation_holds_credits_and_completes_job(): void
    {
        // 1. Topup 10 kredit ke saldo bisnis
        $this->ledgerService->topup($this->business, 10, 'topup-seed-10');
        $this->assertSame(10, $this->ledgerService->getBalance($this->business));

        // 2. Jalankan orkestrator secara sinkron untuk pengujian antrean
        $generation = $this->orchestrator->dispatchGeneration(
            business: $this->business,
            project: $this->project,
            options: [
                'user_id' => $this->user->id,
                'sync' => true,
                'aspect_ratio' => '1:1',
                'batch_count' => 1,
                'style_preset' => 'cinematic',
            ]
        );

        // 3. Verifikasi status generasi dan proyek
        $this->assertSame('completed', $generation->status);
        $this->assertNotNull($generation->completed_at);
        $this->assertNotEmpty($generation->provider_job_id);

        $this->project->refresh();
        $this->assertSame('completed', $this->project->status);

        // 4. Verifikasi hold kredit di-settle
        // Biaya 2 kredit untuk DALL-E 3, sisa saldo = 8
        $this->assertSame(8, $this->ledgerService->getBalance($this->business));

        // 5. Verifikasi aset output tersimpan di database dan disk storage
        $this->assertDatabaseHas('business_inspiration_generation_assets', [
            'generation_id' => $generation->id,
            'business_id' => $this->business->id,
            'aspect_ratio' => '1:1',
        ]);

        $asset = $generation->assets()->firstOrFail();
        $this->assertNotEmpty($asset->output_image_url);
        $this->assertNotEmpty($asset->storage_path);
        Storage::disk('public')->assertExists($asset->storage_path);
    }

    public function test_orchestrator_auto_releases_held_credits_on_error(): void
    {
        // 1. Topup 5 kredit
        $this->ledgerService->topup($this->business, 5, 'topup-seed-5');

        // 2. Buat prompt version yang valid
        $promptVersion = BusinessInspirationPromptVersion::create([
            'business_id' => $this->business->id,
            'project_id' => $this->project->id,
            'version_number' => 1,
            'positive_prompt' => 'Test prompt',
        ]);

        // 3. Buat generation dengan hold kredit
        $holdKey = 'hold-fail-simulation-key';
        $this->ledgerService->reserveHold($this->business, 2, $holdKey);
        $this->assertSame(3, $this->ledgerService->getBalance($this->business));

        $generation = BusinessInspirationGeneration::create([
            'business_id' => $this->business->id,
            'project_id' => $this->project->id,
            'prompt_version_id' => $promptVersion->id,
            'user_id' => $this->user->id,
            'provider' => 'invalid_provider',
            'model_name' => 'unsupported_model',
            'status' => 'queued',
            'credit_cost' => 2,
            'idempotency_key' => $holdKey,
        ]);

        // 4. Simulasikan eksekusi gagal di job
        $job = new ProcessImageGenerationJob($generation->id);
        $job->failed(new \RuntimeException('Connection timed out to provider'));

        $generation->refresh();
        $this->assertSame('failed', $generation->status);

        // 5. Saldo wajib kembali menjadi 5 kredit (tidak hilang karena kegagalan teknis)
        $this->assertSame(5, $this->ledgerService->getBalance($this->business));
    }

    public function test_orchestrator_blocks_cross_tenant_project_execution(): void
    {
        $otherBusiness = Business::create(['name' => 'Bisnis Lain']);

        $this->expectException(InvalidArgumentException::class);

        $this->orchestrator->dispatchGeneration(
            business: $otherBusiness,
            project: $this->project,
            options: []
        );
    }

    public function test_asset_storage_manager_blocks_ssrf_malicious_urls(): void
    {
        $maliciousUrls = [
            'http://127.0.0.1/malicious.png',
            'http://localhost:8000/test.png',
            'http://169.254.169.254/latest/meta-data/',
        ];

        foreach ($maliciousUrls as $url) {
            try {
                $reflection = new \ReflectionClass($this->storageManager);
                $method = $reflection->getMethod('validateSafeUrl');
                $method->setAccessible(true);
                $method->invoke($this->storageManager, $url);
                $this->fail("Expected SSRF exception for URL: {$url}");
            } catch (InvalidArgumentException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }
    }
}
