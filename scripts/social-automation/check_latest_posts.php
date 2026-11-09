<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$posts = App\Models\SocialMediaPost::with(['targets', 'media'])->orderBy('created_at', 'desc')->get();
echo "========================================================================================\n";
echo "                      REKAPITULASI SELURUH POSTINGAN COOCA                              \n";
echo "========================================================================================\n";
foreach ($posts as $idx => $p) {
    $t = $p->targets->first();
    $targetTime = $t && $t->scheduled_at ? $t->scheduled_at->format('Y-m-d H:i:s') : 'N/A';
    $status = $t ? strtoupper($t->status) : 'UNKNOWN';
    $automationId = $p->metrics['automation_id'] ?? 'N/A';
    $topic = $p->metrics['topic_name'] ?? 'N/A';
    $mediaPath = $p->media->first() ? $p->media->first()->local_path : 'NO_MEDIA';
    echo sprintf(
        "%-2d | %-25s | %-9s | %-8s | %s | %s\n     Topic: %s\n     Media: %s\n",
        $idx + 1,
        $automationId,
        strtoupper($p->platform),
        strtoupper($p->media_type),
        $targetTime,
        $status,
        $topic,
        $mediaPath
    );
}
echo "========================================================================================\n";
