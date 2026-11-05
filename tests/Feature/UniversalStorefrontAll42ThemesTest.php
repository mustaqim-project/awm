<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Storefront\StorefrontThemeService;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceStoreSetting;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UniversalStorefrontAll42ThemesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private BusinessLandingPage $landingPage;
    private StorefrontThemeService $themeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);

        $this->owner = User::create([
            'name' => 'Storefront QA Auditor',
            'email' => 'qa-storefront@cooca.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Universal Nusantara Group',
            'slug' => 'universal-nusantara-group',
            'owner_id' => $this->owner->id,
            'description' => 'Solusi produk dan layanan premium untuk berbagai kebutuhan bisnis dan konsumen.',
            'phone' => '081234567890',
            'email' => 'halo@universalnusantara.com',
            'address' => 'Jl. Jenderal Sudirman Kav. 52-53, Jakarta Selatan',
        ]);

        $this->landingPage = BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'is_published' => true,
            'industry_preset' => 'retail',
            'theme_preset' => 'universal_storefront',
            'headline' => 'Koleksi Produk & Layanan Unggulan',
            'subheadline' => 'Kualitas terbaik dengan jaminan garansi dan layanan pelanggan prima 24/7.',
            'announcement_badge' => 'Terverifikasi Resmi',
            'cta_primary_text' => 'Buka Katalog Lengkap',
            'cta_secondary_text' => 'Konsultasi Gratis',
            'values' => [
                ['title' => 'Kualitas Standar ISO', 'icon' => 'shield-check', 'description' => 'Seluruh produk melalui uji kelayakan bertaraf internasional.'],
                ['title' => 'Pengiriman Cepat Aman', 'icon' => 'truck', 'description' => 'Didukung armada terpadu dan jaminan asuransi hingga tujuan.'],
                ['title' => 'Layanan Pelanggan 24/7', 'icon' => 'clock', 'description' => 'Tim representatif siap melayani konsultasi kapan pun dibutuhkan.'],
                ['title' => 'Garansi Kepuasan Penuh', 'icon' => 'award', 'description' => 'Komitmen penggantian jika produk tidak sesuai standar.'],
            ],
            'section_visibility' => [
                'hero' => true,
                'features_banner' => true,
                'category_chips' => true,
                'products' => true,
                'services' => true,
                'about' => true,
            ],
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'store_name' => 'Universal Nusantara Group',
            'is_store_active' => true,
            'allow_guest_checkout' => true,
        ]);

        $cat = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Katalog Unggulan',
            'slug' => 'katalog-unggulan',
            'is_active' => true,
        ]);

        $pcs = \App\Models\Unit::where('code', 'pcs')->first() ?? \App\Models\Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pieces',
            'code' => 'pcs',
            'is_standard' => true,
        ]);

        Product::create([
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'output_unit_id' => $pcs->id,
            'name' => 'Produk Universal Grade A',
            'slug' => 'produk-universal-grade-a',
            'type' => 'goods',
            'selling_price' => 250000,
            'is_active' => true,
            'description' => 'Produk berkualitas tinggi untuk kebutuhan personal dan profesional.',
        ]);

        $this->themeService = app(StorefrontThemeService::class);
    }

    /**
     * Test 1: Verify exactly 42 themes registered and mapped to 6 archetypes.
     */
    public function test_all_42_themes_registered_and_mapped_to_valid_archetypes(): void
    {
        $allThemes = $this->themeService->getAllThemes();
        $this->assertCount(42, $allThemes, 'Exactly 42 storefront themes must be registered.');

        $archetypes = $this->themeService->getAllArchetypes();
        $this->assertCount(6, $archetypes, 'Exactly 6 visual design archetypes must exist.');

        $validArchetypeKeys = array_keys(StorefrontThemeService::ARCHETYPES);
        $totalMappedThemes = 0;

        foreach ($archetypes as $archKey => $archData) {
            $this->assertContains($archKey, $validArchetypeKeys);
            $this->assertNotEmpty($archData['themes']);
            $totalMappedThemes += count($archData['themes']);
        }

        $this->assertSame(42, $totalMappedThemes, 'The sum of themes across 6 archetypes must equal 42.');

        foreach ($allThemes as $themeKey => $themeData) {
            $this->assertArrayHasKey('archetype', $themeData, "Theme {$themeKey} must have archetype.");
            $this->assertContains($themeData['archetype'], $validArchetypeKeys, "Theme {$themeKey} archetype invalid.");
            $this->assertArrayHasKey('archetype_name', $themeData);
            $this->assertArrayHasKey('bundle_dir', $themeData);
        }
    }

    /**
     * Test 2: Verify all 42 theme directories have required Blade components.
     */
    public function test_all_42_themes_have_all_required_blade_components(): void
    {
        $themes = array_keys($this->themeService->getAllThemes());
        $requiredFiles = [
            'hero.blade.php',
            'features_banner.blade.php',
            'category_chips.blade.php',
            'product_card.blade.php',
            'navbar.blade.php',
            'footer.blade.php',
        ];

        $basePath = resource_path('views/public/storefront/themes');

        foreach ($themes as $theme) {
            $dir = $basePath . '/' . $theme;
            $this->assertDirectoryExists($dir, "Directory for theme {$theme} must exist.");

            foreach ($requiredFiles as $file) {
                $filePath = $dir . '/' . $file;
                $this->assertFileExists($filePath, "Theme {$theme} must contain {$file}.");
            }
        }
    }

    /**
     * Test 3: Verify zero Unsplash URLs exist in any theme view.
     */
    public function test_zero_unsplash_urls_in_all_theme_views(): void
    {
        $themes = array_keys($this->themeService->getAllThemes());
        $basePath = resource_path('views/public/storefront/themes');

        foreach ($themes as $theme) {
            $dir = $basePath . '/' . $theme;
            $files = glob($dir . '/*.blade.php') ?: [];

            foreach ($files as $filePath) {
                $content = (string) file_get_contents($filePath);
                $this->assertStringNotContainsString(
                    'images.unsplash.com',
                    $content,
                    "Theme {$theme} file " . basename($filePath) . " contains forbidden Unsplash URL."
                );
            }
        }
    }

    /**
     * Test 4: Verify preview rendering succeeds for each theme.
     */
    public function test_all_42_themes_render_successfully_on_preview(): void
    {
        $themes = array_keys($this->themeService->getAllThemes());

        foreach ($themes as $themeKey) {
            $response = $this->get('/' . $this->business->slug . '?preview=1&preview_theme=' . $themeKey);
            $response->assertStatus(200);
            $response->assertSee('Universal Nusantara Group', false);
        }
    }

    /**
     * Test 5: Verify section visibility controls suppress sections when set to false.
     */
    public function test_section_visibility_controls_suppress_sections_when_false(): void
    {
        // 1. Hide features_banner and category_chips
        $this->landingPage->update([
            'section_visibility' => [
                'hero' => true,
                'features_banner' => false,
                'category_chips' => false,
                'products' => true,
                'services' => true,
                'about' => true,
            ],
        ]);

        $this->assertFalse($this->landingPage->isSectionVisible('features_banner'));
        $this->assertFalse($this->landingPage->isSectionVisible('category_chips'));
        $this->assertTrue($this->landingPage->isSectionVisible('hero'));

        $response = $this->get('/' . $this->business->slug);
        $response->assertStatus(200);
        // Hero values are present in hero bento
        $response->assertSee('Kualitas Standar ISO', false);

        // 2. Hide hero as well
        $this->landingPage->update([
            'section_visibility' => [
                'hero' => false,
                'features_banner' => false,
                'category_chips' => false,
                'products' => true,
                'services' => true,
                'about' => true,
            ],
        ]);

        $this->assertFalse($this->landingPage->isSectionVisible('hero'));
        $response = $this->get('/' . $this->business->slug);
        $response->assertStatus(200);
        $response->assertDontSee('Koleksi Produk & Layanan Unggulan', false);
    }
}
