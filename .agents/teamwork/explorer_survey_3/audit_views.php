<?php

$scopeDirs = [
    "resources/views/public/blog",
    "resources/views/public/business-operating-system",
    "resources/views/public/calculators",
    "resources/views/public/contact",
    "resources/views/public/content-automation",
    "resources/views/public/marketplace",
    "resources/views/public/omnichannel",
    "resources/views/public/omnichannel-erp",
    "resources/views/public/resources",
    "resources/views/public/templates"
];

$bladeFiles = [];
foreach ($scopeDirs as $dir) {
    if (is_dir($dir)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($it as $f) {
            if ($f->isFile() && str_ends_with($f->getFilename(), '.blade.php')) {
                $bladeFiles[] = str_replace('\\', '/', $f->getPathname());
            }
        }
    }
}
sort($bladeFiles);

$report = [];

foreach ($bladeFiles as $file) {
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    $lineCount = count($lines);

    // 1. Heading hierarchy (H1 count & locations)
    preg_match_all('/<h1\b[^>]*>(.*?)<\/h1>/si', $content, $h1Matches, PREG_OFFSET_CAPTURE);
    $h1Count = count($h1Matches[0]);
    $h1Details = [];
    foreach ($h1Matches[0] as $m) {
        $lineNum = substr_count(substr($content, 0, $m[1]), "\n") + 1;
        $h1Details[] = ['line' => $lineNum, 'tag' => substr($m[0], 0, 100)];
    }

    // 2. Images check (alt, width, height, loading)
    preg_match_all('/<img\b([^>]*)>/si', $content, $imgMatches, PREG_OFFSET_CAPTURE);
    $imagesWithoutAlt = [];
    $imagesWithoutDimensions = [];
    $imagesWithoutLazy = [];
    foreach ($imgMatches[0] as $idx => $m) {
        $attrs = $imgMatches[1][$idx][0];
        $lineNum = substr_count(substr($content, 0, $m[1]), "\n") + 1;
        if (!preg_match('/\balt\s*=\s*["\'][^"\']*["\']/i', $attrs)) {
            $imagesWithoutAlt[] = ['line' => $lineNum, 'html' => substr($m[0], 0, 80)];
        }
        if (!preg_match('/\bwidth\s*=/i', $attrs) || !preg_match('/\bheight\s*=/i', $attrs)) {
            $imagesWithoutDimensions[] = ['line' => $lineNum, 'html' => substr($m[0], 0, 80)];
        }
        if (!preg_match('/\bloading\s*=/i', $attrs)) {
            $imagesWithoutLazy[] = ['line' => $lineNum, 'html' => substr($m[0], 0, 80)];
        }
    }

    // 3. Scripts and inline JS
    preg_match_all('/<script\b([^>]*)>(.*?)<\/script>/si', $content, $scriptMatches, PREG_OFFSET_CAPTURE);
    $inlineScripts = [];
    foreach ($scriptMatches[0] as $idx => $m) {
        $lineNum = substr_count(substr($content, 0, $m[1]), "\n") + 1;
        $attrs = $scriptMatches[1][$idx][0];
        $body = trim($scriptMatches[2][$idx][0]);
        $inlineScripts[] = [
            'line' => $lineNum,
            'hasSrc' => (bool)preg_match('/\bsrc\s*=/i', $attrs),
            'length' => strlen($body),
            'preview' => substr($body, 0, 60)
        ];
    }

    // 4. Dark mode coverage: check for bg-white, text-gray-900, text-black, bg-gray-50 without dark:
    // We look for class="..." with bg-white or bg-gray-50/100 without dark:bg
    $potentialThemeIssues = [];
    preg_match_all('/class\s*=\s*["\']([^"\']+)["\']/i', $content, $classMatches, PREG_OFFSET_CAPTURE);
    foreach ($classMatches[0] as $idx => $m) {
        $classes = $classMatches[1][$idx][0];
        $lineNum = substr_count(substr($content, 0, $m[1]), "\n") + 1;
        // Check if class has bg-white but NOT dark:bg
        if (preg_match('/\bbg-(?:white|gray-(?:50|100))\b/', $classes) && !preg_match('/\bdark:bg-/', $classes)) {
            $potentialThemeIssues[] = ['line' => $lineNum, 'type' => 'missing dark:bg', 'class' => substr($classes, 0, 60)];
        }
        // Check if class has text-gray-(?:800|900) but NOT dark:text
        if (preg_match('/\btext-(?:gray-(?:800|900)|black)\b/', $classes) && !preg_match('/\bdark:text-/', $classes)) {
            $potentialThemeIssues[] = ['line' => $lineNum, 'type' => 'missing dark:text', 'class' => substr($classes, 0, 60)];
        }
        // Check if class has border-gray-(?:200|300) but NOT dark:border
        if (preg_match('/\bborder-(?:gray-(?:200|300)|slate-200)\b/', $classes) && !preg_match('/\bdark:border-/', $classes)) {
            $potentialThemeIssues[] = ['line' => $lineNum, 'type' => 'missing dark:border', 'class' => substr($classes, 0, 60)];
        }
    }

    // 5. Hardcoded Indonesian/English text detection outside Blade directives
    // Strip comments, php blocks, script blocks, style blocks
    $clean = preg_replace('/{{--.*?--}}/s', '', $content);
    $clean = preg_replace('/@php.*?@endphp/s', '', $clean);
    $clean = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $clean);
    $clean = preg_replace('/<style\b[^>]*>.*?<\/style>/si', '', $clean);
    // Find text inside tags like <h1-6>, <p>, <span>, <button>, <a>, <div> that is plain text without {{ or @
    preg_match_all('/>\s*([A-Za-z0-9][^<>{}\@]{10,})\s*</u', $clean, $rawTextMatches, PREG_OFFSET_CAPTURE);
    $hardcodedStrings = [];
    foreach ($rawTextMatches[1] as $rtm) {
        $str = trim($rtm[0]);
        // filter out whitespace or code remnants
        if (strlen($str) > 12 && !preg_match('/^(?:function|var|const|let|console|document|window)\b/', $str)) {
            $lineNum = substr_count(substr($clean, 0, $rtm[1]), "\n") + 1;
            $hardcodedStrings[] = ['line' => $lineNum, 'text' => substr($str, 0, 60)];
        }
    }

    $report[$file] = [
        'lines' => $lineCount,
        'h1Count' => $h1Count,
        'h1Details' => $h1Details,
        'imgCount' => count($imgMatches[0]),
        'imgWithoutAlt' => count($imagesWithoutAlt),
        'imgWithoutDim' => count($imagesWithoutDimensions),
        'imgWithoutLazy' => count($imagesWithoutLazy),
        'inlineScripts' => $inlineScripts,
        'themeIssueCount' => count($potentialThemeIssues),
        'themeIssuesSample' => array_slice($potentialThemeIssues, 0, 5),
        'hardcodedCount' => count($hardcodedStrings),
        'hardcodedSample' => array_slice($hardcodedStrings, 0, 5)
    ];
}

// Print summary table
echo "=== SUMMARY AUDIT ACROSS 43 BLADE TEMPLATES ===\n";
printf("%-55s | %5s | %3s | %4s | %4s | %4s | %6s | %5s\n", "File", "Lines", "H1", "Imgs", "!Alt", "!Dim", "Theme!", "Hard!");
echo str_repeat("-", 100) . "\n";

foreach ($report as $f => $d) {
    $short = str_replace('resources/views/public/', '', $f);
    printf("%-55s | %5d | %3d | %4d | %4d | %4d | %6d | %5d\n",
        $short,
        $d['lines'],
        $d['h1Count'],
        $d['imgCount'],
        $d['imgWithoutAlt'],
        $d['imgWithoutDim'],
        $d['themeIssueCount'],
        $d['hardcodedCount']
    );
}
