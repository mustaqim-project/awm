<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Models\AiStudio\BusinessInspirationGeneration;
use App\Models\AiStudio\BusinessInspirationGenerationAsset;
use App\Models\AiStudio\BusinessInspirationProject;
use App\Models\AiStudio\BusinessInspirationPromptVersion;
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

final class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private Business $businessA;
    private Product $productA;
    private BusinessInspirationProject $projectA;

    private User $userB;
    private Business $businessB;
    private Product $productB;
    private BusinessInspirationProject $projectB;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        // 1. Setup Tenant A
        $this->userA = User::create([
            'name' => 'Owner Tenant A',
            'email' => 'tenantA@cooca.id',
            'password' => Hash::make('secret123'),
        ]);

        $this->businessA = Business::create([
            'name' => 'Bisnis Kopi Nusantara A',
            'ai_credits_balance' => 100,
        ]);

        $this->businessA->users()->attach($this->userA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        $unitA = Unit::create([
            'business_id' => $this->businessA->id,
            'code' => 'PCS',
            'name' => 'Pcs',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $this->productA = Product::create([
            'business_id' => $this->businessA->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Kopi Robusta A',
            'slug' => 'kopi-robusta-a',
            'selling_price' => 50000,
            'output_unit_id' => $unitA->id,
            'is_active' => true,
        ]);

        $this->projectA = BusinessInspirationProject::create([
            'business_id' => $this->businessA->id,
            'user_id' => $this->userA->id,
            'name' => 'Proyek Milik Tenant A',
            'target_platform' => 'instagram',
            'status' => 'draft',
            'last_activity_at' => now(),
        ]);

        // 2. Setup Tenant B
        $this->userB = User::create([
            'name' => 'Owner Tenant B',
            'email' => 'tenantB@cooca.id',
            'password' => Hash::make('secret123'),
        ]);

        $this->businessB = Business::create([
            'name' => 'Bisnis Kosmetik B',
            'ai_credits_balance' => 50,
        ]);

        $this->businessB->users()->attach($this->userB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->userB->update(['active_business_id' => $this->businessB->id]);

        $unitB = Unit::create([
            'business_id' => $this->businessB->id,
            'code' => 'BTL',
            'name' => 'Botol',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $this->productB = Product::create([
            'business_id' => $this->businessB->id,
            'type' => Product::TYPE_GOODS,
            'name' => 'Lipstik Matte B',
            'slug' => 'lipstik-matte-b',
            'selling_price' => 75000,
            'output_unit_id' => $unitB->id,
            'is_active' => true,
        ]);

        $this->projectB = BusinessInspirationProject::create([
            'business_id' => $this->businessB->id,
            'user_id' => $this->userB->id,
            'name' => 'Proyek Rahasia Tenant B',
            'target_platform' => 'shopee',
            'status' => 'draft',
            'last_activity_at' => now(),
        ]);
    }

    /**
     * AC-004: Upaya menautkan produk milik tenant lain wajib ditolak (HTTP 422 / 404).
     */
    public function test_tenant_a_cannot_attach_other_tenant_product_to_new_project(): void
    {
        Context::setBusiness($this->businessA);

        $response = $this->actingAs($this->userA)
            ->postJson(route('ai-studio.projects.store'), [
                'name' => 'Eksploitasi Produk Tenant Lain',
                'target_channel' => 'instagram',
                'aspect_ratio' => '1:1',
                'product_id' => $this->productB->id, // Produk milik Tenant B!
            ]);

        // Harus gagal validasi karena product_id diproteksi Rule::exists('products', 'id')->where('business_id', $businessA->id)
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_id']);
    }

    /**
     * Tenant A tidak dapat melihat proyek milik Tenant B.
     */
    public function test_tenant_a_cannot_view_tenant_b_project(): void
    {
        Context::setBusiness($this->businessA);

        $response = $this->actingAs($this->userA)
            ->getJson(route('ai-studio.show', $this->projectB->id));

        $response->assertNotFound();
    }

    /**
     * Tenant A tidak dapat menghapus proyek milik Tenant B.
     */
    public function test_tenant_a_cannot_delete_tenant_b_project(): void
    {
        Context::setBusiness($this->businessA);

        $response = $this->actingAs($this->userA)
            ->deleteJson(route('ai-studio.projects.destroy', $this->projectB->id));

        $response->assertNotFound();
        $this->assertDatabaseHas('business_inspiration_projects', ['id' => $this->projectB->id]);
    }

    /**
     * Tenant A tidak dapat memesan render untuk project_id milik Tenant B.
     */
    public function test_tenant_a_cannot_dispatch_generation_on_tenant_b_project(): void
    {
        Context::setBusiness($this->businessA);

        $response = $this->actingAs($this->userA)
            ->postJson(route('ai-studio.generate'), [
                'project_id' => $this->projectB->id, // Project milik B
                'provider' => 'ideogram',
                'model_name' => 'ideogram-v2',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['project_id']);
    }

    /**
     * Tenant A tidak dapat melihat status generasi milik Tenant B.
     */
    public function test_tenant_a_cannot_view_generation_status_of_tenant_b(): void
    {
        Context::setBusiness($this->businessB);

        $promptVersionB = BusinessInspirationPromptVersion::create([
            'business_id' => $this->businessB->id,
            'project_id' => $this->projectB->id,
            'version_number' => 1,
            'positive_prompt' => 'Test prompt B',
            'aspect_ratio' => '1:1',
        ]);

        $generationB = BusinessInspirationGeneration::create([
            'business_id' => $this->businessB->id,
            'project_id' => $this->projectB->id,
            'prompt_version_id' => $promptVersionB->id,
            'user_id' => $this->userB->id,
            'ai_provider' => 'openai',
            'ai_model_name' => 'dall-e-3',
            'idempotency_key' => 'idemp_' . Str::uuid(),
            'status' => 'succeeded',
            'credit_cost' => 20,
        ]);

        // Login sebagai Tenant A
        Context::setBusiness($this->businessA);

        $response = $this->actingAs($this->userA)
            ->getJson(route('ai-studio.generation.status', [
                'project' => $this->projectB->id,
                'generation' => $generationB->id,
            ]));

        $response->assertNotFound();
    }

    /**
     * Tenant A tidak dapat mengekspor atau memanipulasi aset render milik Tenant B.
     */
    public function test_tenant_a_cannot_export_or_favorite_tenant_b_asset(): void
    {
        $promptVersionB = BusinessInspirationPromptVersion::create([
            'business_id' => $this->businessB->id,
            'project_id' => $this->projectB->id,
            'version_number' => 1,
            'positive_prompt' => 'Test prompt B export',
            'aspect_ratio' => '1:1',
        ]);

        $generationB = BusinessInspirationGeneration::create([
            'business_id' => $this->businessB->id,
            'project_id' => $this->projectB->id,
            'prompt_version_id' => $promptVersionB->id,
            'user_id' => $this->userB->id,
            'ai_provider' => 'ideogram',
            'ai_model_name' => 'ideogram-v2',
            'idempotency_key' => 'idemp_' . Str::uuid(),
            'status' => 'succeeded',
            'credit_cost' => 15,
        ]);

        $assetB = BusinessInspirationGenerationAsset::create([
            'business_id' => $this->businessB->id,
            'generation_id' => $generationB->id,
            'storage_path' => 'bisnis/kosmetik-b/ai-studio/render.png',
            'output_image_url' => 'https://storage.cooca.id/render.png',
            'aspect_ratio' => '1:1',
            'is_selected_favorite' => false,
        ]);

        // Login sebagai Tenant A
        Context::setBusiness($this->businessA);

        // 1. Ekspor katalog harus 404
        $exportResponse = $this->actingAs($this->userA)
            ->postJson(route('ai-studio.assets.export-to-catalog', $assetB->id), [
                'name' => 'Pencurian Gambar Aset B',
                'selling_price' => 10000,
            ]);
        $exportResponse->assertNotFound();

        // 2. Toggle favorite harus 404
        $favResponse = $this->actingAs($this->userA)
            ->postJson(route('ai-studio.assets.toggle-favorite', $assetB->id));
        $favResponse->assertNotFound();
    }
}
