<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SocialMediaPost;

foreach (SocialMediaPost::all() as $p) {
    echo $p->id . ' | ' . strtoupper($p->platform) . ' | Status: ' . $p->status . ' | Sched: ' . ($p->scheduled_at ? $p->scheduled_at->toDateTimeString() : 'NULL') . ' | Pub: ' . ($p->published_at ? $p->published_at->toDateTimeString() : 'NULL') . "\n";
    foreach ($p->targets as $t) {
        echo "   -> Target: " . strtoupper($t->channel) . " | Status: " . $t->status . " | Sched: " . ($t->scheduled_at ? $t->scheduled_at->toDateTimeString() : 'NULL') . " | Pub: " . ($t->published_at ? $t->published_at->toDateTimeString() : 'NULL') . "\n";
    }
}
