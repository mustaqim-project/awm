<?php

declare(strict_types=1);

namespace Tests\Feature\SocialMedia;

use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SocialPostMedia;
use App\Models\StorageFile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SocialMediaCronPurgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    private function makeMerchant(string $bizName = 'Cron Purge Merchant'): array
    {
        $user = User::factory()->create([
            'name'  => 'Owner ' . $bizName,
            'email' => Str::slug($bizName) . '@cooca.id',
        ]);

        $business = Business::create([
            'name'        => $bizName,
            'email'       => Str::slug($bizName) . '@cooca.id',
            'phone'       => '0812' . rand(10000000, 99999999),
            'address'     => 'Jl. Digital No. ' . rand(1, 100),
            'business_type' => 'retail',
            'status'      => 'active',
        ]);

        return [$user, $business];
    }

    /**
     * Test social-media:purge-published with default 0 days deletes physical media from storage
     * while preserving post records and updating media_urls and local_media_paths to null.
     */
    public function test_purge_published_command_deletes_files_from_storage_while_preserving_post_records(): void
    {
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'platform'     => 'facebook',
            'account_id'   => 'fb_acc_cron_1',
            'account_name' => 'FB Page Cron',
            'access_token' => 'token_cron_1',
            'status'       => 'active',
        ]);

        $filePath = "social-media/temp/{$business->id}/published_photo.jpg";
        Storage::disk('public')->put($filePath, 'binary_photo_data_12345');
        $this->assertTrue(Storage::disk('public')->exists($filePath));

        // Create StorageFile record
        StorageFile::create([
            'business_id' => $business->id,
            'owner_id'    => $user->id,
            'user_id'     => $user->id,
            'disk'        => 'public',
            'file_path'   => $filePath,
            'file_name'   => 'published_photo.jpg',
            'mime_type'   => 'image/jpeg',
            'file_size'   => 12345,
            'category'    => StorageFile::CATEGORY_SOCIAL_MEDIA,
        ]);

        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'platform'                => 'facebook',
            'content'                 => 'Postingan Terpublikasi yang Ingin Dibersihkan Filenya',
            'status'                  => 'published',
            'published_at'            => now(),
            'local_media_paths'       => [$filePath],
            'media_urls'              => ["https://cooca.id/storage/{$filePath}"],
        ]);

        $media = SocialPostMedia::create([
            'social_media_post_id' => $post->id,
            'sort_order'           => 1,
            'media_type'           => 'image',
            'media_url'            => "https://cooca.id/storage/{$filePath}",
            'local_path'           => $filePath,
        ]);

        // Run the cron command with --days=0
        $this->artisan('social-media:purge-published', ['--days' => 0])
            ->expectsOutputToContain('Pembersihan selesai:')
            ->assertExitCode(0);

        // 1. Verify physical file was purged from disk
        $this->assertFalse(Storage::disk('public')->exists($filePath));

        // 2. Verify StorageFile entry was soft deleted
        $this->assertSoftDeleted('storage_files', [
            'file_path' => $filePath,
        ]);

        // 3. Verify post record is PRESERVED in database (not deleted)
        $this->assertDatabaseHas('social_media_posts', [
            'id'     => $post->id,
            'status' => 'published',
        ]);

        // 4. Verify post fields are cleaned to avoid 404 images
        $post->refresh();
        $this->assertNull($post->local_media_paths);
        $this->assertNull($post->media_urls);

        // 5. Verify child media local_path is set to null
        $media->refresh();
        $this->assertNull($media->local_path);
    }

    /**
     * Test publish-scheduled command auto-purges storage files for published posts in every-minute cron run.
     */
    public function test_publish_scheduled_command_auto_purges_storage_for_published_posts(): void
    {
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'platform'     => 'instagram',
            'account_id'   => 'ig_acc_cron_2',
            'account_name' => 'IG Business Cron',
            'access_token' => 'token_cron_2',
            'status'       => 'active',
        ]);

        $filePath = "social-media/temp/{$business->id}/reels_video.mp4";
        Storage::disk('public')->put($filePath, 'binary_video_data_99999');
        $this->assertTrue(Storage::disk('public')->exists($filePath));

        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'platform'                => 'instagram',
            'content'                 => 'Postingan Video Instagram Siap Dibersihkan',
            'status'                  => 'published',
            'published_at'            => now(),
            'local_media_paths'       => [$filePath],
            'media_urls'              => ["https://cooca.id/storage/{$filePath}"],
        ]);

        // Run the publish-scheduled command
        $this->artisan('social-media:publish-scheduled')
            ->expectsOutputToContain('Auto-purged storage media files for 1 published post(s).')
            ->assertExitCode(0);

        // Verify physical file was deleted
        $this->assertFalse(Storage::disk('public')->exists($filePath));

        // Verify post record remains
        $post->refresh();
        $this->assertSame('published', $post->status);
        $this->assertNull($post->local_media_paths);
        $this->assertNull($post->media_urls);
    }

    /**
     * Test purge-published command cleans up orphan temp files.
     */
    public function test_purge_published_command_cleans_orphan_temp_files(): void
    {
        $orphanPath = 'social-media/temp/orphan_image.jpg';
        Storage::disk('public')->put($orphanPath, 'orphan_content');
        $this->assertTrue(Storage::disk('public')->exists($orphanPath));

        // Advance simulated time by 30 minutes so file exceeds the 15-minute orphan threshold
        Carbon::setTestNow(Carbon::now()->addMinutes(30));

        try {
            // Run purge with days=0
            $this->artisan('social-media:purge-published', ['--days' => 0])
                ->assertExitCode(0);

            // Orphan file in temp should be cleaned up
            $this->assertFalse(Storage::disk('public')->exists($orphanPath));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_purge_inactive_credentials_safely_clears_tokens_without_integrity_violation(): void
    {
        $business = Business::create([
            'name' => 'Purge Cred Biz',
            'slug' => 'purge-cred-biz',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'is_active' => true,
        ]);

        $account = SocialMediaAccount::create([
            'business_id' => $business->id,
            'platform' => 'facebook',
            'account_id' => 'fb_page_purge_test',
            'account_name' => 'Facebook Test Page',
            'access_token' => 'EAAG...valid_token',
            'refresh_token' => 'EAAG...refresh_token',
            'status' => 'disconnected',
        ]);

        $this->artisan('social-media:purge-published', ['--days' => 0])
            ->assertExitCode(0);

        $refreshed = $account->fresh();
        $this->assertNull($refreshed->access_token);
        $this->assertNull($refreshed->refresh_token);
        $this->assertSame('expired', $refreshed->status);
    }
}
