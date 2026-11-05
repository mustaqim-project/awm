<?php

require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BusinessLandingPage;
use App\Domain\Storefront\StorefrontThemeService;

echo "=== RUNNING FORENSIC EMPIRICAL VERIFICATION ===" . PHP_EOL;

// -------------------------------------------------------------
// 1. BusinessLandingPage forensic checks
// -------------------------------------------------------------
echo "[1] Testing BusinessLandingPage..." . PHP_EOL;
$m = new BusinessLandingPage();

// 1.1 Default visibility constant
assert(count(BusinessLandingPage::DEFAULT_SECTION_VISIBILITY) === 12, 'DEFAULT_SECTION_VISIBILITY must have 12 sections');
$expectedSections = ['hero', 'features_banner', 'category_chips', 'products', 'services', 'gallery', 'testimonials', 'faq', 'stats', 'about', 'contact', 'footer'];
foreach ($expectedSections as $sec) {
    assert(isset(BusinessLandingPage::DEFAULT_SECTION_VISIBILITY[$sec]), "DEFAULT_SECTION_VISIBILITY missing {$sec}");
    assert(BusinessLandingPage::DEFAULT_SECTION_VISIBILITY[$sec] === true, "DEFAULT_SECTION_VISIBILITY {$sec} must be true");
    assert($m->isSectionVisible($sec) === true, "Default {$sec} visibility should be true");
}

// 1.2 Overrides via array
$m->section_visibility = ['hero' => false, 'products' => false];
assert($m->isSectionVisible('hero') === false, 'hero should be false when set');
assert($m->isSectionVisible('products') === false, 'products should be false when set');
assert($m->isSectionVisible('services') === true, 'services should remain true');

// 1.3 Legacy alias handling
$m->section_visibility = ['featured_products' => false];
assert($m->isSectionVisible('products') === false, 'products alias to featured_products failed');
$m->section_visibility = ['products' => false];
assert($m->isSectionVisible('featured_products') === false, 'featured_products alias to products failed');

$m->section_visibility = ['catalog_filter' => false];
assert($m->isSectionVisible('category_chips') === false, 'category_chips alias to catalog_filter failed');

$m->section_visibility = ['faqs' => false];
assert($m->isSectionVisible('faq') === false, 'faq alias to faqs failed');

$m->section_visibility = ['reviews' => false];
assert($m->isSectionVisible('testimonials') === false, 'testimonials alias to reviews failed');

$m->section_visibility = ['about_story' => false];
assert($m->isSectionVisible('about') === false, 'about alias to about_story failed');

// 1.4 JSON string handling
$m->section_visibility = json_encode(['hero' => false, 'contact' => false]);
assert($m->isSectionVisible('hero') === false, 'JSON encoded section_visibility hero failed');
assert($m->isSectionVisible('contact') === false, 'JSON encoded section_visibility contact failed');
assert($m->isSectionVisible('about') === true, 'JSON encoded section_visibility about default failed');

// 1.5 Explicit default parameter override
$m->section_visibility = [];
assert($m->isSectionVisible('hero', false) === false, 'Caller explicit default false override failed');
assert($m->isSectionVisible('nonexistent_random_section', true) === true, 'Unknown section with default true failed');
assert($m->isSectionVisible('nonexistent_random_section', false) === false, 'Unknown section with default false failed');

// 1.6 getSectionVisibility
$m->section_visibility = ['hero' => false];
$merged = $m->getSectionVisibility();
assert($merged['hero'] === false, 'Merged hero should be false');
assert($merged['services'] === true, 'Merged services should be true');
assert(count($merged) >= 12, 'Merged count should be at least 12');

// 1.7 Universal values normalization
$m->values = null;
$vNull = $m->getUniversalValues();
assert(count($vNull) === 4, 'Null values should return exactly 4 items');
assert($vNull[0]['desc'] === $vNull[0]['description'], 'desc and description must match');

