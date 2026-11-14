<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$posts = \App\Models\Post::where('title', 'like', '%Pasti Untung%')
    ->orWhere('title', 'like', '%HPP%')
    ->take(10)
    ->get(['id', 'slug', 'title']);

echo "Total posts: " . $posts->count() . "\n";
foreach ($posts as $p) {
    echo $p->id . " | " . $p->slug . " | " . $p->title . "\n";
}
