<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Support\Facades\App;
use Tests\TestCase;

final class StorefrontEmpiricalTranslationChallengeTest extends TestCase
{
    private array $idTranslations;
    private array $enTranslations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->idTranslations = require base_path('lang/id/storefront.php');
        $this->enTranslations = require base_path('lang/en/storefront.php');
    }

    /**
     * Helper to recursively flatten an array to dot notation keys.
     */
    private function flattenKeys(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $fullKey = $prefix === '' ? (string)$key : "{$prefix}.{$key}";
            if (is_array($value)) {
                $result[$fullKey] = '[ARRAY]';
                $result = array_merge($result, $this->flattenKeys($value, $fullKey));
            } else {
                $result[$fullKey] = $value;
            }
        }
        return $result;
    }

    /**
     * Helper to get leaf-only flattened keys.
     */
    private function flattenLeafKeys(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $fullKey = $prefix === '' ? (string)$key : "{$prefix}.{$key}";
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenLeafKeys($value, $fullKey));
            } else {
                $result[$fullKey] = $value;
            }
        }
        return $result;
    }

    /**
     * 1. Challenge Key Parity for Section: badges
     */
    public function test_badges_section_has_strict_key_parity_and_non_empty_values(): void
    {
        $this->assertArrayHasKey('badges', $this->idTranslations, 'badges section missing in lang/id/storefront.php');
        $this->assertArrayHasKey('badges', $this->enTranslations, 'badges section missing in lang/en/storefront.php');

        $idBadges = $this->idTranslations['badges'];
        $enBadges = $this->enTranslations['badges'];

        $this->assertIsArray($idBadges);
        $this->assertIsArray($enBadges);

        $idKeys = array_keys($idBadges);
        $enKeys = array_keys($enBadges);
        sort($idKeys);
        sort($enKeys);

        $this->assertSame($idKeys, $enKeys, 'Keys in badges section do not match between ID and EN');

        $expectedKeys = [
            'active_status',
            'fast_service',
            'quality_guaranteed',
            'secure_transaction',
            'verified_official',
        ];
        $this->assertSame($expectedKeys, $idKeys, 'badges keys must contain all required badges from blueprint');

        foreach ($expectedKeys as $key) {
            $this->assertNotEmpty($idBadges[$key], "ID badges.{$key} is empty");
            $this->assertNotEmpty($enBadges[$key], "EN badges.{$key} is empty");
            $this->assertNotSame($idBadges[$key], $enBadges[$key], "ID and EN badges.{$key} should not be identical (untranslated)");
        }
    }

    /**
     * 2. Challenge Key Parity for Section: features
     */
    public function test_features_section_has_strict_key_parity_and_non_empty_values(): void
    {
        $this->assertArrayHasKey('features', $this->idTranslations, 'features section missing in lang/id/storefront.php');
        $this->assertArrayHasKey('features', $this->enTranslations, 'features section missing in lang/en/storefront.php');

        $idFeatures = $this->idTranslations['features'];
        $enFeatures = $this->enTranslations['features'];

        $this->assertIsArray($idFeatures);
        $this->assertIsArray($enFeatures);

        $idKeys = array_keys($idFeatures);
        $enKeys = array_keys($enFeatures);
        sort($idKeys);
        sort($enKeys);

        $this->assertSame($idKeys, $enKeys, 'Keys in features section do not match between ID and EN');

        $expectedKeys = [
            'care_desc',
            'customer_care',
            'delivery_desc',
            'easy_payment',
            'official_desc',
            'official_store',
            'payment_desc',
            'secure_delivery',
        ];
        $this->assertSame($expectedKeys, $idKeys, 'features keys must contain all 4 pillars and descriptions');

        foreach ($expectedKeys as $key) {
            $this->assertNotEmpty($idFeatures[$key], "ID features.{$key} is empty");
            $this->assertNotEmpty($enFeatures[$key], "EN features.{$key} is empty");
            $this->assertNotSame($idFeatures[$key], $enFeatures[$key], "ID and EN features.{$key} should not be identical (untranslated)");
        }
    }

    /**
     * 3. Challenge Key Parity for Section: catalog
     */
    public function test_catalog_section_new_keys_parity_and_non_empty_values(): void
    {
        $this->assertArrayHasKey('catalog', $this->idTranslations, 'catalog section missing in lang/id/storefront.php');
        $this->assertArrayHasKey('catalog', $this->enTranslations, 'catalog section missing in lang/en/storefront.php');

        $idCatalog = $this->idTranslations['catalog'];
        $enCatalog = $this->enTranslations['catalog'];

        $newRequiredKeys = [
            'pilih_kategori_filter',
            'semua_produk',
            'kategori_pilihan',
        ];

        foreach ($newRequiredKeys as $key) {
            $this->assertArrayHasKey($key, $idCatalog, "catalog.{$key} missing in ID");
            $this->assertArrayHasKey($key, $enCatalog, "catalog.{$key} missing in EN");
            $this->assertNotEmpty($idCatalog[$key], "ID catalog.{$key} is empty");
            $this->assertNotEmpty($enCatalog[$key], "EN catalog.{$key} is empty");
            $this->assertNotSame($idCatalog[$key], $enCatalog[$key], "ID and EN catalog.{$key} should be translated");
        }
    }

    /**
     * 4. Challenge Key Parity for Section: empty
     */
    public function test_empty_section_parity_and_dual_notation_integrity(): void
    {
        $this->assertArrayHasKey('empty', $this->idTranslations, 'empty section missing in lang/id/storefront.php');
        $this->assertArrayHasKey('empty', $this->enTranslations, 'empty section missing in lang/en/storefront.php');

        $idEmpty = $this->idTranslations['empty'];
        $enEmpty = $this->enTranslations['empty'];

        // Test Flat Keys in empty
        $flatSubkeys = [
            'catalog_title',
            'catalog_desc',
            'catalog_action',
            'products_title',
            'products_desc',
            'products_action',
            'cart_title',
            'cart_desc',
            'cart_action',
        ];

        foreach ($flatSubkeys as $k) {
            $this->assertArrayHasKey($k, $idEmpty, "Flat key empty.{$k} missing in ID");
            $this->assertArrayHasKey($k, $enEmpty, "Flat key empty.{$k} missing in EN");
            $this->assertNotEmpty($idEmpty[$k], "ID empty.{$k} is empty");
            $this->assertNotEmpty($enEmpty[$k], "EN empty.{$k} is empty");
            $this->assertNotSame($idEmpty[$k], $enEmpty[$k], "ID and EN empty.{$k} should be translated");
        }

        // Test Nested Sub-arrays in empty
        $nestedSubarrays = ['catalog', 'products', 'cart'];
        $nestedFields = ['title', 'desc', 'action'];

        foreach ($nestedSubarrays as $sub) {
            $this->assertArrayHasKey($sub, $idEmpty, "Nested array empty.{$sub} missing in ID");
            $this->assertArrayHasKey($sub, $enEmpty, "Nested array empty.{$sub} missing in EN");
            $this->assertIsArray($idEmpty[$sub], "ID empty.{$sub} must be an array");
            $this->assertIsArray($enEmpty[$sub], "EN empty.{$sub} must be an array");

            foreach ($nestedFields as $field) {
                $this->assertArrayHasKey($field, $idEmpty[$sub], "ID empty.{$sub}.{$field} missing");
                $this->assertArrayHasKey($field, $enEmpty[$sub], "EN empty.{$sub}.{$field} missing");
                $this->assertNotEmpty($idEmpty[$sub][$field], "ID empty.{$sub}.{$field} is empty");
                $this->assertNotEmpty($enEmpty[$sub][$field], "EN empty.{$sub}.{$field} is empty");

                // Strict equality between flat key and nested key
                $flatEquivalent = "{$sub}_{$field}";
                $this->assertSame(
                    $idEmpty[$flatEquivalent],
                    $idEmpty[$sub][$field],
                    "ID mismatch between flat key empty.{$flatEquivalent} and nested empty.{$sub}.{$field}"
                );
                $this->assertSame(
                    $enEmpty[$flatEquivalent],
                    $enEmpty[$sub][$field],
                    "EN mismatch between flat key empty.{$flatEquivalent} and nested empty.{$sub}.{$field}"
                );
            }
        }
    }

    /**
     * 5. Challenge Root Alias: pilih_kategori_filter
     */
    public function test_root_alias_pilih_kategori_filter_matches_catalog_key(): void
    {
        $this->assertArrayHasKey('pilih_kategori_filter', $this->idTranslations);
        $this->assertArrayHasKey('pilih_kategori_filter', $this->enTranslations);

        $this->assertSame(
            $this->idTranslations['catalog']['pilih_kategori_filter'],
            $this->idTranslations['pilih_kategori_filter'],
            'ID root alias pilih_kategori_filter must match catalog.pilih_kategori_filter'
        );

        $this->assertSame(
            $this->enTranslations['catalog']['pilih_kategori_filter'],
            $this->enTranslations['pilih_kategori_filter'],
            'EN root alias pilih_kategori_filter must match catalog.pilih_kategori_filter'
        );
    }

    /**
     * 6. Empirical Test: Dot notation access via Laravel trans() / __()
     */
    public function test_dot_notation_access_resolves_cleanly_without_leakage(): void
    {
        App::setLocale('id');

        // Test flat access
        $this->assertSame('Kualitas Terjamin', __('storefront.badges.quality_guaranteed'));
        $this->assertSame('Transaksi Aman', __('storefront.badges.secure_transaction'));
        $this->assertSame('Layanan Cepat', __('storefront.badges.fast_service'));
        $this->assertSame('Toko Resmi Terverifikasi', __('storefront.badges.verified_official'));
        $this->assertSame('Aktif Beroperasi', __('storefront.badges.active_status'));

        $this->assertSame('Outlet Resmi Terverifikasi', __('storefront.features.official_store'));
        $this->assertSame('Pembayaran Mudah & Aman', __('storefront.features.easy_payment'));
        $this->assertSame('Pengiriman Cepat & Aman', __('storefront.features.secure_delivery'));
        $this->assertSame('Layanan Pelanggan Siaga', __('storefront.features.customer_care'));

        // Test flat empty keys
        $this->assertSame('Keranjang Belanja Masih Kosong', __('storefront.empty.cart_title'));
        $this->assertSame('Belum Ada Produk Tersedia', __('storefront.empty.products_title'));
        $this->assertSame('Produk Tidak Ditemukan', __('storefront.empty.catalog_title'));

        // Test nested dot notation empty keys
        $this->assertSame('Keranjang Belanja Masih Kosong', __('storefront.empty.cart.title'));
        $this->assertSame('Belum Ada Produk Tersedia', __('storefront.empty.products.title'));
        $this->assertSame('Produk Tidak Ditemukan', __('storefront.empty.catalog.title'));

        // Test catalog keys
        $this->assertSame('Pilih kategori untuk memfilter etalase belanja', __('storefront.catalog.pilih_kategori_filter'));
        $this->assertSame('Semua Produk', __('storefront.catalog.semua_produk'));
        $this->assertSame('Kategori Pilihan', __('storefront.catalog.kategori_pilihan'));

        // Test root alias
        $this->assertSame('Pilih kategori untuk memfilter etalase belanja', __('storefront.pilih_kategori_filter'));
    }

    /**
     * 7. Empirical Test: Runtime Locale Switching (ID <-> EN <-> ID)
     */
    public function test_runtime_locale_switching_behavior(): void
    {
        // 1. Initial State: Indonesian
        App::setLocale('id');
        $this->assertSame('id', App::getLocale());
        $this->assertSame('Kualitas Terjamin', __('storefront.badges.quality_guaranteed'));
        $this->assertSame('Outlet Resmi Terverifikasi', __('storefront.features.official_store'));
        $this->assertSame('Keranjang Belanja Masih Kosong', __('storefront.empty.cart_title'));
        $this->assertSame('Keranjang Belanja Masih Kosong', __('storefront.empty.cart.title'));
        $this->assertSame('Pilih kategori untuk memfilter etalase belanja', __('storefront.catalog.pilih_kategori_filter'));

        // 2. Switch to English at runtime
        App::setLocale('en');
        $this->assertSame('en', App::getLocale());
        $this->assertSame('Quality Guaranteed', __('storefront.badges.quality_guaranteed'));
        $this->assertSame('Verified Official Store', __('storefront.features.official_store'));
        $this->assertSame('Your Shopping Cart is Empty', __('storefront.empty.cart_title'));
        $this->assertSame('Your Shopping Cart is Empty', __('storefront.empty.cart.title'));
        $this->assertSame('Select a category to filter the store catalog', __('storefront.catalog.pilih_kategori_filter'));

        // 3. Switch back to Indonesian at runtime
        App::setLocale('id');
        $this->assertSame('id', App::getLocale());
        $this->assertSame('Kualitas Terjamin', __('storefront.badges.quality_guaranteed'));
        $this->assertSame('Outlet Resmi Terverifikasi', __('storefront.features.official_store'));
        $this->assertSame('Keranjang Belanja Masih Kosong', __('storefront.empty.cart_title'));
        $this->assertSame('Keranjang Belanja Masih Kosong', __('storefront.empty.cart.title'));

        // 4. Test explicit locale argument override
        $this->assertSame('Quality Guaranteed', __('storefront.badges.quality_guaranteed', [], 'en'));
        $this->assertSame('Kualitas Terjamin', __('storefront.badges.quality_guaranteed', [], 'id'));
    }

    /**
     * 8. Empirical Test: Parameter Replacements in Catalog
     */
    public function test_parameter_replacements_in_catalog(): void
    {
        // ID parameter replacement
        $idProductsFound = __('storefront.catalog.products_found', ['count' => 12], 'id');
        $this->assertSame('12 Produk Ditemukan', $idProductsFound);

        $idInStock = __('storefront.catalog.in_stock', ['count' => 5], 'id');
        $this->assertSame('Tersedia 5 unit', $idInStock);

        // EN parameter replacement
        $enProductsFound = __('storefront.catalog.products_found', ['count' => 12], 'en');
        $this->assertSame('12 Products Found', $enProductsFound);

        $enInStock = __('storefront.catalog.in_stock', ['count' => 5], 'en');
        $this->assertSame('5 units available', $enInStock);
    }

    /**
     * 9. Adversarial Stress-Test: Missing key returns raw key string, not exception or null
     */
    public function test_missing_key_graceful_handling(): void
    {
        $nonExistent = __('storefront.badges.non_existent_key_xyz', [], 'id');
        $this->assertSame('storefront.badges.non_existent_key_xyz', $nonExistent);

        $nonExistentNested = __('storefront.empty.cart.non_existent_action', [], 'en');
        $this->assertSame('storefront.empty.cart.non_existent_action', $nonExistentNested);
    }

    /**
     * 10. Adversarial Stress-Test: Array return type vs string return type
     */
    public function test_array_return_types_for_parents(): void
    {
        // When accessing a parent node directly
        $badgesArray = __('storefront.badges', [], 'id');
        $this->assertIsArray($badgesArray);
        $this->assertArrayHasKey('quality_guaranteed', $badgesArray);

        $emptyCartArray = __('storefront.empty.cart', [], 'id');
        $this->assertIsArray($emptyCartArray);
        $this->assertArrayHasKey('title', $emptyCartArray);
    }

    /**
     * 11. Comprehensive Global Parity Audit: Check entire dictionary for unaligned keys
     */
    public function test_global_leaf_key_parity_report(): void
    {
        $idLeafKeys = array_keys($this->flattenLeafKeys($this->idTranslations));
        $enLeafKeys = array_keys($this->flattenLeafKeys($this->enTranslations));

        sort($idLeafKeys);
        sort($enLeafKeys);

        $missingInEn = array_diff($idLeafKeys, $enLeafKeys);
        $missingInId = array_diff($enLeafKeys, $idLeafKeys);

        // We check if any of the target sections (badges, features, empty, catalog) have parity issues
        $targetSections = ['badges.', 'features.', 'empty.', 'catalog.'];
        $targetMissingInEn = array_filter($missingInEn, function ($k) use ($targetSections) {
            foreach ($targetSections as $prefix) {
                if (str_starts_with($k, $prefix)) return true;
            }
            return false;
        });

        $targetMissingInId = array_filter($missingInId, function ($k) use ($targetSections) {
            foreach ($targetSections as $prefix) {
                if (str_starts_with($k, $prefix)) return true;
            }
            return false;
        });

        $this->assertEmpty($targetMissingInEn, 'Target sections have keys in ID missing in EN: ' . implode(', ', $targetMissingInEn));
        $this->assertEmpty($targetMissingInId, 'Target sections have keys in EN missing in ID: ' . implode(', ', $targetMissingInId));
    }

    /**
     * 12. Empirical Test: Verify keys referenced in StorefrontThemeService getUniversalFallback
     */
    public function test_storefront_theme_service_fallback_translation_resolution(): void
    {
        $themeService = new \App\Domain\Storefront\StorefrontThemeService();
        $fallbackId = $themeService->getUniversalFallback('modern_umkm');

        // Check hero translation keys
        $this->assertNotSame('storefront.hero.welcome', $fallbackId['headline']);
        $this->assertNotSame('storefront.hero.tagline', $fallbackId['subheadline']);
        $this->assertNotSame('storefront.hero.verified_official', $fallbackId['announcement_badge']);
        $this->assertNotSame('storefront.hero.active_status', $fallbackId['active_status']);
        $this->assertNotSame('storefront.hero.explore_catalog', $fallbackId['cta_primary_text']);
        $this->assertNotSame('storefront.hero.contact_us', $fallbackId['cta_secondary_text']);

        // Check catalog translation keys
        $this->assertNotSame('storefront.kategori_pilihan', $fallbackId['catalog']['title'], 'storefront.kategori_pilihan was not resolved!');
        $this->assertNotSame('storefront.pilih_kategori_filter', $fallbackId['catalog']['subtitle'], 'storefront.pilih_kategori_filter was not resolved!');
        $this->assertNotSame('storefront.semua_produk', $fallbackId['catalog']['semua_produk'], 'storefront.semua_produk was not resolved!');
        $this->assertNotSame('storefront.view_all', $fallbackId['catalog']['view_all'], 'storefront.view_all was not resolved!');

        $this->assertNotSame('storefront.catalog.empty_title', $fallbackId['empty_catalog_title']);
        $this->assertNotSame('storefront.catalog.empty_desc', $fallbackId['empty_catalog_desc']);
    }

    /**
     * 13. Deep Dive Test: Check whether root-level aliases exist for kategori_pilihan, semua_produk, and view_all
     */
    public function test_root_level_and_catalog_aliases_for_blueprint_contract(): void
    {
        // In the blueprint contract (STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md lines 247-263):
        // Blade calls:
        // {{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}
        // {{ __('storefront.pilih_kategori_filter') }}
        // {{ __('storefront.view_all') }}
        // {{ __('storefront.semua_produk') }}

        // Let's inspect what __('storefront.view_all') returns
        $viewAllId = __('storefront.view_all', [], 'id');
        $viewAllEn = __('storefront.view_all', [], 'en');

        $kategoriPilihanId = __('storefront.kategori_pilihan', [], 'id');
        $kategoriPilihanEn = __('storefront.kategori_pilihan', [], 'en');

        $semuaProdukId = __('storefront.semua_produk', [], 'id');
        $semuaProdukEn = __('storefront.semua_produk', [], 'en');

        $this->assertSame('Lihat Semua', $viewAllId);
        $this->assertSame('View All', $viewAllEn);

        $this->assertSame('Kategori Pilihan', $kategoriPilihanId);
        // Note: in root level, kategori_pilihan was previously 'Category Selection' in EN, and in catalog it is 'Featured Categories'
        $this->assertNotEmpty($kategoriPilihanEn);

        $this->assertSame('Semua Produk', $semuaProdukId);
        $this->assertSame('All Products', $semuaProdukEn);
    }
}
