<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$dbs = \Illuminate\Support\Facades\DB::select('SHOW DATABASES');
foreach ($dbs as $dbObj) {
    $db = $dbObj->Database;
    try {
        $tables = \Illuminate\Support\Facades\DB::select("SHOW TABLES FROM `$db`");
        foreach ($tables as $t) {
            $tName = array_values((array)$t)[0];
            $cols = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM `$db`.`$tName`");
            $colNames = array_map(fn($c) => $c->Field, $cols);
            if (in_array('code', $colNames) || in_array('sku', $colNames)) {
                $col = in_array('code', $colNames) ? 'code' : 'sku';
                $found = \Illuminate\Support\Facades\DB::select("SELECT * FROM `$db`.`$tName` WHERE `$col` LIKE '%PRD-PRM%'");
                if (!empty($found)) {
                    echo "FOUND in $db.$tName ($col): \n";
                    print_r($found[0]);
                }
            }
        }
    } catch (\Throwable $e) {}
}
