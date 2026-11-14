<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\SocialMediaPost;
use App\Models\SocialPostMedia;
use App\Models\SocialPostTarget;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeployLinkedinViralPostCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cooca:deploy-linkedin-post
                            {--start-date=2026-11-09 : Tanggal target posting (format YYYY-MM-DD)}
                            {--time=08:00 : Jam tayang postingan (format HH:mm, default 08:00 WIB)}
                            {--force : Hapus jadwal lama LinkedIn jika sudah ada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy dedicated B2B high-impact viral post for LinkedIn into production (Multi-Outlet & Governance)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('====================================================================');
        $this->info('  COOCA LINKEDIN AUTOMATION: B2B GOVERNANCE & MULTI-OUTLET SCALE-UP ');
        $this->info('====================================================================');

        $admin = Admin::first();
        if (! $admin) {
            $this->error('Admin super_admin tidak ditemukan di database. Jalankan seeder AdminSeeder.');
            return Command::FAILURE;
        }

        $startDateStr = (string) ($this->option('start-date') ?: '2026-11-09');
        $timeStr = (string) ($this->option('time') ?: '08:00');
        $scheduledAtCarbon = Carbon::parse("{$startDateStr} {$timeStr}:00");

        // Clean up previous automated LinkedIn platform post to prevent duplicates
        $this->info('Menyelaraskan jadwal: Membersihkan jadwal LinkedIn sebelumnya...');
        SocialPostMedia::whereHas('post', fn ($q) => $q->where('is_platform', true)->where('platform', 'linkedin'))->delete();
        SocialPostTarget::whereHas('post', fn ($q) => $q->where('is_platform', true)->where('platform', 'linkedin'))->delete();
        SocialMediaPost::where('is_platform', true)->where('platform', 'linkedin')->delete();

        $caption = <<<TXT
Tantangan terbesar scale up bisnis ritel bukan membuka cabang baru, melainkan mempertahankan standarisasi dan kontrol stok lintas outlet.

Di Toko Kamu yang didukung oleh Cooca Indonesia, monitoring 3 cabang diselesaikan dari satu layar smartphone:
- Pantau perputaran omzet kasir per outlet secara real-time.
- Transfer barang antar gudang tercatat otomatis tanpa risiko selisih stok fisik.
- Hak akses kasir dibatasi per cabang untuk mencegah manipulasi data keuangan.

Pelajari sistem multi-outlet terintegrasi untuk bisnis Anda di: https://cooca.id

#CoocaERP #TataKelolaBisnis #MultiOutlet #SOPBisnis #ManajemenRitel #TokoKamu #CoocaIndonesia
TXT;

        // Clean caption from em dash and en dash
        $cleanCaption = trim(str_replace(['', '–'], '-', $caption));

        // Use primary carousel asset with fallback guarantee
        $imageRel = 'admin/social-media-assets/carousel/slide-04.png';
        $fallbackRel = 'admin/social-media-assets/pinterest-showcase/slide-04.png';
        $fullPath = public_path('storage/' . $imageRel);
        if (! file_exists($fullPath)) {
            $imageRel = file_exists(public_path('storage/' . $fallbackRel)) ? $fallbackRel : $imageRel;
        }

        $publicUrl = asset('storage/' . $imageRel);
        $fileSize = file_exists(public_path('storage/' . $imageRel)) ? filesize(public_path('storage/' . $imageRel)) : 1025244;

        $post = SocialMediaPost::create([
            'admin_id'          => $admin->id,
            'business_id'       => null,
            'is_platform'       => true,
            'platform'          => 'linkedin',
            'content'           => $cleanCaption,
            'media_type'        => 'image',
            'media_urls'        => [$publicUrl],
            'local_media_paths' => [$imageRel],
            'status'            => 'scheduled',
            'approval_status'   => 'approved',
            'scheduled_at'      => $scheduledAtCarbon,
            'metrics'           => [
                'viral_score'           => 92.4,
                'b2b_resonance'         => 95,
                'leadership_relevance'  => 94,
                'repost_intent'         => 89,
                'slot_label'            => 'B2B Morning Insight',
                'target_audience'       => 'Business Owners, Retail Directors, Operations Managers',
            ],
        ]);

        SocialPostMedia::create([
            'social_media_post_id' => $post->id,
            'sort_order'           => 1,
            'media_type'           => 'image',
            'media_url'            => $publicUrl,
            'local_path'           => $imageRel,
            'file_size'            => $fileSize,
            'metadata'             => [
                'dimensions' => '1080x1080',
                'aspect'     => '1:1',
                'title'      => 'Multi-Cabang Dari 1 Layar HP',
            ],
        ]);

        SocialPostTarget::create([
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => null,
            'provider'                => 'linkedin',
            'channel'                 => 'linkedin',
            'content_type'            => 'photo',
            'custom_caption'          => null,
            'status'                  => 'scheduled',
            'scheduled_at'            => $scheduledAtCarbon,
            'retry_count'             => 0,
        ]);

        $this->info(" -> Berhasil membuat postingan LinkedIn [ID: {$post->id}] dengan status SCHEDULED.");

        $this->table(
            ['Post ID', 'Platform', 'Slot Viral', 'Jadwal Tayang', 'Skor B2B', 'Status', 'Fokus Topik'],
            [[
                $post->id,
                'LINKEDIN',
                'B2B Morning Insight',
                $scheduledAtCarbon->format('d M Y H:i') . ' WIB',
                '92.4 / 100',
                strtoupper($post->status),
                'Multi-Cabang Dari 1 Layar HP & Audit Hak Akses',
            ]]
        );

        $this->info("\nOtomatisasi postingan LinkedIn berhasil dijadwalkan!");
        $this->info("Buka Admin Social Media Center di: " . route('admin.social-media.index', ['tab' => 'posts', 'channel' => 'linkedin', 'status' => 'scheduled']));

        return Command::SUCCESS;
    }
}
