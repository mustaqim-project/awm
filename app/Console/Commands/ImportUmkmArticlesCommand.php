<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostCluster;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ImportUmkmArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'blog:import-articles {--force : Force overwrite existing posts}';

    /**
     * The console command description.
     */
    protected $description = 'Import 100 UMKM educational articles from database/data/articles into the posts table';

    /**
     * Curated category visual metadata and images.
     */
    private const CATEGORY_CONFIG = [
        'HPP & Harga Jual' => [
            'color' => '#FF9500',
            'icon' => 'calculator',
            'description' => 'Kalkulasi HPP, Food Cost kuliner, markup harga, margin laba, dan strategi diskon UMKM',
            'images' => [
                'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'Kasir & POS' => [
            'color' => '#007AFF',
            'icon' => 'monitor',
            'description' => 'Panduan aplikasi kasir POS, barcode scanner, printer struk, SOP kasir, dan pembayaran QRIS',
            'images' => [
                'https://images.unsplash.com/photo-1556740738-b6a63e27c4df?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1556742502-ec7c0e9f34b1?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'Inventori & Stok' => [
            'color' => '#5856D6',
            'icon' => 'package',
            'description' => 'Manajemen persediaan barang, stok opname, safety stock, metode FIFO/FEFO, dan multi-gudang',
            'images' => [
                'https://images.unsplash.com/photo-1553413077-190dd305871c?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'Keuangan & Pembukuan' => [
            'color' => '#34C759',
            'icon' => 'trending-up',
            'description' => 'Laporan laba rugi, arus kas (cash flow), neraca sederhana, budgeting, dan pemisahan rekening',
            'images' => [
                'https://images.unsplash.com/photo-1554224154-26032ffc0d07?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'Pajak & Legalitas' => [
            'color' => '#FF2D55',
            'icon' => 'shield-check',
            'description' => 'Panduan izin NIB OSS, sertifikasi Halal, izin PIRT, NPWP usaha, dan pelaporan pajak UMKM',
            'images' => [
                'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'Marketing & WhatsApp' => [
            'color' => '#25D366',
            'icon' => 'message-circle',
            'description' => 'Strategi WhatsApp Business, live TikTok Shop, content calendar, dan optimasi Google Maps',
            'images' => [
                'https://images.unsplash.com/photo-1611746872915-64382b5c76da?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'CRM & Loyalty' => [
            'color' => '#AF52DE',
            'icon' => 'heart',
            'description' => 'Program poin loyalitas pelanggan, membership tier, penanganan komplain, dan retensi pelanggan',
            'images' => [
                'https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'Invoice, PO & SO' => [
            'color' => '#00C4D8',
            'icon' => 'file-text',
            'description' => 'Standar invoice profesional, purchase order (PO), sales order (SO), surat jalan, dan tempo net-30',
            'images' => [
                'https://images.unsplash.com/photo-1450133064473-71024230f91b?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'Solusi Per Industri' => [
            'color' => '#FF3B30',
            'icon' => 'store',
            'description' => 'Solusi operasional spesifik kafe, laundry kiloan, barbershop, salon, bengkel, toko kelontong, dan konveksi',
            'images' => [
                'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1545173168-9f1947eebb7f?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
        'Strategi & Operasional' => [
            'color' => '#5AC8FA',
            'icon' => 'compass',
            'description' => 'SOP operasional usaha, jadwal shift karyawan, KPI harian, target omzet, dan Business Operating System',
            'images' => [
                'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
            ],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dir = database_path('data/articles');
        if (!is_dir($dir)) {
            $dir = resource_path('views/public/blog/articles');
        }
        if (!is_dir($dir)) {
            $this->error("Directory not found in database/data/articles or resources/views/public/blog/articles");
            return 1;
        }

        $files = glob($dir . '/*.md');
        if (empty($files) && is_dir(resource_path('views/public/blog/articles'))) {
            $files = glob(resource_path('views/public/blog/articles') . '/*.md');
        }
        sort($files);
        $totalFiles = count($files);

        if ($totalFiles === 0) {
            $this->warn("No markdown files found in {$dir}");
            return 0;
        }

        $this->info("Found {$totalFiles} article files in {$dir}. Starting import...");

        // 1. Ensure Standard Clusters Exist
        $clusterTutorial = PostCluster::firstOrCreate(
            ['code' => 'tutorial'],
            [
                'name' => 'Cluster K - Tutorial Cara',
                'slug' => 'tutorial',
                'description' => 'Panduan teknis langkah demi langkah operasional bisnis, POS kasir, dan resep usaha',
                'icon' => 'wrench',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $clusterEdukasi = PostCluster::firstOrCreate(
            ['code' => 'edukasi'],
            [
                'name' => 'Cluster O - Edukasi Topikal',
                'slug' => 'edukasi',
                'description' => 'Wawasan bisnis, manajemen keuangan, permodalan, legalitas, dan scale-up UMKM',
                'icon' => 'graduation-cap',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // 2. Ensure Categories Exist with High-Quality Metadata
        $categoryMap = [];
        $order = 1;
        foreach (self::CATEGORY_CONFIG as $catName => $meta) {
            $cat = PostCategory::firstOrCreate(
                ['slug' => Str::slug($catName)],
                [
                    'name' => $catName,
                    'description' => $meta['description'],
                    'color' => $meta['color'],
                    'icon' => $meta['icon'],
                    'is_active' => true,
                    'sort_order' => $order++,
                ]
            );
            $categoryMap[$catName] = $cat;
        }

        // 3. Process Each File
        $importedCount = 0;
        $importedSlugs = [];
        $now = now();

        $this->output->progressStart($totalFiles);

        foreach ($files as $idx => $filePath) {
            $content = file_get_contents($filePath);
            $parsed = $this->parseArticleContent($content, $filePath);

            if (empty($parsed['title']) || empty($parsed['slug'])) {
                $this->warn("Skipping invalid file: " . basename($filePath));
                $this->output->progressAdvance();
                continue;
            }

            // Determine cluster
            $isTutorial = str_contains($parsed['cluster_raw'] ?? '', 'Cluster K') || str_contains($parsed['cluster_raw'] ?? '', 'Tutorial');
            $clusterCode = $isTutorial ? 'tutorial' : 'edukasi';
            $clusterId = $isTutorial ? $clusterTutorial->id : $clusterEdukasi->id;

            // Determine category
            $catName = $parsed['category'] ?? 'Umum';
            $categoryObj = $categoryMap[$catName] ?? null;
            if (!$categoryObj) {
                $categoryObj = PostCategory::firstOrCreate(
                    ['slug' => Str::slug($catName)],
                    [
                        'name' => $catName,
                        'description' => 'Artikel edukasi seputar ' . $catName,
                        'color' => '#007AFF',
                        'icon' => 'folder',
                        'is_active' => true,
                        'sort_order' => 99,
                    ]
                );
                $categoryMap[$catName] = $categoryObj;
            }

            // Pick cover image (if not specified in markdown, pick curated category image)
            $coverImage = $parsed['cover_image'] ?? null;
            if (empty($coverImage)) {
                $imgPool = self::CATEGORY_CONFIG[$catName]['images'] ?? [
                    'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80'
                ];
                $coverImage = $imgPool[$idx % count($imgPool)];
            }

            // Publication date spread over the past 100 days
            $publishedAt = (clone $now)->subDays($totalFiles - $idx)->setHour(8 + ($idx % 12))->setMinute(($idx * 7) % 60);

            // Starting organic views
            $viewsCount = 180 + (($idx * 17) % 650);

            Post::updateOrCreate(
                ['slug' => $parsed['slug']],
                [
                    'title' => $parsed['title'],
                    'slug' => $parsed['slug'],
                    'cluster' => $clusterCode,
                    'cluster_id' => $clusterId,
                    'category' => $catName,
                    'category_id' => $categoryObj->id,
                    'excerpt' => $parsed['excerpt'] ?? '',
                    'content' => $parsed['content'] ?? '',
                    'cover_image' => $coverImage,
                    'meta_title' => $parsed['meta_title'] ?? $parsed['title'],
                    'meta_description' => $parsed['meta_description'] ?? $parsed['excerpt'] ?? '',
                    'author_name' => $parsed['author'] ?? 'Tim Edukasi COOCA',
                    'views_count' => $viewsCount,
                    'is_published' => true,
                    'published_at' => $publishedAt,
                ]
            );

            $importedSlugs[] = $parsed['slug'];
            $importedCount++;
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info("Successfully imported {$importedCount} / {$totalFiles} articles into the database.");

        if ($this->option('force') && !empty($importedSlugs)) {
            $deletedStale = Post::whereNotIn('slug', $importedSlugs)->delete();
            if ($deletedStale > 0) {
                $this->info("Cleaned up {$deletedStale} stale/deprecated posts.");
            }
        }

        try {
            app(\App\Services\Seo\SitemapService::class)->saveBlogToPublic();
            $this->info("Regenerated public sitemap_blog.xml successfully.");
        } catch (\Throwable $e) {
            $this->warn("Sitemap regeneration notice: " . $e->getMessage());
        }

        return 0;
    }

    /**
     * Parse structured markdown article.
     */
    private function parseArticleContent(string $content, string $filename): array
    {
        $data = [];

        // 1. Support modern YAML frontmatter (used by cooca-blog-writer)
        if (preg_match('/^---\s*\r?\n(.*?)\r?\n---\s*\r?\n?(.*)$/s', $content, $fmMatches)) {
            $frontmatterRaw = $fmMatches[1];
            $body = trim($fmMatches[2]);

            $yaml = [];
            foreach (preg_split('/\r?\n/', $frontmatterRaw) as $line) {
                if (str_contains($line, ':')) {
                    [$k, $v] = explode(':', $line, 2);
                    $yaml[trim($k)] = trim(trim($v), "\"'");
                }
            }

            $data['title'] = $yaml['title'] ?? '';
            $data['slug'] = $yaml['slug'] ?? '';
            $data['category'] = $yaml['category'] ?? 'Umum';
            $data['cover_image'] = !empty($yaml['cover_image']) ? $yaml['cover_image'] : null;
            $data['meta_title'] = $yaml['meta_title'] ?? $data['title'];
            $data['meta_description'] = $yaml['meta_description'] ?? '';
            $data['author'] = $yaml['author'] ?? 'Tim Edukasi COOCA';
            $data['excerpt'] = $yaml['meta_description'] ?? '';

            // Strip any editorial note / internal link planning / raw JSON-LD block from public article body
            $body = preg_replace('/\r?\n##\s*Rencana\s+internal\s+link.*$/usi', '', $body);
            $body = preg_replace('/```json\s*\{.*?@context.*?```\s*$/usi', '', $body);
            $body = trim($body);

            // Convert Markdown body to rich, semantic HTML
            $data['content'] = Str::markdown($body);

            // Determine cluster (Tutorial / Edukasi)
            $isTutorial = Str::startsWith($data['title'], 'Cara ')
                || Str::contains($data['title'], 'SOP')
                || Str::contains($data['title'], 'Panduan')
                || in_array($data['category'], ['Kasir & POS', 'Invoice, PO & SO', 'Inventori & Stok', 'HPP & Harga Jual']);
            $data['cluster_raw'] = $isTutorial ? 'Cluster K - Tutorial Cara' : 'Cluster O - Edukasi Topikal';

            if (empty($data['slug'])) {
                $base = basename($filename, '.md');
                $data['slug'] = preg_replace('/^\d+-/', '', $base);
            }

            return $data;
        }

        // 2. Legacy fallback format
        if (preg_match('/\*\*Judul Artikel.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['title'] = trim($m[1]);
        }

        if (preg_match('/\*\*Cluster Konten.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['cluster_raw'] = trim($m[1]);
        }

        if (preg_match('/\*\*Kategori.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['category'] = trim($m[1]);
        }

        if (preg_match('/\*\*Nama Penulis.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['author'] = trim($m[1]);
        }

        if (preg_match('/\*\*URL Cover Image.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $val = trim($m[1]);
            if (!str_starts_with($val, '##') && !str_starts_with($val, '**') && !empty($val)) {
                $data['cover_image'] = $val;
            }
        }

        if (preg_match('/## Ringkasan \/ Excerpt\s*\r?\n+(.*?)(?=\r?\n## Konten Lengkap)/us', $content, $m)) {
            $data['excerpt'] = trim($m[1]);
        }

        if (preg_match('/## Konten Lengkap.*?\r?\n+(.*?)(?=\r?\n## Pengaturan SEO|$)/us', $content, $m)) {
            $data['content'] = trim($m[1]);
        }

        if (preg_match('/\*\*Meta Title.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['meta_title'] = trim($m[1]);
        }

        if (preg_match('/\*\*Meta Description.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['meta_description'] = trim($m[1]);
        }

        if (preg_match('/\*\*Meta Keyword.*?\*\*\s*\r?\n+([^\r\n]+)/u', $content, $m)) {
            $data['meta_keyword'] = trim($m[1]);
        }

        $base = basename($filename, '.md');
        $data['slug'] = preg_replace('/^\d+-/', '', $base);

        return $data;
    }
}
