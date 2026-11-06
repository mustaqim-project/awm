<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\SocialMedia\AdminSocialMediaService;
use App\Models\Admin;
use App\Models\SocialMediaPost;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportDailySocialContentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cooca:import-social-content
                            {--file= : Specific json file path to import}
                            {--force : Force re-import even if already scheduled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import pre-generated daily social media content from JSON into production Admin Social Media Center';

    /**
     * Execute the console command.
     */
    public function handle(AdminSocialMediaService $adminSocialMediaService): int
    {
        $this->info('===========================================================');
        $this->info('  COOCA PRODUCTION SOCIAL MEDIA CONTENT IMPORTER           ');
        $this->info('===========================================================');

        $filePath = $this->option('file') ?: storage_path('app/public/content-assets/last-generated.json');

        if (! file_exists($filePath)) {
            $this->warn("No generated social content JSON found at: {$filePath}");
            return Command::SUCCESS;
        }

        $items = json_decode(file_get_contents($filePath), true);
        if (empty($items) || ! is_array($items)) {
            $this->warn("Invalid or empty content JSON at: {$filePath}");
            return Command::SUCCESS;
        }

        $admin = Admin::first();
        if (! $admin) {
            $this->error('No Admin user found in database to author the social media post.');
            return Command::FAILURE;
        }

        $force = (bool) $this->option('force');
        $tableRows = [];
        $importedCount = 0;

        foreach ($items as $item) {
            $localRelPath = $item['image_relative_path'] ?? null;
            if (! $localRelPath) {
                continue;
            }

            // Check if already registered
            $existing = SocialMediaPost::where('is_platform', true)
                ->where(function ($q) use ($localRelPath, $item) {
                    $q->whereJsonContains('local_media_paths', $localRelPath)
                      ->orWhere('content', $item['caption'] ?? '');
                })
                ->first();

            if ($existing && ! $force) {
                $this->line("Skipping already imported post ID: [<comment>{$existing->id}</comment>] ({$item['feature_name']})");
                $tableRows[] = [
                    $existing->id,
                    $item['feature_name'],
                    strtoupper($item['slot'] ?? 'N/A'),
                    $existing->scheduled_at ? $existing->scheduled_at->format('d M Y H:i') . ' WIB' : 'N/A',
                    strtoupper($existing->status),
                    implode(', ', $item['platforms'] ?? ['instagram']),
                    $item['image_filename'] ?? 'N/A',
                ];
                continue;
            }

            $scheduledAtCarbon = Carbon::parse($item['scheduled_at'] ?? now()->addHours(2));
            if ($scheduledAtCarbon->isPast()) {
                $scheduledAtCarbon = Carbon::now()->addHours(2);
                $this->warn("Adjusted past scheduled time to: {$scheduledAtCarbon->toDateTimeString()}");
            }

            $publicUrl = asset('storage/' . $localRelPath);

            $this->line("Registering post to Admin Social Media Center for [<comment>{$item['feature_name']}</comment>]...");

            $post = $adminSocialMediaService->createAndPublishPlatformPost($admin, [
                'platforms'             => $item['platforms'] ?? ['instagram', 'facebook', 'linkedin', 'threads'],
                'platform'              => ($item['platforms'] ?? ['instagram'])[0],
                'timing_mode'           => 'schedule_all',
                'platform_timing'       => [],
                'platform_scheduled_at' => [],
                'content'               => $item['caption'] ?? '',
                'media_type'            => 'image',
                'media_urls'            => [$publicUrl],
                'local_media_paths'     => [$localRelPath],
                'scheduled_at'          => $scheduledAtCarbon->toDateTimeString(),
            ]);

            $importedCount++;
            $tableRows[] = [
                $post->id,
                $item['feature_name'],
                strtoupper($item['slot'] ?? 'EVENING'),
                $scheduledAtCarbon->format('d M Y H:i') . ' WIB',
                strtoupper($post->status),
                implode(', ', $item['platforms'] ?? ['instagram', 'facebook', 'linkedin', 'threads']),
                $item['image_filename'] ?? basename($localRelPath),
            ];
        }

        $this->info("\n===========================================================");
        $this->info("  IMPORT SUMMARY (Admin Social Media Center)               ");
        $this->info("===========================================================");
        $this->table(
            ['Post ID', 'Fitur Cooca', 'Slot', 'Jadwal Tayang', 'Status', 'Saluran Platform', 'File Desain'],
            $tableRows
        );

        $this->info("\nImport selesai! Postingan siap di Admin Social Media Center.");
        return Command::SUCCESS;
    }
}
