{{--
    Health & Beauty Hero Section (`health_beauty`)
    Dermatological glow & clinical wellness hero for aesthetic clinics, skincare brands, spas, and wellness studios.
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

<section class="relative overflow-hidden bg-gradient-to-b from-teal-50/60 via-rose-50/20 to-white dark:from-[#0F171A] dark:via-[#111C20] dark:to-[#0A1214] border-b border-teal-100 dark:border-teal-950/60 transition-colors">
    {{-- Glow Accents --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-teal-400/15 dark:bg-teal-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-rose-400/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Clinical Story --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-teal-100/80 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-800/60 text-xs font-semibold text-teal-800 dark:text-teal-300">
                    <span class="w-2 h-2 rounded-full bg-teal-600 dark:bg-teal-400 animate-pulse"></span>
                    <span class="tracking-wider uppercase text-[10px] font-bold">{{ $heroEyebrow }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-4xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.15]">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-teal-200/70 max-w-xl leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-900/60 shadow-2xs">
                        <i data-lucide="{{ $badgeIcon1 }}" class="w-3.5 h-3.5 text-teal-600"></i>
                        <span>{{ $badgePill1 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60 shadow-2xs">
                        <i data-lucide="{{ $badgeIcon2 }}" class="w-3.5 h-3.5 text-rose-600"></i>
                        <span>{{ $badgePill2 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60 shadow-2xs">
                        <i data-lucide="{{ $badgeIcon3 }}" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>{{ $badgePill3 }}</span>
                    </span>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-7 py-3.5 rounded-full theme-btn-primary font-semibold text-sm shadow-md transition-all flex items-center gap-2 active:scale-95 min-h-[48px]">
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            <span>{{ $primaryCtaText }}</span>
                        </a>
                    @endif

                    <a href="{{ $secondaryCtaUrl }}" @if(str_contains($secondaryCtaUrl, 'wa.me') || str_contains($secondaryCtaUrl, 'whatsapp')) target="_blank" rel="noopener" @endif
                        class="px-6 py-3.5 rounded-full bg-white dark:bg-slate-900 hover:bg-teal-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white border border-teal-200 dark:border-teal-800 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs min-h-[48px]">
                        <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                        <span>{{ $secondaryCtaText }}</span>
                    </a>
                </div>

                {{-- Metric Badges --}}
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-teal-100 dark:border-teal-950/80 max-w-lg">
                    <div>
                        <span class="block font-extrabold text-2xl text-teal-700 dark:text-teal-400">100%</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ __('storefront.badges.quality_guaranteed') }}</span>
                    </div>
                    <div>
                        <span class="block font-extrabold text-2xl text-rose-600 dark:text-rose-400">Resmi</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ __('storefront.badges.verified_official') }}</span>
                    </div>
                    <div>
                        <span class="block font-extrabold text-2xl text-slate-900 dark:text-white">24/7</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ __('storefront.badges.fast_service') }}</span>
                    </div>
                </div>

            </div>

            {{-- Visual Showcase / Clinic Aesthetic Card --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    @if ($heroImage)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-teal-100 dark:border-teal-900">
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}"
                                class="w-full h-auto max-h-[480px] object-cover">
                        </div>
                    @else
                        {{-- Wellness Aesthetic Graphic --}}
                        <div class="rounded-3xl p-8 bg-gradient-to-br from-teal-900 via-teal-950 to-slate-950 text-white shadow-2xl border border-teal-800 relative overflow-hidden">
                            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-teal-500/20 rounded-full blur-2xl"></div>

                            <div class="relative z-10 space-y-6">
                                <div class="w-14 h-14 rounded-2xl bg-teal-600/40 border border-teal-400/40 flex items-center justify-center text-teal-200">
                                    <span class="font-heading text-xl font-bold text-teal-300">{{ strtoupper(substr($business->name, 0, 2)) }}</span>
                                </div>

                                <div>
                                    <span class="text-xs font-bold uppercase tracking-widest text-teal-300">{{ $heroEyebrow }}</span>
                                    <h3 class="font-heading font-bold text-2xl mt-1 text-white">{{ $business->name }}</h3>
                                    <p class="text-xs text-teal-200/80 leading-relaxed mt-2">
                                        {{ $heroSubheadline }}
                                    </p>
                                </div>

                                <div class="space-y-2.5 pt-2">
                                    @foreach(array_slice($cmsValues, 0, 3) as $val)
                                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10 text-xs">
                                            <i data-lucide="{{ $val['icon'] ?? 'check' }}" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                            <span>{{ $val['title'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>

