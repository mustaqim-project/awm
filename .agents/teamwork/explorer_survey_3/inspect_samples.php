<?php

function inspectFile(string $file) {
    echo "========================================================\n";
    echo "INSPECTING: $file\n";
    echo "========================================================\n";
    if (!file_exists($file)) {
        echo "File does not exist!\n";
        return;
    }
    $content = file_get_contents($file);
    $lines = explode("\n", $content);

    // 1. Title, SEO sections
    echo "--- Head / SEO / Layout Directives ---\n";
    foreach (array_slice($lines, 0, 40) as $i => $l) {
        if (preg_match('/@(extends|section|push)/i', $l)) {
            echo "Line " . ($i + 1) . ": " . trim($l) . "\n";
        }
    }

    // 2. Sample hardcoded text
    echo "--- Sample Hardcoded Plain Text ---\n";
    preg_match_all('/>\s*([A-Za-z0-9][^<>{}\@]{15,})\s*</u', $content, $m, PREG_OFFSET_CAPTURE);
    $count = 0;
    foreach ($m[1] as $match) {
        $str = trim($match[0]);
        if (!preg_match('/^(?:function|var|const|let|console|document|window)\b/', $str)) {
            $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
            echo "Line $line: $str\n";
            $count++;
            if ($count >= 6) break;
        }
    }

    // 3. Sample Theme issues
    echo "--- Sample Theme Contrast Issues ---\n";
    preg_match_all('/class\s*=\s*["\']([^"\']+)["\']/i', $content, $cm, PREG_OFFSET_CAPTURE);
    $tCount = 0;
    foreach ($cm[1] as $match) {
        $classes = $match[0];
        $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
        $issue = '';
        if (preg_match('/\bbg-(?:white|gray-(?:50|100))\b/', $classes) && !preg_match('/\bdark:bg-/', $classes)) {
            $issue = 'Missing dark:bg';
        } elseif (preg_match('/\btext-(?:gray-(?:800|900)|black)\b/', $classes) && !preg_match('/\bdark:text-/', $classes)) {
            $issue = 'Missing dark:text';
        } elseif (preg_match('/\bborder-(?:gray-(?:200|300)|slate-200)\b/', $classes) && !preg_match('/\bdark:border-/', $classes)) {
            $issue = 'Missing dark:border';
        }
        if ($issue !== '') {
            echo "Line $line [$issue]: " . substr($classes, 0, 70) . "...\n";
            $tCount++;
            if ($tCount >= 4) break;
        }
    }

    // 4. Sample Scripts / Inline JS
    echo "--- Inline Scripts / TBT ---\n";
    preg_match_all('/<script\b[^>]*>(.*?)<\/script>/si', $content, $sm, PREG_OFFSET_CAPTURE);
    foreach ($sm[0] as $match) {
        $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
        $body = trim(preg_replace('/^<script\b[^>]*>|<\/script>$/si', '', $match[0]));
        echo "Line $line [Len: " . strlen($body) . "]: " . substr($body, 0, 80) . "...\n";
    }
}

$sampleFiles = [
    "resources/views/public/blog/index.blade.php",
    "resources/views/public/blog/show.blade.php",
    "resources/views/public/business-operating-system/how-it-works.blade.php",
    "resources/views/public/calculators/hpp.blade.php",
    "resources/views/public/calculators/index.blade.php",
    "resources/views/public/contact/index.blade.php",
    "resources/views/public/content-automation/content-calendar.blade.php",
    "resources/views/public/marketplace/index.blade.php",
    "resources/views/public/marketplace/search.blade.php",
    "resources/views/public/omnichannel/whatsapp.blade.php",
    "resources/views/public/omnichannel-erp/pos.blade.php",
    "resources/views/public/omnichannel-erp/accounting.blade.php",
    "resources/views/public/resources/faq.blade.php",
    "resources/views/public/resources/guides.blade.php",
    "resources/views/public/templates/index.blade.php",
    "resources/views/public/templates/show.blade.php"
];

foreach ($sampleFiles as $f) {
    inspectFile($f);
}
