<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SocialMediaPost;

$posts = SocialMediaPost::latest()->take(4)->get();
foreach ($posts as $p) {
    echo strtoupper($p->platform) . ' | Sched: ' . $p->scheduled_at . PHP_EOL;
    echo '  Media URLs: ' . json_encode($p->media_urls) . PHP_EOL;
    echo '  Local paths: ' . json_encode($p->local_media_paths) . PHP_EOL;
}
