{{--
    product_showcase Hero Section
    Universal Storefront Architecture with Dynamic Fallback & Bento Graphic Showcase
--}}
@php
    $heroHeadline = $landingPage->headline ?: $business->name;
    $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
    $heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));

    $primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
    $primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
    $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
    $secondaryCtaUrl  = $landingPage->cta_secondary_url ?: ($hasWhatsapp ? $landingPage->getWhatsAppUrl() : url('/' . $business->slug . '/kontak'));

    $universalValues = $landingPage->getUniversalValues();
    $badgePill1 = $universalValues[0]['title'];
    $badgeIcon1 = $universalValues[0]['icon'];
    $badgePill2 = $universalValues[1]['title'];
    $badgeIcon2 = $universalValues[1]['icon'];
    $badgePill3 = $universalValues[2]['title'];
    $badgeIcon3 = $universalValues[2]['icon'];

    $heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);
@endphp

<section class="relative overflow-hidden bg-[#FAF6F3] dark:bg-[#140F12] border-b border-rose-100 dark:border-rose-950/60 transition-colors">
    {{-- Elegant Ambient Light --}}
    <div class="absolute -top-40 right-1/4 w-[500px] h-[500px] bg-rose-300/15 dark:bg-rose-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-amber-300/10 dark:bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Brand Story --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-900/60 text-xs font-semibold text-rose-700 dark:text-rose-300">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span class="tracking-wider uppercase text-[10px] font-bold">{{ $heroEyebrow }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-rose-50 tracking-tight leading-[1.1]">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-rose-200/70 max-w-xl leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-7 py-3.5 rounded-2xl bg-rose-700 hover:bg-rose-800 text-white font-semibold text-sm shadow-lg shadow-rose-700/25 transition-all flex items-center gap-2 active:scale-95">
                            <span>{{ __('storefront.jelajahi_seri_produk') }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-2xl bg-white dark:bg-zinc-900 hover:bg-rose-50 dark:hover:bg-zinc-800 text-slate-800 dark:text-white border border-rose-200 dark:border-rose-900/60 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>{{ __('storefront.konsultasi_produk_vip') }}</span>
                        </a>
                    @endif
                </div>

                {{-- Key Quality Indicators --}}
                <div class="flex items-center gap-8 pt-6 border-t border-rose-100 dark:border-rose-950/60">
                    <div>
                        <span class="font-heading font-extrabold text-xl text-rose-800 dark:text-rose-300">100%</span>
                        <span class="block text-[11px] text-slate-500 dark:text-rose-300/60">Uji Klinis Terverifikasi</span>
                    </div>
                    <div class="w-px h-8 bg-rose-200 dark:bg-rose-900/60"></div>
                    <div>
                        <span class="font-heading font-extrabold text-xl text-rose-800 dark:text-rose-300">BPOM</span>
                        <span class="block text-[11px] text-slate-500 dark:text-rose-300/60">Sertifikasi Resmi</span>
                    </div>
                    <div class="w-px h-8 bg-rose-200 dark:bg-rose-900/60"></div>
                    <div>
                        <span class="font-heading font-extrabold text-xl text-rose-800 dark:text-rose-300">Cruelty-Free</span>
                        <span class="block text-[11px] text-slate-500 dark:text-rose-300/60">Standar Etis Terpercaya</span>
                    </div>
                </div>
            </div>

            {{-- Spotlight Visual --}}

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @if ($heroImage)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-rose-500/30 aspect-square">
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                            <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-white/20 text-xs shadow-lg flex items-center justify-between text-neutral-900 dark:text-white">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="font-bold">{{ __('storefront.hero.active_status') }}</span>
                                </div>
                                <span class="text-rose-300 font-bold font-mono">{{ __('storefront.hero.verified_official') }}</span>
                            </div>
                        </div>
                    @else
                        {{-- Universal Brand Bento Graphic Showcase Card --}}
                        <div class="rounded-3xl p-7 sm:p-8 bg-gradient-to-br from-[#2A0818] via-[#4C0E2B] to-[#701A44] text-white shadow-2xl border border-rose-500/30 relative overflow-hidden aspect-square flex flex-col justify-between">
                            <div class="absolute -right-10 -bottom-10 w-56 h-56 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="flex items-center justify-between relative z-10">
                                <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-heading font-bold text-2xl text-white shadow-md border border-white/20" style="background-color: var(--theme-primary, #BE185D);">
                                    {{ strtoupper(substr($business->name, 0, 2)) }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 backdrop-blur-md border border-white/20">
                                    {{ __('storefront.hero.active_status') }}
                                </span>
                            </div>

                            <div class="relative z-10 space-y-2 my-auto">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-rose-300 font-mono">{{ __('storefront.hero.verified_official') }}</span>
                                <h3 class="font-heading font-bold text-2xl sm:text-3xl text-white">{{ $business->name }}</h3>
                                <p class="text-xs text-white/80 leading-relaxed font-normal">{{ $heroSubheadline }}</p>
                            </div>

                            <div class="relative z-10 flex flex-wrap gap-2 pt-2 border-t border-white/10 text-xs text-white/90">
                                @foreach ($universalValues as $val)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white/10 backdrop-blur-md">
                                        <i data-lucide="{{ $val['icon'] }}" class="w-3.5 h-3.5 text-rose-300"></i>
                                        <span>{{ $val['title'] }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>