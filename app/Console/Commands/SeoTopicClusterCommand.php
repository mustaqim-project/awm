<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\Seo\SitemapService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeoTopicClusterCommand extends Command
{
    protected $signature = 'seo:topic-clusters {--apply : Apply cluster assignment and in-text links to database}';

    protected $description = 'Assign topic_cluster to blog posts and insert contextual internal links to product pages (Prompt 9)';

    public const CLUSTERS = [
        'warung' => [
            'url' => '/solusi/kasir-warung',
            'anchor' => 'aplikasi kasir warung',
            'title' => 'Kelola Kasir & Kasbon Warung Kelontong Lebih Rapi',
            'desc' => 'Catat bon hutang pelanggan via WhatsApp, scan barcode kemasan sembako dari kamera HP, dan pantau stok kulakan otomatis.',
            'btn' => 'Coba Aplikasi Kasir Warung',
        ],
        'cafe-restoran' => [
            'url' => '/solutions/fnb',
            'anchor' => 'aplikasi kasir restoran',
            'title' => 'Tingkatkan Efisiensi Meja & Resep Kedai Kopi Anda',
            'desc' => 'Kelola resep bahan baku per cup, meja & split bill, cetak tiket dapur, dan pantau HPP riil otomatis.',
            'btn' => 'Coba Aplikasi Kasir Restoran',
        ],
        'retail' => [
            'url' => '/solutions/retail',
            'anchor' => 'aplikasi kasir toko',
            'title' => 'Otomatisasi Kasir Toko & Stok Multi-Satuan',
            'desc' => 'Scan barcode cepat, kelola satuan dus/renceng/pcs, dan pantau stok menipis secara real-time.',
            'btn' => 'Coba Aplikasi Kasir Toko',
        ],
        'bengkel' => [
            'url' => '/solutions/workshop',
            'anchor' => 'aplikasi bengkel motor',
            'title' => 'Kelola SPK Servis & Stok Sparepart Bengkel',
            'desc' => 'SPK digital per nomor polisi, stok oli & sparepart, riwayat servis pelanggan, dan bagi hasil komisi montir otomatis.',
            'btn' => 'Coba Aplikasi Bengkel Motor',
        ],
        'laundry' => [
            'url' => '/solutions/laundry',
            'anchor' => 'aplikasi kasir laundry',
            'title' => 'Pantau Timbangan & Nomor Rak Laundry Kiloan',
            'desc' => 'Integrasi timbangan desimal, cetak label barcode per kantong cucian, dan kirim notifikasi WhatsApp otomatis saat selesai.',
            'btn' => 'Coba Aplikasi Kasir Laundry',
        ],
        'salon-barber' => [
            'url' => '/solusi/kasir-salon',
            'anchor' => 'aplikasi kasir salon',
            'title' => 'Atur Antrean Grooming & Bagi Hasil Komisi Barber',
            'desc' => 'Pilih layanan pangkas atau spa, catat tip & komisi kapster transparan, dan kirim nota digital ke WhatsApp.',
            'btn' => 'Coba Aplikasi Kasir Salon',
        ],
        'catering' => [
            'url' => '/solusi/aplikasi-catering',
            'anchor' => 'aplikasi catering',
            'title' => 'Hitung HPP Porsi & Invoice DP Acara Catering',
            'desc' => 'Hitung modal bahan per porsi kotak, atur timeline jadwal pengiriman event, dan kirim invoice termin DP bertahap.',
            'btn' => 'Coba Aplikasi Catering',
        ],
        'manufaktur-konveksi' => [
            'url' => '/solutions/manufacturing',
            'anchor' => 'software produksi UMKM',
            'title' => 'Kontrol Bahan Baku & HPP Produksi Konveksi',
            'desc' => 'Formula resep Bill of Materials (BOM), work order produksi, alokasi biaya overhead, dan HPP akurat per potong barang.',
            'btn' => 'Coba Software Produksi UMKM',
        ],
        'keuangan' => [
            'url' => '/omnichannel-erp/finance',
            'anchor' => 'aplikasi pembukuan UMKM',
            'title' => 'Buku Kasir Otomatis Menjadi Laporan Laba Rugi',
            'desc' => 'Tinggalkan rekap Excel manual. Setiap penjualan kasir langsung tercatat ke laporan laba rugi, arus kas, dan neraca.',
            'btn' => 'Coba Aplikasi Pembukuan UMKM',
        ],
        'stok' => [
            'url' => '/omnichannel-erp/inventory',
            'anchor' => 'aplikasi stok barang',
            'title' => 'Cegah Selisih Stok & Barang Hilang di Gudang',
            'desc' => 'Pantau stok persediaan multi-cabang secara real-time, stok opname cycle counting, dan reorder point otomatis.',
            'btn' => 'Coba Aplikasi Stok Barang',
        ],
        'hpp' => [
            'url' => '/blog/cara-menghitung-hpp',
            'anchor' => 'cara menghitung HPP',
            'title' => 'Hitung HPP Produk & Margin Laba Ideal Anda',
            'desc' => 'Gunakan rumus baku dan kalkulator HPP online kami untuk memastikan bisnis tidak menjual produk dalam keadaan rugi.',
            'btn' => 'Buka Panduan & Kalkulator HPP',
        ],
        'bep' => [
            'url' => '/blog/cara-menghitung-bep',
            'anchor' => 'cara menghitung BEP',
            'title' => 'Ketahui Titik Impas (BEP) Usaha Anda Hari Ini',
            'desc' => 'Simulasikan berapa porsi atau nominal omzet harian yang harus dicapai agar operasional bisnis tidak nombok.',
            'btn' => 'Buka Panduan & Kalkulator BEP',
        ],
        'pajak' => [
            'url' => '/kalkulator/pph-final',
            'anchor' => 'kalkulator PPh final UMKM',
            'title' => 'Hitung PPh Final UMKM 0,5% Sesuai Aturan PP 55/2022',
            'desc' => 'Simulasikan kewajiban pajak omzet tahunan dengan batas bebas pajak Rp500 juta secara cepat dan akurat.',
            'btn' => 'Buka Kalkulator PPh Final',
        ],
        'legalitas' => [
            'url' => '/blog/cara-membuat-nib',
            'anchor' => 'cara membuat NIB',
            'title' => 'Panduan Lengkap Legalitas Usaha & NIB Gratis',
            'desc' => 'Langkah demi langkah mendaftarkan Nomor Induk Berusaha (NIB) resmi lewat OSS RBA tanpa calo dan tanpa ribet.',
            'btn' => 'Baca Panduan NIB UMKM',
        ],
        'whatsapp' => [
            'url' => '/omnichannel/whatsapp',
            'anchor' => 'WhatsApp bisnis multi agent',
            'title' => '1 Nomor WhatsApp Bisnis untuk Banyak Staf Admin',
            'desc' => 'Sambungkan chat WhatsApp ke kasir POS dan sistem stok toko. Balas pesan pelanggan lebih cepat tanpa berebut HP.',
            'btn' => 'Coba WhatsApp Bisnis Multi Agent',
        ],
        'marketing' => [
            'url' => '/content-automation/content-creation',
            'anchor' => 'jadwal posting otomatis',
            'title' => 'Jadwalkan Posting Konten Medsos Otomatis dengan AI',
            'desc' => 'Buat caption promosi menarik dan jadwalkan postingan ke Instagram, TikTok, Facebook, dan Threads dalam satu klik.',
            'btn' => 'Coba Jadwal Posting Otomatis',
        ],
        'ai' => [
            'url' => '/omnichannel-erp/analytics',
            'anchor' => 'asisten AI bisnis',
            'title' => 'Asisten AI untuk Analisa Penjualan & Strategi Bisnis',
            'desc' => 'Tanyakan tren produk terlaris, prediksi stok menipis, dan strategi harga langsung lewat percakapan AI interaktif.',
            'btn' => 'Pelajari Asisten AI Bisnis',
        ],
        'karyawan' => [
            'url' => '/kalkulator/gaji-karyawan',
            'anchor' => 'kalkulator gaji karyawan',
            'title' => 'Hitung Gaji, Lembur & Komisi Karyawan Transparan',
            'desc' => 'Kelola absensi staf toko, shift kerja, dan pembagian komisi kasir tanpa salah hitung menggunakan kalkulator gaji.',
            'btn' => 'Buka Kalkulator Gaji Karyawan',
        ],
    ];

    public function handle(SitemapService $sitemapService): int
    {
        $apply = (bool) $this->option('apply');

        $this->info('=== PROMPT 9: ASSIGNING TOPIC CLUSTERS & INTERNAL PRODUCT LINKS ===');

        $posts = Post::all();
        $this->info("Total posts in database: {$posts->count()}");

        $stats = [];
        foreach (self::CLUSTERS as $key => $conf) {
            $stats[$key] = 0;
        }

        $tableRows = [];

        foreach ($posts as $post) {
            $cluster = $this->determineCluster($post);
            $stats[$cluster] = ($stats[$cluster] ?? 0) + 1;

            $mapping = self::CLUSTERS[$cluster];

            if ($apply) {
                $post->topic_cluster = $cluster;

                // Insert contextual internal link in paragraph 1 or 2 if not already present
                $content = $post->content;
                if (!str_contains($content, $mapping['url']) && !str_contains($content, $mapping['anchor'])) {
                    $content = $this->injectContextualLink($content, $mapping['url'], $mapping['anchor']);
                    $post->content = $content;
                }

                $post->save();
            }

            if (count($tableRows) < 25) {
                $tableRows[] = [
                    Str::limit($post->title, 40),
                    $post->slug,
                    $cluster,
                    $mapping['url'],
                    $mapping['anchor'],
                ];
            }
        }

        $this->info('Sample of 25 Post Classifications:');
        $this->table(['Title', 'Slug', 'Topic Cluster', 'Target URL', 'Target Anchor'], $tableRows);

        $summaryRows = [];
        foreach ($stats as $cluster => $count) {
            $conf = self::CLUSTERS[$cluster];
            $summaryRows[] = [$cluster, $count, $conf['url'], $conf['anchor']];
        }
        $this->info('Topic Cluster Distribution Summary:');
        $this->table(['Topic Cluster', 'Total Posts', 'Target URL', 'Anchor Text'], $summaryRows);

        if (!$apply) {
            $this->warn('DRY RUN ONLY. Run with `php artisan seo:topic-clusters --apply` to commit changes.');
            return 0;
        }

        $xml = $sitemapService->generateBlogXml();
        $this->info("Regenerated public/sitemap_blog.xml with updated posts (" . strlen($xml) . " bytes).");
        $this->info('Prompt 9 successfully applied!');
        return 0;
    }

    private function determineCluster(Post $post): string
    {
        $slug = ' ' . strtolower($post->slug) . ' ';
        $title = ' ' . strtolower($post->title) . ' ';
        $combined = $slug . ' ' . $title;

        // 1. Sektoral Niche (Specific industries first)
        if (preg_match('/\b(catering|katering|prasmanan|nasi-box|snack-box)\b/i', $combined)) {
            return 'catering';
        }
        if (preg_match('/\b(laundry|cucian|kiloan|setrika)\b/i', $combined)) {
            return 'laundry';
        }
        if (preg_match('/\b(bengkel|montir|sparepart|spk)\b/i', $combined)) {
            return 'bengkel';
        }
        if (preg_match('/\b(salon|barber|barbershop|pangkas|rambut|spa)\b/i', $combined)) {
            return 'salon-barber';
        }
        if (preg_match('/\b(konveksi|garmen|jahit|sablon|maklon)\b/i', $combined)) {
            return 'manufaktur-konveksi';
        }

        // 2. Pillars: HPP, BEP, Pajak, NIB Legalitas, Karyawan
        if (preg_match('/\b(hpp|harga-pokok|cogs|food-cost)\b/i', $combined) || str_contains($slug, 'hpp')) {
            return 'hpp';
        }
        if (preg_match('/\b(bep|break-even|titik-impas)\b/i', $combined) || str_contains($slug, 'bep')) {
            return 'bep';
        }
        if (preg_match('/\b(pajak|pph|spt|djp|npwp)\b/i', $combined)) {
            return 'pajak';
        }
        if (preg_match('/\b(nib|oss|legalitas|siup|izin-usaha|pirt|bpom|halal)\b/i', $combined)) {
            return 'legalitas';
        }
        if (preg_match('/\b(karyawan|gaji|lembur|shift|thr|staff|barberman|kasir-curang|kecurangan-kasir)\b/i', $combined)) {
            return 'karyawan';
        }

        // 3. Cafe & Warung
        if (preg_match('/\b(cafe|kafe|kopi|coffee|restoran|resto|fnb|barista|boba)\b/i', $combined)) {
            return 'cafe-restoran';
        }
        if (preg_match('/\b(warung|kelontong|sembako|kasbon|buku-tulis)\b/i', $combined)) {
            return 'warung';
        }

        // 4. Inventori & Stok
        if (preg_match('/\b(stok|persediaan|gudang|opname|fifo|fefo|reorder|dead-stock|kedaluwarsa)\b/i', $combined)) {
            return 'stok';
        }

        // 5. Retail Toko
        if (preg_match('/\b(retail|minimarket|toko|barcode|scanner|sku|printer|struk)\b/i', $combined)) {
            return 'retail';
        }

        // 6. Channels & AI
        if (preg_match('/\b(whatsapp|wa-bisnis)\b/i', $combined)) {
            return 'whatsapp';
        }
        if (preg_match('/\b(marketing|promosi|sosmed|instagram|tiktok|facebook|posting)\b/i', $combined)) {
            return 'marketing';
        }
        if (preg_match('/\b(ai|prediksi-penjualan|what-if|asisten-ai)\b/i', $combined)) {
            return 'ai';
        }

        // Default: Keuangan
        return 'keuangan';
    }

    private function injectContextualLink(string $content, string $url, string $anchor): string
    {
        // Find the first or second closing </p> tag
        $paragraphs = preg_split('/(<\/p>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (!$paragraphs || count($paragraphs) < 3) {
            // Append at top of content if paragraphs structure is non-standard
            return "<p>Untuk mempermudah operasional usaha Anda secara langsung, gunakan <a href=\"{$url}\" class=\"text-[#007AFF] font-bold hover:underline\">{$anchor}</a>.</p>\n\n" . $content;
        }

        // Inject into paragraph 1 (index 0) or paragraph 2 (index 2)
        $targetIndex = 0;
        if (strlen(strip_tags($paragraphs[0])) < 100 && isset($paragraphs[2])) {
            $targetIndex = 2;
        }

        $injection = " Untuk pengelolaan yang lebih praktis dan otomatis, Anda dapat memanfaatkan <a href=\"{$url}\" class=\"text-[#007AFF] font-bold hover:underline\">{$anchor}</a>.";
        $paragraphs[$targetIndex] .= $injection;

        return implode('', $paragraphs);
    }
}
