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

foreach ($files as $file) {
    echo "=== $file ===\n";
    $c = file_get_contents(__DIR__ . '/../../../' . $file);
    preg_match_all('/<i\s+[^>]*data-lucide=[^>]*>/i', $c, $matches, PREG_OFFSET_CAPTURE);
    foreach ($matches[0] as $match) {
        if (!str_contains($match[0], 'aria-hidden')) {
            $line = substr_count(substr($c, 0, $match[1]), "\n") + 1;
            echo "  Line $line: " . trim($match[0]) . "\n";
        }
    }
}
