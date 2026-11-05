{{--
    Business Corporate Hero Section (`business_corporate`)
    Formal enterprise split layout, B2B credibility metrics, direct RFQ consultation.
--}}
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

<section class="relative overflow-hidden pt-8 pb-16 sm:pt-14 sm:pb-20 border-b border-black/[0.06] dark:border-white/[0.06] bg-slate-50/60 dark:bg-slate-950/40">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            {{-- Col 1: Corporate Copy --}}
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-md text-xs font-semibold tracking-wide uppercase theme-badge border border-black/5 dark:border-white/10">
                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                    <span>{{ $heroEyebrow }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.12]">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    {{ $heroSubheadline }}
                </p>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold theme-btn-primary shadow-sm flex items-center gap-2 min-h-[48px]">
                            <span>{{ $primaryCtaText }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-xs transition flex items-center gap-2 min-h-[48px]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>{{ $secondaryCtaText }}</span>
                        </a>
                    @endif
                </div>

                {{-- Dynamic B2B Trust Metrics Strip (Preserving Institutional Archetype) --}}
                <div class="pt-6 border-t border-slate-200 dark:border-slate-800 grid grid-cols-3 gap-4 text-left">
                    <div>
                        <div class="font-heading font-extrabold text-sm sm:text-base text-slate-900 dark:text-white flex items-center gap-1.5">
                            <i data-lucide="{{ $badgeIcon1 }}" class="w-4 h-4 text-theme-primary"></i>
                            <span>{{ $badgePill1 }}</span>
                        </div>
                        <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('storefront.hero.verified_official') }}</div>
                    </div>
                    <div>
                        <div class="font-heading font-extrabold text-sm sm:text-base text-theme-primary flex items-center gap-1.5">
                            <i data-lucide="{{ $badgeIcon2 }}" class="w-4 h-4"></i>
                            <span>{{ $badgePill2 }}</span>
                        </div>
                        <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('storefront.hero.active_status') }}</div>
                    </div>
                    <div>
                        <div class="font-heading font-extrabold text-sm sm:text-base text-slate-900 dark:text-white flex items-center gap-1.5">
                            <i data-lucide="{{ $badgeIcon3 }}" class="w-4 h-4 text-theme-primary"></i>
                            <span>{{ $badgePill3 }}</span>
                        </div>
                        <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('storefront.badges.quality_guaranteed') }}</div>
                    </div>
                </div>
            </div>

            {{-- Col 2: Corporate Visual --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-xl overflow-hidden shadow-xl border border-slate-200 dark:border-slate-800 aspect-[4/3] sm:aspect-square bg-slate-100 dark:bg-slate-900">
                        @if ($heroImage)
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent"></div>

                            {{-- Corporate Verification Badge on Image --}}
                            <div class="absolute bottom-4 left-4 right-4 p-4 rounded-lg bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 text-xs shadow-md flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-2.5 h-2.5 rounded-full bg-theme-primary animate-pulse"></div>
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ __('storefront.hero.active_status') }}</span>
                                </div>
                                <span class="text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ __('storefront.hero.verified_official') }}</span>
                            </div>
                        @else
                            {{-- Brand Bento Graphic Showcase (Universal Fallback) --}}
                            <div class="w-full h-full p-8 bg-gradient-to-br from-slate-900 to-slate-800 text-white flex flex-col justify-between">
                                <div class="flex items-center justify-between">
                                    <span class="w-14 h-14 rounded-xl flex items-center justify-center font-bold text-2xl text-white shadow-md" style="background-color: var(--theme-primary);">
                                        {{ strtoupper(substr($business->name, 0, 2)) }}
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider theme-badge">
                                        {{ __('storefront.hero.active_status') }}
                                    </span>
                                </div>
                                <div class="space-y-2 mt-auto">
                                    <h3 class="font-heading font-bold text-2xl text-white">{{ $business->name }}</h3>
                                    <p class="text-xs text-slate-300 leading-relaxed">{{ $heroSubheadline }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
