{{--
    Modern UMKM Hero Section (`modern_umkm`)
    Friendly, approachable local merchant hero, authentic handmade/local pride, direct WhatsApp ordering.
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

<section class="relative overflow-hidden pt-8 pb-14 sm:pt-14 sm:pb-20 border-b border-black/[0.06] dark:border-white/[0.06]">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            {{-- Copy --}}
            <div class="lg:col-span-7 space-y-5 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold theme-badge shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                    <span>{{ $heroEyebrow }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl text-neutral-900 dark:text-white tracking-tight leading-tight">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-6 py-3.5 text-sm sm:text-base font-bold theme-btn-primary shadow-md flex items-center gap-2 min-h-[48px]">
                            <span>{{ $primaryCtaText }}</span>
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-bold rounded-xl bg-white dark:bg-neutral-800 text-neutral-800 dark:text-white border border-black/10 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-neutral-700 shadow-xs transition flex items-center gap-2 min-h-[48px]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>{{ $secondaryCtaText }}</span>
                        </a>
                    @endif
                </div>

                {{-- Dynamic UMKM Universal Highlights (Contract §3.1) --}}
                <div class="pt-4 flex flex-wrap items-center justify-center lg:justify-start gap-4 sm:gap-6 text-xs text-neutral-600 dark:text-neutral-400 font-medium">
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="{{ $badgeIcon1 }}" class="w-4 h-4 text-theme-primary"></i>
                        <span>{{ $badgePill1 }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="{{ $badgeIcon2 }}" class="w-4 h-4 text-theme-primary"></i>
                        <span>{{ $badgePill2 }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="{{ $badgeIcon3 }}" class="w-4 h-4 text-theme-primary"></i>
                        <span>{{ $badgePill3 }}</span>
                    </div>
                </div>
            </div>

            {{-- Visual --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-2xl overflow-hidden shadow-xl border border-black/5 dark:border-white/10 aspect-square bg-neutral-100 dark:bg-neutral-800">
                        @if ($heroImage)
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                            <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md text-xs shadow-md flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <span class="font-bold text-neutral-900 dark:text-white">{{ __('storefront.hero.active_status') }}</span>
                                </div>
                                <span class="font-bold text-theme-primary">{{ __('storefront.hero.verified_official') }}</span>
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
