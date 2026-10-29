<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\SitemapService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class SeoCheckH1Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:check-h1
                            {--limit= : Batasi jumlah URL yang dicek (misal: --limit=50)}
                            {--url= : Cek URL atau path spesifik saja (misal: --url=/blog/cara-menghitung-hpp-kopi)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit semua URL publik di sitemap untuk memastikan setiap halaman memiliki tepat 1 tag <h1> dan 0 meta keywords';

    /**
     * Execute the console command.
     */
    public function handle(SitemapService $sitemapService): int
    {
        $this->info('Memulai audit H1 dan Meta Keywords untuk seluruh halaman publik COOCA...');

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

        $specificUrl = $this->option('url');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        if ($specificUrl) {
            $urls = [
                ['loc' => (string) $specificUrl, 'title' => 'URL Spesifik', 'category' => 'Manual'],
            ];
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

            $bar->setMessage(Str::limit($path, 40));

            try {
                $request = Request::create($path, 'GET');
                $response = app()->handle($request);
                $statusCode = $response->getStatusCode();

                // Skip 3xx redirect pages
                if ($statusCode >= 300 && $statusCode < 400) {
                    $bar->advance();
                    continue;
                }

                $content = $response->getContent();

                // 1. Check H1 count
                preg_match_all('/<h1\b[^>]*>(.*?)<\/h1>/si', $content, $h1Matches);
                $h1Count = count($h1Matches[0]);

                // 2. Check meta keywords presence
                $hasKeywords = (bool) preg_match('/<meta\s+name=[\x22\x27]keywords[\x22\x27]/i', $content);

                $h1Texts = array_map(fn($t) => trim(preg_replace('/\s+/', ' ', strip_tags($t))), $h1Matches[0]);

                if ($h1Count !== 1 || $hasKeywords || $statusCode !== 200) {
                    $statusReason = [];
                    if ($statusCode !== 200) {
                        $statusReason[] = "HTTP {$statusCode}";
                    }
                    if ($h1Count === 0) {
                        $statusReason[] = 'TIDAK ADA H1';
                    } elseif ($h1Count > 1) {
                        $statusReason[] = "DOBEL H1 ({$h1Count}x)";
                    }
                    if ($hasKeywords) {
                        $statusReason[] = 'META KEYWORDS TERDETEKSI';
                    }

                    $violations[] = [
                        'path' => $path,
                        'status' => $statusCode,
                        'h1_count' => $h1Count,
                        'h1_preview' => implode(' | ', array_map(fn($t) => Str::limit($t, 35), $h1Texts)),
                        'has_keywords' => $hasKeywords ? 'Ada (Bermasalah)' : 'Bersih',
                        'issue' => implode(', ', $statusReason),
                    ];
                } else {
                    $passedCount++;
                }
            } catch (\Throwable $e) {
                $violations[] = [
                    'path' => $path,
                    'status' => 500,
                    'h1_count' => 0,
                    'h1_preview' => '-',
                    'has_keywords' => 'Error',
                    'issue' => 'EXCEPTION: ' . Str::limit($e->getMessage(), 40),
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if (count($violations) === 0) {
            $this->info("✓ Sempurna! Seluruh {$total} URL yang diaudit memiliki tepat 1 <h1> dan 0 <meta name=\"keywords\">.");
            $this->table(
                ['Metrik Audit', 'Hasil'],
                [
                    ['Total Halaman Publik Diaudit', (string) $total],
                    ['Halaman Lolos (Tepat 1 H1 & Tanpa Meta Keywords)', (string) $passedCount],
                    ['Halaman Bermasalah', '0 (NOL)'],
                    ['Status Kepatuhan SEO', '100% COMPLIANT'],
                ]
            );

            return self::SUCCESS;
        }

        $this->error("Ditemukan " . count($violations) . " halaman dengan masalah H1 atau Meta Keywords:");
        $this->table(
            ['Path', 'HTTP', 'Jumlah H1', 'Teks H1', 'Keywords', 'Masalah'],
            $violations
        );

        return self::FAILURE;
    }
}
