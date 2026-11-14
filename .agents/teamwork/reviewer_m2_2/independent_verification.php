<?php

$files = [
    'resources/views/public/solutions/fnb.blade.php',
    'resources/views/public/solutions/laundry.blade.php',
    'resources/views/public/solutions/manufacturing.blade.php',
    'resources/views/public/solutions/retail.blade.php',
    'resources/views/public/solutions/services.blade.php',
    'resources/views/public/solutions/show.blade.php',
    'resources/views/public/solutions/workshop.blade.php',
];

echo "=== INDEPENDENT VERIFICATION OF 7 SOLUTIONS TEMPLATES ===\n\n";

$allPassed = true;

// 1. PHP Syntax Check
echo "--- 1. PHP Syntax Check (php -l) ---\n";
foreach ($files as $file) {
    $fullPath = __DIR__ . '/../../../' . $file;
    if (!file_exists($fullPath)) {
        echo "[FAIL] File not found: $file\n";
        $allPassed = false;
        continue;
    }
    $cmd = 'php -l ' . escapeshellarg($fullPath);
    exec($cmd, $output, $returnCode);
    if ($returnCode === 0) {
        echo "[PASS] php -l: $file\n";
    } else {
        echo "[FAIL] php -l: $file -> " . implode("\n", $output) . "\n";
        $allPassed = false;
    }
    unset($output);
}

// 2. DOM Tag Hierarchy & Stack Balancing
echo "\n--- 2. DOM Tag Hierarchy & Stack Balancing ---\n";
$pairedTags = ['div', 'section', 'article', 'main', 'nav', 'aside', 'ul', 'ol', 'li', 'form', 'table', 'summary', 'details'];

foreach ($files as $file) {
    $fullPath = __DIR__ . '/../../../' . $file;
    $content = file_get_contents($fullPath);

    // Strip Blade comments {{-- ... --}}
    $cleanContent = preg_replace('/\{\{--.*?--\}\}/s', '', $content);
    // Strip script and style blocks
    $cleanContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $cleanContent);
    $cleanContent = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $cleanContent);

    $counts = [];
    $errors = [];

    foreach ($pairedTags as $tag) {
        $openMatches = preg_match_all('/<' . $tag . '\b(?![^>]*\/>)[^>]*>/i', $cleanContent);
        $closeMatches = preg_match_all('/<\/' . $tag . '>/i', $cleanContent);
        $counts[$tag] = [
            'open' => $openMatches,
            'close' => $closeMatches
        ];
        if ($openMatches !== $closeMatches) {
            $errors[] = "$tag: open=$openMatches vs close=$closeMatches (delta: " . ($openMatches - $closeMatches) . ")";
        }
    }

    if (empty($errors)) {
        echo "[PASS] $file tag balance: ";
        foreach (['div', 'section', 'summary', 'details'] as $t) {
            echo "$t: {$counts[$t]['open']}/{$counts[$t]['close']} ";
        }
        echo "\n";
    } else {
        echo "[FAIL] $file tag imbalance:\n  " . implode("\n  ", $errors) . "\n";
        $allPassed = false;
    }
}

