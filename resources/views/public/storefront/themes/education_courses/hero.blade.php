{{--
    education_courses Hero Section
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

<section class="relative overflow-hidden bg-gradient-to-b from-indigo-50/70 via-white to-white dark:from-[#0F172A] dark:via-[#0D1526] dark:to-[#0A101D] border-b border-indigo-100 dark:border-indigo-950/60 transition-colors">
    {{-- Glow Accents --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-indigo-500/10 dark:bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-orange-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Academic Value Proposition --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-100 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800/60 text-xs font-bold text-indigo-800 dark:text-indigo-300">
                    <i data-lucide="award" class="w-3.5 h-3.5 text-indigo-600"></i>
                    <span class="tracking-wider uppercase text-[10px] font-bold">{{ $heroEyebrow }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-4xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.15]">
                    {{ $heroHeadline }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-indigo-200/70 max-w-xl leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- Trust Pillars Pills --}}
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
                            class="px-7 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-md shadow-indigo-600/25 transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                            <span>Jelajahi Pilihan Kelas</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-indigo-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white border border-indigo-200 dark:border-indigo-800 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>Konsultasi Penjurusan / Silabus</span>
                        </a>
                    @endif
                </div>

                {{-- Student Metrics Stats Bar --}}
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-indigo-100 dark:border-indigo-950/80 max-w-lg">
                    <div>
                        <span class="block font-extrabold text-2xl text-indigo-700 dark:text-indigo-400">98%</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Tingkat Kepuasan</span>
                    </div>
                    <div>
                        <span class="block font-extrabold text-2xl text-orange-600 dark:text-orange-400">100+</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Modul Praktik</span>
                    </div>
                    <div>
                        <span class="block font-extrabold text-2xl text-slate-900 dark:text-white">Seumur Hidup</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Akses Rekaman Kelas</span>
                    </div>
                </div>

            </div>

            {{-- Visual Showcase / Course Highlight Board --}}

            <div class="lg:col-span-5">
                <div class="relative">
                    @if ($heroImage)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-indigo-500/30 aspect-square">
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                            <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-white/20 text-xs shadow-lg flex items-center justify-between text-neutral-900 dark:text-white">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="font-bold">{{ __('storefront.hero.active_status') }}</span>
                                </div>
                                <span class="text-indigo-300 font-bold font-mono">{{ __('storefront.hero.verified_official') }}</span>
                            </div>
                        </div>
                    @else
                        {{-- Universal Brand Bento Graphic Showcase Card --}}
                        <div class="rounded-3xl p-7 sm:p-8 bg-gradient-to-br from-[#0F172A] via-[#1E1B4B] to-[#312E81] text-white shadow-2xl border border-indigo-500/30 relative overflow-hidden aspect-square flex flex-col justify-between">
                            <div class="absolute -right-10 -bottom-10 w-56 h-56 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="flex items-center justify-between relative z-10">
                                <span class="w-14 h-14 rounded-2xl flex items-center justify-center font-heading font-bold text-2xl text-white shadow-md border border-white/20" style="background-color: var(--theme-primary, #4F46E5);">
                                    {{ strtoupper(substr($business->name, 0, 2)) }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 backdrop-blur-md border border-white/20">
                                    {{ __('storefront.hero.active_status') }}
                                </span>
                            </div>

                            <div class="relative z-10 space-y-2 my-auto">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-300 font-mono">{{ __('storefront.hero.verified_official') }}</span>
                                <h3 class="font-heading font-bold text-2xl sm:text-3xl text-white">{{ $business->name }}</h3>
                                <p class="text-xs text-white/80 leading-relaxed font-normal">{{ $heroSubheadline }}</p>
                            </div>

                            <div class="relative z-10 flex flex-wrap gap-2 pt-2 border-t border-white/10 text-xs text-white/90">
                                @foreach ($universalValues as $val)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white/10 backdrop-blur-md">
                                        <i data-lucide="{{ $val['icon'] }}" class="w-3.5 h-3.5 text-indigo-300"></i>
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