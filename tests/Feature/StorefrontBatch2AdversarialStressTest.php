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
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Adversarial empirical challenge and stress test suite for Milestone 2B (Batch 2: 11 Themes).
 * Covers extreme corner cases, XSS resilience, suppression conditions, and graphic showcase integrity.
 */
final class StorefrontBatch2AdversarialStressTest extends TestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);

        $this->themeService = app(StorefrontThemeService::class);

        $this->owner = User::create([
            'name' => 'Empirical Challenger Batch 2',
            'email' => 'critic.batch2@cooca.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Kue & Roti "L\'Amour" <Artisan>',
            'slug' => 'kue-roti-lamour',
            'currency' => 'IDR',
            'is_active' => true,
            'description' => 'Spesialis roti sourdough & kue tart artisanal <script>alert(1)</script> & "freshly baked".',
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
            'theme_preset' => 'bakery_cake',
            'theme_color' => '#D97706',
            'font_family' => 'Playfair Display',
            'headline' => '<script>alert("XSS")</script> Toko & Bakeri "Top"',
            'subheadline' => 'Kue manis & gurih <style>body{color:red}</style> dengan diskon "50%" & garansi mutu.',
            'announcement_badge' => '<b>Promo Spesial!</b> 100% Halal & Fresh',
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
            'values' => [
                ['icon' => 'shield-check', 'title' => 'Mutu "Terjamin" & Bersertifikat', 'desc' => 'Standar ISO & BPOM <verified>'],
                ['icon' => 'truck', 'title' => 'Kirim "Same-Day" <Instant>', 'desc' => 'Armada berpendingin aman & cepat'],
                ['icon' => 'heart', 'title' => 'Bahan 100% "Alami"', 'desc' => 'Tanpa pengawet & pemanis buatan <safe>'],
                ['icon' => 'sparkles', 'title' => 'Resep "Legendaris"', 'desc' => 'Warisan 3 generasi sejak 1975 & terpercaya'],
            ],
        ]);
    }

    /**
     * Test 1: XSS resilience and special characters escaping across all 11 themes.
     * Raw unescaped scripts and HTML tags MUST NOT appear in the rendered HTML output.
     */
    public function test_adversarial_xss_and_special_character_escaping_across_all_11_themes(): void
    {
        $categories = collect([
            (object) ['id' => 101, 'name' => 'Kue Basah & "Pastry" <Hot>', 'products_count' => 15],
            (object) ['id' => 102, 'name' => 'Roti & Pastry <script>alert(2)</script>', 'products_count' => 8],
        ]);

        foreach (self::BATCH_2_THEMES as $theme) {
            $activeTheme = $this->themeService->getTheme($theme);
            $viewData = [
                'business' => $this->business,
                'landingPage' => $this->landingPage,
                'activeTheme' => $activeTheme,
                'theme' => $activeTheme,
                'productCategories' => $categories,
                'hasWhatsapp' => true,
            ];

            // 1. Hero Section
            $heroHtml = View::make("public.storefront.themes.{$theme}.hero", $viewData)->render();
            $this->assertNotEmpty($heroHtml, "Hero HTML empty in {$theme}");
            // Verify NO raw unescaped script tag leaked
            $this->assertStringNotContainsString('<script>alert("XSS")</script>', $heroHtml, "Raw XSS payload leaked into {$theme} hero");
            $this->assertStringNotContainsString('<style>body{color:red}</style>', $heroHtml, "Raw style injection leaked into {$theme} hero");
            // Verify properly escaped
            $this->assertStringContainsString('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', $heroHtml, "Escaped headline missing in {$theme} hero");

            // 2. Features Banner
            $featuresHtml = View::make("public.storefront.themes.{$theme}.features_banner", $viewData)->render();
            $this->assertNotEmpty($featuresHtml, "Features banner HTML empty in {$theme}");
            $this->assertStringNotContainsString('<verified>', $featuresHtml, "Raw tag <verified> leaked into {$theme} features_banner");
            $this->assertStringNotContainsString('<safe>', $featuresHtml, "Raw tag <safe> leaked into {$theme} features_banner");

            // 3. Category Chips
            $chipsHtml = View::make("public.storefront.themes.{$theme}.category_chips", $viewData)->render();
            $this->assertNotEmpty($chipsHtml, "Category chips HTML empty in {$theme}");
            $this->assertStringNotContainsString('<script>alert(2)</script>', $chipsHtml, "Raw XSS tag leaked into {$theme} category_chips");
            $this->assertStringContainsString('&lt;script&gt;alert(2)&lt;/script&gt;', $chipsHtml, "Escaped category name missing in {$theme} category_chips");
        }
    }

    /**
     * Test 2: Inactive catalog state suppresses catalog CTA in hero and completely hides category chips.
     */
    public function test_inactive_catalog_suppresses_cta_and_hides_category_chips(): void
    {
        // Deactivate catalog
        $this->landingPage->update([
            'active_pages' => [
                'home' => true,
                'catalog' => false,
                'about' => true,
                'contact' => true,
            ],
        ]);

        $categories = collect([
            (object) ['id' => 1, 'name' => 'Menu Utama', 'products_count' => 10],
        ]);

        foreach (self::BATCH_2_THEMES as $theme) {
            $activeTheme = $this->themeService->getTheme($theme);
            $viewData = [
                'business' => $this->business,
                'landingPage' => $this->landingPage,
                'activeTheme' => $activeTheme,
                'theme' => $activeTheme,
                'productCategories' => $categories,
                'hasWhatsapp' => true,
            ];

            // 1. Hero: primary CTA button (which links to catalog) should NOT be displayed
            $heroHtml = View::make("public.storefront.themes.{$theme}.hero", $viewData)->render();
            $catalogUrl = url('/' . $this->business->slug . '/katalog');
            $this->assertStringNotContainsString("href=\"{$catalogUrl}\"", $heroHtml, "Catalog link rendered in {$theme} hero despite catalog being inactive");

            // 2. Category Chips: must be completely empty
            $chipsHtml = trim(View::make("public.storefront.themes.{$theme}.category_chips", $viewData)->render());
            $this->assertEmpty($chipsHtml, "Category chips should be completely empty when catalog page is inactive in {$theme}");
        }
    }

    /**
     * Test 3: Missing WhatsApp suppresses secondary CTA across all 11 themes.
     */
    public function test_missing_whatsapp_suppresses_secondary_cta(): void
    {
        $categories = collect([
            (object) ['id' => 1, 'name' => 'Kategori 1'],
        ]);

        foreach (self::BATCH_2_THEMES as $theme) {
            $activeTheme = $this->themeService->getTheme($theme);
            $viewData = [
                'business' => $this->business,
                'landingPage' => $this->landingPage,
                'activeTheme' => $activeTheme,
                'theme' => $activeTheme,
                'productCategories' => $categories,
                'hasWhatsapp' => false,
            ];

            $heroHtml = View::make("public.storefront.themes.{$theme}.hero", $viewData)->render();
            // WhatsApp message icon or wa.me should not be present in the interactive CTAs
            $this->assertStringNotContainsString('api.whatsapp.com', $heroHtml, "WhatsApp link found in {$theme} when hasWhatsapp is false");
            $this->assertStringNotContainsString('wa.me', $heroHtml, "wa.me link found in {$theme} when hasWhatsapp is false");
        }
    }

    /**
     * Test 4: Brand Bento Graphic Showcase renders dynamically when hero image is null or empty.
     * Verifies business monogram, fallback headline/subheadline, zero Unsplash, and status badge.
     */
    public function test_brand_bento_graphic_showcase_dynamic_fallback_integrity(): void
    {
        // Nullify hero image and custom values
        $this->landingPage->update([
            'hero_image_url' => null,
            'about_image_url' => null,
            'headline' => null, // fallback to business name
            'subheadline' => null, // fallback to business description
            'values' => null, // fallback to universal 4 pillars
        ]);

        $categories = collect([
            (object) ['id' => 1, 'name' => 'Kategori A'],
        ]);

        foreach (self::BATCH_2_THEMES as $theme) {
            $activeTheme = $this->themeService->getTheme($theme);
            $viewData = [
                'business' => $this->business,
                'landingPage' => $this->landingPage,
                'activeTheme' => $activeTheme,
                'theme' => $activeTheme,
                'productCategories' => $categories,
                'hasWhatsapp' => true,
            ];

            $heroHtml = View::make("public.storefront.themes.{$theme}.hero", $viewData)->render();

            // 1. Monogram of business name: KU (from "Kue & Roti...")
            $initials = strtoupper(substr($this->business->name, 0, 2));
            $this->assertStringContainsString($initials, $heroHtml, "Monogram '{$initials}' missing in Brand Bento of {$theme}");

            // 2. Zero Unsplash
            $this->assertStringNotContainsString('unsplash', $heroHtml, "Unsplash leaked into {$theme} Brand Bento");

            // 3. Fallback business name in headline
            $this->assertStringContainsString(e($this->business->name), $heroHtml, "Business name missing in {$theme} hero fallback");

            // 4. Fallback description in subheadline
            $this->assertStringContainsString(e($this->business->description), $heroHtml, "Business description missing in {$theme} hero fallback");

            // 5. Active status badge
            $this->assertStringContainsString(__('storefront.hero.active_status'), $heroHtml, "Active status badge missing in {$theme} Brand Bento");
        }
    }

    /**
     * Test 5: Category chips suppression with empty collection and active filter highlighting.
     */
    public function test_category_chips_suppression_and_filter_highlighting(): void
    {
        // 1. Suppression when empty
        foreach (self::BATCH_2_THEMES as $theme) {
            $activeTheme = $this->themeService->getTheme($theme);
            $viewData = [
                'business' => $this->business,
                'landingPage' => $this->landingPage,
                'activeTheme' => $activeTheme,
                'theme' => $activeTheme,
                'productCategories' => collect([]),
                'hasWhatsapp' => true,
            ];

            $chipsHtml = trim(View::make("public.storefront.themes.{$theme}.category_chips", $viewData)->render());
            $this->assertEmpty($chipsHtml, "Category chips did not suppress on empty collection in {$theme}");
        }

        // 2. Active filter highlighting with 15 categories (test take(12))
        $manyCategories = collect();
        for ($i = 1; $i <= 15; $i++) {
            $manyCategories->push((object) [
                'id' => 300 + $i,
                'name' => "Kategori Ke-{$i}",
                'products_count' => $i * 2,
            ]);
        }

        // Simulate active category filter = 305
        Request::instance()->query->set('category', '305');

        foreach (self::BATCH_2_THEMES as $theme) {
            $activeTheme = $this->themeService->getTheme($theme);
            $viewData = [
                'business' => $this->business,
                'landingPage' => $this->landingPage,
                'activeTheme' => $activeTheme,
                'theme' => $activeTheme,
                'productCategories' => $manyCategories,
                'hasWhatsapp' => true,
            ];

            $chipsHtml = View::make("public.storefront.themes.{$theme}.category_chips", $viewData)->render();

            // Kategori 1 to 12 should appear, 13 to 15 must NOT appear (take(12))
            $this->assertStringContainsString('Kategori Ke-1', $chipsHtml, "Category 1 missing in {$theme}");
            $this->assertStringContainsString('Kategori Ke-12', $chipsHtml, "Category 12 missing in {$theme}");
            $this->assertStringNotContainsString('Kategori Ke-13', $chipsHtml, "Category 13 should be excluded by take(12) in {$theme}");
            $this->assertStringNotContainsString('Kategori Ke-14', $chipsHtml, "Category 14 should be excluded by take(12) in {$theme}");
            $this->assertStringNotContainsString('Kategori Ke-15', $chipsHtml, "Category 15 should be excluded by take(12) in {$theme}");

            // Category 305 link must exist
            $this->assertStringContainsString('category=305', $chipsHtml, "Category 305 link missing in {$theme}");

            // Touch target compliance
            $this->assertMatchesRegularExpression('/min-h-\[(?:44|46|48)px\]/', $chipsHtml, "Touch target compliance missing in {$theme}");
        }
    }
}
