<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia\Providers;

use App\Domain\SocialMedia\Clients\TikTokClient;
use App\Domain\SocialMedia\Contracts\SocialMediaProviderInterface;
use App\Models\SocialMediaAccount;
use App\Models\SocialPostTarget;
use Illuminate\Support\Facades\Log;

class TikTokProvider implements SocialMediaProviderInterface
{
    public function __construct(
        protected TikTokClient $client
    ) {}

    public function getClient(): TikTokClient
    {
        return $this->client;
    }

    public function getProviderName(): string
    {
        return 'tiktok';
    }

    public function isConfigured(): bool
    {
        return $this->client->isConfigured();
    }

    public function getAuthUrl(string $redirectUri, string $state): string
    {
        return $this->client->getAuthUrl($redirectUri, $state);
    }

    public function handleAuthCallback(string $code, string $redirectUri): array
    {
        $tokenData = $this->client->exchangeCodeForToken($code, $redirectUri);
        $accessToken = (string) ($tokenData['access_token'] ?? '');
        $refreshToken = (string) ($tokenData['refresh_token'] ?? '');
        $expiresIn = (int) ($tokenData['expires_in'] ?? 86400);
        $openId = (string) ($tokenData['open_id'] ?? '');

        $creatorInfo = [];
        try {
            $creatorInfo = $this->client->getCreatorInfo($accessToken);
        } catch (\Throwable $e) {
            Log::warning('TikTok handleAuthCallback getCreatorInfo failed: ' . $e->getMessage());
        }

        $username = (string) ($creatorInfo['creator_username'] ?? $creatorInfo['username'] ?? $openId);
        $displayName = (string) ($creatorInfo['creator_nickname'] ?? $creatorInfo['display_name'] ?? $username);
        $avatar = (string) ($creatorInfo['creator_avatar_url'] ?? $creatorInfo['avatar_url'] ?? '');

        return [
            'open_id'       => $openId,
            'username'      => $username ? "@{$username}" : null,
            'account_name'  => $displayName ?: 'TikTok Creator',
            'avatar_url'    => $avatar ?: null,
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at'    => now()->addSeconds($expiresIn),
            'creator_info'  => $creatorInfo,
            'raw'           => $tokenData,
        ];
    }

    public function refreshToken(SocialMediaAccount $account): SocialMediaAccount
    {
        if (empty($account->refresh_token)) {
            throw new \RuntimeException('Akun TikTok tidak memiliki refresh token.');
        }

        $tokenData = $this->client->refreshToken($account->refresh_token);

        $newAccessToken = (string) ($tokenData['access_token'] ?? '');
        $newRefreshToken = (string) ($tokenData['refresh_token'] ?? $account->refresh_token);
        $expiresIn = (int) ($tokenData['expires_in'] ?? 86400);

        $account->update([
            'access_token'     => $newAccessToken,
            'refresh_token'    => $newRefreshToken,
            'token_expires_at' => now()->addSeconds($expiresIn),
            'status'           => 'active',
        ]);

        return $account;
    }

    public function checkAndRefreshToken(SocialMediaAccount $account): string
    {
        if ($account->needsTokenRefresh(15)) {
            $this->refreshToken($account);
            $account->refresh();
        }

        return (string) $account->access_token;
    }

