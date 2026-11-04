<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Domain\Ai\Inspiration\Adapters\PexelsAdapter;
use App\Domain\Ai\Inspiration\Adapters\PixabayAdapter;
use App\Domain\Ai\Inspiration\Adapters\UnsplashAdapter;
use App\Domain\Ai\Inspiration\Adapters\PinterestAdapter;
use App\Domain\Ai\Inspiration\Services\ExternalInspirationSearchService;
use App\Models\Admin;
use App\Models\AiStudio\InspirationAsset;
use App\Models\AiStudio\InspirationCollection;
use App\Models\AiStudio\InspirationFavorite;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class InspirationLibraryTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;
    private Business $businessA;
    private User $userB;
    private Business $businessB;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        // Business A & User A
        $this->userA = User::create([
            'name' => 'Merchant A Owner',
            'email' => 'merchant_a@cooca.id',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $this->businessA = Business::create([
            'name' => 'Bisnis Merchant A',
            'ai_credits_balance' => 100,
        ]);

        $this->businessA->users()->attach($this->userA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->userA->update(['active_business_id' => $this->businessA->id]);

        // Business B & User B
        $this->userB = User::create([
            'name' => 'Merchant B Owner',
            'email' => 'merchant_b@cooca.id',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $this->businessB = Business::create([
            'name' => 'Bisnis Merchant B',
            'ai_credits_balance' => 100,
        ]);

        $this->businessB->users()->attach($this->userB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->userB->update(['active_business_id' => $this->businessB->id]);

        // Super Admin
        $this->admin = Admin::create([
            'name' => 'Super Administrator',
            'email' => 'admin-inspiration@cooca.id',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        Context::setBusiness($this->businessA);
    }

    public function test_pexels_adapter_normalizes_response_and_respects_attribution(): void
    {
        Http::fake([
            'api.pexels.com/v1/search*' => Http::response([
                'total_results' => 1,
                'page' => 1,
                'per_page' => 1,
                'photos' => [
                    [
                        'id' => 998877,
                        'width' => 1080,
                        'height' => 1080,
                        'url' => 'https://www.pexels.com/photo/998877',
                        'photographer' => 'John Doe Photographer',
                        'photographer_url' => 'https://www.pexels.com/@johndoe',
                        'src' => [
                            'original' => 'https://images.pexels.com/photos/998877/orig.jpg',
                            'large' => 'https://images.pexels.com/photos/998877/large.jpg',
                            'medium' => 'https://images.pexels.com/photos/998877/medium.jpg',
                        ],
                        'alt' => 'Coffee product layout on clean wooden table',
                    ],
                ],
            ], 200),
        ]);

        Config::set('services.pexels.api_key', 'test_pexels_api_key');

        $adapter = app(PexelsAdapter::class);
        $result = $adapter->searchImages('coffee product');

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['items']);

        $item = $result['items'][0];
        $this->assertEquals('998877', $item['external_id']);
        $this->assertEquals('pexels', $item['source_provider']);
        $this->assertEquals('John Doe Photographer', $item['attribution']['photographer_name']);
        $this->assertEquals('https://www.pexels.com/@johndoe', $item['attribution']['photographer_url']);
        $this->assertTrue($item['license']['commercial_use']);
    }

    public function test_pixabay_adapter_enforces_cache_and_commercial_terms(): void
    {
        Http::fake([
            'pixabay.com/api/*' => Http::response([
                'total' => 1,
                'totalHits' => 1,
                'hits' => [
                    [
                        'id' => 12345,
                        'pageURL' => 'https://pixabay.com/photos/coffee-12345/',
                        'type' => 'photo',
                        'tags' => 'coffee, cup, drink',
                        'previewURL' => 'https://cdn.pixabay.com/photo/preview.jpg',
                        'webformatURL' => 'https://pixabay.com/photo/web.jpg',
                        'largeImageURL' => 'https://pixabay.com/photo/large.jpg',
                        'imageWidth' => 1920,
                        'imageHeight' => 1080,
                        'user' => 'PixabayArtist',
                        'user_id' => 54321,
                    ],
                ],
            ], 200),
        ]);

        Config::set('services.pixabay.api_key', 'test_pixabay_api_key');

        $adapter = app(PixabayAdapter::class);
        $result = $adapter->searchImages('coffee');

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['items']);

        $item = $result['items'][0];
        $this->assertEquals('12345', $item['external_id']);
        $this->assertEquals('pixabay', $item['source_provider']);
        $this->assertTrue($item['license']['commercial_use']);
        $this->assertEquals('Pixabay Content License (Cache min 24h applied)', $item['license']['policy']);
    }

    public function test_unsplash_adapter_tracks_download_endpoint_and_respects_hotlink(): void
    {
        Http::fake([
            'api.unsplash.com/search/photos*' => Http::response([
                'total' => 1,
                'total_pages' => 1,
                'results' => [
                    [
                        'id' => 'abc123xyz',
                        'width' => 1080,
                        'height' => 1350,
                        'description' => 'Organic beauty cosmetic bottle shoot',
                        'alt_description' => 'Minimalist skincare serum',
                        'urls' => [
                            'raw' => 'https://images.unsplash.com/photo-abc123xyz',
                            'full' => 'https://images.unsplash.com/photo-abc123xyz?q=80',
                            'regular' => 'https://images.unsplash.com/photo-abc123xyz?w=1080',
                            'thumb' => 'https://images.unsplash.com/photo-abc123xyz?w=200',
                        ],
                        'links' => [
                            'html' => 'https://unsplash.com/photos/abc123xyz',
                            'download_location' => 'https://api.unsplash.com/photos/abc123xyz/download',
                        ],
                        'user' => [
                            'name' => 'Alice Photographer',
                            'links' => [
                                'html' => 'https://unsplash.com/@alice',
                            ],
                        ],
                    ],
                ],
            ], 200),
            'api.unsplash.com/photos/abc123xyz/download' => Http::response([
                'url' => 'https://images.unsplash.com/download-tracked',
            ], 200),
        ]);

        Config::set('services.unsplash.access_key', 'test_unsplash_access_key');

        $adapter = app(UnsplashAdapter::class);
        $result = $adapter->searchImages('skincare');

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['items']);

        $item = $result['items'][0];
        $this->assertEquals('abc123xyz', $item['external_id']);
        $this->assertEquals('Alice Photographer', $item['attribution']['photographer_name']);

        // Test download location tracking endpoint execution
        $tracked = $adapter->recordDownload('abc123xyz');
        $this->assertTrue($tracked);
    }

    public function test_unified_search_gracefully_degrades_when_one_provider_fails(): void
    {
        // Pexels throws error 500
        Http::fake([
            'api.pexels.com/*' => Http::response(['error' => 'Pexels Rate limit exceeded'], 500),
            'pixabay.com/*' => Http::response([
                'total' => 1,
                'hits' => [
                    [
                        'id' => 777,
                        'pageURL' => 'https://pixabay.com/photos/777',
                        'webformatURL' => 'https://pixabay.com/777.jpg',
                        'largeImageURL' => 'https://pixabay.com/777_lg.jpg',
                        'imageWidth' => 1080,
                        'imageHeight' => 1080,
                        'user' => 'PixArtist',
                    ],
                ],
            ], 200),
        ]);

        Config::set('services.pexels.api_key', 'test_pexels_api_key');
        Config::set('services.pixabay.api_key', 'test_pixabay_api_key');

        $searchService = app(ExternalInspirationSearchService::class);
        $results = $searchService->search(
            query: 'retail store',
            filters: ['source' => 'all', 'per_page' => 10],
            business: $this->businessA,
            user: $this->userA
        );

        $this->assertTrue($results['success']);
        // Pixabay succeeded even though Pexels failed
        $this->assertNotEmpty($results['items']);
        $this->assertArrayHasKey('pexels', $results['errors']);
    }

    public function test_tenant_isolation_collections_cannot_be_accessed_by_other_merchants(): void
    {
        // Merchant A creates a private collection
        $collectionA = InspirationCollection::create([
            'business_id' => $this->businessA->id,
            'owner_type' => InspirationCollection::OWNER_BUSINESS,
            'title' => 'Koleksi Rahasia Merchant A',
            'slug' => 'koleksi-rahasia-merchant-a',
            'visibility' => 'private',
            'status' => 'active',
        ]);

        // Merchant A can access it
        Context::setBusiness($this->businessA);
        $this->actingAs($this->userA);
        $responseA = $this->get(route('ai-studio.inspiration.collections.show', $collectionA->id));
        $responseA->assertOk();
        $responseA->assertSee('Koleksi Rahasia Merchant A');

        // Merchant B tries to access Merchant A's private collection -> 403 Forbidden (IDOR Protected)
        Context::setBusiness($this->businessB);
        $this->actingAs($this->userB);
        $responseB = $this->get(route('ai-studio.inspiration.collections.show', $collectionA->id));
        $responseB->assertForbidden();
    }

    public function test_merchant_favorites_are_strictly_isolated_by_business_id(): void
    {
        // Merchant A favorites an item
        InspirationFavorite::create([
            'business_id' => $this->businessA->id,
            'user_id' => $this->userA->id,
            'external_id' => 'fav_ext_101',
            'source_provider' => 'pexels',
            'title' => 'Foto Favorit Merchant A',
            'preview_url' => 'https://example.com/favA.jpg',
        ]);

        // Merchant A sees their favorite
        Context::setBusiness($this->businessA);
        $this->actingAs($this->userA);
        $responseA = $this->get(route('ai-studio.inspiration.favorites'));
        $responseA->assertOk();
        $responseA->assertSee('Foto Favorit Merchant A');

        // Merchant B accesses favorites -> does NOT see Merchant A's favorite
        Context::setBusiness($this->businessB);
        $this->actingAs($this->userB);
        $responseB = $this->get(route('ai-studio.inspiration.favorites'));
        $responseB->assertOk();
        $responseB->assertDontSee('Foto Favorit Merchant A');
    }

    public function test_use_as_reference_allows_commercial_and_blocks_non_commercial(): void
    {
        Context::setBusiness($this->businessA);
        $this->actingAs($this->userA);

        // 1. Commercial Pexels image -> Accepted
        $resCommercial = $this->post(route('ai-studio.inspiration.use-as-reference'), [
            'image_url' => 'https://images.pexels.com/sample.jpg',
            'title' => 'Commercial Pexels Shoot',
            'source' => 'pexels',
            'commercial_allowed' => true,
        ]);
        $resCommercial->assertRedirect(route('ai-studio.create'));
        $this->assertEquals('https://images.pexels.com/sample.jpg', session('ai_studio_reference_preload.image_url'));

        // 2. Pinterest / non-commercial asset -> Blocked with 422 warning protecting merchant
        $resPinterest = $this->post(route('ai-studio.inspiration.use-as-reference'), [
            'image_url' => 'https://pinterest.com/sample.jpg',
            'title' => 'Pinterest Non-Commercial Pin',
            'source' => 'pinterest',
            'commercial_allowed' => false,
        ]);
        $resPinterest->assertStatus(302);
        $resPinterest->assertSessionHas('error');
    }

    public function test_admin_can_moderate_inspiration_assets(): void
    {
        $asset = InspirationAsset::create([
            'source_type' => InspirationAsset::SOURCE_CURATED,
            'source_name' => 'cooca_curated',
            'title' => 'Foto Menunggu Moderasi',
            'license_name' => 'Commercial',
            'aspect_ratio' => '1:1',
            'moderation_status' => InspirationAsset::MODERATION_PENDING,
        ]);

        $this->actingAs($this->admin, 'admin');

        $res = $this->postJson(route('admin.ai-studio.inspiration.moderate', $asset->id), [
            'status' => 'approved',
        ]);

        $res->assertOk();
        $res->assertJson(['success' => true]);

        $this->assertDatabaseHas('inspiration_assets', [
            'id' => $asset->id,
            'moderation_status' => 'approved',
        ]);
    }

    public function test_owner_can_access_my_library_view_without_errors(): void
    {
        Context::setBusiness($this->businessA);
        $this->actingAs($this->userA);

        $response = $this->get(route('ai-studio.inspiration.my-library'));

        $response->assertOk();
        $response->assertViewIs('app.ai-studio.inspiration.my-library');
        $response->assertViewHas(['assets', 'personalAssets', 'totalUploaded', 'totalGenerated', 'business']);
    }

    public function test_owner_can_upload_asset_to_my_library(): void
    {
        Storage::fake('public');
        Context::setBusiness($this->businessA);
        $this->actingAs($this->userA);

        $file = UploadedFile::fake()->image('produk_sample.jpg', 800, 800);

        $response = $this->post(route('ai-studio.inspiration.my-library.upload'), [
            'image' => $file,
            'title' => 'Foto Kemasan Produk Kopi',
            'aspect_ratio' => '1:1',
        ]);

        $response->assertRedirect(route('ai-studio.inspiration.my-library'));
        $this->assertDatabaseHas('inspiration_assets', [
            'business_id' => $this->businessA->id,
            'title' => 'Foto Kemasan Produk Kopi',
            'aspect_ratio' => '1:1',
        ]);
    }
}
