{{--
  resources/views/partials/seo-head.blade.php — one source of truth for <head> metadata.
  In the layout <head>, after charset:  @include('partials.seo-head')
  MERGE with the existing layout instead of duplicating: if the layout already outputs <title>, description,
  canonical or OG tags, remove those lines in favour of this partial and keep every section name the child views
  already use (@yield('title') etc.), so no existing page loses its metadata.

  Child views provide (all optional, with safe fallbacks):
    @section('title', 'Judul halaman unik')                   // 25-60 chars; brand suffix added automatically
    @section('meta_description', 'Ringkasan 70-160 karakter')
    @section('canonical', route('...'))                         // omit to use the current URL without query string
    @section('og_image', asset('images/og/xxx.jpg'))            // 1200x630
    @section('og_type', 'article')                              // default: website
    @section('robots', 'noindex,follow')                        // only for pages that must not be indexed
    @push('schema') <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG) !!}</script> @endpush
    @push('preload') <link rel="preload" as="image" href="..." fetchpriority="high"> @endpush   // LCP image
--}}
@php
    $siteName  = config('app.name');
    $title     = trim($__env->yieldContent('title')) ?: $siteName;
    $fullTitle = \Illuminate\Support\Str::contains($title, $siteName) ? $title : $title.' | '.$siteName;
    $desc      = trim($__env->yieldContent('meta_description'));
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();   // current() drops ?query
    $ogImage   = trim($__env->yieldContent('og_image')) ?: asset('images/og/default.jpg');
    $ogType    = trim($__env->yieldContent('og_type')) ?: 'website';
    $robots    = trim($__env->yieldContent('robots')) ?: 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
@endphp
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $fullTitle }}</title>
@if ($desc !== '')<meta name="description" content="{{ $desc }}">@endif
<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="{{ $robots }}">
{{-- Single-language site: self-referencing hreflang is optional. Add id-ID/x-default only if alternates exist. --}}

{{-- Open Graph / Twitter --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="id_ID">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $title }}">
@if ($desc !== '')<meta property="og:description" content="{{ $desc }}">@endif
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
@stack('og_extra') {{-- article:published_time, article:modified_time, article:author, article:section --}}

{{-- PWA / app-like chrome --}}
<meta name="theme-color" content="REPLACE_THEME_HEX">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/icons/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png"> {{-- 180x180, opaque background --}}
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="{{ $siteName }}">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="format-detection" content="telephone=no">
<script>document.documentElement.classList.add('js')</script>

@stack('preload')
@stack('schema')
