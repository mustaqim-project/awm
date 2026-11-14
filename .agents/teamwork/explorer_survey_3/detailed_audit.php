<?php

$files = [
    // 1. Blog
    "resources/views/public/blog/index.blade.php",
    "resources/views/public/blog/show.blade.php",
    // 2. Business Operating System
    "resources/views/public/business-operating-system/how-it-works.blade.php",
    "resources/views/public/business-operating-system/overview.blade.php",
    "resources/views/public/business-operating-system/why-cooca.blade.php",
    // 3. Calculators
    "resources/views/public/calculators/bep.blade.php",
    "resources/views/public/calculators/gaji_karyawan.blade.php",
    "resources/views/public/calculators/harga_jual.blade.php",
    "resources/views/public/calculators/hpp.blade.php",
    "resources/views/public/calculators/index.blade.php",
    "resources/views/public/calculators/laba_bersih.blade.php",
    "resources/views/public/calculators/omzet_harian.blade.php",
    "resources/views/public/calculators/pph_final.blade.php",
    "resources/views/public/calculators/simulasi_what_if.blade.php",
    // 4. Contact
    "resources/views/public/contact/index.blade.php",
    // 5. Content Automation
    "resources/views/public/content-automation/analytics.blade.php",
    "resources/views/public/content-automation/content-calendar.blade.php",
    "resources/views/public/content-automation/content-creation.blade.php",
    "resources/views/public/content-automation/publishing.blade.php",
    // 6. Marketplace
    "resources/views/public/marketplace/businesses.blade.php",
    "resources/views/public/marketplace/categories.blade.php",
    "resources/views/public/marketplace/index.blade.php",
    "resources/views/public/marketplace/locations.blade.php",
    "resources/views/public/marketplace/products.blade.php",
    "resources/views/public/marketplace/search.blade.php",
    // 7. Omnichannel
    "resources/views/public/omnichannel/customer.blade.php",
    "resources/views/public/omnichannel/marketplace.blade.php",
    "resources/views/public/omnichannel/orders.blade.php",
    "resources/views/public/omnichannel/social-media.blade.php",
    "resources/views/public/omnichannel/whatsapp.blade.php",
    // 8. Omnichannel ERP
    "resources/views/public/omnichannel-erp/accounting.blade.php",
    "resources/views/public/omnichannel-erp/analytics.blade.php",
    "resources/views/public/omnichannel-erp/crm.blade.php",
    "resources/views/public/omnichannel-erp/erp.blade.php",
    "resources/views/public/omnichannel-erp/finance.blade.php",
    "resources/views/public/omnichannel-erp/hrm.blade.php",
    "resources/views/public/omnichannel-erp/inventory.blade.php",
    "resources/views/public/omnichannel-erp/pos.blade.php",
    // 9. Resources
    "resources/views/public/resources/case-studies.blade.php",
    "resources/views/public/resources/faq.blade.php",
    "resources/views/public/resources/guides.blade.php",
    // 10. Templates
    "resources/views/public/templates/index.blade.php",
    "resources/views/public/templates/show.blade.php",
];

// Load all lang keys from both EN and ID
$langKeys = [];
$langDir = "lang/id";
foreach (scandir($langDir) as $lf) {
    if (str_ends_with($lf, '.php')) {
        $prefix = str_replace('.php', '', $lf);
        $arr = include "$langDir/$lf";
        if (is_array($arr)) {
            flattenTo($arr, $prefix, $langKeys);
        }
    }
}
function flattenTo($arr, $prefix, &$res) {
    foreach ($arr as $k => $v) {
        $full = "$prefix.$k";
        if (is_array($v)) {
            flattenTo($v, $full, $res);
        } else {
            $res[$full] = true;
        }
    }
}

echo "Loaded " . count($langKeys) . " unique ID translation keys.\n\n";

