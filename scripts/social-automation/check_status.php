<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SocialMediaPost;

echo "==========================================================================\n";
echo "  COOCA SOCIAL MEDIA DASHBOARD: AUDIT IDENTITAS & STANDALONE POSTS\n";
echo "==========================================================================\n\n";

$posts = SocialMediaPost::where('is_platform', true)
    ->orderBy('scheduled_at')
    ->get();

echo "Total Posts: " . $posts->count() . "\n\n";

$violations = 0;

foreach ($posts as $p) {
    $metrics = $p->metrics ?? [];
    $aid = $metrics['automation_id'] ?? 'LEGACY (no automation_id)';
    $topic = $metrics['topic_name'] ?? 'N/A';
    $fmt = $metrics['format'] ?? $p->media_type ?? 'N/A';
    $dims = $metrics['dimensions'] ?? 'N/A';
    $slot = $metrics['slot_label'] ?? 'N/A';
    $score = $metrics['viral_score'] ?? 'N/A';

    $hasTokoKamu = (stripos($p->content, 'Toko Kamu') !== false) || (stripos(json_encode($metrics), 'Toko Kamu') !== false);
    $hasOwner = !empty($metrics['owner_name']) || !empty($metrics['business_name']);

    if ($hasTokoKamu || $hasOwner) {
        $violations++;
        echo "[VIOLATION] Post ID {$p->id} masih memuat nama usaha/user!\n";
    }

    echo "ID: {$p->id}\n";
    echo "  Automation : {$aid}\n";
    echo "  Channel    : " . strtoupper($p->platform) . " | Format: {$fmt} ({$dims})\n";
    echo "  Slot       : {$slot}\n";
    echo "  Topik      : {$topic}\n";
    echo "  Skor Viral : {$score}\n";
    echo "  Audit Nama : " . ($hasTokoKamu || $hasOwner ? 'FAIL' : 'PASS (100% Bebas Nama Usaha/User)') . "\n";
    echo "  Status     : {$p->status} | Jadwal: " . ($p->scheduled_at ? $p->scheduled_at->format('d M Y H:i') . ' WIB' : 'NULL') . "\n";
    echo "--------------------------------------------------------------------------\n";
}

echo "\nAudit Selesai: {$violations} pelanggaran ditemukan dari {$posts->count()} postingan.\n";
