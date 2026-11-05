{{--
    Food & Restaurant Hero Section (`food_restaurant`)
    Warm appetite-inducing visual storytelling, chef specialties, dine-in & takeaway reservations.
--}}
@php
    $heroHeadline = $landingPage->headline ?: $business->name;
    $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
    $heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));
    $primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
    $primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
    $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
    $secondaryCtaUrl  = $landingPage->cta_secondary_url ?: ($hasWhatsapp ? $landingPage->getWhatsAppUrl() : url('/' . $business->slug . '/kontak'));

    $cmsValues = $landingPage->getUniversalValues();
    $badgePill1 = $cmsValues[0]['title'] ?? __('storefront.badges.quality_guaranteed');
    $badgeIcon1 = $cmsValues[0]['icon'] ?? 'shield-check';
    $badgePill2 = $cmsValues[1]['title'] ?? __('storefront.badges.secure_transaction');
    $badgeIcon2 = $cmsValues[1]['icon'] ?? 'credit-card';
    $badgePill3 = $cmsValues[2]['title'] ?? __('storefront.badges.fast_service');
    $badgeIcon3 = $cmsValues[2]['icon'] ?? 'truck';

    $heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);
@endphp

<section class="relative overflow-hidden bg-[#FFFDF5] dark:bg-[#18110D] border-b border-amber-100 dark:border-amber-950/60 transition-colors">
    {{-- Warm Hearth Glow --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-red-500/10 dark:bg-red-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-amber-500/10 dark:bg-amber-600/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            {{-- Text & Actions --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-red-100/70 dark:bg-red-950/60 border border-red-200/80 dark:border-red-900/60 text-xs font-semibold text-red-800 dark:text-red-300">
                    <span class="w-2 h-2 rounded-full bg-red-600 dark:bg-red-400 animate-pulse"></span>
                    <span class="tracking-wide uppercase text-[10px] font-bold">{{ $heroEyebrow }}</span>
                </div>

                <h1 class="font-heading font-normal text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-amber-50 tracking-tight leading-[1.15]">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-amber-200/70 max-w-xl leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60">
                        <i data-lucide="{{ $badgeIcon1 }}" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>{{ $badgePill1 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200/60">
                        <i data-lucide="{{ $badgeIcon2 }}" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>{{ $badgePill2 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-red-50 dark:bg-red-950/40 text-red-800 dark:text-red-300 border border-red-200/60">
                        <i data-lucide="{{ $badgeIcon3 }}" class="w-3.5 h-3.5 text-red-600"></i>
                        <span>{{ $badgePill3 }}</span>
                    </span>
                </div>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-6 py-3.5 rounded-2xl theme-btn-primary font-semibold text-sm shadow-md transition-all flex items-center gap-2 active:scale-95 min-h-[48px]">
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            <span>{{ $primaryCtaText }}</span>
                        </a>
                    @endif

                    <a href="{{ $secondaryCtaUrl }}" @if(str_contains($secondaryCtaUrl, 'wa.me') || str_contains($secondaryCtaUrl, 'whatsapp')) target="_blank" rel="noopener" @endif
                        class="px-5 py-3.5 rounded-2xl bg-white dark:bg-zinc-900 hover:bg-amber-50 dark:hover:bg-zinc-800 text-slate-800 dark:text-white border border-amber-200 dark:border-amber-900/60 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs min-h-[48px]">
                        <i data-lucide="message-circle" class="w-4 h-4 text-amber-600"></i>
                        <span>{{ $secondaryCtaText }}</span>
                    </a>
                </div>
            </div>

            {{-- Visual Hero Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-3xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-amber-200/80 dark:border-amber-950/80 bg-white dark:bg-zinc-900">
                        @if ($heroImage)
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-red-900 via-amber-950 to-neutral-950 text-white">
                                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-3">
                                    <span class="font-heading text-2xl font-bold text-amber-400">{{ strtoupper(substr($business->name, 0, 2)) }}</span>
                                </div>
                                <h3 class="font-heading text-2xl text-white mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-amber-200/80 max-w-xs">{{ $heroSubheadline }}</p>
                            </div>
                        @endif

                        {{-- Floating Specialty Card --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-2xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border border-amber-200/60 dark:border-amber-900/60 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-red-950 flex items-center justify-center text-red-600">
                                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-heading font-bold text-xs text-slate-900 dark:text-white block">{{ $heroEyebrow }}</span>
                                    <span class="text-[10px] text-slate-500 dark:text-zinc-400">{{ __('storefront.hero.active_status') }}</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-red-700 dark:text-red-400">{{ __('storefront.badges.verified_official') }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

