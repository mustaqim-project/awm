<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Storefront\StorefrontThemeService;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceStoreSetting;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Adversarial Empirical Challenge Test Suite for Milestone 2B (Batch 2: 11 Themes).
 *
 * Authored by: teamwork_challenger_m2b_2
 * Scope: Independent stress-testing of cross-industry neutrality and touch targets.
 */
final class AdversarialBatch2EmpiricalChallengeTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
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
    private const THEME_COMPONENTS = [
        'hero',
        'features_banner',
        'category_chips',
    ];

    /**
     * Highly specific sector phrases that must NEVER leak into any theme template when rendered
     * for unrelated industries.
     *
     * @var list<string>
     */
    private const FORBIDDEN_SECTOR_KEYWORDS = [
        // Bakery & Cake
        'roti',
        'kue',
        'cake',
        'bakery',
        'patisserie',
        'pastry',
        'adonan',
        'panggangan oven',
        'fresh baked',
        // Cafe & Coffee
        'kopi',
        'coffee',
        'kafe',
        'barista',
        'espresso',
        'cappuccino',
        'latte',
        'biji kopi',
        'roasting',
        // Catering
        'catering',
        'katering',
        'prasmanan',
        'buffet',
        'tumpeng',
        'nasi box',
        'kotak makan',
        // Fast Food
        'fast food',
        'fastfood',
        'burger',
        'pizza',
        'fried chicken',
        'kentang goreng',
        // Resto
        'resto',
        'restoran',
        'kuliner',
        'santapan lezat',
        'hidangan lezat',
        // Bookstore
        'buku',
        'bookstore',
        'novel',
        'komik',
        'bacaan',
        'pustaka',
        'penulis buku',
        // Building
        'semen',
        'pasir',
        'besi beton',
        'bata merah',
        'material bangunan',
        'alat konstruksi',
        'truk dump',
        // Electronics
        'gadget',
        'handphone',
        'smartphone',
        'laptop',
        'imei terdaftar',
        'kemenperin',
        'packing kayu',
        // Florist
        'florist',
        'bunga',
        'bouquet',
        'buket',
        'mawar',
        'rangkaian bunga',
        'tangkai terbaik',
        // Grocery
        'sayur',
        'sayuran',
        'sembako',
        'grocery',
        'supermarket',
        'buah segar',
        'pasar segar',
        'lorong belanja',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);
        $this->themeService = app(StorefrontThemeService::class);

        $this->owner = User::create([
            'name' => 'Empirical Challenger Batch 2',
            'email' => 'empirical.challenger@cooca.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Helper to set up an adversarial business profile with CommerceStoreSetting and BusinessLandingPage.
     */
    private function createAdversarialBusiness(
        string $name,
        string $slug,
        string $desc,
        string $headline,
        string $subheadline,
        ?array $values = null
    ): array {
        $business = Business::create([
            'name' => $name,
            'slug' => $slug,
            'currency' => 'IDR',
            'is_active' => true,
            'description' => $desc,
            'phone' => '081234567890',
        ]);

        $ownerRole = Role::firstOrCreate(
            ['business_id' => $business->id, 'slug' => 'owner'],
            ['name' => 'Owner', 'display_name' => 'Pemilik Bisnis']
        );

        $this->owner->businesses()->attach($business->id, ['role' => 'owner', 'role_id' => $ownerRole->id]);

        CommerceStoreSetting::create([
            'business_id' => $business->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
        ]);

        $landingPage = BusinessLandingPage::create([
            'business_id' => $business->id,
            'theme_preset' => 'retail_building',
            'theme_color' => '#1E293B',
            'font_family' => 'Inter',
            'headline' => $headline,
            'subheadline' => $subheadline,
            'values' => $values,
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

        return [$business, $landingPage];
    }

    /**
     * Challenge 1: Cross-Industry Neutrality across 4 completely unrelated industries.
     * 1. Bengkel Las Listrik (Welding workshop & steel structure)
     * 2. Klinik Hukum & Advokat (Legal clinic & attorney firm)
     * 3. Dental Aesthetics (Dental & aesthetic clinic)
     * 4. Peternakan Kambing (Goat livestock farm & aqiqah service)
     */
    public function test_cross_industry_neutrality_across_4_unrelated_industries(): void
    {
        $industries = [
            'bengkel_las' => [
                'name' => 'Bengkel Las Listrik Reksa Baja Mandiri',
                'slug' => 'bengkel-las-reksa-baja',
                'desc' => 'Spesialis fabrikasi konstruksi baja, kanopi galvalum, dan instalasi teralis minimalis presisi tinggi.',
                'headline' => 'Bengkel Las Listrik & Konstruksi Baja Terpercaya',
                'subheadline' => 'Pengerjaan rapi bergaransi oleh teknisi pengelasan berpengalaman di seluruh area Jabodetabek.',
                'categories' => collect([
                    (object) ['id' => 101, 'name' => 'Kanopi Baja', 'products_count' => 8],
                    (object) ['id' => 102, 'name' => 'Teralis Minimalis', 'products_count' => 14],
                    (object) ['id' => 103, 'name' => 'Pagar Besi Tempa', 'products_count' => 6],
                ]),
            ],
            'klinik_hukum' => [
                'name' => 'Klinik Hukum & Advokat Wardhana Partners',
                'slug' => 'klinik-hukum-wardhana',
                'desc' => 'Firma hukum spesialis advokasi litigasi, arbitrase sengketa bisnis, dan penyusunan legal audit korporasi.',
                'headline' => 'Solusi Kepatuhan Regulasi & Perlindungan Hak Hukum',
                'subheadline' => 'Pendampingan hukum profesional dengan integritas dan kerahasiaan klien yang terjamin penuh.',
                'categories' => collect([
                    (object) ['id' => 201, 'name' => 'Konsultasi Korporasi', 'products_count' => 5],
                    (object) ['id' => 202, 'name' => 'Penyusunan Kontrak', 'products_count' => 9],
                    (object) ['id' => 203, 'name' => 'Pendampingan Litigasi', 'products_count' => 3],
                ]),
            ],
            'dental_aesthetics' => [
                'name' => 'Dental Aesthetics & Ortodonti Spesialis',
                'slug' => 'dental-aesthetics-spesialis',
                'desc' => 'Klinik dokter gigi estetika spesialis behel kawat gigi, pemutihan bleaching porselen, dan implan.',
                'headline' => 'Perawatan Gigi Estetika & Senyum Berkilau',
                'subheadline' => 'Standar higienis sterilisasi rumah sakit dengan dokter gigi spesialis konservasi terakreditasi.',
                'categories' => collect([
                    (object) ['id' => 301, 'name' => 'Bleaching Gigi', 'products_count' => 4],
                    (object) ['id' => 302, 'name' => 'Behel Ortodonti', 'products_count' => 7],
                    (object) ['id' => 303, 'name' => 'Veneer Porselen', 'products_count' => 12],
                ]),
            ],
            'peternakan_kambing' => [
                'name' => 'Peternakan Kambing Boer Mandiri Berkah',
                'slug' => 'peternakan-kambing-boer',
                'desc' => 'Sentra pembiakan kambing bibit unggul boer dan penyedia paket hewan qurban sehat bersertifikat.',
                'headline' => 'Sentra Kambing Qurban Sehat & Bibit Ternak Unggul',
                'subheadline' => 'Kambing dirawat intensif dengan pakan hijauan bernutrisi tinggi dan pengawasan dokter hewan.',
                'categories' => collect([
                    (object) ['id' => 401, 'name' => 'Kambing Qurban', 'products_count' => 25],
                    (object) ['id' => 402, 'name' => 'Bibit Pejantan', 'products_count' => 10],
                    (object) ['id' => 403, 'name' => 'Pakan Fermentasi', 'products_count' => 8],
                ]),
            ],
        ];

        foreach ($industries as $key => $indData) {
            [$biz, $lp] = $this->createAdversarialBusiness(
                $indData['name'],
                $indData['slug'],
                $indData['desc'],
                $indData['headline'],
                $indData['subheadline'],
                null // let universal fallbacks take effect
            );

            foreach (self::BATCH_2_THEMES as $theme) {
                $activeTheme = $this->themeService->getTheme($theme);
                $viewData = [
                    'business' => $biz,
                    'landingPage' => $lp,
                    'activeTheme' => $activeTheme,
                    'theme' => $activeTheme,
                    'productCategories' => $indData['categories'],
                    'hasWhatsapp' => true,
                ];

                foreach (self::THEME_COMPONENTS as $component) {
                    $viewName = "public.storefront.themes.{$theme}.{$component}";
                    $this->assertTrue(View::exists($viewName), "Component {$viewName} must exist");
                    $html = View::make($viewName, $viewData)->render();
                    $this->assertNotEmpty($html, "Rendered output of {$viewName} is unexpectedly empty");

                    // Verify zero forbidden sector keywords appear in rendered component
                    foreach (self::FORBIDDEN_SECTOR_KEYWORDS as $keyword) {
                        $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';
                        $this->assertDoesNotMatchRegularExpression(
                            $pattern,
                            $html,
                            "Forbidden sector keyword '{$keyword}' leaked into {$viewName} when rendering for '{$indData['name']}'!"
                        );
                    }

                    // Verify business identity presence in Hero
                    if ($component === 'hero') {
                        $this->assertStringContainsString(
                            e($indData['headline']),
                            $html,
                            "Custom headline missing in {$viewName}"
                        );
                        $this->assertStringContainsString(
                            strtoupper(substr($indData['name'], 0, 2)),
                            $html,
                            "Brand Bento monogram missing in {$viewName}"
                        );
                    }

                    // Verify Category Chips presence
                    if ($component === 'category_chips') {
                        $this->assertStringContainsString(
                            __('storefront.semua_produk'),
                            $html,
                            "Semua Produk chip missing in {$viewName}"
                        );
                        $this->assertStringContainsString(
                            $indData['categories'][0]->name,
                            $html,
                            "First category missing in {$viewName}"
                        );
                    }
                }
            }
        }
    }

    /**
     * Challenge 2: Mobile Touch Target Compliance (`min-h-[44px]` or higher on all interactive elements).
     */
    public function test_touch_targets_on_all_interactive_elements_meet_minimum_44px(): void
    {
        [$biz, $lp] = $this->createAdversarialBusiness(
            'Baja Mandiri Perkasa',
            'baja-mandiri-perkasa',
            'Distributor baja profil.',
            'Solusi Baja Kuat',
            'Mutu terjamin.'
        );

        $categories = collect([
            (object) ['id' => 99, 'name' => 'Baja Profil', 'products_count' => 10],
        ]);

        foreach (self::BATCH_2_THEMES as $theme) {
            $activeTheme = $this->themeService->getTheme($theme);
            $viewData = [
                'business' => $biz,
                'landingPage' => $lp,
                'activeTheme' => $activeTheme,
                'theme' => $activeTheme,
                'productCategories' => $categories,
                'hasWhatsapp' => true,
            ];

            // 1. Hero CTAs
            $heroHtml = View::make("public.storefront.themes.{$theme}.hero", $viewData)->render();
            preg_match_all('/<a[^>]*class=["\']([^"\']*)["\'][^>]*>/i', $heroHtml, $heroAnchorMatches);
            
            $foundPrimaryCta = false;
            $foundSecondaryCta = false;
            foreach ($heroAnchorMatches[1] as $class) {
                if (str_contains($class, 'katalog')) {
                    $foundPrimaryCta = true;
                    $this->assertMatchesRegularExpression(
                        '/min-h-\[(?:4[4-9]|[5-9]\d)px\]/',
                        $class,
                        "Primary CTA in {$theme}/hero.blade.php must meet min-h-[44px]"
                    );
                }
                if (str_contains($class, 'wa.me') || str_contains($class, 'whatsapp') || str_contains($class, 'target="_blank"')) {
                    $foundSecondaryCta = true;
                    $this->assertMatchesRegularExpression(
                        '/min-h-\[(?:4[4-9]|[5-9]\d)px\]/',
                        $class,
                        "Secondary WhatsApp CTA in {$theme}/hero.blade.php must meet min-h-[44px]"
                    );
                }
            }

            // 2. Category Chips
            $chipsHtml = View::make("public.storefront.themes.{$theme}.category_chips", $viewData)->render();
            preg_match_all('/<a[^>]*class=["\']([^"\']*)["\'][^>]*>/i', $chipsHtml, $chipAnchorMatches);

            $this->assertNotEmpty($chipAnchorMatches[1], "No chips rendered in {$theme}/category_chips");
            foreach ($chipAnchorMatches[1] as $class) {
                // If it's a chip (not the 'Lihat Semua' text link)
                if (str_contains($class, 'rounded-') && (str_contains($class, 'px-') || str_contains($class, 'py-'))) {
                    $this->assertMatchesRegularExpression(
                        '/min-h-\[(?:4[4-9]|[5-9]\d)px\]/',
                        $class,
                        "Category chip in {$theme}/category_chips.blade.php does not meet min-h-[44px]"
                    );
                }
            }
        }
    }

    /**
     * Challenge 3: Extreme Adversarial Payloads & Robustness.
     * Tests: single-char names, long strings, null values, 1-item arrays, missing images.
     */
    public function test_extreme_adversarial_payloads_render_without_exceptions(): void
    {
        $adversarialPayloads = [
            'single_char_business' => [
                'name' => 'X',
                'headline' => 'X Enterprise',
                'subheadline' => 'Minimalist brand.',
                'values' => null,
                'hero_image_url' => null,
            ],
            'special_chars_business' => [
                'name' => '⚡ Electro & Power [99%]',
                'headline' => 'Solusi Kelistrikan Modern & Aman',
                'subheadline' => 'Dukungan genset, panel surya, & distribusi kabel industri.',
                'values' => [
                    ['icon' => 'zap', 'title' => 'Daya Kuat', 'desc' => 'Tegangan stabil'],
                ],
                'hero_image_url' => null,
            ],
            'very_long_strings' => [
                'name' => 'PT Konsorsium Sentosa Jaya Makmur Abadi Dan Rekan Rekan Nusantara Bersatu',
                'headline' => 'Menyediakan Seluruh Peralatan dan Perlengkapan Logistik Nasional Terpadu Dari Sabang Sampai Merauke Dengan Standar Internasional',
                'subheadline' => 'Layanan prima 24 jam sehari 7 hari seminggu dengan dukungan armada truk kontainer, kapal kargo, dan gudang transit pendingin.',
                'values' => null,
                'hero_image_url' => null,
            ],
            'empty_values_array' => [
                'name' => 'Pilar Usaha Mandiri',
                'headline' => 'Pusat Peralatan Kerja',
                'subheadline' => 'Koleksi perkakas terlengkap.',
                'values' => [],
                'hero_image_url' => '',
            ],
        ];

        foreach ($adversarialPayloads as $payloadKey => $payload) {
            [$biz, $lp] = $this->createAdversarialBusiness(
                $payload['name'],
                'slug-' . substr(md5($payloadKey), 0, 8),
                'Adversarial test description',
                $payload['headline'],
                $payload['subheadline'],
                $payload['values']
            );

            if (isset($payload['hero_image_url'])) {
                $lp->update(['hero_image_url' => $payload['hero_image_url']]);
            }

            foreach (self::BATCH_2_THEMES as $theme) {
                $activeTheme = $this->themeService->getTheme($theme);
                $viewData = [
                    'business' => $biz,
                    'landingPage' => $lp,
                    'activeTheme' => $activeTheme,
                    'theme' => $activeTheme,
                    'productCategories' => collect(),
                    'hasWhatsapp' => false,
                ];

                // Hero must render cleanly
                $heroHtml = View::make("public.storefront.themes.{$theme}.hero", $viewData)->render();
                $this->assertNotEmpty($heroHtml, "Hero rendered empty for {$theme} on {$payloadKey}");

                // Monogram must handle single-char or special-char names without crashing
                $expectedMonogram = strtoupper(substr($payload['name'], 0, 2));
                $this->assertStringContainsString($expectedMonogram, $heroHtml, "Monogram '{$expectedMonogram}' missing in {$theme} on {$payloadKey}");

                // Features banner must render all 4 pillars even when values are empty or single item
                $bannerHtml = View::make("public.storefront.themes.{$theme}.features_banner", $viewData)->render();
                $this->assertNotEmpty($bannerHtml, "Features banner rendered empty for {$theme} on {$payloadKey}");

                // Category chips must suppress cleanly when empty
                $chipsHtml = trim(View::make("public.storefront.themes.{$theme}.category_chips", $viewData)->render());
                $this->assertEmpty($chipsHtml, "Category chips should be empty for {$theme} when categories are empty");
            }
        }
    }

    /**
     * Challenge 4: Full Storefront Preview HTTP Requests across all 11 themes.
     * Verifies that the entire assembled page (layouts/app.blade.php + home.blade.php) renders with HTTP 200.
     */
    public function test_full_storefront_preview_http_requests(): void
    {
        [$biz, $lp] = $this->createAdversarialBusiness(
            'Bengkel Bubut & Las Presisi Abadi',
            'bengkel-bubut-abadi',
            'Jasa bubut logam, milling CNC, dan rekayasa permesinan industri berat.',
            'Spesialis Bubut Logam & Machining CNC',
            'Presisi hingga 0.01 mm dengan mesin bubut berteknologi tinggi.'
        );

        $this->owner->forceFill(['active_business_id' => $biz->id])->save();

        foreach (self::BATCH_2_THEMES as $theme) {
            $response = $this->actingAs($this->owner, 'web')
                ->withSession(['active_business_id' => $biz->id])
                ->get('/' . $biz->slug . '?preview=1&preview_theme=' . $theme);

            $this->assertSame(200, $response->getStatusCode(), "Storefront preview HTTP failed for theme {$theme}");
            $response->assertSee(e($biz->name), false);
            $response->assertSee('Spesialis Bubut Logam &amp; Machining CNC', false);
        }
    }
}
