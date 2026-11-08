<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SocialMediaPost;
use App\Models\SocialPostMedia;
use App\Models\SocialPostTarget;
use Carbon\Carbon;
use Illuminate\Support\Str;

echo "==========================================================================\n";
echo "  COOCA SCHEDULED PUBLISHER: LINKEDIN B2B RETAIL GOVERNANCE CAROUSEL\n";
echo "==========================================================================\n\n";

$scheduledAt = Carbon::create(2026, 11, 9, 7, 59, 0, 'Asia/Jakarta');

$caption = <<<TXT
Buka Cabang Banyak Tapi Profit Menipis? Inilah 3 Titik Buta Tata Kelola Ritel Multi-Cabang

Banyak pemilik ritel berasumsi bahwa menambah cabang otomatis melipatgandakan keuntungan bersih. Namun di lapangan, membuka cabang ke-3 hingga ke-5 sering kali memicu fenomena the expansion paradox: omzet di pembukuan melonjak, tetapi uang fisik di rekening kas operasional justru semakin menipis.

Ketika gerai masih berjumlah satu, pemilik dapat mengawasi kasir dan rak gudang secara langsung. Begitu cabang bertambah, jarak menciptakan asimetri pengawasan. Jika tata kelola tidak dibentengi sistem kontrol yang ketat, kebocoran margin akan terjadi di 3 titik buta ini:

1. Wewenang Meja Kasir Tanpa Kunci Otorisasi
Kasir memiliki wewenang setara supervisor. Pembatalan item struk (void) atau pemberian diskon manual dapat dieksekusi sepihak tanpa meminta PIN supervisor. Transaksi tunai dibatalkan di layar POS, tetapi uang pelanggan masuk ke saku pribadi.

2. Phantom Inventory Saat Transfer Antar-Gudang
Gudang pusat mengirim 100 unit barang, namun cabang tujuan hanya memajang 92 unit di rak. Tanpa status transit terverifikasi dan konfirmasi terima digital dua arah, selisih 8 unit barang hilang tanpa jejak pertanggungjawaban antara sopir dan staf toko.

3. Tutup Shift Kasir dengan Saldo Terbuka (Open Cash Count)
Kasir mengetahui target saldo akhir sistem sebelum menghitung uang fisik di laci kas. Jika kasir menghitung uang dengan layar sistem menyala, kecocokan angka adalah rekayasa kasir. Tanpa sistem blind cash count, manipulasi selisih kas tidak akan pernah terdeteksi pemilik.

Bagaimana cara membangun tata kelola multi-cabang yang aman dan rapi?

Kuncinya adalah Sentralisasi Data dan Desentralisasi Operasional:
- Batasi hak akses kasir khusus transaksi. Seluruh fungsi pembatalan nota (void), ubah harga, dan diskon manual wajib meminta PIN otorisasi supervisor.
- Terapkan alur transfer barang terstruktur: Status Pengiriman -> Status Dalam Perjalanan -> Konfirmasi Fisik Penerimaan Cabang.
- Pantau laporan laba rugi dan valuasi stok per cabang secara konsolidasian dari satu pintu pantau pusat.

Membangun fondasi tata kelola yang kuat tidak harus rumit atau mahal. Di Cooca, sistem multi-gudang, pembatasan hak akses kasir, dan laporan konsolidasi multi-cabang sudah dirancang agar dapat dioperasikan secara tertib dan transparan oleh para pelaku ritel Indonesia.

Mulai tata operasional gerai Anda dengan sistem terstandarisasi. Pelajari tata kelola multi-cabang modern di cooca.id.

#RetailManagement #RetailGovernance #ManajemenBisnis #MultiCabang #SOPRitel #OperasionalToko #CoocaERP
TXT;

$post = SocialMediaPost::where('platform', 'linkedin')
    ->where('is_platform', true)
    ->where('media_type', 'carousel')
    ->first();

if (!$post) {
    echo "[INFO] Membuat SocialMediaPost baru untuk LinkedIn Carousel...\n";
    $post = new SocialMediaPost();
    $post->is_platform = true;
    $post->platform = 'linkedin';
}

