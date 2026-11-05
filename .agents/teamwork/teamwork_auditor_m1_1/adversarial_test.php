<?php

require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BusinessLandingPage;
use App\Domain\Storefront\StorefrontThemeService;

echo "=== RUNNING ADVERSARIAL STRESS TESTS ===" . PHP_EOL;

$m = new BusinessLandingPage();
$svc = new StorefrontThemeService();

// Stress Test 1: Corrupted section_visibility values
$corruptInputs = [
    '{corrupted_json: true',
    12345,
    true,
    false,
    3.14159,
    (object)['foo' => 'bar'],
    "",
];

foreach ($corruptInputs as $idx => $corrupt) {
    $m->section_visibility = $corrupt;
    assert($m->isSectionVisible('hero') === true, "Corrupt test #{$idx} failed on hero visibility");
    assert($m->isSectionVisible('products') === true, "Corrupt test #{$idx} failed on products visibility");
    assert($m->isSectionVisible('hero', false) === false, "Corrupt test #{$idx} failed on explicit false default");
    $vis = $m->getSectionVisibility();
    assert(is_array($vis) && count($vis) >= 12, "Corrupt test #{$idx} failed on getSectionVisibility()");
}
echo "  [PASS] Corrupted section_visibility gracefully handled without errors" . PHP_EOL;

// Stress Test 2: Bizarre values in $landingPage->values
$bizarreValues = [
    'invalid_json_string',
    [null, false, 42, "string"],
    [['icon' => '', 'title' => '', 'desc' => '']], // all empty strings
    [['icon' => ' ', 'title' => ' ', 'desc' => ' ']],
    [['unrelated_key' => 123]],
    range(1, 100), // huge array of ints
];

foreach ($bizarreValues as $idx => $bz) {
    $m->values = $bz;
    $vals = $m->getUniversalValues();
    assert(is_array($vals), "Bizarre values #{$idx} did not return array");
    assert(count($vals) === 4, "Bizarre values #{$idx} count was " . count($vals) . " instead of 4");
    for ($i = 0; $i < 4; $i++) {
        assert(!empty($vals[$i]['icon']), "Item {$i} icon empty in test #{$idx}");
        assert(!empty($vals[$i]['title']), "Item {$i} title empty in test #{$idx}");
        assert(!empty($vals[$i]['desc']), "Item {$i} desc empty in test #{$idx}");
        assert($vals[$i]['desc'] === $vals[$i]['description'], "Item {$i} desc !== description in test #{$idx}");
    }
}
echo "  [PASS] Bizarre values input safely normalized to 4 complete pillars" . PHP_EOL;

// Stress Test 3: Pathological theme keys in StorefrontThemeService
$pathologicalKeys = [
    '',
    '   ',
    'nonexistent_theme_99999',
    '../../etc/passwd',
    '<script>alert(1)</script>',
    str_repeat('a', 1000),
    "\0nullbyte",
];

foreach ($pathologicalKeys as $badKey) {
    $arch = $svc->getThemeArchetype($badKey);
    assert(is_array($arch) && $arch['id'] === 'clean_bento', "Pathological key '{$badKey}' did not fall back to clean_bento");

    $fb = $svc->getUniversalFallback($badKey);
    assert(is_array($fb), "Universal fallback for '{$badKey}' did not return array");
    assert(count($fb['features']) === 4, "Universal fallback features count !== 4 for '{$badKey}'");
    assert(count($fb['hero_badges']) === 3, "Universal fallback badges count !== 3 for '{$badKey}'");
}
echo "  [PASS] Pathological theme keys safely handled and fallback preserved" . PHP_EOL;

echo "=== ALL ADVERSARIAL STRESS TESTS PASSED! ===" . PHP_EOL;
