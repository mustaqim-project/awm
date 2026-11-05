{{--
    Coffee Shop & Cafe Hero Section (`fnb_cafe` / `artisan_brew` theme)
    Specialty roastery aesthetic with earthy warm tones & steam wisps.
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

<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-28">
    {{-- Subtle texture background --}}
    <div class="absolute inset-0 opacity-[0.03]"
        style="background-image: url('data:image/svg+xml,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80"><ellipse cx="40" cy="40" rx="18" ry="12" fill="#8B5A2B" transform="rotate(-25 40 40)"/><path d="M28 38 Q40 30 52 38" fill="none" stroke="#8B5A2B" stroke-width="1.5"/></svg>') }}'); background-size: 80px 80px;">
    </div>

    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 items-center">

            {{-- Left: Hero Copy --}}
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                {{-- Origin & Status Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold"
                    style="background: {{ $activeTheme['badge_bg'] ?? 'rgba(139, 90, 43, 0.1)' }}; color: {{ $activeTheme['badge_text'] ?? 'var(--theme-primary, #8B5A2B)' }};">
                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                    <span>{{ $heroEyebrow }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-[3.5rem] tracking-tight leading-[1.1]"
                    style="color: #3D2B1F;">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-base sm:text-lg max-w-2xl mx-auto lg:mx-0 leading-relaxed"
                    style="color: #6B5744;">
                    {{ $heroSubheadline }}
                </p>

                {{-- Dynamic Value Badges --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200"
                        style="background: #FAF7F2; border-color: rgba(200, 138, 88, 0.25); color: #8B5A2B;">
                        <i data-lucide="{{ $badgeIcon1 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill1 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200"
                        style="background: #FAF7F2; border-color: rgba(200, 138, 88, 0.25); color: #8B5A2B;">
                        <i data-lucide="{{ $badgeIcon2 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill2 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200"
                        style="background: #FAF7F2; border-color: rgba(200, 138, 88, 0.25); color: #8B5A2B;">
                        <i data-lucide="{{ $badgeIcon3 }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $badgePill3 }}</span>
                    </span>
                </div>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-semibold rounded-full text-white shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5 active:scale-[0.97]"
                            style="background: linear-gradient(135deg, var(--theme-primary, #8B5A2B), #6B4423);">
                            <span>{{ $primaryCtaText }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-full border-2 shadow-sm hover:shadow-md transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5"
                            style="border-color: #C88A58; color: #8B5A2B; background: rgba(200, 138, 88, 0.08);">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>{{ $secondaryCtaText }}</span>
                        </a>
                    @endif
                </div>

                {{-- Operational Trust Indicator --}}
                <div class="flex items-center justify-center lg:justify-start gap-3 pt-3">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold" style="background: rgba(139, 90, 43, 0.08); color: #8B5A2B;">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>{{ __('storefront.hero.active_status') }}</span>
                    </div>
                    <span class="text-xs text-[#8B5A2B] font-medium">{{ __('storefront.hero.verified_official') }}</span>
                </div>
            </div>

            {{-- Right: Hero Visual Card --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @if ($heroImage)
                        <div class="relative rounded-[24px] overflow-hidden shadow-2xl aspect-[4/5] sm:aspect-[3/4]"
                            style="border: 2px solid rgba(139, 90, 43, 0.12);">
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                            <div class="absolute inset-0"
                                style="background: linear-gradient(180deg, transparent 40%, rgba(61, 43, 31, 0.85) 100%);">
                            </div>

                            {{-- Floating Live Status Card on Image --}}
                            <div
                                class="absolute bottom-4 left-4 right-4 p-4 rounded-[16px] backdrop-blur-xl border text-xs shadow-xl"
                                style="background: rgba(250, 247, 242, 0.92); border-color: rgba(200, 138, 88, 0.2);">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                        <span class="font-semibold" style="color: #3D2B1F;">
                                            {{ __('storefront.hero.active_status') }}
                                        </span>
                                    </div>
                                    <span class="font-mono text-[10px]" style="color: #8B5A2B;">
                                        {{ __('storefront.hero.verified_official') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Brand Bento Graphic Showcase Card (Universal Fallback) --}}
                        <div class="rounded-[24px] p-7 sm:p-8 bg-gradient-to-br from-[#3D2B1F] via-[#2A1D15] to-[#15110E] text-white shadow-2xl relative overflow-hidden aspect-[4/5] sm:aspect-[3/4] flex flex-col justify-between"
                            style="border: 2px solid rgba(200, 138, 88, 0.25);">
                            <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-[#C88A58]/15 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="flex items-center justify-between relative z-10">
                                <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-bold text-2xl text-[#FAF7F2] shadow-md border border-white/20" style="background-color: var(--theme-primary, #8B5A2B);">
                                    {{ strtoupper(substr($business->name, 0, 2)) }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[#C88A58]/20 text-[#C88A58] border border-[#C88A58]/30">
                                    {{ __('storefront.hero.active_status') }}
                                </span>
                            </div>

                            <div class="relative z-10 space-y-2 my-auto">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-[#C88A58]">{{ __('storefront.hero.verified_official') }}</span>
                                <h3 class="font-heading font-extrabold text-2xl sm:text-3xl text-[#FAF7F2]">{{ $business->name }}</h3>
                                <p class="text-xs text-[#CBBDB1] leading-relaxed">{{ $heroSubheadline }}</p>
                            </div>

                            <div class="relative z-10 space-y-2 pt-2 border-t border-white/10 text-xs text-[#FAF7F2]">
                                @foreach ($universalValues as $val)
                                    <div class="flex items-center gap-2.5 p-2 rounded-xl bg-white/5 backdrop-blur-md">
                                        <i data-lucide="{{ $val['icon'] }}" class="w-4 h-4 text-[#C88A58] shrink-0"></i>
                                        <span><strong>{{ $val['title'] }}</strong> &bull; {{ $val['desc'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Decorative Steam Wisps (CSS Animation) --}}
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 pointer-events-none opacity-40">
                        <div class="flex gap-3">
                            <div class="w-1 h-12 rounded-full animate-steam-1"
                                style="background: linear-gradient(to top, #C88A5800, #C88A58, #C88A5800);"></div>
                            <div class="w-1 h-16 rounded-full animate-steam-2"
                                style="background: linear-gradient(to top, #C88A5800, #C88A58, #C88A5800);"></div>
                            <div class="w-1 h-10 rounded-full animate-steam-3"
                                style="background: linear-gradient(to top, #C88A5800, #C88A58, #C88A5800);"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Steam animation keyframes --}}
    <style>
        @keyframes steam-rise-1 {
            0%, 100% { transform: translateY(0) scaleY(1); opacity: 0; }
            25% { opacity: 0.6; }
            50% { transform: translateY(-20px) scaleY(1.2); opacity: 0.3; }
            75% { opacity: 0.1; }
        }
        @keyframes steam-rise-2 {
            0%, 100% { transform: translateY(0) scaleY(1); opacity: 0; }
            30% { opacity: 0.5; }
            60% { transform: translateY(-28px) scaleY(1.3); opacity: 0.2; }
        }
        @keyframes steam-rise-3 {
            0%, 100% { transform: translateY(0) scaleY(1); opacity: 0; }
            20% { opacity: 0.4; }
            70% { transform: translateY(-16px) scaleY(1.1); opacity: 0.15; }
        }
        .animate-steam-1 { animation: steam-rise-1 3s ease-in-out infinite; }
        .animate-steam-2 { animation: steam-rise-2 4s ease-in-out infinite 0.5s; }
        .animate-steam-3 { animation: steam-rise-3 3.5s ease-in-out infinite 1s; }
    </style>
</section>
