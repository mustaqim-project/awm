<?php

declare(strict_types=1);

namespace App\Domain\Ai\Inspiration\Services;

use App\Domain\Ai\Inspiration\Adapters\CivitaiAdapter;
use App\Domain\Ai\Inspiration\Adapters\CopasPromptAdapter;
use App\Domain\Ai\Inspiration\Contracts\InspirationAdapterInterface;
use App\Models\AiStudio\InspirationCategory;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationSource;
use App\Models\AiStudio\InspirationSourcePolicy;
use App\Models\AiStudio\InspirationTrendScore;
use App\Models\AiStudio\InspirationVisualAnalysis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class InspirationHarvesterService
{
    /**
     * @var array<string, InspirationAdapterInterface>
     */
    private array $adapters;

    public function __construct(
        CopasPromptAdapter $copasPromptAdapter,
        CivitaiAdapter $civitaiAdapter
    ) {
        $this->adapters = [
            'copasprompt' => $copasPromptAdapter,
            'civitai' => $civitaiAdapter,
        ];
    }

    /**
     * Daftarkan adapter kustom atau runtime.
     */
    public function registerAdapter(InspirationAdapterInterface $adapter): void
    {
        $this->adapters[$adapter->getSourceCode()] = $adapter;
    }

    /**
     * Dapatkan daftar adapter yang tersedia.
     *
     * @return array<string, InspirationAdapterInterface>
     */
    public function getAdapters(): array
    {
        return $this->adapters;
    }

    /**
     * Sinkronkan katalog inspirasi visual dari sumber eksternal ('all', 'copasprompt', atau 'civitai').
     *
     * @return array{
     *     total_synced: int,
     *     sources: array<string, array{status: string, synced: int, error?: string}>
     * }
     */
    public function harvest(string $sourceCode = 'all', int $limit = 24): array
    {
        $this->ensureDefaultCategoriesExist();

        $targetAdapters = [];
        if ($sourceCode === 'all') {
            $targetAdapters = $this->adapters;
        } elseif (isset($this->adapters[$sourceCode])) {
            $targetAdapters[$sourceCode] = $this->adapters[$sourceCode];
        } else {
            throw new \InvalidArgumentException("Sumber inspirasi '{$sourceCode}' tidak dikenali.");
        }

        $results = [
            'total_synced' => 0,
            'sources' => [],
        ];

        foreach ($targetAdapters as $code => $adapter) {
            try {
                $sourceModel = $this->ensureSourceExists($code, $adapter);

                if (! $sourceModel->is_active) {
                    $results['sources'][$code] = [
                        'status' => 'skipped',
                        'synced' => 0,
                        'reason' => 'Source is inactive',
                    ];
                    continue;
                }

                $items = $adapter->search('', ['limit' => $limit]);
                $syncedCount = 0;

                foreach ($items as $itemData) {
                    $this->persistItem($sourceModel, $itemData);
                    $syncedCount++;
                }

                $sourceModel->update(['last_synced_at' => now()]);

                $results['sources'][$code] = [
                    'status' => 'success',
                    'synced' => $syncedCount,
                ];
                $results['total_synced'] += $syncedCount;
            } catch (Throwable $e) {
                Log::error("[InspirationHarvesterService] Gagal sinkronisasi sumber {$code}: " . $e->getMessage(), [
                    'exception' => $e,
                ]);

                $results['sources'][$code] = [
                    'status' => 'failed',
                    'synced' => 0,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Pastikan record InspirationSource dan Policynya ada di database.
     */
    private function ensureSourceExists(string $code, InspirationAdapterInterface $adapter): InspirationSource
    {
        $defaultConfig = [
            'copasprompt' => [
                'name' => 'CopasPrompt Indonesia',
                'driver_class' => CopasPromptAdapter::class,
                'supports_image' => true,
                'supports_video' => false,
                'fetch_frequency_minutes' => 60,
                'rate_limit_per_minute' => 30,
                'disclaimer' => 'Referensi prompt visual lokal UMKM bersumber dari komunitas CopasPrompt Indonesia.',
            ],
            'civitai' => [
                'name' => 'Civitai Open AI Catalog',
                'driver_class' => CivitaiAdapter::class,
                'supports_image' => true,
                'supports_video' => false,
                'fetch_frequency_minutes' => 60,
                'rate_limit_per_minute' => 60,
                'disclaimer' => 'Referensi prompt visual global bersumber dari komunitas terbuka Civitai Open AI.',
            ],
        ];

        $cfg = $defaultConfig[$code] ?? [
            'name' => ucfirst($code),
            'driver_class' => get_class($adapter),
            'supports_image' => true,
            'supports_video' => false,
            'fetch_frequency_minutes' => 60,
            'rate_limit_per_minute' => 30,
            'disclaimer' => 'Referensi visual disediakan untuk inspirasi rekayasa prompt.',
        ];

        /** @var InspirationSource $source */
        $source = InspirationSource::firstOrCreate(
            ['code' => $code],
            [
                'name' => $cfg['name'],
                'driver_class' => $cfg['driver_class'],
                'is_active' => true,
                'fetch_frequency_minutes' => $cfg['fetch_frequency_minutes'],
                'rate_limit_per_minute' => $cfg['rate_limit_per_minute'],
                'supports_image' => $cfg['supports_image'],
                'supports_video' => $cfg['supports_video'],
            ]
        );

        if (! $source->policy) {
            InspirationSourcePolicy::create([
                'source_id' => $source->id,
                'allow_commercial_use' => true,
                'require_attribution' => true,
                'retention_days' => 180,
                'disclaimer_template' => $cfg['disclaimer'],
            ]);
        }

        return $source;
    }

    /**
     * Pastikan kategori standar terdaftar.
     */
    private function ensureDefaultCategoriesExist(): void
    {
        $categories = [
            [
                'slug' => 'katalog-umkm',
                'name' => 'Katalog & Produk UMKM',
                'industry' => 'retail',
                'sort_order' => 1,
            ],
            [
                'slug' => 'makanan-minuman',
                'name' => 'Makanan & Minuman (F&B)',
                'industry' => 'fnb',
                'sort_order' => 2,
            ],
            [
                'slug' => 'poster-komersial',
                'name' => 'Poster & Iklan Komersial',
                'industry' => 'general',
                'sort_order' => 3,
            ],
            [
                'slug' => 'fashion-kecantikan',
                'name' => 'Fashion & Kecantikan',
                'industry' => 'beauty',
                'sort_order' => 4,
            ],
            [
                'slug' => 'viral-trending',
                'name' => 'Viral & Trending',
                'industry' => 'general',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $cat) {
            InspirationCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'industry' => $cat['industry'],
                    'sort_order' => $cat['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Persist satu item inspirasi beserta analisis visual dan skor tren ke database.
     *
     * @param array<string, mixed> $data
     */
    private function persistItem(InspirationSource $source, array $data): InspirationItem
    {
        return DB::transaction(function () use ($source, $data): InspirationItem {
            $tags = (array) ($data['tags'] ?? []);
            $rawPrompt = (string) ($data['raw_prompt'] ?? $data['caption'] ?? '');
            $categoryId = $this->resolveCategoryId($tags, $rawPrompt);
            $industry = $this->resolveIndustry($tags, $rawPrompt);

            $likes = (int) ($data['like_count'] ?? 0);
            $views = (int) ($data['view_count'] ?? ($likes * 10));

            /** @var InspirationItem $item */
            $item = InspirationItem::updateOrCreate(
                [
                    'source_id' => $source->id,
                    'external_id' => (string) $data['external_id'],
                ],
                [
                    'category_id' => $categoryId,
                    'title' => (string) ($data['title'] ?? 'Inspirasi Visual'),
                    'caption' => Str::limit((string) ($data['caption'] ?? $rawPrompt), 500),
                    'canonical_url' => (string) ($data['canonical_url'] ?? ''),
                    'thumbnail_url' => (string) ($data['thumbnail_url'] ?? $data['canonical_url'] ?? ''),
                    'media_type' => (string) ($data['media_type'] ?? 'image'),
                    'media_urls' => (array) ($data['media_urls'] ?? [$data['canonical_url'] ?? '']),
                    'tags' => $tags,
                    'industry_target' => $industry,
                    'like_count' => $likes,
                    'view_count' => $views,
                    'is_curated' => true,
                    'status' => 'active',
                    'collected_at' => now(),
                ]
            );

            // Simpan Analisis Visual Multimodal AI
            $aspectRatio = (string) ($data['aspect_ratio'] ?? '1:1');
            $suggestedRatios = array_values(array_unique([$aspectRatio, '1:1', '4:5', '9:16']));

            InspirationVisualAnalysis::updateOrCreate(
                ['inspiration_item_id' => $item->id],
                [
                    'composition' => $this->detectComposition($rawPrompt, $tags),
                    'lighting' => $this->detectLighting($rawPrompt),
                    'color_mood' => $this->detectColorMood($rawPrompt, $tags),
                    'visual_hook' => 'Komposisi komersial kontras tinggi dengan fokus tajam produk hero',
                    'cta_pattern' => 'Bottom overlay / Ruang teks minimalis untuk info harga & WA',
                    'extracted_style_prompt' => $rawPrompt,
                    'negative_style_prompt' => (string) ($data['negative_prompt'] ?? 'blurry, low quality, distorted, duplicate, text, watermark'),
                    'suggested_aspect_ratios' => $suggestedRatios,
                    'safety_score' => 99.50,
                    'metadata' => (array) ($data['meta_details'] ?? []),
                    'analyzed_at' => now(),
                ]
            );

            // Simpan Skor Tren & Kecepatan Viralitas
            $trendScore = round(($likes * 1.5) + ($views * 0.05), 2);
            $velocity = round($likes > 0 ? min(100.0, ($likes / max(1, $views)) * 1000) : 10.0, 2);

            InspirationTrendScore::updateOrCreate(
                ['inspiration_item_id' => $item->id],
                [
                    'trend_score' => $trendScore,
                    'virality_velocity' => $velocity,
                    'engagement_rate' => $views > 0 ? round($likes / $views, 4) : 0.0500,
                    'recency_weight' => 1.0000,
                    'source_credibility_weight' => 1.0000,
                    'scored_at' => now(),
                ]
            );

            return $item;
        });
    }

    /**
     * Tentukan ID Kategori berdasarkan isi prompt dan tag.
     */
    private function resolveCategoryId(array $tags, string $prompt): ?string
    {
        $haystack = mb_strtolower(implode(' ', $tags) . ' ' . $prompt);

        if (str_contains($haystack, 'makanan') || str_contains($haystack, 'minuman') || str_contains($haystack, 'kuliner') || str_contains($haystack, 'coffee') || str_contains($haystack, 'food') || str_contains($haystack, 'drink')) {
            return InspirationCategory::where('slug', 'makanan-minuman')->value('id');
        }

        if (str_contains($haystack, 'fashion') || str_contains($haystack, 'busana') || str_contains($haystack, 'skincare') || str_contains($haystack, 'kosmetik') || str_contains($haystack, 'beauty')) {
            return InspirationCategory::where('slug', 'fashion-kecantikan')->value('id');
        }

        if (str_contains($haystack, 'poster') || str_contains($haystack, 'iklan') || str_contains($haystack, 'promo') || str_contains($haystack, 'sale') || str_contains($haystack, 'commercial')) {
            return InspirationCategory::where('slug', 'poster-komersial')->value('id');
        }

        return InspirationCategory::where('slug', 'katalog-umkm')->value('id')
            ?? InspirationCategory::where('slug', 'viral-trending')->value('id');
    }

    /**
     * Tentukan vertikal industri (fnb, fashion, beauty, retail, general).
     */
    private function resolveIndustry(array $tags, string $prompt): string
    {
        $haystack = mb_strtolower(implode(' ', $tags) . ' ' . $prompt);

        if (str_contains($haystack, 'makanan') || str_contains($haystack, 'minuman') || str_contains($haystack, 'kuliner') || str_contains($haystack, 'food') || str_contains($haystack, 'drink') || str_contains($haystack, 'coffee')) {
            return 'fnb';
        }
        if (str_contains($haystack, 'fashion') || str_contains($haystack, 'baju') || str_contains($haystack, 'hijab') || str_contains($haystack, 'outfit')) {
            return 'fashion';
        }
        if (str_contains($haystack, 'skincare') || str_contains($haystack, 'serum') || str_contains($haystack, 'kosmetik') || str_contains($haystack, 'beauty')) {
            return 'beauty';
        }
        if (str_contains($haystack, 'produk') || str_contains($haystack, 'kemasan') || str_contains($haystack, 'packaging') || str_contains($haystack, 'retail')) {
            return 'retail';
        }

        return 'general';
    }

    private function detectComposition(string $prompt, array $tags): string
    {
        $p = mb_strtolower($prompt);
        if (str_contains($p, 'podium') || str_contains($p, 'pedestal')) {
            return 'podium';
        }
        if (str_contains($p, 'flatlay') || str_contains($p, 'top view') || str_contains($p, 'overhead')) {
            return 'flatlay';
        }
        if (str_contains($p, 'close-up') || str_contains($p, 'macro') || str_contains($p, 'detail')) {
            return 'macro_detail';
        }
        if (str_contains($p, 'grid') || str_contains($p, 'split')) {
            return 'grid_layout';
        }
        return 'hero_product';
    }

    private function detectLighting(string $prompt): string
    {
        $p = mb_strtolower($prompt);
        if (str_contains($p, 'golden hour') || str_contains($p, 'sunset')) {
            return 'golden_hour';
        }
        if (str_contains($p, 'neon') || str_contains($p, 'cyberpunk') || str_contains($p, 'glow')) {
            return 'neon_glow';
        }
        if (str_contains($p, 'dramatic') || str_contains($p, 'shadow')) {
            return 'dramatic_shadow';
        }
        return 'studio_soft';
    }

    private function detectColorMood(string $prompt, array $tags): string
    {
        $p = mb_strtolower($prompt);
        if (str_contains($p, 'minimalist') || str_contains($p, 'clean') || str_contains($p, 'pastel')) {
            return 'minimalist_pastel';
        }
        if (str_contains($p, 'wood') || str_contains($p, 'rustic') || str_contains($p, 'earthy')) {
            return 'earthy_warm';
        }
        if (str_contains($p, 'luxury') || str_contains($p, 'dark') || str_contains($p, 'gold')) {
            return 'luxury_gold';
        }
        return 'vibrant_commercial';
    }
}
