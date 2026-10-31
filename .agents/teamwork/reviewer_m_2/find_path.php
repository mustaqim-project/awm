<?php
$sf = require __DIR__ . '/../../../lang/id/storefront.php';

function find_key($arr, $target, $path = '') {
    foreach ($arr as $k => $v) {
        $current = $path === '' ? $k : "$path.$k";
        if ($k === $target) {
            echo "Found target '$target' at: $current\n";
        }
        if (is_array($v)) {
            find_key($v, $target, $current);
        }
    }
}

find_key($sf, 'reservations');
find_key($sf, 'status_confirmed');
