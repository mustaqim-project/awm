{{--
    Wholesale & Distributor Hero Section (`wholesale_distributor`)
    B2B wholesale, bulk distributor & supplier architecture with volume tiering, armada delivery, and pricelist access.
--}}
<section class="relative overflow-hidden bg-[#F8FAFC] dark:bg-[#0B141B] border-b border-emerald-100 dark:border-emerald-950/60 transition-colors">
    {{-- Commercial Distribution Ambient --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-emerald-500/10 dark:bg-emerald-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-blue-600/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Wholesale Supply Metrics --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 text-xs font-bold text-emerald-800 dark:text-emerald-300">
                    <i data-lucide="boxes" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span class="tracking-wider uppercase text-[10px]">Distributor &amp; Pemasok Grosir Resmi</span>
                </div>

                <h1 class="font-heading font-black text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.1]">
                    {{ $landingPage->headline ?: 'Pasokan Grosir Terlengkap, Harga Pabrik & Pengiriman Armada' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Pusat distribusi terpercaya untuk toko ritel, agen, reseller, dan kebutuhan proyek dengan stok berkelanjutan dan harga bertingkat kompetitif.' }}
                </p>

                {{-- Volume Tiers Badges --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[11px] font-bold bg-white dark:bg-slate-900 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60 shadow-2xs">
                        <i data-lucide="tag" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Harga Bertingkat (Karton &bull; Dus &bull; Palet)</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[11px] font-bold bg-white dark:bg-slate-900 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-900/60 shadow-2xs">
                        <i data-lucide="truck" class="w-3.5 h-3.5 text-blue-600"></i>
                        <span>Armada Truk &amp; Ekspedisi Mandiri</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[11px] font-bold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-800 shadow-2xs">
                        <i data-lucide="file-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Faktur Pajak PPN Resmi</span>
                    </span>
                </div>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/25 transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="layout-grid" class="w-4 h-4"></i>
                            <span>{{ __('storefront.katalog_harga_grosir') }}</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-2xl bg-white dark:bg-slate-900 hover:bg-emerald-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white border border-emerald-200 dark:border-emerald-900/60 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>Chat Divisi Grosir (Pricelist)</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Distribution Center Showcase Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-3xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-emerald-200/80 dark:border-emerald-950/80 bg-white dark:bg-slate-900">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-slate-950 via-emerald-950 to-blue-950 text-white">
                                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-3">
                                    <i data-lucide="warehouse" class="w-8 h-8 text-emerald-400"></i>
                                </div>
                                <h3 class="font-heading font-black text-2xl text-white mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-emerald-200 max-w-xs">Pusat gudang distribusi logistik dan pasokan rantai pasok terpercaya.</p>
                            </div>
                        @endif

                        {{-- Floating Fleet Badge --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-emerald-200/60 dark:border-emerald-900/60 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600">
                                    <i data-lucide="truck" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-heading font-bold text-xs text-slate-900 dark:text-white block">Siap Kirim Tonase Besar</span>
                                    <span class="text-[10px] text-slate-500">Jadwal armada kirim harian</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Cek Rute &rarr;</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
