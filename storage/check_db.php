<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SocialMediaPost;

$post = SocialMediaPost::find('56377ee0-15e6-4beb-a971-ea186e469433');
if ($post) {
    echo "=== POST DETAILS ===\n";
    print_r($post->toArray());
    echo "\n=== TARGETS ===\n";
    foreach ($post->targets as $t) {
        print_r($t->toArray());
    }
    echo "\n=== MEDIA ===\n";
    foreach ($post->media as $m) {
        print_r($m->toArray());
    }
}
