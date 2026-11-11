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
<div class="bg-[#F5F5F5] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors duration-300 min-h-screen pb-16 font-sans">

    {{-- Shopee-Style Header & Search Hub (Mempertahankan Warna Cooca: Midnight Blue & Royal Blue) --}}
    <section class="relative bg-gradient-to-b from-[#060B1E] via-[#0A1435] to-[#0A163B] text-white pt-2 pb-5 border-b border-white/10 shadow-md">
        {{-- Ambient Tech Glows --}}
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-1/4 w-80 h-80 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-[1240px] mx-auto px-3 sm:px-4 lg:px-6 relative z-10 space-y-3">
            {{-- Top Mini Utility Bar (Persis Shopee Top Navigation Bar) --}}
            <div class="flex items-center justify-between text-[11px] sm:text-xs border-b border-white/10 pb-2 text-slate-300">
                <div class="flex items-center gap-2.5 sm:gap-4 flex-wrap">
                    <a href="{{ route('login') }}" class="hover:text-white transition flex items-center gap-1.5 font-medium">
                        <i data-lucide="store" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                        <span>Seller Centre UMKM</span>
                    </a>
                    <span class="text-white/20 hidden sm:inline">|</span>
                    <a href="{{ route('register') }}" class="hover:text-white transition flex items-center gap-1.5 font-medium text-emerald-400">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>Mulai Berjualan</span>
                    </a>
                    <span class="text-white/20 hidden sm:inline">|</span>
                    <a href="{{ route('landing') }}#faq" class="hover:text-white transition hidden sm:inline">Download & Bantuan</a>
                    <span class="text-white/20 hidden md:inline">|</span>
                    <div class="hidden md:flex items-center gap-2">
                        <span>Ikuti kami di</span>
                        <a href="https://instagram.com/cooca.id" target="_blank" rel="noopener" class="hover:text-white transition" title="Instagram">
                            <i data-lucide="instagram" class="w-3.5 h-3.5"></i>
                        </a>
                        <a href="https://facebook.com/cooca.id" target="_blank" rel="noopener" class="hover:text-white transition" title="Facebook">
                            <i data-lucide="facebook" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3.5">
                    <div class="hidden sm:flex items-center gap-1.5 text-slate-300 hover:text-white cursor-pointer transition">
                        <i data-lucide="bell" class="w-3.5 h-3.5"></i>
                        <span>Notifikasi</span>
                    </div>
                    <span class="text-white/20 hidden sm:inline">|</span>
                    <a href="{{ route('landing') }}#faq" class="flex items-center gap-1 text-slate-300 hover:text-white transition">
                        <i data-lucide="help-circle" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                        <span>Bantuan</span>
                    </a>
                    <span class="text-white/20">|</span>
                    <div class="flex items-center gap-1 text-slate-300">
                        <i data-lucide="globe" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                        <span class="font-medium">Bahasa Indonesia</span>
                    </div>
                    <span class="text-white/20">|</span>

                    @if (auth('customer')->guest())
                        <div class="flex items-center gap-2 font-medium">
                            <a href="{{ route('register') }}" class="hover:text-white transition">Daftar</a>
                            <span class="text-white/30">/</span>
                            <a href="{{ route('customer.login') }}" class="text-[#00C4D8] hover:text-white font-semibold transition">Log In</a>
                        </div>
                    @else
                        <a href="{{ route('customer.dashboard') }}" class="text-[#00C4D8] hover:text-white font-semibold flex items-center gap-1 transition">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i>
                            <span class="truncate max-w-[120px]">{{ auth('customer')->user()->name }}</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Main Header: Logo + Shopee Central Search Bar + Shopping Cart (Persis Gambar 1) --}}
            <div class="flex flex-col md:flex-row items-center justify-between gap-3 pt-1">
                {{-- Logo & Cooca Market Identity --}}
                <div class="flex items-center justify-between w-full md:w-auto shrink-0 gap-3">
                    <a href="{{ route('marketplace.index') }}" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-[#007AFF] flex items-center justify-center text-white font-black text-xl shadow-md shadow-[#007AFF]/30 group-hover:scale-105 transition-transform">
                            C
                        </div>
                        <div>
                            <span class="font-black text-2xl tracking-tight text-white flex items-center gap-1.5">
                                COOCA <span class="text-[#00C4D8] font-bold text-base">Market</span>
                            </span>
                            <span class="text-[9px] text-slate-400 block -mt-1 tracking-wider uppercase font-semibold">Sentra Belanja UMKM</span>
                        </div>
                    </a>

                    {{-- Mobile Cart & Direct Directory Link --}}
                    <div class="flex md:hidden items-center gap-2">
                        <a href="{{ route('marketplace.sub.businesses') }}" class="p-2 rounded-lg bg-white/10 text-white text-xs flex items-center gap-1" title="Direktori Toko">
                            <i data-lucide="store" class="w-4 h-4 text-[#00C4D8]"></i>
                        </a>
                        <a href="{{ route('marketplace.sub.products') }}" class="p-2 rounded-lg bg-white/10 text-white text-xs flex items-center gap-1" title="Katalog Produk">
                            <i data-lucide="shopping-cart" class="w-4 h-4 text-white"></i>
                        </a>
                    </div>
                </div>

                {{-- Prominent Central Search Bar with Shopee Layout & Keywords Underneath --}}
                <div class="w-full md:flex-1 md:max-w-3xl md:mx-4 space-y-1.5">
                    <form method="GET" action="{{ route('marketplace.search') }}" class="w-full">
                        <div class="relative flex items-center bg-white rounded-md shadow-lg p-1 focus-within:ring-2 focus-within:ring-[#007AFF] transition">
                            <input type="text" name="q" placeholder="Cari produk, kategori, atau nama toko di COOCA Market..."
                                value="{{ request('q') }}"
                                class="w-full bg-transparent border-0 px-3 py-2 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none">
                            <button type="submit"
                                class="shrink-0 px-5 sm:px-6 py-2 rounded bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs sm:text-sm font-semibold flex items-center gap-1.5 shadow-sm active:scale-[0.98] transition min-h-[36px]"
                                title="Cari Produk">
                                <i data-lucide="search" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </form>

                    {{-- Popular Keyword Text Links (Persis Shopee Di Bawah Search Bar) --}}
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar text-[11px] text-slate-300">
                        @foreach ($popularKeywords as $kw)
                            <a href="{{ route('marketplace.search', ['q' => $kw]) }}"
                                class="shrink-0 hover:text-[#00C4D8] hover:underline transition-colors whitespace-nowrap">
                                {{ $kw }}
                            </a>
                            @if (!$loop->last)
                                <span class="text-white/20 shrink-0">•</span>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Right Shopping Cart Button & Store Directory (Desktop) --}}
                <div class="hidden md:flex items-center gap-3 shrink-0">
                    <a href="{{ route('marketplace.sub.products') }}"
                        class="relative p-2.5 rounded-lg bg-white/10 hover:bg-white/15 text-white transition flex items-center justify-center"
                        title="Keranjang Belanja">
                        <i data-lucide="shopping-cart" class="w-6 h-6 text-white"></i>
                        <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-[#007AFF] text-white text-[10px] font-bold flex items-center justify-center border-2 border-[#0A1435]">
                            {{ $totalProducts > 0 ? min($totalProducts, 99) : 0 }}
                        </span>
                    </a>
                    <a href="{{ route('marketplace.sub.businesses') }}"
                        class="h-9 px-3.5 rounded-lg bg-white/10 hover:bg-white/15 text-white border border-white/15 text-xs font-semibold flex items-center gap-1.5 transition backdrop-blur-sm"
                        title="Daftar Toko Resmi">
                        <i data-lucide="store" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                        <span>Direktori Toko</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Container (Lebar Max 1240px standar Shopee Grid) --}}
    <main class="max-w-[1240px] mx-auto px-2 sm:px-4 lg:px-6 pt-3 sm:pt-4 space-y-4">

        {{-- Section 1: Shopee-Style Hero Banners (2/3 Carousel Utama + 1/3 Stacked Banners Kanan - Gambar 1) --}}
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-2.5">
            {{-- Left Banner: Carousel 2/3 Width (lg:col-span-8) --}}
            <div class="lg:col-span-8 relative rounded-lg bg-gradient-to-br from-[#0B1739] via-[#0E1E45] to-[#0A163B] text-white border border-white/10 p-5 sm:p-7 flex flex-col justify-between overflow-hidden shadow-sm group min-h-[220px] sm:min-h-[260px]">
                {{-- Decorative Tech Glows --}}
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-[#007AFF]/25 rounded-full blur-[80px] pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-64 h-64 bg-[#00C4D8]/20 rounded-full blur-[80px] pointer-events-none"></div>

                <div class="relative z-10 space-y-2.5 max-w-lg">
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-[#00C4D8]/15 border border-[#00C4D8]/30 text-[#00C4D8] text-[10px] font-bold tracking-wide uppercase">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                        <span>COOCA PILIH LOKAL</span>
                    </div>

                    <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-white tracking-tight leading-snug">
                        Tempat Belanja Produk Lokal No. 1 di Indonesia
                    </h1>

                    <p class="text-xs text-slate-300 leading-relaxed font-normal">
                        Dukung pertumbuhan bisnis lokal bersama {{ number_format($totalStores) }} toko UMKM terverifikasi dan {{ number_format($totalProducts) }} produk siap kirim ke seluruh nusantara.
                    </p>
                </div>

                <div class="relative z-10 pt-3 flex flex-wrap items-center justify-between gap-3 border-t border-white/10">
                    <div class="flex items-center gap-2.5">
                        <a href="{{ route('marketplace.sub.products') }}"
                            class="h-9 px-4 rounded-md bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm active:scale-95 transition">
                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                            <span>Jelajahi Produk</span>
                        </a>
                        <a href="{{ route('register') }}"
                            class="h-9 px-4 rounded-md bg-white/10 hover:bg-white/15 text-white border border-white/15 text-xs font-semibold flex items-center gap-1.5 transition backdrop-blur-sm">
                            <i data-lucide="store" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                            <span>Buka Toko Gratis</span>
                        </a>
                    </div>

                    {{-- Carousel Navigation Dots Indicator --}}
                    <div class="flex items-center gap-1.5">
                        <span class="w-5 h-1.5 rounded-full bg-[#00C4D8]"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-white/30"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-white/30"></span>
                    </div>
                </div>
            </div>

            {{-- Right Stacked Banners: 1/3 Width (lg:col-span-4) --}}
            <div class="lg:col-span-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2.5">
                {{-- Banner 1: Direktori Toko Resmi UMKM --}}
                <a href="{{ route('marketplace.sub.businesses') }}"
                    class="rounded-lg bg-gradient-to-r from-[#0E1E45] to-[#14295D] border border-white/10 p-4 text-white flex flex-col justify-between relative overflow-hidden shadow-sm group hover:border-[#007AFF]/50 transition min-h-[120px]">
                    <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-[#007AFF]/20 rounded-full blur-[30px] pointer-events-none"></div>
                    <div class="space-y-1 relative z-10">
                        <span class="text-[9px] font-bold text-[#00C4D8] tracking-wider uppercase block">
                            DIREKTORI RESMI
                        </span>
                        <h2 class="text-sm font-bold text-white group-hover:text-[#00C4D8] transition leading-snug">
                            Toko Binaan UMKM Nusantara
                        </h2>
                        <p class="text-[11px] text-slate-300 line-clamp-1">
                            Jelajahi {{ number_format($totalStores) }} merchant terverifikasi dengan profil usaha resmi.
                        </p>
                    </div>
                    <div class="pt-1.5 flex items-center justify-between text-xs font-semibold text-[#00C4D8] relative z-10">
                        <span>Lihat Semua Toko</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                {{-- Banner 2: Integrasi Kasir POS & Toko Online --}}
                <a href="{{ route('register') }}"
                    class="rounded-lg bg-gradient-to-r from-[#082238] to-[#0A3254] border border-white/10 p-4 text-white flex flex-col justify-between relative overflow-hidden shadow-sm group hover:border-emerald-500/50 transition min-h-[120px]">
                    <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-emerald-500/20 rounded-full blur-[30px] pointer-events-none"></div>
                    <div class="space-y-1 relative z-10">
                        <span class="text-[9px] font-bold text-emerald-400 tracking-wider uppercase block">
                            EKOSISTEM BISNIS COOCA
                        </span>
                        <h2 class="text-sm font-bold text-white group-hover:text-emerald-300 transition leading-snug">
                            Kasir POS & Toko Online 0% Komisi
                        </h2>
                        <p class="text-[11px] text-slate-300 line-clamp-1">
                            Kelola produk, kasir POS, dan pesanan web tanpa biaya potongan per transaksi.
                        </p>
                    </div>
                    <div class="pt-1.5 flex items-center justify-between text-xs font-semibold text-emerald-400 relative z-10">
                        <span>Daftar Bisnis Baru</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>
            </div>
        </section>

        {{-- Section 2: Quick Features Icons Row (10 Menu Riil Fitur & Sektor Bisnis COOCA) --}}
        <section class="bg-white dark:bg-[#1C1C1E] rounded-sm sm:rounded-md border border-slate-200/80 dark:border-white/10 p-3 sm:p-4 shadow-sm">
            <div class="grid grid-cols-5 md:grid-cols-10 gap-2 sm:gap-3 text-center">
                {{-- 1. Katalog Produk --}}
                <a href="{{ route('marketplace.sub.products') }}" class="group flex flex-col items-center gap-1.5">
                    <div class="w-11 h-11 rounded-2xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center group-hover:scale-105 group-hover:bg-[#007AFF] group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="package" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 group-hover:text-[#007AFF] line-clamp-2 leading-tight">
                        Katalog Produk
                    </span>
                </a>

                {{-- 2. Direktori Toko --}}
                <a href="{{ route('marketplace.sub.businesses') }}" class="group flex flex-col items-center gap-1.5">
                    <div class="w-11 h-11 rounded-2xl bg-[#00C4D8]/10 dark:bg-[#00C4D8]/20 text-[#00C4D8] flex items-center justify-center group-hover:scale-105 group-hover:bg-[#00C4D8] group-hover:text-slate-900 transition-all shadow-sm">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 group-hover:text-[#007AFF] line-clamp-2 leading-tight">
                        Direktori Toko
                    </span>
                </a>

                {{-- 3. Sentra Wilayah --}}
                <a href="{{ route('marketplace.sub.locations') }}" class="group flex flex-col items-center gap-1.5">
                    <div class="w-11 h-11 rounded-2xl bg-sky-500/10 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 flex items-center justify-center group-hover:scale-105 group-hover:bg-sky-500 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 group-hover:text-sky-600 line-clamp-2 leading-tight">
                        Sentra Wilayah
                    </span>
                </a>

                {{-- 4. 20 Sektor UMKM --}}
                <a href="{{ route('marketplace.sub.categories') }}" class="group flex flex-col items-center gap-1.5">
                    <div class="w-11 h-11 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-105 group-hover:bg-amber-500 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 group-hover:text-amber-600 line-clamp-2 leading-tight">
                        20 Sektor UMKM
                    </span>
                </a>

                {{-- 5. Kuliner & Resto --}}
                <a href="{{ route('marketplace.search', ['kategori' => 'fnb_resto']) }}" class="group flex flex-col items-center gap-1.5">
                    <div class="w-11 h-11 rounded-2xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center group-hover:scale-105 group-hover:bg-orange-500 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="utensils" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 group-hover:text-orange-600 line-clamp-2 leading-tight">
                        Kuliner & Resto
                    </span>
                </a>

                {{-- 6. Coffee & Cafe --}}
                <a href="{{ route('marketplace.search', ['kategori' => 'fnb_cafe']) }}" class="group flex flex-col items-center gap-1.5">
                    <div class="w-11 h-11 rounded-2xl bg-amber-700/10 dark:bg-amber-700/20 text-amber-700 dark:text-amber-300 flex items-center justify-center group-hover:scale-105 group-hover:bg-amber-700 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="coffee" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 group-hover:text-amber-700 line-clamp-2 leading-tight">
                        Coffee & Cafe
                    </span>
                </a>

                {{-- 7. Fashion & Garment --}}
                <a href="{{ route('marketplace.search', ['kategori' => 'fashion']) }}" class="group flex flex-col items-center gap-1.5">
                    <div class="w-11 h-11 rounded-2xl bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center group-hover:scale-105 group-hover:bg-rose-500 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="shirt" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 group-hover:text-rose-600 line-clamp-2 leading-tight">
                        Fashion & Busana
                    </span>
                </a>

                {{-- 8. Toko Ritel & Kelontong --}}
                <a href="{{ route('marketplace.search', ['kategori' => 'retail']) }}" class="group flex flex-col items-center gap-1.5">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-105 group-hover:bg-emerald-500 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 group-hover:text-emerald-600 line-clamp-2 leading-tight">
                        Ritel & Toko
                    </span>
                </a>

                {{-- 9. Bengkel & Otomotif --}}
                <a href="{{ route('marketplace.search', ['kategori' => 'workshop']) }}" class="group flex flex-col items-center gap-1.5">
                    <div class="w-11 h-11 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-105 group-hover:bg-indigo-500 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="wrench" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 group-hover:text-indigo-600 line-clamp-2 leading-tight">
                        Bengkel & Servis
                    </span>
                </a>

                {{-- 10. Jasa & Layanan Bisnis --}}
                <a href="{{ route('marketplace.search', ['kategori' => 'agency']) }}" class="group flex flex-col items-center gap-1.5">
                    <div class="w-11 h-11 rounded-2xl bg-slate-500/10 dark:bg-slate-500/20 text-slate-600 dark:text-slate-400 flex items-center justify-center group-hover:scale-105 group-hover:bg-slate-700 group-hover:text-white transition-all shadow-sm">
                        <i data-lucide="briefcase" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 group-hover:text-slate-900 line-clamp-2 leading-tight">
                        Jasa & Layanan
                    </span>
                </a>
            </div>
        </section>

        {{-- Section 3: Shopee "KATEGORI" Section (Box Putih dengan 2 Baris Kategori Berlingkar - Gambar 1) --}}
        <section class="bg-white dark:bg-[#1C1C1E] rounded-sm sm:rounded-md border border-slate-200/80 dark:border-white/10 shadow-sm overflow-hidden">
            {{-- Header KATEGORI --}}
            <div class="px-4 py-3 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                <h2 class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">
                    KATEGORI
                </h2>
                <a href="{{ route('marketplace.sub.categories') }}" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                    <span>Lihat Semua</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            {{-- 2-Row Category Grid (Persis Seperti di Gambar 1: Lingkaran Abu-Abu + Teks Kategori Rapi) --}}
            <div class="p-3 sm:p-4 relative">
                <div id="category-scroll-container" class="grid grid-rows-2 grid-flow-col auto-cols-[100px] sm:auto-cols-[116px] gap-2 overflow-x-auto no-scrollbar scroll-smooth">
                    @foreach ($categories as $key => $cat)
                        <a href="{{ route('marketplace.search', ['kategori' => $key]) }}"
                            class="p-2 rounded border border-slate-100 dark:border-white/5 hover:border-[#007AFF] hover:shadow-sm transition-all text-center group flex flex-col items-center justify-start gap-1.5 bg-slate-50/50 dark:bg-white/[0.02]">
                            {{-- Circular Thumbnail --}}
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 flex items-center justify-center group-hover:scale-105 group-hover:bg-[#007AFF]/10 group-hover:text-[#007AFF] transition-all">
                                <i data-lucide="{{ $cat['icon'] }}" class="w-6 h-6 sm:w-7 sm:h-7 stroke-[1.75]"></i>
                            </div>
                            <span class="text-[11px] sm:text-xs text-slate-700 dark:text-slate-200 group-hover:text-[#007AFF] text-center line-clamp-2 leading-tight transition-colors">
                                {{ $cat['label'] }}
                            </span>
                        </a>
                    @endforeach
                </div>

                {{-- Floating Next Arrow Button (Persis Gambar 1) --}}
                <button type="button"
                    onclick="document.getElementById('category-scroll-container').scrollBy({left: 240, behavior: 'smooth'})"
                    class="hidden md:flex absolute right-1 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white dark:bg-[#2C2C2E] border border-slate-200 dark:border-white/20 shadow-md items-center justify-center text-slate-600 dark:text-white hover:bg-slate-50 transition z-10"
                    title="Geser Kategori">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </div>
        </section>

        {{-- Section 4: Produk Pilihan & Terbaru UMKM --}}
        @if ($popularProducts->isNotEmpty())
            <section class="bg-white dark:bg-[#1C1C1E] rounded-sm sm:rounded-md border border-slate-200/80 dark:border-white/10 p-3 sm:p-4 shadow-sm space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 dark:border-white/10 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#007AFF]"></span>
                        <h2 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                            PRODUK TERBARU UMKM
                        </h2>
                    </div>

                    <a href="{{ route('marketplace.sub.products') }}" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                        <span>Lihat Semua Produk</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                {{-- Product Grid 6 Kolom --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 sm:gap-2.5">
                    @foreach ($popularProducts->take(6) as $item)
                        <a href="{{ url('/' . $item->business->slug . '/produk/' . ($item->slug ?: $item->id)) }}"
                            class="group rounded border border-slate-100 dark:border-white/10 bg-white dark:bg-[#1C1C1E] overflow-hidden hover:border-[#007AFF] hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="aspect-square bg-slate-100 dark:bg-[#2C2C2E] overflow-hidden relative">
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                                        <i data-lucide="package" class="w-8 h-8"></i>
                                    </div>
                                @endif
                                @if ($item->is_preorder)
                                    <span class="absolute top-1 right-1 bg-amber-500 text-white font-bold text-[9px] px-1 py-0.5 rounded shadow-sm">
                                        Pre-Order
                                    </span>
                                @elseif ($item->category)
                                    <span class="absolute top-1 right-1 bg-[#060B1E]/80 backdrop-blur-sm text-white font-semibold text-[9px] px-1.5 py-0.5 rounded shadow-sm truncate max-w-[90px]">
                                        {{ $item->category->name }}
                                    </span>
                                @endif
                            </div>

                            <div class="p-2 space-y-1.5">
                                <h3 class="text-xs font-medium text-slate-800 dark:text-slate-100 line-clamp-1 group-hover:text-[#007AFF] transition-colors">
                                    {{ $item->name }}
                                </h3>
                                <p class="text-sm font-bold text-[#007AFF] dark:text-[#38bdf8] font-mono leading-none">
                                    Rp {{ number_format($item->selling_price, 0, ',', '.') }}
                                </p>
                                <div class="text-[10px] text-slate-500 truncate pt-1 border-t border-slate-100 dark:border-white/5">
                                    {{ $item->business->name }}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Section 5: Rekomendasi Produk UMKM (6 Kolom Grid Responsif - 100% Data Riil COOCA) --}}
        <section class="space-y-3">
            {{-- Header Tab Rekomendasi --}}
            <div class="bg-white dark:bg-[#1C1C1E] rounded-sm sm:rounded-md border border-slate-200/80 dark:border-white/10 shadow-sm flex items-center justify-center">
                <div class="border-b-4 border-[#007AFF] text-[#007AFF] font-extrabold text-sm sm:text-base tracking-wider uppercase py-3.5 px-6 inline-flex items-center gap-2">
                    <span>REKOMENDASI PRODUK UMKM</span>
                </div>
            </div>

            {{-- 6-Column Responsive Product Cards Grid (Data Real Skema Dinamis) --}}
            @if ($popularProducts->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 sm:gap-2.5">
                    @foreach ($popularProducts as $idx => $product)
                        <a href="{{ url('/' . $product->business->slug . '/produk/' . ($product->slug ?: $product->id)) }}"
                            class="group bg-white dark:bg-[#1C1C1E] border border-slate-200/90 dark:border-white/10 rounded-sm overflow-hidden hover:border-[#007AFF] hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between shadow-sm relative">

                            {{-- 1:1 Aspect Square Product Photo Container --}}
                            <div class="aspect-square bg-slate-100 dark:bg-[#2C2C2E] overflow-hidden relative">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                                        <i data-lucide="package" class="w-10 h-10"></i>
                                    </div>
                                @endif

                                {{-- Real Status Badges (Pre-Order / Kategori / Jasa) --}}
                                <div class="absolute top-1 right-1 flex flex-col items-end gap-1">
                                    @if ($product->is_preorder)
                                        <span class="bg-amber-500 text-white font-bold text-[9px] px-1.5 py-0.5 rounded shadow-sm">
                                            Pre-Order
                                        </span>
                                    @elseif ($product->type === 'service')
                                        <span class="bg-sky-600 text-white font-bold text-[9px] px-1.5 py-0.5 rounded shadow-sm">
                                            Jasa
                                        </span>
                                    @elseif ($product->category)
                                        <span class="bg-[#060B1E]/80 backdrop-blur-sm text-white font-semibold text-[9px] px-1.5 py-0.5 rounded shadow-sm truncate max-w-[85px]">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Card Details Body --}}
                            <div class="p-2 space-y-1.5 flex-1 flex flex-col justify-between">
                                <div class="space-y-1">
                                    {{-- Toko & Nama Produk --}}
                                    <h3 class="text-xs text-slate-800 dark:text-slate-100 font-normal leading-snug line-clamp-2 group-hover:text-[#007AFF] transition-colors">
                                        @if ($product->business->is_public_listed)
                                            <span class="inline-block bg-[#007AFF] text-white text-[9px] font-bold px-1 py-0.2 rounded-sm mr-1 align-baseline uppercase">
                                                Resmi
                                            </span>
                                        @endif
                                        {{ $product->name }}
                                    </h3>

                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">
                                        {{ $product->business->name }}
                                    </p>
                                </div>

                                <div class="pt-1 space-y-1">
                                    {{-- Price: 'Rp' kecil + Angka Bold --}}
                                    <div class="flex items-baseline gap-0.5 text-[#007AFF] dark:text-[#38bdf8]">
                                        <span class="text-[11px] font-semibold">Rp</span>
                                        <span class="font-extrabold text-sm sm:text-base font-mono leading-none">
                                            {{ number_format($product->selling_price, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    {{-- Footer: Lokasi Kota Penjual --}}
                                    <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-100 dark:border-white/5">
                                        <span class="truncate max-w-[90px]">
                                            {{ $product->business->storeSetting?->city ?: ($product->business->city ?: 'Indonesia') }}
                                        </span>
                                        <span class="shrink-0 text-slate-500 font-medium">Tersedia</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Tombol Lihat Lebih Banyak --}}
                <div class="pt-4 text-center">
                    <a href="{{ route('marketplace.sub.products') }}"
                        class="inline-flex items-center justify-center gap-2 h-10 px-8 rounded bg-white dark:bg-[#1C1C1E] border border-slate-300 dark:border-white/15 hover:border-[#007AFF] text-slate-700 dark:text-slate-200 hover:text-[#007AFF] text-xs sm:text-sm font-semibold shadow-sm transition active:scale-95">
                        <span>Lihat Semua Produk</span>
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </a>
                </div>
            @else
                {{-- Clean Real Empty State (Zero Dummy Data - Skema Otomatis Untuk Masa Mendatang) --}}
                <div class="p-8 sm:p-12 rounded bg-white dark:bg-[#1C1C1E] border border-slate-200/80 dark:border-white/10 text-center space-y-3 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
                        <i data-lucide="package" class="w-6 h-6"></i>
                    </div>
                    <div class="max-w-md mx-auto space-y-1">
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                            Belum Ada Produk yang Ditampilkan
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Produk dari toko yang telah mengaktifkan etalase web dan landing page akan otomatis muncul di feed ini secara real-time.
                        </p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-1.5 px-5 py-2 rounded bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold shadow-sm transition">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>Buka Toko & Pasang Produk Pertama</span>
                        </a>
                    </div>
                </div>
            @endif
        </section>

        {{-- Section 6: Official Stores (TOKO RESMI UMKM / COOCA MALL) --}}
        @if ($featuredStores->isNotEmpty())
            <section class="bg-white dark:bg-[#1C1C1E] rounded-sm sm:rounded-md border border-slate-200/80 dark:border-white/10 p-3 sm:p-4 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-white/10 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                        <h2 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                            TOKO RESMI UMKM • VERIFIED MERCHANTS
                        </h2>
                    </div>
                    <a href="{{ route('marketplace.sub.businesses') }}" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                        <span>Lihat Semua Toko</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                    @foreach ($featuredStores as $store)
                        <a href="{{ url('/' . $store->slug) }}"
                            class="group rounded border border-slate-100 dark:border-white/10 p-3 hover:border-[#007AFF] hover:shadow-sm transition-all flex items-center gap-3 bg-slate-50/50 dark:bg-white/[0.02]">
                            @if ($store->logo_url || $store->landingPage?->logo_url)
                                <img src="{{ $store->logo_url ?: $store->landingPage?->logo_url }}" alt="{{ $store->name }}"
                                    class="w-11 h-11 rounded-lg object-cover border border-slate-200 dark:border-white/10 shrink-0">
                            @else
                                <div class="w-11 h-11 rounded-lg bg-[#007AFF] text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-sm">
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

        {{-- Section 7: Merchant Acquisition Banner (Cooca Theme) --}}
        <section class="relative p-5 sm:p-8 rounded-sm sm:rounded-md bg-gradient-to-br from-[#060B1E] via-[#0A1435] to-[#0A163B] text-white border border-white/10 shadow-lg overflow-hidden text-center space-y-3">
            <div class="absolute -right-20 -top-20 w-72 h-72 bg-[#007AFF]/20 rounded-full blur-[90px] pointer-events-none"></div>
            <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-[#00C4D8]/15 rounded-full blur-[90px] pointer-events-none"></div>

            <div class="relative z-10 space-y-2 max-w-xl mx-auto">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1">
                    <i data-lucide="store" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>PUSAT PENJUAL UMKM INDONESIA</span>
                </span>

                <h2 class="text-lg sm:text-2xl font-extrabold text-white tracking-tight">
                    Mulai Berjualan di COOCA Market Sekarang
                </h2>

                <p class="text-xs text-slate-300 max-w-lg mx-auto leading-relaxed">
                    Sistem otomatis terintegrasi kasir POS, etalase toko online, dan landing page gratis tanpa potongan komisi per transaksi.
                </p>

                <div class="flex flex-wrap items-center justify-center gap-2.5 pt-2">
                    <a href="{{ route('register') }}"
                        class="h-9 px-5 rounded bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 shadow-md active:scale-95 transition">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                        <span>Buka Toko Gratis</span>
                    </a>
                    <a href="{{ route('login') }}"
                        class="h-9 px-5 rounded bg-white/10 hover:bg-white/15 text-white border border-white/15 text-xs font-semibold transition active:scale-95 flex items-center gap-1.5">
                        <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                        <span>Masuk Penjual</span>
                    </a>
                </div>
            </div>
        </section>

    </main>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    });
</script>
@endpush
@endsection
