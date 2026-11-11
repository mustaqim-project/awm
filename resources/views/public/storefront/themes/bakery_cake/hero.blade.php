{{-- ========================================================================= --}}
{{-- ARTISAN BAKERY & PATISSERIE HERO SECTION (`bakery_cake`)                  --}}
{{-- Architectural Grace, Pure Butter Craftsmanship & Culinary Editorial Elegance--}}
{{-- ========================================================================= --}}
@php
    $heroHeadline = $landingPage->headline ?: $business->name;
    $heroSubheadline = $landingPage->subheadline ?: ($business->description ?: __('storefront.hero.tagline'));
    $heroEyebrow = $landingPage->announcement_badge ?: ($activeTheme['industry'] ?? 'Artisan Bakery & Patisserie');

    $primaryCtaText = $landingPage->cta_primary_text ?: 'Jelajahi Menu & Roti';
    $primaryCtaUrl  = $landingPage->cta_primary_url ?: url('/' . $business->slug . '/katalog');
    $secondaryCtaText = $landingPage->cta_secondary_text ?: 'Pesan Custom Cake';
    $secondaryCtaUrl  = $landingPage->cta_secondary_url ?: $landingPage->getWhatsAppUrl();

    $stats = $landingPage->stats ?? [];
    $stat1Value = $stats['clients'] ?? '100%';
    $stat1Label = $stats['clients_label'] ?? 'Mentega Murni';
    $stat2Value = $stats['experience'] ?? 'Fresh';
    $stat2Label = $stats['experience_label'] ?? 'Dipanggang Tiap Pagi';
    $stat3Value = $stats['rating'] ?? '4.9★';
    $stat3Label = $stats['rating_label'] ?? 'Kepuasan Rasa';

    $heroImage = $landingPage->hero_image_url ?: ($landingPage->about_image_url ?: null);

    $universalValues = $landingPage->getUniversalValues();
    $uv1 = $universalValues[0] ?? ['title' => '100% Pure Butter Recipe', 'desc' => 'Tanpa margarin buatan atau pewarna sintetis', 'icon' => 'sparkles'];
    $uv2 = $universalValues[1] ?? ['title' => 'Fresh Morning Batch', 'desc' => 'Dipanggang bertahap setiap jam operasional', 'icon' => 'clock'];
@endphp

