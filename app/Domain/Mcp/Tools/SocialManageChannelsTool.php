<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\User;

final class SocialManageChannelsTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'social_manage_channels';
    }

    public function getDescription(): string
    {
        return 'Melihat daftar akun dan kanal media sosial yang terhubung ke COOCA (Instagram Business, Facebook Pages, TikTok, X/Twitter, Threads), status koneksi akun (aktif/kedaluwarsa), dan tautan pengaturan otorisasi kanal.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:social:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'platform' => [
                    'type' => 'string',
                    'description' => 'Opsional: filter platform tertentu (instagram, facebook, tiktok, twitter, threads).',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $query = SocialMediaAccount::where('business_id', $business->id);

        if (! empty($arguments['platform'])) {
            $query->where('platform', strtolower(trim((string) $arguments['platform'])));
        }

        $accounts = $query->get();

        $channels = $accounts->map(function (SocialMediaAccount $acc) {
            $postsCount = SocialMediaPost::where('social_media_account_id', $acc->id)->count();

            return [
                'id'                  => (string) $acc->id,
                'platform'            => $acc->platform,
                'account_name'        => $acc->account_name,
                'username'            => $acc->username,
                'profile_picture_url' => $acc->profile_picture_url,
                'status'              => $acc->status,
                'is_connected'        => $acc->status === 'active',
                'token_expires_at'    => $acc->token_expires_at?->toIso8601String(),
                'total_posts_created' => $postsCount,
            ];
        })->all();

        return [
            'success'                 => true,
            'total_channels'          => count($channels),
            'active_channels_count'   => count(array_filter($channels, fn ($c) => $c['is_connected'])),
            'channels'                => $channels,
            'settings_url'            => url('/social-media/accounts'),
            'connect_instructions'    => 'Untuk menautkan atau memperbarui izin akun Instagram Business / Facebook Page, kunjungi menu Media Sosial > Pengaturan Akun di dashboard COOCA (https://cooca.id/social-media/accounts).',
        ];
    }
}
