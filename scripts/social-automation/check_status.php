<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SocialMediaPost;

echo "==========================================================================\n";
echo "  COOCA SOCIAL MEDIA DASHBOARD: ALL SCHEDULED STANDALONE POSTS\n";
echo "==========================================================================\n\n";

$posts = SocialMediaPost::where('is_platform', true)
    ->orderBy('scheduled_at')
    ->get();

echo "Total Posts: " . $posts->count() . "\n\n";

foreach ($posts as $p) {
    $metrics = $p->metrics ?? [];
    $aid = $metrics['automation_id'] ?? 'LEGACY (no automation_id)';
    $topic = $metrics['topic_name'] ?? 'N/A';
    $fmt = $metrics['format'] ?? $p->media_type ?? 'N/A';
    $dims = $metrics['dimensions'] ?? 'N/A';
    $slot = $metrics['slot_label'] ?? 'N/A';
    $score = $metrics['viral_score'] ?? 'N/A';

    echo "ID: {$p->id}\n";
    echo "  Automation : {$aid}\n";
    echo "  Channel    : " . strtoupper($p->platform) . " | Format: {$fmt} ({$dims})\n";
    echo "  Slot       : {$slot}\n";
    echo "  Topik      : {$topic}\n";
    echo "  Skor Viral : {$score}\n";
    echo "  Status     : {$p->status} | Jadwal: " . ($p->scheduled_at ? $p->scheduled_at->format('d M Y H:i') . ' WIB' : 'NULL') . "\n";
    foreach ($p->targets as $t) {
        echo "  -> Target  : " . strtoupper($t->channel) . " | {$t->status} | " . ($t->scheduled_at ? $t->scheduled_at->format('d M Y H:i') : 'NULL') . "\n";
    }
    echo "--------------------------------------------------------------------------\n";
}
