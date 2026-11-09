<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Web\PublicBlogController;
use App\Models\Post;
use Illuminate\Http\Request;

$slugs = [
    'isu-pajak-qris-dan-transaksi-digital-umkm',
    'cara-mencegah-retur-cod-paket-fiktif-marketplace',
    'strategi-promo-hari-pahlawan-produk-lokal-umkm',
    'panduan-pendaftaran-harbolnas-umkm-portal-smesta',
    'cara-mengelola-piutang-usaha-dan-penagihan-tempo',
    'sop-kasir-kafe-jam-sibuk-dan-split-bill',
    'cara-cuci-gudang-dead-stock-toko-ritel',
    'cara-menghitung-upah-lembur-karyawan-toko-umkm',
    'panduan-qris-cross-border-usaha-umkm',
    'cara-negosiasi-tempo-pembayaran-supplier-bahan-baku',
    'cara-menghitung-biaya-kemasan-ramah-lingkungan-hpp',
    'sop-penerimaan-barang-supplier-gudang-toko',
    'strategi-program-loyalty-point-membership-toko',
    'panduan-memilih-jasa-ekspedisi-kurir-toko-online',
    'cara-menyusun-anggaran-operasional-toko-umkm',
    'sop-kas-kecil-petty-cash-toko-ritel-imprest',
    'strategi-live-shopping-affiliate-tiktok-tokopedia-umkm',
    'cara-mengurus-sertifikasi-halal-reguler-lph',
    'standarisasi-resep-dan-recipe-costing-franchise',
    'cara-mengaktifkan-pelanggan-pasif-via-whatsapp',
];

echo "=== Verifying 20 Batch 5 Articles in PublicBlogController ===\n";

$allPassed = true;
$controller = $app->make(PublicBlogController::class);

// Create dummy session so session() in controller works
$sessionManager = $app->make('session');
$sessionStore = $sessionManager->driver('array');
$app->instance('session.store', $sessionStore);
session()->setId('testing-session-batch5');
session()->start();

foreach ($slugs as $idx => $slug) {
    $num = $idx + 1;
    $post = Post::published()->where('slug', $slug)->first();
    if (!$post) {
        echo "[$num/20] [FAIL] Slug not found or not published in DB: $slug\n";
        $allPassed = false;
        continue;
    }

    try {
        $view = $controller->show($slug);
        if (!$view instanceof \Illuminate\View\View) {
            echo "[$num/20] [FAIL] Controller did not return a View for: $slug\n";
            $allPassed = false;
            continue;
        }

        $html = $view->render();

        // Check for H1
        $hasH1 = preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $h1Matches);
        $h1Text = $hasH1 ? trim(strip_tags($h1Matches[1])) : '';

        // Check for JSON-LD schema
        $hasJsonLd = preg_match('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $ldMatches);
        $jsonValid = false;
        if ($hasJsonLd) {
            $decoded = json_decode($ldMatches[1], true);
            $jsonValid = ($decoded !== null && isset($decoded['@context']));
        }

        if (!$hasH1 || empty($h1Text)) {
            echo "[$num/20] [FAIL] Missing H1 in rendered HTML for: $slug\n";
            $allPassed = false;
        } elseif (!$hasJsonLd || !$jsonValid) {
            echo "[$num/20] [FAIL] Invalid Schema.org JSON-LD in: $slug\n";
            $allPassed = false;
        } else {
            echo "[$num/20] [PASS] HTTP 200 OK | H1: \"$h1Text\" | JSON-LD Valid | Slug: $slug\n";
        }
    } catch (\Throwable $e) {
        echo "[$num/20] [ERROR] Exception rendering $slug: " . $e->getMessage() . "\n";
        $allPassed = false;
    }
}

if ($allPassed) {
    echo "\n=== ALL 20 BATCH 5 ARTICLES VERIFIED SUCCESSFULLY IN CONTROLLER & VIEW! ===\n";
    exit(0);
} else {
    echo "\n=== SOME ARTICLES FAILED VERIFICATION! ===\n";
    exit(1);
}
