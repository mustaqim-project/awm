<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class SeoBlogReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:blog-report
                            {--output= : Path file CSV output laporan}
                            {--draft= : Path file draf usulan perbaikan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ekspor laporan CSV status SEO blog (title, meta description, panjang karakter, jumlah kata) dan buat draf usulan perbaikan';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Menganalisa seluruh artikel blog untuk audit SEO Prompt 3...');

        $posts = Post::orderBy('id')->get();
        if ($posts->isEmpty()) {
            $this->warn('Tidak ada artikel blog di database.');
            return self::SUCCESS;
        }

        $reportRows = [];
        $proposals = [];
        $statusCounts = [
            'OK' => 0,
            'title kepanjangan' => 0,
            'description terpotong' => 0,
            'description pendek' => 0,
            'description kepanjangan' => 0,
        ];

        foreach ($posts as $post) {
            $slug = (string) $post->slug;
            $seoTitle = (string) $post->resolved_seo_title;
            $titleLen = mb_strlen($seoTitle);

            $metaDesc = (string) $post->resolved_meta_description;
            $descLen = mb_strlen($metaDesc);

            $cleanBody = strip_tags((string) $post->content);
            $wordCount = count(preg_split('/\s+/u', trim($cleanBody), -1, PREG_SPLIT_NO_EMPTY));

            $statuses = [];
            if ($titleLen > 60) {
                $statuses[] = 'title kepanjangan';
                $statusCounts['title kepanjangan']++;
            }
            if (str_ends_with($metaDesc, '...') || str_ends_with($metaDesc, '…')) {
                $statuses[] = 'description terpotong';
                $statusCounts['description terpotong']++;
            }
            if ($descLen < 120) {
                $statuses[] = 'description pendek';
                $statusCounts['description pendek']++;
            } elseif ($descLen > 160) {
                $statuses[] = 'description kepanjangan';
                $statusCounts['description kepanjangan']++;
            }

            $statusText = empty($statuses) ? 'OK' : implode('; ', $statuses);
            if (empty($statuses)) {
                $statusCounts['OK']++;
            }

            $reportRows[] = [
                'slug' => $slug,
                'title' => $seoTitle,
                'title_length' => $titleLen,
                'meta_description' => $metaDesc,
                'meta_description_length' => $descLen,
                'word_count' => $wordCount,
                'status' => $statusText,
            ];

            // If problematic, build optimized proposal draft
            if (!empty($statuses)) {
                $proposals[] = $this->generateProposal($post, $statuses);
            }
        }

        // 1. Export Report CSV
        $defaultCsvPath = storage_path('app/seo_blog_report.csv');
        $csvPath = $this->option('output') ?: $defaultCsvPath;
        $dir = dirname($csvPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fp = fopen($csvPath, 'w');
        fputcsv($fp, ['slug', 'title', 'panjang_title', 'meta_description', 'panjang_description', 'jumlah_kata', 'status']);
        foreach ($reportRows as $row) {
            fputcsv($fp, [
                $row['slug'],
                $row['title'],
                $row['title_length'],
                $row['meta_description'],
                $row['meta_description_length'],
                $row['word_count'],
                $row['status'],
            ]);
        }
        fclose($fp);

        // 2. Export Proposal Drafts (JSON & CSV)
        $defaultDraftPath = storage_path('app/seo_blog_proposals_draft.json');
        $draftPath = $this->option('draft') ?: $defaultDraftPath;
        file_put_contents($draftPath, json_encode($proposals, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $draftCsvPath = preg_replace('/\.json$/i', '.csv', $draftPath);
        $fpDraft = fopen($draftCsvPath, 'w');
        fputcsv($fpDraft, ['slug', 'status_awal', 'title_awal', 'usulan_seo_title', 'panjang_usulan_title', 'usulan_meta_description', 'panjang_usulan_desc']);
        foreach ($proposals as $prop) {
            fputcsv($fpDraft, [
                $prop['slug'],
                implode('; ', $prop['issues']),
                $prop['current_title'],
                $prop['proposed_seo_title'],
                mb_strlen($prop['proposed_seo_title']),
                $prop['proposed_meta_description'],
                mb_strlen($prop['proposed_meta_description']),
            ]);
        }
        fclose($fpDraft);

        $this->info("✓ Berhasil mengekspor laporan SEO Blog ke: {$csvPath}");
        $this->info("✓ Berhasil menyimpan draf usulan perbaikan ke: {$draftPath}");

        $this->table(
            ['Metrik Analisa Blog', 'Jumlah Artikel'],
            [
                ['Total Artikel Dianalisa', (string) count($posts)],
                ['Status OK (Patuh Penuh)', (string) $statusCounts['OK']],
                ['Title Kepanjangan (>60 karakter)', (string) $statusCounts['title kepanjangan']],
                ['Meta Description Terpotong (...)', (string) $statusCounts['description terpotong']],
                ['Meta Description Terlalu Pendek (<120 karakter)', (string) $statusCounts['description pendek']],
                ['Meta Description Terlalu Panjang (>160 karakter)', (string) $statusCounts['description kepanjangan']],
                ['Total Usulan Perbaikan Disiapkan di Draf', (string) count($proposals)],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Generate an optimized proposal for problematic blog articles.
     *
     * @param array<string> $issues
     * @return array<string, mixed>
     */
    private function generateProposal(Post $post, array $issues): array
    {
        $currentTitle = (string) $post->resolved_seo_title;
        $title = (string) $post->title;

        // 1. Proposed Title (Ensure <= 60 chars)
        $proposedTitle = $title;
        if (mb_strlen($proposedTitle) > 60) {
            // Remove common conversational preambles
            $short = preg_replace('/^(cara|tips|panduan|langkah|rahasia|strategi)\s+/i', '', $proposedTitle);
            $short = preg_replace('/\s+untuk\s+umkm.*$/i', ' untuk UMKM', $short);
            $short = preg_replace('/\s+dalam\s+\d+\s+menit.*$/i', '', $short);
            $proposedTitle = Str::limit($short, 50, '');
        }

        if (mb_strlen($proposedTitle) <= 52) {
            $proposedTitle = rtrim($proposedTitle) . ' | COOCA';
        }

        if (mb_strlen($proposedTitle) > 60) {
            $proposedTitle = Str::limit($proposedTitle, 60, '');
        }

        // 2. Proposed Meta Description (120 - 158 chars, ending with dot, no ellipsis)
        $excerpt = strip_tags((string) ($post->excerpt ?: $post->content));
        $excerpt = html_entity_decode($excerpt, ENT_QUOTES, 'UTF-8');
        $excerpt = trim((string) preg_replace('/\s+/', ' ', $excerpt));

        if (mb_strlen($excerpt) < 120) {
            // Expand with helpful context
            $excerpt .= " Pelajari panduan praktis dan langkah operasional selengkapnya di COOCA.";
        }

        if (mb_strlen($excerpt) > 155) {
            $sub = mb_substr($excerpt, 0, 155);
            $lastSpace = mb_strrpos($sub, ' ');
            if ($lastSpace !== false && $lastSpace > 90) {
                $sub = mb_substr($sub, 0, $lastSpace);
            }
            $excerpt = rtrim(trim($sub), ",;:-.…") . '.';
        } else {
            $excerpt = rtrim($excerpt, ",;:-.…") . '.';
        }

        return [
            'slug' => (string) $post->slug,
            'issues' => $issues,
            'current_title' => $currentTitle,
            'proposed_seo_title' => $proposedTitle,
            'proposed_meta_description' => $excerpt,
        ];
    }
}

