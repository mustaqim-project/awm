{{-- ========================================================================= --}}
{{-- ARTISAN BAKERY & PATISSERIE HERO SECTION (`bakery_cake`)                  --}}
{{-- Warm Golden Oven Atmosphere, Pure Butter Craftsmanship & Celebration Cake --}}
{{-- ========================================================================= --}}
@php
    $heroHeadline = $landingPage->headline ?: $business->name;
    $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
    $heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? __('storefront.hero.verified_official'));

    $primaryCtaText = $landingPage->cta_primary_text ?: __('storefront.hero.explore_catalog');
    $primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
    $secondaryCtaText = $landingPage->cta_secondary_text ?: __('storefront.hero.contact_us');
    $secondaryCtaUrl  = $landingPage->cta_secondary_url ?: $landingPage->getWhatsAppUrl();

    $universalValues = $landingPage->getUniversalValues();
    $badgePill1 = $universalValues[0]['title'] ?? 'Resep Artisan Asli';
    $badgeIcon1 = $universalValues[0]['icon'] ?? 'sparkles';
    $badgePill2 = $universalValues[1]['title'] ?? 'Bahan Alami & Halal';
    $badgeIcon2 = $universalValues[1]['icon'] ?? 'shield-check';
    $badgePill3 = $universalValues[2]['title'] ?? 'Dipanggang Segar Tiap Hari';
    $badgeIcon3 = $universalValues[2]['icon'] ?? 'flame';

    $stats = $landingPage->stats ?? [];
    $stat1Value = $stats['clients'] ?? '100%';
    $stat1Label = $stats['clients_label'] ?? $badgePill1;
    $stat2Value = $stats['experience'] ?? 'Fresh';
    $stat2Label = $stats['experience_label'] ?? $badgePill2;
    $stat3Value = $stats['rating'] ?? '4.9★';
    $stat3Label = $stats['rating_label'] ?? $badgePill3;

    $heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);
@endphp