$m->values = [
    ['icon' => 'star', 'title' => 'Custom Title', 'desc' => 'Custom Desc'],
    ['icon' => 'zap', 'title' => 'Custom 2', 'description' => 'Custom Desc 2'],
    'stats' => ['number' => 100], // Legacy non-numeric key should be ignored
];
$vCustom = $m->getUniversalValues();
assert(count($vCustom) === 4, 'Should normalize to exactly 4 items');
assert($vCustom[0]['title'] === 'Custom Title', 'Item 0 title mismatch');
assert($vCustom[0]['desc'] === 'Custom Desc' && $vCustom[0]['description'] === 'Custom Desc', 'Item 0 desc mismatch');
assert($vCustom[1]['title'] === 'Custom 2', 'Item 1 title mismatch');
assert($vCustom[1]['desc'] === 'Custom Desc 2' && $vCustom[1]['description'] === 'Custom Desc 2', 'Item 1 description alias mismatch');
assert(!empty($vCustom[2]['title']), 'Item 2 should fall back to default pillar');
assert(!empty($vCustom[3]['title']), 'Item 3 should fall back to default pillar');

echo "  -> BusinessLandingPage: ALL CHECKS PASSED!" . PHP_EOL;

// -------------------------------------------------------------
// 2. StorefrontThemeService forensic checks
// -------------------------------------------------------------
echo "[2] Testing StorefrontThemeService..." . PHP_EOL;
$svc = new StorefrontThemeService();

// 2.1 Archetypes structure and theme coverage
$archetypes = $svc->getAllArchetypes();
assert(count($archetypes) === 6, 'There must be exactly 6 archetypes');

$allThemes = $svc->getAllThemes();
$allThemeIds = array_keys($allThemes);
assert(count($allThemeIds) === 42, 'getAllThemes must have 42 themes');

$mappedThemes = [];
foreach ($archetypes as $aKey => $arch) {
    assert(isset($arch['count']) && $arch['count'] === count($arch['themes']), "Count in archetype {$aKey} mismatch");
    foreach ($arch['themes'] as $t) {
        assert(in_array($t, $allThemeIds, true), "Theme {$t} in archetype {$aKey} does not exist in THEMES");
        assert(!in_array($t, $mappedThemes, true), "Theme {$t} is duplicated across archetypes");
        $mappedThemes[] = $t;
    }
}
assert(count($mappedThemes) === 42, 'Total mapped themes must equal 42');

// 2.2 THEME_ARCHETYPE_MAP completeness
assert(count(StorefrontThemeService::THEME_ARCHETYPE_MAP) === 42, 'THEME_ARCHETYPE_MAP must have 42 entries');
foreach ($allThemeIds as $tid) {
    assert(isset(StorefrontThemeService::THEME_ARCHETYPE_MAP[$tid]), "Theme {$tid} missing from THEME_ARCHETYPE_MAP");
}

// 2.3 getThemeArchetype checks
assert($svc->getThemeArchetype('universal_storefront')['id'] === 'clean_bento');
assert($svc->getThemeArchetype('coffee_beverage')['id'] === 'warm_artisan');
assert($svc->getThemeArchetype('retail_fashion')['id'] === 'luxe_editorial');
assert($svc->getThemeArchetype('service_workshop')['id'] === 'dark_carbon');
assert($svc->getThemeArchetype('food_restaurant')['id'] === 'playful_fresh');
assert($svc->getThemeArchetype('business_corporate')['id'] === 'institutional_trust');

// Alias check
assert($svc->getThemeArchetype('artisan_brew')['id'] === 'warm_artisan');
assert($svc->getThemeArchetype('aqua_bubble')['id'] === 'institutional_trust');

// Fallback for random unknown theme
$randomKey = 'unknown_theme_' . bin2hex(random_bytes(6));
assert($svc->getThemeArchetype($randomKey)['id'] === 'clean_bento', 'Unknown theme should fall back to clean_bento');

// 2.4 getAllThemes enrichment
$allThemes = $svc->getAllThemes();
assert(count($allThemes) === 42, 'getAllThemes must return 42 themes');
foreach ($allThemes as $k => $t) {
    assert(!empty($t['archetype']), "Theme {$k} missing archetype");
    assert(!empty($t['archetype_name']), "Theme {$k} missing archetype_name");
}

// 2.5 getTheme enrichment
$single = $svc->getTheme('retail_fashion');
assert($single['archetype'] === 'luxe_editorial', 'Single getTheme archetype mismatch');
assert($single['archetype_name'] === 'High-End Luxe & Editorial', 'Single getTheme archetype_name mismatch');

