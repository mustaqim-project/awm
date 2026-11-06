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
                'https://cooca.id/images/blog/photo-1501339847302-ac426a4a7cbb.jpg',
                'https://cooca.id/images/blog/photo-1554224155-8d04cb21cd6c.jpg',
            ],
        ],
        'Kasir & POS' => [
            'color' => '#007AFF',
            'icon' => 'monitor',
            'description' => 'Panduan aplikasi kasir POS, barcode scanner, printer struk, SOP kasir, dan pembayaran QRIS',
            'images' => [
                'https://cooca.id/images/blog/photo-1556740738-b6a63e27c4df.jpg',
                'https://cooca.id/images/blog/photo-1556742502-ec7c0e9f34b1.jpg',
            ],
        ],
        'Inventori & Stok' => [
            'color' => '#5856D6',
            'icon' => 'package',
            'description' => 'Manajemen persediaan barang, stok opname, safety stock, metode FIFO/FEFO, dan multi-gudang',
            'images' => [
                'https://cooca.id/images/blog/photo-1553413077-190dd305871c.jpg',
                'https://cooca.id/images/blog/photo-1578916171728-46686eac8d58.jpg',
            ],
        ],
        'Keuangan & Pembukuan' => [
            'color' => '#34C759',
            'icon' => 'trending-up',
            'description' => 'Laporan laba rugi, arus kas (cash flow), neraca sederhana, budgeting, dan pemisahan rekening',
            'images' => [
                'https://cooca.id/images/blog/photo-1554224154-26032ffc0d07.jpg',
                'https://cooca.id/images/blog/photo-1460925895917-afdab827c52f.jpg',
            ],
        ],
        'Pajak & Legalitas' => [
            'color' => '#FF2D55',
            'icon' => 'shield-check',
            'description' => 'Panduan izin NIB OSS, sertifikasi Halal, izin PIRT, NPWP usaha, dan pelaporan pajak UMKM',
            'images' => [
                'https://cooca.id/images/blog/photo-1486406146926-c627a92ad1ab.jpg',
                'https://cooca.id/images/blog/photo-1589829545856-d10d557cf95f.jpg',
            ],
        ],
        'Marketing & WhatsApp' => [
            'color' => '#25D366',
            'icon' => 'message-circle',
            'description' => 'Strategi WhatsApp Business, live TikTok Shop, content calendar, dan optimasi Google Maps',
            'images' => [
                'https://cooca.id/images/blog/photo-1611746872915-64382b5c76da.jpg',
                'https://cooca.id/images/blog/photo-1611162617474-5b21e879e113.jpg',
            ],
        ],
        'CRM & Loyalty' => [
            'color' => '#AF52DE',
            'icon' => 'heart',
            'description' => 'Program poin loyalitas pelanggan, membership tier, penanganan komplain, dan retensi pelanggan',
            'images' => [
                'https://cooca.id/images/blog/photo-1556740758-90de374c12ad.jpg',
                'https://cooca.id/images/blog/photo-1521791136064-7986c2920216.jpg',
            ],
        ],
        'Invoice, PO & SO' => [
            'color' => '#00C4D8',
            'icon' => 'file-text',
            'description' => 'Standar invoice profesional, purchase order (PO), sales order (SO), surat jalan, dan tempo net-30',
            'images' => [
                'https://cooca.id/images/blog/photo-1450133064473-71024230f91b.jpg',
                'https://cooca.id/images/blog/photo-1454165804606-c3d57bc86b40.jpg',
            ],
        ],
        'Solusi Per Industri' => [
            'color' => '#FF3B30',
            'icon' => 'store',
            'description' => 'Solusi operasional spesifik kafe, laundry kiloan, barbershop, salon, bengkel, toko kelontong, dan konveksi',
            'images' => [
                'https://cooca.id/images/blog/photo-1501339847302-ac426a4a7cbb.jpg',
                'https://cooca.id/images/blog/photo-1545173168-9f1947eebb7f.jpg',
            ],
        ],
        'Strategi & Operasional' => [
            'color' => '#5AC8FA',
            'icon' => 'compass',
            'description' => 'SOP operasional usaha, jadwal shift karyawan, KPI harian, target omzet, dan Business Operating System',
            'images' => [
                'https://cooca.id/images/blog/photo-1519389950473-47ba0277781c.jpg',
                'https://cooca.id/images/blog/photo-1460925895917-afdab827c52f.jpg',
            ],
        ],
        'Legalitas' => [
            'color' => '#FF2D55',
            'icon' => 'shield-check',
            'description' => 'Izin usaha OSS, NIB gratis, sertifikasi Halal self-declare, izin PIRT, dan perlindungan hukum UMKM',
            'images' => [
                'https://cooca.id/images/blog/photo-1450133064473-71024230f91b.jpg',
                'https://cooca.id/images/blog/photo-1589829545856-d10d557cf95f.jpg',
            ],
        ],
        'Modal' => [
            'color' => '#FF9500',
            'icon' => 'coins',
            'description' => 'Akses permodalan UMKM, simulasi pinjaman KUR 2026, bantuan pemerintah, leasing, dan crowdfunding',
            'images' => [
                'https://cooca.id/images/blog/photo-1553729459-efe14ef6055d.jpg',
                'https://cooca.id/images/blog/photo-1554224154-26032ffc0d07.jpg',
            ],
        ],
        'Operasional' => [
            'color' => '#5AC8FA',
            'icon' => 'settings',
            'description' => 'Manajemen operasional warung, SOP karyawan, shift kerja, supplier bahan baku, dan efisiensi harian',
            'images' => [
                'https://cooca.id/images/blog/photo-1556740758-90de374c12ad.jpg',
                'https://cooca.id/images/blog/photo-1586528116311-ad8dd3c8310d.jpg',
            ],
        ],
        'Marketing' => [
            'color' => '#AF52DE',
            'icon' => 'megaphone',
            'description' => 'Strategi video TikTok viral, Instagram Reels, foto produk HP, copywriting WhatsApp, dan Google Maps',
            'images' => [
                'https://cooca.id/images/blog/photo-1611162617474-5b21e879e113.jpg',
                'https://cooca.id/images/blog/photo-1611746872915-64382b5c76da.jpg',
            ],
        ],
        'Keuangan' => [
            'color' => '#34C759',
            'icon' => 'trending-up',
            'description' => 'Kalkulasi HPP kuliner, BEP warung, arus kas, pemisahan rekening, pajak UMKM, dan pembukuan praktis',
            'images' => [
                'https://cooca.id/images/blog/photo-1554224155-8d04cb21cd6c.jpg',
                'https://cooca.id/images/blog/photo-1460925895917-afdab827c52f.jpg',
            ],
        ],
        'Scale-up' => [
            'color' => '#007AFF',
            'icon' => 'rocket',
            'description' => 'Strategi ekspansi multi-cabang, implementasi AI UMKM, migrasi cloud software, dan otomatisasi bisnis',
            'images' => [
                'https://cooca.id/images/blog/photo-1486406146926-c627a92ad1ab.jpg',
                'https://cooca.id/images/blog/photo-1522071820081-009f0129c71c.jpg',
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
                    'https://cooca.id/images/blog/photo-1460925895917-afdab827c52f.jpg'
                ];
                $coverImage = $imgPool[$idx % count($imgPool)];
            }

            // Publication date spread over the past 100 days (or explicit from frontmatter/json)
            $publishedAt = !empty($parsed['published_at'])
                ? Carbon::parse($parsed['published_at'], 'Asia/Jakarta')
                : (clone $now)->subDays($totalFiles - $idx)->setHour(8 + ($idx % 12))->setMinute(($idx * 7) % 60);

            // Starting organic views
            $viewsCount = 180 + (($idx * 17) % 650);

            $postAttributes = [
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
            ];

            if (!empty($parsed['created_at'])) {
                $postAttributes['created_at'] = Carbon::parse($parsed['created_at'], 'Asia/Jakarta');
            }

            Post::updateOrCreate(
                ['slug' => $parsed['slug']],
                $postAttributes
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
            $data['published_at'] = $yaml['published_at'] ?? null;
            $data['created_at'] = $yaml['created_at'] ?? null;

            // Also check corresponding .json metadata file if exists
            $jsonFile = preg_replace('/\.md$/', '.json', $filename);
            if (file_exists($jsonFile)) {
                $metaJson = json_decode(file_get_contents($jsonFile), true);
                if (is_array($metaJson)) {
                    if (empty($data['published_at']) && !empty($metaJson['published_at'])) {
                        $data['published_at'] = $metaJson['published_at'];
                    }
                    if (empty($data['created_at']) && !empty($metaJson['created_at'])) {
                        $data['created_at'] = $metaJson['created_at'];
                    }
                }
            }

            // Strip any editorial note / internal link planning / raw JSON-LD block from public article body
            $body = preg_replace('/\r?\n##\s*Rencana\s+internal\s+link.*$/usi', '', $body);
            $body = preg_replace('/```json\s*\{.*?@context.*?```\s*$/usi', '', $body);
            $body = trim($body);

            // Strip leading H1 heading from markdown body so body does not repeat title H1
            $body = preg_replace('/^\s*#\s+[^\r\n]+\r?\n+/u', '', $body);
            // Convert any internal # Heading to ## Heading (H2) to guarantee single H1 per page
            $body = preg_replace('/^#\s+(.+)$/m', '## $1', $body);

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
            $legacyBody = trim($m[1]);
            $legacyBody = preg_replace('/^\s*#\s+[^\r\n]+\r?\n+/u', '', $legacyBody);
            $legacyBody = preg_replace('/^#\s+(.+)$/m', '## $1', $legacyBody);
            $data['content'] = $legacyBody;
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

        $jsonPath = preg_replace('/\.md$/i', '.json', $filename);
        if (file_exists($jsonPath)) {
            $jsonData = json_decode((string)file_get_contents($jsonPath), true);
            if (is_array($jsonData)) {
                if (!empty($jsonData['published_at']) && empty($data['published_at'])) {
                    $data['published_at'] = $jsonData['published_at'];
                }
                if (!empty($jsonData['created_at']) && empty($data['created_at'])) {
                    $data['created_at'] = $jsonData['created_at'];
                }
                if (!empty($jsonData['cover_image']) && empty($data['cover_image'])) {
                    $data['cover_image'] = $jsonData['cover_image'];
                }
            }
        }

        return $data;
    }
}
