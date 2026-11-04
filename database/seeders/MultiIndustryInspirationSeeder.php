<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AiStudio\InspirationCategory;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationSource;
use App\Models\AiStudio\InspirationSourcePolicy;
use App\Models\AiStudio\InspirationTrendScore;
use App\Models\AiStudio\InspirationVisualAnalysis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MultiIndustryInspirationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure source exists (inactive - superseded by real photographic catalogs)
        $source = InspirationSource::firstOrCreate(
            ['code' => 'cooca_curated'],
            [
                'name' => 'Cooca Multi-Industry Studio',
                'driver_class' => 'App\\Domain\\Ai\\Inspiration\\Adapters\\GrokImageAdapter',
                'is_active' => false,
                'fetch_frequency_minutes' => 60,
                'rate_limit_per_minute' => 60,
                'supports_image' => true,
                'supports_video' => false,
            ]
        );

        if (! $source->policy) {
            InspirationSourcePolicy::create([
                'source_id' => $source->id,
                'allow_commercial_use' => true,
                'require_attribution' => false,
                'retention_days' => 365,
                'disclaimer_template' => 'Referensi visual komersial multi-industri resmi Cooca.',
            ]);
        }

        // 2. Comprehensive Multi-Industry Categories
        $categoriesData = [
            ['slug' => 'makanan-minuman', 'name' => 'Makanan & Minuman (F&B)', 'industry' => 'fnb', 'sort_order' => 1],
            ['slug' => 'fashion-tekstil', 'name' => 'Fashion & Konveksi', 'industry' => 'fashion', 'sort_order' => 2],
            ['slug' => 'kecantikan-skincare', 'name' => 'Kecantikan & Skincare', 'industry' => 'beauty', 'sort_order' => 3],
            ['slug' => 'jasa-otomotif', 'name' => 'Bengkel & Otomotif', 'industry' => 'automotive', 'sort_order' => 4],
            ['slug' => 'jasa-lifestyle', 'name' => 'Jasa, Salon & Laundry', 'industry' => 'service', 'sort_order' => 5],
            ['slug' => 'ritel-kelontong', 'name' => 'Ritel & Toko Kelontong', 'industry' => 'retail', 'sort_order' => 6],
            ['slug' => 'kesehatan-farmasi', 'name' => 'Apotek, Farmasi & Herbal', 'industry' => 'pharmacy', 'sort_order' => 7],
            ['slug' => 'manufaktur-percetakan', 'name' => 'Percetakan & Kemasan', 'industry' => 'printing', 'sort_order' => 8],
            ['slug' => 'furniture-craft', 'name' => 'Furniture & Kerajinan', 'industry' => 'furniture', 'sort_order' => 9],
            ['slug' => 'kontraktor-interior', 'name' => 'Kontraktor & Bangunan', 'industry' => 'contractor', 'sort_order' => 10],
            ['slug' => 'event-wedding', 'name' => 'Event & Wedding Organizer', 'industry' => 'event', 'sort_order' => 11],
            ['slug' => 'distribusi-fmcg', 'name' => 'Distributor FMCG & Grosir', 'industry' => 'distributor', 'sort_order' => 12],
            ['slug' => 'pertanian-agro', 'name' => 'Pertanian & Peternakan', 'industry' => 'agriculture', 'sort_order' => 13],
            ['slug' => 'viral-trending', 'name' => 'Viral & Trending Multi-Industri', 'industry' => 'general', 'sort_order' => 14],
            ['slug' => 'poster-komersial', 'name' => 'Poster & Iklan Komersial', 'industry' => 'general', 'sort_order' => 15],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $cat = InspirationCategory::updateOrCreate(
                ['slug' => $c['slug']],
                [
                    'name' => $c['name'],
                    'industry' => $c['industry'],
                    'sort_order' => $c['sort_order'],
                    'is_active' => true,
                ]
            );
            $categories[$c['slug']] = $cat;
        }

        // 3. Seed Pinterest E-Commerce & Commercial Catalog for 20+ Industries (Real High-Res Photography)
        $this->seedPinterestCatalog($categories);

        // 4. Update legacy 'general' items with intelligent industry mapping
        $this->smartReclassifyLegacyItems();
    }

    /**
     * Seed curated Pinterest commercial photography catalog.
     */
    private function seedPinterestCatalog(array $categories): void
    {
        $pinterestSource = InspirationSource::firstOrCreate(
            ['code' => 'pinterest'],
            [
                'name' => 'Pinterest Commercial Design Boards',
                'driver_class' => 'App\\Domain\\Ai\\Inspiration\\Adapters\\PinterestAdapter',
                'is_active' => true,
                'fetch_frequency_minutes' => 60,
                'rate_limit_per_minute' => 60,
                'supports_image' => true,
                'supports_video' => false,
            ]
        );

        if (! $pinterestSource->policy) {
            InspirationSourcePolicy::create([
                'source_id' => $pinterestSource->id,
                'allow_commercial_use' => true,
                'require_attribution' => false,
                'retention_days' => 365,
                'disclaimer_template' => 'Referensi visual komersial Pinterest bebas royalti untuk inspirasi fotografi UMKM.',
            ]);
        }

        $templates = (new \App\Domain\Ai\Inspiration\Adapters\PinterestAdapter())->getCuratedPinterestTemplates();

        foreach ($templates as $tpl) {
            $catSlug = match ($tpl['category_code'] ?? $tpl['industry_target']) {
                'fnb', 'Makanan & Minuman (F&B)' => 'makanan-minuman',
                'fashion', 'Fashion & Konveksi' => 'fashion-tekstil',
                'beauty', 'Kecantikan & Skincare' => 'kecantikan-skincare',
                'automotive', 'Bengkel & Otomotif' => 'jasa-otomotif',
                'service', 'Jasa & Layanan Profesional', 'Jasa, Salon & Laundry' => 'jasa-lifestyle',
                'retail', 'Ritel & Produk Kemasan', 'Ritel & Toko Kelontong' => 'ritel-kelontong',
                'pharmacy', 'Apotek, Farmasi & Herbal' => 'kesehatan-farmasi',
                'printing', 'Percetakan & Kemasan' => 'manufaktur-percetakan',
                'furniture', 'Furniture & Kerajinan' => 'furniture-craft',
                'contractor', 'Kontraktor & Bangunan' => 'kontraktor-interior',
                'event', 'Event & Wedding Organizer' => 'event-wedding',
                'distributor', 'Distributor FMCG & Grosir' => 'distribusi-fmcg',
                'agriculture', 'Pertanian & Peternakan' => 'pertanian-agro',
                default => 'viral-trending',
            };

            $cat = $categories[$catSlug] ?? ($categories['viral-trending'] ?? null);

            $safeId = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $tpl['external_id']);
            $localRel = "ai-studio/inspirations/pinterest/{$safeId}.jpg";
            $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($localRel) || file_exists(public_path('storage/' . $localRel))
                ? "/storage/{$localRel}"
                : ($tpl['thumbnail_url'] ?? $tpl['canonical_url']);

            $industryTarget = match ($tpl['industry_target'] ?? 'general') {
                'Makanan & Minuman (F&B)' => 'fnb',
                'Fashion & Konveksi' => 'fashion',
                'Kecantikan & Skincare' => 'beauty',
                'Bengkel & Otomotif' => 'automotive',
                'Jasa & Layanan Profesional', 'Jasa, Salon & Laundry' => 'service',
                'Ritel & Produk Kemasan', 'Ritel & Toko Kelontong' => 'retail',
                'Apotek, Farmasi & Herbal' => 'pharmacy',
                'Percetakan & Kemasan' => 'printing',
                'Furniture & Kerajinan' => 'furniture',
                'Kontraktor & Bangunan' => 'contractor',
                'Event & Wedding Organizer' => 'event',
                'Distributor FMCG & Grosir' => 'distributor',
                'Pertanian & Peternakan' => 'agriculture',
                default => (string) ($tpl['industry_target'] ?? 'general'),
            };

            $item = InspirationItem::updateOrCreate(
                [
                    'source_id' => $pinterestSource->id,
                    'external_id' => $tpl['external_id'],
                ],
                [
                    'category_id' => $cat?->id,
                    'title' => $tpl['title'],
                    'caption' => $tpl['caption'] ?? $tpl['title'],
                    'canonical_url' => $imageUrl,
                    'thumbnail_url' => $imageUrl,
                    'media_type' => 'image',
                    'tags' => $tpl['tags'] ?? [],
                    'industry_target' => $industryTarget,
                    'view_count' => rand(4500, 15000),
                    'like_count' => rand(850, 3200),
                    'comment_count' => rand(70, 480),
                    'share_count' => rand(120, 890),
                    'save_count' => rand(300, 1450),
                    'is_curated' => true,
                    'curated_notes' => 'Kurasi Pinterest commercial photography untuk UMKM Indonesia.',
                    'status' => 'active',
                    'collected_at' => now(),
                ]
            );

            $vis = $tpl['visual_analysis'] ?? [];
            InspirationVisualAnalysis::updateOrCreate(
                ['inspiration_item_id' => $item->id],
                [
                    'composition' => $vis['composition'] ?? 'Dynamic commercial layout',
                    'lighting' => $vis['lighting'] ?? 'Studio lighting',
                    'color_palette' => $vis['color_palette'] ?? ['#111111', '#EEEEEE'],
                    'color_mood' => $vis['color_mood'] ?? 'Commercial appeal',
                    'visual_hook' => $vis['visual_hook'] ?? 'Crisp product focal point',
                    'cta_pattern' => $vis['cta_pattern'] ?? 'Pesan Sekarang',
                    'extracted_style_prompt' => $vis['extracted_style_prompt'] ?? $tpl['caption'],
                    'negative_style_prompt' => $vis['negative_style_prompt'] ?? 'blurry, low quality, distorted',
                    'suggested_aspect_ratios' => $vis['suggested_aspect_ratios'] ?? ['4:5', '1:1', '9:16'],
                    'safety_score' => 0.99,
                    'metadata' => [
                        'source' => 'pinterest',
                        'industry_sector' => $industryTarget,
                    ],
                    'analyzed_at' => now(),
                ]
            );

            InspirationTrendScore::updateOrCreate(
                ['inspiration_item_id' => $item->id],
                [
                    'trend_score' => (float) rand(91, 99) + (rand(1, 9) / 10),
                    'virality_velocity' => (float) rand(88, 98),
                    'engagement_rate' => 0.09,
                    'recency_weight' => 1.0,
                    'source_credibility_weight' => 0.98,
                    'scored_at' => now(),
                ]
            );
        }
    }

    /**
     * Reclassify existing legacy items based on keywords in title/caption.
     */
    private function smartReclassifyLegacyItems(): void
    {
        $rules = [
            'fnb' => ['kopi', 'coffee', 'cafe', 'croissant', 'makanan', 'minuman', 'roti', 'cake', 'bakery', 'kuliner', 'ayam', 'teh', 'boba'],
            'fashion' => ['sepatu', 'sneaker', 'baju', 'hijab', 'kaos', 'fashion', 't-shirt', 'clothing', 'pakaian', 'tas', 'jaket'],
            'beauty' => ['perfume', 'parfum', 'skincare', 'lipstik', 'serum', 'lotion', 'cream', 'beauty', 'kosmetik'],
            'service' => ['barber', 'barbershop', 'laundry', 'salon', 'cukur', 'rambut'],
            'retail' => ['gadget', 'keyboard', 'tech', 'mouse', 'elektronik', 'retail', 'toko'],
            'automotive' => ['motor', 'mobil', 'bengkel', 'oli', 'ban', 'velg'],
        ];

        $items = InspirationItem::where('industry_target', 'general')->get();
        foreach ($items as $item) {
            $text = strtolower($item->title . ' ' . ($item->caption ?? '') . ' ' . json_encode($item->tags ?? []));
            foreach ($rules as $target => $keywords) {
                foreach ($keywords as $kw) {
                    if (str_contains($text, $kw)) {
                        $item->industry_target = $target;
                        $item->save();
                        break 2;
                    }
                }
            }
        }
    }
}
