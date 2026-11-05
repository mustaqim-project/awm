{{--
    Fashion, Apparel & Butik Hero Section (`retail_fashion` / `vogue_minimalist`)
    Editorial clean lookbook, Cormorant Garamond serif, monochrome elegance, premium fabrics.
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

<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-medium tracking-widest uppercase bg-black/5 dark:bg-white/10 text-neutral-900 dark:text-neutral-100 border border-black/10 dark:border-white/10">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    <span>{{ $heroEyebrow }}</span>
                </div>

                <h1 class="font-heading font-light text-4xl sm:text-6xl lg:text-7xl text-neutral-900 dark:text-white tracking-tight leading-[1.08]">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    {{ $heroSubheadline }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-black/5 dark:border-white/10">
                        <i data-lucide="{{ $badgeIcon1 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill1 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-black/5 dark:border-white/10">
                        <i data-lucide="{{ $badgeIcon2 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill2 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-black/5 dark:border-white/10">
                        <i data-lucide="{{ $badgeIcon3 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill3 }}</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-8 py-3.5 text-sm sm:text-base font-medium rounded-full bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 shadow-md hover:opacity-90 transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5">
                            <span>{{ $primaryCtaText }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-7 py-3.5 text-sm sm:text-base font-medium rounded-full border border-black/20 dark:border-white/20 transition-all duration-300 flex items-center gap-2 min-h-[48px] bg-white dark:bg-neutral-900 text-neutral-800 dark:text-white hover:bg-neutral-50 dark:hover:bg-neutral-800">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>{{ $secondaryCtaText }}</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-black/5 dark:border-white/10 aspect-[3/4] bg-neutral-100 dark:bg-neutral-800">
                        @if ($heroImage)
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>

                            <div class="absolute bottom-4 left-4 right-4 p-4 rounded-xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md text-xs shadow-md flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-neutral-900 dark:text-white block">{{ $business->name }}</span>
                                    <span class="text-[11px] text-neutral-500">{{ __('storefront.hero.active_status') }}</span>
                                </div>
                                <span class="text-neutral-900 dark:text-white font-mono font-bold">{{ __('storefront.hero.verified_official') }}</span>
                            </div>
                        @else
                            {{-- Luxe Editorial Brand Bento Card (Universal Fallback) --}}
                            <div class="w-full h-full p-8 bg-gradient-to-br from-neutral-900 to-neutral-800 text-white flex flex-col justify-between">
                                <div class="flex items-center justify-between">
                                    <span class="font-heading font-light text-3xl tracking-widest text-white border-b border-white/20 pb-1">
                                        {{ strtoupper(substr($business->name, 0, 2)) }}
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider theme-badge">
                                        {{ __('storefront.hero.active_status') }}
                                    </span>
                                </div>
                                <div class="space-y-2 mt-auto">
                                    <h3 class="font-heading font-light text-2xl sm:text-3xl text-white">{{ $business->name }}</h3>
                                    <p class="text-xs text-neutral-300 leading-relaxed font-light">{{ $heroSubheadline }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
