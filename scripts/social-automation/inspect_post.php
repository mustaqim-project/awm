<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$posts = \App\Models\SocialMediaPost::all();
foreach ($posts as $p) {
    echo "ID: " . $p->id . PHP_EOL;
    echo "Platform: " . $p->platform . PHP_EOL;
    echo "is_platform: " . ($p->is_platform ? 'YES' : 'NO') . PHP_EOL;
    echo "Status: " . $p->status . PHP_EOL;
    echo "Media Type: " . $p->media_type . PHP_EOL;
    echo "Scheduled At: " . ($p->scheduled_at ? $p->scheduled_at->toDateTimeString() : 'NULL') . PHP_EOL;
    echo "Published At: " . ($p->published_at ? $p->published_at->toDateTimeString() : 'NULL') . PHP_EOL;
    echo "Media URLs: " . json_encode($p->media_urls) . PHP_EOL;
    echo "Local Media Paths: " . json_encode($p->local_media_paths) . PHP_EOL;
    echo "Content Preview: " . mb_substr($p->content, 0, 150) . "..." . PHP_EOL;
    echo "Metrics: " . json_encode($p->metrics) . PHP_EOL;
    echo "Targets count: " . $p->targets()->count() . PHP_EOL;
    foreach ($p->targets as $t) {
        echo "  - Target: " . $t->channel . " | Status: " . $t->status . " | Sched: " . ($t->scheduled_at ? $t->scheduled_at->toDateTimeString() : 'NULL') . " | Pub: " . ($t->published_at ? $t->published_at->toDateTimeString() : 'NULL') . PHP_EOL;
    }
    echo "--------------------------------------------------------" . PHP_EOL;
}
