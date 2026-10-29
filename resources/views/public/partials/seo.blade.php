@php
    use App\Services\Seo\SeoMetadataService;

    // 0. Preconfigured page SEO from config/seo.php (Prompt 2 compliant single source of truth)
    $pageSeo = SeoMetadataService::getPageSeo();
    $isRootPath = (Request::path() === '/' || Request::path() === '');
    $adminCustomTitle = $isRootPath ? \App\Models\SystemSetting::get('seo_meta_title') : null;
    $adminCustomDesc = $isRootPath ? \App\Models\SystemSetting::get('seo_meta_description') : null;
    $adminCustomOgTitle = $isRootPath ? \App\Models\SystemSetting::get('seo_og_title') : null;
    $adminCustomOgDesc = $isRootPath ? \App\Models\SystemSetting::get('seo_og_description') : null;

    // 1. Resolve Title (Single source of truth with admin CMS override capability)
    $rawTitle = $adminCustomTitle ?: ($pageSeo['title'] ?? ($title ?? (View::hasSection('title') ? View::yieldContent('title') : null)));
    $resolvedTitle = SeoMetadataService::cleanTitle($rawTitle);

    // 2. Resolve Meta Description
    $rawDesc = $adminCustomDesc ?: ($pageSeo['description'] ?? ($description ?? (View::hasSection('description') ? View::yieldContent('description') : null)));
    if (empty($rawDesc)) {
        $rawDesc = \App\Models\SystemSetting::get(
            'seo_meta_description',
            'Platform terintegrasi kasir POS, akuntansi riil, stok resep bahan baku, WhatsApp otomatis, dan toko online untuk UMKM Indonesia.'
        );
    }
    $resolvedDesc = SeoMetadataService::cleanText((string) $rawDesc, 160);

    // 3. Resolve Canonical & OG URL (Absolute, clean, no tracker params)
    $resolvedCanonical = SeoMetadataService::resolveCanonicalUrl($url ?? $canonical ?? null);

    // 4. Resolve OG Image (Absolute HTTPS, 1200x630 guaranteed)
    $ogImgData = SeoMetadataService::resolveOgImage(
        $image ?? null,
        $imageAlt ?? $resolvedTitle,
        $fallbackImage ?? null
    );

    // 5. Resolve Site Name & Locale
    $resolvedSiteName = $siteName ?? ($business->name ?? \App\Models\SystemSetting::get('app_name', 'COOCA'));
    $resolvedLocale = $locale ?? 'id_ID';

    // 6. Resolve OG Type
    $resolvedOgType = $type ?? (View::hasSection('og_type') ? trim((string) View::yieldContent('og_type')) : 'website');

    // 7. Resolve OG Title & OG Description
    $rawOgTitle = $adminCustomOgTitle ?: ($pageSeo['og_title'] ?? $pageSeo['title'] ?? ($ogTitle ?? (View::hasSection('og_title') ? View::yieldContent('og_title') : $resolvedTitle)));
    $resolvedOgTitle = SeoMetadataService::cleanTitle((string) $rawOgTitle);

    $rawOgDesc = $adminCustomOgDesc ?: ($pageSeo['og_description'] ?? $pageSeo['description'] ?? ($ogDescription ?? (View::hasSection('og_description') ? View::yieldContent('og_description') : $resolvedDesc)));
    $resolvedOgDesc = SeoMetadataService::cleanText((string) $rawOgDesc, 160);

    // 8. Resolve Twitter Card
    $resolvedTwitterCard = $twitterCard ?? (View::hasSection('twitter_card') ? View::yieldContent('twitter_card') : 'summary_large_image');
    $resolvedTwitterSite = $twitterSite ?? \App\Models\SystemSetting::get('seo_twitter_site', '@cooca_id');

    // 9. Resolve Robots (Enforces noindex for test & demo stores - Prompt 3 & 5 compliant)
    $isDemoStore = isset($business) && (bool) ($business->is_demo ?? false);
    $isTestStore = (isset($business) && (bool) ($business->is_test ?? false)) || !empty($noindex);

    if ($isTestStore) {
        $resolvedRobots = 'noindex, nofollow';
    } elseif ($isDemoStore) {
        $resolvedRobots = 'noindex, follow';
    } else {
        $resolvedRobots = SeoMetadataService::resolveRobots($robots ?? null, false);
    }

    // 10. Resolve Author (Keywords removed per SEO best practices)
    $resolvedAuthor = $author ?? (View::hasSection('author') ? View::yieldContent('author') : \App\Models\SystemSetting::get('seo_author', 'COOCA Indonesia'));

    // 11. Article / Product specifics
    $resolvedPublishedAt = $publishedAt ?? (View::hasSection('article_published_time') ? View::yieldContent('article_published_time') : null);
    $resolvedModifiedAt = $modifiedAt ?? (View::hasSection('article_modified_time') ? View::yieldContent('article_modified_time') : null);
    $resolvedArticleAuthor = $articleAuthor ?? (View::hasSection('article_author') ? View::yieldContent('article_author') : null);

    $resolvedPrice = $price ?? (View::hasSection('product_price') ? View::yieldContent('product_price') : null);