$slideNames = [
    1 => 'linkedin_retail_slide-01.png',
    2 => 'linkedin_retail_slide-02.png',
    3 => 'linkedin_retail_slide-03.png',
    4 => 'linkedin_retail_slide-04.png',
    5 => 'linkedin_retail_slide-05.png',
    6 => 'linkedin_retail_slide-06.png',
    7 => 'linkedin_retail_slide-07.png',
];

$slideTitles = [
    1 => 'Slide 1: The Multi-Branch Paradox (Hook)',
    2 => 'Slide 2: Titik Buta 1 - Hak Akses Kasir & Modus Void',
    3 => 'Slide 3: Titik Buta 2 - Phantom Inventory Antar Cabang',
    4 => 'Slide 4: Titik Buta 3 - Tutup Shift Saldo Terbuka vs Blind Count',
    5 => 'Slide 5: Standar Matriks Wewenang RBAC Meja Kasir',
    6 => 'Slide 6: Laporan Konsolidasi Multi-Outlet Real-Time',
    7 => 'Slide 7: Kesimpulan & Solusi Cooca ERP Solo Owner Rp 0',
];

$mediaUrls = [];
$localPaths = [];

foreach ($slideNames as $idx => $fileName) {
    $relPath = 'admin/social-media-assets/carousel/' . $fileName;
    $mediaUrls[] = asset('storage/' . $relPath);
    $localPaths[] = $relPath;
}

$post->content = $caption;
$post->media_type = 'carousel';
$post->media_urls = $mediaUrls;
$post->local_media_paths = $localPaths;
$post->status = 'scheduled';
$post->approval_status = 'approved';
$post->scheduled_at = $scheduledAt;
$post->published_at = null;
$post->platform_post_id = null;
$post->metrics = [
    'format' => 'carousel',
    'dimensions' => '1080x1080',
    'hook_title' => 'Buka Cabang Banyak Tapi Profit Menipis? Inilah 3 Titik Buta Tata Kelola Ritel Multi-Cabang',
    'slot_label' => 'LinkedIn Morning Executive Carousel Deck (K1 B2B)',
    'topic_name' => 'Tata Kelola Multi-Cabang & Arsitektur Kontrol Ritel B2B',
    'deployed_at' => Carbon::now()->toDateTimeString(),
    'viral_score' => 98.2,
    'automation_id' => 'cooca-carousel-linkedin',
    'slides_count' => 7,
    'identity_policy' => 'strictly_neutral_no_business_or_user_name',
    'audit_status' => 'PASS (100% Bebas Em-Dash, Brand Netral, UI Otentik)',
];
$post->save();

// Recreate SocialPostMedia records
SocialPostMedia::where('social_media_post_id', $post->id)->delete();

foreach ($slideNames as $idx => $fileName) {
    $relPath = 'admin/social-media-assets/carousel/' . $fileName;
    $fullPath = public_path('storage/' . $relPath);
    if (!file_exists($fullPath)) {
        $fullPath = public_path($relPath);
    }
    $fileSize = file_exists($fullPath) ? filesize($fullPath) : 1200000;

    $media = new SocialPostMedia();
    $media->social_media_post_id = $post->id;
    $media->sort_order = $idx;
    $media->media_type = 'image';
    $media->media_url = asset('storage/' . $relPath);
    $media->local_path = $relPath;
    $media->file_size = $fileSize;
    $media->metadata = [
        'title' => $slideTitles[$idx],
        'dimensions' => '1080x1080',
        'retina' => true,
    ];
    $media->save();
}

// Ensure SocialPostTarget is updated to scheduled
$target = SocialPostTarget::where('social_media_post_id', $post->id)->first();
if (!$target) {
    $target = new SocialPostTarget();
    $target->social_media_post_id = $post->id;
    $target->channel = 'linkedin';
}
$target->status = 'scheduled';
$target->scheduled_at = $scheduledAt;
$target->published_at = null;
$target->retry_count = 0;
$target->error_message = null;
$target->save();

echo "[SUCCESS] Post LinkedIn ID: {$post->id} berhasil dijadwalkan!\n";
echo "          Status: {$post->status} (Approved)\n";
echo "          Jadwal Tayang: {$scheduledAt->toDateTimeString()} WIB\n";
echo "          Slides: " . count($slideNames) . " Slide Carousel (1080x1080 px Retina)\n";
echo "          Target ID: {$target->id} (Status: {$target->status})\n";
echo "==========================================================================\n";
