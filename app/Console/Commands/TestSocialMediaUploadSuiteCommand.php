<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\SocialMedia\SocialMediaManager;
use App\Domain\SocialMedia\SocialMediaService;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SocialPostMedia;
use App\Models\SocialPostTarget;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class TestSocialMediaUploadSuiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'social-media:test-upload-suite
                            {--format=all : Format to test: feed, reels, carousel, or all}
                            {--platform=all : Platform to test: tiktok, instagram, facebook, or all}
                            {--account-id= : Target a specific SocialMediaAccount UUID}
                            {--dry-run : Validate and create records without sending external API requests}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test uploading Feed (photo), Reels (video), and Carousel across connected social accounts';

    public function handle(SocialMediaService $socialService, SocialMediaManager $manager): int
    {
        $requestedFormat = strtolower((string) $this->option('format'));
        $requestedPlatform = strtolower((string) $this->option('platform'));
        $specificAccountId = $this->option('account-id');
        $isDryRun = (bool) $this->option('dry-run');

        $this->info('===============================================================');
        $this->info('  COOCA SOCIAL MEDIA UPLOAD TEST SUITE');
        $this->info('  Feed (Photo) | Reels (Video) | Carousel (6 Slides)');
        $this->info('===============================================================');
        $this->line("Mode: " . ($isDryRun ? '<comment>DRY-RUN</comment>' : '<info>LIVE EXECUTION</info>'));
        $this->line("Format: <comment>{$requestedFormat}</comment>");
        $this->line("Platform: <comment>{$requestedPlatform}</comment>");

        // 1. Resolve Accounts
        $accountsQuery = SocialMediaAccount::query();
        if (! empty($specificAccountId)) {
            $accountsQuery->where('id', $specificAccountId);
        } elseif ($requestedPlatform !== 'all') {
            $accountsQuery->where('platform', $requestedPlatform);
        }

        $accounts = $accountsQuery->where('status', 'active')->get();
        if ($accounts->isEmpty()) {
            $this->warn('No active accounts found with status=active. Looking for any connected accounts...');
            $accounts = $accountsQuery->latest()->get();
        }

        if ($accounts->isEmpty()) {
            $this->error('No social media accounts found in the system to run tests against.');
            $this->writeResultSummary([
                'status'  => 'skipped',
                'message' => 'No social media accounts available in database.',
                'tests'   => [],
            ]);
            return self::FAILURE;
        }

        $this->info("Found {$accounts->count()} account(s) to test:");
        foreach ($accounts as $acc) {
            $this->line(" - [{$acc->platform}] {$acc->account_name} (@{$acc->username}) [ID: {$acc->id}]");
        }
        $this->newLine();

        $baseUrl = rtrim(config('app.url') ?: 'https://cooca.id', '/');
        // Ensure HTTPS base URL for third-party social APIs
        if (! str_starts_with($baseUrl, 'https://') && ! app()->environment('local', 'testing')) {
            $baseUrl = preg_replace('#^http://#', 'https://', $baseUrl);
        }

        $runTag = "\n\n[Ref: " . date('d/m H:i') . "]";

        // Define Test Cases
        $testDefinitions = [
            'feed' => [
                'title'        => 'Testing Upload Feed (COOCA AI Banner)',
                'format'       => 'photo',
                'media_type'   => 'photo',
                'content_type' => 'photo',
                'content'      => "Mau Tahu Kondisi Bisnis? Tanya COOCA AI.\n\nDari penjualan, stok, hingga arus kas. Dapatkan ringkasan bisnis lewat chat.\n\n#CoocaAI #BisnisUMKM #KelolaBisnis #AsistenBisnis #AplikasiKasir" . $runTag,
                'media_items'  => [
                    [
                        'url'  => "{$baseUrl}/konten-day-1/F1.jpg",
                        'type' => 'image',
                    ],
                ],
            ],
            'reels' => [
                'title'        => 'Testing Upload Reels (Video Konten)',
                'format'       => 'reels',
                'media_type'   => 'reels',
                'content_type' => 'reel',
                'content'      => "Simak kemudahan kelola bisnis dan ringkasan otomatis bersama COOCA AI.\n\n#CoocaAI #ReelsBisnis #UMKMIndonesia #TipsBisnis #KasirOnline" . $runTag,
                'media_items'  => [
                    [
                        'url'  => "{$baseUrl}/konten-video.mp4",
                        'type' => 'video',
                    ],
                ],
            ],
            'carousel' => [
                'title'        => 'Testing Upload Carousel (Day 1 - 6 Slides)',
                'format'       => 'carousel',
                'media_type'   => 'carousel',
                'content_type' => 'carousel',
                'content'      => "Panduan praktis mengelola operasional bisnis Anda langkah demi langkah bersama COOCA.\n\nGeser ke samping untuk melihat detailnya 👉\n\n#CoocaID #TipsBisnis #UMKMIndonesia #KelolaBisnis #AplikasiBisnis" . $runTag,
                'media_items'  => [
                    ['url' => "{$baseUrl}/konten-day-1/1.jpg", 'type' => 'image'],
                    ['url' => "{$baseUrl}/konten-day-1/2.jpg", 'type' => 'image'],
                    ['url' => "{$baseUrl}/konten-day-1/3.jpg", 'type' => 'image'],
                    ['url' => "{$baseUrl}/konten-day-1/4.jpg", 'type' => 'image'],
                    ['url' => "{$baseUrl}/konten-day-1/5.jpg", 'type' => 'image'],
                    ['url' => "{$baseUrl}/konten-day-1/6.jpg", 'type' => 'image'],
                ],
            ],
        ];

        // Filter formats to execute
        $formatsToRun = [];
        if ($requestedFormat === 'all') {
            $formatsToRun = ['feed', 'reels', 'carousel'];
        } elseif (isset($testDefinitions[$requestedFormat])) {
            $formatsToRun = [$requestedFormat];
        } else {
            $this->error("Invalid format [{$requestedFormat}]. Allowed values: feed, reels, carousel, all.");
            return self::FAILURE;
        }

        $results = [];
        $overallSuccess = true;

        foreach ($formatsToRun as $formatKey) {
            $def = $testDefinitions[$formatKey];
            $this->info("---------------------------------------------------------------");
            $this->info("RUNNING TEST: {$def['title']}");
            $this->info("---------------------------------------------------------------");

            foreach ($accounts as $account) {
                $platform = strtolower((string) $account->platform);
                $this->line("Target Account: <info>{$account->account_name}</info> ({$platform})");

                // Check content type compatibility
                $validator = $manager->getValidator();
                $targetContentType = $def['content_type'];

                // Channel-specific content type normalization
                if ($platform === 'tiktok') {
                    $targetContentType = ($def['format'] === 'reels') ? 'video' : 'photo';
                } elseif ($platform === 'facebook') {
                    $targetContentType = ($def['format'] === 'reels') ? 'video' : 'feed';
                } elseif ($platform === 'instagram') {
                    $targetContentType = ($def['format'] === 'carousel') ? 'carousel' : (($def['format'] === 'reels') ? 'reel' : 'photo');
                }

                $business = $account->business ?: Business::find($account->business_id) ?: Business::first();

                // Build Post
                $mediaUrls = array_map(fn ($m) => $m['url'], $def['media_items']);

                $post = SocialMediaPost::create([
                    'business_id'             => $business?->id,
                    'social_media_account_id' => $account->id,
                    'platform'                => $platform,
                    'content'                 => $def['content'],
                    'media_type'              => $def['media_type'],
                    'media_urls'              => $mediaUrls,
                    'local_media_paths'       => null, // Keep null to avoid deleting permanent test assets
                    'status'                  => 'publishing',
                    'approval_status'         => 'approved',
                ]);

                // Create Post Media Records
                foreach ($def['media_items'] as $idx => $item) {
                    SocialPostMedia::create([
                        'social_media_post_id' => $post->id,
                        'sort_order'           => $idx + 1,
                        'media_type'           => $item['type'],
                        'media_url'            => $item['url'],
                        'local_path'           => null,
                        'file_size'            => null,
                    ]);
                }

                // Create Post Target
                $target = SocialPostTarget::create([
                    'social_media_post_id'    => $post->id,
                    'social_media_account_id' => $account->id,
                    'provider'                => $account->provider ?: ($platform === 'tiktok' ? 'tiktok' : ($platform === 'linkedin' ? 'linkedin' : 'meta')),
                    'channel'                 => $platform,
                    'content_type'            => $targetContentType,
                    'status'                  => 'pending',
                ]);

                if ($isDryRun) {
                    $this->comment(" [DRY-RUN] Created Post {$post->id} and Target {$target->id} with " . count($mediaUrls) . " media file(s).");
                    $results[] = [
                        'format'           => $formatKey,
                        'platform'         => $platform,
                        'account'          => $account->account_name,
                        'post_id'          => $post->id,
                        'status'           => 'dry_run_success',
                        'platform_post_id' => 'DRY_RUN_OK',
                        'media_count'      => count($mediaUrls),
                        'error'            => null,
                    ];
                    continue;
                }

                // Execute Live Publishing
                try {
                    $this->line(" Dispatching post {$post->id} to {$platform} API...");
                    $socialService->publishPost($business, $post);

                    $post->refresh();
                    $target->refresh();

                    $isSuccess = ($post->status === 'published' || $target->status === 'published');
                    $platformPostId = $post->platform_post_id ?: $target->platform_post_id;
                    $error = $target->error_message ?: $post->error_message;

                    if ($isSuccess) {
                        $this->info(" [SUCCESS] Published! Platform Post ID: {$platformPostId}");
                        $results[] = [
                            'format'           => $formatKey,
                            'platform'         => $platform,
                            'account'          => $account->account_name,
                            'post_id'          => $post->id,
                            'status'           => 'published',
                            'platform_post_id' => $platformPostId,
                            'media_count'      => count($mediaUrls),
                            'error'            => null,
                        ];
                    } else {
                        $this->error(" [FAILED] Status: {$post->status} | Error: {$error}");
                        $overallSuccess = false;
                        $results[] = [
                            'format'           => $formatKey,
                            'platform'         => $platform,
                            'account'          => $account->account_name,
                            'post_id'          => $post->id,
                            'status'           => 'failed',
                            'platform_post_id' => null,
                            'media_count'      => count($mediaUrls),
                            'error'            => $error,
                        ];
                    }
                } catch (\Throwable $e) {
                    $this->error(" [EXCEPTION] " . $e->getMessage());
                    Log::error("[TestSocialMediaUploadSuiteCommand] Test failed: {$e->getMessage()}", [
                        'format'     => $formatKey,
                        'platform'   => $platform,
                        'account_id' => $account->id,
                        'trace'      => $e->getTraceAsString(),
                    ]);
                    $overallSuccess = false;
                    $results[] = [
                        'format'           => $formatKey,
                        'platform'         => $platform,
                        'account'          => $account->account_name,
                        'post_id'          => $post->id,
                        'status'           => 'exception',
                        'platform_post_id' => null,
                        'media_count'      => count($mediaUrls),
                        'error'            => $e->getMessage(),
                    ];
                }
            }

            $this->newLine();
        }

        // Summary Table
        $this->info('===============================================================');
        $this->info('  TEST RESULTS SUMMARY');
        $this->info('===============================================================');
        $this->table(
            ['Format', 'Platform', 'Account', 'Status', 'Platform Post ID', 'Error / Info'],
            array_map(fn ($r) => [
                strtoupper((string) $r['format']),
                strtoupper((string) $r['platform']),
                $r['account'],
                $r['status'],
                $r['platform_post_id'] ?? '-',
                $r['error'] ? mb_substr((string) $r['error'], 0, 45) . '...' : 'OK',
            ], $results)
        );

        $summary = [
            'executed_at'     => now()->toIso8601String(),
            'overall_success' => $overallSuccess,
            'total_tests'     => count($results),
            'results'         => $results,
        ];

        $this->writeResultSummary($summary);

        return $overallSuccess ? self::SUCCESS : self::FAILURE;
    }

    protected function writeResultSummary(array $summary): void
    {
        try {
            $dest = storage_path('framework/test_upload_suite_result.json');
            File::ensureDirectoryExists(dirname($dest));
            File::put($dest, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            Log::info('[TestSocialMediaUploadSuiteCommand] Saved test summary', $summary);
        } catch (\Throwable $e) {
            Log::warning('[TestSocialMediaUploadSuiteCommand] Could not save json summary: ' . $e->getMessage());
        }
    }
}
