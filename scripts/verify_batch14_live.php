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
    719 => 'aturan-upah-minimum-umkm-pasca-putusan-mk-pp-49-2025',
    720 => 'strategi-packing-gudang-harbolnas-12-12-alur-batch-shipping',
    721 => 'sop-stock-opname-tutup-buku-akhir-tahun-sak-emkm',
    722 => 'cara-menyusun-laporan-keuangan-sak-emkm-akhir-tahun',
    723 => 'manajemen-penagihan-piutang-b2b-akhir-tahun-anti-macet',
    724 => 'mitigasi-retur-cod-marketplace-musim-hujan-cek-whatsapp',
    725 => 'kalkulasi-biaya-kemasan-musim-hujan-ke-hpp-produk',
    726 => 'sinkronisasi-stok-kasir-pos-dan-marketplace-harbolnas-12-12',
    727 => 'evaluasi-biaya-operasional-dan-overhead-akhir-tahun-umkm',
    728 => 'rekonsiliasi-settlement-marketplace-akhir-tahun-toko-online',
    729 => 'sop-serah-terima-kasir-shift-malam-dan-brankas-libur-nataru',
    730 => 'strategi-promo-tukar-poin-loyalitas-member-akhir-tahun',
    731 => 'sop-penerimaan-barang-retur-dan-klaim-supplier-toko',
    732 => 'cara-menghitung-target-omzet-dan-bep-cabang-baru-2027',
    733 => 'panduan-surat-jalan-dan-delivery-order-pengiriman-grosir-b2b',
    734 => 'strategi-menu-engineering-kafe-resto-evaluasi-akhir-tahun',
    735 => 'sop-antrean-kasir-jam-sibuk-split-bill-struk-dapur-cepat',
    736 => 'panduan-perpanjangan-sertifikat-halal-dan-izin-pirt-umkm',
    737 => 'strategi-whatsapp-broadcast-tersegmentasi-promo-gajian-12-12',
    738 => 'manajemen-kasbon-karyawan-dan-potong-payroll-slip-gaji-digital',
];

$emDash = chr(0xE2) . chr(0x80) . chr(0x94);
$enDash = chr(0xE2) . chr(0x80) . chr(0x93);

echo "========================================================\n";
echo "BATCH 14 LIVE DATABASE & CONTROLLER VERIFICATION\n";
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

    $passedCount++;
    echo "PASS [Title: " . mb_substr($post->title, 0, 35) . "... | Words: " . str_word_count(strip_tags((string)$post->content)) . "]\n";
}

echo "========================================================\n";
echo "RESULT: {$passedCount} / " . count($slugs) . " ARTICLES PASSED 100% VERIFICATION!\n";
echo "STATUS: READY FOR PRODUCTION DEPLOYMENT\n";
echo "========================================================\n";
