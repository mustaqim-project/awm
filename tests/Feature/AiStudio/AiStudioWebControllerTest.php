<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Domain\Ai\Studio\Billing\CreditLedgerService;
use App\Models\Admin;
use App\Models\AiStudio\BusinessInspirationGeneration;
use App\Models\AiStudio\BusinessInspirationGenerationAsset;
use App\Models\AiStudio\BusinessInspirationProject;
use App\Models\AiStudio\BusinessInspirationPromptVersion;
use App\Models\AiStudio\InspirationCategory;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationSource;
use App\Models\AiStudio\InspirationSourcePolicy;
use App\Models\AiStudio\InspirationTrendScore;
use App\Models\AiStudio\InspirationVisualAnalysis;
use App\Models\Business;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AiStudioWebControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private Admin $admin;
    private Unit $unit;
    private Product $product;
    private InspirationSource $source;
    private InspirationCategory $category;
    private InspirationItem $inspirationItem;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        $this->user = User::create([
            'name' => 'Owner Merchant',
            'email' => 'merchant@cooca.id',
            'password' => Hash::make('secret123'),
        ]);

        $this->business = Business::create([
            'name' => 'Brand Lokal Official',
            'ai_credits_balance' => 10,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->admin = Admin::create([
            'name' => 'Super Administrator',
            'email' => 'admin@cooca.id',
            'password' => Hash::make('secret123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pcs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Serum Vitamin C Glowing',
            'slug' => 'serum-vitamin-c-glowing',
            'selling_price' => 125000,
            'output_unit_id' => $this->unit->id,
            'image_path' => 'products/serum_glow.jpg',
            'is_active' => true,
        ]);

        $this->source = InspirationSource::create([
            'code' => 'openverse',
            'name' => 'Openverse Legal CC0',
            'driver_class' => 'App\\Domain\\Ai\\Inspiration\\OpenverseAdapter',
            'is_active' => true,
        ]);

        InspirationSourcePolicy::create([
            'source_id' => $this->source->id,
            'allow_commercial_use' => true,
            'require_attribution' => true,
            'retention_days' => 90,
        ]);

        $this->category = InspirationCategory::create([
            'name' => 'Skincare & Beauty',
            'slug' => 'skincare-beauty',
            'sort_order' => 1,
            'industry' => 'beauty',
            'is_active' => true,
        ]);

        $this->inspirationItem = InspirationItem::create([
            'source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'external_id' => 'item_beauty_01',
            'title' => 'Minimalist Aesthetic Serum on Marble Podium',
            'thumbnail_url' => 'https://images.example.com/serum_podium.jpg',
            'canonical_url' => 'https://images.example.com/serum_podium.jpg',
            'status' => 'active',
            'industry_target' => 'beauty',
        ]);

        InspirationVisualAnalysis::create([
            'inspiration_item_id' => $this->inspirationItem->id,
            'composition_type' => 'podium',
            'lighting_style' => 'soft diffused morning sunlight',
            'color_palette' => ['#ffffff', '#f5e6d3', '#e0cfb8'],
            'analyzed_attributes' => ['podium' => 'marble', 'minimalist' => true],
            'confidence_score' => 0.95,
        ]);

        InspirationTrendScore::create([
            'inspiration_item_id' => $this->inspirationItem->id,
            'composite_score' => 88.5,
            'views_count' => 1500,
            'saves_count' => 320,
        ]);
    }

    public function test_owner_can_list_ai_studio_projects(): void
    {
        BusinessInspirationProject::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'name' => 'Campaign Glow Summer',
            'target_channel' => 'instagram',
            'aspect_ratio' => '1:1',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('ai-studio.index'));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('stats.total_projects', 1);
        $response->assertJsonPath('stats.credits_balance', 10);
    }

    public function test_owner_can_create_project_with_catalog_product_and_inspiration(): void
    {
        $payload = [
            'name' => 'Peluncuran Serum Glowing 2026',
            'target_channel' => 'instagram',
            'aspect_ratio' => '4:5',
            'style_preset' => 'editorial_clean',
            'custom_notes' => 'Tampilkan pantulan bayangan lembut',
            'product_id' => $this->product->id,
            'inspiration_item_id' => $this->inspirationItem->id,
            'role' => 'hero_product',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('ai-studio.projects.store'), $payload);

        $response->assertCreated();
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('business_inspiration_projects', [
            'business_id' => $this->business->id,
            'name' => 'Peluncuran Serum Glowing 2026',
            'target_platform' => 'instagram',
        ]);

        $this->assertDatabaseHas('business_inspiration_project_assets', [
            'business_id' => $this->business->id,
            'asset_type' => 'original_product',
        ]);

        $this->assertDatabaseHas('business_inspiration_project_assets', [
            'business_id' => $this->business->id,
            'asset_type' => 'reference_style',
        ]);
    }

    public function test_owner_can_compile_prompt_and_receive_quote(): void
    {
        $project = BusinessInspirationProject::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'name' => 'Test Prompt Project',
            'target_platform' => 'instagram',
            'status' => 'draft',
        ]);

        $payload = [
            'project_id' => $project->id,
            'product_id' => $this->product->id,
            'inspiration_item_id' => $this->inspirationItem->id,
            'target_channel' => 'instagram',
            'aspect_ratio' => '1:1',
            'style_preset' => 'editorial_clean',
            'provider' => 'ideogram',
            'model_name' => 'ideogram-v2',
            'batch_count' => 1,
            'enable_upscale' => false,
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('ai-studio.prompt.compile'), $payload);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('quote.credit_cost', 2); // ideogram-v2 costs 2 credits
        $this->assertNotNull($response->json('prompt_version_id'));

        $this->assertDatabaseHas('business_inspiration_prompt_versions', [
            'project_id' => $project->id,
            'version_number' => 1,
        ]);
    }

    public function test_owner_can_dispatch_generation_and_credits_held(): void
    {
        $project = BusinessInspirationProject::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'name' => 'Generasi Serum Summer',
            'target_platform' => 'instagram',
            'status' => 'draft',
        ]);

        $promptVersion = BusinessInspirationPromptVersion::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'version_number' => 1,
            'positive_prompt' => 'Studio product shot of Serum Vitamin C Glowing on marble podium',
            'aspect_ratio' => '1:1',
            'prompt_parameters' => ['estimated_credits' => 2],
        ]);

        $payload = [
            'project_id' => $project->id,
            'prompt_version_id' => $promptVersion->id,
            'provider' => 'ideogram',
            'model_name' => 'ideogram-v2',
            'aspect_ratio' => '1:1',
            'batch_count' => 1,
            'enable_upscale' => false,
            'idempotency_key' => 'dispatch_test_key_01',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('ai-studio.generate'), $payload);

        $response->assertStatus(202);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('generation.status', 'queued');

        // Saldo bisnis terpotong 2 kredit (10 - 2 = 8)
        $this->business->refresh();
        $this->assertEquals(8, $this->business->ai_credits_balance);

        $this->assertDatabaseHas('ai_credit_transactions', [
            'business_id' => $this->business->id,
            'type' => 'usage',
            'amount' => -2,
        ]);
    }

    public function test_owner_can_poll_generation_status(): void
    {
        $project = BusinessInspirationProject::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'name' => 'Polling Project',
            'target_channel' => 'instagram',
            'aspect_ratio' => '1:1',
            'status' => 'generating',
        ]);

        $promptVersion = BusinessInspirationPromptVersion::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'version_number' => 1,
            'positive_prompt' => 'Product shot on podium',
            'aspect_ratio' => '1:1',
            'prompt_parameters' => ['estimated_credits' => 2],
        ]);

        $generation = BusinessInspirationGeneration::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'prompt_version_id' => $promptVersion->id,
            'provider' => 'openai',
            'model_name' => 'dall-e-3',
            'status' => 'succeeded',
            'credit_cost' => 2,
            'idempotency_key' => 'poll_test_01',
        ]);

        BusinessInspirationGenerationAsset::create([
            'business_id' => $this->business->id,
            'generation_id' => $generation->id,
            'output_image_url' => 'https://storage.cooca.id/render1.png',
            'storage_path' => 'businesses/brand/ai-studio/render1.png',
            'width' => 1024,
            'height' => 1024,
            'is_selected_favorite' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('ai-studio.generation.status', [
                'project' => $project->id,
                'generation' => $generation->id,
            ]));

        $response->assertOk();
        $response->assertJsonPath('status', 'succeeded');
        $response->assertJsonCount(1, 'assets');
    }

    public function test_owner_can_export_rendered_asset_to_catalog_product(): void
    {
        $project = BusinessInspirationProject::create([
            'business_id' => $this->business->id,
            'name' => 'Export Project',
            'target_channel' => 'instagram',
            'aspect_ratio' => '1:1',
            'status' => 'succeeded',
        ]);

        $promptVersion = BusinessInspirationPromptVersion::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'version_number' => 1,
            'positive_prompt' => 'Product photo for catalog',
            'aspect_ratio' => '1:1',
        ]);

        $generation = BusinessInspirationGeneration::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'prompt_version_id' => $promptVersion->id,
            'provider' => 'openai',
            'model_name' => 'dall-e-3',
            'status' => 'succeeded',
            'credit_cost' => 2,
            'idempotency_key' => 'export_test_01',
        ]);

        $asset = BusinessInspirationGenerationAsset::create([
            'business_id' => $this->business->id,
            'generation_id' => $generation->id,
            'output_image_url' => 'https://storage.cooca.id/output_photo.jpg',
            'storage_path' => 'businesses/brand/ai-studio/output_photo.jpg',
            'width' => 1024,
            'height' => 1024,
        ]);

        // Ekspor ke produk katalog yang sudah ada
        $response = $this->actingAs($this->user)
            ->postJson(route('ai-studio.assets.export-to-catalog', $asset->id), [
                'product_id' => $this->product->id,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $this->product->refresh();
        $this->assertEquals('businesses/brand/ai-studio/output_photo.jpg', $this->product->image_path);
    }

    public function test_owner_can_browse_and_search_inspiration_library(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('ai-studio.inspiration.search', ['q' => 'podium']));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertNotEmpty($response->json('data'));
        $this->assertEquals('Minimalist Aesthetic Serum on Marble Podium', $response->json('data.0.title'));
    }

    public function test_admin_can_manage_sources_and_api_keys_are_never_exposed(): void
    {
        // 1. Simpan Kredensial API
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.ai-studio.sources.credentials.save', $this->source->id), [
                'credential_type' => 'api_key',
                'api_key' => 'secret_live_openverse_api_token_12345678',
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('has_credentials', true);
        // Pastikan raw key TIDAK ADA pada response JSON (AC-009)
        $this->assertArrayNotHasKey('api_key', $response->json());
        $this->assertStringNotContainsString('secret_live_openverse', $response->getContent());

        // 2. Akses index sumber
        $indexResponse = $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.ai-studio.sources.index'));

        $indexResponse->assertOk();
        $this->assertStringNotContainsString('secret_live_openverse', $indexResponse->getContent());
        $indexResponse->assertJsonPath('data.0.has_credentials', true);
    }

    public function test_admin_can_grant_ai_credits_to_business(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.ai-studio.pricing.grant-credits'), [
                'business_id' => $this->business->id,
                'credits' => 50,
                'description' => 'Bonus promo merchant aktif Q4',
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('new_balance', 60); // 10 awal + 50 = 60

        $this->business->refresh();
        $this->assertEquals(60, $this->business->ai_credits_balance);

        $this->assertDatabaseHas('ai_credit_transactions', [
            'business_id' => $this->business->id,
            'type' => 'topup',
            'amount' => 50,
        ]);
    }

    public function test_merchant_can_render_dashboard_and_create_views(): void
    {
        $dashboardResponse = $this->actingAs($this->user)
            ->get(route('ai-studio.index'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertViewIs('app.ai-studio.index');

        $createResponse = $this->actingAs($this->user)
            ->get(route('ai-studio.create'));
        $createResponse->assertOk();
        $createResponse->assertViewIs('app.ai-studio.create');

        $creditHistoryResponse = $this->actingAs($this->user)
            ->get(route('ai-studio.credit-history'));
        $creditHistoryResponse->assertOk();
        $creditHistoryResponse->assertViewIs('app.ai-studio.credit-history');
    }

    public function test_merchant_can_render_inspiration_gallery_and_show_views(): void
    {
        $galleryResponse = $this->actingAs($this->user)
            ->get(route('ai-studio.inspiration.index'));
        $galleryResponse->assertOk();
        $galleryResponse->assertViewIs('app.ai-studio.inspiration.index');

        $showResponse = $this->actingAs($this->user)
            ->get(route('ai-studio.inspiration.show', $this->inspirationItem->id));
        $showResponse->assertOk();
        $showResponse->assertViewIs('app.ai-studio.inspiration.show');
    }

    public function test_merchant_can_render_project_show_canvas_view(): void
    {
        $project = BusinessInspirationProject::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'name' => 'Canvas Visual Show Test',
            'target_platform' => 'instagram',
            'status' => 'draft',
            'last_activity_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('ai-studio.show', $project->id));
        $response->assertOk();
        $response->assertViewIs('app.ai-studio.show');
    }

    public function test_admin_can_render_sources_and_pricing_views(): void
    {
        $sourcesResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.ai-studio.sources.index'));
        $sourcesResponse->assertOk();
        $sourcesResponse->assertViewIs('admin.ai-studio.sources');

        $pricingResponse = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.ai-studio.pricing.index'));
        $pricingResponse->assertOk();
        $pricingResponse->assertViewIs('admin.ai-studio.pricing');
    }

    public function test_merchant_can_fetch_topup_packages(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('ai-studio.topup.packages'));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'credits_balance',
            'packages',
            'payment_channels',
        ]);
        $response->assertJson(['success' => true]);
    }

    public function test_merchant_can_order_topup(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('ai-studio.topup.order'), [
                'tier_code' => 'growth',
                'payment_method' => 'qris',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'payment' => [
                'id',
                'order_number',
                'credits',
                'amount',
                'payment_method',
                'payment_url',
                'status_url',
            ],
        ]);
        $this->assertEquals(150, $response->json('payment.credits'));
        $this->assertDatabaseHas('subscription_payments', [
            'business_id' => $this->business->id,
            'payment_type' => 'ai_token',
            'topup_quantity' => 150,
        ]);
    }

    public function test_merchant_can_check_topup_status(): void
    {
        $payment = \App\Models\SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'payment_type' => 'ai_token',
            'order_number' => 'TOP-TEST-0001',
            'plan_code' => 'topup',
            'cycle' => 'one_time',
            'amount' => 65000,
            'total_payable' => 65000,
            'topup_quantity' => 150,
            'payment_method' => 'qris',
            'payment_gateway' => \App\Models\SubscriptionPayment::GATEWAY_TRIPAY,
            'status' => \App\Models\SubscriptionPayment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('ai-studio.topup.status', $payment->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => \App\Models\SubscriptionPayment::STATUS_PENDING,
            'is_paid' => false,
        ]);
    }

    public function test_approving_topup_payment_adds_credits_to_business(): void
    {
        $initialBalance = (int) $this->business->ai_credits_balance;

        $payment = \App\Models\SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'payment_type' => 'ai_token',
            'order_number' => 'TOP-TEST-0002',
            'plan_code' => 'topup',
            'cycle' => 'one_time',
            'amount' => 65000,
            'total_payable' => 65000,
            'topup_quantity' => 150,
            'payment_method' => 'qris',
            'payment_gateway' => \App\Models\SubscriptionPayment::GATEWAY_TRIPAY,
            'status' => \App\Models\SubscriptionPayment::STATUS_PENDING,
        ]);

        $entitlementService = app(\App\Domain\Billing\EntitlementService::class);
        $entitlementService->approvePayment($payment, null, 'Test approve topup');

        $this->business->refresh();
        $this->assertEquals($initialBalance + 150, $this->business->ai_credits_balance);

        $this->assertDatabaseHas('ai_credit_transactions', [
            'business_id' => $this->business->id,
            'type' => 'topup',
            'amount' => 150,
        ]);
    }
}

