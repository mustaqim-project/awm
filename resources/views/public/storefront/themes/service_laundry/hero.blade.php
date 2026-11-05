{{--
    service_laundry Hero Section
    Universal Storefront Architecture with Dynamic Fallback & Bento Graphic Showcase
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

<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-28">
    {{-- Water Bubble Flow Background Pattern --}}
    <div class="absolute inset-0 opacity-[0.04]"
        style="background-image: url('data:image/svg+xml,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60"><circle cx="30" cy="30" r="12" fill="#0284C7"/><circle cx="15" cy="45" r="6" fill="#38BDF8"/><circle cx="48" cy="18" r="4" fill="#0284C7"/></svg>') }}'); background-size: 60px 60px;">
    </div>

    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 items-center">

            {{-- Left: Copy & Value Proposition --}}
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                {{-- Hygiene Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold"
                    style="background: {{ $activeTheme['badge_bg'] ?? '#0284C715' }}; color: {{ $activeTheme['badge_text'] ?? '#0284C7' }};">
                    <span class="w-2 h-2 rounded-full bg-current animate-ping"></span>
                    <span>{{ $heroEyebrow }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-[3.4rem] tracking-tight leading-[1.12] text-neutral-900 dark:text-white">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- Feature Badges Strip --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-white/10 dark:bg-white/5 border border-black/10 dark:border-white/10 shadow-2xs">
                        <i data-lucide="{{ $badgeIcon1 }}" class="w-3.5 h-3.5 text-theme-primary"></i>
                        <span>{{ $badgePill1 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-white/10 dark:bg-white/5 border border-black/10 dark:border-white/10 shadow-2xs">
                        <i data-lucide="{{ $badgeIcon2 }}" class="w-3.5 h-3.5 text-theme-primary"></i>
                        <span>{{ $badgePill2 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-white/10 dark:bg-white/5 border border-black/10 dark:border-white/10 shadow-2xs">
                        <i data-lucide="{{ $badgeIcon3 }}" class="w-3.5 h-3.5 text-theme-primary"></i>
                        <span>{{ $badgePill3 }}</span>
                    </span>
                </div>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-semibold rounded-2xl text-white shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5 active:scale-[0.98]"
                            style="background: linear-gradient(135deg, var(--theme-primary, #0284C7), #0369A1);">
                            <span>{{ $primaryCtaText }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl border-2 shadow-sm hover:shadow-md transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5 bg-white dark:bg-[#181E2A] text-neutral-800 dark:text-white border-sky-500/30">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Jemput Cucian Saya</span>
                        </a>
                    @endif

                    {{-- Track Order Direct Trigger --}}
                    <button type="button" @click="$dispatch('open-order-track-modal')"
                        class="px-4 py-3.5 text-xs sm:text-sm font-semibold rounded-2xl text-sky-700 dark:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-950/30 transition flex items-center gap-1.5 min-h-[48px]">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>{{ __('storefront.cek_status_nota') }}</span>
                    </button>
                </div>
            </div>

            {{-- Right: Visual & Service Highlights Bento --}}

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none space-y-3.5">
                    @if ($heroImage)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-sky-500/30 aspect-square">
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                            <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-white/20 text-xs shadow-lg flex items-center justify-between text-neutral-900 dark:text-white">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="font-bold">{{ __('storefront.hero.active_status') }}</span>
                                </div>
                                <span class="text-sky-300 font-bold font-mono">{{ __('storefront.hero.verified_official') }}</span>
                            </div>
                        </div>
                    @else
                        {{-- Universal Brand Bento Graphic Showcase Card --}}
                        <div class="rounded-3xl p-7 sm:p-8 bg-gradient-to-br from-[#082F49] via-[#0C4A6E] to-[#075985] text-white shadow-2xl border border-sky-500/30 relative overflow-hidden aspect-square flex flex-col justify-between">
                            <div class="absolute -right-10 -bottom-10 w-56 h-56 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="flex items-center justify-between relative z-10">
                                <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-heading font-bold text-2xl text-white shadow-md border border-white/20" style="background-color: var(--theme-primary, #0EA5E9);">
                                    {{ strtoupper(substr($business->name, 0, 2)) }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 backdrop-blur-md border border-white/20">
                                    {{ __('storefront.hero.active_status') }}
                                </span>
                            </div>

                            <div class="relative z-10 space-y-2 my-auto">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-sky-300 font-mono">{{ __('storefront.hero.verified_official') }}</span>
                                <h3 class="font-heading font-bold text-2xl sm:text-3xl text-white">{{ $business->name }}</h3>
                                <p class="text-xs text-white/80 leading-relaxed font-normal">{{ $heroSubheadline }}</p>
                            </div>

                            <div class="relative z-10 flex flex-wrap gap-2 pt-2 border-t border-white/10 text-xs text-white/90">
                                @foreach ($universalValues as $val)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white/10 backdrop-blur-md">
                                        <i data-lucide="{{ $val['icon'] }}" class="w-3.5 h-3.5 text-sky-300"></i>
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