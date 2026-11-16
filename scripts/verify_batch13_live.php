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
    699 => 'billing-coretax-djp-kadaluarsa-batas-15-november',
    700 => 'sanksi-pengawasan-halal-bpjph-2026-self-declare',
    701 => 'kebijakan-mdr-qris-terbaru-usaha-mikro-batas-500-ribu',
    702 => 'kalkulasi-hpp-hampers-natal-tahun-baru-bundling-pre-order',
    703 => 'antisipasi-banjir-kelembapan-gudang-logistik-harbolnas-12-12',
    704 => 'sop-kasir-blind-cash-drop-tutup-shift-jam-sibuk',
    705 => 'strategi-blast-whatsapp-bisnis-resmi-promo-akhir-tahun-anti-banned',
    706 => 'manajemen-aging-piutang-usaha-b2b-penagihan-tutup-buku-akhir-tahun',
    707 => 'sop-alokasi-stok-gudang-omnichannel-flash-sale-12-12',
    708 => 'sop-karyawan-paruh-waktu-musiman-toko-ritel-libur-akhir-tahun',
    709 => 'penyesuaian-master-data-produk-tarif-ppn-12-persen-kasir-pos',
    710 => 'cara-rekonsiliasi-arus-kas-qris-edc-transfer-bank-toko-ritel',
    711 => 'strategi-upselling-paket-bundling-menu-musim-hujan-kafe-kuliner',
    712 => 'mitigasi-retur-paket-cod-gagal-kirim-harbolnas-12-12-musim-hujan',
    713 => 'panduan-penyusunan-laporan-laba-rugi-umkm-akhir-tahun-sak-emkm',
    714 => 'cara-membangun-program-loyalitas-member-poin-retensi-pelanggan-toko',
    715 => 'sop-penerimaan-bahan-baku-kuliner-musim-hujan-cek-mutu-surat-jalan',
    716 => 'pengajuan-sisa-plafon-kur-2026-sektor-produksi-tutup-tahun',
    717 => 'hak-akses-role-based-rbac-kasir-supervisor-cegah-fraud-toko',
    718 => 'aplikasi-kasir-pos-multi-gudang-terintegrasi-toko-fisik-dan-online',
];

$emDash = chr(0xE2) . chr(0x80) . chr(0x94);
$enDash = chr(0xE2) . chr(0x80) . chr(0x93);

echo "========================================================\n";
echo "BATCH 13 LIVE DATABASE & CONTROLLER VERIFICATION\n";
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

    echo "PASSED (Word Count: " . str_word_count(strip_tags((string)$post->content)) . ")\n";
    $passedCount++;
}

echo "========================================================\n";
echo "SUCCESS! All {$passedCount}/20 articles verified live with 100% PASS!\n";
echo "========================================================\n";
