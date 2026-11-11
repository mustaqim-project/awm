<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Web\PublicBlogController;
use App\Models\Post;

$slugs = [
    'manajemen-lonjakan-pesanan-11-11-fulfillment-same-day',
    'cara-mengatasi-out-of-stock-backorder-marketplace',
    'sop-penanganan-resi-tertahan-investigasi-paket-hilang',
    'strategi-upselling-cross-selling-kasir-pos',
    'cara-menghitung-biaya-kemasan-musim-hujan-anti-bocor',
    'sop-rekonsiliasi-saldo-escrow-marketplace-payout',
    'cara-menyusun-laporan-perubahan-modal-umkm-sak-emkm',
    'strategi-mengelola-jam-lembur-staf-gudang',
    'panduan-menerapkan-dynamic-pricing-toko-umkm',
    'sop-pemeliharaan-mesin-kasir-pos-printer-thermal',
    'cara-menghitung-bep-layanan-pesan-antar-kuliner',
    'strategi-mengurangi-ketergantungan-diskon-harga-umkm',
    'sop-penerimaan-retur-toko-fisik-tukar-barang-kasir',
    'cara-negosiasi-biaya-sewa-kios-perpanjangan-2027',
    'panduan-standarisasi-barcode-sku-gs1-produk-umkm',
    'cara-mengelola-kasbon-karyawan-plafon-pinjaman-umkm',
    'strategi-reaktivasi-pelanggan-pasca-event-via-whatsapp',
    'sop-penyusunan-purchase-order-bahan-baku-12-12',
    'cara-menghitung-biaya-reverse-logistics-retur-ritel',
    'panduan-izin-edar-kosmetik-notifikasi-bpom-umkm',
];

echo "=== Verifying 20 Batch 8 Articles in PublicBlogController & Database ===\n";

$allPassed = true;
$controller = $app->make(PublicBlogController::class);

// Create dummy session so session() in controller works
$sessionManager = $app->make('session');
$sessionStore = $sessionManager->driver('array');
$app->instance('session.store', $sessionStore);
session()->setId('testing-session-batch8');
session()->start();

foreach ($slugs as $idx => $slug) {
    $num = $idx + 1;
    $post = Post::published()->where('slug', $slug)->first();
    if (!$post) {
        echo "[$num/20] [FAIL] Slug not found or not published in DB: $slug\n";
        $allPassed = false;
        continue;
    }

    // Check no em dash or en dash in post attributes
    $fieldsToCheck = [
        'title' => $post->title,
        'meta_title' => $post->meta_title,
        'meta_description' => $post->meta_description,
        'content' => $post->content,
    ];

    $hasDash = false;
    foreach ($fieldsToCheck as $fname => $fval) {
        if ($fval && (str_contains($fval, "\u{2014}") || str_contains($fval, "\u{2013}"))) {
            echo "[$num/20] [FAIL] Post $fname contains em/en dash: $slug\n";
            $hasDash = true;
            $allPassed = false;
        }
    }
    if ($hasDash) {
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
            echo "[$num/20] [PASS] $slug (Length: $len bytes, H1: YES, JSON-LD: YES, Dash: NO)\n";
        }
    } catch (\Throwable $e) {
        echo "[$num/20] [ERROR] Exception rendering $slug: " . $e->getMessage() . "\n";
        $allPassed = false;
    }
}

if ($allPassed) {
    echo "\n[SUCCESS] All 20 Batch 8 articles verified successfully in Database and Live Controller!\n";
    exit(0);
} else {
    echo "\n[ERROR] One or more articles failed verification!\n";
    exit(1);
}
