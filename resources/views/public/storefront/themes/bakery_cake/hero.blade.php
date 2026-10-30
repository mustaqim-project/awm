{{-- ========================================================================= --}}
{{-- ARTISAN BAKERY & PATISSERIE HERO SECTION (`bakery_cake`)                  --}}
{{-- Warm Golden Oven Atmosphere, Pure Butter Craftsmanship & Celebration Cake --}}
{{-- ========================================================================= --}}
<section class="relative overflow-hidden bg-gradient-to-b from-amber-100/60 via-[#FFFDF5] to-white dark:from-[#18110B] dark:via-[#221810] dark:to-[#18110B] border-b border-amber-200/70 dark:border-amber-950/70 transition-colors">
    {{-- Warm Butter & Hearth Hearth Glow Accents --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-amber-400/15 dark:bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-rose-400/10 dark:bg-rose-500/5 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

            {{-- 1. Narrative & Craftsmanship Column --}}
            <div class="lg:col-span-7 space-y-6 text-left">
                
                {{-- Fresh Oven Batch Live Pill --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-800/10 dark:bg-amber-400/10 border border-amber-800/20 dark:border-amber-400/25 text-xs font-semibold text-amber-900 dark:text-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                    <span class="text-[11px] font-bold tracking-wide">🥖 Dipanggang Pagi Ini &bull; 100% Pure Butter &amp; Ragi Alami</span>
                </div>

                {{-- Hero Title --}}
                <h1 class="font-heading font-normal text-4xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-amber-50 tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Roti Hangat & Kue Istimewa Dipanggang Setiap Pagi' }}
                </h1>

                {{-- Narrative Subheading --}}
                <p class="text-sm sm:text-base text-neutral-600 dark:text-amber-200/80 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Dibuat dengan ragi alami sourdough, tepung terigu pilihan, dan mentega murni berkualitas tinggi tanpa pengawet buatan. Nikmati aneka roti artisan lembut, pastry renyah, dan kue perayaan custom.' }}
                </p>

                {{-- Quality & Craftsmanship Badges --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-amber-950/60 text-amber-900 dark:text-amber-200 border border-amber-900/10 dark:border-amber-500/20 shadow-2xs">
                        <i data-lucide="award" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0"></i>
                        <span>100% Mentega Murni</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-amber-950/60 text-rose-900 dark:text-rose-300 border border-rose-900/10 dark:border-rose-500/20 shadow-2xs">
                        <i data-lucide="cake" class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400 shrink-0"></i>
                        <span>Pre-Order Custom Cake</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-amber-950/60 text-emerald-900 dark:text-emerald-200 border border-emerald-900/10 dark:border-emerald-500/20 shadow-2xs">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                        <span>100% Halal &amp; Tanpa Pengawet</span>
                    </span>
                </div>

                {{-- Dual Action CTAs --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-6 sm:px-7 py-3.5 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm shadow-md shadow-amber-700/25 transition-all flex items-center justify-center gap-2 active:scale-95 min-h-[48px]">
                            <i data-lucide="cake-slice" class="w-4 h-4"></i>
                            <span>{{ __('storefront.pilih_roti_pastry') }}</span>
                        </a>
                    @endif

                    @if (!empty($hasWhatsapp))
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-5 sm:px-6 py-3.5 rounded-2xl bg-white dark:bg-amber-950/50 hover:bg-amber-50 dark:hover:bg-amber-900/60 text-neutral-800 dark:text-amber-100 border border-amber-900/15 dark:border-amber-400/25 font-semibold text-sm transition-all flex items-center justify-center gap-2 shadow-2xs min-h-[48px]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                            <span>Konsultasi Custom Cake</span>
                        </a>
                    @endif
                </div>

                {{-- Transparency Metrics Strip --}}
                <div class="grid grid-cols-3 gap-3 sm:gap-4 pt-6 border-t border-amber-900/10 dark:border-amber-500/15 max-w-lg">
                    <div>
                        <span class="block font-heading text-2xl sm:text-3xl font-bold text-amber-700 dark:text-amber-400">06:00</span>
                        <span class="text-[11px] text-neutral-500 dark:text-amber-300/70 font-medium leading-tight">Batch Pertama Oven</span>
                    </div>
                    <div>
                        <span class="block font-heading text-2xl sm:text-3xl font-bold text-amber-600 dark:text-amber-400">48 Jam</span>
                        <span class="text-[11px] text-neutral-500 dark:text-amber-300/70 font-medium leading-tight">Fermentasi Sourdough</span>
                    </div>
                    <div>
                        <span class="block font-heading text-2xl sm:text-3xl font-bold text-rose-700 dark:text-rose-400">100%</span>
                        <span class="text-[11px] text-neutral-500 dark:text-amber-300/70 font-medium leading-tight">Mentega Alami Murni</span>
                    </div>
                </div>

            </div>

            {{-- 2. Visual Bakery Showcase Column --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    @if ($landingPage->hero_image_url)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-amber-900/10 dark:border-amber-500/20 aspect-[4/3] sm:aspect-square">
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 inset-x-4 p-3.5 rounded-2xl bg-white/92 dark:bg-[#18110B]/92 backdrop-blur-md border border-white/20 text-xs">
                                <span class="font-bold text-amber-800 dark:text-amber-300 block text-sm">Dapur Pembuat Terverifikasi</span>
                                <span class="text-[11px] text-neutral-600 dark:text-neutral-300">Dipanggang segar tiap pagi dengan bahan-bahan premium</span>
                            </div>
                        </div>
                    @else
                        {{-- Patisserie Hearth Bento Showcase Card --}}
                        <div class="rounded-3xl p-7 sm:p-8 bg-gradient-to-br from-[#78350F] via-[#92400E] to-[#18110B] text-white shadow-2xl border border-amber-600/40 relative overflow-hidden">
                            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-amber-500/20 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="relative z-10 space-y-5">
                                <div class="flex items-center justify-between">
                                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-amber-300 shadow-sm">
                                        <i data-lucide="cake-slice" class="w-6 h-6"></i>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-amber-400 text-amber-950 shadow-2xs">
                                        Hangat Dari Oven
                                    </span>
                                </div>

                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-widest text-amber-300">Sentuhan Keahlian Patisserie</span>
                                    <h3 class="font-heading font-normal text-2xl sm:text-3xl mt-1 text-white leading-tight">
                                        Tekstur Lembut, Renyah &amp; Istimewa
                                    </h3>
                                    <p class="text-xs text-amber-100/80 leading-relaxed mt-2">
                                        Menggabungkan tradisi baking Eropa klasik dengan cita rasa nusantara yang menggugah selera untuk setiap momen perayaan Anda.
                                    </p>
                                </div>

                                <div class="space-y-2 pt-1 text-xs">
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                                        <i data-lucide="check" class="w-4 h-4 text-amber-300 shrink-0"></i>
                                        <span>Roti sourdough dengan resep ragi alami tanpa pengembang kimia</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                                        <i data-lucide="check" class="w-4 h-4 text-amber-300 shrink-0"></i>
                                        <span>Cake custom dengan dekorasi krim lembut &amp; buah segar</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                                        <i data-lucide="check" class="w-4 h-4 text-amber-300 shrink-0"></i>
                                        <span>Kotak kemasan eksklusif anti-guncang aman sampai tujuan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>