    public function publish(SocialMediaAccount $account, SocialPostTarget $target, array $mediaItems = []): array
    {
        // 1. Ensure token is valid (refresh if expiring soon)
        if ($account->needsTokenRefresh(15)) {
            $this->refreshToken($account);
            $account->refresh();
        }

        $caption = $target->getEffectiveCaption();
        $contentType = strtolower($target->content_type);

        // 2. Resolve allowed privacy level via Creator Info (prevents rejection if app is unaudited or restricted)
        $privacyLevel = 'PUBLIC_TO_EVERYONE';
        try {
            $creatorInfo = $this->client->getCreatorInfo($account->access_token);
            $optionsList = $creatorInfo['privacy_level_options'] ?? [];
            if (! empty($optionsList) && is_array($optionsList)) {
                if (in_array('PUBLIC_TO_EVERYONE', $optionsList, true)) {
                    $privacyLevel = 'PUBLIC_TO_EVERYONE';
                } else {
                    // Fallback to highest permitted level (e.g. SELF_ONLY for unaudited apps)
                    $privacyLevel = (string) ($optionsList[0] ?? 'SELF_ONLY');
                    Log::info("[TikTokProvider] Creator {$account->account_name} restricts public posting. Using permitted privacy level: {$privacyLevel}");
                }
            }
        } catch (\Throwable $e) {
            Log::warning("[TikTokProvider] Could not query creator_info privacy options: {$e->getMessage()}");
        }

        $publishOptions = [
            'privacy_level' => $privacyLevel,
        ];

        // 3. Direct Photo Post (Single photo or Photo carousel mode: 1 to 35 images)
        if (in_array($contentType, ['photo', 'carousel', 'feed', 'image'], true)) {
            $imageUrls = [];
            foreach ($mediaItems as $m) {
                $url = is_array($m) ? ($m['media_url'] ?? '') : (string) $m;
                if (! empty($url)) {
                    // TikTok Photo API strictly rejects PNG format with file_format_check_failed.
                    // If a PNG URL is passed, resolve to corresponding .jpg or convert on-the-fly.
                    if (preg_match('/\.png(\?.*)?$/i', $url)) {
                        $jpgUrl = preg_replace('/\.png(\?.*)?$/i', '.jpg$1', $url);
                        $parsedPath = parse_url($url, PHP_URL_PATH);
                        $localFile = public_path(ltrim((string) $parsedPath, '/'));
                        $localJpg = preg_replace('/\.png$/i', '.jpg', $localFile);

                        if (file_exists($localJpg)) {
                            $url = $jpgUrl;
                        } elseif (file_exists($localFile) && function_exists('imagecreatefrompng') && function_exists('imagejpeg')) {
                            $src = @imagecreatefrompng($localFile);
                            if ($src) {
                                $w = imagesx($src);
                                $h = imagesy($src);
                                $scale = ($w > 1080 || $h > 1080) ? min(1080 / $w, 1080 / $h) : 1.0;
                                $newW = (int) round($w * $scale);
                                $newH = (int) round($h * $scale);
                                $bg = imagecreatetruecolor($newW, $newH);
                                $white = imagecolorallocate($bg, 255, 255, 255);
                                imagefilledrectangle($bg, 0, 0, $newW, $newH, $white);
                                imagecopyresampled($bg, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
                                imagejpeg($bg, $localJpg, 92);
                                imagedestroy($src);
                                imagedestroy($bg);
                                $url = $jpgUrl;
                            }
                        } else {
                            $url = $jpgUrl;
                        }
                    }

                    // Enforce max 1080px dimension boundary on local JPG to prevent picture_size_check_failed
                    $parsedPath = parse_url($url, PHP_URL_PATH);
                    $localJpg = public_path(ltrim((string) $parsedPath, '/'));
                    if (file_exists($localJpg) && function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
                        [$w, $h] = @getimagesize($localJpg) ?: [0, 0];
                        if ($w > 1080 || $h > 1080) {
                            $scale = min(1080 / $w, 1080 / $h);
                            $newW = (int) round($w * $scale);
                            $newH = (int) round($h * $scale);
                            $src = @imagecreatefromjpeg($localJpg);
                            if ($src) {
                                $dst = imagecreatetruecolor($newW, $newH);
                                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
                                imagejpeg($dst, $localJpg, 92);
                                imagedestroy($src);
                                imagedestroy($dst);
                                Log::info("[TikTokProvider] Auto-downscaled image {$localJpg} from {$w}x{$h} to {$newW}x{$newH}");
                            }
                        }
                    }

                    $imageUrls[] = $url;
                }
            }

            if (count($imageUrls) < 1) {
                throw new \InvalidArgumentException('TikTok Photo Mode mewajibkan minimal 1 gambar.');
            }

            $initData = null;
            try {
                $initData = $this->client->publishPhoto($account->access_token, $caption, $imageUrls, $publishOptions);
            } catch (\Throwable $e) {
                if (str_contains($e->getMessage(), 'unaudited_client_can_only_post_to_private_accounts')) {
                    Log::warning("[TikTokProvider] Direct Photo Post restricted by unaudited TikTok client policy. Retrying DIRECT_POST with SELF_ONLY...", [
                        'account_id'   => $account->id,
                        'account_name' => $account->account_name,
                    ]);
                    try {
                        $initData = $this->client->publishPhoto($account->access_token, $caption, $imageUrls, array_merge($publishOptions, [
                            'privacy_level' => 'SELF_ONLY',
                            'post_mode'     => 'DIRECT_POST',
                        ]));
                    } catch (\Throwable $e2) {
                        Log::warning("[TikTokProvider] Direct Photo Post with SELF_ONLY also rejected ({$e2->getMessage()}). Falling back to TikTok Inbox Mode (MEDIA_UPLOAD)...");
                        $initData = $this->client->publishPhoto($account->access_token, $caption, $imageUrls, array_merge($publishOptions, [
                            'post_mode' => 'MEDIA_UPLOAD',
                        ]));
                    }
                } else {
                    throw $e;
                }
            }

            $publishId = (string) ($initData['publish_id'] ?? '');

            $statusData = $this->client->waitForPublishFinished($account->access_token, $publishId);

            return [
                'id'         => (string) ($statusData['publicaly_available_post_id'] ?? $statusData['post_id'] ?? $publishId),
                'publish_id' => $publishId,
                'status'     => $statusData['status'] ?? 'SUCCESS',
                'raw'        => $statusData,
            ];
        }

        // 4. Direct Video Post
        $videoUrl = '';
        foreach ($mediaItems as $m) {
            $url = is_array($m) ? ($m['media_url'] ?? '') : (string) $m;
            if (! empty($url)) {
                $videoUrl = $url;
                break;
            }
        }

        if (empty($videoUrl)) {
            throw new \InvalidArgumentException('TikTok Video mewajibkan berkas video yang valid.');
        }

        $initData = null;
        try {
            $initData = $this->client->publishVideo($account->access_token, $caption, $videoUrl, $publishOptions);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'unaudited_client_can_only_post_to_private_accounts')) {
                Log::warning("[TikTokProvider] Direct Video Post restricted by unaudited TikTok client policy. Retrying DIRECT_POST with SELF_ONLY...", [
                    'account_id'   => $account->id,
                    'account_name' => $account->account_name,
                ]);
                try {
                    $initData = $this->client->publishVideo($account->access_token, $caption, $videoUrl, array_merge($publishOptions, [
                        'privacy_level' => 'SELF_ONLY',
                        'post_mode'     => 'DIRECT_POST',
                    ]));
                } catch (\Throwable $e2) {
                    Log::warning("[TikTokProvider] Direct Video Post with SELF_ONLY also rejected ({$e2->getMessage()}). Falling back to TikTok Inbox Mode (MEDIA_UPLOAD)...");
                    $initData = $this->client->publishVideo($account->access_token, $caption, $videoUrl, array_merge($publishOptions, [
                        'post_mode' => 'MEDIA_UPLOAD',
                    ]));
                }
            } else {
                throw $e;
            }
        }

