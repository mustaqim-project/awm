<?php
$content = file_get_contents(__DIR__ . '/../../../resources/views/layouts/public_marketing.blade.php');
$lines = explode("\n", $content);
$potentialHardcoded = [];
foreach ($lines as $i => $line) {
    $lineNum = $i + 1;
    // Check for obvious Indonesian phrases in plain HTML text
    if (preg_match('/>\s*([A-Za-z0-9\s,\.\-&]+)\s*</', $line, $m)) {
        $text = trim($m[1]);
        if (strlen($text) > 3 && !preg_match('/^(\{\{|\@|\$|<|&nbsp;|\d+$)/', $text)) {
            // Check if contains typical Indonesian words
            if (preg_match('/\b(dan|atau|untuk|dengan|dari|yang|ke|di|ini|itu|kami|anda|gratis|fitur|bisnis|solusi|bantuan|hubungi|masuk|daftar|keluar|panduan|semua)\b/i', $text)) {
                $potentialHardcoded[] = "Line $lineNum: " . substr($text, 0, 80);
            }
        }
    }
}
echo "Found " . count($potentialHardcoded) . " potential hardcoded Indonesian lines:\n";
foreach (array_slice($potentialHardcoded, 0, 30) as $item) {
    echo "  $item\n";
}
