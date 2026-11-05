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

final class StorefrontThemes6To10EmpiricalChallengeTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private BusinessLandingPage $landingPage;
    private StorefrontThemeService $themeService;

    /**
     * @var list<string>
     */
    private array $themes = [
        'agriculture_farm',
        'retail_fashion',
        'service_workshop',
        'food_restaurant',
        'health_beauty',
    ];

    /**
     * @var list<string>
     */
    private array $themeFiles = [
        'hero.blade.php',
        'features_banner.blade.php',
        'category_chips.blade.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);

        $this->themeService = app(StorefrontThemeService::class);

        $this->owner = User::create([
            'name' => 'Owner Universal Test',
            'email' => 'challenger.owner@cooca.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Klinik & Petcare Sejahtera',
            'slug' => 'klinik-petcare-sejahtera',
            'currency' => 'IDR',
            'is_active' => true,
            'description' => 'Solusi perawatan kesehatan hewan dan klinik terpadu dengan standar internasional.',
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
            'theme_preset' => 'agriculture_farm',
            'theme_color' => '#16a34a',
            'font_family' => 'Inter',
            'headline' => 'Perawatan Profesional & Terpercaya',
            'subheadline' => 'Didukung tim dokter dan tenaga ahli bersertifikasi',
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
     * Challenge 1: File Existence & PHP Syntax Lint across all 15 files in Themes 6-10.
     */
    public function test_themes_6_to_10_files_exist_and_pass_php_lint(): void
    {
        $baseDir = resource_path('views/public/storefront/themes');

        foreach ($this->themes as $theme) {
            foreach ($this->themeFiles as $file) {
                $path = $baseDir . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $file;
                $this->assertFileExists($path, "File $file missing in theme $theme");

                // Execute php -l syntax check
                $output = [];
                $returnCode = 0;
                exec('php -l ' . escapeshellarg($path), $output, $returnCode);
                $this->assertSame(0, $returnCode, "PHP syntax error in $path: " . implode("\n", $output));
            }
        }
    }

    /**
     * Challenge 2: Search via grep/regex across all 15 files in Themes 6-10 for forbidden sector phrases.
     */
    public function test_static_grep_for_forbidden_sector_phrases_and_unsplash(): void
    {
        $baseDir = resource_path('views/public/storefront/themes');

        $forbiddenTerms = [
            'images.unsplash.com',
            'Tune-up',
            'Petik Segar',
            'Jahitan Rapi',
            'OBD-II',
            'Mekanik',
        ];

        foreach ($this->themes as $theme) {
            foreach ($this->themeFiles as $file) {
                $filePath = $baseDir . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $file;
                $content = file_get_contents($filePath);
                $this->assertNotFalse($content, "Failed to read $filePath");

                // Check strictly for forbidden phrases
                foreach ($forbiddenTerms as $term) {
                    $pattern = '/' . preg_quote($term, '/') . '/i';
                    
                    // Allow term only if it's strictly within the informational header comments at the very top of file
                    // But in executable Blade body, forbidden term must be 0
                    if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                        foreach ($matches[0] as $match) {
                            $offset = $match[1];
                            $before = substr($content, 0, $offset);
                            // If before contains closing comment --}} then it's in the executable body!
                            if (str_contains($before, '--}}')) {
                                $this->fail("Forbidden sector term '{$term}' found in executable body of {$theme}/{$file} at offset {$offset}");
                            }
                        }
                    }

                    // For unsplash, it is strictly forbidden anywhere in the file (even in comments)
                    if (str_contains($term, 'unsplash')) {
                        $this->assertStringNotContainsStringIgnoringCase(
                            $term,
                            $content,
                            "Unsplash reference '{$term}' found in {$theme}/{$file}"
                        );
                    }
                }
            }
        }
    }

    /**
     * Challenge 3: Verify that all 15 files render cleanly under various combinations of:
     * - $landingPage->headline (populated, empty string, null)
     * - $landingPage->values (populated, empty array, null, malformed/sparse)
     * - $landingPage->hero_image_url (populated URL, empty string, null)
     */
    public function test_all_15_files_render_cleanly_under_all_parameter_combinations(): void
    {
        $categories = collect([
            (object) ['id' => 101, 'name' => 'Layanan Utama', 'products_count' => 8],
            (object) ['id' => 102, 'name' => 'Produk Penunjang', 'products_count' => 14],
        ]);

        $combinations = [
            'fully_populated' => [
                'headline' => 'Klinik Spesialis Hewan & Unggas',
                'subheadline' => 'Dokter spesialis bedah dan penanganan darurat 24 jam',
                'hero_image_url' => 'https://cooca.id/storage/uploads/custom_hero.jpg',
                'values' => [
                    ['icon' => 'activity', 'title' => 'Dokter Siaga', 'desc' => 'Pelayanan medis 24/7'],
                    ['icon' => 'award', 'title' => 'Sertifikasi ISO', 'desc' => 'Fasilitas steril higienis'],
                    ['icon' => 'heart-pulse', 'title' => 'Cepat & Ramah', 'desc' => 'Prioritas keselamatan pasien'],
                    ['icon' => 'sparkles', 'title' => 'Peralatan Modern', 'desc' => 'USG dan rontgen digital'],
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
                'headline' => 'Pusat Distribusi Alat Medis Terbesar',
                'subheadline' => 'Menyediakan ribuan alat kesehatan siap kirim',
                'hero_image_url' => null,
                'values' => null,
            ],
            'sparse_single_value_with_image' => [
                'headline' => 'Layanan Konsultasi Hukum & Bisnis',
                'subheadline' => 'Bimbingan legalitas usaha dan paten brand',
                'hero_image_url' => 'https://cooca.id/storage/uploads/legal_office.jpg',
                'values' => [
                    ['icon' => 'scale', 'title' => 'Advokat Berpengalaman', 'desc' => 'Pendampingan perkara hukum'],
                ],
            ],
        ];

        foreach ($this->themes as $theme) {
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

                // 1. Render hero
                $heroView = "public.storefront.themes.{$theme}.hero";
                $this->assertTrue(View::exists($heroView), "View $heroView must exist");
                $heroHtml = View::make($heroView, $viewData)->render();
                $this->assertNotEmpty($heroHtml, "Theme {$theme} hero rendered empty HTML on {$comboName}");

                // In all_null or null hero, Bento fallback card must render with business initials
                if ($comboData['hero_image_url'] === null || $comboData['hero_image_url'] === '') {
                    $initials = strtoupper(substr($this->business->name, 0, 2));
                    $this->assertStringContainsString($initials, $heroHtml, "Bento fallback initials missing in {$theme} on {$comboName}");
                    $this->assertStringNotContainsString('images.unsplash.com', $heroHtml, "Unsplash leaked in {$theme} on {$comboName}");
                } else {
                    $this->assertStringContainsString($comboData['hero_image_url'], $heroHtml, "Hero image URL missing in {$theme} on {$comboName}");
                }

                // If headline is null/empty, business name fallback must be used
                if (empty($comboData['headline'])) {
                    $this->assertStringContainsString(e($this->business->name), $heroHtml, "Fallback business name missing in {$theme} on {$comboName}");
                } else {
                    $this->assertStringContainsString(e($comboData['headline']), $heroHtml, "Custom headline missing in {$theme} on {$comboName}");
                }

                // 2. Render features_banner
                $featuresView = "public.storefront.themes.{$theme}.features_banner";
                $this->assertTrue(View::exists($featuresView), "View $featuresView must exist");
                $featuresHtml = View::make($featuresView, $viewData)->render();
                $this->assertNotEmpty($featuresHtml, "Theme {$theme} features_banner rendered empty HTML on {$comboName}");

                // When custom values are provided, verify custom titles appear
                if (!empty($comboData['values'])) {
                    foreach ($comboData['values'] as $item) {
                        $this->assertStringContainsString(e($item['title']), $featuresHtml, "Custom feature title missing in {$theme} on {$comboName}");
                    }
                }

                // 3. Render category_chips
                $chipsView = "public.storefront.themes.{$theme}.category_chips";
                $this->assertTrue(View::exists($chipsView), "View $chipsView must exist");
                $chipsHtml = View::make($chipsView, $viewData)->render();
                $this->assertNotEmpty($chipsHtml, "Theme {$theme} category_chips rendered empty HTML on {$comboName}");
                $this->assertStringContainsString(__('storefront.semua_produk'), $chipsHtml, "Semua Produk chip missing in {$theme} on {$comboName}");
                $this->assertStringContainsString('Layanan Utama', $chipsHtml, "Category title missing in {$theme} on {$comboName}");
                $this->assertStringContainsString('min-h-[44px]', $chipsHtml, "44px touch target compliance missing in {$theme} on {$comboName}");
            }
        }
    }

    /**
     * Challenge 4: Category Chips suppression when product categories are empty.
     */
    public function test_category_chips_cleanly_suppresses_when_categories_empty(): void
    {
        $emptyCategories = collect();

        foreach ($this->themes as $theme) {
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
     * Challenge 5: Cross-industry neutrality empirical verification.
     * When a non-related business selects Themes 6 to 10, zero sector-specific copy appears on rendered storefront.
     */
    public function test_cross_industry_neutrality_rendering_on_live_storefront(): void
    {
        // Merchant is a petcare clinic
        $this->landingPage->update([
            'headline' => 'Klinik Petcare Bergaransi',
            'subheadline' => 'Perawatan terpercaya untuk anabul kesayangan',
            'values' => null, // rely on universal fallbacks
            'hero_image_url' => null, // rely on bento card fallback
        ]);

        $forbiddenKeywords = [
            'Tune-up',
            'Petik Segar',
            'Jahitan Rapi',
            'OBD-II',
            'Mekanik',
        ];

        foreach ($this->themes as $theme) {
            $activeTheme = $this->themeService->getTheme($theme);
            $viewData = [
                'business' => $this->business,
                'landingPage' => $this->landingPage,
                'activeTheme' => $activeTheme,
                'theme' => $activeTheme,
                'productCategories' => collect([(object) ['id' => 1, 'name' => 'Konsultasi']]),
                'hasWhatsapp' => true,
            ];

            // 1. Verify theme components have zero unsplash and zero forbidden sector keywords in rendered HTML
            foreach ($this->themeFiles as $file) {
                $filePath = resource_path("views/public/storefront/themes/{$theme}/{$file}");
                $fileContent = file_get_contents($filePath);
                $this->assertStringNotContainsStringIgnoringCase('unsplash', $fileContent, "Unsplash found in {$theme}/{$file}");

                $viewName = 'public.storefront.themes.' . $theme . '.' . str_replace('.blade.php', '', $file);
                $renderedHtml = View::make($viewName, $viewData)->render();

                foreach ($forbiddenKeywords as $badWord) {
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
            $response->assertSee('Klinik &amp; Petcare Sejahtera', false);
            $response->assertSee('Klinik Petcare Bergaransi');
        }
    }
}
