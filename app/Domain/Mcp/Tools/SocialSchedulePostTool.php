<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SocialPostTarget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class SocialSchedulePostTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'social_schedule_post';
    }

    public function getDescription(): string
    {
        return 'Menjadwalkan posting konten promosi ke kanal media sosial (Instagram, Facebook Page, TikTok, LinkedIn, Threads, X/Twitter).';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:social:manage';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'caption' => [
                    'type' => 'string',
                    'description' => 'Teks caption konten promosi termasuk hashtag dan CTA (Call to Action).',
                ],
                'channels' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'enum' => ['instagram', 'facebook', 'tiktok', 'x', 'twitter', 'linkedin', 'threads', 'pinterest', 'youtube'],
                    ],
                    'description' => 'Daftar channel tujuan publikasi: "instagram", "facebook", "tiktok", "x", "twitter", "linkedin", "threads", "pinterest", "youtube".',
                ],
                'scheduled_at' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'description' => 'Waktu jadwal tayang format ISO-8601 (misal: 2026-10-08T17:00:00+07:00). Kosongkan jika ingin segera diterbitkan.',
                ],
                'media_urls' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Daftar URL gambar/video publik (misal hasil kembalian dari tool social_upload_media atau social_generate_visual). Untuk postingan Feed Instagram disarankan rasio 1:1 atau 4:5, sedangkan untuk Reels/TikTok gunakan rasio vertikal 9:16.',
                ],
                'generate_visual' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Jika true, COOCA akan secara otomatis men-generate gambar visual/banner/video promosi di server dan melampirkannya ke postingan ini.',
                ],
                'visual_type' => [
                    'type' => 'string',
                    'enum' => ['ai_photo', 'promo_banner', 'hybrid', 'ai_video'],
                    'description' => 'Jenis visual yang ingin dibuat otomatis: "ai_photo", "promo_banner", "hybrid", atau "ai_video".',
                ],
                'visual_prompt' => [
                    'type' => 'string',
                    'description' => 'Deskripsi visual jika ingin dibuatkan foto/video produk AI otomatis (misal: "iced latte with espresso swirl on wooden table, warm sunlight, photorealistic 8k").',
                ],
                'video_url' => [
                    'type' => 'string',
                    'description' => 'URL video MP4 dari AI video generator eksternal untuk diunduh dan diterbitkan sebagai TikTok Video / Instagram Reels.',
                ],
                'visual_headline' => [
                    'type' => 'string',
                    'description' => 'Judul headline promosi jika ingin dibuatkan banner poster promosi otomatis berlogo resmi bisnis (misal: "PROMO WEEKEND DISKON 30%").',
                ],
                'visual_subheadline' => [
                    'type' => 'string',
                    'description' => 'Subjudul promo pada banner (misal: "Beli 2 Cup Kopi Gratis 1 Donat").',
                ],
                'visual_badge' => [
                    'type' => 'string',
                    'description' => 'Badge promo (misal: "DISKON 30%", "PROMO TERBATAS", "MENU BARU").',
                ],
                'visual_theme' => [
                    'type' => 'string',
                    'description' => 'Tema warna / suasana visual bebas sesuai kebutuhan Anda (misal: "sage green", "kopi susu gula aren", "pastel pink", "oranye ceria", "emas mewah", "monokrom", "ungu lilac", "biru dongker", atau kode HEX #ff5722). Jika dikosongkan, sistem otomatis mencocokkan dengan profil industri & brand toko Anda.',
                ],
                'publish_now' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Jika true, konten langsung dipublikasikan seketika tanpa menunggu antrean jadwal.',
                ],
                'dry_run' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Mode uji coba/pratinjau. Jika true, sistem memvalidasi teks, panjang karakter, status akun, dan membuat preview visual TANPA menyimpan ke database atau memublikasikan postingan ke media sosial.',
                ],
            ],
            'required' => ['caption', 'channels'],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $caption = trim((string) ($arguments['caption'] ?? ''));
        if ($caption === '') {
            throw new InvalidArgumentException('Teks caption konten tidak boleh kosong.');
        }

        $channels = (array) ($arguments['channels'] ?? []);
        if (empty($channels)) {
            throw new InvalidArgumentException('Minimal satu channel media sosial harus dipilih.');
        }

        $dryRun = (bool) ($arguments['dry_run'] ?? false);

        $isDraft = (bool) ($arguments['is_draft'] ?? false) || (($arguments['status'] ?? '') === 'draft');
        $publishNow = (! $isDraft) && (bool) ($arguments['publish_now'] ?? false);
        $scheduledAt = (! empty($arguments['scheduled_at']) && ! $publishNow && ! $isDraft)
            ? Carbon::parse((string) $arguments['scheduled_at'], 'Asia/Jakarta')->setTimezone('Asia/Jakarta')
            : ($isDraft ? null : now('Asia/Jakarta')->addMinutes(5));

        $mediaUrls = (array) ($arguments['media_urls'] ?? []);
        $generatedType = null;

        // Autopilot Visual Generator: Jika media_urls kosong tapi diminta generate visual
        if (empty($mediaUrls) && (! empty($arguments['generate_visual']) || ! empty($arguments['visual_prompt']) || ! empty($arguments['visual_headline']) || ! empty($arguments['video_url']))) {
            try {
                $visualService = app(\App\Domain\SocialMedia\SocialVisualGenerationService::class);
                $visualRes = $visualService->generateVisual($business, $user, [
                    'type'        => $arguments['visual_type'] ?? (! empty($arguments['video_url']) ? 'ai_video' : ''),
                    'prompt'      => $arguments['visual_prompt'] ?? '',
                    'video_url'   => $arguments['video_url'] ?? '',
                    'headline'    => $arguments['visual_headline'] ?? '',
                    'subheadline' => $arguments['visual_subheadline'] ?? '',
                    'badge'       => $arguments['visual_badge'] ?? '',
                    'theme'       => $arguments['visual_theme'] ?? '',
                    'aspect_ratio'=> $arguments['aspect_ratio'] ?? ((! empty($arguments['video_url']) || ($arguments['visual_type'] ?? '') === 'ai_video') ? '9:16' : '4:5'),
                ]);
                if (! empty($visualRes['media_url'])) {
                    $mediaUrls = [$visualRes['media_url']];
                    $generatedType = $visualRes['media_type'] ?? $visualRes['type'] ?? null;
                }
            } catch (\Throwable $e) {
                // Lanjutkan jika visual gagal agar post teks tetap tersimpan atau lempar exception
                \Illuminate\Support\Facades\Log::warning('[SocialSchedulePostTool] Gagal generate visual otomatis: ' . $e->getMessage());
            }
        }

        $firstMedia = $mediaUrls[0] ?? '';
        $isMediaVideo = $generatedType === 'video' || (! empty($firstMedia) && (
            str_ends_with(strtolower((string) parse_url($firstMedia, PHP_URL_PATH)), '.mp4') ||
            str_ends_with(strtolower((string) parse_url($firstMedia, PHP_URL_PATH)), '.mov') ||
            str_ends_with(strtolower((string) parse_url($firstMedia, PHP_URL_PATH)), '.webm')
        ));

        $mediaType = ! empty($mediaUrls) ? ($isMediaVideo ? 'video' : 'image') : 'text';

        // Cari akun media sosial terhubung
        $accounts = SocialMediaAccount::where('business_id', $business->id)->get();
        $primaryAccount = $accounts->first();

        // Mode Dry-Run / Pratinjau (tanpa menyimpan ke DB atau memublikasikan)
        if ($dryRun) {
            $charLimits = [
                'x'         => 280,
                'twitter'   => 280,
                'threads'   => 500,
                'instagram' => 2200,
                'tiktok'    => 2200,
                'linkedin'  => 3000,
                'facebook'  => 63206,
                'pinterest' => 500,
                'youtube'   => 5000,
            ];

            $captionLen = mb_strlen($caption);
            $validation = [];
            foreach ($channels as $ch) {
                $limit = $charLimits[strtolower($ch)] ?? 2200;
                $isOk = $captionLen <= $limit;
                $acc = $accounts->first(fn ($a) => strtolower($a->platform ?? '') === strtolower($ch));
                $validation[$ch] = [
                    'account_connected' => $acc !== null,
                    'account_name'      => $acc?->name ?? 'Belum terhubung',
                    'token_status'      => ($acc && $acc->token_expires_at && $acc->token_expires_at->isPast()) ? 'expired' : 'active',
                    'caption_length'    => $captionLen,
                    'char_limit'        => $limit,
                    'is_valid'          => $isOk,
                    'warning'           => (! $isOk) ? "Panjang caption ({$captionLen} karakter) melebihi batas kanal {$ch} ({$limit} karakter)." : null,
                ];
            }

            return [
                'status'           => 'dry_run_success',
                'is_dry_run'       => true,
                'channels'         => $channels,
                'channel_checks'   => $validation,
                'media_type'       => $mediaType,
                'media_urls'       => $mediaUrls,
                'scheduled_at'     => $scheduledAt?->setTimezone('Asia/Jakarta')->toIso8601String(),
                'preview_caption'  => $caption,
                'message'          => 'Mode pratinjau (dry run) berhasil! Seluruh validasi kanal, limit teks, dan visual preview telah diuji tanpa menyimpan atau memublikasikan postingan ke akun media sosial.',
            ];
        }

        $initialStatus = $isDraft ? 'draft' : ($publishNow ? 'publishing' : 'scheduled');
        $approvalStatus = $isDraft ? 'draft' : 'approved';

        $post = DB::transaction(function () use ($business, $user, $caption, $channels, $scheduledAt, $mediaUrls, $mediaType, $isMediaVideo, $primaryAccount, $accounts, $initialStatus, $approvalStatus) {
            $newPost = SocialMediaPost::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'social_media_account_id' => $primaryAccount?->id,
                'platform' => $channels[0] ?? 'instagram',
                'content' => $caption,
                'media_type' => $mediaType,
                'media_urls' => $mediaUrls,
                'status' => $initialStatus,
                'approval_status' => $approvalStatus,
                'scheduled_at' => $scheduledAt,
            ]);

            foreach ($channels as $ch) {
                $targetAcc = $accounts->first(fn ($a) => strtolower($a->platform ?? '') === strtolower($ch));

                $targetContentType = $mediaType;
                if ($isMediaVideo) {
                    $targetContentType = match (strtolower($ch)) {
                        'instagram' => 'reel',
                        'tiktok', 'facebook', 'threads' => 'video',
                        default => 'video',
                    };
                }

                SocialPostTarget::create([
                    'social_media_post_id' => $newPost->id,
                    'social_media_account_id' => $targetAcc?->id ?? $primaryAccount?->id,
                    'provider' => $ch,
                    'channel' => $ch,
                    'content_type' => $targetContentType,
                    'custom_caption' => $caption,
                    'status' => $initialStatus,
                ]);
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'action' => 'mcp.social_schedule_post',
                'auditable_type' => SocialMediaPost::class,
                'auditable_id' => $newPost->id,
                'old_values' => null,
                'new_values' => [
                    'channels' => $channels,
                    'scheduled_at' => $scheduledAt?->toIso8601String(),
                    'caption_preview' => mb_strimwidth($caption, 0, 50, '...'),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent() ?: 'COOCA-MCP-Agent',
            ]);

            return $newPost;
        });

        // Trigger immediate publishing if requested
        if ($publishNow) {
            try {
                $service = app(\App\Domain\SocialMedia\SocialMediaService::class);
                $post = $service->publishPost($business, $post);
            } catch (\Throwable $e) {
                $post->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return [
            'status'           => 'success',
            'post_id'          => $post->id,
            'is_published'     => $post->status === 'published',
            'channels'         => $channels,
            'scheduled_at'     => $post->scheduled_at?->setTimezone('Asia/Jakarta')->toIso8601String(),
            'published_at'     => $post->published_at?->setTimezone('Asia/Jakarta')->toIso8601String(),
            'platform_post_id' => $post->platform_post_id,
            'error_message'    => $post->error_message,
            'message'          => $post->status === 'published'
                ? 'Konten promosi berhasil diterbitkan langsung ke ' . implode(', ', $channels) . '!'
                : 'Konten promosi berhasil dijadwalkan ke ' . implode(', ', $channels) . ($post->scheduled_at ? ' untuk tayang pada ' . $post->scheduled_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB.' : ' sebagai draft.'),
        ];
    }
}
