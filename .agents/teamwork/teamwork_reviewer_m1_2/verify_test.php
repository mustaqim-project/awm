<?php

require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new App\Domain\Storefront\StorefrontThemeService();

echo "=== 1. VERIFY 42 THEMES AND 6 ARCHETYPES ===" . PHP_EOL;
$allThemes = $service->getAllThemes();
echo 'Total themes from getAllThemes(): ' . count($allThemes) . PHP_EOL;
if (count($allThemes) !== 42) {
    throw new Exception("Expected 42 themes, got " . count($allThemes));
}

$archetypes = $service->getAllArchetypes();
echo 'Total archetypes from getAllArchetypes(): ' . count($archetypes) . PHP_EOL;
if (count($archetypes) !== 6) {
    throw new Exception("Expected 6 archetypes, got " . count($archetypes));
}

$expectedArchetypes = [
    'clean_bento' => 8,
    'warm_artisan' => 7,
    'luxe_editorial' => 6,
    'dark_carbon' => 7,
    'playful_fresh' => 7,
    'institutional_trust' => 7,
];

$blueprintThemes = [
    'clean_bento' => [
        'universal_storefront', 'modern_umkm', 'online_store', 'service_agency',
        'retail_electronics', 'retail_building', 'professional_services', 'retail_grocery'
    ],
    'warm_artisan' => [
        'coffee_beverage', 'agriculture_farm', 'bakery_cake', 'fnb_bakery', 'fnb_cafe',
        'retail_florist', 'hospitality_accommodation'
    ],
    'luxe_editorial' => [
        'retail_fashion', 'health_beauty', 'product_showcase', 'event_wedding',
        'personal_brand', 'portfolio_creative'
    ],
    'dark_carbon' => [
        'service_workshop', 'service_carwash', 'service_fitness', 'service_laundry',
        'construction_property', 'manufacturing_factory', 'logistics_transportation'
    ],
    'playful_fresh' => [
        'food_restaurant', 'fnb_resto', 'fnb_fastfood', 'fnb_catering',
        'retail_bookstore', 'service_petshop', 'service_beauty'
    ],
    'institutional_trust' => [
        'business_corporate', 'education_courses', 'retail_pharmacy', 'service_business',
        'service_printing', 'booking_appointment', 'wholesale_distributor'
    ],
];

foreach ($expectedArchetypes as $archId => $expectedCount) {
    if (!isset($archetypes[$archId])) {
        throw new Exception("Missing archetype definition: {$archId}");
    }
    $actualCount = count($archetypes[$archId]['themes']);
    echo "Archetype {$archId}: expected count {$expectedCount}, got {$actualCount}" . PHP_EOL;
    if ($actualCount !== $expectedCount) {
        throw new Exception("Count mismatch for {$archId}: expected {$expectedCount}, got {$actualCount}");
    }
    
    // Check against blueprint list
    $diff = array_diff($blueprintThemes[$archId], $archetypes[$archId]['themes']);
    if (!empty($diff)) {
        throw new Exception("Blueprint themes missing in {$archId}: " . implode(', ', $diff));
    }
}

echo "=== 2. BIJECTION AND REVERSE MAP VERIFICATION ===" . PHP_EOL;
$allArchetypeThemes = [];
foreach ($archetypes as $archId => $arch) {
    foreach ($arch['themes'] as $themeId) {
        if (isset($allArchetypeThemes[$themeId])) {
            throw new Exception("Theme {$themeId} duplicated in {$archId} and " . $allArchetypeThemes[$themeId]);
        }
        $allArchetypeThemes[$themeId] = $archId;
        if (!isset($allThemes[$themeId])) {
            throw new Exception("Theme in archetype {$archId} not found in THEMES catalogue: {$themeId}");
        }
    }
}

if (count($allArchetypeThemes) !== 42) {
    throw new Exception("Partition does not cover all 42 themes: count is " . count($allArchetypeThemes));
}

