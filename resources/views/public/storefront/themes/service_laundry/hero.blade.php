{{--
    Laundry Kiloan & Satuan Hero Section (`service_laundry` / `aqua_bubble`)
    PRD-21: Clean water aesthetic with hygiene guarantee, turnaround speed, and pickup/delivery.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-28">
    {{-- Water Bubble Flow Background Pattern --}}
    <div class="absolute inset-0 opacity-[0.04]"
        style="background-image: url('data:image/svg+xml,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60"><circle cx="30" cy="30" r="12" fill="#0284C7"/><circle cx="15" cy="45" r="6" fill="#38BDF8"/><circle cx="48" cy="18" r="4" fill="#0284C7"/></svg>') }}'); background-size: 60px 60px;">
    </div>

    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 items-center">

            {{-- Left: Copy & Value Proposition --}}
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                {{-- Hygiene Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold"
                    style="background: {{ $activeTheme['badge_bg'] ?? '#0284C715' }}; color: {{ $activeTheme['badge_text'] ?? '#0284C7' }};">
                    <span class="w-2 h-2 rounded-full bg-current animate-ping"></span>
                    <span>{{ $activeTheme['industry'] ?? 'Laundry Kiloan & Satuan Premium' }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-[3.4rem] tracking-tight leading-[1.12] text-neutral-900 dark:text-white">
                    {{ $landingPage->headline ?: 'Pakaian Bersih, Harum & Rapi Tanpa Ribet' }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Layanan cuci kiloan, dry clean satuan, cuci sepatu hingga karpet dengan deterjen ramah serat kain, setrika uap presisi, dan jaminan 1 mesin per 1 pelanggan.' }}
                </p>

                {{-- Feature Badges Strip --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200/60 dark:border-sky-800/40">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        <span>1 Mesin 1 Nota (Anti Campur)</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200/60 dark:border-sky-800/40">
                        <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                        <span>Express 3 Jam & 1 Hari</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-200/60 dark:border-sky-800/40">
                        <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                        <span>Antar-Jemput Gratis</span>
                    </span>
                </div>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-semibold rounded-2xl text-white shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5 active:scale-[0.98]"
                            style="background: linear-gradient(135deg, var(--theme-primary, #0284C7), #0369A1);">
                            <span>{{ $landingPage->cta_primary_text ?: 'Pilih Layanan Cuci' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl border-2 shadow-sm hover:shadow-md transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5 bg-white dark:bg-[#181E2A] text-neutral-800 dark:text-white border-sky-500/30">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Jemput Cucian Saya</span>
                        </a>
                    @endif

                    {{-- Track Order Direct Trigger --}}
                    <button type="button" @click="$dispatch('open-order-track-modal')"
                        class="px-4 py-3.5 text-xs sm:text-sm font-semibold rounded-2xl text-sky-700 dark:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-950/30 transition flex items-center gap-1.5 min-h-[48px]">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Cek Status Nota</span>
                    </button>
                </div>
            </div>

            {{-- Right: Visual & Service Highlights Bento --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none space-y-3.5">
                    <div class="p-6 rounded-3xl bg-white dark:bg-[#181E2A] border border-sky-100 dark:border-white/10 shadow-xl space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                                    <i data-lucide="washing-machine" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-neutral-900 dark:text-white">Estimasi Cepat Laundry</h4>
                                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Pilih jenis cucian untuk order mudah</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600">Buka Hari Ini</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 text-left text-xs">
                            <div class="p-3 rounded-2xl bg-sky-50/70 dark:bg-white/[0.03] border border-sky-200/40 dark:border-white/5 space-y-1">
                                <div class="font-bold text-neutral-800 dark:text-neutral-200">Kiloan Reguler</div>
                                <div class="text-[11px] text-neutral-500 dark:text-neutral-400">Cuci + Kering + Lipat</div>
                                <div class="text-sky-600 dark:text-sky-400 font-extrabold text-sm pt-1">Mulai Rp 7.000<span class="text-[10px] font-normal text-neutral-400">/kg</span></div>
                            </div>

                            <div class="p-3 rounded-2xl bg-sky-50/70 dark:bg-white/[0.03] border border-sky-200/40 dark:border-white/5 space-y-1">
                                <div class="font-bold text-neutral-800 dark:text-neutral-200">Express Kilat</div>
                                <div class="text-[11px] text-neutral-500 dark:text-neutral-400">Selesai 4-6 Jam</div>
                                <div class="text-sky-600 dark:text-sky-400 font-extrabold text-sm pt-1">Mulai Rp 12.000<span class="text-[10px] font-normal text-neutral-400">/kg</span></div>
                            </div>

                            <div class="p-3 rounded-2xl bg-sky-50/70 dark:bg-white/[0.03] border border-sky-200/40 dark:border-white/5 space-y-1">
                                <div class="font-bold text-neutral-800 dark:text-neutral-200">Cuci Satuan & Jas</div>
                                <div class="text-[11px] text-neutral-500 dark:text-neutral-400">Dry Clean Care</div>
                                <div class="text-sky-600 dark:text-sky-400 font-extrabold text-sm pt-1">Mulai Rp 20.000<span class="text-[10px] font-normal text-neutral-400">/pcs</span></div>
                            </div>

                            <div class="p-3 rounded-2xl bg-sky-50/70 dark:bg-white/[0.03] border border-sky-200/40 dark:border-white/5 space-y-1">
                                <div class="font-bold text-neutral-800 dark:text-neutral-200">Sepatu & Tas</div>
                                <div class="text-[11px] text-neutral-500 dark:text-neutral-400">Deep Cleaning Treatment</div>
                                <div class="text-sky-600 dark:text-sky-400 font-extrabold text-sm pt-1">Mulai Rp 30.000<span class="text-[10px] font-normal text-neutral-400">/psg</span></div>
                            </div>
                        </div>

                        <div class="pt-2 text-center">
                            <span class="text-[11px] text-neutral-500 dark:text-neutral-400 flex items-center justify-center gap-1.5">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500"></i>
                                Garansi cuci ulang jika bau apek atau tidak bersih
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