// 3. Heading Hierarchy Verification
echo "\n--- 3. Heading Hierarchy Verification ---\n";
foreach ($files as $file) {
    $fullPath = __DIR__ . '/../../../' . $file;
    $content = file_get_contents($fullPath);

    $isIndustry = !str_contains($file, 'show.blade.php');
    $fileIssues = [];

    // Verify bottom CTA has h2
    if (!preg_match('/<h2[^>]*>[\s\S]*?(?:public_solutions\.[a-z_]+bottom|fnb_bottom_cta_title|lnd_bottom_cta_alt|mfg_bottom_cta_alt|ret_bottom_cta_alt|srv_bottom_cta_alt|wrk_bottom_cta_title|show_bottom_title)[\s\S]*?<\/h2>/i', $content)) {
        $fileIssues[] = "Bottom CTA does not appear to use <h2>";
    }

    // Verify Flow steps use h3
    if ($isIndustry) {
        // Find the flow section
        if (preg_match('/<!-- Alur Operasional -->|<h2[^>]*>[^<]*?(?:Alur|Flow|Langkah|Cara Kerja)[\s\S]*?<\/section>/iu', $content, $match)) {
            // Check if h4 exists inside this flow section
            if (preg_match_all('/<h4\b[^>]*>/i', $match[0], $h4Matches)) {
                $fileIssues[] = "Flow steps section contains " . count($h4Matches[0]) . " <h4> tags; expected <h3>";
            }
        }
    }

    // Verify cross-links section uses h2 and cards use h3
    if (preg_match('/{{-- Related Modules & Vertical Cross Links --}}|{{-- Cross-Link to Other Verticals --}}/i', $content)) {
        // extract from cross-links comment to next section or end
        $parts = preg_split('/{{-- Related Modules & Vertical Cross Links --}}|{{-- Cross-Link to Other Verticals --}}/i', $content);
        if (isset($parts[1])) {
            $crossSection = substr($parts[1], 0, strpos($parts[1], '</section>') + 10);
            if (!preg_match('/<h2[^>]*>[\s\S]*?(?:cross_section_title|show_other_solutions_title)[\s\S]*?<\/h2>/i', $crossSection)) {
                $fileIssues[] = "Cross links section title is not <h2>";
            }
            if (preg_match_all('/<h4\b[^>]*>/i', $crossSection, $h4Matches)) {
                $fileIssues[] = "Cross links section contains " . count($h4Matches[0]) . " <h4> tags; expected <h3>";
            }
        }
    }

    if (empty($fileIssues)) {
        echo "[PASS] Heading hierarchy: $file\n";
    } else {
        echo "[FAIL] Heading hierarchy: $file:\n  " . implode("\n  ", $fileIssues) . "\n";
        $allPassed = false;
    }
}

// 4. Mobile Grid Layout Upgrade
echo "\n--- 4. Mobile Grid Layout Upgrade ---\n";
foreach ($files as $file) {
    if (str_contains($file, 'show.blade.php')) {
        // In show.blade.php, cross-links grid was grid-cols-2 sm:grid-cols-4
        continue;
    }
    $fullPath = __DIR__ . '/../../../' . $file;
    $content = file_get_contents($fullPath);

    // Look for grid-cols-1 sm:grid-cols-2 lg:grid-cols-4
    $gridMatches = preg_match_all('/grid-cols-1\s+sm:grid-cols-2\s+lg:grid-cols-4/i', $content);
    // Ensure no old grid-cols-2 md:grid-cols-4 remains for flow or cross-links
    $oldMatches = preg_match_all('/grid-cols-2\s+md:grid-cols-4/i', $content);

    if ($gridMatches >= 2 && $oldMatches === 0) {
        echo "[PASS] Responsive grids ($gridMatches found, 0 legacy): $file\n";
    } else {
        echo "[WARN/FAIL] Responsive grids: $file (Found $gridMatches new, $oldMatches legacy)\n";
        if ($oldMatches > 0 || $gridMatches < 2) $allPassed = false;
    }
}

// 5. Contrast Classes on Feature Icons
echo "\n--- 5. Feature Icon Contrast Classes ---\n";
foreach ($files as $file) {
    $fullPath = __DIR__ . '/../../../' . $file;
    $content = file_get_contents($fullPath);

    // Check for unstyled text-amber-500, text-emerald-500, text-cyan-500 on icon containers
    preg_match_all('/class="[^"]*\btext-(amber|emerald|cyan)-500\b(?![^"]*dark:text-)[^"]*"/i', $content, $badContrastMatches);
    
    // Check for upgraded contrast classes
    $amberUpgraded = preg_match('/text-amber-600\s+dark:text-amber-400/i', $content);
    $emeraldUpgraded = preg_match('/text-emerald-600\s+dark:text-emerald-400/i', $content);
    
    if (count($badContrastMatches[0]) === 0) {
        echo "[PASS] Dual-theme icon contrast: $file (Amber upgraded: " . ($amberUpgraded ? 'YES' : 'N/A') . ", Emerald upgraded: " . ($emeraldUpgraded ? 'YES' : 'N/A') . ")\n";
    } else {
        echo "[WARN] Contrast issue in $file: " . count($badContrastMatches[0]) . " instances of unstyled single-theme *-500 text\n";
        foreach (array_slice($badContrastMatches[0], 0, 3) as $m) {
            echo "   $m\n";
        }
    }
}

