{{--
    Online Store Hero Section (`online_store`)
    Modern high-converting retail hero with promo pill, product discovery CTA, and shopping guarantee badges.
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

<section class="relative overflow-hidden bg-white dark:bg-zinc-950 border-b border-zinc-200/80 dark:border-zinc-800 transition-colors">
    {{-- Background Glow --}}
    <div class="absolute -top-32 right-0 w-96 h-96 bg-theme-primary/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-72 h-72 bg-theme-accent/5 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            {{-- Text & CTA Column --}}
            <div class="lg:col-span-7 space-y-6">
                {{-- Announcement Chip --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700/80 text-xs font-semibold text-zinc-900 dark:text-zinc-100 shadow-2xs">
                    <span class="flex h-2 w-2 rounded-full bg-theme-primary animate-ping"></span>
                    <span class="text-theme-primary uppercase tracking-wider text-[10px] font-black">{{ __('storefront.hero.verified_official') }}</span>
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                    <span class="font-medium text-zinc-600 dark:text-zinc-300">{{ $heroEyebrow }}</span>
                </div>

                {{-- Headline --}}
                <h1 class="font-heading font-extrabold text-3xl sm:text-4xl lg:text-5xl text-zinc-950 dark:text-white tracking-tight leading-[1.15]">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-sm sm:text-base text-zinc-600 dark:text-zinc-400 max-w-xl leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-3 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-6 py-3.5 rounded-xl theme-btn-primary font-semibold text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2 min-h-[48px]">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                            <span>{{ $primaryCtaText }}</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-5 py-3.5 rounded-xl bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-100 border border-zinc-300 dark:border-zinc-700 font-semibold text-sm shadow-xs hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all flex items-center gap-2 min-h-[48px]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>{{ $secondaryCtaText }}</span>
                        </a>
                    @endif
                </div>

                {{-- Dynamic Trust Pillars (Contract §3.1) --}}
                <div class="grid grid-cols-3 gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800 text-left">
                    <div class="flex items-start gap-2">
                        <i data-lucide="{{ $badgeIcon1 }}" class="w-4 h-4 text-theme-primary shrink-0 mt-0.5"></i>
                        <div>
                            <span class="block font-heading font-bold text-xs sm:text-sm text-zinc-900 dark:text-white">{{ $badgePill1 }}</span>
                            <span class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ __('storefront.hero.verified_official') }}</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <i data-lucide="{{ $badgeIcon2 }}" class="w-4 h-4 text-theme-primary shrink-0 mt-0.5"></i>
                        <div>
                            <span class="block font-heading font-bold text-xs sm:text-sm text-zinc-900 dark:text-white">{{ $badgePill2 }}</span>
                            <span class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ __('storefront.hero.active_status') }}</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <i data-lucide="{{ $badgeIcon3 }}" class="w-4 h-4 text-theme-primary shrink-0 mt-0.5"></i>
                        <div>
                            <span class="block font-heading font-bold text-xs sm:text-sm text-zinc-900 dark:text-white">{{ $badgePill3 }}</span>
                            <span class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ __('storefront.badges.quality_guaranteed') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Image / Visual Card Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 shadow-xl aspect-[4/3] sm:aspect-[16/11]">
                        @if ($heroImage)
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-br from-zinc-900 to-zinc-950 text-white">
                                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-4 border border-white/20">
                                    <span class="font-heading font-bold text-2xl text-white">{{ strtoupper(substr($business->name, 0, 2)) }}</span>
                                </div>
                                <h3 class="font-heading font-bold text-lg text-white mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-zinc-300 max-w-xs">{{ $heroSubheadline }}</p>
                            </div>
                        @endif

                        {{-- Floating Status Badge --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3 rounded-xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border border-zinc-200/80 dark:border-zinc-800 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg theme-badge flex items-center justify-center">
                                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <span class="text-[11px] font-bold text-zinc-900 dark:text-white block">{{ __('storefront.hero.active_status') }}</span>
                                    <span class="text-[10px] text-zinc-500">{{ __('storefront.hero.verified_official') }}</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-theme-primary">{{ __('storefront.features.official_store') }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
