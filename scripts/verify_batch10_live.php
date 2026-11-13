<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Post;
use App\Http\Controllers\Web\PublicBlogController;

$targetSlugs = [
    'tata-cara-validasi-nik-npwp-16-digit-transaksi-b2b',
    'persiapan-tutup-buku-tahun-2026-umkm-sak-emkm',
    'manajemen-piutang-dagang-akhir-tahun-umkm-net-30',
    'sop-inventarisasi-aset-fisik-toko-dan-depresiasi-alat',
    'sop-penyimpanan-stok-gudang-musim-hujan-anti-lembab',
    'manajemen-armada-dan-rute-delivery-toko-musim-hujan',
    'strategi-safety-stock-bahan-baku-kuliner-musim-hujan',
    'strategi-persiapan-stok-toko-menjelang-harbolnas-12-12',
    'sop-kasir-dan-penanganan-antrean-panjang-peak-season',
    'strategi-bundling-hampers-natal-tahun-baru-untung-maksimal',
    'manajemen-order-b2b-corporate-gift-akhir-tahun',
    'cara-broadcast-whatsapp-akhir-tahun-anti-banned',
    'program-poin-loyalitas-tukar-reward-akhir-tahun-ritel',
    'strategi-reaktivasi-database-pelanggan-lama-lewat-wa',
    'cara-mengelola-retur-paket-cod-dan-rekonsiliasi-stok',
    'strategi-cash-flow-pacing-alokasi-dana-kulakan-grosir',
    'sop-pengecekan-dan-kalibrasi-hardware-pos-toko-ritel',
    'manajemen-shift-kerja-lembur-karyawan-toko-nataru',
    'sop-serah-terima-kasir-pergantian-shift-toko-ritel',
    'cara-evaluasi-kinerja-supplier-bahan-baku-akhir-tahun'
];

echo "Verifying " . count($targetSlugs) . " Batch 10 articles in DB and Controller...\n";

$dbPosts = Post::whereIn('slug', $targetSlugs)->get()->keyBy('slug');
echo "Found in Database: " . $dbPosts->count() . " / " . count($targetSlugs) . "\n";

$controller = app(PublicBlogController::class);

$emDashErrors = 0;
$controllerErrors = 0;
$schemaErrors = 0;

foreach ($targetSlugs as $idx => $slug) {
    if (!isset($dbPosts[$slug])) {
        echo "MISSING in DB: {$slug}\n";
        continue;
    }
    $post = $dbPosts[$slug];
    
    // 1. Check em/en dash in DB fields
    $fields = ['title' => $post->title, 'content' => $post->content, 'meta_title' => $post->meta_title, 'meta_description' => $post->meta_description];
    foreach ($fields as $fn => $fv) {
        if (str_contains((string)$fv, '—') || str_contains((string)$fv, '–')) {
            echo "DASH ERROR in DB [{$fn}]: {$slug}\n";
            $emDashErrors++;
        }
    }
    
    // 2. Test controller render on all 20 articles
    try {
        $view = $controller->show($slug);
        if ($view instanceof \Illuminate\View\View) {
            $renderedHtml = $view->render();
            if (empty($renderedHtml)) {
                echo "Empty HTML rendered for {$slug}\n";
                $controllerErrors++;
            } else {
                // Check dash in rendered HTML
                if (str_contains($renderedHtml, '—') || str_contains($renderedHtml, '–')) {
                    echo "DASH ERROR in Rendered HTML: {$slug}\n";
                    $emDashErrors++;
                }
                // Check H1
                if (!str_contains($renderedHtml, '<h1')) {
                    echo "H1 Missing in Rendered HTML: {$slug}\n";
                    $controllerErrors++;
                }
                // Check Schema.org
                if (!str_contains($renderedHtml, 'schema.org')) {
                    echo "Schema.org Missing in Rendered HTML: {$slug}\n";
                    $schemaErrors++;
                }
            }
        } else {
            echo "Non-view returned for {$slug}\n";
            $controllerErrors++;
        }
    } catch (\Throwable $e) {
        echo "Exception on {$slug}: " . $e->getMessage() . "\n";
        $controllerErrors++;
    }
}

echo "\n================ VERIFICATION SUMMARY ================\n";
echo "  Total Target Articles: " . count($targetSlugs) . "\n";
echo "  Found in Database: " . $dbPosts->count() . " / 20\n";
echo "  Zero Em Dash & En Dash Violations: " . ($emDashErrors === 0 ? "PASSED (0 violations)" : "FAILED ({$emDashErrors} violations)") . "\n";
echo "  Live Blade Render Check: " . ($controllerErrors === 0 ? "PASSED (20/20 views OK)" : "FAILED ({$controllerErrors} errors)") . "\n";
echo "  Schema.org Structured Data Check: " . ($schemaErrors === 0 ? "PASSED (20/20 valid)" : "FAILED ({$schemaErrors} errors)") . "\n";

if ($dbPosts->count() === 20 && $emDashErrors === 0 && $controllerErrors === 0 && $schemaErrors === 0) {
    echo "STATUS: ALL 20 BATCH 10 ARTICLES ARE LIVE, VALID, AND 100% PRODUCTION-READY!\n";
} else {
    exit(1);
}
