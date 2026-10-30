{{--
    Hospitality & Accommodation Hero Section (`hospitality_accommodation`)
    Resort luxury getaway aesthetic for hotels, villas, guest houses, and private retreats.
--}}
<section class="relative overflow-hidden bg-[#F0FDF4] dark:bg-[#09161C] border-b border-sky-100 dark:border-sky-950/60 transition-colors">
    {{-- Azure & Sunset Warm Glow --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-sky-400/10 dark:bg-sky-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-amber-400/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Getaway Story --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sky-100 dark:bg-sky-950/60 border border-sky-200 dark:border-sky-800/60 text-xs font-semibold text-sky-800 dark:text-sky-300">
                    <i data-lucide="palmtree" class="w-3.5 h-3.5 text-sky-600"></i>
                    <span class="tracking-wider uppercase text-[10px] font-bold">Resort &bull; Villa &bull; Penginapan Eksklusif</span>
                </div>

                <h1 class="font-heading font-normal text-4xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.1]">
                    {{ $landingPage->headline ?: 'Kenyamanan Istimewa, Suasana Tenang & Liburan Berkesan' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-sky-200/70 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Lepaskan penat dalam keindahan alam yang memikat dengan fasilitas kamar premium, keramahan pelayanan terbaik, dan lokasi strategis.' }}
                </p>

                {{-- Amenity Badges --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-sky-800 dark:text-sky-300 border border-sky-200 dark:border-sky-900/60 shadow-2xs">
                        <i data-lucide="wifi" class="w-3.5 h-3.5 text-sky-600"></i>
                        <span>High-Speed Wi-Fi</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60 shadow-2xs">
                        <i data-lucide="coffee" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Breakfast Termasuk</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60 shadow-2xs">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Kebersihan Terstandarisasi</span>
                    </span>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-full bg-sky-600 hover:bg-sky-700 text-white font-semibold text-sm shadow-md shadow-sky-600/25 transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="bed-double" class="w-4 h-4"></i>
                            <span>{{ __('storefront.pilih_tipe_kamar_villa') }}</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-full bg-white dark:bg-slate-900 hover:bg-sky-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white border border-sky-200 dark:border-sky-800 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="calendar" class="w-4 h-4 text-amber-600"></i>
                            <span>Cek Tanggal Check-in</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Room Showcase Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-3xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-sky-200/80 dark:border-sky-950/80 bg-white dark:bg-slate-900">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-sky-900 via-slate-950 to-teal-950 text-white">
                                <div class="w-16 h-16 rounded-full bg-sky-500/20 border border-sky-400/30 flex items-center justify-center mb-3 text-sky-300">
                                    <i data-lucide="hotel" class="w-8 h-8"></i>
                                </div>
                                <h3 class="font-heading text-3xl text-white mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-sky-200/80 max-w-xs">Pengalaman menginap mewah, pemandangan asri, dan kenyamanan sempurna.</p>
                            </div>
                        @endif

                        {{-- Floating Room Badge --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-sky-100 dark:border-sky-900/60 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600">
                                    <i data-lucide="star" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-heading font-bold text-xs text-slate-900 dark:text-white block">Resort &amp; Hospitality</span>
                                    <span class="text-[10px] text-slate-500">Pilihan favorit tamu menginap</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-sky-600 dark:text-sky-400">Rincian &rarr;</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