// 6. Schema.org JSON-LD escaping and structure
echo "\n--- 6. Schema.org JSON-LD Escaping & Structure ---\n";
foreach ($files as $file) {
    $fullPath = __DIR__ . '/../../../' . $file;
    $content = file_get_contents($fullPath);

    // Check if JSON-LD scripts exist
    if (preg_match_all('/<script\s+type="application\/ld\+json">([\s\S]*?)<\/script>/i', $content, $jsonLdBlocks)) {
        foreach ($jsonLdBlocks[1] as $idx => $block) {
            // Check for unescaped @context outside of json_encode
            if (preg_match('/(?<!@)@context/i', $block) && !str_contains($block, 'json_encode')) {
                echo "[FAIL] $file: block #$idx has unescaped @context: $block\n";
                $allPassed = false;
            } elseif (str_contains($block, '@@context') || str_contains($block, 'json_encode')) {
                echo "[PASS] $file: block #$idx properly handles @context\n";
            }
        }
    } else {
        echo "[FAIL] $file: No JSON-LD block found!\n";
        $allPassed = false;
    }
}

// 7. FAQ <summary> Touch Target min-height >= 44px
echo "\n--- 7. FAQ <summary> Touch Target min-height >= 44px ---\n";
foreach ($files as $file) {
    if (str_contains($file, 'show.blade.php')) {
        // show.blade.php has dynamic faqs or summary
    }
    $fullPath = __DIR__ . '/../../../' . $file;
    $content = file_get_contents($fullPath);

    if (preg_match_all('/<summary\b([^>]*)>/i', $content, $summaryMatches)) {
        $nonCompliant = 0;
        foreach ($summaryMatches[1] as $attrs) {
            if (!str_contains($attrs, 'min-h-[44px]') && !str_contains($attrs, 'h-11') && !str_contains($attrs, 'min-h-11')) {
                $nonCompliant++;
            }
        }
        if ($nonCompliant === 0) {
            echo "[PASS] FAQ <summary> touch targets (" . count($summaryMatches[0]) . " compliant): $file\n";
        } else {
            echo "[FAIL] FAQ <summary> touch targets: $file has $nonCompliant non-compliant summaries!\n";
            $allPassed = false;
        }
    } else {
        echo "[INFO] No <summary> elements in $file\n";
    }
}

// 8. Decorative Lucide Icons aria-hidden="true"
echo "\n--- 8. Decorative Lucide Icons aria-hidden='true' ---\n";
foreach ($files as $file) {
    $fullPath = __DIR__ . '/../../../' . $file;
    $content = file_get_contents($fullPath);

    preg_match_all('/<i\s+[^>]*data-lucide=[^>]*>/i', $content, $lucideMatches);
    $missingAria = 0;
    foreach ($lucideMatches[0] as $tag) {
        if (!str_contains($tag, 'aria-hidden="true"') && !str_contains($tag, "aria-hidden='true'")) {
            $missingAria++;
        }
    }
    $total = count($lucideMatches[0]);
    if ($missingAria === 0) {
        echo "[PASS] Lucide icons aria-hidden ($total / $total compliant): $file\n";
    } else {
        echo "[WARN] Lucide icons aria-hidden in $file: $missingAria / $total missing aria-hidden\n";
    }
}

echo "\n=======================================================\n";
echo "OVERALL VERIFICATION RESULT: " . ($allPassed ? "ALL OBJECTIVES MET" : "FAILURES DETECTED") . "\n";
