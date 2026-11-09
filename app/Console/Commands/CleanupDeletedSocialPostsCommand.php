<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\SocialMedia\AdminSocialMediaService;
use App\Models\SocialMediaPost;
use Illuminate\Console\Command;

class CleanupDeletedSocialPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cooca:cleanup-deleted-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently purge broken, obsolete, or deleted social media posts and blacklist them from resurrection';

    /**
     * Execute the console command.
     */
    public function handle(AdminSocialMediaService $adminSocialMediaService): int
    {
        $this->info('===========================================================');
        $this->info('  COOCA SOCIAL MEDIA DELETED & BROKEN POSTS CLEANUP        ');
        $this->info('===========================================================');

        $targetIds = [
            'db777670-c83b-454b-98aa-13ec16af6038',
            '0b21a626-00e2-4d1f-980c-b652fe621736',
        ];

        $deletedCount = 0;

        // 1. Delete specific known obsolete IDs
        foreach ($targetIds as $id) {
            $post = SocialMediaPost::find($id);
            if ($post) {
                $this->line("Purging specific target post ID: {$id}");
                $adminSocialMediaService->deletePlatformPost($post);
                $deletedCount++;
            }
        }

        // 2. Delete posts containing obsolete text 'Toko Kamu' or 'Rahasia Toko Ritel Bebas Kebocoran'
        $obsoletePosts = SocialMediaPost::where('is_platform', true)
            ->where(function ($q) {
                $q->where('content', 'like', '%Toko Kamu%')
                  ->orWhere('content', 'like', '%Rahasia Toko Ritel Bebas Kebocoran%');
            })
            ->get();

        foreach ($obsoletePosts as $post) {
            $this->line("Purging obsolete content post ID: {$post->id} ({$post->platform})");
            $adminSocialMediaService->deletePlatformPost($post);
            $deletedCount++;
        }

        // 3. Delete any platform post whose image does not exist on disk
        $allPlatformPosts = SocialMediaPost::where('is_platform', true)->get();
        foreach ($allPlatformPosts as $post) {
            $paths = (array) ($post->local_media_paths ?? []);
            if (! empty($paths)) {
                $allMissing = true;
                foreach ($paths as $path) {
                    $clean = ltrim((string) $path, '/\\');
                    if (str_starts_with($clean, 'public/')) {
                        $clean = substr($clean, 7);
                    }
                    if (str_starts_with($clean, 'storage/')) {
                        $clean = substr($clean, 8);
                    }
                    if (file_exists(storage_path('app/public/' . $clean)) || file_exists(public_path('storage/' . $clean))) {
                        $allMissing = false;
                        break;
                    }
                }

                if ($allMissing) {
                    $this->warn("Purging post with broken/missing media files: {$post->id} ({$post->platform})");
                    $adminSocialMediaService->deletePlatformPost($post);
                    $deletedCount++;
                }
            }
        }

        // 4. Scrub and reset last-generated.json
        $jsonPath = storage_path('app/public/content-assets/last-generated.json');
        if (file_exists($jsonPath)) {
            @file_put_contents($jsonPath, '[]');
            $this->info("Reset {$jsonPath} to empty array.");
        }

        $this->info("Cleanup completed successfully. Total purged posts: {$deletedCount}.");
        return Command::SUCCESS;
    }
}
