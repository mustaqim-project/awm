{{--
    Online Store Hero Section (`online_store`)
    Modern high-converting retail hero with promo pill, product discovery CTA, and shopping guarantee badges.
--}}
<section class="relative overflow-hidden bg-white dark:bg-zinc-950 border-b border-zinc-200/80 dark:border-zinc-800 transition-colors">
    {{-- Background Glow --}}
    <div class="absolute -top-32 right-0 w-96 h-96 bg-indigo-500/10 dark:bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-72 h-72 bg-violet-500/10 dark:bg-violet-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            {{-- Text & CTA Column --}}
            <div class="lg:col-span-7 space-y-6">
                {{-- Promo Banner Chip --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700/80 text-xs font-semibold text-zinc-900 dark:text-zinc-100 shadow-2xs">
                    <span class="flex h-2 w-2 rounded-full bg-indigo-600 animate-ping"></span>
                    <span class="text-indigo-600 dark:text-indigo-400 uppercase tracking-wider text-[10px] font-black">Official Store</span>
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                    <span class="font-medium text-zinc-600 dark:text-zinc-300">Koleksi Terbaru &amp; Ready Stock</span>
                </div>

                {{-- Headline --}}
                <h1 class="font-heading font-extrabold text-3xl sm:text-4xl lg:text-5xl text-zinc-950 dark:text-white tracking-tight leading-[1.15]">
                    {{ $landingPage->headline ?: 'Belanja Produk Pilihan, Kualitas Premium & Bergaransi' }}
                </h1>

                <p class="text-sm sm:text-base text-zinc-600 dark:text-zinc-400 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Temukan beragam pilihan produk terbaik dengan jaminan originalitas, proses transaksi aman, dan pengiriman kilat ke seluruh nusantara.' }}
                </p>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-3 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-6 py-3.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-100 dark:text-zinc-900 text-white font-semibold text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                            <span>Jelajahi Etalase</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-5 py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-sm shadow-md transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>Chat Admin Toko</span>
                        </a>
                    @endif
                </div>

                {{-- Trust Pillars --}}
                <div class="grid grid-cols-3 gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800 text-left">
                    <div>
                        <span class="block font-heading font-bold text-base sm:text-lg text-zinc-900 dark:text-white">100% Ori</span>
                        <span class="text-[11px] text-zinc-500 dark:text-zinc-400">Jaminan Asli</span>
                    </div>
                    <div>
                        <span class="block font-heading font-bold text-base sm:text-lg text-zinc-900 dark:text-white">Fast Pack</span>
                        <span class="text-[11px] text-zinc-500 dark:text-zinc-400">Kirim Hari Ini</span>
                    </div>
                    <div>
                        <span class="block font-heading font-bold text-base sm:text-lg text-zinc-900 dark:text-white">Aman</span>
                        <span class="text-[11px] text-zinc-500 dark:text-zinc-400">Garansi Retur</span>
                    </div>
                </div>
            </div>

            {{-- Image / Visual Card Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 shadow-xl aspect-[4/3] sm:aspect-[16/11]">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-br from-zinc-900 to-indigo-950 text-white">
                                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-4 border border-white/20">
                                    <i data-lucide="store" class="w-8 h-8 text-indigo-300"></i>
                                </div>
                                <h3 class="font-heading font-bold text-lg text-white mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-indigo-200/80 max-w-xs">Destinasi belanja pilihan dengan promo spesial setiap minggu.</p>
                            </div>
                        @endif

                        {{-- Floating Flash Badge --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3 rounded-xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border border-zinc-200/80 dark:border-zinc-800 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                    <i data-lucide="zap" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <span class="text-[11px] font-bold text-zinc-900 dark:text-white block">Promo Flash Order</span>
                                    <span class="text-[10px] text-zinc-500">Nikmati subsidi ongkir hari ini</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">Cek Produk &rarr;</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
