<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia\Clients;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TikTokClient
{
    protected string $clientKey;

    protected string $clientSecret;

    protected string $apiUrl;

    protected string $authUrl;

    public function __construct(
        ?string $clientKey = null,
        ?string $clientSecret = null,
        ?string $apiUrl = null,
        ?string $authUrl = null
    ) {
        $this->clientKey = $clientKey ?? (string) (SystemSetting::get('tiktok_client_key') ?: config('services.tiktok.client_key', ''));
        $this->clientSecret = $clientSecret ?? (string) (SystemSetting::get('tiktok_client_secret') ?: config('services.tiktok.client_secret', ''));
        $this->apiUrl = rtrim($apiUrl ?? (string) (SystemSetting::get('tiktok_api_url') ?: config('services.tiktok.api_url', 'https://open.tiktokapis.com/v2/')), '/') . '/';
        $this->authUrl = $authUrl ?? (string) (SystemSetting::get('tiktok_auth_url') ?: config('services.tiktok.auth_url', 'https://www.tiktok.com/v2/auth/authorize/'));
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function isConfigured(): bool
    {
        return ! empty($this->clientKey) && ! empty($this->clientSecret);
    }

    public function endpoint(string $path): string
    {
        return $this->apiUrl . ltrim($path, '/');
    }

    /**
     * Generate official TikTok OAuth 2.0 authorization URL.
     *
     * @param  list<string>|null  $scopes
     */
    public function getAuthUrl(string $redirectUri, string $state, ?array $scopes = null): string
    {
        $defaultScopes = [
            'user.info.basic',
            'user.info.profile',
            'user.info.stats',
            'video.publish',
            'video.upload',
            'video.list',
        ];

        $scopeList = implode(',', $scopes ?: $defaultScopes);

        $params = [
            'client_key'        => $this->clientKey,
            'scope'             => $scopeList,
            'response_type'     => 'code',
            'redirect_uri'      => $redirectUri,
            'state'             => $state,
            'disable_auto_auth' => '1',
            'prompt'            => 'consent',
        ];

        return $this->authUrl . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for Access Token and Refresh Token.
     *
     * @return array<string, mixed>
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $response = Http::asForm()->post($this->endpoint('oauth/token/'), [
            'client_key'    => $this->clientKey,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $redirectUri,
        ]);

        if (! $response->successful() || ! empty($response->json('error'))) {
            $msg = $response->json('error_description') ?: ($response->json('message') ?? 'Gagal menukar kode otorisasi TikTok.');
            Log::error('TikTok exchangeCodeForToken failed', ['body' => $response->body()]);
            throw new \RuntimeException("TikTok OAuth Error: {$msg}");
        }

        $data = $response->json('data') ?: $response->json();
        if (empty($data['access_token'])) {
            throw new \RuntimeException('Access Token TikTok tidak ditemukan dalam respons.');
        }

        return $data;
    }

    /**
     * Refresh an expired or expiring Access Token using Refresh Token.
     *
     * @return array<string, mixed>
     */
    public function refreshToken(string $refreshToken): array
    {
        $response = Http::asForm()->post($this->endpoint('oauth/token/'), [
            'client_key'    => $this->clientKey,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        if (! $response->successful() || ! empty($response->json('error'))) {
            $msg = $response->json('error_description') ?: ($response->json('message') ?? 'Gagal memperbarui token TikTok.');
            Log::error('TikTok refreshToken failed', ['body' => $response->body()]);
            throw new \RuntimeException("TikTok Token Refresh Error: {$msg}");
        }

        $data = $response->json('data') ?: $response->json();
        if (empty($data['access_token'])) {
            throw new \RuntimeException('Token pembaruan TikTok tidak valid.');
        }

        return $data;
    }

    /**
     * Revoke access token.
     */
    public function revokeToken(string $token): array
    {
        $response = Http::asForm()->post($this->endpoint('oauth/revoke/'), [
            'client_key'    => $this->clientKey,
            'client_secret' => $this->clientSecret,
            'token'         => $token,
        ]);

        return $response->json() ?: [];
    }

    /**
     * Query creator info (creator avatar, username, privacy options, interaction limits).
     *
     * @return array<string, mixed>
     */
    public function getCreatorInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->post($this->endpoint('post/publish/creator_info/query/'), (object) []);

        if (! $response->successful()) {
            Log::warning('TikTok getCreatorInfo failed', ['body' => $response->body()]);

            return [];
        }

        return $response->json('data') ?: [];
    }

    /**
     * Fetch user profile info including follower_count, following_count, likes_count, video_count.
     * Requires scope: user.info.stats / user.info.profile
     *
     * @return array<string, mixed>
     */
    public function getUserInfo(string $accessToken): array
    {
        $fields = 'open_id,union_id,avatar_url,display_name,bio_description,profile_deep_link,is_verified,username,follower_count,following_count,likes_count,video_count';

        $response = Http::withToken($accessToken)
            ->get($this->endpoint('user/info/'), [
                'fields' => $fields,
            ]);

        if (! $response->successful()) {
            Log::warning('TikTok getUserInfo failed', ['status' => $response->status(), 'body' => $response->body()]);

            return [];
        }

        return $response->json('data.user') ?: ($response->json('data') ?: []);
    }

    /**
     * Direct Post: Publish video to TikTok using PULL_FROM_URL.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function publishVideo(string $accessToken, string $caption, string $videoUrl, array $options = []): array
    {
        $postMode = strtoupper((string) ($options['post_mode'] ?? 'DIRECT_POST'));
        $title = mb_substr($caption, 0, 2200, 'UTF-8');

        if ($postMode === 'MEDIA_UPLOAD') {
            $endpoint = $this->endpoint('post/publish/inbox/video/init/');
            $payload = [
                'source_info' => [
                    'source'    => 'PULL_FROM_URL',
                    'video_url' => $videoUrl,
                ],
            ];
        } else {
            $endpoint = $this->endpoint('post/publish/video/init/');
            $payload = [
                'post_info' => [
                    'title'                => $title,
                    'privacy_level'        => $options['privacy_level'] ?? 'PUBLIC_TO_EVERYONE',
                    'disable_duet'         => (bool) ($options['disable_duet'] ?? false),
                    'disable_stitch'       => (bool) ($options['disable_stitch'] ?? false),
                    'disable_comment'      => (bool) ($options['disable_comment'] ?? false),
                    'brand_content_toggle' => (bool) ($options['brand_content_toggle'] ?? false),
                    'brand_organic_toggle' => (bool) ($options['brand_organic_toggle'] ?? false),
                    'is_aigc'              => (bool) ($options['is_aigc'] ?? false),
                ],
                'source_info' => [
                    'source'    => 'PULL_FROM_URL',
                    'video_url' => $videoUrl,
                ],
            ];
        }

        $response = Http::withToken($accessToken)->post($endpoint, $payload);

        if (! $response->successful() || (! empty($response->json('error.code')) && $response->json('error.code') !== 'ok')) {
            $errCode = (string) ($response->json('error.code') ?? '');
            $errMsg = (string) ($response->json('error.message') ?: ($response->json('message') ?? 'Gagal menginisialisasi posting video TikTok.'));
            $err = ! empty($errCode) ? "{$errCode}: {$errMsg}" : $errMsg;
            Log::error('TikTok publishVideo failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException("TikTok Video Direct Post Error: {$err}");
        }

        $data = $response->json('data') ?: $response->json();
        $publishId = $data['publish_id'] ?? null;

        if (empty($publishId)) {
            throw new \RuntimeException('Publish ID TikTok tidak ditemukan dalam respons.');
        }

        return $data;
    }

    /**
     * Direct Post: Publish Photo Album Mode to TikTok using PULL_FROM_URL.
     * Supports single photo (1 image) or photo carousel (2 to 35 images).
     *
     * @param  list<string>          $imageUrls
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function publishPhoto(string $accessToken, string $caption, array $imageUrls, array $options = []): array
    {
        if (count($imageUrls) < 1) {
            throw new \InvalidArgumentException('TikTok Photo Mode membutuhkan minimal 1 gambar.');
        }

        if (count($imageUrls) > 35) {
            throw new \InvalidArgumentException('TikTok Photo Mode mendukung maksimal 35 gambar.');
        }

        // TikTok Photo API requirements:
        // - title: max 90 UTF-16 characters
        // - description: max 4,000 UTF-16 characters
        $cleanTitle = trim((string) preg_replace('/\s+/', ' ', strip_tags($caption)));
        $title = $options['title'] ?? (mb_substr($cleanTitle, 0, 85, 'UTF-8') ?: 'Postingan Foto');
        $description = $options['description'] ?? mb_substr($caption, 0, 4000, 'UTF-8');

        $coverIndex = (int) ($options['photo_cover_index'] ?? 0);
        if ($coverIndex < 0 || $coverIndex >= count($imageUrls)) {
            $coverIndex = 0;
        }

        $postMode = strtoupper((string) ($options['post_mode'] ?? 'DIRECT_POST'));
        if (! in_array($postMode, ['DIRECT_POST', 'MEDIA_UPLOAD'], true)) {
            $postMode = 'DIRECT_POST';
        }

        $postInfo = [
            'title'                => $title,
            'description'          => $description,
            'disable_comment'      => (bool) ($options['disable_comment'] ?? false),
            'auto_add_music'       => (bool) ($options['auto_add_music'] ?? true),
            'brand_content_toggle' => (bool) ($options['brand_content_toggle'] ?? false),
            'brand_organic_toggle' => (bool) ($options['brand_organic_toggle'] ?? false),
            'is_aigc'              => (bool) ($options['is_aigc'] ?? false),
        ];

        if ($postMode === 'DIRECT_POST') {
            $postInfo['privacy_level'] = $options['privacy_level'] ?? 'PUBLIC_TO_EVERYONE';
        }

        $payload = [
            'post_info'   => $postInfo,
            'source_info' => [
                'source'            => 'PULL_FROM_URL',
                'photo_cover_index' => $coverIndex,
                'photo_images'      => array_values($imageUrls),
            ],
            'post_mode'  => $postMode,
            'media_type' => 'PHOTO',
        ];

        $response = Http::withToken($accessToken)
            ->post($this->endpoint('post/publish/content/init/'), $payload);

        if (! $response->successful() || (! empty($response->json('error.code')) && $response->json('error.code') !== 'ok')) {
            $errCode = (string) ($response->json('error.code') ?? '');
            $errMsg = (string) ($response->json('error.message') ?: ($response->json('message') ?? 'Gagal menginisialisasi posting foto TikTok.'));
            $err = ! empty($errCode) ? "{$errCode}: {$errMsg}" : $errMsg;
            Log::error('TikTok publishPhoto failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException("TikTok Photo Direct Post Error: {$err}");
        }

        $data = $response->json('data') ?: $response->json();
        $publishId = $data['publish_id'] ?? null;

        if (empty($publishId)) {
            throw new \RuntimeException('Publish ID TikTok tidak ditemukan dalam respons.');
        }

        return $data;
    }

    /**
     * Fetch list of public videos from authorized user's TikTok profile.
     * Requires scope: video.list
     *
     * @return array<string, mixed>
     */
    public function getVideoList(string $accessToken, int $maxCount = 20, ?string $cursor = null): array
    {
        $payload = [
            'max_count' => min(max($maxCount, 1), 20),
        ];

        if (! empty($cursor)) {
            $payload['cursor'] = (int) $cursor;
        }

        $fields = 'id,title,video_description,duration,cover_image_url,embed_link,like_count,comment_count,share_count,view_count';

        $response = Http::withToken($accessToken)
            ->post($this->endpoint("video/list/?fields={$fields}"), $payload);

        if (! $response->successful()) {
            Log::warning('TikTok getVideoList failed', ['status' => $response->status(), 'body' => $response->body()]);

            return [];
        }

        return $response->json('data') ?: [];
    }

    /**
     * Query status of a TikTok publish task.
     *
     * @return array<string, mixed>
     */
    public function getPublishStatus(string $accessToken, string $publishId): array
    {
        $response = Http::withToken($accessToken)
            ->post($this->endpoint('post/publish/status/fetch/'), [
                'publish_id' => $publishId,
            ]);

        if (! $response->successful()) {
            Log::warning('TikTok getPublishStatus failed', ['body' => $response->body()]);

            return ['status' => 'UNKNOWN'];
        }

        return $response->json('data') ?: [];
    }

    /**
     * Poll TikTok publish status until SUCCESS or FAILED.
     *
     * @return array<string, mixed>
     */
    public function waitForPublishFinished(
        string $accessToken,
        string $publishId,
        int $maxAttempts = 10,
        int $sleepSeconds = 2
    ): array {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $data = $this->getPublishStatus($accessToken, $publishId);
            $status = strtoupper((string) ($data['status'] ?? ''));

            if (in_array($status, ['SUCCESS', 'PUBLISH_COMPLETE', 'SEND_TO_USER_INBOX'], true)) {
                return $data;
            }

            if ($status === 'FAILED' || $status === 'FAIL') {
                $reason = $data['fail_reason'] ?? 'Gagal dipublikasikan oleh TikTok.';
                throw new \RuntimeException("TikTok Publish Failed: {$reason}");
            }

            if ($attempt < $maxAttempts) {
                sleep($sleepSeconds);
            }
        }

        // Return current status data if timeout reached (non-blocking for background queue)
        return $data ?? ['status' => 'PROCESSING', 'publish_id' => $publishId];
    }
}
