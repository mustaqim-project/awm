<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Post;

echo "Total posts: " . Post::count() . PHP_EOL;
echo "Now(): " . now()->toIso8601String() . PHP_EOL;
echo "Max ID: " . Post::max('id') . PHP_EOL;
foreach (Post::latest('id')->take(25)->get() as $p) {
    echo "ID {$p->id}: {$p->slug} | {$p->title} | Published: {$p->published_at}" . PHP_EOL;
}

