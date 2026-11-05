{{--
    booking_appointment Hero Section
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

{{-- HERO: Booking & Appointment (Salon, Klinik, Studio, Konsultasi & Bengkel) --}}
{{-- ========================================================================= --}}
<section class="relative overflow-hidden bg-gradient-to-b from-[#F5F3FF] via-[#EDE9FE]/40 to-[#F5F3FF] dark:from-[#0E0B17] dark:via-[#161224] dark:to-[#0E0B17] border-b border-purple-200/60 dark:border-purple-900/40 transition-colors">
    {{-- Ambient Violet & Rose Glows --}}
    <div class="absolute -top-32 right-8 w-96 h-96 bg-purple-500/15 dark:bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 -left-20 w-80 h-80 bg-rose-500/10 dark:bg-rose-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

            {{-- Text & Reservation Proposition --}}
            <div class="lg:col-span-7 space-y-6">
                {{-- Live Slot Badge --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-purple-100 dark:bg-purple-950/80 border border-purple-300/80 dark:border-purple-800/80 text-xs font-semibold text-purple-900 dark:text-purple-300 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400"></i>
                    <span class="tracking-wider uppercase text-[10px] font-bold">{{ $heroEyebrow }}</span>
                </div>

                {{-- Headline --}}
                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-purple-100 tracking-tight leading-[1.12]">
                    {{ $heroHeadline }}
                </h1>

                {{-- Subheadline --}}
                <p class="text-sm sm:text-base text-slate-600 dark:text-purple-200/80 max-w-xl leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- Key Trust Metrics --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
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

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="min-h-[48px] px-7 py-3.5 rounded-2xl bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-sm shadow-md shadow-purple-600/30 transition-all flex items-center justify-center gap-2.5 active:scale-95">
                            <i data-lucide="calendar-plus" class="w-4.5 h-4.5"></i>
                            <span>{{ __('storefront.pilih_sesi_layanan') }}</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener noreferrer"
                            class="min-h-[48px] px-6 py-3.5 rounded-2xl bg-white dark:bg-[#161224] hover:bg-purple-50 dark:hover:bg-purple-950/60 text-slate-800 dark:text-purple-200 border border-purple-200 dark:border-purple-800/80 font-bold text-sm transition-all flex items-center justify-center gap-2.5 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4.5 h-4.5 text-emerald-500"></i>
                            <span>Konsultasi via WhatsApp</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Live Interactive Appointment Bento Showcase --}}

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @if ($heroImage)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-purple-500/20 aspect-square">
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                            <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-white/20 text-xs shadow-lg flex items-center justify-between text-neutral-900 dark:text-white">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="font-bold">{{ __('storefront.hero.active_status') }}</span>
                                </div>
                                <span class="text-purple-300 font-bold font-mono">{{ __('storefront.hero.verified_official') }}</span>
                            </div>
                        </div>
                    @else
                        {{-- Universal Brand Bento Graphic Showcase Card --}}
                        <div class="rounded-3xl p-7 sm:p-8 bg-gradient-to-br from-[#2E1065] via-[#3B0764] to-[#581C87] text-white shadow-2xl border border-purple-500/20 relative overflow-hidden aspect-square flex flex-col justify-between">
                            <div class="absolute -right-10 -bottom-10 w-56 h-56 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="flex items-center justify-between relative z-10">
                                <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-heading font-bold text-2xl text-white shadow-md border border-white/20" style="background-color: var(--theme-primary, #9333EA);">
                                    {{ strtoupper(substr($business->name, 0, 2)) }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 backdrop-blur-md border border-white/20">
                                    {{ __('storefront.hero.active_status') }}
                                </span>
                            </div>

                            <div class="relative z-10 space-y-2 my-auto">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-purple-300 font-mono">{{ __('storefront.hero.verified_official') }}</span>
                                <h3 class="font-heading font-bold text-2xl sm:text-3xl text-white">{{ $business->name }}</h3>
                                <p class="text-xs text-white/80 leading-relaxed font-normal">{{ $heroSubheadline }}</p>
                            </div>

                            <div class="relative z-10 flex flex-wrap gap-2 pt-2 border-t border-white/10 text-xs text-white/90">
                                @foreach ($universalValues as $val)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white/10 backdrop-blur-md">
                                        <i data-lucide="{{ $val['icon'] }}" class="w-3.5 h-3.5 text-purple-300"></i>
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