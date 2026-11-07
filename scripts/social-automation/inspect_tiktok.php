<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SocialMediaPost;

$post = SocialMediaPost::where('platform', 'tiktok')->first();
if ($post) {
    echo "=== POST DATA ===\n";
    print_r($post->toArray());
    echo "\n=== MEDIA DATA ===\n";
    foreach ($post->media as $m) {
        print_r($m->toArray());
    }
    echo "\n=== TARGETS DATA ===\n";
    foreach ($post->targets as $t) {
        print_r($t->toArray());
    }
} else {
    echo "No TikTok post found.\n";
}
