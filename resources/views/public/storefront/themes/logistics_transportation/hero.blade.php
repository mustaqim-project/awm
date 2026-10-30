{{--
    Logistics & Transportation Hero Section (`logistics_transportation`)
    High-speed freight dispatch & fleet powerhouse hero for cargo, trucking, couriers, and freight forwarders.
--}}
<section class="relative overflow-hidden bg-gradient-to-b from-amber-50/60 via-slate-50/30 to-white dark:from-[#0F141C] dark:via-[#131B26] dark:to-[#0B0F15] border-b border-amber-200/80 dark:border-amber-950/60 transition-colors">
    {{-- Glow Accents --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-amber-500/10 dark:bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-red-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Fleet Powerhouse --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-100 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-800/60 text-xs font-bold text-amber-900 dark:text-amber-300">
                    <i data-lucide="truck" class="w-3.5 h-3.5 text-amber-600"></i>
                    <span class="tracking-widest uppercase text-[10px]">Ekspedisi Kargo &bull; Sewa Truk &bull; Distribusi Logistik</span>
                </div>

                <h1 class="font-heading font-black text-4xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.1] uppercase">
                    {{ $landingPage->headline ?: 'Pengiriman Cepat, Armada Tangguh, Jangkauan Logistik Tanpa Batas' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-amber-100/70 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Layanan ekspedisi kargo, sewa armada truk terawat, dan distribusi logistik aman dengan pelacakan muatan real-time ke seluruh penjuru Nusantara.' }}
                </p>

                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-white dark:bg-slate-900 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-900/60 shadow-2xs">
                        <i data-lucide="scan-line" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Pelacakan Resi Real-Time</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-white dark:bg-slate-900 text-red-900 dark:text-red-300 border border-red-300 dark:border-red-900/60 shadow-2xs">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-red-600"></i>
                        <span>Asuransi Muatan Penuh</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 border border-slate-300 dark:border-slate-800 shadow-2xs">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Jaringan Door-to-Door</span>
                    </span>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-sm uppercase tracking-wider shadow-md shadow-amber-500/25 transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="truck" class="w-4 h-4"></i>
                            <span>{{ __('storefront.pilih_armada_layanan') }}</span>
                        </a>
                    @endif

                    @if (!empty($hasWhatsapp))
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-amber-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white border border-amber-300 dark:border-amber-800 font-bold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>Sewa Armada / Tanya Tarif Truk</span>
                        </a>
                    @endif
                </div>

                {{-- Metric Badges --}}
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-amber-200 dark:border-amber-950/80 max-w-lg">
                    <div>
                        <span class="block font-heading font-black text-2xl text-amber-600 dark:text-amber-400">99.8%</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold uppercase">On-Time Arrival</span>
                    </div>
                    <div>
                        <span class="block font-heading font-black text-2xl text-red-600 dark:text-red-400">500+</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold uppercase">Titik Kota Terhubung</span>
                    </div>
                    <div>
                        <span class="block font-heading font-black text-2xl text-slate-900 dark:text-white">24/7</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold uppercase">Live GPS Dispatch</span>
                    </div>
                </div>

            </div>

            {{-- Visual Showcase / Dispatch Dashboard Card --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    @if ($landingPage->hero_image_url)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-amber-300 dark:border-amber-900">
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-auto max-h-[480px] object-cover">
                        </div>
                    @else
                        {{-- High-tech Fleet Graphic Card --}}
                        <div class="rounded-3xl p-8 bg-gradient-to-br from-slate-950 via-slate-900 to-[#0F141C] text-white shadow-2xl border border-amber-500/30 relative overflow-hidden">
                            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-amber-500/10 rounded-full blur-2xl"></div>

                            <div class="relative z-10 space-y-6">
                                <div class="w-14 h-14 rounded-2xl bg-amber-500/20 border border-amber-400/40 flex items-center justify-center text-amber-300">
                                    <i data-lucide="container" class="w-7 h-7 text-amber-400"></i>
                                </div>

                                <div>
                                    <span class="text-xs font-black uppercase tracking-widest text-amber-400">Fleet Precision &bull; Nationwide</span>
                                    <h3 class="font-heading font-black text-2xl mt-1 text-white uppercase">{{ __('storefront.sistem_logistik_andal') }}</h3>
                                    <p class="text-xs text-slate-300 leading-relaxed mt-2">
                                        Didukung sistem pemantauan posisi berkala, jaringan sopir tersertifikasi, dan asuransi muatan menyeluruh untuk ketenangan bisnis Anda.
                                    </p>
                                </div>

                                <div class="space-y-2.5 pt-2">
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 backdrop-blur-xs border border-white/10 text-xs font-medium">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>Armada Box, Bak, Wingbox, &amp; Reefer Lengkap</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 backdrop-blur-xs border border-white/10 text-xs font-medium">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>Integrasi Surat Jalan Digital &amp; Bukti Kirim (POD)</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 backdrop-blur-xs border border-white/10 text-xs font-medium">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>Pemberitahuan Status Muatan Otomatis</span>
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
