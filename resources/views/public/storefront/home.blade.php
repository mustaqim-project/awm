@extends('public.storefront.layouts.app')

@php
    $pageTitle = $landingPage->meta_title ?: ($business->name . ' - ' . ($landingPage->headline ?: 'Toko Online Resmi'));
    $pageDesc = $landingPage->meta_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Selamat datang di toko resmi ' . $business->name . '. Belanja aneka produk dan layanan berkualitas dengan transaksi aman.'));
    $pageDesc = \Illuminate\Support\Str::limit(strip_tags((string) $pageDesc), 155);
    $pageOgImage = $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: ($business->logo_url ?: asset('assets/seo/cooca-og-default.jpg')));
    $pageCanonical = url('/' . $business->slug);

    // Fase 5: Resolve active theme preset for conditional sector-specific partials
    $activeTheme = $theme ?? app(\App\Domain\Storefront\StorefrontThemeService::class)->resolveTheme($landingPage);
    $themePreset = $activeTheme['id'] ?? 'business_corporate';
    $bundleDir = $activeTheme['bundle_dir'] ?? 'business_corporate';
    $isArtisanBrew = in_array($themePreset, ['coffee_beverage', 'artisan_brew'], true);
@endphp

@section('title', $pageTitle)
@section('description', $pageDesc)
@section('canonical', $pageCanonical)
@section('og_title', $business->name . ' | ' . ($landingPage->headline ?: 'Toko Resmi Terverifikasi'))
@section('og_description', $pageDesc)
@section('og_image', $pageOgImage)
@section('og_type', 'website')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Store",
  "name": "{{ addslashes($business->name) }}",
  "description": "{{ addslashes($pageDesc) }}",
  "url": "{{ $pageCanonical }}",
  "telephone": "{{ $landingPage->whatsapp_number ?: ($business->phone ?: '') }}",
  "image": "{{ (str_starts_with((string)$pageOgImage, 'http') ? $pageOgImage : url($pageOgImage)) }}",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "{{ addslashes($landingPage->contact_address ?: ($business->address ?: 'Indonesia')) }}",
    "addressCountry": "ID"
  }
}
</script>
@endpush

