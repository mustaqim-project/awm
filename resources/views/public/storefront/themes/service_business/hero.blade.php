{{--
    Service Business Hero Section (`service_business`)
    Clean fresh hygiene aesthetic for laundry, cleaning, salon & care services with pickup/delivery & order tracking.
--}}
<section class="relative overflow-hidden bg-[#F0F9FF] dark:bg-[#0A1622] border-b border-sky-100 dark:border-sky-950/60 transition-colors">
    {{-- Fresh Mist Glow --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-sky-400/10 dark:bg-sky-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-teal-400/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            {{-- Text & Guarantees --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sky-100 dark:bg-sky-950/60 border border-sky-200 dark:border-sky-800/60 text-xs font-semibold text-sky-800 dark:text-sky-300">
                    <span class="w-2 h-2 rounded-full bg-sky-500 animate-ping"></span>
                    <span class="tracking-wider uppercase text-[10px] font-bold">Layanan Higienis &bull; 1 Mesin 1 Pelanggan</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Pakaian & Layanan Bersih, Harum, Cepat & Terpercaya' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-sky-200/70 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Nikmati kemudahan layanan perawatan berkualitas dengan jaminan pengerjaan tepat waktu, deterjen ramah serat kain, dan fasilitas antar-jemput cepat.' }}
                </p>

                {{-- Feature Badges --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-sky-800 dark:text-sky-300 border border-sky-200 dark:border-sky-900/60 shadow-2xs">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-sky-600"></i>
                        <span>Anti-Campur (Higienis)</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-900/60 shadow-2xs">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-teal-600"></i>
                        <span>Layanan Express Kilat</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60 shadow-2xs">
                        <i data-lucide="truck" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Antar-Jemput Praktis</span>
                    </span>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-6 py-3.5 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-sm shadow-md shadow-sky-600/25 transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span>{{ __('storefront.daftar_paket_layanan') }}</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-5 py-3.5 rounded-2xl bg-white dark:bg-slate-900 hover:bg-sky-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white border border-sky-200 dark:border-sky-900/60 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="truck" class="w-4 h-4 text-sky-600"></i>
                            <span>Order Antar-Jemput</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Showcase Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-3xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-sky-200/80 dark:border-sky-950/80 bg-white dark:bg-slate-900">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-sky-600 via-sky-700 to-teal-800 text-white">
                                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-3">
                                    <i data-lucide="sparkles" class="w-8 h-8 text-sky-200"></i>
                                </div>
                                <h3 class="font-heading font-bold text-2xl text-white mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-sky-100 max-w-xs">Solusi perawatan bersih tuntas, wangi tahan lama dan higienis.</p>
                            </div>
                        @endif

                        {{-- Floating Tracking Badge --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-sky-200/60 dark:border-sky-900/60 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-sky-100 dark:bg-sky-950 flex items-center justify-center text-sky-600">
                                    <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-heading font-bold text-xs text-slate-900 dark:text-white block">Status Nota Real-time</span>
                                    <span class="text-[10px] text-slate-500">Lacak pengerjaan nota Anda</span>
                                </div>
                            </div>
                            <button type="button" @click="$dispatch('open-order-track-modal')" class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline">
                                Lacak &rarr;
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
