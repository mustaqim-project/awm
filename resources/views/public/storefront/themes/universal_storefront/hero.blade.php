{{--
    Universal Storefront Hero Section (`universal_storefront` / `universal_clean`)
    Apple HIG Bento Modern Architecture, adaptive to any business sector.
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

            {{-- Hero Copy --}}
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold theme-badge shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                    <span>{{ $heroEyebrow }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white tracking-tight leading-[1.15]">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- 3 Universal Badges --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold theme-badge border border-black/5 dark:border-white/10">
                        <i data-lucide="{{ $badgeIcon1 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill1 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold theme-badge border border-black/5 dark:border-white/10">
                        <i data-lucide="{{ $badgeIcon2 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill2 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold theme-badge border border-black/5 dark:border-white/10">
                        <i data-lucide="{{ $badgeIcon3 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill3 }}</span>
                    </span>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-semibold theme-btn-primary shadow-md flex items-center gap-2 min-h-[48px]">
                            <span>{{ $primaryCtaText }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl bg-white dark:bg-neutral-800 text-neutral-800 dark:text-white border border-black/10 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-neutral-700 shadow-xs transition flex items-center gap-2 min-h-[48px]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>{{ $secondaryCtaText }}</span>
                        </a>
                    @endif

                    <button type="button" @click="$dispatch('open-order-track-modal')"
                        class="px-4 py-3.5 text-xs sm:text-sm font-semibold text-neutral-600 dark:text-neutral-400 hover:text-theme-primary transition flex items-center gap-1.5 min-h-[48px]">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>{{ __('storefront.order_tracking') }}</span>
                    </button>
                </div>
            </div>

            {{-- Hero Visual --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-black/5 dark:border-white/10 aspect-[4/3] sm:aspect-square bg-neutral-100 dark:bg-neutral-800">
                        @if ($heroImage)
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                            {{-- Floating Status Pill --}}
                            <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-white/20 dark:border-black/20 text-xs shadow-lg flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                    <span class="font-semibold text-neutral-900 dark:text-white">{{ __('storefront.hero.active_status') }}</span>
                                </div>
                                <span class="text-neutral-500 dark:text-neutral-400 font-mono text-[11px]">{{ __('storefront.hero.verified_official') }}</span>
                            </div>
                        @else
                            {{-- Brand Bento Graphic Showcase (Universal Fallback) --}}
                            <div class="w-full h-full p-8 bg-gradient-to-br from-neutral-900 to-neutral-800 text-white flex flex-col justify-between">
                                <div class="flex items-center justify-between">
                                    <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-bold text-2xl text-white shadow-md" style="background-color: var(--theme-primary);">
                                        {{ strtoupper(substr($business->name, 0, 2)) }}
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider theme-badge">
                                        {{ __('storefront.hero.active_status') }}
                                    </span>
                                </div>
                                <div class="space-y-2 mt-auto">
                                    <h3 class="font-heading font-bold text-2xl text-white">{{ $business->name }}</h3>
                                    <p class="text-xs text-neutral-300 leading-relaxed">{{ $heroSubheadline }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