@endphp

<!-- Primary HTML Meta Tags -->
<title>{{ $resolvedTitle }}</title>
<meta name="description" content="{{ $resolvedDesc }}">
<meta name="author" content="{{ $resolvedAuthor }}">
<link rel="canonical" href="{{ $resolvedCanonical }}">
<meta name="robots" content="{{ $resolvedRobots }}">

<!-- Open Graph / Facebook / WhatsApp / LinkedIn / Telegram / Discord -->
<meta property="og:type" content="{{ $resolvedOgType }}">
<meta property="og:site_name" content="{{ $resolvedSiteName }}">
<meta property="og:locale" content="{{ $resolvedLocale }}">
<meta property="og:url" content="{{ $resolvedCanonical }}">
<meta property="og:title" content="{{ $resolvedOgTitle }}">
<meta property="og:description" content="{{ $resolvedOgDesc }}">
<meta property="og:image" content="{{ $ogImgData['url'] }}">
<meta property="og:image:secure_url" content="{{ $ogImgData['secure_url'] }}">
<meta property="og:image:type" content="{{ $ogImgData['type'] }}">
<meta property="og:image:width" content="{{ $ogImgData['width'] }}">
<meta property="og:image:height" content="{{ $ogImgData['height'] }}">
<meta property="og:image:alt" content="{{ $ogImgData['alt'] }}">

@if($resolvedOgType === 'article')
@if(!empty($resolvedPublishedAt))
<meta property="article:published_time" content="{{ $resolvedPublishedAt }}">
@endif
@if(!empty($resolvedModifiedAt))
<meta property="article:modified_time" content="{{ $resolvedModifiedAt }}">
@endif
@if(!empty($resolvedArticleAuthor))
<meta property="article:author" content="{{ $resolvedArticleAuthor }}">
@endif
@endif

@if($resolvedOgType === 'product' && !empty($resolvedPrice))
<meta property="product:price:amount" content="{{ $resolvedPrice }}">
<meta property="product:price:currency" content="IDR">
@endif

<!-- Twitter / X Card -->
<meta name="twitter:card" content="{{ $resolvedTwitterCard }}">
@if(!empty($resolvedTwitterSite))
<meta name="twitter:site" content="{{ $resolvedTwitterSite }}">
@endif
<meta name="twitter:title" content="{{ $resolvedOgTitle }}">
<meta name="twitter:description" content="{{ $resolvedOgDesc }}">
<meta name="twitter:image" content="{{ $ogImgData['url'] }}">
<meta name="twitter:image:alt" content="{{ $ogImgData['alt'] }}">

@if(!empty($schema))
<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endif
