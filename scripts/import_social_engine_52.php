<?php
// scripts/import_social_engine_52.php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;
use App\Models\SocialMediaPost;
use App\Models\SocialPostMedia;
use App\Models\SocialPostTarget;
use Carbon\Carbon;
use Illuminate\Support\Str;

$admin = Admin::first();
if (!$admin) {
    echo "No admin found.\n";
    exit(1);
}

$jsonPath = __DIR__ . '/../storage/social_engine/evening_evergreen_schedule_52.json';
if (!file_exists($jsonPath)) {
    echo "JSON file not found: $jsonPath\n";
    exit(1);
}

$items = json_decode(file_get_contents($jsonPath), true);
echo "Importing " . count($items) . " social media posts for evening slot deployment...\n";

$imported = 0;
foreach ($items as $item) {
    $scheduledAt = Carbon::parse($item['iso_datetime'], 'Asia/Jakarta');
    $isPast = $scheduledAt->isPast();
    $status = $isPast ? 'published' : 'scheduled';
    $publishedAt = $isPast ? $scheduledAt : null;

    // Channels to deploy: instagram, facebook, linkedin, tiktok
    $platforms = [
        'instagram' => $item['instagram_caption'],
        'facebook' => $item['facebook_caption'],
        'linkedin' => $item['linkedin_caption'],
        'tiktok' => $item['tiktok_caption'],
    ];

    foreach ($platforms as $platform => $caption) {
        $post = SocialMediaPost::updateOrCreate(
            [
                'admin_id' => $admin->id,
                'is_platform' => true,
                'platform' => $platform,
                'scheduled_at' => $scheduledAt,
            ],
            [
                'content' => $caption,
                'media_type' => 'image',
                'media_urls' => [$item['cover_image']],
                'status' => $status,
                'approval_status' => 'approved',
                'published_at' => $publishedAt,
            ]
        );

        // Target record
        $provider = in_array($platform, ['instagram', 'facebook']) ? 'meta' : $platform;
        SocialPostTarget::updateOrCreate(
            [
                'social_media_post_id' => $post->id,
                'channel' => $platform,
            ],
            [
                'provider' => $provider,
                'scheduled_at' => $scheduledAt,
                'status' => $status,
                'published_at' => $publishedAt,
                'platform_post_id' => $isPast ? 'simulated_' . Str::random(12) : null,
            ]
        );

        // Media record
        SocialPostMedia::updateOrCreate(
            [
                'social_media_post_id' => $post->id,
                'media_url' => $item['cover_image'],
            ],
            [
                'media_type' => 'image',
                'sort_order' => 1,
            ]
        );

        $imported++;
    }
}

echo "Successfully populated {$imported} platform social media posts across 4 platforms (Instagram, Facebook, LinkedIn, TikTok) in Evening Slot!\n";