// 2.6 getUniversalFallback
foreach (['retail_fashion', 'service_workshop', 'food_restaurant', 'universal_storefront', 'coffee_beverage', 'business_corporate'] as $sampleKey) {
    $fb = $svc->getUniversalFallback($sampleKey);
    assert(!empty($fb['theme_id']), "Fallback {$sampleKey} missing theme_id");
    assert(!empty($fb['headline']), "Fallback {$sampleKey} missing headline");
    assert(!empty($fb['subheadline']), "Fallback {$sampleKey} missing subheadline");
    assert(count($fb['features']) === 4, "Fallback {$sampleKey} must have 4 features");
    assert(count($fb['hero_badges']) === 3, "Fallback {$sampleKey} must have 3 hero_badges");
    assert(isset($fb['catalog']['semua_produk']), "Fallback {$sampleKey} missing catalog string");
}

echo "  -> StorefrontThemeService: ALL CHECKS PASSED!" . PHP_EOL;

// -------------------------------------------------------------
// 3. Translation resolution checks
// -------------------------------------------------------------
echo "[3] Testing i18n translation resolution..." . PHP_EOL;
$testKeys = [
    'badges.quality_guaranteed' => ['id' => 'Kualitas Terjamin', 'en' => 'Quality Guaranteed'],
    'badges.secure_transaction' => ['id' => 'Transaksi Aman', 'en' => 'Secure Transaction'],
    'badges.fast_service' => ['id' => 'Layanan Cepat', 'en' => 'Fast Service'],
    'badges.verified_official' => ['id' => 'Toko Resmi Terverifikasi', 'en' => 'Verified Official Store'],
    'badges.active_status' => ['id' => 'Aktif Beroperasi', 'en' => 'Operating Actively'],
    'features.official_store' => ['id' => 'Outlet Resmi Terverifikasi', 'en' => 'Verified Official Store'],
    'features.easy_payment' => ['id' => 'Pembayaran Mudah & Aman', 'en' => 'Seamless & Secure Payment'],
    'features.secure_delivery' => ['id' => 'Pengiriman Cepat & Aman', 'en' => 'Fast & Reliable Delivery'],
    'features.customer_care' => ['id' => 'Layanan Pelanggan Siaga', 'en' => 'Dedicated Customer Support'],
    'catalog.pilih_kategori_filter' => ['id' => 'Pilih kategori untuk memfilter etalase belanja', 'en' => 'Select a category to filter the store catalog'],
    'catalog.semua_produk' => ['id' => 'Semua Produk', 'en' => 'All Products'],
    'catalog.kategori_pilihan' => ['id' => 'Kategori Pilihan', 'en' => 'Featured Categories'],
    'empty.catalog_title' => ['id' => 'Produk Tidak Ditemukan', 'en' => 'No Products Found'],
    'empty.cart_title' => ['id' => 'Keranjang Belanja Masih Kosong', 'en' => 'Your Shopping Cart is Empty'],
    'empty.products_title' => ['id' => 'Belum Ada Produk Tersedia', 'en' => 'No Products Available Yet'],
    // Dot notation tests
    'empty.catalog.title' => ['id' => 'Produk Tidak Ditemukan', 'en' => 'No Products Found'],
    'empty.cart.title' => ['id' => 'Keranjang Belanja Masih Kosong', 'en' => 'Your Shopping Cart is Empty'],
    'empty.products.title' => ['id' => 'Belum Ada Produk Tersedia', 'en' => 'No Products Available Yet'],
];

foreach ($testKeys as $k => $expected) {
    $resId = __('storefront.' . $k, [], 'id');
    $resEn = __('storefront.' . $k, [], 'en');
    assert($resId === $expected['id'], "Translation mismatch for ID 'storefront.{$k}': got '{$resId}', expected '{$expected['id']}'");
    assert($resEn === $expected['en'], "Translation mismatch for EN 'storefront.{$k}': got '{$resEn}', expected '{$expected['en']}'");
}

echo "  -> Translations: ALL 18 KEYS IN ID & EN MATCH EXPECTATIONS!" . PHP_EOL;

echo "=== ALL FORENSIC CHECKS PASSED WITH ZERO INTEGRITY VIOLATIONS ===" . PHP_EOL;