        $publishId = (string) ($initData['publish_id'] ?? '');

        $statusData = $this->client->waitForPublishFinished($account->access_token, $publishId);

        return [
            'id'         => (string) ($statusData['publicaly_available_post_id'] ?? $statusData['post_id'] ?? $publishId),
            'publish_id' => $publishId,
            'status'     => $statusData['status'] ?? 'SUCCESS',
            'raw'        => $statusData,
        ];
    }

    public function getCreatorInfo(SocialMediaAccount $account): array
    {
        if ($account->needsTokenRefresh(15)) {
            $this->refreshToken($account);
            $account->refresh();
        }

        return $this->client->getCreatorInfo($account->access_token);
    }

    public function syncMetrics(SocialMediaAccount $account, string $platformPostId): array
    {
        if ($account->needsTokenRefresh(15)) {
            $this->refreshToken($account);
            $account->refresh();
        }

        $metrics = [
            'impressions' => 0,
            'reach'       => 0,
            'likes'       => 0,
            'comments'    => 0,
            'shares'      => 0,
        ];

        try {
            $videoListData = $this->client->getVideoList($account->access_token, 20);
            $videos = $videoListData['videos'] ?? [];
            foreach ($videos as $v) {
                if (($v['id'] ?? '') === $platformPostId) {
                    $metrics['likes'] = (int) ($v['like_count'] ?? 0);
                    $metrics['comments'] = (int) ($v['comment_count'] ?? 0);
                    $metrics['shares'] = (int) ($v['share_count'] ?? 0);
                    $metrics['reach'] = (int) ($v['view_count'] ?? 0);
                    $metrics['impressions'] = (int) ($v['view_count'] ?? 0);
                    break;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("[TikTokProvider] syncMetrics failed: {$e->getMessage()}");
        }

        return $metrics;
    }
}
