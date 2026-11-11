<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Post;
use App\Http\Controllers\Web\PublicBlogController;

$slug = 'manajemen-lonjakan-pesanan-11-11-fulfillment-same-day';
$post = Post::where('slug', $slug)->first();

echo "Post content has em-dash: " . (str_contains($post->content, "\u{2014}") ? "YES" : "NO") . "\n";
echo "Post content has en-dash: " . (str_contains($post->content, "\u{2013}") ? "YES" : "NO") . "\n";
echo "Post title has em-dash: " . (str_contains($post->title, "\u{2014}") ? "YES" : "NO") . "\n";
echo "Post meta_title has em-dash: " . (str_contains($post->meta_title, "\u{2014}") ? "YES" : "NO") . "\n";

$sessionManager = $app->make('session');
$app->instance('session.store', $sessionManager->driver('array'));
session()->start();

$controller = $app->make(PublicBlogController::class);
$view = $controller->show($slug);
$html = $view->render();

$lines = explode("\n", $html);
foreach ($lines as $i => $line) {
    if (str_contains($line, "\u{2014}") || str_contains($line, "\u{2013}")) {
        echo "Line " . ($i + 1) . " has dash: " . trim($line) . "\n";
    }
}
