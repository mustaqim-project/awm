<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$posts = App\Models\SocialMediaPost::with('targets')->orderBy('created_at', 'desc')->take(9)->get();
echo "========================================================================================\n";
echo "                      REKAPITULASI 9 POSTINGAN SCHEDULED COOCA                          \n";
echo "========================================================================================\n";
foreach ($posts as $idx => $p) {
    $t = $p->targets->first();
    $targetTime = $t ? $t->scheduled_at->format('Y-m-d H:i:s') : 'N/A';
    $status = $t ? strtoupper($t->status) : 'UNKNOWN';
    echo sprintf(
        "%-2d | ID: %-36s | %-9s | %-8s | %s | Status: %s\n",
        $idx + 1,
        $p->id,
        strtoupper($p->platform),
        strtoupper($p->media_type),
        $targetTime,
        $status
    );
}
echo "========================================================================================\n";
