{{-- ========================================================================= --}}
{{-- AGRICULTURE & FARM HERO SECTION (`agriculture_farm`)                     --}}
{{-- Agrarian editorial dignity, farm-fresh harvest story & fair-trade pillars  --}}
{{-- ========================================================================= --}}
<section class="relative overflow-hidden bg-gradient-to-b from-emerald-50/80 via-[#F8FAF6] to-white dark:from-[#0A140E] dark:via-[#0F1C14] dark:to-[#0A140E] border-b border-emerald-900/10 dark:border-emerald-500/15 transition-colors">
    {{-- Ambient Botanical Glow Accents --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-emerald-500/15 dark:bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-amber-500/10 dark:bg-amber-500/5 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

            {{-- 1. Text & Agrarian Narrative --}}
            <div class="lg:col-span-7 space-y-6 text-left">
                
                {{-- Morning Picking Live Status Pill --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-700/10 dark:bg-emerald-400/10 border border-emerald-700/20 dark:border-emerald-400/20 text-xs font-semibold text-emerald-900 dark:text-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                    <span class="text-[11px] font-bold tracking-wide">🌾 Petik Segar Subuh Tadi &bull; Langsung dari Lahan Petani</span>
                </div>

                {{-- Hero Title --}}
                <h1 class="font-heading font-normal text-3xl sm:text-4xl md:text-5xl lg:text-6xl text-neutral-900 dark:text-emerald-50 tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Kualitas Panen Terbaik dari Kebun Asri, Segar ke Meja Anda' }}
                </h1>

                {{-- Narrative Subheading --}}
                <p class="text-sm sm:text-base text-neutral-600 dark:text-emerald-200/80 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Menghubungkan kebaikan alam dan jerih payah petani Nusantara. Komoditas panen pilihan, bibit unggul bersertifikat, dan produk organik berkualitas tanpa perantara.' }}
                </p>

                {{-- Quality & Ethical Badges --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-200 border border-emerald-900/10 dark:border-emerald-500/20 shadow-2xs">
                        <i data-lucide="leaf" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                        <span>100% Organik &amp; Bebas Residu</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-emerald-950/60 text-amber-900 dark:text-amber-300 border border-amber-900/10 dark:border-amber-500/20 shadow-2xs">
                        <i data-lucide="sun" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0"></i>
                        <span>Sortir Grade A Pagi Hari</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-200 border border-emerald-900/10 dark:border-emerald-500/20 shadow-2xs">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                        <span>Garansi Tiba Segar</span>
                    </span>
                </div>

                {{-- Dual Action CTAs --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-6 sm:px-7 py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm shadow-md shadow-emerald-800/25 transition-all flex items-center justify-center gap-2 active:scale-95 min-h-[48px]">
                            <i data-lucide="shopping-basket" class="w-4 h-4"></i>
                            <span>Beli Hasil Panen &amp; Bibit</span>
                        </a>
                    @endif

                    @if (!empty($hasWhatsapp))
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-5 sm:px-6 py-3.5 rounded-2xl bg-white dark:bg-emerald-950/50 hover:bg-emerald-50 dark:hover:bg-emerald-900/60 text-neutral-800 dark:text-emerald-100 border border-emerald-900/15 dark:border-emerald-400/20 font-semibold text-sm transition-all flex items-center justify-center gap-2 shadow-2xs min-h-[48px]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                            <span>Pasokan Grosir / Suplai B2B</span>
                        </a>
                    @endif
                </div>

                {{-- Transparency Metrics Strip --}}
                <div class="grid grid-cols-3 gap-3 sm:gap-4 pt-6 border-t border-emerald-900/10 dark:border-emerald-500/15 max-w-lg">
                    <div>
                        <span class="block font-heading text-2xl sm:text-3xl font-bold text-emerald-700 dark:text-emerald-400">05:00</span>
                        <span class="text-[11px] text-neutral-500 dark:text-emerald-300/70 font-medium leading-tight">Jam Petik Segar Subuh</span>
                    </div>
                    <div>
                        <span class="block font-heading text-2xl sm:text-3xl font-bold text-amber-600 dark:text-amber-400">50+ Ha</span>
                        <span class="text-[11px] text-neutral-500 dark:text-emerald-300/70 font-medium leading-tight">Lahan Binaan Mitra</span>
                    </div>
                    <div>
                        <span class="block font-heading text-2xl sm:text-3xl font-bold text-emerald-800 dark:text-emerald-200">100%</span>
                        <span class="text-[11px] text-neutral-500 dark:text-emerald-300/70 font-medium leading-tight">Garansi Ganti Barang</span>
                    </div>
                </div>

            </div>

            {{-- 2. Visual Farm Showcase Card --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    @if ($landingPage->hero_image_url)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-emerald-900/10 dark:border-emerald-500/20 aspect-[4/3] sm:aspect-square">
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 inset-x-4 p-3 rounded-2xl bg-white/90 dark:bg-[#0A140E]/90 backdrop-blur-md border border-white/20 text-xs">
                                <span class="font-bold text-emerald-800 dark:text-emerald-300 block">Kebun Asri Terverifikasi</span>
                                <span class="text-[11px] text-neutral-600 dark:text-neutral-300">Dipetik &amp; dikirim langsung hari ini</span>
                            </div>
                        </div>
                    @else
                        {{-- Farm Harvest Bento Showcase Card --}}
                        <div class="rounded-3xl p-7 sm:p-8 bg-gradient-to-br from-emerald-800 via-emerald-900 to-[#0A140E] text-white shadow-2xl border border-emerald-700/40 relative overflow-hidden">
                            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-emerald-500/20 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="relative z-10 space-y-5">
                                <div class="flex items-center justify-between">
                                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-emerald-300 shadow-sm">
                                        <i data-lucide="trees" class="w-6 h-6"></i>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-amber-400 text-emerald-950 shadow-2xs">
                                        Petik Hari Ini
                                    </span>
                                </div>

                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-widest text-emerald-300">Rantai Pasok Langsung Dari Petani</span>
                                    <h3 class="font-heading font-normal text-2xl sm:text-3xl mt-1 text-white leading-tight">
                                        Segar, Utuh, &amp; Menyehatkan
                                    </h3>
                                    <p class="text-xs text-emerald-100/80 leading-relaxed mt-2">
                                        Rantai pasok ringkas tanpa perantara tengkulak memastikan komoditas tiba di dapur Anda dengan kadar nutrisi tertinggi.
                                    </p>
                                </div>

                                <div class="space-y-2 pt-1 text-xs">
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-300 shrink-0"></i>
                                        <span>Sortir teliti memisahkan grade kualitas prima</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-300 shrink-0"></i>
                                        <span>Kemasan aman dengan sirkulasi udara terkontrol</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-300 shrink-0"></i>
                                        <span>Penggantian 100% jika produk rusak saat transit</span>
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