$results = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    $fileRes = [
        'file' => $file,
        'seo' => [],
        'responsive' => [],
        'cwv' => [],
        'js_tbt' => [],
        'css_assets' => [],
        'theme' => [],
        'i18n' => []
    ];

    // SEO checks
    $hasTitleSection = preg_match('/@section\(\s*[\'"]title[\'"]/i', $content);
    $hasDescSection = preg_match('/@section\(\s*[\'"]description[\'"]/i', $content);
    $hasCanonicalSection = preg_match('/@section\(\s*[\'"]canonical[\'"]/i', $content);
    $hasOgTitle = preg_match('/@section\(\s*[\'"]og_title[\'"]/i', $content);
    $hasOgDesc = preg_match('/@section\(\s*[\'"]og_description[\'"]/i', $content);
    $hasJsonLd = preg_match('/application\/ld\+json/i', $content);

    preg_match_all('/<h1\b[^>]*>(.*?)<\/h1>/si', $content, $h1Matches, PREG_OFFSET_CAPTURE);
    $h1Count = count($h1Matches[0]);

    if ($h1Count !== 1) {
        $fileRes['seo'][] = "H1 count is $h1Count (expected exactly 1)";
    }
    if (!$hasTitleSection) {
        $fileRes['seo'][] = "Missing explicit @section('title')";
    }
    if (!$hasDescSection) {
        $fileRes['seo'][] = "Missing explicit @section('description')";
    }
    if (!$hasCanonicalSection) {
        $fileRes['seo'][] = "Missing explicit @section('canonical')";
    }
    if (!$hasJsonLd) {
        $fileRes['seo'][] = "Missing Schema JSON-LD structured data block";
    }

    // Images
    preg_match_all('/<img\b([^>]*)>/si', $content, $imgMatches, PREG_OFFSET_CAPTURE);
    foreach ($imgMatches[0] as $idx => $m) {
        $attrs = $imgMatches[1][$idx][0];
        $lineNum = substr_count(substr($content, 0, $m[1]), "\n") + 1;
        if (!preg_match('/\balt\s*=\s*["\'][^"\']*["\']/i', $attrs)) {
            $fileRes['seo'][] = "Line $lineNum: <img> missing alt attribute";
        }
        if (!preg_match('/\bwidth\s*=/i', $attrs) || !preg_match('/\bheight\s*=/i', $attrs)) {
            $fileRes['cwv'][] = "Line $lineNum: <img> missing explicit width/height (CLS risk)";
        }
        if (!preg_match('/\bloading\s*=\s*["\']lazy["\']/i', $attrs) && !preg_match('/\bfetchpriority\s*=\s*["\']high["\']/i', $attrs)) {
            $fileRes['cwv'][] = "Line $lineNum: <img> missing loading='lazy' or fetchpriority='high'";
        }
    }

    // Responsive / Overflow risks
    // Look for fixed pixel widths > 320px without max-w
    preg_match_all('/\b(?:w|min-w)-\[(\d+)px\]/i', $content, $fixedWMatches, PREG_OFFSET_CAPTURE);
    foreach ($fixedWMatches[0] as $idx => $m) {
        $px = (int)$fixedWMatches[1][$idx][0];
        $lineNum = substr_count(substr($content, 0, $m[1]), "\n") + 1;
        if ($px > 340) {
            // Check if there is responsive prefix like sm: or md: or lg:
            $matchContext = substr($content, max(0, $m[1] - 10), 10);
            if (!preg_match('/(?:sm|md|lg|xl|2xl):$/', $matchContext)) {
                $fileRes['responsive'][] = "Line $lineNum: Fixed width {$px}px without responsive breakpoint (overflow risk on mobile)";
            }
        }
    }

    // JS / TBT risks
    preg_match_all('/<script\b([^>]*)>(.*?)<\/script>/si', $content, $sm, PREG_OFFSET_CAPTURE);
    foreach ($sm[0] as $idx => $m) {
        $attrs = $sm[1][$idx][0];
        $body = trim($sm[2][$idx][0]);
        $lineNum = substr_count(substr($content, 0, $m[1]), "\n") + 1;
        if (preg_match('/type\s*=\s*["\']application\/ld\+json["\']/i', $attrs)) {
            continue; // JSON-LD is fine
        }
        $fileRes['js_tbt'][] = "Line $lineNum: Inline <script> (" . strlen($body) . " bytes) should be deferred or moved to asset bundle";
    }

    // CSS / Styles risks
    preg_match_all('/<style\b([^>]*)>(.*?)<\/style>/si', $content, $styleMatches, PREG_OFFSET_CAPTURE);
    foreach ($styleMatches[0] as $idx => $m) {
        $lineNum = substr_count(substr($content, 0, $m[1]), "\n") + 1;
        $body = trim($styleMatches[2][$idx][0]);
        $fileRes['css_assets'][] = "Line $lineNum: Inline <style> block (" . strlen($body) . " bytes) creates render blocking / asset bloat";
    }

    // Theme contrast
    preg_match_all('/class\s*=\s*["\']([^"\']+)["\']/i', $content, $classMatches, PREG_OFFSET_CAPTURE);
    $themeCount = 0;
    foreach ($classMatches[0] as $idx => $m) {
        $classes = $classMatches[1][$idx][0];
        $lineNum = substr_count(substr($content, 0, $m[1]), "\n") + 1;
        if (preg_match('/\bbg-(?:white|gray-(?:50|100))\b/', $classes) && !preg_match('/\bdark:bg-/', $classes)) {
            $fileRes['theme'][] = "Line $lineNum: bg-white/gray without dark:bg variant";
            $themeCount++;
        } elseif (preg_match('/\btext-(?:gray-(?:800|900)|black)\b/', $classes) && !preg_match('/\bdark:text-/', $classes)) {
            $fileRes['theme'][] = "Line $lineNum: dark text color without dark:text variant";
            $themeCount++;
        }
        if ($themeCount >= 5) break; // cap report
    }

    // i18n: Check missing translation keys
    preg_match_all("/(?:__|@lang|trans)\(\s*['\"]([^'\"]+)['\"]/u", $content, $transMatches, PREG_OFFSET_CAPTURE);
    foreach ($transMatches[1] as $idx => $tm) {
        $key = $tm[0];
        $lineNum = substr_count(substr($content, 0, $tm[1]), "\n") + 1;
        if (!isset($langKeys[$key])) {
            $fileRes['i18n'][] = "Line $lineNum: Missing translation key '$key'";
        }
    }

    $results[$file] = $fileRes;
}

