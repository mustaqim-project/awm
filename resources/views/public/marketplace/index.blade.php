@extends('layouts.public_marketing')

@section('title', __('public_marketplace.mkt_title') . ' | COOCA')
@section('description', __('public_marketplace.mkt_subtitle', ['products' => number_format($totalProducts), 'stores' => number_format($totalStores)]))
@section('canonical', route('marketplace.index'))
@section('og_title', __('public_marketplace.mkt_title') . ' | COOCA')
@section('og_description', __('public_marketplace.mkt_subtitle', ['products' => number_format($totalProducts), 'stores' => number_format($totalStores)]))
@section('og_type', 'website')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "WebSite",
  "name": "COOCA Marketplace",
  "url": "{{ route('marketplace.index') }}",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "{{ route('marketplace.search') }}?q={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>
@endpush

@section('content')
<div class="bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors duration-300 min-h-screen pb-16">

    {{-- Shopee-Style Header & Search Hub (Cooca Midnight Blue & Royal Blue Palette) --}}
    <section class="relative bg-gradient-to-b from-[#060B1E] via-[#0A1435] to-[#0A163B] text-white pt-2.5 pb-6 border-b border-white/10 shadow-lg">
        {{-- Ambient Tech Glows --}}
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-1/4 w-80 h-80 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-3.5">
            {{-- Top Utility Bar (Shopee-Style Top Mini Nav) --}}
            <div class="flex items-center justify-between text-xs border-b border-white/10 pb-2.5 text-slate-300">
                <div class="flex items-center gap-3 sm:gap-5 flex-wrap">
                    <a href="{{ route('login') }}" class="hover:text-white transition flex items-center gap-1.5 font-medium">
                        <i data-lucide="store" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                        <span>Pusat Penjual UMKM</span>
                    </a>
                    <span class="text-white/20 hidden sm:inline">|</span>
                    <a href="{{ route('register') }}" class="hover:text-white transition flex items-center gap-1.5 font-medium text-emerald-400">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>Mulai Jualan Gratis</span>
                    </a>
                    <span class="text-white/20 hidden md:inline">|</span>
                    <a href="{{ route('landing') }}#faq" class="hover:text-white transition hidden md:inline">Pusat Bantuan</a>
                </div>

                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="hidden sm:flex items-center gap-1.5 text-slate-300 hover:text-white cursor-pointer transition">
                        <i data-lucide="bell" class="w-3.5 h-3.5"></i>
                        <span>Notifikasi</span>
                    </div>
                    <span class="text-white/20 hidden sm:inline">|</span>
                    <div class="flex items-center gap-1 text-slate-300">
                        <i data-lucide="globe" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                        <span class="font-medium">Indonesia</span>
                    </div>
                    <span class="text-white/20">|</span>

                    @if (auth('customer')->guest())
                        <div class="flex items-center gap-2">
                            <a href="{{ route('register') }}" class="hover:text-white font-medium transition">Daftar</a>
                            <span class="text-white/30">/</span>
                            <a href="{{ route('customer.login') }}" class="text-[#00C4D8] hover:text-white font-semibold transition">Masuk</a>
                        </div>
                    @else
                        <a href="{{ route('customer.dashboard') }}" class="text-[#00C4D8] hover:text-white font-semibold flex items-center gap-1 transition">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i>
                            <span class="truncate max-w-[120px]">{{ auth('customer')->user()->name }}</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Breadcrumb Navigation (Apple HIG Standard) --}}
            <nav class="flex items-center gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-white transition-colors">{{ __('public_marketing.nav_home') }}</a>
                <span aria-hidden="true" class="text-white/20">/</span>
                <span class="text-[#00C4D8] font-semibold" aria-current="page">Marketplace UMKM</span>
            </nav>

            {{-- Main Header & Shopee-Style Search Row --}}
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 pt-1">
                {{-- Logo & Marketplace Badge --}}
                <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-start">
                    <a href="{{ route('marketplace.index') }}" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-[#007AFF] flex items-center justify-center text-white font-black text-lg shadow-md shadow-[#007AFF]/30 group-hover:scale-105 transition-transform">
                            C
                        </div>
                        <div>
                            <span class="font-black text-xl tracking-tight text-white flex items-center gap-1.5">
                                COOCA <span class="text-[#00C4D8] font-semibold text-sm">Market</span>
                            </span>
                            <span class="text-[10px] text-slate-400 block -mt-1 tracking-wider uppercase font-semibold">Sentra UMKM Indonesia</span>
                        </div>
                    </a>

                    {{-- Mobile Direct Directory Link --}}
                    <a href="{{ route('marketplace.sub.businesses') }}" class="md:hidden p-2 rounded-xl bg-white/10 text-white text-xs flex items-center gap-1">
                        <i data-lucide="store" class="w-4 h-4 text-[#00C4D8]"></i>
                    </a>
                </div>

                {{-- Prominent Central Search Bar with Shopee Layout --}}
                <div class="w-full md:flex-1 md:max-w-3xl md:mx-6 space-y-2">
                    <form method="GET" action="{{ route('marketplace.search') }}" class="w-full">
                        <div class="relative flex items-center bg-white rounded-xl shadow-xl p-1 focus-within:ring-2 focus-within:ring-[#007AFF] transition">
                            <input type="text" name="q" placeholder="{{ __('public_marketplace.mkt_search_placeholder') }}"
                                value="{{ request('q') }}"
                                class="w-full bg-transparent border-0 px-3.5 py-2 sm:py-2.5 text-sm sm:text-base text-slate-900 placeholder-slate-400 focus:outline-none">
                            <button type="submit"
                                class="shrink-0 px-5 sm:px-7 py-2 sm:py-2.5 rounded-lg bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs sm:text-sm font-semibold flex items-center gap-2 shadow-sm active:scale-[0.98] transition min-h-[40px]">
                                <i data-lucide="search" class="w-4 h-4"></i>
                                <span class="hidden sm:inline">{{ __('public_marketplace.mkt_search_submit') }}</span>
                            </button>
                        </div>
                    </form>

                    {{-- Popular Keyword Pills (Shopee-Style under search bar) --}}
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar text-[11px] sm:text-xs text-slate-300 pt-0.5">
                        <span class="text-slate-400 shrink-0 font-medium">Populer:</span>
                        @foreach ($popularKeywords as $kw)
                            <a href="{{ route('marketplace.search', ['q' => $kw]) }}"
                                class="shrink-0 hover:text-[#00C4D8] hover:underline transition-colors">
                                {{ $kw }}
                            </a>
                            @if (!$loop->last)
                                <span class="text-white/20 shrink-0">•</span>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Right: Store Directory & Merchant CTA --}}
                <div class="hidden md:flex items-center gap-3 shrink-0">
                    <a href="{{ route('marketplace.sub.businesses') }}"
                        class="h-10 px-4 rounded-xl bg-white/10 hover:bg-white/15 text-white border border-white/15 text-xs font-semibold flex items-center gap-2 transition backdrop-blur-sm shadow-sm"
                        title="Daftar Seluruh Toko Terverifikasi">
                        <i data-lucide="store" class="w-4 h-4 text-[#00C4D8]"></i>
                        <span>Direktori Toko</span>
                    </a>
                    <a href="{{ route('register') }}"
                        class="h-10 px-4 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 shadow-md shadow-[#007AFF]/25 transition"
                        title="Buka Toko Gratis">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Buka Toko</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Container --}}
    <main class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6 space-y-6 sm:space-y-8">

        {{-- Section 1: Shopee-Style Hero Banners (2/3 Carousel + 1/3 Stacked Banners) --}}
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-3.5">
            {{-- Left Banner (2/3 width, lg:col-span-8) --}}
            <div class="lg:col-span-8 relative rounded-2xl bg-gradient-to-br from-[#0B1739] via-[#0E1E45] to-[#0A163B] text-white border border-white/10 p-6 sm:p-8 flex flex-col justify-between overflow-hidden shadow-md group min-h-[260px] sm:min-h-[300px]">
                {{-- Decorative Glows --}}
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-[#007AFF]/25 rounded-full blur-[80px] pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-64 h-64 bg-[#00C4D8]/20 rounded-full blur-[80px] pointer-events-none"></div>

                <div class="relative z-10 space-y-3 max-w-xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#00C4D8]/15 border border-[#00C4D8]/30 text-[#00C4D8] text-[11px] font-bold tracking-wide uppercase">
                        <span class="w-2 h-2 rounded-full bg-[#00C4D8] animate-pulse"></span>
                        <span>{{ __('public_marketplace.mkt_hero_badge') }}</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        {{ __('public_marketplace.mkt_title') }}
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-normal">
                        {{ __('public_marketplace.mkt_subtitle', ['products' => number_format($totalProducts), 'stores' => number_format($totalStores)]) }}
                    </p>
                </div>

                <div class="relative z-10 pt-4 flex flex-wrap items-center justify-between gap-3 border-t border-white/10">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('marketplace.sub.products') }}"
                            class="h-10 px-5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs sm:text-sm font-semibold flex items-center gap-1.5 shadow-md shadow-[#007AFF]/30 transition active:scale-95">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                            <span>Jelajahi Produk</span>
                        </a>
                        <a href="{{ route('register') }}"
                            class="h-10 px-5 rounded-xl bg-white/10 hover:bg-white/15 text-white border border-white/15 text-xs sm:text-sm font-semibold flex items-center gap-1.5 transition backdrop-blur-sm">
                            <i data-lucide="store" class="w-4 h-4 text-[#00C4D8]"></i>
                            <span>Buka Toko Gratis</span>
                        </a>
                    </div>

                    {{-- Carousel Dots Indicator --}}
                    <div class="hidden sm:flex items-center gap-1.5">
                        <span class="w-6 h-2 rounded-full bg-[#00C4D8]"></span>
                        <span class="w-2 h-2 rounded-full bg-white/30"></span>
                        <span class="w-2 h-2 rounded-full bg-white/30"></span>
                    </div>
                </div>
            </div>

            {{-- Right Stacked Banners (1/3 width, lg:col-span-4) --}}
            <div class="lg:col-span-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3.5">
                {{-- Banner 1: Cooca Verified Store --}}
                <a href="{{ route('marketplace.sub.businesses') }}"
                    class="rounded-2xl bg-gradient-to-r from-[#0E1E45] to-[#14295D] border border-white/10 p-5 text-white flex flex-col justify-between relative overflow-hidden shadow-sm group hover:border-[#007AFF]/50 transition min-h-[140px]">
                    <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-[#007AFF]/20 rounded-full blur-[40px] pointer-events-none"></div>
                    <div class="space-y-1.5 relative z-10">
                        <span class="text-[10px] font-bold text-[#00C4D8] tracking-wider uppercase block">
                            {{ __('public_marketplace.mkt_hero_side_1_badge') }}
                        </span>
                        <h2 class="text-sm sm:text-base font-bold text-white group-hover:text-[#00C4D8] transition">
                            {{ __('public_marketplace.mkt_hero_side_1_title') }}
                        </h2>
                        <p class="text-[11px] text-slate-300 line-clamp-2">
                            {{ __('public_marketplace.mkt_hero_side_1_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 flex items-center justify-between text-xs font-semibold text-[#00C4D8] relative z-10">
                        <span>Lihat Toko Terverifikasi</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                {{-- Banner 2: Free 0% Commission Store Creation --}}
                <a href="{{ route('register') }}"
                    class="rounded-2xl bg-gradient-to-r from-[#082238] to-[#0A3254] border border-white/10 p-5 text-white flex flex-col justify-between relative overflow-hidden shadow-sm group hover:border-emerald-500/50 transition min-h-[140px]">
                    <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-emerald-500/20 rounded-full blur-[40px] pointer-events-none"></div>
                    <div class="space-y-1.5 relative z-10">
                        <span class="text-[10px] font-bold text-emerald-400 tracking-wider uppercase block">
                            {{ __('public_marketplace.mkt_hero_side_2_badge') }}
                        </span>
                        <h2 class="text-sm sm:text-base font-bold text-white group-hover:text-emerald-300 transition">
                            {{ __('public_marketplace.mkt_hero_side_2_title') }}
                        </h2>
                        <p class="text-[11px] text-slate-300 line-clamp-2">
                            {{ __('public_marketplace.mkt_hero_side_2_desc') }}
                        </p>
                    </div>
                    <div class="pt-2 flex items-center justify-between text-xs font-semibold text-emerald-400 relative z-10">
                        <span>Daftar Sekarang (1 Menit)</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>
            </div>
        </section>

        {{-- Section 2: Shopee-Style Quick Features Icons Row (Circular Icons with Labels) --}}
        <section class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 sm:p-5 shadow-sm">
            <div class="grid grid-cols-5 md:grid-cols-10 gap-3 sm:gap-4 text-center">
                {{-- 1. Pilih Lokal --}}
                <a href="{{ route('marketplace.sub.products') }}" class="group flex flex-col items-center gap-2">
                    <div class="w-12 h-12 rounded-2xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center group-hover:scale-110 group-hover:bg-[#007AFF] group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="heart" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-300 group-hover:text-[#007AFF] line-clamp-1">
                        {{ __('public_marketplace.mkt_quick_local') }}
                    </span>
                </a>

                {{-- 2. Toko Resmi --}}
                <a href="{{ route('marketplace.sub.businesses') }}" class="group flex flex-col items-center gap-2">
                    <div class="w-12 h-12 rounded-2xl bg-[#00C4D8]/10 dark:bg-[#00C4D8]/20 text-[#00C4D8] flex items-center justify-center group-hover:scale-110 group-hover:bg-[#00C4D8] group-hover:text-slate-900 transition-all shadow-sm">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-300 group-hover:text-[#007AFF] line-clamp-1">
                        {{ __('public_marketplace.mkt_quick_official') }}
                    </span>
                </a>

                {{-- 3. Katalog UMKM --}}
                <a href="{{ route('marketplace.sub.products') }}" class="group flex flex-col items-center gap-2">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-emerald-500 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="package" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-300 group-hover:text-emerald-600 line-clamp-1">
                        {{ __('public_marketplace.mkt_quick_catalog') }}
                    </span>
                </a>

                {{-- 4. Promo Kilat --}}
                <a href="#flash-sale" class="group flex flex-col items-center gap-2">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-500 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="zap" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-300 group-hover:text-amber-600 line-clamp-1">
                        {{ __('public_marketplace.mkt_quick_flash') }}
                    </span>
                </a>

                {{-- 5. Sentra Kota --}}
                <a href="{{ route('marketplace.sub.locations') }}" class="group flex flex-col items-center gap-2">
                    <div class="w-12 h-12 rounded-2xl bg-purple-500/10 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-purple-500 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-300 group-hover:text-purple-600 line-clamp-1">
                        {{ __('public_marketplace.mkt_quick_city') }}
                    </span>
                </a>

                {{-- 6. Buka Toko --}}
                <a href="{{ route('register') }}" class="group flex flex-col items-center gap-2">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-indigo-500 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-300 group-hover:text-indigo-600 line-clamp-1">
                        {{ __('public_marketplace.mkt_quick_register') }}
                    </span>
                </a>

                {{-- 7. Kasir POS --}}
                <a href="{{ route('landing') }}#pos" class="group flex flex-col items-center gap-2">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600/10 dark:bg-blue-600/20 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-300 group-hover:text-blue-600 line-clamp-1">
                        {{ __('public_marketplace.mkt_quick_pos') }}
                    </span>
                </a>

                {{-- 8. Kuliner & F&B --}}
                <a href="{{ route('marketplace.search', ['kategori' => 'fnb']) }}" class="group flex flex-col items-center gap-2">
                    <div class="w-12 h-12 rounded-2xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-orange-500 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="utensils" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-300 group-hover:text-orange-600 line-clamp-1">
                        {{ __('public_marketplace.mkt_quick_fnb') }}
                    </span>
                </a>

                {{-- 9. Ritel & Toko --}}
                <a href="{{ route('marketplace.search', ['kategori' => 'retail']) }}" class="group flex flex-col items-center gap-2">
                    <div class="w-12 h-12 rounded-2xl bg-teal-500/10 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-teal-500 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-300 group-hover:text-teal-600 line-clamp-1">
                        {{ __('public_marketplace.mkt_quick_retail') }}
                    </span>
                </a>

                {{-- 10. Semua Menu / Kategori --}}
                <a href="{{ route('marketplace.sub.categories') }}" class="group flex flex-col items-center gap-2">
                    <div class="w-12 h-12 rounded-2xl bg-slate-500/10 dark:bg-slate-500/20 text-slate-600 dark:text-slate-400 flex items-center justify-center group-hover:scale-110 group-hover:bg-slate-700 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="layout-grid" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-300 group-hover:text-slate-900 line-clamp-1">
                        {{ __('public_marketplace.mkt_quick_all') }}
                    </span>
                </a>
            </div>
        </section>

        {{-- Section 3: Shopee "KATEGORI" Section (2-Row Category Box with Circular Thumbnails) --}}
        <section class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 sm:p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-white/10 pb-3">
                <h2 class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>KATEGORI</span>
                </h2>
                <a href="{{ route('marketplace.sub.categories') }}" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            {{-- 2 Rows of Category Cells (6 items per row = 12 total items) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 sm:gap-2.5">
                @foreach ($categories as $key => $cat)
                    <a href="{{ route('marketplace.search', ['kategori' => $key]) }}"
                        class="p-3 rounded-xl border border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/[0.02] hover:bg-white dark:hover:bg-[#2C2C2E] hover:border-[#007AFF] hover:shadow-md transition-all text-center group flex flex-col items-center justify-center gap-2">
                        <div class="w-12 h-12 rounded-full bg-[#007AFF]/10 dark:bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center group-hover:scale-110 group-hover:bg-[#007AFF] group-hover:text-white transition-all shadow-sm">
                            <i data-lucide="{{ $cat['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0 w-full">
                            <span class="font-semibold text-xs text-slate-700 dark:text-slate-200 block truncate group-hover:text-[#007AFF] transition-colors">
                                {{ $cat['label'] }}
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5 tabular-nums">
                                {{ $cat['count'] > 0 ? $cat['count'] . ' Toko' : 'Tersedia' }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Section 4: Shopee "FLASH SALE" Bar with Real-Time Countdown Timer (Only when real products exist) --}}
        @if ($popularProducts->isNotEmpty())
            <section id="flash-sale" class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 sm:p-5 shadow-sm space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-white/10 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="text-sm sm:text-base font-black tracking-wider text-[#007AFF] uppercase flex items-center gap-1.5">
                            <i data-lucide="zap" class="w-4 h-4 fill-[#007AFF] text-[#007AFF]"></i>
                            <span>{{ __('public_marketplace.mkt_flash_sale_title') }}</span>
                        </span>

                        {{-- Shopee-Style Countdown Badges --}}
                        <div class="flex items-center gap-1 text-xs font-bold font-mono">
                            <span id="timer-hours" class="px-2 py-0.5 rounded-md bg-[#060B1E] text-white">06</span>
                            <span class="text-slate-400">:</span>
                            <span id="timer-minutes" class="px-2 py-0.5 rounded-md bg-[#060B1E] text-white">29</span>
                            <span class="text-slate-400">:</span>
                            <span id="timer-seconds" class="px-2 py-0.5 rounded-md bg-[#060B1E] text-white">54</span>
                        </div>
                    </div>

                    <a href="{{ route('marketplace.sub.products') }}" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                        <span>Lihat Semua Promo</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                {{-- Flash Sale 6-Column Grid (Real Products) --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5 sm:gap-3">
                    @foreach ($popularProducts->take(6) as $index => $item)
                        <a href="{{ url('/' . $item->business->slug . '/produk/' . ($item->slug ?: $item->id)) }}"
                            class="group rounded-xl border border-slate-100 dark:border-white/10 bg-white dark:bg-[#1C1C1E] overflow-hidden hover:border-[#007AFF] hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="aspect-square bg-slate-100 dark:bg-[#2C2C2E] overflow-hidden relative">
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                                        <i data-lucide="package" class="w-8 h-8"></i>
                                    </div>
                                @endif
                                <span class="absolute top-1.5 right-1.5 bg-amber-500 text-white font-bold text-[10px] px-1.5 py-0.5 rounded shadow-sm">
                                    PROMO
                                </span>
                            </div>

                            <div class="p-2.5 space-y-1.5">
                                <h3 class="text-xs font-medium text-slate-800 dark:text-slate-100 line-clamp-1 group-hover:text-[#007AFF] transition-colors">
                                    {{ $item->name }}
                                </h3>
                                <p class="text-sm font-bold text-[#007AFF] dark:text-[#38bdf8] font-mono">
                                    Rp {{ number_format($item->selling_price, 0, ',', '.') }}
                                </p>
                                <div class="w-full bg-slate-100 dark:bg-white/10 h-2 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-amber-500 to-[#007AFF] h-full rounded-full" style="width: 85%"></div>
                                </div>
                                <span class="text-[10px] font-semibold text-amber-600 block text-center uppercase">Stok Terbatas</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Section 5: Shopee Mall / Official Stores (TOKO PILIHAN RESMI) --}}
        @if ($featuredStores->isNotEmpty())
            <section class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 sm:p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-white/10 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#007AFF]"></span>
                        <h2 class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                            TOKO RESMI UMKM • VERIFIED MERCHANTS
                        </h2>
                    </div>
                    <a href="{{ route('marketplace.sub.businesses') }}" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                        <span>Lihat Semua Toko</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    @foreach ($featuredStores as $store)
                        <a href="{{ url('/' . $store->slug) }}"
                            class="group rounded-xl border border-slate-100 dark:border-white/10 p-3.5 hover:border-[#007AFF] hover:shadow-md transition-all flex items-center gap-3 bg-slate-50/50 dark:bg-white/[0.02]">
                            @if ($store->logo_url || $store->landingPage?->logo_url)
                                <img src="{{ $store->logo_url ?: $store->landingPage?->logo_url }}" alt="{{ $store->name }}"
                                    class="w-12 h-12 rounded-xl object-cover border border-slate-200 dark:border-white/10 shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-xl bg-[#007AFF] text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-sm">
                                    {{ strtoupper(substr($store->name, 0, 2)) }}
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1">
                                    <h3 class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 truncate group-hover:text-[#007AFF] transition-colors">
                                        {{ $store->name }}
                                    </h3>
                                    <i data-lucide="badge-check" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                                </div>
                                <p class="text-[11px] text-slate-400 truncate">
                                    {{ $store->industry_category ?: 'Toko UMKM' }}
                                </p>
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold block mt-0.5">
                                    {{ $store->products_count }} Produk Aktif
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Section 6: Shopee "REKOMENDASI" Product Feed (Exact Shopee 6-Column Card Grid) --}}
        <section class="space-y-4">
            {{-- Shopee-Style Section Header with Active Accent Line --}}
            <div class="bg-white dark:bg-[#1C1C1E] rounded-xl border border-slate-200/80 dark:border-white/10 px-5 pt-1 shadow-sm flex items-center justify-between">
                <div class="border-b-4 border-[#007AFF] text-[#007AFF] font-extrabold text-sm sm:text-base tracking-wider uppercase py-3 inline-flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>{{ __('public_marketplace.mkt_rekomendasi_title') }}</span>
                </div>
                <span class="text-xs text-slate-400 hidden sm:inline">
                    {{ __('public_marketplace.mkt_rekomendasi_subtitle') }}
                </span>
            </div>

            {{-- 6-Column Responsive Product Cards Grid (Real Data Schema) --}}
            @if ($popularProducts->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5 sm:gap-3.5">
                    @foreach ($popularProducts as $idx => $product)
                        <a href="{{ url('/' . $product->business->slug . '/produk/' . ($product->slug ?: $product->id)) }}"
                            class="group bg-white dark:bg-[#1C1C1E] border border-slate-200/90 dark:border-white/10 rounded-xl overflow-hidden hover:border-[#007AFF] hover:shadow-lg hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between shadow-sm">
                            {{-- Image Container (1:1 Aspect Ratio) --}}
                            <div class="aspect-square bg-slate-100 dark:bg-[#2C2C2E] overflow-hidden relative">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                                        <i data-lucide="package" class="w-10 h-10"></i>
                                    </div>
                                @endif

                                {{-- Corner Promo Badge --}}
                                <span class="absolute top-1.5 right-1.5 bg-[#007AFF] text-white font-bold text-[9px] px-1.5 py-0.5 rounded shadow-sm uppercase">
                                    PROMO
                                </span>
                            </div>

                            {{-- Card Details --}}
                            <div class="p-2.5 space-y-1.5 flex-1 flex flex-col justify-between">
                                <div class="space-y-1">
                                    {{-- Star / Verified Badge --}}
                                    <div class="flex items-center gap-1">
                                        <span class="bg-[#007AFF]/10 text-[#007AFF] text-[9px] font-bold px-1.5 py-0.5 rounded flex items-center gap-0.5">
                                            <i data-lucide="check" class="w-2.5 h-2.5"></i> Star
                                        </span>
                                        <span class="text-[10px] text-slate-400 truncate max-w-[100px]">
                                            {{ $product->business->name }}
                                        </span>
                                    </div>

                                    {{-- 2-Line Product Title --}}
                                    <h3 class="text-xs sm:text-[13px] text-slate-800 dark:text-slate-100 font-normal leading-snug line-clamp-2 group-hover:text-[#007AFF] transition-colors">
                                        {{ $product->name }}
                                    </h3>

                                    {{-- Tag --}}
                                    <span class="inline-block border border-[#007AFF]/30 text-[#007AFF] text-[9px] font-semibold px-1 rounded">
                                        Stok Siap
                                    </span>
                                </div>

                                <div class="pt-1.5 space-y-1">
                                    {{-- Price --}}
                                    <p class="font-extrabold text-sm sm:text-base text-[#007AFF] dark:text-[#38bdf8] font-mono leading-none">
                                        Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                    </p>

                                    {{-- Location & Sales Footer --}}
                                    <div class="flex items-center justify-between text-[10px] text-slate-400 pt-0.5 border-t border-slate-100 dark:border-white/5">
                                        <span class="truncate flex items-center gap-0.5">
                                            <i data-lucide="map-pin" class="w-2.5 h-2.5 shrink-0"></i>
                                            {{ $product->business->city ?: 'Indonesia' }}
                                        </span>
                                        <span class="shrink-0 text-slate-500 font-medium">Tersedia</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Shopee-Style "Lihat Lebih Banyak" Button --}}
                <div class="pt-6 text-center">
                    <a href="{{ route('marketplace.sub.products') }}"
                        class="inline-flex items-center justify-center gap-2 h-11 px-10 rounded-xl bg-white dark:bg-[#1C1C1E] border border-slate-300 dark:border-white/15 hover:border-[#007AFF] text-slate-700 dark:text-slate-200 hover:text-[#007AFF] text-xs sm:text-sm font-semibold shadow-sm transition active:scale-95">
                        <span>{{ __('public_marketplace.mkt_load_more_products') }}</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            @else
                {{-- Clean Real Empty State (Zero Dummy Data) --}}
                <div class="p-8 sm:p-12 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-slate-200/80 dark:border-white/10 text-center space-y-3 shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
                        <i data-lucide="package" class="w-7 h-7"></i>
                    </div>
                    <div class="max-w-md mx-auto space-y-1">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                            {{ __('public_marketplace.prod_empty_title') }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            {{ __('public_marketplace.prod_empty_desc') }}
                        </p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs sm:text-sm font-semibold shadow-sm transition">
                            <span>{{ __('public_marketplace.prod_empty_cta') }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            @endif
        </section>

        {{-- Section 7: Merchant Acquisition Banner (Cooca Midnight Blue Theme) --}}
        <section class="relative p-6 sm:p-10 rounded-2xl bg-gradient-to-br from-[#060B1E] via-[#0A1435] to-[#0A163B] text-white border border-white/10 shadow-xl overflow-hidden text-center space-y-4">
            <div class="absolute -right-20 -top-20 w-72 h-72 bg-[#007AFF]/20 rounded-full blur-[90px] pointer-events-none"></div>
            <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-[#00C4D8]/15 rounded-full blur-[90px] pointer-events-none"></div>

            <div class="relative z-10 space-y-3 max-w-2xl mx-auto">
                <span class="text-xs font-bold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1.5">
                    <i data-lucide="store" class="w-4 h-4 text-emerald-400"></i>
                    <span>{{ __('public_marketplace.mkt_seller_banner_tag') }}</span>
                </span>

                <h2 class="text-xl sm:text-3xl font-extrabold text-white tracking-tight">
                    {{ __('public_marketplace.mkt_seller_banner_title') }}
                </h2>

                <p class="text-xs sm:text-sm text-slate-300 max-w-xl mx-auto leading-relaxed">
                    {{ __('public_marketplace.mkt_seller_banner_desc') }}
                </p>

                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                    <a href="{{ route('register') }}"
                        class="h-11 px-7 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs sm:text-sm font-semibold flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition min-h-[44px]">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>{{ __('public_marketplace.mkt_seller_banner_cta_reg') }}</span>
                    </a>
                    <a href="{{ route('login') }}"
                        class="h-11 px-6 rounded-xl bg-white/10 hover:bg-white/15 text-white border border-white/15 text-xs sm:text-sm font-semibold transition active:scale-95 flex items-center gap-2 min-h-[44px]">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>{{ __('public_marketplace.mkt_seller_banner_cta_login') }}</span>
                    </a>
                </div>
            </div>
        </section>

    </main>
</div>

{{-- Real-Time Countdown Timer Simulation Script (Shopee Flash Sale Style) --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let hours = 6;
        let minutes = 29;
        let seconds = 54;

        const elH = document.getElementById('timer-hours');
        const elM = document.getElementById('timer-minutes');
        const elS = document.getElementById('timer-seconds');

        if (elH && elM && elS) {
            setInterval(function () {
                if (seconds > 0) {
                    seconds--;
                } else {
                    seconds = 59;
                    if (minutes > 0) {
                        minutes--;
                    } else {
                        minutes = 59;
                        if (hours > 0) {
                            hours--;
                        } else {
                            hours = 23;
                        }
                    }
                }
                elH.textContent = String(hours).padStart(2, '0');
                elM.textContent = String(minutes).padStart(2, '0');
                elS.textContent = String(seconds).padStart(2, '0');
            }, 1000);
        }

        // Re-init lucide icons if available
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    });
</script>
@endpush
@endsection