@section('content')
    <div class="space-y-16 sm:space-y-24 pb-20">

        {{-- ========================================================================= --}}
        {{-- HERO SECTION (Theme Hero Preset Architecture per 21 Industry Themes)       --}}
        {{-- ========================================================================= --}}
        @if (view()->exists("public.storefront.themes.{$bundleDir}.hero"))
            @include("public.storefront.themes.{$bundleDir}.hero")
        @elseif (view()->exists("public.storefront.themes.modern_umkm.hero"))
            @include("public.storefront.themes.modern_umkm.hero")
        @elseif (view()->exists("public.storefront.themes.universal_storefront.hero"))
            @include("public.storefront.themes.universal_storefront.hero")
        @endif

        {{-- ========================================================================= --}}
        {{-- SECTOR FEATURES BANNER (Distinct Highlights per 21 Industry Themes)       --}}
        {{-- ========================================================================= --}}
        @if (view()->exists("public.storefront.themes.{$bundleDir}.features_banner"))
            @include("public.storefront.themes.{$bundleDir}.features_banner")
        @elseif (view()->exists("public.storefront.themes.modern_umkm.features_banner"))
            @include("public.storefront.themes.modern_umkm.features_banner")
        @endif

        {{-- ========================================================================= --}}
        {{-- CATEGORY SHORTCUT CHIPS (Sector-specific or Universal Fallback)           --}}
        {{-- ========================================================================= --}}
        @if (view()->exists("public.storefront.themes.{$bundleDir}.category_chips"))
            @include("public.storefront.themes.{$bundleDir}.category_chips")
        @elseif (view()->exists("public.storefront.themes.modern_umkm.category_chips"))
            @include("public.storefront.themes.modern_umkm.category_chips")
        @elseif (view()->exists("public.storefront.themes.universal_storefront.category_chips"))
            @include("public.storefront.themes.universal_storefront.category_chips")
        @endif

        {{-- ========================================================================= --}}
        {{-- FEATURED PRODUCTS GRID (Dynamic 21 Themes Product Cards)                  --}}
        {{-- ========================================================================= --}}
        @if ($featuredProducts->isNotEmpty())
            <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                    <div>
                        <div class="inline-block px-3 py-1 rounded-full text-xs font-semibold mb-2"
                            style="background: {{ $activeTheme['badge_bg'] ?? 'rgba(0,0,0,0.05)' }}; color: {{ $activeTheme['badge_text'] ?? 'var(--theme-primary)' }};">
                            <i data-lucide="sparkles" class="w-3 h-3 inline-block mr-0.5"></i> {{ $activeTheme['industry'] ?? 'Koleksi Pilihan' }}
                        </div>
                        <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-neutral-900 dark:text-white">
                            {{ $isArtisanBrew ? 'Kopi Pilihan & Menu Favorit' : 'Produk Terlaris & Rekomendasi' }}
                        </h2>
                    </div>
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl border border-black/10 dark:border-white/10 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-xs sm:text-sm font-medium transition min-h-[40px] text-neutral-800 dark:text-neutral-200">
                            <span>{{ $isArtisanBrew ? 'Lihat Semua Menu' : 'Buka Katalog Lengkap' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($featuredProducts as $item)
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
            </section>
        @endif

        {{-- ========================================================================= --}}
        {{-- SERVICES HIGHLIGHT (If Merchant provides services)                        --}}
        {{-- ========================================================================= --}}
        @if ($services->isNotEmpty())
            <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="p-6 sm:p-10 rounded-theme bg-white dark:bg-neutral-800/60 border border-black/5 dark:border-white/10 shadow-sm">
                    <div class="max-w-2xl mb-8">
                        <div class="inline-block px-3 py-1 rounded-[8px] text-xs font-semibold theme-badge mb-2">Layanan
                            Profesional</div>
                        <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-neutral-900 dark:text-white">
                            Jasa & Perawatan Spesialis
                        </h2>
                        <p class="text-sm text-neutral-500 mt-2">Dikerjakan oleh teknisi andal dan profesional di bidangnya.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($services as $srv)
                            <div
                                class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-black/5 dark:border-white/10 flex flex-col justify-between gap-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span
                                            class="font-heading font-bold text-base text-neutral-900 dark:text-white">{{ $srv->name }}</span>
                                        <span class="text-xs font-bold text-theme-primary"
                                            style="font-variant-numeric: tabular-nums;">
                                            {{ $srv->selling_price > 0 ? 'Rp ' . number_format((float) $srv->selling_price, 0, ',', '.') : 'Tanya Jadwal' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-neutral-600 dark:text-neutral-400 line-clamp-2 leading-relaxed">
                                        {{ $srv->description ?: 'Layanan profesional dengan standar kualitas tinggi.' }}
                                    </p>
                                </div>

                                @if ($landingPage->isPageActive('reservation'))
                                    <a href="{{ url('/' . $business->slug . '/reservasi') }}"
                                        class="inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-[12px] text-xs font-semibold theme-btn-primary shadow-sm min-h-[36px]">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('storefront.book_service') }}</span>
                                    </a>
                                @else
                                    <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                                        class="inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl text-xs font-semibold bg-emerald-500 text-white hover:bg-emerald-600 transition">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                        <span>Konsultasi CS</span>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- ========================================================================= --}}
        {{-- FASE 5: QR DINE-IN ORDER SECTION (artisan_brew / fnb_cafe only)           --}}
        {{-- ========================================================================= --}}
        @if ($bundleDir === 'fnb_cafe' && view()->exists('public.storefront.themes.fnb_cafe.qr_dinein'))
            <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
                @include('public.storefront.themes.fnb_cafe.qr_dinein')
            </section>
        @endif

        {{-- ========================================================================= --}}
        {{-- RESERVATION QUICK BOOKING SECTION (If Enabled & Has Tables)               --}}
        {{-- ========================================================================= --}}
        @if (
            ($storeSetting?->allow_reservation || $landingPage->isPageActive('reservation')) &&
                (!empty($posTables) && $posTables->isNotEmpty()))
            <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="p-6 sm:p-10 rounded-theme bg-white dark:bg-neutral-800/60 border border-black/5 dark:border-white/10 shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                        <div>
                            <div class="inline-block px-3 py-1 rounded-[8px] text-xs font-semibold theme-badge mb-2">
                                Reservasi Tempat</div>
                            <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-neutral-900 dark:text-white">{{ __('storefront.table_and_room_reservation') }}</h2>
                            <p class="text-sm text-neutral-500 mt-1">Pilih meja favorit Anda dan jadwalkan kunjungan Anda
                                lebih awal.</p>
                        </div>
                        @if ($landingPage->isPageActive('reservation'))
                            <a href="{{ url('/' . $business->slug . '/reservasi') }}"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-theme-primary hover:underline">
                                <span>{{ __('storefront.full_form') }}</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($posTables as $tbl)
                            <div
                                class="p-4 rounded-[12px] border border-black/5 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900/60 flex items-center justify-between">
                                <div class="space-y-1">
                                    <div class="font-heading font-bold text-sm text-neutral-900 dark:text-white">
                                        {{ $tbl->name ?: 'Meja ' . $tbl->table_number }}
                                    </div>
                                    <div class="text-xs text-neutral-500">
                                        Kapasitas {{ $tbl->capacity }} Orang
                                    </div>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-[6px] text-[10px] font-semibold bg-emerald-100 text-emerald-700">Tersedia</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- ========================================================================= --}}
        {{-- ABOUT TEASER & STORY & OPERATIONAL HOURS                                  --}}
        {{-- ========================================================================= --}}
        @php
            $normalizedHours = method_exists($landingPage, 'getNormalizedOperationalHours')
                ? $landingPage->getNormalizedOperationalHours()
                : [];
        @endphp
        @if ($landingPage->isPageActive('about') || !empty($normalizedHours))
            <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <div class="{{ !empty($normalizedHours) ? 'lg:col-span-7' : 'lg:col-span-6' }} space-y-4">
                        <div class="inline-block px-3 py-1 rounded-[8px] text-xs font-semibold theme-badge">Cerita Brand
                        </div>
                        <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-neutral-900 dark:text-white">
                            {{ $landingPage->about_title ?: 'Dedikasi & Komitmen ' . $business->name }}
                        </h2>
                        <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-300 leading-relaxed">
                            {{ \Illuminate\Support\Str::limit($landingPage->about_story ?: $business->description, 350) }}
                        </p>
                        @if ($landingPage->isPageActive('about'))
                            <div>
                                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                                    class="inline-flex items-center gap-2 font-semibold text-sm text-theme-primary hover:underline">
                                    <span>{{ __('storefront.read_more_about_us') }}</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        @endif
                    </div>

                    @if (!empty($normalizedHours))
                        <div
                            class="lg:col-span-5 p-6 rounded-[20px] bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm space-y-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-[12px] bg-theme-primary/10 text-theme-primary flex items-center justify-center shrink-0">
                                    <i data-lucide="clock" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="font-heading font-bold text-base text-neutral-900 dark:text-white">Jadwal
                                        Operasional</h3>
                                    <p class="text-xs text-neutral-500">Waktu pelayanan resmi outlet</p>
                                </div>
                            </div>
                            <div class="divide-y divide-black/5 dark:divide-white/5 text-xs">
                                @foreach ($normalizedHours as $h)
                                    <div class="py-2 flex justify-between items-center">
                                        <span
                                            class="font-medium text-neutral-700 dark:text-neutral-300">{{ $h['day'] ?? 'Hari' }}</span>
                                        @if (!empty($h['is_open']))
                                            <span
                                                class="font-semibold text-emerald-600 dark:text-emerald-400 tabular-nums">
                                                {{ $h['hours'] ?: 'Buka' }}
                                            </span>
                                        @else
                                            <span class="font-semibold text-neutral-400">{{ __('storefront.close') }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="lg:col-span-6">
                            <div
                                class="rounded-[20px] overflow-hidden border border-black/5 dark:border-white/10 shadow-lg aspect-video bg-neutral-100 dark:bg-neutral-800">
                                <img src="{{ $landingPage->about_image_url ?: ($landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1000&auto=format&fit=crop&q=80') }}"
                                    alt="{{ __('storefront.tentang_business_name') }}" class="w-full h-full object-cover">
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- ========================================================================= --}}
        {{-- RECENT ARTICLES TEASER (SEO Organic Content (§PRD-07 §2.A.8))             --}}
        {{-- ========================================================================= --}}
        @if ($landingPage->isPageActive('blog') && !empty($recentArticles) && count($recentArticles) > 0)
            <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4 mb-6">
                    <div>
                        <div class="inline-block px-3 py-1 rounded-[8px] text-xs font-semibold theme-badge mb-2">Edukasi &
                            Tips</div>
                        <h2 class="font-heading font-bold text-xl sm:text-2xl text-neutral-900 dark:text-white">Artikel
                            Terbaru</h2>
                    </div>
                    <a href="{{ url('/' . $business->slug . '/artikel') }}"
                        class="text-xs sm:text-sm font-semibold text-theme-primary hover:underline flex items-center gap-1">
                        <span>{{ __('storefront.all_articles') }}</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($recentArticles as $art)
                        <a href="{{ url('/' . $business->slug . '/artikel/' . $art->slug) }}"
                            class="group rounded-[20px] overflow-hidden bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm hover:shadow-md transition flex flex-col">
                            <div class="aspect-video bg-neutral-100 dark:bg-neutral-900 overflow-hidden">
                                @if ($art->cover_image)
                                    <img src="{{ $art->cover_image }}" alt="{{ $art->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-neutral-400">
                                        <i data-lucide="file-text" class="w-10 h-10"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-5 flex flex-col flex-1">
                                <span
                                    class="text-[11px] text-neutral-400 font-medium mb-1">{{ optional($art->published_at)->format('d M Y') }}</span>
                                <h3
                                    class="font-heading font-bold text-base text-neutral-900 dark:text-white group-hover:text-theme-primary transition line-clamp-2 mb-2">
                                    {{ $art->title }}
                                </h3>
                                <p class="text-xs text-neutral-500 line-clamp-2 leading-relaxed mt-auto">
                                    {{ $art->excerpt ?: strip_tags($art->content) }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ========================================================================= --}}
        {{-- STOREFRONT PROMOTIONAL POP-UP MODAL (§PRD-POPUP)                          --}}
        {{-- ========================================================================= --}}
        @if ($landingPage->isPopupActive())
            @php
                $popupCacheKey =
                    'cooca_popup_' .
                    $business->id .
                    '_' .
                    md5(
                        ($landingPage->popup_title ?? '') .
                            ($landingPage->popup_badge ?? '') .
                            ($landingPage->popup_content ?? '') .
                            ($landingPage->updated_at ? $landingPage->updated_at->timestamp : ''),
                    );
                $popupFrequency = $landingPage->popup_frequency ?? 'once_per_day';
            @endphp

            <div x-data="storefrontPopupModal({
                storageKey: '{{ $popupCacheKey }}',
                frequency: '{{ $popupFrequency }}'
            })" x-cloak x-show="isOpen" @keydown.escape.window="close()"
                class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true"
                aria-labelledby="storefront-popup-title">

                {{-- Backdrop with blur and smooth fade --}}
                <div x-show="isOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="close()"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-sm transition-opacity"></div>

                {{-- Centering wrapper --}}
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">

                    {{-- Modal Card --}}
                    <div x-show="isOpen" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        @click.outside="close()"
                        class="relative w-full max-w-lg transform overflow-hidden rounded-[24px] sm:rounded-[28px] bg-white dark:bg-neutral-900 border border-black/10 dark:border-white/10 text-left shadow-2xl transition-all">

                        {{-- Close Button (Top Right X) --}}
                        <button type="button" @click="close()"
                            class="absolute top-3.5 right-3.5 z-20 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center backdrop-blur-md transition-all active:scale-90"
                            aria-label="{{ __('storefront.close_popup') }}">
                            <i data-lucide="x" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </button>

                        {{-- Image Banner (Full bleed top) --}}
                        @if ($landingPage->popup_image_url)
                            <div
                                class="relative w-full aspect-[16/9] sm:aspect-[2/1] bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
                                <img src="{{ $landingPage->popup_image_url }}"
                                    alt="{{ $landingPage->popup_title ?? 'Promosi Toko' }}"
                                    class="w-full h-full object-cover">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent">
                                </div>
                            </div>
                        @endif

                        {{-- Modal Body Content --}}
                        <div class="p-6 sm:p-7 space-y-4">
                            @if ($landingPage->popup_badge)
                                <div
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-[8px] text-xs font-bold theme-badge tracking-wide uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <span>{{ $landingPage->popup_badge }}</span>
                                </div>
                            @endif

                            @if ($landingPage->popup_title)
                                <h3 id="storefront-popup-title"
                                    class="font-heading font-extrabold text-xl sm:text-2xl text-neutral-900 dark:text-white leading-tight">
                                    {{ $landingPage->popup_title }}
                                </h3>
                            @endif

                            @if ($landingPage->popup_content)
                                <div
                                    class="text-sm sm:text-base text-neutral-600 dark:text-neutral-300 leading-relaxed whitespace-pre-line max-h-48 overflow-y-auto">
                                    {{ $landingPage->popup_content }}
                                </div>
                            @endif

                            {{-- Action Buttons --}}
                            <div class="pt-2 flex flex-col sm:flex-row items-center gap-2.5">
                                @if ($landingPage->popup_cta_text && $landingPage->popup_cta_url)
                                    <a href="{{ $landingPage->popup_cta_url }}" @click="close()"
                                        class="w-full sm:flex-1 py-3 px-5 rounded-[12px] theme-btn-primary font-bold text-center text-sm sm:text-base shadow-md transition-all active:scale-[0.98] flex items-center justify-center gap-2 min-h-[48px]">
                                        <span>{{ $landingPage->popup_cta_text }}</span>
                                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                    </a>
                                @endif

                                <button type="button" @click="close()"
                                    class="w-full sm:w-auto py-2.5 px-4 text-xs sm:text-sm font-medium text-neutral-500 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-white transition">{{ __('storefront.close') }}</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <script>
                function storefrontPopupModal(config) {
                    return {
                        isOpen: false,
                        init() {
                            if (!this.shouldShow()) {
                                return;
                            }
                            // Gentle delayed pop-up entrance for premium user experience
                            setTimeout(() => {
                                this.isOpen = true;
                                if (window.lucide) {
                                    window.lucide.createIcons();
                                }
                            }, 600);
                        },
                        shouldShow() {
                            if (config.frequency === 'always') {
                                return true;
                            }
                            if (config.frequency === 'once_per_session') {
                                return sessionStorage.getItem(config.storageKey) !== 'seen';
                            }
                            if (config.frequency === 'once_per_day') {
                                const lastSeen = localStorage.getItem(config.storageKey);
                                if (!lastSeen) return true;
                                const now = new Date().getTime();
                                const oneDayMs = 24 * 60 * 60 * 1000;
                                return (now - parseInt(lastSeen, 10)) > oneDayMs;
                            }
                            return true;
                        },
                        close() {
                            this.isOpen = false;
                            if (config.frequency === 'once_per_session') {
                                sessionStorage.setItem(config.storageKey, 'seen');
                            } else if (config.frequency === 'once_per_day') {
                                localStorage.setItem(config.storageKey, new Date().getTime().toString());
                            }
                        }
                    };
                }
            </script>
        @endif

    </div>
@endsection