// Print detailed issues
foreach ($results as $file => $r) {
    $totalIssues = count($r['seo']) + count($r['responsive']) + count($r['cwv']) + count($r['js_tbt']) + count($r['css_assets']) + count($r['theme']) + count($r['i18n']);
    if ($totalIssues > 0) {
        echo "========================================================\n";
        echo "FILE: $file ($totalIssues issues detected)\n";
        echo "========================================================\n";
        if (!empty($r['i18n'])) {
            echo "[Dimension 7: Multilingual/i18n]\n";
            foreach ($r['i18n'] as $iss) echo "  - $iss\n";
        }
        if (!empty($r['seo'])) {
            echo "[Dimension 5: SEO & Semantics]\n";
            foreach ($r['seo'] as $iss) echo "  - $iss\n";
        }
        if (!empty($r['cwv'])) {
            echo "[Dimension 2: Core Web Vitals & LCP/CLS]\n";
            foreach ($r['cwv'] as $iss) echo "  - $iss\n";
        }
        if (!empty($r['responsive'])) {
            echo "[Dimension 1: Responsive Design]\n";
            foreach ($r['responsive'] as $iss) echo "  - $iss\n";
        }
        if (!empty($r['js_tbt'])) {
            echo "[Dimension 3: JavaScript & TBT]\n";
            foreach ($r['js_tbt'] as $iss) echo "  - $iss\n";
        }
        if (!empty($r['css_assets'])) {
            echo "[Dimension 4: CSS & Asset Loading]\n";
            foreach ($r['css_assets'] as $iss) echo "  - $iss\n";
        }
        if (!empty($r['theme'])) {
            echo "[Dimension 6: Theme Contrast]\n";
            foreach ($r['theme'] as $iss) echo "  - $iss\n";
        }
        echo "\n";
    }
}
