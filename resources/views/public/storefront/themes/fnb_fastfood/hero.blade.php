{{--
    Fast Food, Burger & Snack Hero Section (`fnb_fastfood` / `neon_crunch`)
    High-energy vibrant aesthetic, Outfit typography, adaptive to any business.
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
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-black tracking-wider uppercase"
                    style="background: {{ $activeTheme['badge_bg'] ?? 'rgba(220, 38, 38, 0.1)' }}; color: {{ $activeTheme['badge_text'] ?? 'var(--theme-primary, #DC2626)' }};">
                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                    <span>{{ $heroEyebrow }}</span>
                </div>

                <h1 class="font-heading font-black text-4xl sm:text-6xl lg:text-7xl text-neutral-900 dark:text-white tracking-tight leading-[1.05]">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-medium">
                    {{ $heroSubheadline }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-black bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-300">
                        <i data-lucide="{{ $badgeIcon1 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill1 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-black bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                        <i data-lucide="{{ $badgeIcon2 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill2 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-black bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                        <i data-lucide="{{ $badgeIcon3 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill3 }}</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-8 py-4 text-base font-extrabold rounded-2xl text-white shadow-xl transition-all duration-300 flex items-center gap-2 min-h-[50px] hover:scale-105 active:scale-95"
                            style="background: linear-gradient(135deg, var(--theme-primary, #DC2626), #EA580C);">
                            <span>{{ $primaryCtaText }}</span>
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-6 py-4 text-base font-bold rounded-2xl border-2 shadow-sm transition-all duration-300 flex items-center gap-2 min-h-[50px] bg-white dark:bg-[#181E2A] text-neutral-800 dark:text-white border-red-300 dark:border-red-800/50 hover:bg-red-50/50">
                            <i data-lucide="message-circle" class="w-5 h-5 text-emerald-500"></i>
                            <span>{{ $secondaryCtaText }}</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @if ($heroImage)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border-2 border-red-200 dark:border-white/10 aspect-square bg-red-50 dark:bg-red-950/20">
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                            <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md text-xs shadow-lg flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-red-600 animate-ping"></span>
                                    <span class="font-black text-neutral-900 dark:text-white uppercase tracking-wider">{{ __('storefront.hero.active_status') }}</span>
                                </div>
                                <span class="text-red-600 font-black text-sm">{{ __('storefront.hero.verified_official') }}</span>
                            </div>
                        </div>
                    @else
                        {{-- Brand Bento Graphic Showcase Card (Universal Fallback) --}}
                        <div class="rounded-3xl p-7 sm:p-8 bg-gradient-to-br from-[#991B1B] via-[#DC2626] to-[#7F1D1D] text-white shadow-2xl border-2 border-red-400/40 relative overflow-hidden aspect-square flex flex-col justify-between">
                            <div class="absolute -right-10 -bottom-10 w-56 h-56 bg-amber-400/20 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="flex items-center justify-between relative z-10">
                                <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-black text-2xl text-white shadow-md" style="background-color: var(--theme-primary, #DC2626);">
                                    {{ strtoupper(substr($business->name, 0, 2)) }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-white/20 text-white border border-white/30">
                                    {{ __('storefront.hero.active_status') }}
                                </span>
                            </div>

                            <div class="relative z-10 space-y-2 my-auto">
                                <span class="text-[10px] font-black uppercase tracking-widest text-amber-200">{{ __('storefront.hero.verified_official') }}</span>
                                <h3 class="font-heading font-black text-3xl text-white">{{ $business->name }}</h3>
                                <p class="text-xs text-red-100 leading-relaxed font-medium">{{ $heroSubheadline }}</p>
                            </div>

                            <div class="relative z-10 flex flex-wrap gap-2 pt-2 border-t border-white/20 text-xs text-white">
                                @foreach ($universalValues as $val)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-black/20 backdrop-blur-md">
                                        <i data-lucide="{{ $val['icon'] }}" class="w-3.5 h-3.5 text-amber-300"></i>
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
