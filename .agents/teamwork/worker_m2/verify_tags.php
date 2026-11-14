<?php
$files = glob('resources/views/public/solutions/*.blade.php');
foreach ($files as $file) {
    $c = file_get_contents($file);
    preg_match_all('/<div[\s>]/', $c, $do);
    preg_match_all('/<\/div>/', $c, $dc);
    preg_match_all('/<section[\s>]/', $c, $so);
    preg_match_all('/<\/section>/', $c, $sc);
    echo basename($file) . " -> div: " . count($do[0]) . "/" . count($dc[0]) . " | section: " . count($so[0]) . "/" . count($sc[0]) . PHP_EOL;
}
