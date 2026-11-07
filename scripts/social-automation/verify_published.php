<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(\App\Domain\SocialMedia\AdminSocialMediaService::class);
$published = $svc->getPlatformPosts(12, ['status' => 'published']);
echo "Total published posts via AdminSocialMediaService: " . $published->total() . PHP_EOL;
foreach($published as $p) {
    echo " - [" . strtoupper($p->platform) . "] " . $p->status . " | Pub: " . $p->published_at . " | Media: " . $p->media->count() . " | Asset: " . ($p->local_media_paths[0] ?? 'N/A') . PHP_EOL;
}
