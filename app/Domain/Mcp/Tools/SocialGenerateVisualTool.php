<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\SocialMedia\SocialVisualGenerationService;
use App\Models\Business;
use App\Models\User;

final class SocialGenerateVisualTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'social_generate_visual';
    }

    public function getDescription(): string
    {
        return 'Men-generate visual konten media sosial (gambar AI fotorealistik, banner promo berlogo resmi, video reels/TikTok, atau poster diskon) secara otomatis langsung di server COOCA tanpa memerlukan upload berkas manual. URL media publik yang dihasilkan siap digunakan untuk penjadwalan postingan media sosial.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:social:create';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'type' => [
                    'type' => 'string',
                    'enum' => ['ai_photo', 'promo_banner', 'hybrid', 'ai_video'],
                    'default' => 'ai_photo',
                    'description' => 'Jenis visual yang ingin dibuat: "ai_photo" (fotografi produk/suasana AI), "promo_banner" (poster promosi berlogo & teks rapi), "hybrid" (foto AI berlatar belakang promo dengan teks dan logo), atau "ai_video" (video MP4 vertikal untuk TikTok/Reels).',
                ],
                'prompt' => [
                    'type' => 'string',
                    'description' => 'Deskripsi visual yang ingin dibuat (misal: "delicious iced coffee latte with foam on rustic wooden table, photorealistic 8k, golden hour"). Wajib untuk type ai_photo, hybrid, atau ai_video.',
                ],
                'video_url' => [
                    'type' => 'string',
                    'description' => 'Tautan URL publik berkas MP4 video dari generator video eksternal (Kling, Luma, Runway, HeyGen) untuk diimpor dan disimpan ke server COOCA.',
                ],
                'headline' => [
                    'type' => 'string',
                    'description' => 'Judul headline promosi besar pada banner (misal: "PROMO WEEKEND DISKON 30%").',
                ],
                'subheadline' => [
                    'type' => 'string',
                    'description' => 'Subjudul penjelasan promosi (misal: "Khusus Dine-in & Takeaway Weekend Ini").',
                ],
                'badge' => [
                    'type' => 'string',
                    'description' => 'Badge kecil penarik perhatian (misal: "DISKON 30%", "PROMO TERBATAS", "MENU BARU").',
                ],
                'cta' => [
                    'type' => 'string',
                    'default' => 'Pesan Sekarang',
                    'description' => 'Teks tombol Call to Action (misal: "Order Sekarang", "Kunjungi Outlet").',
                ],
                'aspect_ratio' => [
                    'type' => 'string',
                    'enum' => ['4:5', '1:1', '9:16'],
                    'default' => '4:5',
                    'description' => 'Rasio aspek gambar/video: "4:5" (1080x1350 Feed Instagram), "1:1" (1080x1080 Feed Kotak), atau "9:16" (1080x1920 Story / Reels / TikTok).',
                ],
                'theme' => [
                    'type' => 'string',
                    'description' => 'Tema warna / suasana visual bebas sesuai kebutuhan Anda (misal: "sage green", "kopi susu gula aren", "pastel pink feminin", "oranye ceria", "emas mewah", "monokrom hitam putih", "ungu lilac", "biru laut", atau kode HEX seperti "#0ea5e9"). Jika dikosongkan, sistem otomatis mencocokkan dengan profil industri & brand toko Anda.',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $service = app(SocialVisualGenerationService::class);

        return $service->generateVisual($business, $user, $arguments);
    }
}
