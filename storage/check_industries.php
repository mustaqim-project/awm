<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cats = \Illuminate\Support\Facades\DB::table('inspiration_categories')->get();
echo "Categories in DB:\n";
foreach ($cats as $c) {
    echo "- id: {$c->id}, name: {$c->name}, slug: {$c->slug}\n";
}
