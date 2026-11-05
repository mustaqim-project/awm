{{--
    manufacturing_factory Hero Section
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

<section class="relative overflow-hidden bg-[#F1F5F9] dark:bg-[#0E131A] border-b border-slate-300 dark:border-slate-800 transition-colors">
    {{-- Industrial Steel Ambient --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-orange-600/10 dark:bg-orange-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-slate-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Industrial Capabilities --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-xs font-bold uppercase tracking-wider">
                    <i data-lucide="factory" class="w-3.5 h-3.5 text-orange-500"></i>
                    <span class="text-[10px]">Pabrik &amp; Fasilitas Manufaktur OEM / ODM</span>
                </div>

                <h1 class="font-heading font-black text-3xl sm:text-5xl lg:text-6xl text-slate-950 dark:text-white tracking-tight leading-[1.08] uppercase">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 max-w-xl leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- Industrial Production Metrics --}}
                <div class="grid grid-cols-3 gap-3 pt-2">
                    <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 shadow-2xs">
                        <span class="font-heading font-black text-lg sm:text-xl text-orange-600 dark:text-orange-400 block">500.000+</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 uppercase font-semibold">Kapasitas / Bulan</span>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 shadow-2xs">
                        <span class="font-heading font-black text-lg sm:text-xl text-slate-900 dark:text-white block">ISO &bull; SNI</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 uppercase font-semibold">Standar Mutu Resmi</span>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 shadow-2xs">
                        <span class="font-heading font-black text-lg sm:text-xl text-emerald-600 dark:text-emerald-400 block">OEM &bull; Maklon</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 uppercase font-semibold">Custom Label Brand</span>
                    </div>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-7 py-3.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm shadow-md transition-all flex items-center gap-2 active:scale-95 uppercase tracking-wider">
                            <i data-lucide="package" class="w-4 h-4"></i>
                            <span>{{ __('storefront.katalog_produk_manufaktur') }}</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-lg bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700 font-bold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="file-text" class="w-4 h-4 text-orange-500"></i>
                            <span>Minta Penawaran (RFQ Maklon)</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Plant Showcase Column --}}

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @if ($heroImage)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-slate-500/30 aspect-square">
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                            <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-white/20 text-xs shadow-lg flex items-center justify-between text-neutral-900 dark:text-white">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="font-bold">{{ __('storefront.hero.active_status') }}</span>
                                </div>
                                <span class="text-slate-300 font-bold font-mono">{{ __('storefront.hero.verified_official') }}</span>
                            </div>
                        </div>
                    @else
                        {{-- Universal Brand Bento Graphic Showcase Card --}}
                        <div class="rounded-3xl p-7 sm:p-8 bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#334155] text-white shadow-2xl border border-slate-500/30 relative overflow-hidden aspect-square flex flex-col justify-between">
                            <div class="absolute -right-10 -bottom-10 w-56 h-56 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="flex items-center justify-between relative z-10">
                                <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-heading font-bold text-2xl text-white shadow-md border border-white/20" style="background-color: var(--theme-primary, #EA580C);">
                                    {{ strtoupper(substr($business->name, 0, 2)) }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 backdrop-blur-md border border-white/20">
                                    {{ __('storefront.hero.active_status') }}
                                </span>
                            </div>

                            <div class="relative z-10 space-y-2 my-auto">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-300 font-mono">{{ __('storefront.hero.verified_official') }}</span>
                                <h3 class="font-heading font-bold text-2xl sm:text-3xl text-white">{{ $business->name }}</h3>
                                <p class="text-xs text-white/80 leading-relaxed font-normal">{{ $heroSubheadline }}</p>
                            </div>

                            <div class="relative z-10 flex flex-wrap gap-2 pt-2 border-t border-white/10 text-xs text-white/90">
                                @foreach ($universalValues as $val)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white/10 backdrop-blur-md">
                                        <i data-lucide="{{ $val['icon'] }}" class="w-3.5 h-3.5 text-slate-300"></i>
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