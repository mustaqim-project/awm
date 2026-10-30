{{--
    Manufacturing & Factory Hero Section (`manufacturing_factory`)
    Heavy industrial manufacturing aesthetic for factories, OEM/ODM producers, garment makers, and industrial processors.
--}}
<section class="relative overflow-hidden bg-[#F1F5F9] dark:bg-[#0E131A] border-b border-slate-300 dark:border-slate-800 transition-colors">
    {{-- Industrial Steel Ambient --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-orange-600/10 dark:bg-orange-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-slate-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Industrial Capabilities --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-xs font-bold uppercase tracking-wider">
                    <i data-lucide="factory" class="w-3.5 h-3.5 text-orange-500"></i>
                    <span class="text-[10px]">Pabrik &amp; Fasilitas Manufaktur OEM / ODM</span>
                </div>

                <h1 class="font-heading font-black text-3xl sm:text-5xl lg:text-6xl text-slate-950 dark:text-white tracking-tight leading-[1.08] uppercase">
                    {{ $landingPage->headline ?: 'Kapasitas Produksi Skala Besar & Presisi Standar Industri' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Mitra maklon dan manufaktur terpercaya dengan lini mesin otomatis, standar kontrol mutu ISO, serta kapasitas pasokan stabil untuk brand Anda.' }}
                </p>

                {{-- Industrial Production Metrics --}}
                <div class="grid grid-cols-3 gap-3 pt-2">
                    <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 shadow-2xs">
                        <span class="font-heading font-black text-lg sm:text-xl text-orange-600 dark:text-orange-400 block">500.000+</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 uppercase font-semibold">Kapasitas / Bulan</span>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 shadow-2xs">
                        <span class="font-heading font-black text-lg sm:text-xl text-slate-900 dark:text-white block">ISO &bull; SNI</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 uppercase font-semibold">Standar Mutu Resmi</span>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 shadow-2xs">
                        <span class="font-heading font-black text-lg sm:text-xl text-emerald-600 dark:text-emerald-400 block">OEM &bull; Maklon</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 uppercase font-semibold">Custom Label Brand</span>
                    </div>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm shadow-md transition-all flex items-center gap-2 active:scale-95 uppercase tracking-wider">
                            <i data-lucide="package" class="w-4 h-4"></i>
                            <span>{{ __('storefront.katalog_produk_manufaktur') }}</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-lg bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700 font-bold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="file-text" class="w-4 h-4 text-orange-500"></i>
                            <span>Minta Penawaran (RFQ Maklon)</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Plant Showcase Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-2xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-slate-950 via-slate-900 to-orange-950 text-white">
                                <div class="w-16 h-16 rounded-lg bg-orange-600/20 border border-orange-500/30 flex items-center justify-center mb-3 text-orange-400">
                                    <i data-lucide="cpu" class="w-8 h-8"></i>
                                </div>
                                <h3 class="font-heading font-black text-2xl text-white mb-1 uppercase">{{ $business->name }}</h3>
                                <p class="text-xs text-slate-300 max-w-xs">Fasilitas pabrik manufaktur dan lini produksi otomatis berstandar industri.</p>
                            </div>
                        @endif

                        {{-- Floating Certification Badge --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-xl bg-slate-900/95 text-white backdrop-blur-md border border-slate-700 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-orange-600/30 flex items-center justify-center text-orange-400">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-heading font-bold text-xs block">Quality Control Terpadu</span>
                                    <span class="text-[10px] text-slate-400">Inspeksi toleransi presisi tinggi</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-orange-400">SOP Mutu &rarr;</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
