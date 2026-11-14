<?php
$files = [
    'resources/views/layouts/public_marketing.blade.php',
    'resources/views/layouts/partials/help_chatbot_widget.blade.php',
    'resources/views/layouts/partials/typography.blade.php',
    'resources/views/public/partials/cookie_consent_banner.blade.php',
    'resources/views/public/partials/live_chat_widget.blade.php',
    'resources/views/public/partials/seo.blade.php',
    'resources/views/public/partials/subpage_layout.blade.php',
    'resources/views/public/partials/tracking_pixels.blade.php',
    'resources/views/public/partials/verified_testimonials.blade.php',
    'resources/views/public/partials/video_testimonials.blade.php',
];

$allId = [];
foreach (glob(__DIR__ . '/../../../lang/id/*.php') as $f) {
    $group = basename($f, '.php');
    $arr = require $f;
    if (is_array($arr)) {
        foreach ($arr as $k => $v) {
            $allId["{$group}.{$k}"] = $v;
        }
    }
}

foreach ($files as $file) {
    $path = __DIR__ . '/../../../' . $file;
    if (!file_exists($path)) {
        echo "NOT FOUND: $file\n";
        continue;
    }
    $content = file_get_contents($path);
    preg_match_all("/__\(['\"]([^'\"]+)['\"]\)/", $content, $m);
    $usedKeys = array_unique($m[1]);
    $missing = [];
    foreach ($usedKeys as $k) {
        if (!isset($allId[$k])) {
            $missing[] = $k;
        }
    }
    echo "File: $file\n";
    echo "  Total __() calls: " . count($m[1]) . " (unique: " . count($usedKeys) . ")\n";
    if (!empty($missing)) {
        echo "  MISSING KEYS (" . count($missing) . "):\n";
        foreach ($missing as $mk) {
            echo "    - $mk\n";
        }
    } else {
        echo "  All translation keys resolved in lang/id/*.php!\n";
    }
}
