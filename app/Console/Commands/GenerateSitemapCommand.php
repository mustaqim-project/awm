<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\SitemapService;
use Illuminate\Console\Command;

final class GenerateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {--path= : Path file output alternatif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap.xml statis ke folder public untuk performa SEO maksimal';

    /**
     * Execute the console command.
     */
    public function handle(SitemapService $sitemapService): int
    {
        $this->info('Memulai pembuatan sitemap XML split index untuk COOCA...');

        $files = $sitemapService->saveAllToPublic();

        $totalPages = count($sitemapService->getPagesUrls());
        $totalBlog = count($sitemapService->getBlogUrls());
        $totalMarketplace = count($sitemapService->getMarketplaceUrls());
        $totalTools = count($sitemapService->getToolsUrls());
        $totalAll = count($sitemapService->getPublicUrls());

        $this->info('✓ Berhasil membuat sitemap XML index dan sub-sitemaps!');
        $this->table(
            ['Sitemap', 'Path File', 'Total URL'],
            [
                ['Sitemap Index (Utama)', $files['index'], '4 sub-sitemaps'],
                ['Pages (Marketing & Solusi)', $files['pages'], (string) $totalPages],
                ['Blog (Hub & Artikel)', $files['blog'], (string) $totalBlog],
                ['Marketplace & Storefronts', $files['marketplace'], (string) $totalMarketplace],
                ['Tools (Kalkulator & Template)', $files['tools'], (string) $totalTools],
                ['TOTAL AGREGAT URL TERINDEKS', '-', (string) $totalAll],
            ]
        );

        return self::SUCCESS;
    }
}