<section class="relative overflow-hidden bg-gradient-to-b from-amber-100/60 via-[#FFFDF5] to-white dark:from-[#18110B] dark:via-[#221810] dark:to-[#18110B] border-b border-amber-200/70 dark:border-amber-950/70 transition-colors">
    {{-- Warm Butter & Hearth Hearth Glow Accents --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-amber-400/15 dark:bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-rose-400/10 dark:bg-rose-500/5 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

            {{-- 1. Narrative & Craftsmanship Column --}}
            <div class="lg:col-span-7 space-y-6 text-left">
                
                {{-- Fresh Batch Live Pill --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-800/10 dark:bg-amber-400/10 border border-amber-800/20 dark:border-amber-400/25 text-xs font-semibold text-amber-900 dark:text-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                    <span class="text-[11px] font-bold tracking-wide">{{ $heroEyebrow }}</span>
                </div>

                {{-- Hero Title --}}
                <h1 class="font-heading font-normal text-4xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-amber-50 tracking-tight leading-[1.12]">
                    {{ $heroHeadline }}
                </h1>

                {{-- Narrative Subheading --}}
                <p class="text-sm sm:text-base text-neutral-600 dark:text-amber-200/80 max-w-xl leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- Quality & Craftsmanship Badges --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-amber-950/60 text-amber-900 dark:text-amber-200 border border-amber-900/10 dark:border-amber-500/20 shadow-2xs">
                        <i data-lucide="{{ $badgeIcon1 }}" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0"></i>
                        <span>{{ $badgePill1 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-amber-950/60 text-rose-900 dark:text-rose-300 border border-rose-900/10 dark:border-rose-500/20 shadow-2xs">
                        <i data-lucide="{{ $badgeIcon2 }}" class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400 shrink-0"></i>
                        <span>{{ $badgePill2 }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-amber-950/60 text-emerald-900 dark:text-emerald-200 border border-emerald-900/10 dark:border-emerald-500/20 shadow-2xs">
                        <i data-lucide="{{ $badgeIcon3 }}" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                        <span>{{ $badgePill3 }}</span>
                    </span>
                </div>

                {{-- Dual Action CTAs --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-6 sm:px-7 py-3.5 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm shadow-md shadow-amber-700/25 transition-all flex items-center justify-center gap-2 active:scale-95 min-h-[48px]">
                            <span>{{ $primaryCtaText }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if (!empty($hasWhatsapp))
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-5 sm:px-6 py-3.5 rounded-2xl bg-white dark:bg-amber-950/50 hover:bg-amber-50 dark:hover:bg-amber-900/60 text-neutral-800 dark:text-amber-100 border border-amber-900/15 dark:border-amber-400/25 font-semibold text-sm transition-all flex items-center justify-center gap-2 shadow-2xs min-h-[48px]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                            <span>{{ $secondaryCtaText }}</span>
                        </a>
                    @endif
                </div>

                {{-- Transparency Metrics Strip (Configurable via Landing Page CMS Stats) --}}
                <div class="grid grid-cols-3 gap-3 sm:gap-4 pt-6 border-t border-amber-900/10 dark:border-amber-500/15 max-w-lg">
                    <div class="space-y-0.5">
                        <span class="block font-heading text-2xl sm:text-3xl font-bold text-amber-700 dark:text-amber-400 tabular-nums">{{ $stat1Value }}</span>
                        <span class="text-[11px] text-neutral-500 dark:text-amber-300/70 font-medium leading-tight block">{{ $stat1Label }}</span>
                    </div>
                    <div class="space-y-0.5">
                        <span class="block font-heading text-2xl sm:text-3xl font-bold text-amber-600 dark:text-amber-400 tabular-nums">{{ $stat2Value }}</span>
                        <span class="text-[11px] text-neutral-500 dark:text-amber-300/70 font-medium leading-tight block">{{ $stat2Label }}</span>
                    </div>
                    <div class="space-y-0.5">
                        <span class="block font-heading text-2xl sm:text-3xl font-bold text-rose-700 dark:text-rose-400 tabular-nums">{{ $stat3Value }}</span>
                        <span class="text-[11px] text-neutral-500 dark:text-amber-300/70 font-medium leading-tight block">{{ $stat3Label }}</span>
                    </div>
                </div>

            </div>

            {{-- 2. Visual Showcase Column --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    @if ($heroImage)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-amber-900/10 dark:border-amber-500/20 aspect-[4/3] sm:aspect-square">
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 inset-x-4 p-3.5 rounded-2xl bg-white/92 dark:bg-[#18110B]/92 backdrop-blur-md border border-white/20 text-xs">
                                <span class="font-bold text-amber-800 dark:text-amber-300 block text-sm">{{ __('storefront.hero.active_status') }}</span>
                                <span class="text-[11px] text-neutral-600 dark:text-neutral-300">{{ __('storefront.hero.verified_official') }}</span>
                            </div>
                        </div>
                    @else
                        {{-- Brand Bento Graphic Showcase Card --}}
                        <div class="rounded-3xl p-7 sm:p-8 bg-gradient-to-br from-[#78350F] via-[#92400E] to-[#18110B] text-white shadow-2xl border border-amber-600/40 relative overflow-hidden">
                            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-amber-500/20 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="relative z-10 space-y-5">
                                <div class="flex items-center justify-between">
                                    <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-bold text-2xl text-white shadow-md" style="background-color: var(--theme-primary, #D97706);">
                                        {{ strtoupper(substr($business->name, 0, 2)) }}
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-amber-400 text-amber-950 shadow-2xs">
                                        {{ __('storefront.hero.active_status') }}
                                    </span>
                                </div>

                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-widest text-amber-300">{{ __('storefront.hero.verified_official') }}</span>
                                    <h3 class="font-heading font-normal text-2xl sm:text-3xl mt-1 text-white leading-tight">
                                        {{ $business->name }}
                                    </h3>
                                    <p class="text-xs text-amber-100/80 leading-relaxed mt-2">
                                        {{ $heroSubheadline }}
                                    </p>
                                </div>

                                <div class="space-y-2 pt-1 text-xs">
                                    @foreach ($universalValues as $val)
                                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                                            <i data-lucide="{{ $val['icon'] }}" class="w-4 h-4 text-amber-300 shrink-0"></i>
                                            <span><strong>{{ $val['title'] }}</strong> &bull; {{ $val['desc'] }}</span>
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
