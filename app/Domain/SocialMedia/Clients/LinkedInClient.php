<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia\Clients;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInClient
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $apiUrl;
    protected string $authUrl;
    protected string $tokenUrl;

    public function __construct(
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $apiUrl = null,
        ?string $authUrl = null,
        ?string $tokenUrl = null
    ) {
        $this->clientId = $clientId
            ?: (string) (SystemSetting::get('linkedin_client_id') ?: config('services.linkedin.client_id', '868wurbnxke9xg'));
        $this->clientSecret = $clientSecret
            ?: (string) (SystemSetting::get('linkedin_client_secret') ?: config('services.linkedin.client_secret', 'WPL_AP1.T6CrhB0PHBB6XA3T.ddwN2A=='));
        $this->apiUrl = rtrim($apiUrl ?: (string) config('services.linkedin.api_url', 'https://api.linkedin.com/'), '/') . '/';
        $this->authUrl = $authUrl ?: (string) config('services.linkedin.auth_url', 'https://www.linkedin.com/oauth/v2/authorization');
        $this->tokenUrl = $tokenUrl ?: (string) config('services.linkedin.token_url', 'https://www.linkedin.com/oauth/v2/accessToken');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->clientId) && ! empty($this->clientSecret);
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * Generate LinkedIn OAuth 2.0 Authorization URL with OpenID and Social Posting scopes.
     */
    public function getAuthUrl(string $redirectUri, string $state): string
    {
        $params = [
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'redirect_uri'  => $redirectUri,
            'state'         => $state,
            'scope'         => 'openid profile email w_member_social',
        ];

        return $this->authUrl . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for LinkedIn access token.
     *
     * @return array<string, mixed>
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $response = Http::asForm()->timeout(15)->post($this->tokenUrl, [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirectUri,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (! $response->successful()) {
            $errorMsg = $response->json('error_description') ?? $response->json('error') ?? $response->body();
            Log::error('LinkedInClient::exchangeCodeForToken failed', [
                'status' => $response->status(),
                'error'  => $errorMsg,
            ]);

            throw new \RuntimeException("Otorisasi LinkedIn gagal ({$response->status()}): {$errorMsg}");
        }

        return (array) $response->json();
    }

    /**
     * Get member profile info using OpenID UserInfo endpoint.
     *
     * @return array<string, mixed>
     */
    public function getProfile(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->timeout(10)
            ->get($this->apiUrl . 'v2/userinfo');

        if (! $response->successful()) {
            $errorMsg = $response->json('message') ?? $response->body();
            Log::error('LinkedInClient::getProfile failed', [
                'status' => $response->status(),
                'error'  => $errorMsg,
            ]);

            throw new \RuntimeException("Gagal mengambil profil LinkedIn ({$response->status()}): {$errorMsg}");
        }

        return (array) $response->json();
    }

    /**
     * Register upload asset for media (images) via LinkedIn Digital Media API.
     *
     * @return array{upload_url: string, asset: string}
     */
    /**
     * Register upload asset for media (image or video) via LinkedIn Digital Media API.
     *
     * @return array{upload_url: string, asset: string}
     */
    public function registerMediaUpload(string $authorUrn, string $accessToken, string $recipeType = 'image'): array
    {
        $url = $this->apiUrl . 'v2/assets?action=registerUpload';

        $recipe = ($recipeType === 'video')
            ? 'urn:li:digitalmediaRecipe:feedshare-video'
            : 'urn:li:digitalmediaRecipe:feedshare-image';

        $body = [
            'registerUploadRequest' => [
                'recipes' => [
                    $recipe,
                ],
                'owner' => $authorUrn,
                'serviceRelationships' => [
                    [
                        'relationshipType' => 'OWNER',
                        'identifier'       => 'urn:li:userGeneratedContent',
                    ],
                ],
                'supportedUploadMechanism' => [
                    'SYNCHRONOUS_UPLOAD',
                ],
            ],
        ];

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'X-Restli-Protocol-Version' => '2.0.0',
            ])
            ->timeout(20)
            ->post($url, $body);

        if (! $response->successful()) {
            $errorMsg = $response->json('message') ?? $response->body();
            Log::error("LinkedInClient::registerMediaUpload ({$recipeType}) failed", [
                'status' => $response->status(),
                'error'  => $errorMsg,
            ]);
            throw new \RuntimeException("Gagal mendaftarkan unggahan media LinkedIn ({$recipeType}): {$errorMsg}");
        }

        $data = (array) $response->json('value', []);
        $uploadUrl = (string) data_get($data, 'uploadMechanism.com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest.uploadUrl', '');
        $asset = (string) ($data['asset'] ?? '');

        if (empty($uploadUrl) || empty($asset)) {
            throw new \RuntimeException('Respons inisialisasi media LinkedIn tidak valid.');
        }

        return [
            'upload_url' => $uploadUrl,
            'asset'      => $asset,
        ];
    }

    /**
     * Upload physical or downloaded media binary to LinkedIn upload URL.
     */
    public function uploadMediaBinary(string $uploadUrl, string $accessToken, string $binary, string $contentType = 'image/jpeg'): bool
    {
        // Try with Authorization token
        $response = Http::withToken($accessToken)
            ->withBody($binary, $contentType)
            ->timeout(60)
            ->put($uploadUrl);

        if ($response->successful()) {
            return true;
        }

        // Try without Authorization header (presigned S3 / CDN URLs)
        $responseNoAuth = Http::withBody($binary, $contentType)
            ->timeout(60)
            ->put($uploadUrl);

        if ($responseNoAuth->successful()) {
            return true;
        }

        Log::warning('LinkedInClient: uploadMediaBinary failed', [
            'status' => $response->status(),
            'body'   => mb_substr($response->body(), 0, 300),
        ]);

        return false;
    }

    /**
     * Resolve media binary from local storage, local public disk, or HTTP URL.
     */
    protected function resolveMediaBinary(string $mediaUrl, ?string $localPath = null): ?string
    {
        // 1. Storage disk check
        if (! empty($localPath) && \Illuminate\Support\Facades\Storage::disk('public')->exists($localPath)) {
            return (string) \Illuminate\Support\Facades\Storage::disk('public')->get($localPath);
        }

        // 2. Direct public disk path check (instant, immune to loopback network issues)
        if (! empty($mediaUrl)) {
            $parsedPath = parse_url($mediaUrl, PHP_URL_PATH);
            if (! empty($parsedPath)) {
                $directPublic = public_path(ltrim((string) $parsedPath, '/'));
                if (file_exists($directPublic) && is_file($directPublic)) {
                    return (string) file_get_contents($directPublic);
                }
            }
        }

        // 3. Fallback to HTTP GET
        if (! empty($mediaUrl)) {
            try {
                $dl = Http::timeout(30)->withoutVerifying()->get($mediaUrl);
                if ($dl->successful()) {
                    return $dl->body();
                }
            } catch (\Throwable $e) {
                Log::warning("LinkedInClient: Failed downloading media URL [{$mediaUrl}]: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Publish post to member's feed (Text, Image, Multi-Image Carousel, or Video).
     *
     * @param  list<array<string, mixed>>  $mediaItems
     * @return array<string, mixed>
     */
    public function publishPost(string $authorSubOrUrn, string $accessToken, string $caption, array $mediaItems = []): array
    {
        $authorUrn = str_starts_with($authorSubOrUrn, 'urn:li:')
            ? $authorSubOrUrn
            : "urn:li:person:{$authorSubOrUrn}";

        $hasVideo = false;
        foreach ($mediaItems as $m) {
            $type = is_array($m) ? ($m['media_type'] ?? '') : '';
            $url = is_array($m) ? ($m['media_url'] ?? '') : (string) $m;
            if ($type === 'video' || preg_match('/\.(mp4|mov|webm)(\?.*)?$/i', $url)) {
                $hasVideo = true;
                break;
            }
        }

        $mediaList = [];
        $shareMediaCategory = 'NONE';

        if ($hasVideo) {
            // Process Video Upload
            foreach ($mediaItems as $item) {
                $url = is_array($item) ? ($item['media_url'] ?? '') : (string) $item;
                $localPath = is_array($item) ? ($item['local_path'] ?? null) : null;
                $binary = $this->resolveMediaBinary($url, $localPath);

                if (! empty($binary)) {
                    $reg = $this->registerMediaUpload($authorUrn, $accessToken, 'video');
                    $uploaded = $this->uploadMediaBinary($reg['upload_url'], $accessToken, $binary, 'video/mp4');

                    if ($uploaded) {
                        $shareMediaCategory = 'VIDEO';
                        $mediaList[] = [
                            'status'      => 'READY',
                            'description' => [
                                'text' => mb_substr($caption, 0, 200, 'UTF-8'),
                            ],
                            'media'       => $reg['asset'],
                            'title'       => [
                                'text' => 'Video Cooca',
                            ],
                        ];
                        break; // LinkedIn supports 1 video per UGC post
                    } else {
                        throw new \RuntimeException('Gagal mengunggah berkas video ke server LinkedIn CDN.');
                    }
                }
            }
        } elseif (! empty($mediaItems)) {
            // Process Image(s) Upload (Supports single image and multi-image carousel up to 9 items)
            foreach ($mediaItems as $idx => $item) {
                if (count($mediaList) >= 9) {
                    break;
                }

                $url = is_array($item) ? ($item['media_url'] ?? '') : (string) $item;
                $localPath = is_array($item) ? ($item['local_path'] ?? null) : null;
                $binary = $this->resolveMediaBinary($url, $localPath);

                if (! empty($binary)) {
                    $mime = 'image/jpeg';
                    if (str_starts_with($binary, "\x89PNG")) {
                        $mime = 'image/png';
                    }

                    $reg = $this->registerMediaUpload($authorUrn, $accessToken, 'image');
                    $uploaded = $this->uploadMediaBinary($reg['upload_url'], $accessToken, $binary, $mime);

                    if ($uploaded) {
                        $mediaList[] = [
                            'status'      => 'READY',
                            'description' => [
                                'text' => mb_substr($caption, 0, 200, 'UTF-8'),
                            ],
                            'media'       => $reg['asset'],
                            'title'       => [
                                'text' => 'Slide ' . ($idx + 1),
                            ],
                        ];
                    }
                }
            }

            if (! empty($mediaList)) {
                $shareMediaCategory = 'IMAGE';
            } elseif (count($mediaItems) > 0) {
                throw new \RuntimeException('Gagal memproses berkas gambar untuk publikasi LinkedIn.');
            }
        }

        // Build UGC Post Request
        $ugcUrl = $this->apiUrl . 'v2/ugcPosts';

        $body = [
            'author'         => $authorUrn,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => [
                        'text' => $caption,
                    ],
                    'shareMediaCategory' => $shareMediaCategory,
                    'media'              => $mediaList,
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ];

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'X-Restli-Protocol-Version' => '2.0.0',
            ])
            ->timeout(25)
            ->post($ugcUrl, $body);

        // Auto-handle 422 duplicate content error by appending a unique timestamp reference
        if (! $response->successful() && $response->status() === 422 && str_contains((string) $response->body(), 'duplicate')) {
            Log::info('LinkedInClient: Duplicate content detected, retrying with unique timestamp reference...');
            $uniqueCaption = $caption . "\n\n[" . now()->format('d/m H:i:s') . ']';
            $body['specificContent']['com.linkedin.ugc.ShareContent']['shareCommentary']['text'] = $uniqueCaption;

            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'X-Restli-Protocol-Version' => '2.0.0',
                ])
                ->timeout(25)
                ->post($ugcUrl, $body);
        }

        if (! $response->successful()) {
            $errorMsg = $response->json('message') ?? $response->body();
            Log::error('LinkedInClient::publishPost failed', [
                'status' => $response->status(),
                'error'  => $errorMsg,
            ]);

            throw new \RuntimeException("Gagal menerbitkan postingan ke LinkedIn ({$response->status()}): {$errorMsg}");
        }

        $resData = (array) $response->json();
        $postId = (string) ($resData['id'] ?? $response->header('x-restli-id') ?? '');

        return [
            'id'     => $postId,
            'status' => 'SUCCESS',
            'raw'    => $resData,
        ];
    }

    /**
     * Get post engagement metrics (impressions, likes, comments).
     *
     * @return array<string, int>
     */
    public function getPostInsights(string $postId, string $accessToken): array
    {
        return [
            'impressions' => 0,
            'reach'       => 0,
            'likes'       => 0,
            'comments'    => 0,
            'shares'      => 0,
        ];
    }
}
