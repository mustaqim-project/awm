{{--
    Coffee & Beverage Hero Section (`coffee_beverage`)
    Artisan specialty coffee & cafe roastery with tasting notes, roast level badges, and barista recommendations.
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

<section class="relative overflow-hidden bg-[#FAF7F2] dark:bg-[#15110E] border-b border-[#E8DEC8] dark:border-[#2C211A] transition-colors">
    {{-- Warm Aroma Glow --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-[#8B5A2B]/10 dark:bg-[#C88A58]/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-[#C88A58]/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            {{-- Text & Roastery Details --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#8B5A2B]/10 dark:bg-[#8B5A2B]/20 border border-[#8B5A2B]/20 text-xs font-semibold text-[#8B5A2B] dark:text-[#C88A58]">
                    <span class="w-2 h-2 rounded-full bg-current animate-pulse shrink-0"></span>
                    <span class="tracking-wider uppercase text-[10px] font-bold">{{ $heroEyebrow }}</span>
                </div>

                <h1 class="font-heading font-bold text-3xl sm:text-5xl lg:text-6xl text-[#3D2B1F] dark:text-[#FAF7F2] tracking-tight leading-[1.12]">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-sm sm:text-base text-[#6B5744] dark:text-[#CBBDB1] max-w-xl leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- Badges Strip --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-medium bg-white dark:bg-[#201914] text-[#8B5A2B] dark:text-[#C88A58] border border-[#E8DEC8] dark:border-[#382B21] shadow-2xs">
                        <i data-lucide="{{ $badgeIcon1 }}" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>{{ $badgePill1 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-medium bg-white dark:bg-[#201914] text-[#8B5A2B] dark:text-[#C88A58] border border-[#E8DEC8] dark:border-[#382B21] shadow-2xs">
                        <i data-lucide="{{ $badgeIcon2 }}" class="w-3.5 h-3.5 text-rose-500"></i>
                        <span>{{ $badgePill2 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-medium bg-white dark:bg-[#201914] text-[#8B5A2B] dark:text-[#C88A58] border border-[#E8DEC8] dark:border-[#382B21] shadow-2xs">
                        <i data-lucide="{{ $badgeIcon3 }}" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>{{ $badgePill3 }}</span>
                    </span>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-6 py-3.5 rounded-full theme-btn-primary font-semibold text-sm shadow-md transition-all flex items-center gap-2 active:scale-95 min-h-[48px]">
                            <span>{{ $primaryCtaText }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-5 py-3.5 rounded-full bg-white dark:bg-[#201914] hover:bg-[#F2ECE3] dark:hover:bg-[#2C231C] text-[#3D2B1F] dark:text-[#FAF7F2] border border-[#E8DEC8] dark:border-[#382B21] font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs min-h-[48px]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>{{ $secondaryCtaText }}</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Visual Hero Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-3xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-[#E8DEC8] dark:border-[#2C211A] bg-white dark:bg-[#201914]">
                        @if ($heroImage)
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                            {{-- Floating Status Badge --}}
                            <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-2xl bg-white/95 dark:bg-[#15110E]/95 backdrop-blur-md border border-[#E8DEC8] dark:border-[#2C211A] shadow-lg flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-[#8B5A2B]/15 flex items-center justify-center text-[#8B5A2B] dark:text-[#C88A58]">
                                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <span class="font-heading font-bold text-xs text-[#3D2B1F] dark:text-white block">{{ __('storefront.hero.active_status') }}</span>
                                        <span class="text-[10px] text-stone-500">{{ __('storefront.hero.verified_official') }}</span>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-[#8B5A2B] dark:text-[#C88A58]">{{ __('storefront.features.official_store') }}</span>
                            </div>
                        @else
                            {{-- Brand Bento Graphic Showcase (Universal Fallback) --}}
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-[#3D2B1F] via-[#2A1D15] to-[#15110E] text-white">
                                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-3 border border-white/20">
                                    <span class="font-heading font-bold text-2xl text-[#FAF7F2]">{{ strtoupper(substr($business->name, 0, 2)) }}</span>
                                </div>
                                <h3 class="font-heading font-bold text-2xl text-[#FAF7F2] mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-[#CBBDB1] max-w-xs leading-relaxed">{{ $heroSubheadline }}</p>
                                <span class="mt-4 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider theme-badge">
                                    {{ __('storefront.hero.active_status') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
