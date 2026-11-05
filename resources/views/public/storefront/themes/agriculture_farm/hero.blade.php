{{-- ========================================================================= --}}
{{-- AGRICULTURE & FARM HERO SECTION (`agriculture_farm`)                     --}}
{{-- Agrarian editorial dignity, farm-fresh harvest story & fair-trade pillars  --}}
{{-- ========================================================================= --}}
@php
    $heroHeadline = $landingPage->headline ?: $business->name;
    $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
    $heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));

    $primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
    $primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
    $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
    $secondaryCtaUrl  = $landingPage->cta_secondary_url ?: $landingPage->getWhatsAppUrl();

    $universalValues = $landingPage->getUniversalValues();
    $badgePill1 = $universalValues[0]['title'];
    $badgeIcon1 = $universalValues[0]['icon'];
    $badgePill2 = $universalValues[1]['title'];
    $badgeIcon2 = $universalValues[1]['icon'];
    $badgePill3 = $universalValues[2]['title'];
    $badgeIcon3 = $universalValues[2]['icon'];

    $heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);
@endphp

<section class="relative overflow-hidden bg-gradient-to-b from-emerald-50/70 via-[#F7FAF4] to-white dark:from-[#07130A] dark:via-[#0D1F12] dark:to-[#07130A] border-b border-emerald-900/10 dark:border-emerald-500/15 transition-colors">
    {{-- Ambient Botanical Glow Accents --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-emerald-500/15 dark:bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-amber-500/10 dark:bg-amber-500/5 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

            {{-- 1. Text & Agrarian Narrative --}}
            <div class="lg:col-span-7 space-y-6 text-left">
                
                {{-- Live Status Pill --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-800/10 dark:bg-emerald-400/10 border border-emerald-800/20 dark:border-emerald-400/25 text-xs font-semibold text-emerald-900 dark:text-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                    <span class="text-[11px] font-bold tracking-wide">{{ $heroEyebrow }}</span>
                </div>

                {{-- Hero Title --}}
                <h1 class="font-heading font-normal text-3xl sm:text-4xl md:text-5xl lg:text-6xl text-neutral-900 dark:text-emerald-50 tracking-tight leading-[1.12]">
                    {{ $heroHeadline }}
                </h1>

                {{-- Narrative Subheading --}}
                <p class="text-sm sm:text-base text-neutral-600 dark:text-emerald-200/80 max-w-xl leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- Quality & Value Badges --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-200 border border-emerald-900/10 dark:border-emerald-500/20 shadow-2xs">
                        <i data-lucide="{{ $badgeIcon1 }}" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                        <span>{{ $badgePill1 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-emerald-950/60 text-amber-900 dark:text-amber-300 border border-amber-900/10 dark:border-amber-500/20 shadow-2xs">
                        <i data-lucide="{{ $badgeIcon2 }}" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0"></i>
                        <span>{{ $badgePill2 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-200 border border-emerald-900/10 dark:border-emerald-500/20 shadow-2xs">
                        <i data-lucide="{{ $badgeIcon3 }}" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                        <span>{{ $badgePill3 }}</span>
                    </span>
                </div>

                {{-- Dual Action CTAs --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-6 sm:px-7 py-3.5 rounded-2xl theme-btn-primary font-semibold text-sm shadow-md transition-all flex items-center justify-center gap-2 active:scale-95 min-h-[48px]">
                            <span>{{ $primaryCtaText }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if (!empty($hasWhatsapp))
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-5 sm:px-6 py-3.5 rounded-2xl bg-white dark:bg-emerald-950/50 hover:bg-emerald-50 dark:hover:bg-emerald-900/60 text-neutral-800 dark:text-emerald-100 border border-emerald-900/15 dark:border-emerald-400/25 font-semibold text-sm transition-all flex items-center justify-center gap-2 shadow-2xs min-h-[48px]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                            <span>{{ $secondaryCtaText }}</span>
                        </a>
                    @endif
                </div>

                {{-- Transparency Metrics Strip --}}
                <div class="grid grid-cols-3 gap-3 sm:gap-4 pt-6 border-t border-emerald-900/10 dark:border-emerald-500/15 max-w-lg">
                    <div>
                        <span class="block font-heading text-2xl sm:text-3xl font-bold text-emerald-700 dark:text-emerald-400">100%</span>
                        <span class="text-[11px] text-neutral-500 dark:text-emerald-300/70 font-medium leading-tight">{{ __('storefront.badges.quality_guaranteed') }}</span>
                    </div>
                    <div>
                        <span class="block font-heading text-2xl sm:text-3xl font-bold text-amber-600 dark:text-amber-400">24/7</span>
                        <span class="text-[11px] text-neutral-500 dark:text-emerald-300/70 font-medium leading-tight">{{ __('storefront.hero.active_status') }}</span>
                    </div>
                    <div>
                        <span class="block font-heading text-2xl sm:text-3xl font-bold text-emerald-800 dark:text-emerald-200">Resmi</span>
                        <span class="text-[11px] text-neutral-500 dark:text-emerald-300/70 font-medium leading-tight">{{ __('storefront.features.official_store') }}</span>
                    </div>
                </div>

            </div>

            {{-- 2. Visual Farm Showcase Card --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    @if ($heroImage)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-emerald-900/10 dark:border-emerald-500/20 aspect-[4/3] sm:aspect-square">
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 inset-x-4 p-3.5 rounded-2xl bg-white/92 dark:bg-[#07130A]/92 backdrop-blur-md border border-white/20 text-xs flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="font-bold text-emerald-800 dark:text-emerald-300 block text-sm">{{ __('storefront.hero.active_status') }}</span>
                                </div>
                                <span class="text-[11px] text-neutral-600 dark:text-neutral-300 font-mono">{{ __('storefront.hero.verified_official') }}</span>
                            </div>
                        </div>
                    @else
                        {{-- Farm Harvest Bento Showcase Card (Universal Fallback) --}}
                        <div class="rounded-3xl p-7 sm:p-8 bg-gradient-to-br from-emerald-800 via-emerald-900 to-[#07130A] text-white shadow-2xl border border-emerald-700/40 relative overflow-hidden">
                            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-emerald-500/20 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="relative z-10 space-y-5">
                                <div class="flex items-center justify-between">
                                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-emerald-300 shadow-sm font-heading font-bold text-2xl">
                                        {{ strtoupper(substr($business->name, 0, 2)) }}
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-amber-400 text-emerald-950 shadow-2xs">
                                        {{ __('storefront.hero.active_status') }}
                                    </span>
                                </div>

                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-widest text-emerald-300">{{ __('storefront.hero.verified_official') }}</span>
                                    <h3 class="font-heading font-normal text-2xl sm:text-3xl mt-1 text-white leading-tight">
                                        {{ $business->name }}
                                    </h3>
                                    <p class="text-xs text-emerald-100/80 leading-relaxed mt-2">
                                        {{ $heroSubheadline }}
                                    </p>
                                </div>

                                <div class="space-y-2 pt-1 text-xs">
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                                        <i data-lucide="{{ $badgeIcon1 }}" class="w-4 h-4 text-emerald-300 shrink-0"></i>
                                        <span>{{ $badgePill1 }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                                        <i data-lucide="{{ $badgeIcon2 }}" class="w-4 h-4 text-emerald-300 shrink-0"></i>
                                        <span>{{ $badgePill2 }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                                        <i data-lucide="{{ $badgeIcon3 }}" class="w-4 h-4 text-emerald-300 shrink-0"></i>
                                        <span>{{ $badgePill3 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>
