<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\PublicBlogController;

$slugs = [
    739 => 'panduan-coretax-djp-umkm-kode-billing-pph-final',
    740 => 'cara-menghitung-hpp-hampers-natal-tahun-baru-kuliner',
    741 => 'cara-menyusun-proyeksi-arus-kas-q1-awal-tahun-umkm',
    742 => 'strategi-bundling-dan-flash-sale-harbolnas-12-12-umkm',
    743 => 'sop-pengemasan-paket-musim-hujan-ekspedisi-anti-rusak',
    744 => 'format-evaluasi-kpi-karyawan-toko-dan-bonus-akhir-tahun',
    745 => 'manfaat-perpres-38-2026-kewirausahaan-nasional-dan-nib-oss',
    746 => 'sop-reservasi-meja-restoran-dan-kafe-libur-nataru',
    747 => 'cara-stock-opname-cepat-dengan-barcode-scanner-toko',
    748 => 'manajemen-stok-toko-fisik-dan-live-shopping-video-commerce',
    749 => 'cara-mencatat-biaya-barang-rusak-dan-kedaluwarsa-sak-emkm',
    750 => 'strategi-reaktivasi-pelanggan-dormant-whatsapp-akhir-tahun',
    751 => 'panduan-padisera-padi-umkm-dan-ekatalog-lkpp-v6',
    752 => 'sop-pengelolaan-kas-kembalian-dan-modal-awal-laci-kasir',
    753 => 'strategi-purchase-order-bahan-baku-antisipasi-libur-supplier',
    754 => 'menu-engineering-musim-hujan-substitusi-bahan-baku-kafe',
    755 => 'tata-cara-pembagian-dividen-dan-laba-ditahan-akhir-tahun-cv-pt',
    756 => 'kampanye-kedaluwarsa-poin-loyalitas-member-dorong-repeat-order',
    757 => 'audit-keamanan-kasir-pos-cegah-modus-void-pembatalan-palsu',
    758 => 'panduan-merekrut-karyawan-part-time-musiman-harbolnas-liburan',
];

$emDash = chr(0xE2) . chr(0x80) . chr(0x94);
$enDash = chr(0xE2) . chr(0x80) . chr(0x93);

echo "========================================================\n";
echo "BATCH 15 LIVE DATABASE & CONTROLLER VERIFICATION\n";
echo "========================================================\n";

$sitemap = file_get_contents(__DIR__ . '/../public/sitemap_blog.xml');
$blogLinksData = json_decode(file_get_contents(__DIR__ . '/../blog_links.json'), true);
$linkUrls = array_column($blogLinksData['links'] ?? $blogLinksData, 'url');
$blogLinksSet = array_flip($linkUrls);

$passedCount = 0;
$controller = new PublicBlogController();

foreach ($slugs as $id => $slug) {
    echo "Testing #{$id} [{$slug}] ... ";

    // 1. Database check
    $post = Post::published()->where('slug', $slug)->first();
    if (!$post) {
        echo "FAILED: Post not found or not published!\n";
        exit(1);
    }

    // 2. Dash check
    if (str_contains($post->title, $emDash) || str_contains($post->title, $enDash)) {
        echo "FAILED: Em/En dash found in title!\n";
        exit(1);
    }
    if (str_contains((string)$post->content, $emDash) || str_contains((string)$post->content, $enDash)) {
        echo "FAILED: Em/En dash found in content!\n";
        exit(1);
    }
    if (str_contains((string)$post->meta_description, $emDash) || str_contains((string)$post->meta_description, $enDash)) {
        echo "FAILED: Em/En dash found in meta_description!\n";
        exit(1);
    }

    // 3. Controller Rendering Check
    $view = $controller->show($slug);
    $html = $view->render();

    if (!str_contains($html, '<h1')) {
        echo "FAILED: Missing H1 in rendered HTML!\n";
        exit(1);
    }
    if (!str_contains($html, 'https://schema.org') || !str_contains($html, 'FAQPage')) {
        echo "FAILED: Missing Schema.org FAQPage in rendered HTML!\n";
        exit(1);
    }
    if (str_contains($html, $emDash) || str_contains($html, $enDash)) {
        echo "FAILED: Rendered HTML contains em/en dash!\n";
        exit(1);
    }

    // 4. Sitemap & blog_links check
    $url = "https://cooca.id/blog/{$slug}";
    if (!str_contains($sitemap, "<loc>{$url}</loc>")) {
        echo "FAILED: Missing in sitemap_blog.xml!\n";
        exit(1);
    }
    if (!isset($blogLinksSet[$url])) {
        echo "FAILED: Missing in blog_links.json!\n";
        exit(1);
    }

    echo "PASSED 100%\n";
    $passedCount++;
}

echo "========================================================\n";
echo "SUCCESS: All {$passedCount}/" . count($slugs) . " Batch 15 articles verified LIVE!\n";
echo "========================================================\n";
