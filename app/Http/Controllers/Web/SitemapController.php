<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Seo\SitemapService;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class SitemapController extends Controller
{
    public function __construct(
        private readonly SitemapService $sitemapService
    ) {}

    /**
     * Render the root XML sitemap index referencing all sub-sitemaps.
     */
    public function xml(): Response
    {
        $xml = $this->sitemapService->generateSitemapIndexXml();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, follow',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Render the pages XML sitemap (marketing, solution, omnichannel, resources).
     */
    public function pagesXml(): Response
    {
        $xml = $this->sitemapService->generatePagesXml();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, follow',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Render the dedicated blog XML sitemap (sitemap_blog.xml).
     */
    public function blogXml(): Response
    {
        $xml = $this->sitemapService->generateBlogXml();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, follow',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Render the marketplace XML sitemap (sitemap_marketplace.xml).
     */
    public function marketplaceXml(): Response
    {
        $xml = $this->sitemapService->generateMarketplaceXml();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, follow',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Render the tools XML sitemap (sitemap_tools.xml: calculators & templates).
     */
    public function toolsXml(): Response
    {
        $xml = $this->sitemapService->generateToolsXml();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, follow',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Render the human-friendly HTML sitemap page.
     */
    public function html(): View
    {
        $groupedUrls = $this->sitemapService->getGroupedUrls();
        $totalUrls = count($this->sitemapService->getPublicUrls());

        return view('public.sitemap', [
            'groupedUrls' => $groupedUrls,
            'totalUrls' => $totalUrls,
        ]);
    }
}
