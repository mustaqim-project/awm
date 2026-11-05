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

final class StorefrontBatch1EmpiricalChallengeTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private BusinessLandingPage $landingPage;
    private ProductCategory $categoryA;
    private ProductCategory $categoryB;

    /**
     * @var list<string>
     */
    private const BATCH_1_THEMES = [
        'universal_storefront',
        'modern_umkm',
        'online_store',
        'business_corporate',
        'coffee_beverage',
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
     * @var list<string>
     */
    private const FORBIDDEN_SECTOR_PHRASES = [
        'Tune-up',
        'Petik Segar',
        'Jahitan Rapi',
        'Specialty Roastery',
        '80+ Cupping',
        'images.unsplash.com',
        'unsplash.com',
        'Bongkar Pasang',
        'Resep Warisan Nusantara',
        'Sewa Kamar',
        'Corporate Enterprise',
        '10k+ Pasien',
        'BPOM',
        'OBD-II',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);

        $this->owner = User::create([
            'name' => 'Adversarial Tester',
            'email' => 'challenger@cooca.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Omni Enterprise Store',
            'slug' => 'omni-enterprise-store',
            'currency' => 'IDR',
            'is_active' => true,
            'description' => 'Solusi bisnis serba guna untuk seluruh sektor UMKM',
            'phone' => '081234567890',
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
            'theme_preset' => 'universal_storefront',
            'theme_color' => '#10B981',
            'font_family' => 'Plus Jakarta Sans',
            'headline' => 'Headline Awal',
            'subheadline' => 'Subheadline awal toko serba ada',
            'is_published' => true,
            'active_pages' => [
                'home' => true,
                'catalog' => true,
                'about' => true,
                'contact' => true,
            ],
        ]);

        $this->categoryA = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Produk Unggulan',
            'slug' => 'produk-unggulan',
            'is_active' => true,
        ]);

        $this->categoryB = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Koleksi Spesial',
            'slug' => 'koleksi-spesial',
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
            'category_id' => $this->categoryA->id,
            'output_unit_id' => $pcs->id,
            'name' => 'Produk Serbaguna 1',
            'slug' => 'produk-serbaguna-1',
            'selling_price' => 50000,
            'is_active' => true,
            'is_storefront' => true,
            'type' => 'goods',
        ]);
    }

    /**
     * EMPIRICAL CHECK 1: Search all 15 files across Themes 1-5 for forbidden sector copy & Unsplash URLs.
     */
    public function test_forbidden_phrases_zero_leakage_across_all_15_files(): void
    {
        $baseDir = resource_path('views/public/storefront/themes');
        $violations = [];

        foreach (self::BATCH_1_THEMES as $theme) {
            foreach (self::THEME_FILES as $file) {
                $filePath = $baseDir . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $file;
                $this->assertFileExists($filePath, "Expected file must exist: $filePath");

                $content = file_get_contents($filePath);
                $this->assertNotFalse($content, "File content could not be read: $filePath");

                foreach (self::FORBIDDEN_SECTOR_PHRASES as $phrase) {
                    if (stripos($content, $phrase) !== false) {
                        $violations[] = [
                            'theme' => $theme,
                            'file' => $file,
                            'phrase' => $phrase,
                        ];
                    }
                }
            }
        }

        $this->assertEmpty(
            $violations,
            'Forbidden sector phrases or external Unsplash links detected in Themes 1-5 files: ' . json_encode($violations, JSON_PRETTY_PRINT)
        );
    }

    /**
     * EMPIRICAL CHECK 2: Render all 15 files under boundary variations of headline, values, and hero_image_url.
     */
    public function test_all_15_files_render_cleanly_under_all_boundary_permutations(): void
    {
        $scenarios = [
            'scenario_nulls' => [
                'headline' => null,
                'subheadline' => null,
                'announcement_badge' => null,
                'values' => null,
                'hero_image_url' => null,
                'about_image_url' => null,
            ],
            'scenario_empty_strings' => [
                'headline' => '',
                'subheadline' => '',
                'announcement_badge' => '',
                'values' => [],
                'hero_image_url' => '',
                'about_image_url' => '',
            ],
            'scenario_fully_populated' => [
                'headline' => 'Katalog Produk Universal 2026',
                'subheadline' => 'Menyediakan aneka perlengkapan & servis terbaik',
                'announcement_badge' => 'Promo Terverifikasi',
                'values' => [
                    ['icon' => 'award', 'title' => 'Kualitas Prima', 'desc' => 'Standar mutu terjamin untuk semua sektor'],
                    ['icon' => 'lock', 'title' => 'Transaksi Aman', 'desc' => 'Enkripsi & jaminan pembayaran resmi'],
                    ['icon' => 'send', 'title' => 'Pengiriman Cepat', 'desc' => 'Kurir ekspres ke seluruh wilayah'],
                    ['icon' => 'headphones', 'title' => 'Layanan 24 Jam', 'desc' => 'Bantuan siap siaga kapan saja'],
                ],
                'hero_image_url' => 'https://cooca.id/storage/banners/universal-hero.webp',
                'about_image_url' => 'https://cooca.id/storage/banners/universal-about.webp',
            ],
            'scenario_partial_single_pillar_and_about_fallback' => [
                'headline' => null,
                'subheadline' => 'Deskripsi parsial',
                'announcement_badge' => null,
                'values' => [
                    ['icon' => 'star', 'title' => 'Hanya 1 Pilar CMS', 'desc' => 'Pilar khusus tunggal'],
                ],
                'hero_image_url' => null,
                'about_image_url' => 'https://cooca.id/storage/banners/fallback-about.webp',
            ],
            'scenario_malformed_values_and_special_chars' => [
                'headline' => 'Toko "Spesial" & <Sensasional> & \'Keren\'',
                'subheadline' => 'Sub & "Quote" <script>console.log("safe")</script>',
                'announcement_badge' => 'Badge "Top" & 100%',
                'values' => [
                    ['invalid_key' => 'foo'],
                    'string_instead_of_array',
                    null,
                ],
                'hero_image_url' => null,
                'about_image_url' => null,
            ],
        ];

        $productCategories = collect([$this->categoryA, $this->categoryB]);
        $hasWhatsapp = true;
        $activeThemeService = app(StorefrontThemeService::class);

        foreach (self::BATCH_1_THEMES as $themeKey) {
            $themeData = $activeThemeService->getTheme($themeKey);

            foreach ($scenarios as $scenarioName => $attrs) {
                $this->landingPage->forceFill($attrs)->save();
                $this->landingPage->refresh();

                $viewData = [
                    'business' => $this->business,
                    'landingPage' => $this->landingPage,
                    'activeTheme' => $themeData,
                    'theme' => $themeData,
                    'hasWhatsapp' => $hasWhatsapp,
                    'productCategories' => $productCategories,
                ];

                // 1. Test hero.blade.php
                $heroView = "public.storefront.themes.{$themeKey}.hero";
                $this->assertTrue(View::exists($heroView), "View must exist: $heroView");
                $renderedHero = View::make($heroView, $viewData)->render();
                $this->assertNotEmpty($renderedHero, "Rendered hero cannot be empty in $themeKey ($scenarioName)");

                // Ensure raw Blade tags are not printed as literal text
                $this->assertStringNotContainsString('@php', $renderedHero);
                $this->assertStringNotContainsString('@endphp', $renderedHero);
                $this->assertStringNotContainsString('@if', $renderedHero);
                $this->assertStringNotContainsString('@endif', $renderedHero);

                // Specific scenario behavioral assertions
                if ($attrs['hero_image_url'] !== null && $attrs['hero_image_url'] !== '') {
                    $this->assertStringContainsString($attrs['hero_image_url'], $renderedHero);
                } elseif (! empty($attrs['about_image_url'])) {
                    $this->assertStringContainsString($attrs['about_image_url'], $renderedHero);
                } else {
                    // Universal Brand Bento Graphic fallback must show business monogram / name
                    $this->assertStringContainsString($this->business->name, $renderedHero);
                    $this->assertStringContainsString(strtoupper(substr($this->business->name, 0, 2)), $renderedHero);
                }

                if (! empty($attrs['headline'])) {
                    $this->assertStringContainsString(e($attrs['headline']), $renderedHero);
                } else {
                    $this->assertStringContainsString($this->business->name, $renderedHero);
                }

                // 2. Test features_banner.blade.php
                $featuresView = "public.storefront.themes.{$themeKey}.features_banner";
                $this->assertTrue(View::exists($featuresView), "View must exist: $featuresView");
                $renderedFeatures = View::make($featuresView, $viewData)->render();
                $this->assertNotEmpty($renderedFeatures, "Rendered features cannot be empty in $themeKey ($scenarioName)");
                $this->assertStringNotContainsString('@foreach', $renderedFeatures);
                $this->assertStringNotContainsString('@endforeach', $renderedFeatures);

                // If populated, verify custom pillar title is rendered
                if ($scenarioName === 'scenario_fully_populated') {
                    $this->assertStringContainsString('Kualitas Prima', $renderedFeatures);
                    $this->assertStringContainsString('Layanan 24 Jam', $renderedFeatures);
                }

                // 3. Test category_chips.blade.php
                $chipsView = "public.storefront.themes.{$themeKey}.category_chips";
                $this->assertTrue(View::exists($chipsView), "View must exist: $chipsView");
                $renderedChips = View::make($chipsView, $viewData)->render();
                $this->assertNotEmpty($renderedChips, "Rendered category chips cannot be empty in $themeKey ($scenarioName)");
                $this->assertStringContainsString('Semua Produk', $renderedChips);
                $this->assertStringContainsString('Produk Unggulan', $renderedChips);
                $this->assertStringContainsString('Koleksi Spesial', $renderedChips);
            }
        }
    }

    /**
     * EMPIRICAL CHECK 3: Verify category chips behavior under empty categories and active category selection.
     */
    public function test_category_chips_behavior_with_filter_states_and_empty_categories(): void
    {
        $activeThemeService = app(StorefrontThemeService::class);

        foreach (self::BATCH_1_THEMES as $themeKey) {
            $themeData = $activeThemeService->getTheme($themeKey);
            $chipsView = "public.storefront.themes.{$themeKey}.category_chips";

            // State A: Empty categories -> Should render nothing cleanly without throwing any exception
            $emptyData = [
                'business' => $this->business,
                'landingPage' => $this->landingPage,
                'activeTheme' => $themeData,
                'productCategories' => collect([]),
            ];
            $renderedEmpty = View::make($chipsView, $emptyData)->render();
            $this->assertSame('', trim($renderedEmpty), "Empty categories must render empty string in $themeKey");

            // State B: Active category selected in query string
            request()->query->set('category', (string) $this->categoryA->id);
            $activeData = [
                'business' => $this->business,
                'landingPage' => $this->landingPage,
                'activeTheme' => $themeData,
                'productCategories' => collect([$this->categoryA, $this->categoryB]),
            ];
            $renderedActive = View::make($chipsView, $activeData)->render();
            $this->assertStringContainsString('theme-btn-primary', $renderedActive);
            $this->assertStringContainsString('category=' . $this->categoryA->id, $renderedActive);

            // Clear request query parameter
            request()->query->remove('category');
        }
    }

    /**
     * EMPIRICAL CHECK 4: Full HTTP Storefront Rendering across Themes 1 to 5.
     */
    public function test_full_http_storefront_renders_each_theme_with_http_200(): void
    {
        foreach (self::BATCH_1_THEMES as $themeKey) {
            $response = $this->actingAs($this->owner, 'web')
                ->withSession(['active_business_id' => $this->business->id])
                ->get('/' . $this->business->slug . '?preview=1&preview_theme=' . $themeKey);

            $this->assertTrue(
                $response->isOk(),
                "Storefront HTTP preview failed for theme '$themeKey'. Status: " . $response->status()
            );

            // Assert business identity renders
            $response->assertSee($this->business->name);

            // Assert no unrendered blade tags leaked in the final HTTP response
            $content = $response->getContent();
            $this->assertStringNotContainsString('@php', $content);
            $this->assertStringNotContainsString('@endphp', $content);
            $this->assertStringNotContainsString('@if', $content);
            $this->assertStringNotContainsString('@endif', $content);
        }
    }

    /**
     * EMPIRICAL CHECK 5: Verify behavior when hasWhatsapp is explicitly false.
     */
    public function test_rendering_when_has_whatsapp_is_false(): void
    {
        $activeThemeService = app(StorefrontThemeService::class);

        foreach (self::BATCH_1_THEMES as $themeKey) {
            $heroView = "public.storefront.themes.{$themeKey}.hero";
            $dataWithFalseWhatsapp = [
                'business' => $this->business,
                'landingPage' => $this->landingPage,
                'activeTheme' => $activeThemeService->getTheme($themeKey),
                'hasWhatsapp' => false,
            ];

            $rendered = View::make($heroView, $dataWithFalseWhatsapp)->render();
            $this->assertNotEmpty($rendered);
            // Verify message-circle icon / secondary CTA is NOT rendered
            $this->assertStringNotContainsString('data-lucide="message-circle"', $rendered);
        }
    }
}
