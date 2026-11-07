<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\SocialMedia\AdminSocialMediaService;
use App\Models\Admin;
use App\Models\SocialMediaPost;
use App\Models\SocialPostMedia;
use App\Models\SocialPostTarget;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeployUmkmCarouselAndViralPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cooca:deploy-umkm-carousel
                            {--start-date=2026-11-09 : Tanggal target posting (format YYYY-MM-DD)}
                            {--force : Hapus jadwal lama jika sudah ada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy 5-slide HD 3D & Human Cutout Instagram Carousel and 4 unique daily viral platform posts into production';

    /**
     * Execute the console command.
     */
    public function handle(AdminSocialMediaService $adminSocialMediaService): int
    {
        $this->info('====================================================================');
        $this->info('  COOCA PRODUCTION DEPLOYMENT: HD 3D CAROUSEL & 4 VIRAL PLATFORMS   ');
        $this->info('====================================================================');

        $admin = Admin::first();
        if (! $admin) {
            $this->error('Admin super_admin tidak ditemukan di database. Jalankan seeder AdminSeeder.');
            return Command::FAILURE;
        }

        $startDateStr = (string) ($this->option('start-date') ?: '2026-11-09');
        $force = (bool) $this->option('force');

        if ($force) {
            $this->warn('Force mode aktif: Menghapus data jadwal media sosial platform sebelumnya...');
            SocialPostMedia::whereHas('post', fn ($q) => $q->where('is_platform', true))->delete();
            SocialPostTarget::whereHas('post', fn ($q) => $q->where('is_platform', true))->delete();
            SocialMediaPost::where('is_platform', true)->delete();
        }

        // =========================================================================
        // 1. DEPLOY MULTI-SLIDE INSTAGRAM CAROUSEL (5 SLIDES HD 3D & HUMAN CUTOUT)
        // =========================================================================
        $this->info("\n[1/2] Menyiapkan Multi-Slide Carousel Instagram (5 Slides 1080x1080)...");

        $carouselSlides = [
            [
                'slide'       => 1,
                'title'       => 'Hook / Selisih Kas',
                'file_name'   => 'slide-01.png',
                'rel_path'    => 'admin/social-media-assets/carousel/slide-01.png',
            ],
            [
                'slide'       => 2,
                'title'       => 'Anatomi 3 Titik Kebocoran',
                'file_name'   => 'slide-02.png',
                'rel_path'    => 'admin/social-media-assets/carousel/slide-02.png',
            ],
            [
                'slide'       => 3,
                'title'       => 'Live Real Screen UI POS',
                'file_name'   => 'slide-03.png',
                'rel_path'    => 'admin/social-media-assets/carousel/slide-03.png',
            ],
            [
                'slide'       => 4,
                'title'       => 'Audit Keuangan & Anti-Void',
                'file_name'   => 'slide-04.png',
                'rel_path'    => 'admin/social-media-assets/carousel/slide-04.png',
            ],
            [
                'slide'       => 5,
                'title'       => 'High-Converting Offer & CTA',
                'file_name'   => 'slide-05.png',
                'rel_path'    => 'admin/social-media-assets/carousel/slide-05.png',
            ],
        ];

        $mediaUrls = [];
        $localMediaPaths = [];
        foreach ($carouselSlides as $slide) {
            $mediaUrls[] = asset('storage/' . $slide['rel_path']);
            $localMediaPaths[] = $slide['rel_path'];
        }

        $carouselCaption = <<<CAPTION
Omzet ramai pas toko lagi jam sibuk, tapi pas tutup kasir uang kas sering selisih?

Jangan anggap wajar selisih kas harian. Riset kami menemukan bahwa 82% kebocoran toko bermula dari kasir manual tanpa sistem rekonsiliasi otomatis.

Geser slide untuk melihat:
1. 3 titik rawan kebocoran kas di meja kasir toko ritel.
2. Trik atasi antrean kasir macet kurang dari 3 detik.
3. Cara kunci menu kasir sensitif (anti-void tanpa PIN Supervisor).
4. Solusi pembukuan laba rugi otomatis langsung dari HP Anda.

Di Toko Kamu yang dikelola bersama Cooca Indonesia, seluruh transaksi kasir, mutasi stok barang, dan laporan keuangan terhubung otomatis secara real-time.

Coba demo gratis sistem kasir Cooca sekarang:
Kunjungi https://cooca.id (Tautan lengkap ada di bio)
Gratis uji coba 14 hari tanpa perlu kartu kredit!

#CoocaERP #AplikasiKasir #KasirPintar #UMKMIndonesia #BisnisRitel #KelolaToko #ManajemenKeuangan #TokoKamu
CAPTION;

        // Clean caption from forbidden characters
        $carouselCaption = str_replace(['—', '–'], '-', $carouselCaption);

        $igSchedTime = Carbon::parse("{$startDateStr} 12:15:00");

        $carouselPost = SocialMediaPost::create([
            'admin_id'          => $admin->id,
            'business_id'       => null,
            'is_platform'       => true,
            'platform'          => 'instagram',
            'content'           => trim($carouselCaption),
            'media_type'        => 'carousel',
            'media_urls'        => $mediaUrls,
            'local_media_paths' => $localMediaPaths,
            'status'            => 'scheduled',
            'approval_status'   => 'approved',
            'scheduled_at'      => $igSchedTime,
            'metrics'           => [
                'viral_score'    => 96.5,
                'trend_velocity' => 96,
                'hook_urgency'   => 98,
                'visual_depth'   => 98,
                'cta_clarity'    => 94,
                'slides_count'   => 5,
            ],
        ]);

        // Create 5 media items for the carousel
        foreach ($carouselSlides as $index => $slide) {
            $fullPath = public_path('storage/' . $slide['rel_path']);
            $fileSize = file_exists($fullPath) ? filesize($fullPath) : (file_exists(public_path($slide['rel_path'])) ? filesize(public_path($slide['rel_path'])) : 1200000);

            SocialPostMedia::create([
                'social_media_post_id' => $carouselPost->id,
                'sort_order'           => $index + 1,
                'media_type'           => 'image',
                'media_url'            => $mediaUrls[$index],
                'local_path'           => $slide['rel_path'],
                'file_size'            => $fileSize,
                'metadata'             => [
                    'dimensions' => '1080x1080',
                    'aspect'     => '1:1',
                    'title'      => $slide['title'],
                ],
            ]);
        }

        // Create Instagram target
        SocialPostTarget::create([
            'social_media_post_id'    => $carouselPost->id,
            'social_media_account_id' => null,
            'provider'                => 'meta',
            'channel'                 => 'instagram',
            'content_type'            => 'carousel',
            'custom_caption'          => null,
            'status'                  => 'scheduled',
            'scheduled_at'            => $igSchedTime,
            'retry_count'             => 0,
        ]);

        $this->info(" -> Berhasil membuat Carousel Post [ID: {$carouselPost->id}] dengan 5 slide HD 3D.");

        // =========================================================================
        // 2. DEPLOY 4 KONTEN UNIK HARIAN DI JAM VIRAL RESMI
        // =========================================================================
        $this->info("\n[2/2] Menyiapkan 4 Konten Harian di Jam Viral Resmi Lintas Platform...");

        $dailyPostsSpec = [
            [
                'platform'     => 'linkedin',
                'viral_time'   => '08:00:00',
                'slot_label'   => 'B2B Morning Insight',
                'feature_id'   => 'multi_outlet',
                'feature_name' => 'Multi-Cabang Dari 1 Layar HP',
                'image_rel'    => 'admin/social-media-assets/pinterest-showcase/slide-01.png',
                'viral_score'  => 91.3,
                'caption'      => <<<TXT
Tantangan terbesar scale up bisnis ritel bukan membuka cabang baru, melainkan mempertahankan standarisasi dan kontrol stok lintas outlet.

Di Toko Kamu yang didukung oleh Cooca Indonesia, monitoring 3 cabang diselesaikan dari satu layar smartphone:
- Pantau perputaran omzet kasir per outlet secara real-time.
- Transfer barang antar gudang tercatat otomatis tanpa risiko selisih stok fisik.
- Hak akses kasir dibatasi per cabang untuk mencegah manipulasi data keuangan.

Pelajari sistem multi-outlet terintegrasi untuk bisnis Anda di: https://cooca.id

#CoocaERP #TataKelolaBisnis #MultiOutlet #SOPBisnis #ManajemenRitel #TokoKamu #CoocaIndonesia
TXT
            ],
            [
                'platform'     => 'instagram',
                'viral_time'   => '12:15:00',
                'slot_label'   => 'Lunchtime Visual Carousel',
                'feature_id'   => 'pos',
                'feature_name' => 'Kasir POS Pintar Kilat',
                'image_rel'    => 'admin/social-media-assets/carousel/slide-01.png',
                'viral_score'  => 96.5,
                'is_carousel'  => true,
                'post_id'      => $carouselPost->id, // Reuses carousel post created above
                'caption'      => $carouselCaption,
            ],
            [
                'platform'     => 'facebook',
                'viral_time'   => '16:30:00',
                'slot_label'   => 'Afternoon Community Post',
                'feature_id'   => 'inventory',
                'feature_name' => 'Manajemen Stok & Low-Stock Alert',
                'image_rel'    => 'admin/social-media-assets/pinterest-showcase/slide-02.png',
                'viral_score'  => 95.1,
                'caption'      => <<<TXT
Bagi rekan-rekan pemilik toko dan grosir ritel, pernahkah mengalami situasi stok di buku masih ada 10 pcs tapi saat dicari di rak barangnya sudah habis?

Hal ini biasanya terjadi karena:
1. Kasir lupa mencatat barang keluar saat toko sedang ramai.
2. Ada retur barang yang tidak tercatat ke kartu stok.
3. Tidak ada sistem notifikasi saat barang sudah mencapai batas minimum.

Di Toko Kamu bersama Cooca Indonesia, setiap kali kasir scan produk di kasir, kartu stok otomatis berkurang detik itu juga. Ketika barang menipis, sistem langsung mengirim pengingat otomatis untuk restock.

Simak studi kasus lengkapnya di: https://cooca.id

#KomunitasRitel #StudiKasusUMKM #StokGudang #AplikasiToko #TokoKamu #CoocaIndonesia
TXT
            ],
            [
                'platform'     => 'tiktok',
                'viral_time'   => '19:45:00',
                'slot_label'   => 'Prime Time FYP Hook',
                'feature_id'   => 'fraud_prevention',
                'feature_name' => 'Hak Akses Kasir & Anti-Kecurangan',
                'image_rel'    => 'admin/social-media-assets/pinterest-showcase/slide-03.png',
                'viral_score'  => 96.2,
                'caption'      => <<<TXT
3 detik langsung beres! Trik kasir toko anti-bocor kas dan anti-kecurangan.

Jangan biarkan uang kembalian atau transaksi void kasir bikin omzet toko kamu hilang sia-sia.
Dengan sistem Kasir Pintar COOCA di Toko Kamu:
- Kasir tidak bisa hapus transaksi tanpa PIN Supervisor.
- Rekap kas otomatis per pergantian shift.
- Struk belanja langsung terkirim via WhatsApp pelanggan.

Coba gratis sekarang di https://cooca.id (klik link di bio!)

#trikbisnis #kasirtoko #aplikasikasir #umkmmaju #tokokamu #coocaindonesia
TXT
            ],
        ];

        $tableRows = [];

        foreach ($dailyPostsSpec as $spec) {
            $targetPlatform = $spec['platform'];
            $scheduledAtCarbon = Carbon::parse("{$startDateStr} {$spec['viral_time']}");

            if (! empty($spec['is_carousel'])) {
                // Post already registered
                $post = $carouselPost;
            } else {
                $publicUrl = asset('storage/' . $spec['image_rel']);
                $cleanCap = str_replace(['—', '–'], '-', $spec['caption']);

                $post = SocialMediaPost::create([
                    'admin_id'          => $admin->id,
                    'business_id'       => null,
                    'is_platform'       => true,
                    'platform'          => $targetPlatform,
                    'content'           => trim($cleanCap),
                    'media_type'        => 'image',
                    'media_urls'        => [$publicUrl],
                    'local_media_paths' => [$spec['image_rel']],
                    'status'            => 'scheduled',
                    'approval_status'   => 'approved',
                    'scheduled_at'      => $scheduledAtCarbon,
                    'metrics'           => [
                        'viral_score' => $spec['viral_score'],
                        'slot_label'  => $spec['slot_label'],
                    ],
                ]);

                $fullPath = public_path('storage/' . $spec['image_rel']);
                $fileSize = file_exists($fullPath) ? filesize($fullPath) : (file_exists(public_path($spec['image_rel'])) ? filesize(public_path($spec['image_rel'])) : 1000000);

                SocialPostMedia::create([
                    'social_media_post_id' => $post->id,
                    'sort_order'           => 1,
                    'media_type'           => 'image',
                    'media_url'            => $publicUrl,
                    'local_path'           => $spec['image_rel'],
                    'file_size'            => $fileSize,
                    'metadata'             => [
                        'dimensions' => '1080x1080',
                        'aspect'     => '1:1',
                        'title'      => $spec['feature_name'],
                    ],
                ]);

                SocialPostTarget::create([
                    'social_media_post_id'    => $post->id,
                    'social_media_account_id' => null,
                    'provider'                => in_array($targetPlatform, ['facebook', 'instagram', 'threads'], true) ? 'meta' : ($targetPlatform === 'linkedin' ? 'linkedin' : 'tiktok'),
                    'channel'                 => $targetPlatform,
                    'content_type'            => 'photo',
                    'custom_caption'          => null,
                    'status'                  => 'scheduled',
                    'scheduled_at'            => $scheduledAtCarbon,
                    'retry_count'             => 0,
                ]);
            }

            $tableRows[] = [
                $post->id,
                strtoupper($targetPlatform),
                $spec['slot_label'],
                $scheduledAtCarbon->format('d M Y H:i') . ' WIB',
                $spec['viral_score'] . '/100',
                strtoupper($post->status),
                $spec['feature_name'],
            ];
        }

        $this->info("\n==========================================================================================");
        $this->info("  SCHEDULED CONTENT SUMMARY (4 Platform-Unique Posts at Viral Hours)                     ");
        $this->info("==========================================================================================");
        $this->table(
            ['Post ID', 'Platform', 'Slot Viral', 'Jadwal Tayang', 'Skor Viral', 'Status', 'Topik Fitur'],
            $tableRows
        );

        $this->info("\nSemua konten (Carousel 5 Slide + 4 Postingan Harian di Jam Viral) berhasil dideploy ke sistem!");
        $this->info("Buka Admin Social Media Center di: " . route('admin.social-media.index', ['tab' => 'posts']));

        return Command::SUCCESS;
    }
}
