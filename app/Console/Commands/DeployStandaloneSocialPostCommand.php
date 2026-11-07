<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\SocialMediaPost;
use App\Models\SocialPostMedia;
use App\Models\SocialPostTarget;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DeployStandaloneSocialPostCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cooca:deploy-standalone-post
                            {--automation-id= : Unique Automation ID (e.g. cooca-carousel-linkedin)}
                            {--spec-json= : Path to evaluated spec JSON file}
                            {--start-date=2026-11-09 : Target posting date (format YYYY-MM-DD)}
                            {--time= : Override posting time (format HH:mm)}
                            {--force : Force replace existing post for this specific automation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy independent, stand-alone social media post or carousel from evaluated spec JSON without overwriting other automations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $admin = Admin::first();
        if (! $admin) {
            $this->error('Admin super_admin tidak ditemukan di database. Jalankan seeder AdminSeeder.');
            return Command::FAILURE;
        }

        $specPath = (string) $this->option('spec-json');
        $automationId = (string) $this->option('automation-id');
        $spec = [];

        if ($specPath && File::exists(base_path($specPath))) {
            $spec = json_decode(File::get(base_path($specPath)), true) ?: [];
        } elseif ($specPath && File::exists($specPath)) {
            $spec = json_decode(File::get($specPath), true) ?: [];
        }

        $automationId = $spec['automation_id'] ?? ($automationId ?: 'cooca-standalone-post');
        $channel = $spec['channel'] ?? 'instagram';
        $format = $spec['format'] ?? 'feed';
        $mediaType = ($format === 'carousel') ? 'carousel' : 'image';
        $caption = $spec['caption'] ?? 'Tips Operasional Toko Kamu bersama Cooca Indonesia di https://cooca.id';
        $hookTitle = $spec['hook_title'] ?? $spec['topic_name'] ?? 'Konten Edukasi Toko Kamu';
        $topicName = $spec['topic_name'] ?? $hookTitle;
        $slotLabel = $spec['slot_label'] ?? (ucfirst($channel) . ' ' . ucfirst($format) . ' Post');
        $dimensions = $spec['dimensions'] ?? '1080x1080';
        $viralScore = (float) ($spec['viral_score'] ?? 95.0);
        $slides = $spec['slides'] ?? [];

        $startDateStr = (string) ($this->option('start-date') ?: '2026-11-09');
        $timeStr = (string) ($this->option('time') ?: ($spec['target_time'] ?? '12:15'));
        $scheduledAtCarbon = Carbon::parse("{$startDateStr} {$timeStr}:00");

        $this->info('====================================================================');
        $this->info("  COOCA STANDALONE DEPLOY: {$automationId}");
        $this->info("  Channel: " . strtoupper($channel) . " | Format: " . strtoupper($format) . " ({$dimensions})");
        $this->info("  Jadwal: " . $scheduledAtCarbon->format('d M Y H:i') . " WIB | Slot: {$slotLabel}");
        $this->info('====================================================================');

        // ISOLASI TOTAL: Hanya hapus postingan yang secara spesifik berasal dari automation_id atau slot_label ini!
        // Tidak akan pernah menghapus postingan otomasi lain di channel yang sama!
        SocialPostMedia::whereHas('post', function ($q) use ($automationId, $slotLabel) {
            $q->where('is_platform', true)
              ->where(function ($sub) use ($automationId, $slotLabel) {
                  $sub->whereJsonContains('metrics->automation_id', $automationId)
                      ->orWhereJsonContains('metrics->slot_label', $slotLabel);
              });
        })->delete();

        SocialPostTarget::whereHas('post', function ($q) use ($automationId, $slotLabel) {
            $q->where('is_platform', true)
              ->where(function ($sub) use ($automationId, $slotLabel) {
                  $sub->whereJsonContains('metrics->automation_id', $automationId)
                      ->orWhereJsonContains('metrics->slot_label', $slotLabel);
              });
        })->delete();

        SocialMediaPost::where('is_platform', true)
            ->where(function ($sub) use ($automationId, $slotLabel) {
                $sub->whereJsonContains('metrics->automation_id', $automationId)
                    ->orWhereJsonContains('metrics->slot_label', $slotLabel);
            })->delete();

        // Cari atau susun aset media sesuai channel & format
        $mediaItems = $this->resolveMediaItems($channel, $format, $dimensions, $slides);

        // Buat Postingan Utama
        $post = SocialMediaPost::create([
            'admin_id'          => $admin->id,
            'is_platform'       => true,
            'platform'          => $channel,
            'content'           => $caption,
            'media_type'        => $mediaType,
            'media_urls'        => array_column($mediaItems, 'media_url'),
            'local_media_paths' => array_column($mediaItems, 'local_path'),
            'status'            => 'scheduled',
            'approval_status'   => 'approved',
            'scheduled_at'      => $scheduledAtCarbon,
            'metrics'           => [
                'automation_id'             => $automationId,
                'slot_label'                => $slotLabel,
                'topic_name'                => $topicName,
                'hook_title'                => $hookTitle,
                'viral_score'               => $viralScore,
                'dimensions'                => $dimensions,
                'format'                    => $format,
                'business_name'             => 'Toko Kamu',
                'owner_name'                => 'Cooca Indonesia',
                'deployed_at'               => now()->toDateTimeString(),
            ],
        ]);

        // Masukkan Media Items
        foreach ($mediaItems as $idx => $m) {
            SocialPostMedia::create([
                'social_media_post_id' => $post->id,
                'sort_order'           => $idx + 1,
                'media_type'           => 'image',
                'media_url'            => $m['media_url'],
                'local_path'           => $m['local_path'],
                'file_size'            => $m['file_size'] ?? 102400,
                'metadata'             => [
                    'dimensions' => $dimensions,
                    'title'      => $m['title'] ?? ($hookTitle . ' - Slide ' . ($idx + 1)),
                ],
            ]);
        }

        // Tentukan Provider Meta / TikTok / LinkedIn
        $provider = match ($channel) {
            'instagram', 'facebook' => 'meta',
            'tiktok'                => 'tiktok',
            'linkedin'              => 'linkedin',
            default                 => 'custom',
        };

        // Daftarkan Target Jadwal
        SocialPostTarget::create([
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => null,
            'provider'                => $provider,
            'channel'                 => $channel,
            'content_type'            => ($mediaType === 'carousel') ? 'carousel' : 'photo',
            'custom_caption'          => null,
            'status'                  => 'scheduled',
            'scheduled_at'            => $scheduledAtCarbon,
            'retry_count'             => 0,
        ]);

        $this->info(" -> Berhasil membuat postingan mandiri [ID: {$post->id}] status SCHEDULED.");
        $this->table(
            ['Automation ID', 'Channel', 'Format', 'Jadwal', 'Skor Viral', 'Status', 'Fokus Konten'],
            [[
                $automationId,
                strtoupper($channel),
                strtoupper($format) . " ({$dimensions})",
                $scheduledAtCarbon->format('d M Y H:i') . ' WIB',
                "{$viralScore} / 100",
                strtoupper($post->status),
                $topicName,
            ]]
        );

        return Command::SUCCESS;
    }

    /**
     * Cari berkas media yang sesuai untuk postingan ini.
     */
    protected function resolveMediaItems(string $channel, string $format, string $dimensions, array $slides): array
    {
        $items = [];
        $baseAssets = storage_path('app/public/admin/social-media-assets');

        if ($format === 'carousel') {
            // Cek folder slide per channel
            $potentialDirs = [
                "{$baseAssets}/carousel/{$channel}",
                "{$baseAssets}/{$channel}-carousel",
                "{$baseAssets}/carousel",
            ];

            $foundFiles = [];
            foreach ($potentialDirs as $dir) {
                if (File::isDirectory($dir)) {
                    $files = File::glob("{$dir}/slide-*.png");
                    if (! empty($files)) {
                        sort($files);
                        $foundFiles = $files;
                        break;
                    }
                }
            }

            if (! empty($foundFiles)) {
                $maxSlides = count($slides) > 0 ? min(count($slides), count($foundFiles)) : count($foundFiles);
                for ($i = 0; $i < $maxSlides; $i++) {
                    $file = $foundFiles[$i];
                    $relPath = str_replace(storage_path('app/public/'), '', $file);
                    $items[] = [
                        'media_url'  => asset('storage/' . str_replace('\\', '/', $relPath)),
                        'local_path' => $relPath,
                        'file_size'  => File::size($file),
                        'title'      => $slides[$i]['title'] ?? ("Slide " . ($i + 1)),
                    ];
                }
            } else {
                // Fallback default slide
                $items[] = $this->getFallbackItem($channel, $dimensions);
            }
        } else {
            // Format Single Feed
            $specificFeedMap = [
                'facebook'  => 'platform/facebook-feed-inventory-1080x1080.png',
                'linkedin'  => 'platform/2026-11-07-toko-kamu-linkedin-finance-1080x1350.png',
                'instagram' => ($dimensions === '1080x1350')
                    ? 'platform/2026-11-06-danastri-apparel-1080x1350.png'
                    : 'platform/2026-11-07-toko-kamu-instagram-fraud_prevention-1080x1350.png',
                'tiktok'    => 'platform/2026-11-09-toko-kamu-tiktok-fraud_prevention-1080x1350.png',
            ];

            $relPath = $specificFeedMap[$channel] ?? 'platform/facebook-feed-inventory-1080x1080.png';
            $fullPath = storage_path("app/public/{$relPath}");

            if (File::exists($fullPath)) {
                $items[] = [
                    'media_url'  => asset('storage/' . str_replace('\\', '/', $relPath)),
                    'local_path' => $relPath,
                    'file_size'  => File::size($fullPath),
                    'title'      => ucfirst($channel) . ' Feed Visual',
                ];
            } else {
                $items[] = $this->getFallbackItem($channel, $dimensions);
            }
        }

        return $items;
    }

    protected function getFallbackItem(string $channel, string $dimensions): array
    {
        return [
            'media_url'  => asset('assets/image/1785229034_logo_light.png'),
            'local_path' => 'public/assets/image/1785229034_logo_light.png',
            'file_size'  => 50000,
            'title'      => 'Cooca Indonesia - Toko Kamu',
        ];
    }
}
