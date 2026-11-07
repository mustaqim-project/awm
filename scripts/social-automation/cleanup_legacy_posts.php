<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SocialMediaPost;
use App\Models\SocialPostMedia;
use App\Models\SocialPostTarget;

// Remove legacy posts that lack automation_id in metrics (created by old commands)
$legacyPosts = SocialMediaPost::where('is_platform', true)
    ->get()
    ->filter(function ($p) {
        $metrics = $p->metrics ?? [];
        return empty($metrics['automation_id']);
    });

$count = $legacyPosts->count();
echo "Found {$count} legacy posts without automation_id.\n";

foreach ($legacyPosts as $p) {
    echo "  Removing legacy: {$p->id} | " . strtoupper($p->platform) . " | {$p->status}\n";
    SocialPostMedia::where('social_media_post_id', $p->id)->delete();
    SocialPostTarget::where('social_media_post_id', $p->id)->delete();
    $p->delete();
}

echo "\nRemaining standalone posts:\n";
foreach (SocialMediaPost::where('is_platform', true)->get() as $p) {
    $metrics = $p->metrics ?? [];
    $aid = $metrics['automation_id'] ?? 'N/A';
    $topic = $metrics['topic_name'] ?? 'N/A';
    $fmt = $metrics['format'] ?? 'N/A';
    $dims = $metrics['dimensions'] ?? 'N/A';
    echo "{$p->id} | " . strtoupper($p->platform) . " | {$fmt} ({$dims}) | automation_id={$aid}\n";
    echo "   Topic: {$topic}\n";
}