<section class="relative overflow-hidden bg-[#FAF8F5] dark:bg-[#14100E] border-b border-stone-200/70 dark:border-stone-800/80 transition-colors duration-300">
    {{-- Soft Atmospheric Hearth Lighting (No harsh neon gradients) --}}
    <div class="absolute top-0 right-1/4 w-[500px] h-[500px] bg-amber-400/[0.04] dark:bg-amber-500/[0.03] rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-[400px] h-[400px] bg-rose-400/[0.03] dark:bg-rose-500/[0.02] rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 pt-10 sm:pt-14 lg:pt-16 pb-12 sm:pb-16 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">

            {{-- 1. Narrative & Craftsmanship Column (Left) --}}
            <div class="lg:col-span-7 space-y-6 sm:space-y-7 text-left">
                
                {{-- Refined Eyebrow Pill --}}
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-900/[0.04] dark:bg-amber-400/[0.06] border border-amber-900/[0.08] dark:border-amber-400/[0.12]">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 dark:bg-amber-400"></span>
                    <span class="text-[11px] font-bold tracking-widest uppercase text-amber-900 dark:text-amber-200">{{ $heroEyebrow }}</span>
                </div>

                {{-- Hero Editorial Title --}}
                <h1 class="font-heading text-4xl sm:text-5xl lg:text-[3.75rem] text-stone-900 dark:text-stone-50 tracking-tight leading-[1.12]">
                    {{ $heroHeadline }}
                </h1>

                {{-- Appetizing Narrative Subheading --}}
                <p class="text-sm sm:text-base text-stone-600 dark:text-stone-300/85 max-w-xl leading-relaxed">
                    {{ $heroSubheadline }}
                </p>

                {{-- Restrained & Intentional CTAs --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ $primaryCtaUrl }}"
                            class="px-6 sm:px-7 py-3.5 rounded-2xl bg-amber-700 hover:bg-amber-800 active:scale-[0.98] text-white font-semibold text-xs sm:text-sm shadow-[0_8px_20px_-4px_rgba(180,83,9,0.35)] transition-all flex items-center justify-center gap-2 min-h-[48px]">
                            <span>{{ $primaryCtaText }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if (!empty($hasWhatsapp))
                        <a href="{{ $secondaryCtaUrl }}" target="_blank" rel="noopener"
                            class="px-5 sm:px-6 py-3.5 rounded-2xl bg-white dark:bg-stone-900 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-800 dark:text-stone-200 border border-stone-300/70 dark:border-stone-700 font-semibold text-xs sm:text-sm transition-all flex items-center justify-center gap-2 active:scale-[0.98] min-h-[48px] shadow-xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                            <span>{{ $secondaryCtaText }}</span>
                        </a>
                    @endif
                </div>

                {{-- Clean Trust Micro-Strip (Integrated Stats from CMS) --}}
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-stone-200/80 dark:border-stone-800/80 max-w-lg">
                    <div>
                        <span class="block font-heading text-2xl sm:text-3xl font-semibold text-amber-800 dark:text-amber-400 tabular-nums">{{ $stat1Value }}</span>
                        <span class="text-[11px] text-stone-500 dark:text-stone-400 font-medium leading-tight block mt-0.5">{{ $stat1Label }}</span>
                    </div>
                    <div>
                        <span class="block font-heading text-2xl sm:text-3xl font-semibold text-stone-800 dark:text-stone-200 tabular-nums">{{ $stat2Value }}</span>
                        <span class="text-[11px] text-stone-500 dark:text-stone-400 font-medium leading-tight block mt-0.5">{{ $stat2Label }}</span>
                    </div>
                    <div>
                        <span class="block font-heading text-2xl sm:text-3xl font-semibold text-stone-800 dark:text-stone-200 tabular-nums">{{ $stat3Value }}</span>
                        <span class="text-[11px] text-stone-500 dark:text-stone-400 font-medium leading-tight block mt-0.5">{{ $stat3Label }}</span>
                    </div>
                </div>

            </div>

            {{-- 2. Visual Showcase Column (Right) --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    @if ($heroImage)
                        {{-- Photographic Editorial Frame --}}
                        <div class="relative rounded-3xl overflow-hidden shadow-[0_24px_50px_-12px_rgba(0,0,0,0.18)] border border-stone-200/80 dark:border-stone-800 aspect-[4/5] sm:aspect-square bg-stone-100 dark:bg-stone-900 group">
                            <img src="{{ $heroImage }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-700 ease-out">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                            
                            {{-- Floating Live Oven Badge --}}
                            <div class="absolute bottom-4 inset-x-4 p-4 rounded-2xl bg-white/95 dark:bg-[#14100E]/95 backdrop-blur-xl border border-white/40 dark:border-stone-800 text-xs shadow-lg flex items-center justify-between">
                                <div class="min-w-0">
                                    <span class="font-heading text-base font-semibold text-stone-900 dark:text-stone-100 block truncate">{{ $business->name }}</span>
                                    <span class="text-[11px] text-amber-800 dark:text-amber-400 font-medium flex items-center gap-1.5 mt-0.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Dapur Aktif &bull; Batch Hangat Tersedia</span>
                                    </span>
                                </div>
                                <span class="px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-200 text-[10px] font-bold uppercase tracking-wider shrink-0 border border-amber-900/10">
                                    Patisserie
                                </span>
                            </div>
                        </div>
                    @else
                        {{-- Curated Artisan Atelier Showcase Card (No Ugly Blank Brown Box!) --}}
                        <div class="relative rounded-3xl p-7 sm:p-9 bg-gradient-to-br from-[#2D1B10] via-[#22150D] to-[#150D08] text-white shadow-[0_24px_60px_-12px_rgba(45,27,16,0.5)] border border-amber-900/40 overflow-hidden">
                            {{-- Soft Warm Glow Accent --}}
                            <div class="absolute -top-16 -right-16 w-48 h-48 bg-amber-500/20 rounded-full blur-3xl pointer-events-none"></div>

                            <div class="relative z-10 space-y-6">
                                {{-- Brand Mark & Heritage Tag --}}
                                <div class="flex items-center justify-between">
                                    <div class="w-12 h-12 rounded-2xl bg-amber-600/30 border border-amber-500/40 text-amber-300 flex items-center justify-center font-heading text-xl font-bold shadow-xs">
                                        {{ strtoupper(substr($business->name, 0, 2)) }}
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase bg-amber-400/20 text-amber-300 border border-amber-400/30">
                                        Artisan Crafted
                                    </span>
                                </div>

                                {{-- Brand & Quality Statement --}}
                                <div class="space-y-1.5">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-amber-400/80">Koleksi Spesial Oven Cooca</span>
                                    <h3 class="font-heading text-2xl sm:text-3xl text-stone-100 font-normal leading-tight">
                                        {{ $business->name }}
                                    </h3>
                                    <p class="text-xs text-stone-300/80 leading-relaxed pt-1">
                                        Setiap roti, sourdough, dan pastry dibuat secara perlahan dengan ragi alami dan mentega berkualitas prima untuk tekstur lembut dan aroma yang kaya.
                                    </p>
                                </div>

                                {{-- 2 Pillars Pill Minimalist (Dynamic from CMS getUniversalValues) --}}
                                <div class="grid grid-cols-1 gap-2.5 pt-1 text-xs">
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.05] border border-white/[0.08] backdrop-blur-sm">
                                        <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-300 flex items-center justify-center shrink-0">
                                            <i data-lucide="{{ $uv1['icon'] ?? 'sparkles' }}" class="w-4 h-4"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-stone-100 text-xs">{{ $uv1['title'] }}</p>
                                            <p class="text-[11px] text-stone-400 truncate">{{ $uv1['desc'] }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.05] border border-white/[0.08] backdrop-blur-sm">
                                        <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-300 flex items-center justify-center shrink-0">
                                            <i data-lucide="{{ $uv2['icon'] ?? 'clock' }}" class="w-4 h-4"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-stone-100 text-xs">{{ $uv2['title'] }}</p>
                                            <p class="text-[11px] text-stone-400 truncate">{{ $uv2['desc'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Bottom Atelier Footer Badge --}}
                                <div class="pt-2 flex items-center justify-between text-[11px] text-amber-300/70 border-t border-white/[0.08]">
                                    <span class="flex items-center gap-1.5">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                                        <span>Higienis &amp; Halal Pilihan</span>
                                    </span>
                                    <span class="font-mono text-stone-400">Oven Fresh Daily</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>
