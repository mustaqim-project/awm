<?php
$sf = require __DIR__ . '/../../../lang/id/storefront.php';
var_dump(isset($sf['reservations']));
var_dump(isset($sf['reservations']['status_confirmed']));

function flatten_keys($arr, $prefix = '') {
    $keys = [];
    foreach ($arr as $k => $v) {
        $p = $prefix === '' ? (string)$k : "$prefix.$k";
        if (is_array($v)) {
            $keys = array_merge($keys, flatten_keys($v, $p));
        } else {
            $keys[$p] = true;
        }
    }
    return $keys;
}

$flat = flatten_keys($sf);
var_dump(isset($flat['reservations.status_confirmed']));
