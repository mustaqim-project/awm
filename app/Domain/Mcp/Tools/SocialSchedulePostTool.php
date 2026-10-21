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
        return 'Menjadwalkan posting konten promosi ke kanal media sosial (Instagram, TikTok, Facebook Page, X/Twitter).';
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
                        'enum' => ['instagram', 'facebook', 'tiktok', 'twitter'],
                    ],
                    'description' => 'Daftar channel tujuan publikasi.',
                ],
                'scheduled_at' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'description' => 'Waktu jadwal tayang format ISO-8601 (misal: 2026-10-08T17:00:00+07:00). Kosongkan jika ingin segera diterbitkan.',
                ],
                'media_urls' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Daftar URL gambar/video dari cloud storage yang akan diunggah.',
                ],
                'publish_now' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Jika true, konten langsung dipublikasikan seketika tanpa menunggu antrean jadwal.',
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

        $publishNow = (bool) ($arguments['publish_now'] ?? false);
        $scheduledAt = (! empty($arguments['scheduled_at']) && ! $publishNow)
            ? Carbon::parse((string) $arguments['scheduled_at'])
            : now()->addMinutes(5);

        $mediaUrls = (array) ($arguments['media_urls'] ?? []);
        $mediaType = ! empty($mediaUrls) ? 'image' : 'text';

        // Cari akun media sosial terhubung
        $accounts = SocialMediaAccount::where('business_id', $business->id)->get();
        $primaryAccount = $accounts->first();

        $post = DB::transaction(function () use ($business, $user, $caption, $channels, $scheduledAt, $mediaUrls, $mediaType, $primaryAccount, $accounts) {
            $newPost = SocialMediaPost::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'social_media_account_id' => $primaryAccount?->id,
                'platform' => $channels[0] ?? 'instagram',
                'content' => $caption,
                'media_type' => $mediaType,
                'media_urls' => $mediaUrls,
                'status' => 'scheduled',
                'approval_status' => 'approved',
                'scheduled_at' => $scheduledAt,
            ]);

            foreach ($channels as $ch) {
                $targetAcc = $accounts->first(fn ($a) => strtolower($a->platform ?? '') === strtolower($ch));

                SocialPostTarget::create([
                    'social_media_post_id' => $newPost->id,
                    'social_media_account_id' => $targetAcc?->id ?? $primaryAccount?->id,
                    'provider' => $ch,
                    'channel' => $ch,
                    'content_type' => $mediaType,
                    'custom_caption' => $caption,
                    'status' => 'scheduled',
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
                    'scheduled_at' => $scheduledAt->toIso8601String(),
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
            'scheduled_at'     => $post->scheduled_at?->toIso8601String(),
            'published_at'     => $post->published_at?->toIso8601String(),
            'platform_post_id' => $post->platform_post_id,
            'error_message'    => $post->error_message,
            'message'          => $post->status === 'published'
                ? 'Konten promosi berhasil diterbitkan langsung ke ' . implode(', ', $channels) . '!'
                : 'Konten promosi berhasil dijadwalkan ke ' . implode(', ', $channels) . ' untuk tayang pada ' . $scheduledAt->format('d M Y H:i') . '.',
        ];
    }
}
