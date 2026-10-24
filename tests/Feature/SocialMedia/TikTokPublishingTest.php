<?php

declare(strict_types=1);

namespace Tests\Feature\SocialMedia;

use App\Domain\SocialMedia\Clients\TikTokClient;
use App\Domain\SocialMedia\Providers\TikTokProvider;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SocialPostTarget;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class TikTokPublishingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeMerchant(string $bizName = 'Toko TikTok Eksklusif'): array
    {
        $user = User::factory()->create([
            'name'              => 'Owner ' . $bizName,
            'email'             => Str::slug($bizName) . '@cooca.id',
            'email_verified_at' => now(),
        ]);

        $business = Business::create([
            'user_id'  => $user->id,
            'name'     => $bizName,
            'status'   => 'active',
            'currency' => 'IDR',
        ]);

        $business->users()->attach($user->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $user->update(['active_business_id' => $business->id]);
        Context::setBusiness($business);

        return [$user, $business];
    }

    /**
     * Test single photo publishing to TikTok succeeds with proper payload.
     */
    public function test_tiktok_single_photo_publishing_succeeds(): void
    {
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'provider'     => 'tiktok',
            'platform'     => 'tiktok',
            'account_id'   => 'tt_acc_single_photo',
            'account_name' => 'TikTok Photo Creator',
            'access_token' => 'valid_token_photo',
            'status'       => 'active',
        ]);

        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'platform'                => 'tiktok',
            'content'                 => 'Promo Spesial Menu Baru Cooca #kuliner #promo',
            'status'                  => 'draft',
        ]);

        $target = SocialPostTarget::create([
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => $account->id,
            'provider'                => 'tiktok',
            'channel'                 => 'tiktok',
            'content_type'            => 'photo',
            'status'                  => 'pending',
        ]);

        $mediaItems = [
            ['media_url' => 'https://cooca.id/storage/photos/promo1.jpg', 'media_type' => 'image'],
        ];

        $mockClient = Mockery::mock(TikTokClient::class);
        $mockClient->shouldReceive('getCreatorInfo')
            ->once()
            ->with('valid_token_photo')
            ->andReturn([
                'creator_nickname'      => 'TikTok Photo Creator',
                'privacy_level_options' => ['PUBLIC_TO_EVERYONE', 'MUTUAL_FOLLOW_FRIENDS', 'SELF_ONLY'],
            ]);

        $mockClient->shouldReceive('publishPhoto')
            ->once()
            ->with(
                'valid_token_photo',
                'Promo Spesial Menu Baru Cooca #kuliner #promo',
                ['https://cooca.id/storage/photos/promo1.jpg'],
                Mockery::on(function ($options) {
                    return isset($options['privacy_level']) && $options['privacy_level'] === 'PUBLIC_TO_EVERYONE';
                })
            )
            ->andReturn([
                'publish_id' => 'v_pub_photo_123',
            ]);

        $mockClient->shouldReceive('waitForPublishFinished')
            ->once()
            ->with('valid_token_photo', 'v_pub_photo_123')
            ->andReturn([
                'status'  => 'SUCCESS',
                'post_id' => 'tt_post_photo_999',
            ]);

        $provider = new TikTokProvider($mockClient);
        $result = $provider->publish($account, $target, $mediaItems);

        $this->assertSame('tt_post_photo_999', $result['id']);
        $this->assertSame('v_pub_photo_123', $result['publish_id']);
        $this->assertSame('SUCCESS', $result['status']);
    }

    /**
     * Test video publishing with fallback to SELF_ONLY when app is unaudited.
     */
    public function test_tiktok_video_publishing_adapts_to_unaudited_privacy_level(): void
    {
        [$user, $business] = $this->makeMerchant();

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'provider'     => 'tiktok',
            'platform'     => 'tiktok',
            'account_id'   => 'tt_acc_video_demo',
            'account_name' => 'TikTok Video Creator',
            'access_token' => 'valid_token_video',
            'status'       => 'active',
        ]);

        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'platform'                => 'tiktok',
            'content'                 => 'Review produk terlaris minggu ini #viral #fyp',
            'status'                  => 'draft',
        ]);

        $target = SocialPostTarget::create([
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => $account->id,
            'provider'                => 'tiktok',
            'channel'                 => 'tiktok',
            'content_type'            => 'video',
            'status'                  => 'pending',
        ]);

        $mediaItems = [
            ['media_url' => 'https://cooca.id/storage/videos/demo.mp4', 'media_type' => 'video'],
        ];

        $mockClient = Mockery::mock(TikTokClient::class);
        // Simulate unaudited app where TikTok only allows SELF_ONLY
        $mockClient->shouldReceive('getCreatorInfo')
            ->once()
            ->with('valid_token_video')
            ->andReturn([
                'creator_nickname'      => 'TikTok Video Creator',
                'privacy_level_options' => ['SELF_ONLY'],
            ]);

        $mockClient->shouldReceive('publishVideo')
            ->once()
            ->with(
                'valid_token_video',
                'Review produk terlaris minggu ini #viral #fyp',
                'https://cooca.id/storage/videos/demo.mp4',
                Mockery::on(function ($options) {
                    return isset($options['privacy_level']) && $options['privacy_level'] === 'SELF_ONLY';
                })
            )
            ->andReturn([
                'publish_id' => 'v_pub_video_456',
            ]);

        $mockClient->shouldReceive('waitForPublishFinished')
            ->once()
            ->with('valid_token_video', 'v_pub_video_456')
            ->andReturn([
                'status'  => 'SUCCESS',
                'post_id' => 'tt_post_video_888',
            ]);

        $provider = new TikTokProvider($mockClient);
        $result = $provider->publish($account, $target, $mediaItems);

        $this->assertSame('tt_post_video_888', $result['id']);
        $this->assertSame('v_pub_video_456', $result['publish_id']);
        $this->assertSame('SUCCESS', $result['status']);
    }

    /**
     * Test automatic fallback to MEDIA_UPLOAD (Inbox Draft Mode) when unaudited client error occurs.
     */
    public function test_tiktok_video_publishing_falls_back_to_inbox_mode_when_unaudited_client_restricted(): void
    {
        [$user, $business] = $this->makeMerchant('Toko Kopi Senja');

        $account = SocialMediaAccount::create([
            'business_id'  => $business->id,
            'provider'     => 'tiktok',
            'platform'     => 'tiktok',
            'account_id'   => 'tt_acc_fallback_test',
            'account_name' => 'TikTok Fallback Creator',
            'access_token' => 'valid_token_fallback',
            'status'       => 'active',
        ]);

        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $account->id,
            'platform'                => 'tiktok',
            'content'                 => 'Promo kopi sore hari #ngopi #cooca',
            'status'                  => 'draft',
        ]);

        $target = SocialPostTarget::create([
            'social_media_post_id'    => $post->id,
            'social_media_account_id' => $account->id,
            'provider'                => 'tiktok',
            'channel'                 => 'tiktok',
            'content_type'            => 'video',
            'status'                  => 'pending',
        ]);

        $mediaItems = [
            ['media_url' => 'https://cooca.id/storage/videos/kopi.mp4', 'media_type' => 'video'],
        ];

        $mockClient = Mockery::mock(TikTokClient::class);
        $mockClient->shouldReceive('getCreatorInfo')
            ->once()
            ->with('valid_token_fallback')
            ->andReturn([
                'creator_nickname'      => 'TikTok Fallback Creator',
                'privacy_level_options' => ['PUBLIC_TO_EVERYONE', 'MUTUAL_FOLLOW_FRIENDS', 'SELF_ONLY'],
            ]);

        // 1. First call fails with unaudited_client_can_only_post_to_private_accounts
        $mockClient->shouldReceive('publishVideo')
            ->once()
            ->with(
                'valid_token_fallback',
                'Promo kopi sore hari #ngopi #cooca',
                'https://cooca.id/storage/videos/kopi.mp4',
                Mockery::on(function ($options) {
                    return ! isset($options['post_mode']) || $options['post_mode'] === 'DIRECT_POST';
                })
            )
            ->andThrow(new \RuntimeException('TikTok Video Direct Post Error: unaudited_client_can_only_post_to_private_accounts: Please review our integration guidelines at https://developers.tiktok.com/doc/content-sharing-guidelines/'));

        // 2. Second call falls back to MEDIA_UPLOAD (Inbox mode)
        $mockClient->shouldReceive('publishVideo')
            ->once()
            ->with(
                'valid_token_fallback',
                'Promo kopi sore hari #ngopi #cooca',
                'https://cooca.id/storage/videos/kopi.mp4',
                Mockery::on(function ($options) {
                    return isset($options['post_mode']) && $options['post_mode'] === 'MEDIA_UPLOAD';
                })
            )
            ->andReturn([
                'publish_id' => 'v_inbox_video_777',
            ]);

        // 3. Status checks for SEND_TO_USER_INBOX
        $mockClient->shouldReceive('waitForPublishFinished')
            ->once()
            ->with('valid_token_fallback', 'v_inbox_video_777')
            ->andReturn([
                'status'     => 'SEND_TO_USER_INBOX',
                'publish_id' => 'v_inbox_video_777',
            ]);

        $provider = new TikTokProvider($mockClient);
        $result = $provider->publish($account, $target, $mediaItems);

        $this->assertSame('v_inbox_video_777', $result['id']);
        $this->assertSame('v_inbox_video_777', $result['publish_id']);
        $this->assertSame('SEND_TO_USER_INBOX', $result['status']);
    }
}
