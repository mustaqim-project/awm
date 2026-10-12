{{--
    Bakery & Cake Hero Section (`bakery_cake`)
    Warm golden oven atmosphere, Cormorant Garamond typography, freshly baked sourdough & custom cake orders.
--}}
<section class="relative overflow-hidden bg-[#FFFDF5] dark:bg-[#1A130D] border-b border-amber-200/60 dark:border-amber-950/60 transition-colors">
    {{-- Warm Butter Hearth Glow --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-amber-400/10 dark:bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-rose-400/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            {{-- Text & Craftsmanship --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-100/80 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-900/60 text-xs font-semibold text-amber-900 dark:text-amber-300">
                    <i data-lucide="croissant" class="w-3.5 h-3.5 text-amber-700"></i>
                    <span class="tracking-wider uppercase text-[10px] font-bold">Fresh From The Oven &bull; Pure European Butter</span>
                </div>

                <h1 class="font-heading font-normal text-4xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-amber-50 tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Roti Hangat & Kue Istimewa Dipanggang Setiap Pagi' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-amber-200/70 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Dibuat dengan ragi alami, tepung pilihan dan mentega murni tanpa bahan pengawet. Tersedia roti artisan, pastry renyah, dan kue perayaan custom.' }}
                </p>

                {{-- Freshness Badges --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-zinc-900 text-amber-900 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60 shadow-2xs">
                        <i data-lucide="sun" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>Bake Pukul 06:00 WIB</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-zinc-900 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60 shadow-2xs">
                        <i data-lucide="cake" class="w-3.5 h-3.5 text-rose-500"></i>
                        <span>Pre-Order Custom Cake</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-zinc-900 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60 shadow-2xs">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>100% Halal Certified</span>
                    </span>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-6 py-3.5 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm shadow-md shadow-amber-600/25 transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="cake-slice" class="w-4 h-4"></i>
                            <span>Pilih Roti &amp; Pastry</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-5 py-3.5 rounded-2xl bg-white dark:bg-zinc-900 hover:bg-amber-50 dark:hover:bg-zinc-800 text-slate-800 dark:text-white border border-amber-200 dark:border-amber-900/60 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Pesan Cake Kustom</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Visual Showcase Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-3xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-amber-200/80 dark:border-amber-950/80 bg-white dark:bg-zinc-900">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-amber-900 via-amber-950 to-neutral-950 text-white">
                                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-3">
                                    <i data-lucide="cake" class="w-8 h-8 text-amber-300"></i>
                                </div>
                                <h3 class="font-heading text-3xl text-amber-100 mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-amber-200/80 max-w-xs">Aroma wangi panggangan roti dan kue manis untuk setiap perayaan.</p>
                            </div>
                        @endif

                        {{-- Floating Pastry Badge --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-2xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border border-amber-200/60 dark:border-amber-900/60 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-700">
                                    <i data-lucide="croissant" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-heading font-bold text-xs text-slate-900 dark:text-white block">Fresh Daily Batch</span>
                                    <span class="text-[10px] text-slate-500">Croissant, sourdough &amp; hampers</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-amber-700 dark:text-amber-400">Lihat &rarr;</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
