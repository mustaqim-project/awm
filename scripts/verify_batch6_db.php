<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Web\PublicBlogController;
use App\Models\Post;

$slugs = [
    'cara-menyiapkan-daftar-inventaris-aset-tetap-pajak-umkm',
    'strategi-menghindari-denda-spt-masa-pph-final-umkm',
    'cara-rekonsiliasi-faktur-pajak-masukan-keluaran-pkp-ritel',
    'sop-pick-and-pack-gudang-toko-online-harbolnas',
    'cara-mengatasi-selisih-timbangan-paket-denda-kurir',
    'strategi-alokasi-stok-toko-fisik-dan-marketplace',
    'cara-membuat-proyeksi-kas-mingguan-umkm',
    'sop-penggajian-dan-bonus-akhir-tahun-karyawan-toko',
    'cara-mengelola-dana-cadangan-darurat-bisnis-ritel',
    'sop-audit-kasir-harian-dan-prosedur-void-pos',
    'panduan-kasir-self-checkout-qris-dinamis-umkm',
    'cara-integrasi-printer-dapur-dan-kasir-pos-kafe',
    'cara-menghitung-hpp-paket-katering-dan-nasi-kotak',
    'strategi-menu-engineering-kafe-matriks-bcg',
    'cara-menghitung-trimming-loss-bahan-baku-kuliner',
    'sop-pembuatan-surat-penawaran-harga-quotation-b2b',
    'cara-menetapkan-minimum-order-quantity-moq-grosir',
    'cara-mengelola-surat-jalan-dan-bast-pengiriman-b2b',
    'strategi-mengumpulkan-database-whatsapp-kasir-pos',
    'cara-kirim-whatsapp-pengingat-invoice-tempo-b2b',
];

echo "=== Verifying 20 Batch 6 Articles in PublicBlogController & Database ===\n";

$allPassed = true;
$controller = $app->make(PublicBlogController::class);

// Create dummy session so session() in controller works
$sessionManager = $app->make('session');
$sessionStore = $sessionManager->driver('array');
$app->instance('session.store', $sessionStore);
session()->setId('testing-session-batch6');
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
            echo "[$num/20] [FAIL] Controller did not return View for slug: $slug\n";
            $allPassed = false;
            continue;
        }

        $html = $view->render();
        $hasH1 = str_contains($html, '<h1');
        $hasJsonLd = str_contains($html, 'application/ld+json');
        $len = strlen($html);

        if (!$hasH1 || !$hasJsonLd) {
            echo "[$num/20] [FAIL] Render missing H1 or JSON-LD: $slug\n";
            $allPassed = false;
        } else {
            echo "[$num/20] [PASS] $slug (Length: $len bytes, H1: YES, JSON-LD: YES)\n";
        }
    } catch (\Throwable $e) {
        echo "[$num/20] [ERROR] Exception rendering $slug: " . $e->getMessage() . "\n";
        $allPassed = false;
    }
}

if ($allPassed) {
    echo "\n[SUCCESS] All 20 Batch 6 articles are published in DB and render 200 OK with H1 and JSON-LD!\n";
    exit(0);
} else {
    echo "\n[FAILED] One or more articles failed verification!\n";
    exit(1);
}
