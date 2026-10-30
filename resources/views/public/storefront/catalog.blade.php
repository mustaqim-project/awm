@extends('public.storefront.layouts.app')

@php
    $catTitle = 'Katalog Produk & Layanan | ' . $business->name;
    $catDesc = 'Jelajahi seluruh koleksi produk dan layanan resmi ' . $business->name . '. Dapatkan penawaran harga terbaik, stok kasir terjamin, dan pengiriman aman.';
    $catOgImage = $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: ($business->logo_url ?: asset('assets/seo/cooca-og-default.jpg')));
    $catCanonical = url('/' . $business->slug . '/katalog');

    // Resolve active theme preset for sector-specific partials
    $activeTheme = $theme ?? app(\App\Domain\Storefront\StorefrontThemeService::class)->resolveTheme($landingPage);
    $themePreset = $activeTheme['id'] ?? 'artisan_brew';
    $bundleDir = $activeTheme['bundle_dir'] ?? 'universal_storefront';
    $isArtisanBrew = $themePreset === 'artisan_brew';
@endphp

@section('title', $catTitle)
@section('description', $catDesc)
@section('canonical', $catCanonical)
@section('og_title', 'Katalog Produk & Layanan - ' . $business->name)
@section('og_description', $catDesc)
@section('og_image', $catOgImage)
@section('og_type', 'website')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "{{ addslashes($catTitle) }}",
  "description": "{{ addslashes($catDesc) }}",
  "url": "{{ $catCanonical }}",
  "publisher": {
    "@type": "Organization",
    "name": "{{ addslashes($business->name) }}"
  }
}
</script>
@endpush

@section('content')
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-8">

        {{-- Breadcrumb & Header Title --}}
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2 text-xs text-neutral-500">
                <a href="{{ url('/' . $business->slug) }}" class="hover:text-theme-primary transition">{{ __('storefront.nav_home') }}</a>
                <span>/</span>
                <span class="text-neutral-800 dark:text-neutral-200 font-medium">
                    {{ $isArtisanBrew ? 'Menu Kopi' : 'Katalog Toko' }}
                </span>
            </div>
            <h1 class="font-heading font-extrabold text-2xl sm:text-4xl text-neutral-900 dark:text-white">
                {{ $isArtisanBrew ? 'Menu & Katalog Kopi' : 'Katalog Produk & Layanan' }}
            </h1>
            <p class="text-sm max-w-2xl text-neutral-600 dark:text-neutral-400">
                {{ $isArtisanBrew
                    ? 'Temukan biji kopi spesialti, minuman signature, dan pastry pilihan dari ' . $business->name . '.'
                    : 'Jelajahi seluruh koleksi produk dan layanan resmi ' . $business->name . '. Temukan barang kebutuhan Anda dengan harga terbaik.' }}
            </p>
        </div>

        {{-- Filter & Search Toolbar --}}
        {{-- Fase 5: artisan_brew uses coffee-specific filter with quick-filter chips --}}
        @if ($isArtisanBrew)
            @include('public.storefront.themes.fnb_cafe.catalog_filter')
        @else
        <div
            class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm">
            <form method="GET" action="{{ url('/' . $business->slug . '/katalog') }}"
                class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-center">

                {{-- Keyword Search --}}
                <div class="sm:col-span-5 relative">
                    <i data-lucide="search" class="w-4 h-4 text-neutral-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="q" value="{{ $search }}"
                        placeholder="{{ __('storefront.search_products_placeholder') }}"
                        class="w-full pl-10 pr-4 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-theme-primary">
                </div>

                {{-- Category Filter --}}
                <div class="sm:col-span-3">
                    <select name="category" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-theme-primary">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId === $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Sorting --}}
                <div class="sm:col-span-2">
                    <select name="sort" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-theme-primary">
                        <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Terpopuler</option>
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Harga Terendah</option>
                        <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                    </select>
                </div>

                {{-- Submit / Reset Button --}}
                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit"
                        class="w-full py-2.5 px-4 rounded-[12px] text-sm font-semibold theme-btn-primary shadow-sm flex items-center justify-center gap-1.5 min-h-[44px]">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Terapkan</span>
                    </button>
                    @if ($search !== '' || !empty($categoryId) || $sort !== 'popular')
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="p-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200 transition min-h-[44px] flex items-center justify-center"
                            title="{{ __('storefront.reset_filter') }}">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

            </form>
        </div>
        @endif

        {{-- Product Grid --}}
        @if ($products->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach ($products as $item)
                    @if (view()->exists("public.storefront.themes.{$bundleDir}.product_card"))
                        @include("public.storefront.themes.{$bundleDir}.product_card", ['item' => $item])
                    @elseif (view()->exists("public.storefront.themes.modern_umkm.product_card"))
                        @include("public.storefront.themes.modern_umkm.product_card", ['item' => $item])
                    @elseif (view()->exists("public.storefront.themes.universal_storefront.product_card"))
                        @include("public.storefront.themes.universal_storefront.product_card", ['item' => $item])
                    @else
                        @include("public.storefront.components.product_card", ['product' => $item, 'business' => $business])
                    @endif
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="pt-6">
                {{ $products->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div
                class="py-16 text-center rounded-[20px] bg-white dark:bg-neutral-800/60 border border-black/5 dark:border-white/10 p-8 space-y-4">
                <div
                    class="w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-700 text-neutral-400 mx-auto flex items-center justify-center">
                    <i data-lucide="package-x" class="w-8 h-8"></i>
                </div>
                <h3 class="font-heading font-bold text-lg text-neutral-800 dark:text-neutral-200">{{ __('storefront.no_products_found') }}</h3>
                <p class="text-sm text-neutral-500 max-w-sm mx-auto">
                    Coba ubah kata kunci pencarian Anda atau reset filter untuk menampilkan semua produk.
                </p>
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[12px] font-medium text-sm theme-btn-primary min-h-[44px]">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    <span>{{ __('storefront.reset_all_filters') }}</span>
                </a>
            </div>
        @endif

    </div>
@endsection
