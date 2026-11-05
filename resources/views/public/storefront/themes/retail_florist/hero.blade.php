{{--
    Florist & Toko Bunga Hero Section (`retail_florist` / `flora_romance`)
    Botanical elegance, Bodoni Moda typography, fresh blooms, greeting cards & bouquet deliveries.
--}}
@php
    $heroHeadline = $landingPage->headline ?: $business->name;
    $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
    $heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));

    $primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
    $primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
    $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
    $secondaryCtaUrl  = $landingPage->cta_secondary_url ?: ($hasWhatsapp ? $landingPage->getWhatsAppUrl() : url('/' . $business->slug . '/kontak'));

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
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold"
                    style="background: {{ $activeTheme['badge_bg'] ?? 'rgba(77, 124, 15, 0.1)' }}; color: {{ $activeTheme['badge_text'] ?? 'var(--theme-primary, #4D7C0F)' }};">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ $heroEyebrow }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white tracking-tight leading-[1.12]">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    {{ $heroSubheadline }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-lime-50 dark:bg-lime-950/40 text-lime-800 dark:text-lime-300 border border-lime-200/60 dark:border-lime-800/40">
                        <i data-lucide="{{ $badgeIcon1 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill1 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-pink-50 dark:bg-pink-950/40 text-pink-700 dark:text-pink-300 border border-pink-200/60 dark:border-pink-800/40">
                        <i data-lucide="{{ $badgeIcon2 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill2 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                        <i data-lucide="{{ $badgeIcon3 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill3 }}</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-semibold rounded-2xl text-white shadow-lg transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5 active:scale-[0.98]"
                            style="background: linear-gradient(135deg, var(--theme-primary, #4D7C0F), #3F6212);">
                            <span>{{ $primaryCtaText }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl border-2 shadow-sm transition-all duration-300 flex items-center gap-2 min-h-[48px] bg-white dark:bg-[#181E2A] text-neutral-800 dark:text-white border-lime-300 dark:border-lime-800/50 hover:bg-lime-50/50">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>{{ $secondaryCtaText }}</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @if ($heroImage)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-lime-100 dark:border-white/10 aspect-square bg-lime-50 dark:bg-lime-950/20">
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                            <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-white/20 text-xs shadow-lg flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="font-semibold text-neutral-900 dark:text-white">{{ __('storefront.hero.active_status') }}</span>
                                </div>
                                <span class="text-lime-700 dark:text-lime-400 font-bold font-mono">{{ __('storefront.hero.verified_official') }}</span>
                            </div>
                        </div>
                    @else
                        {{-- Botanical Artisan Brand Bento Showcase Card (Universal Fallback) --}}
                        <div class="rounded-3xl p-7 sm:p-8 bg-gradient-to-br from-[#1C2E12] via-[#2D4A1D] to-[#3F6212] text-white shadow-2xl border border-lime-500/20 relative overflow-hidden aspect-square flex flex-col justify-between">
                            <div class="absolute -right-10 -bottom-10 w-56 h-56 bg-lime-500/10 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="flex items-center justify-between relative z-10">
                                <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-heading font-serif text-2xl text-white shadow-md border border-white/20" style="background-color: var(--theme-primary, #4D7C0F);">
                                    {{ strtoupper(substr($business->name, 0, 2)) }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-lime-900/60 text-lime-300 border border-lime-500/30">
                                    {{ __('storefront.hero.active_status') }}
                                </span>
                            </div>

                            <div class="relative z-10 space-y-2 my-auto">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-lime-300 font-mono">{{ __('storefront.hero.verified_official') }}</span>
                                <h3 class="font-heading font-serif text-2xl sm:text-3xl text-white">{{ $business->name }}</h3>
                                <p class="text-xs text-lime-100/80 leading-relaxed font-normal">{{ $heroSubheadline }}</p>
                            </div>

                            <div class="relative z-10 flex flex-wrap gap-2 pt-2 border-t border-lime-700/50 text-xs text-lime-100">
                                @foreach ($universalValues as $val)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white/10 backdrop-blur-md">
                                        <i data-lucide="{{ $val['icon'] }}" class="w-3.5 h-3.5 text-lime-300"></i>
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
