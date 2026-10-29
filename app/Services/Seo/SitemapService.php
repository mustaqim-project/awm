<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BusinessLandingPage;
use App\Models\CommerceStoreSetting;
use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class SitemapService
{
    /**
     * Vertical solution definitions (synchronized with PublicSolutionController).
     */
    private const SOLUTIONS = [
        'kasir-warung' => ['name' => 'Aplikasi Kasir Warung & Toko Kelontong', 'view' => 'public.solutions.show'],
        'kasir-cafe-kecil' => ['name' => 'Aplikasi Kasir Cafe & Kedai Kopi', 'view' => 'public.solutions.show'],
        'kasir-kios' => ['name' => 'Aplikasi Kasir Konter HP & Pulsa', 'view' => 'public.solutions.show'],
        'kasir-salon' => ['name' => 'Aplikasi Kasir Salon & Spa Kecantikan', 'view' => 'public.solutions.show'],
        'kasir-barbershop' => ['name' => 'Aplikasi Kasir Barbershop & Pangkas Rambut', 'view' => 'public.solutions.show'],
        'kasir-irt' => ['name' => 'Aplikasi Usaha Rumahan & Produsen Snack', 'view' => 'public.solutions.show'],
        'kasir-apotek' => ['name' => 'Aplikasi Kasir Apotek & Toko Obat', 'view' => 'public.solutions.show'],
        'aplikasi-catering' => ['name' => 'Aplikasi Catering & Usaha Prasmanan', 'view' => 'public.solutions.show'],
    ];

    /**
     * Core public marketing, solutions, and resource pages.
     */
    private const CORE_PAGES = [
        '/' => ['name' => 'Beranda Cooca', 'category' => 'Halaman Utama', 'priority' => '1.0', 'changefreq' => 'daily', 'view' => 'landing'],
        '/pricing' => ['name' => 'Harga Aplikasi Kasir & ERP UMKM', 'category' => 'Harga & Paket', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.pricing'],
        '/about' => ['name' => 'Tentang Kami & Misi UMKM Cooca', 'category' => 'Tentang Kami', 'priority' => '0.8', 'changefreq' => 'monthly', 'view' => 'public.about'],
        '/demo' => ['name' => 'Jadwal Demo & Uji Coba Gratis', 'category' => 'Demo Produk', 'priority' => '0.8', 'changefreq' => 'weekly', 'view' => 'public.demo'],
        '/support' => ['name' => 'Pusat Bantuan & Layanan Konsumen', 'category' => 'Bantuan & Support', 'priority' => '0.8', 'changefreq' => 'monthly', 'view' => 'public.support'],
        '/kontak' => ['name' => 'Hubungi Tim Cooca', 'category' => 'Informasi', 'priority' => '0.7', 'changefreq' => 'monthly', 'view' => 'public.contact.index'],
        '/terms' => ['name' => 'Syarat & Ketentuan Layanan', 'category' => 'Legalitas', 'priority' => '0.5', 'changefreq' => 'monthly', 'view' => 'public.terms'],
        '/privacy' => ['name' => 'Kebijakan Privasi & Perlindungan Data', 'category' => 'Legalitas', 'priority' => '0.5', 'changefreq' => 'monthly', 'view' => 'public.privacy'],
        '/sitemap' => ['name' => 'Peta Situs (HTML Sitemap)', 'category' => 'Informasi', 'priority' => '0.6', 'changefreq' => 'weekly', 'view' => 'public.sitemap'],
        '/aplikasi-kasir-gratis' => ['name' => 'Aplikasi Kasir Gratis Selamanya', 'category' => 'Solusi Kasir', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.landing_commercial.aplikasi_kasir_gratis'],
        '/aplikasi-kasir-android' => ['name' => 'Aplikasi Kasir Android HP & Tablet', 'category' => 'Solusi Kasir', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.landing_commercial.aplikasi_kasir_android'],
        '/aplikasi-kasir-offline' => ['name' => 'Aplikasi Kasir Offline Tanpa Internet', 'category' => 'Solusi Kasir', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.landing_commercial.aplikasi_kasir_offline'],
        '/solutions/fnb' => ['name' => 'Aplikasi Kasir Restoran & Cafe', 'category' => 'Solusi Industri', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.solutions.fnb'],
        '/solutions/retail' => ['name' => 'Aplikasi Kasir Toko & Minimarket', 'category' => 'Solusi Industri', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.solutions.retail'],
        '/solutions/workshop' => ['name' => 'Aplikasi Bengkel Motor & Mobil', 'category' => 'Solusi Industri', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.solutions.workshop'],
        '/solutions/laundry' => ['name' => 'Aplikasi Kasir Laundry Kiloan', 'category' => 'Solusi Industri', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.solutions.laundry'],
        '/solutions/manufacturing' => ['name' => 'Software Produksi & BOM UMKM', 'category' => 'Solusi Industri', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.solutions.manufacturing'],
        '/solutions/services' => ['name' => 'Aplikasi Booking & Invoice Jasa', 'category' => 'Solusi Industri', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.solutions.services'],
        '/omnichannel-erp/pos' => ['name' => 'Aplikasi POS Kasir Online', 'category' => 'Fitur Produk', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.omnichannel-erp.pos'],
        '/omnichannel-erp/inventory' => ['name' => 'Aplikasi Stok Barang & Gudang', 'category' => 'Fitur Produk', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.omnichannel-erp.inventory'],
        '/omnichannel-erp/finance' => ['name' => 'Aplikasi Pembukuan & Keuangan', 'category' => 'Fitur Produk', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.omnichannel-erp.finance'],
        '/omnichannel-erp/erp' => ['name' => 'ERP UMKM Terintegrasi', 'category' => 'Fitur Produk', 'priority' => '0.9', 'changefreq' => 'weekly', 'view' => 'public.omnichannel-erp.erp'],
        '/omnichannel-erp/analytics' => ['name' => 'Asisten AI & Analisa Penjualan', 'category' => 'Fitur Produk', 'priority' => '0.8', 'changefreq' => 'weekly', 'view' => 'public.omnichannel-erp.analytics'],
        '/omnichannel/whatsapp' => ['name' => 'WhatsApp Multi-Agent & Chatbot', 'category' => 'Fitur Produk', 'priority' => '0.8', 'changefreq' => 'weekly', 'view' => 'public.omnichannel.whatsapp'],
        '/omnichannel/customer' => ['name' => 'CRM & Loyalitas Pelanggan', 'category' => 'Fitur Produk', 'priority' => '0.8', 'changefreq' => 'weekly', 'view' => 'public.omnichannel.customer'],
        '/omnichannel/marketplace' => ['name' => 'Integrasi Marketplace Shopee & Tokopedia', 'category' => 'Fitur Produk', 'priority' => '0.8', 'changefreq' => 'weekly', 'view' => 'public.omnichannel.marketplace'],
        '/content-automation/content-creation' => ['name' => 'Auto-Post & Konten AI UMKM', 'category' => 'Fitur Produk', 'priority' => '0.8', 'changefreq' => 'weekly', 'view' => 'public.content-automation.content-creation'],
        '/resources/case-studies' => ['name' => 'Contoh Skenario UMKM & Studi Kasus', 'category' => 'Pusat Edukasi', 'priority' => '0.8', 'changefreq' => 'weekly', 'view' => 'public.resources.case-studies'],
        '/resources/guides' => ['name' => 'Panduan Operasional & Panduan UMKM', 'category' => 'Pusat Edukasi', 'priority' => '0.8', 'changefreq' => 'weekly', 'view' => 'public.resources.guides'],
        '/resources/faq' => ['name' => 'Pertanyaan Umum (FAQ) Cooca', 'category' => 'Pusat Edukasi', 'priority' => '0.8', 'changefreq' => 'weekly', 'view' => 'public.resources.faq'],
    ];

    /**
     * Free lead capture template definitions.
     */
    private const TEMPLATES = [
        'pembukuan-warung-excel' => 'Template Pembukuan Warung & Toko Kelontong (Excel)',
        'laporan-keuangan-sederhana' => 'Template Laporan Keuangan Sederhana UMKM',
        'stok-opname-excel' => 'Template Stok Opname & Kartu Persediaan Barang',
        'invoice-sederhana' => 'Template Invoice & Nota Pembayaran Sederhana',
    ];

    /**
     * Business calculators.
     */
    private const CALCULATORS = [
        'hpp' => ['name' => 'Kalkulator HPP & Food Costing', 'path' => '/kalkulator/hpp', 'priority' => '0.9', 'view' => 'public.calculators.hpp'],
        'bep' => ['name' => 'Kalkulator Break Even Point (BEP)', 'path' => '/kalkulator/bep', 'priority' => '0.9', 'view' => 'public.calculators.bep'],
        'harga-jual' => ['name' => 'Kalkulator Harga Jual & Margin Laba', 'path' => '/kalkulator/harga-jual', 'priority' => '0.9', 'view' => 'public.calculators.harga-jual'],
        'laba-bersih' => ['name' => 'Kalkulator Laba Bersih & Arus Kas', 'path' => '/kalkulator/laba-bersih', 'priority' => '0.8', 'view' => 'public.calculators.laba-bersih'],
        'gaji-karyawan' => ['name' => 'Kalkulator Gaji & Upah Lembur Karyawan', 'path' => '/kalkulator/gaji-karyawan', 'priority' => '0.8', 'view' => 'public.calculators.gaji-karyawan'],
        'pph-final' => ['name' => 'Kalkulator Pajak PPh Final UMKM 0.5%', 'path' => '/kalkulator/pph-final', 'priority' => '0.8', 'view' => 'public.calculators.pph-final'],
        'omzet-harian' => ['name' => 'Kalkulator Target Omzet Harian', 'path' => '/kalkulator/omzet-harian', 'priority' => '0.8', 'view' => 'public.calculators.omzet-harian'],
        'simulasi-what-if' => ['name' => 'Simulator Skenario Bisnis What-If', 'path' => '/kalkulator/simulasi-what-if', 'priority' => '0.8', 'view' => 'public.calculators.simulasi-what-if'],
    ];

    /**
     * Resolve the canonical production base URL (strictly HTTPS).
     */
    public function getBaseUrl(): string
    {
        $canonical = config('app.canonical_url');
        if (!empty($canonical) && !str_contains($canonical, '127.0.0.1') && !str_contains($canonical, 'localhost')) {
            return preg_replace('/^http:/i', 'https:', rtrim((string) $canonical, '/'));
        }

        $appUrl = (string) config('app.url', '');
        if (!empty($appUrl) && !str_contains($appUrl, '127.0.0.1') && !str_contains($appUrl, 'localhost')) {
            return preg_replace('/^http:/i', 'https:', rtrim($appUrl, '/'));
        }

        return 'https://cooca.id';
    }

    /**
     * Get real content modification timestamp for a blade view file.
     */
    private function getViewLastmod(string $viewName): string
    {
        try {
            $viewPath = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');
            if (file_exists($viewPath)) {
                return Carbon::createFromTimestamp((int) filemtime($viewPath))->toIso8601String();
            }
        } catch (\Throwable) {
        }

        return Carbon::now()->subDays(1)->toIso8601String();
    }

    /**
     * Compile public marketing, solution, omnichannel, and resource URLs (sitemap_pages.xml).
     *
     * @return array<int, array{loc: string, lastmod: string, changefreq: string, priority: string, category: string, title: string}>
     */
    public function getPagesUrls(): array
    {
        $baseUrl = $this->getBaseUrl();
        $urls = [];

        foreach (self::CORE_PAGES as $path => $page) {
            $urls[] = [
                'loc' => $baseUrl . ($path === '/' ? '' : $path),
                'lastmod' => $this->getViewLastmod($page['view']),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
                'category' => $page['category'],
                'title' => $page['name'],
            ];
        }

        foreach (self::SOLUTIONS as $slug => $data) {
            $urls[] = [
                'loc' => $baseUrl . '/solusi/' . $slug,
                'lastmod' => $this->getViewLastmod($data['view']),
                'changefreq' => 'weekly',
                'priority' => '0.9',
                'category' => 'Solusi Industri',
                'title' => $data['name'],
            ];
        }

        return $urls;
    }

    /**
     * Compile all public blog URLs (sitemap_blog.xml).
     *
     * @return array<int, array{loc: string, lastmod: string, changefreq: string, priority: string, category: string, title: string}>
     */
    public function getBlogUrls(): array
    {
        $baseUrl = $this->getBaseUrl();
        $urls = [];

        $blogHubLastmod = $this->getViewLastmod('public.blog.index');

        $urls[] = [
            'loc' => $baseUrl . '/blog',
            'lastmod' => $blogHubLastmod,
            'changefreq' => 'daily',
            'priority' => '0.9',
            'category' => 'Edukasi & Blog',
            'title' => 'Pusat Panduan & Edukasi Bisnis UMKM',
        ];

        try {
            $posts = Post::published()->orderByDesc('published_at')->get();
            foreach ($posts as $post) {
                $postDate = ($post->updated_at ?? $post->published_at ?? Carbon::now())->toIso8601String();
                $urls[] = [
                    'loc' => $baseUrl . '/blog/' . $post->slug,
                    'lastmod' => $postDate,
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                    'category' => 'Artikel & Tutorial',
                    'title' => (string) $post->title,
                ];
            }
        } catch (\Throwable) {
        }

        return $urls;
    }

    /**
     * Compile all public marketplace & discoverable merchant storefront URLs (sitemap_marketplace.xml).
     *
     * @return array<int, array{loc: string, lastmod: string, changefreq: string, priority: string, category: string, title: string}>
     */
    public function getMarketplaceUrls(): array
    {
        $baseUrl = $this->getBaseUrl();
        $urls = [];

        $urls[] = [
            'loc' => $baseUrl . '/marketplace',
            'lastmod' => $this->getViewLastmod('public.marketplace.index'),
            'changefreq' => 'daily',
            'priority' => '0.9',
            'category' => 'Marketplace',
            'title' => 'Direktori Merchant & Produk Unggulan UMKM',
        ];

        $urls[] = [
            'loc' => $baseUrl . '/marketplace/businesses',
            'lastmod' => $this->getViewLastmod('public.marketplace.businesses'),
            'changefreq' => 'daily',
            'priority' => '0.8',
            'category' => 'Marketplace',
            'title' => 'Daftar Toko & Merchant UMKM Terpercaya',
        ];

        $urls[] = [
            'loc' => $baseUrl . '/marketplace/products',
            'lastmod' => $this->getViewLastmod('public.marketplace.products'),
            'changefreq' => 'daily',
            'priority' => '0.8',
            'category' => 'Marketplace',
            'title' => 'Katalog Produk & Penawaran UMKM Terkini',
        ];

        $urls[] = [
            'loc' => $baseUrl . '/marketplace/categories',
            'lastmod' => $this->getViewLastmod('public.marketplace.categories'),
            'changefreq' => 'weekly',
            'priority' => '0.7',
            'category' => 'Marketplace',
            'title' => 'Kategori Industri & Bisnis UMKM Indonesia',
        ];

        $urls[] = [
            'loc' => $baseUrl . '/marketplace/locations',
            'lastmod' => $this->getViewLastmod('public.marketplace.locations'),
            'changefreq' => 'weekly',
            'priority' => '0.7',
            'category' => 'Marketplace',
            'title' => 'Lokasi Usaha & Merchant UMKM per Kota',
        ];

        $urls[] = [
            'loc' => $baseUrl . '/jelajah',
            'lastmod' => $this->getViewLastmod('public.discovery.index'),
            'changefreq' => 'daily',
            'priority' => '0.8',
            'category' => 'Direktori UMKM',
            'title' => 'Direktori & Jelajah Toko Online UMKM',
        ];

        try {
            $seenBusinessSlugs = [];

            $businessLandings = BusinessLandingPage::where('is_published', true)
                ->whereHas('business', fn($q) => $q->publicListed())
                ->with('business')
                ->get();

            foreach ($businessLandings as $landing) {
                if ($landing->business && ($landing->business->slug || $landing->business->name)) {
                    $slug = $landing->business->slug ?: Str::slug($landing->business->name);
                    $seenBusinessSlugs[$slug] = true;
                    $landingDate = ($landing->updated_at ?? Carbon::now())->toIso8601String();
                    $urls[] = [
                        'loc' => $baseUrl . '/' . $slug,
                        'lastmod' => $landingDate,
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                        'category' => 'Toko & Etalase UMKM',
                        'title' => (string) ($landing->headline ?: $landing->business->name),
                    ];
                }
            }

            $discoverableStores = CommerceStoreSetting::where('is_storefront_enabled', true)
                ->where('is_discoverable', true)
                ->whereHas('business', fn($q) => $q->publicListed())
                ->with('business')
                ->get();

            foreach ($discoverableStores as $setting) {
                if ($setting->business && $setting->business->slug) {
                    $slug = $setting->business->slug;
                    if (isset($seenBusinessSlugs[$slug])) {
                        continue;
                    }
                    $seenBusinessSlugs[$slug] = true;
                    $urls[] = [
                        'loc' => $baseUrl . '/' . $slug,
                        'lastmod' => ($setting->updated_at ?? Carbon::now())->toIso8601String(),
                        'changefreq' => 'daily',
                        'priority' => '0.8',
                        'category' => 'Toko & Etalase UMKM',
                        'title' => (string) $setting->business->name,
                    ];
                }
            }
        } catch (\Throwable) {
        }

        return $urls;
    }

    /**
     * Compile all public calculators and spreadsheet templates (sitemap_tools.xml).
     *
     * @return array<int, array{loc: string, lastmod: string, changefreq: string, priority: string, category: string, title: string}>
     */
    public function getToolsUrls(): array
    {
        $baseUrl = $this->getBaseUrl();
        $urls = [];

        $urls[] = [
            'loc' => $baseUrl . '/kalkulator',
            'lastmod' => $this->getViewLastmod('public.calculators.index'),
            'changefreq' => 'weekly',
            'priority' => '0.9',
            'category' => 'Kalkulator Bisnis',
            'title' => 'Pusat Kalkulator & Simulasi Keuangan UMKM',
        ];

        foreach (self::CALCULATORS as $calc) {
            $urls[] = [
                'loc' => $baseUrl . $calc['path'],
                'lastmod' => $this->getViewLastmod($calc['view']),
                'changefreq' => 'weekly',
                'priority' => $calc['priority'],
                'category' => 'Kalkulator Bisnis',
                'title' => $calc['name'],
            ];
        }

        $urls[] = [
            'loc' => $baseUrl . '/template-pembukuan-gratis',
            'lastmod' => $this->getViewLastmod('public.templates.index'),
            'changefreq' => 'weekly',
            'priority' => '0.9',
            'category' => 'Template Gratis',
            'title' => 'Download Template Pembukuan & Excel Gratis',
        ];

        foreach (self::TEMPLATES as $slug => $title) {
            $urls[] = [
                'loc' => $baseUrl . '/template/' . $slug,
                'lastmod' => $this->getViewLastmod('public.templates.show'),
                'changefreq' => 'monthly',
                'priority' => '0.8',
                'category' => 'Template Gratis',
                'title' => $title,
            ];
        }

        return $urls;
    }

    /**
     * Compile all public indexable URLs with metadata (aggregated for HTML sitemap).
     *
     * @return array<int, array{loc: string, lastmod: string, changefreq: string, priority: string, category: string, title: string}>
     */
    public function getPublicUrls(): array
    {
        return array_merge(
            $this->getPagesUrls(),
            $this->getBlogUrls(),
            $this->getMarketplaceUrls(),
            $this->getToolsUrls()
        );
    }

    /**
     * Render the root Sitemap Index XML (<sitemapindex>).
     */
    public function generateSitemapIndexXml(): string
    {
        $baseUrl = $this->getBaseUrl();
        $nowDate = Carbon::now()->toIso8601String();

        $subSitemaps = [
            '/sitemap_pages.xml',
            '/sitemap_blog.xml',
            '/sitemap_marketplace.xml',
            '/sitemap_tools.xml',
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($subSitemaps as $path) {
            $loc = htmlspecialchars($baseUrl . $path, ENT_XML1, 'UTF-8');
            $xml .= "    <sitemap>\n";
            $xml .= "        <loc>{$loc}</loc>\n";
            $xml .= "        <lastmod>{$nowDate}</lastmod>\n";
            $xml .= "    </sitemap>\n";
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    /**
     * Render a standard XML <urlset> wrapper for given items.
     *
     * @param array<int, array{loc: string, lastmod: string, changefreq: string, priority: string}> $items
     */
    private function renderUrlset(array $items): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        foreach ($items as $item) {
            $loc = htmlspecialchars($item['loc'], ENT_XML1, 'UTF-8');
            $lastmod = htmlspecialchars($item['lastmod'], ENT_XML1, 'UTF-8');
            $changefreq = htmlspecialchars($item['changefreq'], ENT_XML1, 'UTF-8');
            $priority = htmlspecialchars($item['priority'], ENT_XML1, 'UTF-8');

            $xml .= "    <url>\n";
            $xml .= "        <loc>{$loc}</loc>\n";
            $xml .= "        <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "        <changefreq>{$changefreq}</changefreq>\n";
            $xml .= "        <priority>{$priority}</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    public function generatePagesXml(): string
    {
        return $this->renderUrlset($this->getPagesUrls());
    }

    public function generateBlogXml(): string
    {
        return $this->renderUrlset($this->getBlogUrls());
    }

    public function generateMarketplaceXml(): string
    {
        return $this->renderUrlset($this->getMarketplaceUrls());
    }

    public function generateToolsXml(): string
    {
        return $this->renderUrlset($this->getToolsUrls());
    }

    /**
     * Generate standard XML sitemap index for sitemap.xml.
     */
    public function generateXml(): string
    {
        return $this->generateSitemapIndexXml();
    }

    /**
     * Save all split sitemaps to public folder.
     *
     * @return array<string, string>
     */
    public function saveAllToPublic(): array
    {
        $indexFile = public_path('sitemap.xml');
        $pagesFile = public_path('sitemap_pages.xml');
        $blogFile = public_path('sitemap_blog.xml');
        $marketplaceFile = public_path('sitemap_marketplace.xml');
        $toolsFile = public_path('sitemap_tools.xml');

        file_put_contents($indexFile, $this->generateSitemapIndexXml());
        file_put_contents($pagesFile, $this->generatePagesXml());
        file_put_contents($blogFile, $this->generateBlogXml());
        file_put_contents($marketplaceFile, $this->generateMarketplaceXml());
        file_put_contents($toolsFile, $this->generateToolsXml());

        return [
            'index' => $indexFile,
            'pages' => $pagesFile,
            'blog' => $blogFile,
            'marketplace' => $marketplaceFile,
            'tools' => $toolsFile,
        ];
    }

    public function saveToPublic(?string $path = null): string
    {
        $targetPath = $path ?? public_path('sitemap.xml');
        file_put_contents($targetPath, $this->generateSitemapIndexXml());

        return $targetPath;
    }

    public function saveBlogToPublic(?string $path = null): string
    {
        $targetPath = $path ?? public_path('sitemap_blog.xml');
        file_put_contents($targetPath, $this->generateBlogXml());

        return $targetPath;
    }

    /**
     * Group URLs by category for rendering in an HTML Sitemap page.
     *
     * @return array<string, array<int, array{loc: string, title: string, priority: string}>>
     */
    public function getGroupedUrls(): array
    {
        $all = $this->getPublicUrls();
        $grouped = [];

        foreach ($all as $item) {
            $cat = $item['category'];
            $grouped[$cat][] = [
                'loc' => $item['loc'],
                'title' => $item['title'],
                'priority' => $item['priority'],
            ];
        }

        return $grouped;
    }
}
