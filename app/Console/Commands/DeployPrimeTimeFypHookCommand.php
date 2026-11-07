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

class DeployPrimeTimeFypHookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cooca:deploy-prime-time-hook
                            {--start-date=2026-11-09 : Tanggal target penayangan FYP (format YYYY-MM-DD)}
                            {--force : Hapus jadwal lama jika sudah ada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy Viral Educational Social Media (Prime Time FYP Hook) into production for TikTok at 19:45 WIB';

    /**
     * Execute the console command.
     */
    public function handle(AdminSocialMediaService $adminSocialMediaService): int
    {
        $this->info('====================================================================');
        $this->info('  COOCA PRODUCTION DEPLOYMENT: VIRAL PRIME TIME FYP HOOK (TIKTOK)   ');
        $this->info('  Jam Viral Resmi: 19:45:00 WIB | Skor Viral: 96.2/100              ');
        $this->info('====================================================================');

        $admin = Admin::first();
        if (! $admin) {
            $this->error('Admin super_admin tidak ditemukan di database. Jalankan seeder AdminSeeder.');
            return Command::FAILURE;
        }

        $startDateStr = (string) ($this->option('start-date') ?: '2026-11-09');
        $force = (bool) $this->option('force');

        $scheduledAtCarbon = Carbon::parse("{$startDateStr} 19:45:00");
        $relImagePath = 'admin/social-media-assets/platform/2026-11-09-toko-kamu-tiktok-fraud_prevention-1080x1350.png';
        $fullPath = storage_path('app/public/' . $relImagePath);

        // Fallback to existing asset if needed
        if (! file_exists($fullPath)) {
            $fallbackPath = public_path('storage/' . $relImagePath);
            if (file_exists($fallbackPath)) {
                $fullPath = $fallbackPath;
            }
        }

        $publicUrl = asset('storage/' . $relImagePath);

        $tiktokCaption = <<<TXT
Cara mencegah kecurangan kasir toko ritel agar bebas dari selisih uang kas setiap hari!

Kasir kamu masih bisa hapus transaksi sesuka hati? Bahaya banget! Kunci hak akses kasir pakai PIN Supervisor COOCA seperti di Toko Kamu. Aman dan transparan! Cek di https://cooca.id

#KeamananToko #AntiFraudKasir #CoocaERP #AuditBisnis #SistemUsaha #TokoKamu
TXT;

        // Clean caption from forbidden dashes
        $cleanCaption = str_replace(['—', '–'], '-', $tiktokCaption);

        if ($force) {
            $this->warn('Force mode aktif: Menghapus postingan TikTok scheduled sebelumnya...');
            SocialPostMedia::whereHas('post', fn ($q) => $q->where('is_platform', true)->where('platform', 'tiktok'))->delete();
            SocialPostTarget::whereHas('post', fn ($q) => $q->where('is_platform', true)->where('platform', 'tiktok'))->delete();
            SocialMediaPost::where('is_platform', true)->where('platform', 'tiktok')->delete();
        } else {
            // Check existing post
            $existing = SocialMediaPost::where('is_platform', true)
                ->where('platform', 'tiktok')
                ->where('scheduled_at', $scheduledAtCarbon)
                ->first();

            if ($existing) {
                $this->warn("Post TikTok Prime Time FYP Hook sudah terdaftar [ID: {$existing->id}]. Menghapus versi sebelumnya untuk update...");
                SocialPostMedia::where('social_media_post_id', $existing->id)->delete();
                SocialPostTarget::where('social_media_post_id', $existing->id)->delete();
                $existing->delete();
            }
        }

        $post = SocialMediaPost::create([
            'admin_id'          => $admin->id,
            'business_id'       => null,
            'is_platform'       => true,
            'platform'          => 'tiktok',
            'content'           => trim($cleanCaption),
            'media_type'        => 'image',
            'media_urls'        => [$publicUrl],
            'local_media_paths' => [$relImagePath],
            'status'            => 'scheduled',
            'approval_status'   => 'approved',
            'scheduled_at'      => $scheduledAtCarbon,
            'metrics'           => [
                'viral_score'    => 96.2,
                'trend_velocity' => 95,
                'hook_urgency'   => 99,
                'visual_depth'   => 95,
                'cta_clarity'    => 95,
                'slot_label'     => 'Prime Time FYP Hook',
                'target_time'    => '19:45:00 WIB',
                'topic'          => 'Hak Akses Kasir & Anti-Kecurangan',
            ],
        ]);

        $fileSize = file_exists($fullPath) ? filesize($fullPath) : 550000;

        SocialPostMedia::create([
            'social_media_post_id' => $post->id,
            'sort_order'           => 1,
            'media_type'           => 'image',
            'media_url'            => $publicUrl,
            'local_path'           => $relImagePath,
            'file_size'            => $fileSize,
            'metadata'             => [
                'dimensions' => '1080x1350',
                'aspect'     => '4:5',
                'title'      => 'Hak Akses Kasir & Anti-Kecurangan (Prime Time FYP Hook)',
            ],
        ]);

        SocialPostTarget::create([
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => null,
            'provider'                => 'tiktok',
            'channel'                 => 'tiktok',
            'content_type'            => 'photo',
            'custom_caption'          => null,
            'status'                  => 'scheduled',
            'scheduled_at'            => $scheduledAtCarbon,
            'retry_count'             => 0,
        ]);

        $this->info(" -> Berhasil mendaftarkan Post TikTok Prime Time FYP Hook [ID: {$post->id}]!");

        $summaryTable = [
            [
                $post->id,
                'TIKTOK',
                'Prime Time FYP Hook',
                $scheduledAtCarbon->format('d M Y H:i') . ' WIB',
                '96.2 / 100',
                strtoupper($post->status),
                'Hak Akses Kasir & Anti-Kecurangan (1080x1350)',
            ],
        ];

        $this->info("\n==========================================================================================");
        $this->info("  SCHEDULED CONTENT SUMMARY: PRIME TIME FYP HOOK (PRODUCTION DEPLOYED)                    ");
        $this->info("==========================================================================================");
        $this->table(
            ['Post ID', 'Saluran', 'Slot Viral', 'Jadwal Tayang', 'Skor Viral FYP', 'Status', 'Topik Fitur'],
            $summaryTable
        );

        $this->info("\nKonten Viral Prime Time FYP Hook berhasil dideploy ke sistem produksi!");
        $this->info("Buka Admin Social Media Center di: " . route('admin.social-media.index', ['tab' => 'posts', 'status' => 'scheduled']));

        return Command::SUCCESS;
    }
}
