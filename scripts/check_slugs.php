<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Solution Slugs ---\n";
// Check if Solution model exists or controller has list
try {
    if (class_exists(\App\Models\Solution::class)) {
        $solutions = \App\Models\Solution::all(['slug']);
        foreach ($solutions as $s) echo "Solution: " . $s->slug . "\n";
    }
} catch (\Throwable $e) { echo $e->getMessage() . "\n"; }

echo "--- Template Slugs ---\n";
try {
    \App\Models\ExcelTemplate::seedDefaultTemplatesIfEmpty();
    $templates = \App\Models\ExcelTemplate::all(['slug']);
    foreach ($templates as $t) echo "Template: " . $t->slug . "\n";
} catch (\Throwable $e) { echo $e->getMessage() . "\n"; }


echo "--- Business Slugs ---\n";
try {
    $biz = \App\Models\Business::where('is_active', true)->take(3)->get(['id', 'slug', 'name']);
    foreach ($biz as $b) echo "Business: " . $b->slug . " (" . $b->name . ")\n";
} catch (\Throwable $e) { echo $e->getMessage() . "\n"; }
