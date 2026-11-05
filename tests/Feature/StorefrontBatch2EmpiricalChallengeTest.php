<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Storefront\StorefrontThemeService;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceStoreSetting;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

final class StorefrontBatch2EmpiricalChallengeTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private BusinessLandingPage $landingPage;
    private StorefrontThemeService $themeService;

    /**
     * @var list<string>
     */
    private const BATCH_2_THEMES = [
        'bakery_cake',
        'fnb_bakery',
        'fnb_cafe',
        'fnb_catering',
        'fnb_fastfood',
        'fnb_resto',
        'retail_bookstore',
        'retail_building',
        'retail_electronics',
        'retail_florist',
        'retail_grocery',
    ];

    /**
     * @var list<string>
     */
    private const THEME_FILES = [
        'hero.blade.php',
        'features_banner.blade.php',
        'category_chips.blade.php',
    ];

    /**
     * Sector-specific phrases that must NEVER leak into the executable Blade body of refactored universal themes.
     *
     * @var list<string>
     */
    private const FORBIDDEN_SECTOR_PHRASES = [
        'Bahan Bangunan & Alat Konstruksi SNI',
        'Material Konstruksi Kokoh',
        'Distributor semen, besi beton SNI',
        'Besi beton bersertifikat',
        'Armada Truk Dump',
        'Gadget & Komputer Resmi',
        'Performa Teknologi Masa Depan',
        'IMEI Terdaftar Kemenperin',
        'Packing Kayu & Bubble Tebal',
        'Bunga Segar Terawat',
        'Dipilih tangkai terbaik',
        'Pilihan Rangkaian Bunga',
        'Lorong Belanja Sembako',
        'Sembako Lengkap, Sayur Segar',
        'Pasokan Segar Harian',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);

        $this->themeService = app(StorefrontThemeService::class);

        $this->owner = User::create([
            'name' => 'Adversarial Tester Batch 2',
            'email' => 'challenger.batch2@cooca.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Nusantara Global Solusindo',
            'slug' => 'nusantara-global-solusindo',
            'currency' => 'IDR',
            'is_active' => true,
            'description' => 'Penyedia solusi terpadu perlengkapan dan layanan operasional bisnis skala nasional.',
            'phone' => '081298765432',
        ]);

        $ownerRole = Role::firstOrCreate(
            ['business_id' => $this->business->id, 'slug' => 'owner'],
            ['name' => 'Owner', 'display_name' => 'Pemilik Bisnis']
        );

        $this->owner->businesses()->attach($this->business->id, ['role' => 'owner', 'role_id' => $ownerRole->id]);
        $this->owner->forceFill(['active_business_id' => $this->business->id])->save();

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
        ]);

        $this->landingPage = BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'theme_preset' => 'retail_building',
            'theme_color' => '#475569',
            'font_family' => 'Barlow Semi Condensed',
            'headline' => 'Solusi Pengadaan Material & Peralatan Terpercaya',
            'subheadline' => 'Koleksi lengkap produk unggulan dengan jaminan mutu dan pengiriman kilat.',
            'is_published' => true,
            'active_pages' => [
                'home' => true,
                'catalog' => true,
                'about' => true,
                'contact' => true,
            ],
            'section_visibility' => [
                'hero' => true,
                'features_banner' => true,
                'category_chips' => true,
            ],
        ]);
    }

    /**
     * Challenge 1: File Existence & PHP Syntax Lint across all 33 files in Batch 2 (11 themes).
     */
    public function test_all_11_batch_2_themes_exist_and_pass_php_lint(): void
    {
        $baseDir = resource_path('views/public/storefront/themes');

        foreach (self::BATCH_2_THEMES as $theme) {
            foreach (self::THEME_FILES as $file) {
                $path = $baseDir . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $file;
                $this->assertFileExists($path, "File {$file} missing in theme {$theme}");

                $output = [];
                $returnCode = 0;
                exec('php -l ' . escapeshellarg($path), $output, $returnCode);
                $this->assertSame(0, $returnCode, "PHP syntax error in {$path}: " . implode("\n", $output));
            }
        }
    }

    /**
     * Challenge 2: Search via grep/regex across all 33 files in Batch 2 for zero Unsplash and zero forbidden sector leaks.
     */
    public function test_zero_unsplash_and_zero_static_leakages_in_all_batch_2_templates(): void
    {
        $baseDir = resource_path('views/public/storefront/themes');

        foreach (self::BATCH_2_THEMES as $theme) {
            foreach (self::THEME_FILES as $file) {
                $filePath = $baseDir . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $file;
                $content = file_get_contents($filePath);
                $this->assertNotFalse($content, "Failed to read {$filePath}");

                // 1. Strict Unsplash check (forbidden everywhere including comments)
                $this->assertStringNotContainsStringIgnoringCase(
                    'unsplash',
                    $content,
                    "Forbidden Unsplash URL/reference found in {$theme}/{$file}"
                );

                // 2. Strict forbidden sector phrase check in executable body
                foreach (self::FORBIDDEN_SECTOR_PHRASES as $term) {
                    $pattern = '/' . preg_quote($term, '/') . '/i';
                    if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                        foreach ($matches[0] as $match) {
                            $offset = $match[1];
                            $before = substr($content, 0, $offset);
                            // If before contains closing comment '--}}', it's in executable body
                            if (str_contains($before, '--}}')) {
                                $this->fail("Forbidden sector term '{$term}' found in executable body of {$theme}/{$file} at offset {$offset}");
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Challenge 3: Verify dynamic rendering integrity under 5 edge-case parameter combinations across all 11 themes.
     */
    public function test_all_11_batch_2_themes_render_cleanly_under_all_parameter_combinations(): void
    {
        $categories = collect([
            (object) ['id' => 201, 'name' => 'Perlengkapan Pokok', 'products_count' => 12],
            (object) ['id' => 202, 'name' => 'Alat Penunjang', 'products_count' => 5],
        ]);

        $combinations = [
            'fully_populated' => [
                'headline' => 'Pusat Pengadaan Industri & Komersial',
                'subheadline' => 'Kemitraan pasokan resmi untuk ribuan proyek di seluruh nusantara.',
                'hero_image_url' => 'https://cooca.id/storage/uploads/custom_hero_b2.jpg',
                'values' => [
                    ['icon' => 'shield-check', 'title' => 'Mutu Terverifikasi', 'desc' => 'Uji standar mutu berkala'],
                    ['icon' => 'truck', 'title' => 'Distribusi Kilat', 'desc' => 'Jangkauan armada ke seluruh daerah'],
                    ['icon' => 'badge-percent', 'title' => 'Harga Kompetitif', 'desc' => 'Penawaran terbaik grosir dan ritel'],
                    ['icon' => 'headphones', 'title' => 'Konsultasi Teknis', 'desc' => 'Dukungan tim profesional siap bantu'],
                ],
            ],
            'all_null' => [
                'headline' => null,
                'subheadline' => null,
                'hero_image_url' => null,
                'values' => null,
            ],
            'all_empty' => [
                'headline' => '',
                'subheadline' => '',
                'hero_image_url' => '',
                'values' => [],
            ],
            'null_hero_with_custom_headline' => [
                'headline' => 'Layanan Profesional Ekosistem Bisnis',
                'subheadline' => 'Mendukung pertumbuhan usaha UMKM modern secara berkelanjutan.',
                'hero_image_url' => null,
                'values' => null,
            ],
            'sparse_single_value_with_image' => [
                'headline' => 'Distribusi Terintegrasi Nasional',
                'subheadline' => 'Kecepatan pengiriman dan akurasi pesanan adalah prioritas kami.',
                'hero_image_url' => 'https://cooca.id/storage/uploads/warehouse_logistics.jpg',
                'values' => [
                    ['icon' => 'check-circle-2', 'title' => 'Garansi Kepuasan', 'desc' => 'Jaminan produk original'],
                ],
            ],
        ];

        foreach (self::BATCH_2_THEMES as $theme) {
            $activeTheme = $this->themeService->getTheme($theme);

            foreach ($combinations as $comboName => $comboData) {
                $this->landingPage->forceFill([
                    'headline' => $comboData['headline'],
                    'subheadline' => $comboData['subheadline'],
                    'hero_image_url' => $comboData['hero_image_url'],
                    'values' => $comboData['values'],
                ]);

                $viewData = [
                    'business' => $this->business,
                    'landingPage' => $this->landingPage,
                    'activeTheme' => $activeTheme,
                    'theme' => $activeTheme,
                    'productCategories' => $categories,
                    'hasWhatsapp' => true,
                ];

                // 1. Render Hero Section
                $heroView = "public.storefront.themes.{$theme}.hero";
                $this->assertTrue(View::exists($heroView), "View {$heroView} must exist");
                $heroHtml = View::make($heroView, $viewData)->render();
                $this->assertNotEmpty($heroHtml, "Theme {$theme} hero rendered empty HTML on {$comboName}");

                // In all_null or null hero_image, Brand Bento Showcase card must render with business monogram
                if (empty($comboData['hero_image_url'])) {
                    $initials = strtoupper(substr($this->business->name, 0, 2));
                    $this->assertStringContainsString($initials, $heroHtml, "Brand Bento monogram '{$initials}' missing in {$theme} on {$comboName}");
                    $this->assertStringNotContainsString('images.unsplash.com', $heroHtml, "Unsplash leaked in {$theme} on {$comboName}");
                } else {
                    $this->assertStringContainsString($comboData['hero_image_url'], $heroHtml, "Hero image URL missing in {$theme} on {$comboName}");
                }

                // If headline is null/empty, fallback to business name
                if (empty($comboData['headline'])) {
                    $this->assertStringContainsString(e($this->business->name), $heroHtml, "Fallback business name missing in {$theme} on {$comboName}");
                } else {
                    $this->assertStringContainsString(e($comboData['headline']), $heroHtml, "Custom headline missing in {$theme} on {$comboName}");
                }

                // Dynamic badges in Hero
                $universalValues = $this->landingPage->getUniversalValues();
                $this->assertStringContainsString(e($universalValues[0]['title']), $heroHtml, "Hero dynamic badge 1 missing in {$theme} on {$comboName}");

                // 2. Render Features Banner
                $featuresView = "public.storefront.themes.{$theme}.features_banner";
                $this->assertTrue(View::exists($featuresView), "View {$featuresView} must exist");
                $featuresHtml = View::make($featuresView, $viewData)->render();
                $this->assertNotEmpty($featuresHtml, "Theme {$theme} features_banner rendered empty HTML on {$comboName}");

                // When custom values are present, check their presence in features banner
                if (!empty($comboData['values'])) {
                    foreach ($comboData['values'] as $item) {
                        $this->assertStringContainsString(e($item['title']), $featuresHtml, "Custom feature title '{$item['title']}' missing in {$theme} on {$comboName}");
                    }
                } else {
                    // Fallback universal 4 pillars
                    foreach ($universalValues as $item) {
                        $this->assertStringContainsString(e($item['title']), $featuresHtml, "Universal pillar '{$item['title']}' missing in {$theme} on {$comboName}");
                    }
                }

                // 3. Render Category Chips
                $chipsView = "public.storefront.themes.{$theme}.category_chips";
                $this->assertTrue(View::exists($chipsView), "View {$chipsView} must exist");
                $chipsHtml = View::make($chipsView, $viewData)->render();
                $this->assertNotEmpty($chipsHtml, "Theme {$theme} category_chips rendered empty HTML on {$comboName}");
                $this->assertStringContainsString(__('storefront.semua_produk'), $chipsHtml, "Semua Produk chip missing in {$theme} on {$comboName}");
                $this->assertStringContainsString('Perlengkapan Pokok', $chipsHtml, "Category title missing in {$theme} on {$comboName}");
                $this->assertMatchesRegularExpression('/min-h-\[(?:44|46|48)px\]/', $chipsHtml, "Touch target compliance missing in {$theme} on {$comboName}");
            }
        }
    }

    /**
     * Challenge 4: Category Chips suppression when product categories are empty.
     */
    public function test_category_chips_cleanly_suppresses_when_categories_empty(): void
    {
        $emptyCategories = collect();

        foreach (self::BATCH_2_THEMES as $theme) {
            $activeTheme = $this->themeService->getTheme($theme);
            $viewData = [
                'business' => $this->business,
                'landingPage' => $this->landingPage,
                'activeTheme' => $activeTheme,
                'productCategories' => $emptyCategories,
                'hasWhatsapp' => false,
            ];

            $chipsView = "public.storefront.themes.{$theme}.category_chips";
            $html = trim(View::make($chipsView, $viewData)->render());
            $this->assertEmpty($html, "Category chips should render empty when categories are empty in {$theme}");
        }
    }

    /**
     * Challenge 5: Cross-industry neutrality empirical verification on live preview HTTP responses.
     */
    public function test_cross_industry_neutrality_rendering_on_live_storefront(): void
    {
        // Merchant is an IT consultancy / consulting agency
        $this->landingPage->update([
            'headline' => 'Konsultasi IT & Solusi Transformasi Digital',
            'subheadline' => 'Membantu otomatisasi alur kerja dan integrasi ERP modern untuk bisnis Anda.',
            'values' => null, // rely on universal fallbacks
            'hero_image_url' => null, // rely on brand bento card fallback
        ]);

        foreach (self::BATCH_2_THEMES as $theme) {
            $activeTheme = $this->themeService->getTheme($theme);
            $viewData = [
                'business' => $this->business,
                'landingPage' => $this->landingPage,
                'activeTheme' => $activeTheme,
                'theme' => $activeTheme,
                'productCategories' => collect([(object) ['id' => 1, 'name' => 'Konsultasi Cloud']]),
                'hasWhatsapp' => true,
            ];

            // 1. Verify component views have 0 unsplash and 0 forbidden phrases
            foreach (self::THEME_FILES as $file) {
                $filePath = resource_path("views/public/storefront/themes/{$theme}/{$file}");
                $fileContent = file_get_contents($filePath);
                $this->assertStringNotContainsStringIgnoringCase('unsplash', $fileContent, "Unsplash found in {$theme}/{$file}");

                $viewName = 'public.storefront.themes.' . $theme . '.' . str_replace('.blade.php', '', $file);
                $renderedHtml = View::make($viewName, $viewData)->render();

                foreach (self::FORBIDDEN_SECTOR_PHRASES as $badWord) {
                    $this->assertStringNotContainsStringIgnoringCase(
                        $badWord,
                        $renderedHtml,
                        "Forbidden keyword '{$badWord}' appeared in rendered output of {$viewName}!"
                    );
                }
            }

            // 2. Verify live storefront HTTP 200 OK
            $response = $this->actingAs($this->owner, 'web')
                ->withSession(['active_business_id' => $this->business->id])
                ->get('/' . $this->business->slug . '?preview=1&preview_theme=' . $theme);

            $this->assertTrue($response->isOk(), "Storefront preview failed for theme {$theme} with status {$response->status()}");

            // Verify merchant identity is preserved
            $response->assertSee(e($this->business->name), false);
            $response->assertSee('Konsultasi IT &amp; Solusi Transformasi Digital', false);
        }
    }
}
