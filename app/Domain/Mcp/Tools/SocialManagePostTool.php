<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\SocialMedia\SocialMediaService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SocialPostTarget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class SocialManagePostTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'social_manage_post';
    }

    public function getDescription(): string
    {
        return 'Mengelola postingan konten media sosial: melihat daftar postingan (list), cek status & hasil publikasi (get), buat/jadwalkan postingan (create), edit postingan lama (update), hapus/batalkan postingan (delete), dan terbitkan seketika (publish_now) ke Instagram, Facebook Page, TikTok, dan X/Twitter.';
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
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'get', 'create', 'update', 'delete', 'publish_now'],
                    'default' => 'list',
                    'description' => 'Aksi pengelolaan postingan: list (daftar postingan), get (detail & verifikasi status publikasi), create (buat/jadwalkan konten), update (edit postingan lama), delete (hapus postingan), publish_now (terbitkan seketika).',
                ],
                'post_id' => [
                    'type' => 'string',
                    'description' => 'ID unik postingan (UUID). Wajib untuk aksi get, update, delete, dan publish_now.',
                ],
                'caption' => [
                    'type' => 'string',
                    'description' => 'Teks caption konten promosi termasuk hashtag dan Call to Action (CTA).',
                ],
                'channels' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'enum' => ['instagram', 'facebook', 'tiktok', 'twitter', 'threads'],
                    ],
                    'description' => 'Daftar channel/platform tujuan publikasi (misal: ["instagram", "facebook"]).',
                ],
                'media_urls' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Daftar URL gambar atau video yang akan diunggah (bisa didapatkan dari tool social_upload_media).',
                ],
                'scheduled_at' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'description' => 'Waktu jadwal tayang format ISO-8601 (contoh: 2026-10-22T17:00:00+07:00). Jika publish_now true atau dikosongkan pada publish_now, konten langsung dipublikasikan saat ini.',
                ],
                'publish_now' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Jika true pada create, sistem langsung menerbitkan postingan ke media sosial detik itu juga tanpa menunggu jadwal.',
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['all', 'scheduled', 'published', 'failed', 'draft'],
                    'description' => 'Filter status postingan untuk aksi list.',
                ],
                'platform' => [
                    'type' => 'string',
                    'description' => 'Filter platform media sosial untuk aksi list (instagram, facebook, tiktok, twitter).',
                ],
                'limit' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 50,
                    'default' => 20,
                    'description' => 'Jumlah maksimal postingan yang diambil pada aksi list.',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = trim((string) ($arguments['action'] ?? 'list'));

        return match ($action) {
            'get'         => $this->getPost($business, $arguments),
            'create'      => $this->createPost($business, $user, $arguments),
            'update'      => $this->updatePost($business, $user, $arguments),
            'delete'      => $this->deletePost($business, $user, $arguments),
            'publish_now' => $this->publishNow($business, $arguments),
            default       => $this->listPosts($business, $arguments),
        };
    }

    /**
     * Mengambil daftar postingan media sosial.
     */
    private function listPosts(Business $business, array $args): array
    {
        $query = SocialMediaPost::where('business_id', $business->id)
            ->with(['targets', 'account:id,platform,account_name,username'])
            ->latest();

        if (! empty($args['status']) && $args['status'] !== 'all') {
            $query->where('status', trim((string) $args['status']));
        }

        if (! empty($args['platform'])) {
            $query->where('platform', strtolower(trim((string) $args['platform'])));
        }

        $limit = isset($args['limit']) ? max(1, min(50, (int) $args['limit'])) : 20;
        $totalCount = (clone $query)->count();
        $posts = $query->take($limit)->get();

        $items = $posts->map(function (SocialMediaPost $p) {
            $channels = $p->targets->pluck('channel')->all();
            if (empty($channels) && ! empty($p->platform)) {
                $channels = [$p->platform];
            }

            return [
                'id'               => (string) $p->id,
                'platform'         => $p->platform,
                'channels'         => $channels,
                'status'           => $p->status,
                'caption'          => $p->content,
                'caption_preview'  => mb_strimwidth((string) $p->content, 0, 90, '...'),
                'media_type'       => $p->media_type,
                'media_urls'       => $p->media_urls ?? [],
                'has_media'        => ! empty($p->media_urls),
                'scheduled_at'     => $p->scheduled_at?->toIso8601String(),
                'published_at'     => $p->published_at?->toIso8601String(),
                'platform_post_id' => $p->platform_post_id,
                'error_message'    => $p->error_message,
                'metrics'          => $p->metrics ?? [],
            ];
        })->all();

        return [
            'success'        => true,
            'total_posts'    => $totalCount,
            'returned_count' => count($items),
            'management_url' => url('/social-media/posts'),
            'posts'          => $items,
        ];
    }

    /**
     * Mengambil detail dan verifikasi status sebuah postingan.
     */
    private function getPost(Business $business, array $args): array
    {
        $postId = trim((string) ($args['post_id'] ?? ''));
        if ($postId === '') {
            throw new InvalidArgumentException('Parameter post_id wajib diisi untuk melihat detail postingan.');
        }

        $post = SocialMediaPost::where('business_id', $business->id)
            ->where('id', $postId)
            ->with(['targets', 'account'])
            ->first();

        if ($post === null) {
            throw new InvalidArgumentException("Postingan dengan ID '{$postId}' tidak ditemukan.");
        }

        $targets = $post->targets->map(fn ($t) => [
            'channel'          => $t->channel,
            'status'           => $t->status,
            'platform_post_id' => $t->platform_post_id,
            'post_url'         => $t->post_url ?? null,
            'error_message'    => $t->error_message,
        ])->all();

        return [
            'success'          => true,
            'post_id'          => (string) $post->id,
            'status'           => $post->status,
            'is_published'     => $post->status === 'published',
            'platform'         => $post->platform,
            'caption'          => $post->content,
            'media_type'       => $post->media_type,
            'media_urls'       => $post->media_urls ?? [],
            'scheduled_at'     => $post->scheduled_at?->toIso8601String(),
            'published_at'     => $post->published_at?->toIso8601String(),
            'platform_post_id' => $post->platform_post_id,
            'error_message'    => $post->error_message,
            'metrics'          => $post->metrics ?? [],
            'targets'          => $targets,
            'management_url'   => url('/social-media/posts/' . $post->id),
            'message'          => match ($post->status) {
                'published'  => "Postingan telah berhasil terbit di {$post->platform} pada " . ($post->published_at?->format('d M Y H:i') ?? '-'),
                'scheduled'  => "Postingan dijadwalkan untuk tayang pada " . ($post->scheduled_at?->format('d M Y H:i') ?? '-'),
                'publishing' => 'Postingan sedang dalam antrean pengiriman ke platform media sosial.',
                'failed'     => 'Publikasi gagal: ' . ($post->error_message ?: 'Terjadi kendala pada akun kanal media sosial.'),
                default      => 'Status postingan: ' . $post->status,
            },
        ];
    }

    /**
     * Membuat atau langsung menerbitkan postingan.
     */
    private function createPost(Business $business, ?User $user, array $args): array
    {
        $caption = trim((string) ($args['caption'] ?? ''));
        if ($caption === '') {
            throw new InvalidArgumentException('Teks caption konten tidak boleh kosong.');
        }

        $channels = (array) ($args['channels'] ?? []);
        if (empty($channels)) {
            $channels = ['instagram'];
        }

        $publishNow = (bool) ($args['publish_now'] ?? false);
        $scheduledAt = (! empty($args['scheduled_at']) && ! $publishNow)
            ? Carbon::parse((string) $args['scheduled_at'])
            : now();

        $mediaUrls = (array) ($args['media_urls'] ?? []);
        $mediaType = ! empty($mediaUrls) ? 'image' : 'text';

        $accounts = SocialMediaAccount::where('business_id', $business->id)->get();
        $primaryAccount = $accounts->first();

        $post = DB::transaction(function () use ($business, $user, $caption, $channels, $scheduledAt, $mediaUrls, $mediaType, $primaryAccount, $accounts) {
            $newPost = SocialMediaPost::create([
                'business_id'             => $business->id,
                'user_id'                 => $user?->id,
                'social_media_account_id' => $primaryAccount?->id,
                'platform'                => $channels[0] ?? 'instagram',
                'content'                 => $caption,
                'media_type'              => $mediaType,
                'media_urls'              => $mediaUrls,
                'status'                  => 'scheduled',
                'approval_status'         => 'approved',
                'scheduled_at'            => $scheduledAt,
            ]);

            foreach ($channels as $ch) {
                $targetAcc = $accounts->first(fn ($a) => strtolower($a->platform ?? '') === strtolower($ch));

                SocialPostTarget::create([
                    'social_media_post_id'    => $newPost->id,
                    'social_media_account_id' => $targetAcc?->id ?? $primaryAccount?->id,
                    'provider'                => $ch,
                    'channel'                 => $ch,
                    'content_type'            => $mediaType,
                    'custom_caption'          => $caption,
                    'status'                  => 'scheduled',
                ]);
            }

            AuditLog::create([
                'business_id'    => $business->id,
                'user_id'        => $user?->id,
                'action'         => 'mcp.social_manage_post.created',
                'auditable_type' => SocialMediaPost::class,
                'auditable_id'   => $newPost->id,
                'payload_after'  => [
                    'channels'     => $channels,
                    'scheduled_at' => $scheduledAt->toIso8601String(),
                ],
            ]);

            return $newPost;
        });

        // Instant publish jika diminta
        if ($publishNow) {
            try {
                $service = app(SocialMediaService::class);
                $post = $service->publishPost($business, $post);
            } catch (\Throwable $e) {
                $post->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success'          => true,
            'post_id'          => (string) $post->id,
            'status'           => $post->status,
            'is_published'     => $post->status === 'published',
            'channels'         => $channels,
            'scheduled_at'     => $post->scheduled_at?->toIso8601String(),
            'published_at'     => $post->published_at?->toIso8601String(),
            'platform_post_id' => $post->platform_post_id,
            'error_message'    => $post->error_message,
            'management_url'   => url('/social-media/posts/' . $post->id),
            'message'          => $post->status === 'published'
                ? "Konten berhasil diterbitkan langsung ke " . implode(', ', $channels) . "!"
                : "Konten berhasil dijadwalkan ke " . implode(', ', $channels) . " untuk tayang pada " . $scheduledAt->format('d M Y H:i') . ".",
        ];
    }

    /**
     * Memperbarui postingan lama yang belum terbit.
     */
    private function updatePost(Business $business, ?User $user, array $args): array
    {
        $postId = trim((string) ($args['post_id'] ?? ''));
        if ($postId === '') {
            throw new InvalidArgumentException('Parameter post_id wajib diisi untuk mengedit postingan.');
        }

        $post = SocialMediaPost::where('business_id', $business->id)->where('id', $postId)->first();
        if ($post === null) {
            throw new InvalidArgumentException("Postingan dengan ID '{$postId}' tidak ditemukan.");
        }

        if ($post->status === 'published') {
            throw new InvalidArgumentException('Postingan yang sudah terbit di media sosial tidak dapat diedit via MCP. Silakan kelola langsung di platform terkait.');
        }

        $updates = [];
        if (! empty($args['caption'])) {
            $updates['content'] = trim((string) $args['caption']);
        }

        if (! empty($args['scheduled_at'])) {
            $updates['scheduled_at'] = Carbon::parse((string) $args['scheduled_at']);
        }

        if (isset($args['media_urls'])) {
            $mediaUrls = (array) $args['media_urls'];
            $updates['media_urls'] = $mediaUrls;
            $updates['media_type'] = ! empty($mediaUrls) ? 'image' : 'text';
        }

        if (! empty($args['channels'])) {
            $channels = (array) $args['channels'];
            $updates['platform'] = $channels[0] ?? $post->platform;
        }

        if (! empty($updates)) {
            $post->update($updates);

            AuditLog::create([
                'business_id'    => $business->id,
                'user_id'        => $user?->id,
                'action'         => 'mcp.social_manage_post.updated',
                'auditable_type' => SocialMediaPost::class,
                'auditable_id'   => $post->id,
                'payload_after'  => $updates,
            ]);
        }

        return [
            'success'        => true,
            'post_id'        => (string) $post->id,
            'status'         => $post->status,
            'caption'        => $post->content,
            'media_urls'     => $post->media_urls ?? [],
            'scheduled_at'   => $post->scheduled_at?->toIso8601String(),
            'message'        => "Postingan '{$post->id}' berhasil diperbarui.",
            'management_url' => url('/social-media/posts/' . $post->id),
        ];
    }

    /**
     * Menghapus atau membatalkan postingan.
     */
    private function deletePost(Business $business, ?User $user, array $args): array
    {
        $postId = trim((string) ($args['post_id'] ?? ''));
        if ($postId === '') {
            throw new InvalidArgumentException('Parameter post_id wajib diisi untuk menghapus postingan.');
        }

        $post = SocialMediaPost::where('business_id', $business->id)->where('id', $postId)->first();
        if ($post === null) {
            throw new InvalidArgumentException("Postingan dengan ID '{$postId}' tidak ditemukan.");
        }

        $postSummary = [
            'id'       => $post->id,
            'platform' => $post->platform,
            'content'  => mb_strimwidth((string) $post->content, 0, 50, '...'),
            'status'   => $post->status,
        ];

        // Hapus target postingan
        SocialPostTarget::where('social_media_post_id', $post->id)->delete();
        $post->delete();

        AuditLog::create([
            'business_id'    => $business->id,
            'user_id'        => $user?->id,
            'action'         => 'mcp.social_manage_post.deleted',
            'auditable_type' => SocialMediaPost::class,
            'auditable_id'   => $postId,
            'payload_before' => $postSummary,
        ]);

        return [
            'success' => true,
            'post_id' => $postId,
            'message' => "Postingan '{$postId}' berhasil dibatalkan dan dihapus dari jadwal COOCA.",
        ];
    }

    /**
     * Menerbitkan postingan seketika ke media sosial (Instant Publish).
     */
    private function publishNow(Business $business, array $args): array
    {
        $postId = trim((string) ($args['post_id'] ?? ''));
        if ($postId === '') {
            throw new InvalidArgumentException('Parameter post_id wajib diisi untuk menerbitkan postingan.');
        }

        $post = SocialMediaPost::where('business_id', $business->id)->where('id', $postId)->first();
        if ($post === null) {
            throw new InvalidArgumentException("Postingan dengan ID '{$postId}' tidak ditemukan.");
        }

        if ($post->status === 'published') {
            return [
                'success'          => true,
                'post_id'          => $post->id,
                'status'           => 'published',
                'platform_post_id' => $post->platform_post_id,
                'message'          => 'Postingan ini sudah pernah berhasil diterbitkan sebelumnya.',
            ];
        }

        try {
            $service = app(SocialMediaService::class);
            $post = $service->publishPost($business, $post);
        } catch (\Throwable $e) {
            $post->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success'       => false,
                'post_id'       => $post->id,
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'message'       => 'Publikasi gagal: ' . $e->getMessage(),
            ];
        }

        return [
            'success'          => $post->status === 'published',
            'post_id'          => $post->id,
            'status'           => $post->status,
            'platform_post_id' => $post->platform_post_id,
            'published_at'     => $post->published_at?->toIso8601String(),
            'error_message'    => $post->error_message,
            'message'          => $post->status === 'published'
                ? "Postingan '{$post->id}' berhasil diterbitkan ke {$post->platform}!"
                : "Status publikasi: {$post->status} (" . ($post->error_message ?: 'Menunggu proses') . ")",
        ];
    }
}
