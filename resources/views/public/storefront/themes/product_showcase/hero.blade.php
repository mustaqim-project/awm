{{--
    Product Showcase Hero Section (`product_showcase`)
    Cinematic luxury spotlight for hero products, skincare formulas, and signature innovations.
--}}
<section class="relative overflow-hidden bg-[#FAF6F3] dark:bg-[#140F12] border-b border-rose-100 dark:border-rose-950/60 transition-colors">
    {{-- Elegant Ambient Light --}}
    <div class="absolute -top-40 right-1/4 w-[500px] h-[500px] bg-rose-300/15 dark:bg-rose-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-amber-300/10 dark:bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Brand Story --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-900/60 text-xs font-semibold text-rose-700 dark:text-rose-300">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span class="tracking-wider uppercase text-[11px] font-bold">Signature Collection &bull; Rilis Eksklusif</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-rose-50 tracking-tight leading-[1.1]">
                    {{ $landingPage->headline ?: 'Kemurnian Formula, Hasil Nyata & Elegan' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-rose-200/70 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Diformulasikan khusus dengan bahan baku teruji klinis untuk menghadirkan pengalaman terbaik dan hasil nyata yang tak tertandingi.' }}
                </p>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-2xl bg-rose-700 hover:bg-rose-800 text-white font-semibold text-sm shadow-lg shadow-rose-700/25 transition-all flex items-center gap-2 active:scale-95">
                            <span>Jelajahi Seri Produk</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-2xl bg-white dark:bg-zinc-900 hover:bg-rose-50 dark:hover:bg-zinc-800 text-slate-800 dark:text-white border border-rose-200 dark:border-rose-900/60 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Konsultasi Produk VIP</span>
                        </a>
                    @endif
                </div>

                {{-- Key Quality Indicators --}}
                <div class="flex items-center gap-8 pt-6 border-t border-rose-100 dark:border-rose-950/60">
                    <div>
                        <span class="font-heading font-extrabold text-xl text-rose-800 dark:text-rose-300">100%</span>
                        <span class="block text-[11px] text-slate-500 dark:text-rose-300/60">Uji Klinis Terverifikasi</span>
                    </div>
                    <div class="w-px h-8 bg-rose-200 dark:bg-rose-900/60"></div>
                    <div>
                        <span class="font-heading font-extrabold text-xl text-rose-800 dark:text-rose-300">BPOM</span>
                        <span class="block text-[11px] text-slate-500 dark:text-rose-300/60">Sertifikasi Resmi</span>
                    </div>
                    <div class="w-px h-8 bg-rose-200 dark:bg-rose-900/60"></div>
                    <div>
                        <span class="font-heading font-extrabold text-xl text-rose-800 dark:text-rose-300">Cruelty-Free</span>
                        <span class="block text-[11px] text-slate-500 dark:text-rose-300/60">Standar Etis Terpercaya</span>
                    </div>
                </div>
            </div>

            {{-- Spotlight Visual --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative aspect-[4/5] rounded-3xl overflow-hidden shadow-2xl border border-rose-200/80 dark:border-rose-900/40 bg-white dark:bg-zinc-900">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-b from-rose-100/50 via-rose-50 to-white dark:from-zinc-900 dark:to-zinc-950">
                                <div class="w-20 h-20 rounded-full bg-rose-500/10 dark:bg-rose-500/20 flex items-center justify-center mb-4 text-rose-600 dark:text-rose-300">
                                    <i data-lucide="gem" class="w-10 h-10 stroke-1"></i>
                                </div>
                                <span class="text-xs uppercase tracking-widest font-bold text-rose-500 mb-1">Spotlight Showcase</span>
                                <h3 class="font-heading font-bold text-xl text-slate-900 dark:text-white mb-2">{{ $business->name }}</h3>
                                <p class="text-xs text-slate-500 dark:text-zinc-400 max-w-xs">Pengalaman istimewa dengan kualitas dan kemasan standar premium.</p>
                            </div>
                        @endif

                        {{-- Floating Certificate Badge --}}
                        <div class="absolute bottom-5 left-5 right-5 p-4 rounded-2xl bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border border-rose-100 dark:border-rose-900/60 shadow-lg flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center text-amber-600 shrink-0">
                                <i data-lucide="award" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="font-heading font-bold text-xs text-slate-900 dark:text-white block">Formula Teruji &amp; Aman</span>
                                <span class="text-[11px] text-slate-500 dark:text-zinc-400">Garansi kepuasan pelanggan penuh</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
