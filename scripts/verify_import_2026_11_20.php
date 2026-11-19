<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$research = json_decode(file_get_contents(__DIR__ . '/../database/data/articles/keyword-research-2026-11-20.json'), true);
$slugs = array_column($research['topics'], 'slug');
$posts = \App\Models\Post::whereIn('slug', $slugs)->get();

echo "Total expected: " . count($slugs) . PHP_EOL;
echo "Found in DB: " . $posts->count() . PHP_EOL;
$publishedCount = $posts->where('is_published', true)->count();
echo "Published in DB: " . $publishedCount . PHP_EOL;

if ($publishedCount === 40) {
    echo "VERIFICATION SUCCESS: ALL 40 ARTICLES ARE PUBLISHED IN DB!" . PHP_EOL;
} else {
    echo "WARNING: Only $publishedCount published out of 40!" . PHP_EOL;
}
