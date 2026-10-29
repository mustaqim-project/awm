<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\SitemapService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class SeoAuditCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:audit
                            {--limit= : Batasi jumlah URL yang diaudit}
                            {--url= : Audit URL atau path spesifik saja}
                            {--core-only : Hanya audit 18 halaman inti yang dikonfigurasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit kepatuhan SEO (Title <= 60 karakter, Meta Description 120-160 karakter, H1 tunggal) untuk seluruh URL di sitemap';

    /**
     * Execute the console command.
     */
    public function handle(SitemapService $sitemapService): int
    {
        $this->info('Memulai Audit SEO On-Page (Title, Meta Description, H1) COOCA...');

        // Bypass HTTP throttle middleware for internal CLI batch audit
        app()->bind(\Illuminate\Routing\Middleware\ThrottleRequests::class, function () {
            return new class {
                public function handle($request, \Closure $next) {
                    return $next($request);
                }
            };
        });
        app()->bind(\Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class, function () {
            return new class {
                public function handle($request, \Closure $next) {
                    return $next($request);
                }
            };
        });

        $coreOnly = (bool) $this->option('core-only');
        $specificUrl = $this->option('url');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        if ($specificUrl) {
            $urls = [['loc' => (string) $specificUrl, 'title' => 'URL Spesifik']];
        } elseif ($coreOnly) {
            $baseUrl = $sitemapService->getBaseUrl();
            $corePaths = array_keys(config('seo.pages', []));
            $urls = array_map(fn($p) => ['loc' => $baseUrl . $p, 'title' => 'Halaman Inti'], $corePaths);
        } else {
            $urls = $sitemapService->getPublicUrls();
            if ($limit !== null && $limit > 0) {
                $urls = array_slice($urls, 0, $limit);
            }
        }

        $total = count($urls);
        $this->line("Ditemukan {$total} URL untuk diaudit.\n");

        $violations = [];
        $passedCount = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $bar->setMessage('Memulai...');
        $bar->start();

        foreach ($urls as $item) {
            $loc = $item['loc'];
            $path = parse_url($loc, PHP_URL_PATH) ?: '/';
            $bar->setMessage(Str::limit($path, 35));

            try {
                $request = Request::create($path, 'GET');
                $response = app()->handle($request);
                $statusCode = $response->getStatusCode();

                if ($statusCode >= 300 && $statusCode < 400) {
                    $bar->advance();
                    continue;
                }

                $content = $response->getContent();

                // 1. Title
                preg_match('/<title>(.*?)<\/title>/si', $content, $titleMatches);
                $rawTitle = html_entity_decode(strip_tags($titleMatches[1] ?? ''), ENT_QUOTES, 'UTF-8');
                $rawTitle = trim((string) preg_replace('/\s+/', ' ', $rawTitle));
                $titleLen = mb_strlen($rawTitle);

                // 2. Meta Description
                preg_match('/<meta\s+name=[\x22\x27]description[\x22\x27]\s+content=[\x22\x27](.*?)[\x22\x27]/si', $content, $descMatches);
                $rawDesc = html_entity_decode(strip_tags($descMatches[1] ?? ''), ENT_QUOTES, 'UTF-8');
                $rawDesc = trim((string) preg_replace('/\s+/', ' ', $rawDesc));
                $descLen = mb_strlen($rawDesc);

                // 3. H1 count
                preg_match_all('/<h1\b[^>]*>(.*?)<\/h1>/si', $content, $h1Matches);
                $h1Count = count($h1Matches[0]);

                $issues = [];
                if ($statusCode !== 200) {
                    $issues[] = "HTTP {$statusCode}";
                }
                if ($titleLen === 0) {
                    $issues[] = 'TITLE KOSONG';
                } elseif ($titleLen > 60) {
                    $issues[] = "TITLE KEPANJANGAN ({$titleLen} > 60)";
                }

                if ($descLen === 0) {
                    $issues[] = 'DESC KOSONG';
                } elseif ($descLen < 120) {
                    $issues[] = "DESC TERLALU PENDEK ({$descLen} < 120)";
                } elseif ($descLen > 160) {
                    $issues[] = "DESC TERLALU PANJANG ({$descLen} > 160)";
                }

                if (str_ends_with($rawDesc, '...') || str_ends_with($rawDesc, '…')) {
                    $issues[] = 'DESC TERPOTONG (...)';
                }

                if ($h1Count !== 1) {
                    $issues[] = $h1Count === 0 ? 'TIDAK ADA H1' : "H1 GANDA ({$h1Count}x)";
                }

                if (!empty($issues)) {
                    $violations[] = [
                        'path' => Str::limit($path, 32),
                        'title_len' => $titleLen,
                        'desc_len' => $descLen,
                        'h1_count' => $h1Count,
                        'issues' => implode('; ', $issues),
                    ];
                } else {
                    $passedCount++;
                }
            } catch (\Throwable $e) {
                $violations[] = [
                    'path' => Str::limit($path, 32),
                    'title_len' => 0,
                    'desc_len' => 0,
                    'h1_count' => 0,
                    'issues' => 'EXCEPTION: ' . Str::limit($e->getMessage(), 40),
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metrik Audit SEO', 'Hasil'],
            [
                ['Total Halaman Publik Diperiksa', (string) $total],
                ['Halaman Lolos 100% (Title <= 60, Desc 120-160, H1 = 1)', (string) $passedCount],
                ['Halaman Belum Patuh', (string) count($violations)],
            ]
        );

        if (count($violations) === 0) {
            $this->info("✓ SEMPURNA! Seluruh {$total} URL yang diaudit 100% mematuhi aturan SEO.");
            return self::SUCCESS;
        }

        $this->warn("Terdapat " . count($violations) . " halaman dengan catatan audit:");
        $this->table(
            ['Path', 'Title Len', 'Desc Len', 'H1 Count', 'Catatan Masalah'],
            array_slice($violations, 0, 50)
        );

        return self::FAILURE;
    }
}

