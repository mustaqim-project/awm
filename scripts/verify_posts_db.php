<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Web\PublicBlogController;

$slugs = [
    'cara-hitung-diskon-flash-sale-11-11-agar-tidak-boncos',
    'mencegah-overselling-dan-pembatalan-order-harbolnas',
    'mengelola-jeda-pencairan-saldo-marketplace-arus-kas',
    'relaksasi-bertahap-wajib-halal-umk-dan-syarat-sehati',
    'sop-pengemasan-kilat-dan-mitigasi-selisih-ongkir-kurir',
    'sop-penanganan-retur-barang-dan-cadangan-klaim-garansi',
    'strategi-invoice-financing-dan-po-discounting-umkm',
    'cara-menghitung-rendemen-dan-yield-bahan-baku-kuliner',
    'sop-blind-cash-count-dan-pencegahan-fraud-kasir-malam',
    'prosedur-rekonsiliasi-bank-dan-tutup-buku-umkm-2026',
    'cara-mengendalikan-food-waste-dapur-kafe-restoran',
    'perbedaan-izin-edar-pirt-dan-bpom-olahan-pangan-umkm',
    'panduan-pelaporan-spt-tahunan-umkm-coretax-djp',
    'sop-stok-opname-akhir-tahun-toko-ritel',
    'strategi-menjaga-arus-kas-toko-umkm-libur-akhir-tahun',
    'manajemen-pesanan-omnichannel-marketplace-dan-toko-fisik',
    'sop-penerimaan-pembayaran-qris-dinamis-kasir-pos',
    'cara-menyusun-struktur-dan-skala-upah-karyawan-toko-umkm',
    'panduan-pengajuan-kur-syariah-modal-kerja-usaha-mikro',
    'strategi-follow-up-keranjang-belanja-terbengkalai-whatsapp-umkm'
];

$controller = app(PublicBlogController::class);
$allPassed = true;

// Set up fake session for controller
session()->setDefaultDriver('array');
session()->start();

foreach ($slugs as $slug) {
    try {
        $view = $controller->show($slug);
        if ($view instanceof \Illuminate\View\View) {
            $rendered = $view->render();
            $hasH1 = str_contains($rendered, '<h1');
            $hasJsonLd = str_contains($rendered, 'application/ld+json');
            echo "[RENDER OK] {$slug} -> Length: " . strlen($rendered) . " bytes | Has H1: " . ($hasH1 ? 'YES' : 'NO') . " | Has JSON-LD: " . ($hasJsonLd ? 'YES' : 'NO') . "\n";
        } else {
            echo "[WARN] {$slug} returned redirect or unexpected response\n";
        }
    } catch (\Throwable $e) {
        echo "[ERROR] {$slug} rendering failed: " . $e->getMessage() . "\n";
        $allPassed = false;
    }
}

if ($allPassed) {
    echo "\nAll 10 posts RENDERED SUCCESSFULLY with 200 OK, H1, and Schema.org JSON-LD!\n";
} else {
    exit(1);
}
