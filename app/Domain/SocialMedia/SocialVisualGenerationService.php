<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia;

use App\Domain\Storage\StorageTrackingService;
use App\Domain\Storage\TenantStorage;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class SocialVisualGenerationService
{
    /**
     * Resolve dimensions from aspect ratio string.
     *
     * @return array{int, int} [width, height]
     */
    public function resolveDimensions(string $aspectRatio): array
    {
        return match ($aspectRatio) {
            '1:1'   => [1080, 1080],
            '9:16'  => [1080, 1920],
            default => [1080, 1350], // 4:5 Instagram Portrait
        };
    }

    /**
     * Automatically detect the best aesthetic visual theme based on the business industry.
     */
    public function detectThemeFromBusiness(Business $business): string
    {
        $category = strtolower((string) ($business->industry_category ?? $business->template_code ?? ''));

        if (Str::contains($category, ['coffee', 'cafe', 'fnb', 'resto', 'kuliner', 'bakery', 'roti', 'makanan', 'minuman'])) {
            return 'warm_coffee';
        }

        if (Str::contains($category, ['fashion', 'beauty', 'butik', 'salon', 'barber', 'perhiasan', 'jewelry', 'skincare'])) {
            return 'pastel_aesthetic';
        }

        if (Str::contains($category, ['bengkel', 'otomotif', 'workshop', 'hardware', 'bangunan', 'material', 'alat'])) {
            return 'industrial_bold';
        }

        if (Str::contains($category, ['apotek', 'klinik', 'dental', 'gigi', 'medis', 'health', 'obat', 'sehat'])) {
            return 'fresh_green';
        }

        if (Str::contains($category, ['laundry', 'cuci', 'cleaning', 'jasa', 'service', 'bersih'])) {
            return 'vibrant_blue';
        }

        if (Str::contains($category, ['tech', 'gadget', 'komputer', 'elektronik', 'hp'])) {
            return 'cyber_neon';
        }

        return 'dark_luxury';
    }

    /**
     * Generate an AI visual from text prompt via server-side pipeline.
     */
    public function generatePhoto(
        Business $business,
        ?User $user,
        string $prompt,
        string $aspectRatio = '4:5'
    ): array {
        $prompt = trim($prompt);
        if ($prompt === '') {
            throw new InvalidArgumentException('Prompt visual tidak boleh kosong.');
        }

        [$width, $height] = $this->resolveDimensions($aspectRatio);

        // Include industry-specific keywords for richer realism
        $industry = $business->industry_category ?: 'product';
        $enhancedPrompt = "{$prompt}, {$industry} subject, professional commercial photography, 8k resolution, studio lighting, highly detailed, photorealistic";
        $encodedPrompt = rawurlencode($enhancedPrompt);

        $serviceUrl = "https://image.pollinations.ai/prompt/{$encodedPrompt}?width={$width}&height={$height}&nologo=true&model=flux";

        try {
            $response = Http::timeout(45)->withHeaders([
                'User-Agent' => 'COOCA-AI-Visual-Engine/1.0',
            ])->get($serviceUrl);

            if (! $response->successful() || strlen($response->body()) === 0) {
                // Fallback to simpler model
                $fallbackUrl = "https://image.pollinations.ai/prompt/" . rawurlencode($prompt) . "?width={$width}&height={$height}&nologo=true";
                $response = Http::timeout(30)->get($fallbackUrl);
            }

            if (! $response->successful() || strlen($response->body()) === 0) {
                throw new \RuntimeException("Gagal men-generate gambar dari AI service (HTTP {$response->status()}).");
            }

            $binaryData = $response->body();
            $fileSize = strlen($binaryData);
            $mimeType = $response->header('Content-Type') ?: 'image/jpeg';
            $extension = str_contains($mimeType, 'png') ? 'png' : 'jpg';

            $fileName = 'ai-visual-' . date('Ymd-His') . '-' . Str::random(5) . '.' . $extension;
            $tenantDir = TenantStorage::publicDir($business, 'social-media');
            $relativePath = $tenantDir . '/' . $fileName;

            Storage::disk(TenantStorage::DISK_PUBLIC)->put($relativePath, $binaryData);

            // Record to StorageTrackingService
            try {
                $owner = $business->owner ?? $user ?? User::where('id', $business->user_id)->first();
                if ($owner !== null) {
                    app(StorageTrackingService::class)->recordUpload(
                        file: $relativePath,
                        filePath: $relativePath,
                        category: 'social-media',
                        module: 'social-media',
                        owner: $owner,
                        business: $business,
                        uploader: $user,
                        disk: TenantStorage::DISK_PUBLIC,
                        fileName: $fileName,
                        mimeType: $mimeType,
                        fileSize: $fileSize
                    );
                }
            } catch (\Throwable) {
                // Soft fail tracking
            }

            $publicUrl = TenantStorage::url($relativePath);

            AuditLog::create([
                'business_id'    => $business->id,
                'user_id'        => $user?->id,
                'action'         => 'mcp.social_generate_visual.photo',
                'auditable_type' => Business::class,
                'auditable_id'   => $business->id,
                'old_values'     => null,
                'new_values'     => [
                    'prompt'     => $prompt,
                    'dimensions' => "{$width}x{$height}",
                    'public_url' => $publicUrl,
                    'file_size'  => $fileSize,
                ],
                'ip_address'     => request()->ip(),
                'user_agent'     => 'COOCA-AI-Engine',
            ]);

            return [
                'success'        => true,
                'media_type'     => 'image',
                'media_url'      => $publicUrl,
                'media_path'     => $relativePath,
                'file_name'      => $fileName,
                'mime_type'      => $mimeType,
                'file_size'      => $fileSize,
                'dimensions'     => "{$width}x{$height}",
                'type'           => 'ai_photo',
                'message'        => 'Visual foto AI berhasil dibuat dan disimpan ke cloud storage bisnis Anda.',
            ];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Gagal men-generate gambar visual: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Ingest or download video clip from external AI video generator or URL.
     *
     * @param array<string, mixed> $params
     */
    public function generateVideo(Business $business, ?User $user, array $params): array
    {
        $videoUrl = trim((string) ($params['video_url'] ?? ''));
        $prompt = trim((string) ($params['prompt'] ?? ''));

        if ($videoUrl === '' && $prompt === '') {
            throw new InvalidArgumentException('Wajib menyertakan video_url atau prompt video.');
        }

        // If no direct URL given, construct cloud AI video source
        if ($videoUrl === '' && $prompt !== '') {
            $encodedPrompt = rawurlencode($prompt);
            $videoUrl = "https://image.pollinations.ai/prompt/{$encodedPrompt}?width=720&height=1280&nologo=true&format=mp4";
        }

        try {
            $response = Http::timeout(60)->withHeaders([
                'User-Agent' => 'COOCA-AI-Video-Engine/1.0',
            ])->get($videoUrl);

            if (! $response->successful()) {
                throw new \RuntimeException("Gagal mengunduh video dari sumber eksternal (HTTP {$response->status()}).");
            }

            $binaryData = $response->body();
            $fileSize = strlen($binaryData);
            if ($fileSize === 0) {
                throw new \RuntimeException('Ukuran berkas video yang diunduh berukuran 0 byte.');
            }

            // Max 60 MB for videos
            if ($fileSize > 62914560) {
                throw new InvalidArgumentException('Ukuran berkas video melebihi batas maksimal 60 MB.');
            }

            $detectedMime = $response->header('Content-Type') ?: 'video/mp4';
            $extension = str_contains($detectedMime, 'quicktime') ? 'mov' : 'mp4';

            // Fallback if image returned instead of video
            if (str_starts_with($detectedMime, 'image/')) {
                $extension = 'jpg';
                $detectedMime = 'image/jpeg';
            }

            $isRealVideo = str_starts_with($detectedMime, 'video/');
            $fileName = ($isRealVideo ? 'ai-video-' : 'ai-visual-') . date('Ymd-His') . '-' . Str::random(5) . '.' . $extension;
            $tenantDir = TenantStorage::publicDir($business, 'social-media');
            $relativePath = $tenantDir . '/' . $fileName;

            Storage::disk(TenantStorage::DISK_PUBLIC)->put($relativePath, $binaryData);

            // Record storage
            try {
                $owner = $business->owner ?? $user ?? User::where('id', $business->user_id)->first();
                if ($owner !== null) {
                    app(StorageTrackingService::class)->recordUpload(
                        file: $relativePath,
                        filePath: $relativePath,
                        category: 'social-media',
                        module: 'social-media',
                        owner: $owner,
                        business: $business,
                        uploader: $user,
                        disk: TenantStorage::DISK_PUBLIC,
                        fileName: $fileName,
                        mimeType: $detectedMime,
                        fileSize: $fileSize
                    );
                }
            } catch (\Throwable) {
                // Soft fail
            }

            $publicUrl = TenantStorage::url($relativePath);

            return [
                'success'        => true,
                'media_type'     => $isRealVideo ? 'video' : 'image',
                'media_url'      => $publicUrl,
                'media_path'     => $relativePath,
                'file_name'      => $fileName,
                'mime_type'      => $detectedMime,
                'file_size'      => $fileSize,
                'dimensions'     => '1080x1920',
                'type'           => $isRealVideo ? 'video' : 'ai_photo',
                'message'        => $isRealVideo
                    ? 'Video pendek berhasil diunduh dan disimpan ke storage cloud COOCA untuk TikTok / Reels.'
                    : 'Visual berhasil disimpan ke cloud storage.',
            ];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Gagal memproses video AI: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate a branded promotional banner using PHP GD engine.
     *
     * @param array<string, mixed> $params
     */
    public function generateBanner(Business $business, ?User $user, array $params): array
    {
        $headline = trim((string) ($params['headline'] ?? $business->name . ' Promo'));
        $subheadline = trim((string) ($params['subheadline'] ?? ''));
        $badge = trim((string) ($params['badge'] ?? 'SPESIAL PROMO'));
        $cta = trim((string) ($params['cta'] ?? 'Pesan Sekarang'));

        // If theme is empty, auto-detect based on business industry!
        $theme = ! empty($params['theme']) ? trim((string) $params['theme']) : $this->detectThemeFromBusiness($business);
        $aspectRatio = (string) ($params['aspect_ratio'] ?? '4:5');
        $bgImageUrl = trim((string) ($params['background_image_url'] ?? ''));

        [$width, $height] = $this->resolveDimensions($aspectRatio);

        $im = imagecreatetruecolor($width, $height);
        imagealphablending($im, true);
        imagesavealpha($im, true);

        // Color palettes dynamically resolved from user theme / hex / industry
        $colors = $this->resolveThemeColors($theme, $business);

        // Background
        $bgDrawn = false;
        if ($bgImageUrl !== '') {
            try {
                $bgResp = Http::timeout(20)->get($bgImageUrl);
                if ($bgResp->successful() && strlen($bgResp->body()) > 0) {
                    $bgSource = @imagecreatefromstring($bgResp->body());
                    if ($bgSource !== false) {
                        $srcW = imagesx($bgSource);
                        $srcH = imagesy($bgSource);
                        imagecopyresampled($im, $bgSource, 0, 0, 0, 0, $width, $height, $srcW, $srcH);
                        imagedestroy($bgSource);
                        $bgDrawn = true;

                        // Dark overlay for readable contrast
                        $overlay = imagecolorallocatealpha($im, 10, 15, 25, 45); // ~65% opaque
                        imagefilledrectangle($im, 0, 0, $width, $height, $overlay);
                    }
                }
            } catch (\Throwable) {
                $bgDrawn = false;
            }
        }

        if (! $bgDrawn) {
            $this->drawGradientBackground($im, $width, $height, $colors['bg_top'], $colors['bg_bottom']);
        }

        // Draw decorative subtle ambient glow circles
        $ambient = imagecolorallocatealpha($im, $colors['accent'][0], $colors['accent'][1], $colors['accent'][2], 100);
        imagefilledellipse($im, (int) ($width * 0.85), (int) ($height * 0.2), 450, 450, $ambient);
        imagefilledellipse($im, (int) ($width * 0.15), (int) ($height * 0.8), 500, 500, $ambient);

        $textWhite = imagecolorallocate($im, 255, 255, 255);
        $textMuted = imagecolorallocate($im, 203, 213, 225);
        $accentColor = imagecolorallocate($im, $colors['accent'][0], $colors['accent'][1], $colors['accent'][2]);
        $badgeBg = imagecolorallocate($im, $colors['badge_bg'][0], $colors['badge_bg'][1], $colors['badge_bg'][2]);
        $badgeText = imagecolorallocate($im, $colors['badge_text'][0], $colors['badge_text'][1], $colors['badge_text'][2]);

        $currentY = 80;

        // 1. Business Logo (Draw official logo if exists)
        $logoDrawn = false;
        if (! empty($business->logo_path)) {
            try {
                if (Storage::disk(TenantStorage::DISK_PUBLIC)->exists($business->logo_path)) {
                    $logoBinary = Storage::disk(TenantStorage::DISK_PUBLIC)->get($business->logo_path);
                    if ($logoBinary) {
                        $logoIm = @imagecreatefromstring($logoBinary);
                        if ($logoIm !== false) {
                            $logoW = imagesx($logoIm);
                            $logoH = imagesy($logoIm);
                            $targetH = 70;
                            $targetW = (int) ($logoW * ($targetH / max(1, $logoH)));
                            if ($targetW > 180) {
                                $targetW = 180;
                                $targetH = (int) ($logoH * ($targetW / max(1, $logoW)));
                            }
                            $logoX = (int) (($width - $targetW) / 2);
                            imagecopyresampled($im, $logoIm, $logoX, $currentY, 0, 0, $targetW, $targetH, $logoW, $logoH);
                            imagedestroy($logoIm);
                            $currentY += $targetH + 15;
                            $logoDrawn = true;
                        }
                    }
                }
            } catch (\Throwable) {
                $logoDrawn = false;
            }
        }

        // Business Name Header
        $businessName = strtoupper($business->name);
        $this->drawCenteredText($im, $businessName, $width / 2, $currentY, $textMuted, 5, true);
        $currentY += 35;

        // Subtle divider line
        $divColor = imagecolorallocatealpha($im, 255, 255, 255, 95);
        imageline($im, (int) ($width / 2 - 120), $currentY, (int) ($width / 2 + 120), $currentY, $divColor);
        $currentY += 70;

        // 2. Badge (e.g. [ DISKON 30% ])
        if ($badge !== '') {
            $badgeStr = '  ' . strtoupper($badge) . '  ';
            $bWidth = strlen($badgeStr) * 16 + 40;
            $bHeight = 44;
            $bX1 = (int) (($width - $bWidth) / 2);
            $bY1 = $currentY;
            $bX2 = $bX1 + $bWidth;
            $bY2 = $bY1 + $bHeight;

            // Draw rounded pill
            imagefilledrectangle($im, $bX1 + 10, $bY1, $bX2 - 10, $bY2, $badgeBg);
            imagefilledellipse($im, $bX1 + 10, (int) ($bY1 + $bHeight / 2), $bHeight, $bHeight, $badgeBg);
            imagefilledellipse($im, $bX2 - 10, (int) ($bY1 + $bHeight / 2), $bHeight, $bHeight, $badgeBg);

            $this->drawCenteredText($im, $badgeStr, $width / 2, $bY1 + 12, $badgeText, 5, true);
            $currentY += 90;
        }

        // 3. Main Headline (Multi-line wrap)
        $headlineLines = $this->wrapText($headline, 22);
        foreach ($headlineLines as $line) {
            $this->drawCenteredText($im, strtoupper($line), $width / 2, $currentY, $textWhite, 5, true);
            $this->drawCenteredText($im, strtoupper($line), $width / 2 + 1, $currentY, $textWhite, 5, true);
            $currentY += 52;
        }
        $currentY += 20;

        // 4. Subheadline
        if ($subheadline !== '') {
            $subLines = $this->wrapText($subheadline, 32);
            foreach ($subLines as $sLine) {
                $this->drawCenteredText($im, $sLine, $width / 2, $currentY, $textMuted, 4, false);
                $currentY += 34;
            }
            $currentY += 35;
        }

        // 5. CTA Button Pill
        if ($cta !== '') {
            $currentY = max($currentY, $height - 230);
            $ctaStr = ' ' . strtoupper($cta) . ' ➔ ';
            $cWidth = strlen($ctaStr) * 15 + 60;
            $cHeight = 56;
            $cX1 = (int) (($width - $cWidth) / 2);
            $cY1 = $currentY;
            $cX2 = $cX1 + $cWidth;
            $cY2 = $cY1 + $cHeight;

            imagefilledrectangle($im, $cX1 + 12, $cY1, $cX2 - 12, $cY2, $accentColor);
            imagefilledellipse($im, $cX1 + 12, (int) ($cY1 + $cHeight / 2), $cHeight, $cHeight, $accentColor);
            imagefilledellipse($im, $cX2 - 12, (int) ($cY1 + $cHeight / 2), $cHeight, $cHeight, $accentColor);

            $this->drawCenteredText($im, $ctaStr, $width / 2, $cY1 + 16, $textWhite, 5, true);
        }

        // 6. Footer branding
        $footerText = $business->address ?: 'Kunjungi outlet kami hari ini • Info & Reservasi';
        $this->drawCenteredText($im, $footerText, $width / 2, $height - 60, $textMuted, 2, false);

        // Capture PNG binary
        ob_start();
        imagepng($im, null, 8);
        $binaryData = (string) ob_get_clean();
        imagedestroy($im);

        $fileSize = strlen($binaryData);
        $fileName = 'banner-' . date('Ymd-His') . '-' . Str::random(5) . '.png';
        $tenantDir = TenantStorage::publicDir($business, 'social-media');
        $relativePath = $tenantDir . '/' . $fileName;

        Storage::disk(TenantStorage::DISK_PUBLIC)->put($relativePath, $binaryData);

        // Record storage tracking
        try {
            $owner = $business->owner ?? $user ?? User::where('id', $business->user_id)->first();
            if ($owner !== null) {
                app(StorageTrackingService::class)->recordUpload(
                    file: $relativePath,
                    filePath: $relativePath,
                    category: 'social-media',
                    module: 'social-media',
                    owner: $owner,
                    business: $business,
                    uploader: $user,
                    disk: TenantStorage::DISK_PUBLIC,
                    fileName: $fileName,
                    mimeType: 'image/png',
                    fileSize: $fileSize
                );
            }
        } catch (\Throwable) {
            // Soft fail
        }

        $publicUrl = TenantStorage::url($relativePath);

        return [
            'success'        => true,
            'media_type'     => 'image',
            'media_url'      => $publicUrl,
            'media_path'     => $relativePath,
            'file_name'      => $fileName,
            'mime_type'      => 'image/png',
            'file_size'      => $fileSize,
            'dimensions'     => "{$width}x{$height}",
            'type'           => 'promo_banner',
            'theme'          => $theme,
            'message'        => "Banner promosi beresolusi {$width}x{$height} tema '{$theme}' berhasil dibuat dan siap dipublikasikan.",
        ];
    }

    /**
     * Unified visual generator dispatcher (photo, banner, hybrid, or video).
     *
     * @param array<string, mixed> $params
     */
    public function generateVisual(Business $business, ?User $user, array $params): array
    {
        $prompt = trim((string) ($params['prompt'] ?? ''));
        $headline = trim((string) ($params['headline'] ?? ''));
        $type = (string) ($params['type'] ?? '');
        $aspectRatio = (string) ($params['aspect_ratio'] ?? '4:5');

        // Mode 0: Video Generation / Ingestion
        if ($type === 'ai_video' || ! empty($params['video_url'])) {
            return $this->generateVideo($business, $user, $params);
        }

        // Mode 1: Photorealistic AI Image
        if ($type === 'ai_photo' || ($prompt !== '' && $headline === '' && $type !== 'promo_banner')) {
            return $this->generatePhoto($business, $user, $prompt, $aspectRatio);
        }

        // Mode 2: Hybrid (Generate AI photo first, then use it as background for promotional banner)
        if ($prompt !== '' && $headline !== '') {
            try {
                $photoRes = $this->generatePhoto($business, $user, $prompt, $aspectRatio);
                if ($photoRes['success'] && ! empty($photoRes['media_url'])) {
                    $params['background_image_url'] = $photoRes['media_url'];
                }
            } catch (\Throwable) {
                // Fallback to pure gradient banner if AI photo failed
            }
        }

        // Mode 3: Branded Promotional Banner
        return $this->generateBanner($business, $user, $params);
    }

    /**
     * Dynamically resolve theme color palette from custom user prompt, Hex codes, or business industry.
     *
     * @return array<string, array<int, int>>
     */
    public function resolveThemeColors(string $theme, Business $business): array
    {
        $themeLower = strtolower(trim($theme));

        if ($themeLower === '') {
            $themeLower = $this->detectThemeFromBusiness($business);
        }

        // 1. Check if user provided custom Hex code(s) (e.g. "#ff5722" or "#1e293b #f59e0b")
        preg_match_all('/#([a-f0-9]{6}|[a-f0-9]{3})\b/i', $themeLower, $hexMatches);
        if (! empty($hexMatches[0])) {
            $hexes = $hexMatches[0];
            $primaryRgb = $this->hexToRgb($hexes[0]);
            $accentRgb = isset($hexes[1]) ? $this->hexToRgb($hexes[1]) : $this->getContrastingAccent($primaryRgb);

            return [
                'bg_top'     => $this->darken($primaryRgb, 0.7),
                'bg_bottom'  => $this->darken($primaryRgb, 0.9),
                'accent'     => $accentRgb,
                'badge_bg'   => $accentRgb,
                'badge_text' => $this->isLightColor($accentRgb) ? [15, 23, 42] : [255, 255, 255],
            ];
        }

        // 2. Keyword matching in Indonesian and English
        if (Str::contains($themeLower, ['sage', 'mint', 'olive', 'lumut', 'matcha'])) {
            return [
                'bg_top'     => [40, 54, 44],
                'bg_bottom'  => [20, 28, 22],
                'accent'     => [138, 171, 143], // Sage green
                'badge_bg'   => [184, 207, 187],
                'badge_text' => [30, 44, 34],
            ];
        }

        if (Str::contains($themeLower, ['lilac', 'lavender', 'ungu', 'purple', 'violet'])) {
            return [
                'bg_top'     => [45, 25, 60],
                'bg_bottom'  => [18, 10, 28],
                'accent'     => [192, 132, 252], // Lilac purple
                'badge_bg'   => [233, 213, 255],
                'badge_text' => [88, 28, 135],
            ];
        }

        if (Str::contains($themeLower, ['pink', 'merah muda', 'rose', 'blush', 'peach', 'pastel', 'feminin'])) {
            return [
                'bg_top'     => [60, 25, 45],
                'bg_bottom'  => [28, 10, 20],
                'accent'     => [244, 114, 182], // Soft Rose
                'badge_bg'   => [251, 207, 232],
                'badge_text' => [131, 24, 67],
            ];
        }

        if (Str::contains($themeLower, ['kopi', 'coklat', 'coffee', 'brown', 'aren', 'latte', 'mocca', 'terracotta'])) {
            return [
                'bg_top'     => [44, 24, 16],
                'bg_bottom'  => [18, 10, 6],
                'accent'     => [217, 119, 6],   // Amber caramel
                'badge_bg'   => [245, 158, 11],
                'badge_text' => [18, 10, 6],
            ];
        }

        if (Str::contains($themeLower, ['orange', 'oranye', 'jingga', 'sunset', 'ceria', 'hangat'])) {
            return [
                'bg_top'     => [67, 20, 7],
                'bg_bottom'  => [26, 8, 3],
                'accent'     => [249, 115, 22],  // Vibrant orange
                'badge_bg'   => [251, 146, 60],
                'badge_text' => [255, 255, 255],
            ];
        }

        if (Str::contains($themeLower, ['kuning', 'emas', 'gold', 'yellow', 'mewah', 'luxury'])) {
            return [
                'bg_top'     => [35, 30, 10],
                'bg_bottom'  => [15, 12, 5],
                'accent'     => [234, 179, 8],   // Rich gold
                'badge_bg'   => [250, 204, 21],
                'badge_text' => [20, 15, 5],
            ];
        }

        if (Str::contains($themeLower, ['hijau', 'green', 'emerald', 'medis', 'segar', 'fresh', 'farmasi', 'klinik'])) {
            return [
                'bg_top'     => [6, 78, 59],
                'bg_bottom'  => [2, 44, 34],
                'accent'     => [52, 199, 89],   // Emerald green
                'badge_bg'   => [52, 199, 89],
                'badge_text' => [2, 44, 34],
            ];
        }

        if (Str::contains($themeLower, ['biru', 'blue', 'navy', 'ocean', 'laut', 'langit', 'dongker', 'laundry'])) {
            return [
                'bg_top'     => [15, 23, 42],
                'bg_bottom'  => [2, 6, 23],
                'accent'     => [0, 122, 255],   // Royal iOS Blue
                'badge_bg'   => [56, 189, 248],  // Sky blue
                'badge_text' => [15, 23, 42],
            ];
        }

        if (Str::contains($themeLower, ['merah', 'red', 'crimson', 'maroon', 'berani', 'pedas', 'bengkel'])) {
            return [
                'bg_top'     => [136, 19, 55],
                'bg_bottom'  => [76, 5, 25],
                'accent'     => [244, 63, 94],   // Crimson red
                'badge_bg'   => [244, 63, 94],
                'badge_text' => [255, 255, 255],
            ];
        }

        if (Str::contains($themeLower, ['hitam', 'putih', 'monochrome', 'monokrom', 'abu', 'slate', 'gray', 'grey', 'minimalis'])) {
            return [
                'bg_top'     => [24, 24, 27],
                'bg_bottom'  => [9, 9, 11],
                'accent'     => [244, 244, 245], // Crisp white accent
                'badge_bg'   => [255, 255, 255],
                'badge_text' => [9, 9, 11],
            ];
        }

        if (Str::contains($themeLower, ['neon', 'cyber', 'futuristik', 'tech', 'gamer', 'gadget'])) {
            return [
                'bg_top'     => [30, 15, 60],
                'bg_bottom'  => [10, 5, 25],
                'accent'     => [6, 182, 212],   // Electric cyan
                'badge_bg'   => [168, 85, 247],  // Neon purple
                'badge_text' => [255, 255, 255],
            ];
        }

        // Default luxury dark obsidian
        return [
            'bg_top'     => [24, 24, 27],
            'bg_bottom'  => [9, 9, 11],
            'accent'     => [234, 179, 8],       // Gold
            'badge_bg'   => [234, 179, 8],
            'badge_text' => [9, 9, 11],
        ];
    }

    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $int = hexdec($hex);

        return [
            ($int >> 16) & 255,
            ($int >> 8) & 255,
            $int & 255,
        ];
    }

    protected function darken(array $rgb, float $factor): array
    {
        return [
            max(0, min(255, (int) ($rgb[0] * (1 - $factor)))),
            max(0, min(255, (int) ($rgb[1] * (1 - $factor)))),
            max(0, min(255, (int) ($rgb[2] * (1 - $factor)))),
        ];
    }

    protected function isLightColor(array $rgb): bool
    {
        $luminance = (0.299 * $rgb[0] + 0.587 * $rgb[1] + 0.114 * $rgb[2]) / 255;

        return $luminance > 0.6;
    }

    protected function getContrastingAccent(array $rgb): array
    {
        if ($this->isLightColor($rgb)) {
            return [234, 88, 12]; // Vibrant orange
        }

        return [250, 204, 21]; // Bright yellow
    }

    /**
     * Draw a smooth vertical gradient background.
     */
    protected function drawGradientBackground($im, int $w, int $h, array $cTop, array $cBottom): void
    {
        for ($y = 0; $y < $h; $y++) {
            $ratio = $y / max(1, $h - 1);
            $r = (int) ($cTop[0] + ($cBottom[0] - $cTop[0]) * $ratio);
            $g = (int) ($cTop[1] + ($cBottom[1] - $cTop[1]) * $ratio);
            $b = (int) ($cTop[2] + ($cBottom[2] - $cTop[2]) * $ratio);
            $color = imagecolorallocate($im, $r, $g, $b);
            imageline($im, 0, $y, $w, $y, $color);
        }
    }

    /**
     * Draw centered text using built-in GD font.
     */
    protected function drawCenteredText($im, string $text, float $centerX, int $y, int $color, int $font = 5, bool $bold = false): void
    {
        $charWidth = imagefontwidth($font);
        $textWidth = strlen($text) * $charWidth;
        $posX = (int) ($centerX - ($textWidth / 2));

        imagestring($im, $font, $posX, $y, $text, $color);
        if ($bold) {
            imagestring($im, $font, $posX + 1, $y, $text, $color);
        }
    }

    /**
     * Word wrap text by maximum characters per line.
     *
     * @return array<int, string>
     */
    protected function wrapText(string $text, int $maxChars): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            if (strlen($current . ' ' . $word) <= $maxChars) {
                $current = $current === '' ? $word : $current . ' ' . $word;
            } else {
                if ($current !== '') {
                    $lines[] = $current;
                }
                $current = $word;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }
}