foreach (array_keys($allThemes) as $themeId) {
    if (!isset($allArchetypeThemes[$themeId])) {
        throw new Exception("Theme {$themeId} from THEMES is not present in any archetype!");
    }
    // Check THEME_ARCHETYPE_MAP consistency
    if (App\Domain\Storefront\StorefrontThemeService::THEME_ARCHETYPE_MAP[$themeId] !== $allArchetypeThemes[$themeId]) {
        throw new Exception("THEME_ARCHETYPE_MAP mismatch for {$themeId}");
    }
    
    // Check getThemeArchetype()
    $arch = $service->getThemeArchetype($themeId);
    if ($arch['id'] !== $allArchetypeThemes[$themeId]) {
        throw new Exception("getThemeArchetype() mismatch for {$themeId}");
    }
    
    // Check getAllThemes enrichment
    if ($allThemes[$themeId]['archetype'] !== $allArchetypeThemes[$themeId] || $allThemes[$themeId]['archetype_name'] !== $arch['name']) {
        throw new Exception("getAllThemes() enrichment mismatch for {$themeId}");
    }
    
    // Check getTheme enrichment
    $singleTheme = $service->getTheme($themeId);
    if ($singleTheme['archetype'] !== $allArchetypeThemes[$themeId] || $singleTheme['archetype_name'] !== $arch['name']) {
        throw new Exception("getTheme() enrichment mismatch for {$themeId}");
    }
}

echo "All 42 themes perfectly partitioned and enriched!" . PHP_EOL;

echo "=== 3. getUniversalFallback() CONTRACT VERIFICATION ===" . PHP_EOL;
foreach (array_keys($allThemes) as $themeId) {
    $fb = $service->getUniversalFallback($themeId);
    
    // Check structure
    $requiredKeys = [
        'theme_id', 'theme_name', 'archetype', 'archetype_name',
        'headline', 'subheadline', 'announcement_badge', 'active_status',
        'cta_primary_text', 'cta_secondary_text',
        'hero_badges', 'badges', 'features', 'pillars',
        'catalog', 'empty_catalog_title', 'empty_catalog_desc'
    ];
    foreach ($requiredKeys as $rk) {
        if (!array_key_exists($rk, $fb)) {
            throw new Exception("getUniversalFallback({$themeId}) missing required key: {$rk}");
        }
    }
    
    // Check hero_badges count
    if (count($fb['hero_badges']) !== 3 || count($fb['badges']) !== 3) {
        throw new Exception("getUniversalFallback({$themeId}) badges must have 3 items");
    }
    
    // Check features count
    if (count($fb['features']) !== 4 || count($fb['pillars']) !== 4) {
        throw new Exception("getUniversalFallback({$themeId}) features must have 4 items");
    }
    
    // Check features dual key support ('desc' and 'description')
    foreach ($fb['features'] as $idx => $feat) {
        if (!isset($feat['icon'], $feat['title'], $feat['desc'], $feat['description'])) {
            throw new Exception("Feature item {$idx} in {$themeId} missing required field");
        }
        if ($feat['desc'] !== $feat['description']) {
            throw new Exception("Feature item {$idx} desc != description");
        }
    }
    
    // Check catalog keys
    if (!isset($fb['catalog']['title'], $fb['catalog']['subtitle'], $fb['catalog']['semua_produk'], $fb['catalog']['view_all'])) {
        throw new Exception("Catalog keys missing in {$themeId}");
    }
}
echo "getUniversalFallback() contract verified for all 42 themes!" . PHP_EOL;

echo "=== 4. ADVERSARIAL STRESS TESTING ===" . PHP_EOL;

// 4.1 Unknown theme key in getThemeArchetype()
$fallbackArch = $service->getThemeArchetype('completely_unknown_theme_xyz');
if ($fallbackArch['id'] !== 'clean_bento') {
    throw new Exception("getThemeArchetype() failed to fall back to clean_bento for unknown theme");
}
echo "Unknown theme fallback: OK (fell back to clean_bento)" . PHP_EOL;

// 4.2 Empty string and whitespace in getThemeArchetype()
$emptyArch = $service->getThemeArchetype('   ');
if ($emptyArch['id'] !== 'clean_bento') {
    throw new Exception("getThemeArchetype() failed to handle whitespace");
}
echo "Whitespace theme input: OK" . PHP_EOL;

// 4.3 Unknown theme key in getUniversalFallback()
$fbUnknown = $service->getUniversalFallback('non_existent_theme');
if ($fbUnknown['archetype'] !== 'clean_bento') {
    throw new Exception("getUniversalFallback() unknown theme archetype mismatch");
}
echo "getUniversalFallback('non_existent_theme'): OK" . PHP_EOL;

