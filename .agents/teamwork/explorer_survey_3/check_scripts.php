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

foreach ($scopeDirs as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $f) {
        if ($f->isFile() && str_ends_with($f->getFilename(), '.blade.php')) {
            $path = str_replace('\\', '/', $f->getPathname());
            $content = file_get_contents($path);
            preg_match_all('/<script\b([^>]*)>(.*?)<\/script>/si', $content, $m, PREG_OFFSET_CAPTURE);
            foreach ($m[0] as $idx => $match) {
                $attrs = $m[1][$idx][0];
                $body = trim($m[2][$idx][0]);
                if (stripos($attrs, 'application/ld+json') !== false) {
                    continue;
                }
                $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                echo "$path : Line $line (len: " . strlen($body) . ")\n";
                echo "  Attrs: " . trim($attrs) . "\n";
                echo "  Snippet: " . substr(str_replace("\n", " ", $body), 0, 100) . "...\n\n";
            }
        }
    }
}
