<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$migration = require __DIR__ . '/../../database/migrations/2026_11_07_000001_ensure_social_media_tables.php';
$migration->up();
echo "social_media_comments created: " . (\Illuminate\Support\Facades\Schema::hasTable('social_media_comments') ? 'YES' : 'NO') . PHP_EOL;

$svc = app(\App\Domain\SocialMedia\AdminSocialMediaService::class);
$posts = $svc->getPlatformPosts(12, ['status' => 'scheduled']);
echo "Total scheduled posts via AdminSocialMediaService: " . $posts->total() . PHP_EOL;
foreach($posts as $p) {
    echo " - [" . strtoupper($p->platform) . "] " . $p->status . " | Jadwal: " . $p->scheduled_at . " | Media: " . $p->media->count() . PHP_EOL;
}