// 4.4 resolveTheme() backward compatibility
$blp = new App\Models\BusinessLandingPage();
$blp->theme_preset = 'retail_fashion';
$blp->theme_color = '#123456';
$blp->font_family = 'Playfair Display';
$blp->dark_mode = true;

$resolved = $service->resolveTheme($blp);
if ($resolved['id'] !== 'retail_fashion') {
    throw new Exception("resolveTheme() id mismatch");
}
if ($resolved['primary_color'] !== '#123456') {
    throw new Exception("resolveTheme() color override failed");
}
if ($resolved['font_heading'] !== 'Playfair Display') {
    throw new Exception("resolveTheme() font override failed");
}
if ($resolved['header_dark'] !== true || $resolved['bg_color'] !== '#0B0F19') {
    throw new Exception("resolveTheme() dark mode failed");
}
if ($resolved['archetype'] !== 'luxe_editorial' || $resolved['archetype_name'] !== 'High-End Luxe & Editorial') {
    throw new Exception("resolveTheme() archetype metadata missing or corrupted");
}
echo "resolveTheme() full test: OK" . PHP_EOL;

// 4.5 BusinessLandingPage helper stress test
$blpTest = new App\Models\BusinessLandingPage();

// Case A: section_visibility is null
$blpTest->section_visibility = null;
if (!$blpTest->isSectionVisible('hero') || !$blpTest->isSectionVisible('products')) {
    throw new Exception("isSectionVisible failed on null section_visibility");
}

// Case B: section_visibility is empty array
$blpTest->section_visibility = [];
if (!$blpTest->isSectionVisible('hero') || !$blpTest->isSectionVisible('category_chips')) {
    throw new Exception("isSectionVisible failed on empty array");
}

// Case C: section_visibility is json string
$blpTest->section_visibility = json_encode(['hero' => false, 'featured_products' => false]);
if ($blpTest->isSectionVisible('hero') !== false) {
    throw new Exception("isSectionVisible failed to parse JSON string");
}
if ($blpTest->isSectionVisible('products') !== false) {
    throw new Exception("isSectionVisible legacy alias 'featured_products' -> 'products' failed");
}
if ($blpTest->isSectionVisible('category_chips') !== true) {
    throw new Exception("isSectionVisible unmentioned section should default to true");
}

// Case D: getUniversalValues with empty values
$blpTest->values = null;
$vals = $blpTest->getUniversalValues();
if (count($vals) !== 4) {
    throw new Exception("getUniversalValues on null must return 4 default pillars");
}

// Case E: getUniversalValues with custom partial values
$blpTest->values = [
    ['icon' => 'star', 'title' => 'Custom Pillar 1', 'desc' => 'Custom Desc 1'],
];
$vals2 = $blpTest->getUniversalValues();
if (count($vals2) !== 4) {
    throw new Exception("getUniversalValues must always pad to exactly 4 items");
}
if ($vals2[0]['title'] !== 'Custom Pillar 1' || $vals2[0]['description'] !== 'Custom Desc 1') {
    throw new Exception("getUniversalValues failed to retain custom first pillar");
}
// Case F: Test alias resolutions in getThemeArchetype()
$aliasesToTest = [
    'artisan_brew' => 'warm_artisan',
    'aqua_bubble' => 'institutional_trust',
    'velvet_patisserie' => 'warm_artisan',
    'nusantara_feast' => 'playful_fresh',
    'flora_romance' => 'luxe_editorial',
];
foreach ($aliasesToTest as $alias => $expectedArch) {
    $resolvedArch = $service->getThemeArchetype($alias);
    if ($resolvedArch['id'] !== $expectedArch) {
        throw new Exception("Alias {$alias} resolved to {$resolvedArch['id']}, expected {$expectedArch}");
    }
}
echo "Alias resolution stress test: ALL PASSED!" . PHP_EOL;

echo "BusinessLandingPage stress test: ALL PASSED!" . PHP_EOL;

echo "=== ALL VERIFICATION CHECKS COMPLETED SUCCESSFULLY ===" . PHP_EOL;
