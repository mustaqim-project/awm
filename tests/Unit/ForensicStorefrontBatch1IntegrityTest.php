<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

final class ForensicStorefrontBatch1IntegrityTest extends TestCase
{
    private const BATCH_1_THEMES = [
        'universal_storefront',
        'modern_umkm',
        'online_store',
        'business_corporate',
        'coffee_beverage',
        'agriculture_farm',
        'retail_fashion',
        'service_workshop',
        'food_restaurant',
        'health_beauty',
    ];

    private const COMPONENT_FILES = [
        'hero.blade.php',
        'features_banner.blade.php',
        'category_chips.blade.php',
    ];

    public function test_all_30_modified_blade_files_exist_and_pass_php_lint(): void
    {
        $checked = 0;
        foreach (self::BATCH_1_THEMES as $theme) {
            foreach (self::COMPONENT_FILES as $file) {
                $path = resource_path("views/public/storefront/themes/{$theme}/{$file}");
                $this->assertFileExists($path, "File must exist: {$path}");

                $output = [];
                $returnVar = 0;
                exec('php -l ' . escapeshellarg($path), $output, $returnVar);

                $this->assertSame(
                    0,
                    $returnVar,
                    "PHP lint error in {$path}: " . implode("\n", $output)
                );
                $checked++;
            }
        }

        $this->assertSame(30, $checked, 'Must verify strictly 30 files across Themes 1 to 10');
    }

    public function test_zero_unsplash_photos_in_all_batch_1_templates(): void
    {
        foreach (self::BATCH_1_THEMES as $theme) {
            foreach (self::COMPONENT_FILES as $file) {
                $path = resource_path("views/public/storefront/themes/{$theme}/{$file}");
                $content = file_get_contents($path);

                $this->assertStringNotContainsStringIgnoringCase(
                    'unsplash.com',
                    $content,
                    "Forbidden Unsplash URL detected in {$path}"
                );
                $this->assertStringNotContainsStringIgnoringCase(
                    'images.unsplash',
                    $content,
                    "Forbidden Unsplash reference detected in {$path}"
                );
            }
        }
    }

    public function test_zero_hardcoded_vertical_sector_copy_in_rendered_code(): void
    {
        $forbiddenPhrases = [
            'Tune-up Mesin',
            'Petik Segar Subuh',
            'Jahitan Rapi & Fitting',
            'Specialty Roastery',
            '80+ Cupping',
            'Bongkar Pasang',
            '100% Produk Lokal',
            'Corporate Enterprise',
            '10k+ Pasien',
        ];

        foreach (self::BATCH_1_THEMES as $theme) {
            foreach (self::COMPONENT_FILES as $file) {
                $path = resource_path("views/public/storefront/themes/{$theme}/{$file}");
                $content = file_get_contents($path);

                // Strip Blade comments {{-- ... --}} to focus on active code
                $activeCode = preg_replace('/\{\{--.*?--\}\}/s', '', $content);

                foreach ($forbiddenPhrases as $phrase) {
                    $this->assertStringNotContainsString(
                        $phrase,
                        $activeCode,
                        "Forbidden hardcoded sector phrase '{$phrase}' found in {$path}"
                    );
                }
            }
        }
    }

    public function test_all_10_hero_templates_have_dynamic_monogram_and_brand_bento_showcase(): void
    {
        foreach (self::BATCH_1_THEMES as $theme) {
            $path = resource_path("views/public/storefront/themes/{$theme}/hero.blade.php");
            $content = file_get_contents($path);

            $this->assertMatchesRegularExpression(
                '/substr\(\$business->name,\s*0,\s*2\)/',
                $content,
                "Dynamic monogram logic missing in {$theme}/hero.blade.php"
            );

            $this->assertStringContainsString(
                '$landingPage->getUniversalValues()',
                $content,
                "getUniversalValues() call missing in {$theme}/hero.blade.php"
            );

            $this->assertStringContainsString(
                '$business->name',
                $content,
                "Dynamic business name missing in {$theme}/hero.blade.php"
            );
        }
    }

    public function test_all_10_features_banners_loop_authentically_over_universal_values(): void
    {
        foreach (self::BATCH_1_THEMES as $theme) {
            $path = resource_path("views/public/storefront/themes/{$theme}/features_banner.blade.php");
            $content = file_get_contents($path);

            $this->assertStringContainsString(
                '$landingPage->getUniversalValues()',
                $content,
                "getUniversalValues() call missing in {$theme}/features_banner.blade.php"
            );

            $this->assertMatchesRegularExpression(
                '/@foreach\s*\(\s*\$(pillars|featureItems)/',
                $content,
                "Dynamic foreach loop missing in {$theme}/features_banner.blade.php"
            );
        }
    }

    public function test_all_10_category_chips_have_semua_produk_and_touch_target_compliance(): void
    {
        foreach (self::BATCH_1_THEMES as $theme) {
            $path = resource_path("views/public/storefront/themes/{$theme}/category_chips.blade.php");
            $content = file_get_contents($path);

            $this->assertStringContainsString(
                'storefront.semua_produk',
                $content,
                "Universal 'semua_produk' chip missing in {$theme}/category_chips.blade.php"
            );

            $this->assertStringContainsString(
                'min-h-[44px]',
                $content,
                "Thumb-zone 44px min-height missing in {$theme}/category_chips.blade.php"
            );

            $this->assertTrue(
                str_contains($content, "!request()->filled('category')") || str_contains($content, "!request('category')"),
                "Default active indicator missing in {$theme}/category_chips.blade.php"
            );

            $this->assertMatchesRegularExpression(
                '/request\(\'category\'\)\s*==\s*\$cat->id/',
                $content,
                "Active category highlighting missing in {$theme}/category_chips.blade.php"
            );
        }
    }
}
