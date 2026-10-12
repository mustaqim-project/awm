{{--
    Agriculture & Farm Hero Section (`agriculture_farm`)
    Organic harvest and rural vitality hero for agriculture, livestock, fisheries, seedlings, and farm supplies.
--}}
<section class="relative overflow-hidden bg-gradient-to-b from-emerald-50/70 via-stone-50/30 to-white dark:from-[#0F1912] dark:via-[#112117] dark:to-[#0A140E] border-b border-emerald-100 dark:border-emerald-950/60 transition-colors">
    {{-- Glow Accents --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-emerald-500/10 dark:bg-emerald-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Farm Story --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                    <i data-lucide="sprout" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span class="tracking-wider uppercase text-[10px] font-bold">Hasil Bumi Segar &bull; Langsung dari Lahan Petani</span>
                </div>

                <h1 class="font-heading font-normal text-4xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.15]">
                    {{ $landingPage->headline ?: 'Kualitas Panen Terbaik dari Kebun Asri, Segar ke Meja Anda' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-emerald-200/70 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Menghubungkan kebaikan alam dan jerih payah petani Nusantara. Produk panen pilihan, bibit unggul terverifikasi, dan sarana pertanian berkualitas tinggi.' }}
                </p>

                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60 shadow-2xs">
                        <i data-lucide="leaf" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>100% Alami &amp; Non-Pestisida</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60 shadow-2xs">
                        <i data-lucide="sun" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Petik Segar Pagi Hari</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-900/60 shadow-2xs">
                        <i data-lucide="heart-handshake" class="w-3.5 h-3.5 text-green-600"></i>
                        <span>Kemitraan Tani Adil</span>
                    </span>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm shadow-md shadow-emerald-700/25 transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="sprout" class="w-4 h-4"></i>
                            <span>Beli Hasil Panen &amp; Produk</span>
                        </a>
                    @endif

                    @if (!empty($hasWhatsapp))
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-emerald-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white border border-emerald-200 dark:border-emerald-800 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>Pemesanan Jumlah Besar / B2B Tani</span>
                        </a>
                    @endif
                </div>

                {{-- Metric Badges --}}
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-emerald-100 dark:border-emerald-950/80 max-w-lg">
                    <div>
                        <span class="block font-extrabold text-2xl text-emerald-700 dark:text-emerald-400">100%</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Petik Segar Tiap Hari</span>
                    </div>
                    <div>
                        <span class="block font-extrabold text-2xl text-amber-600 dark:text-amber-400">50+ Ha</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Binaan Lahan Tani</span>
                    </div>
                    <div>
                        <span class="block font-extrabold text-2xl text-slate-900 dark:text-white">Standar</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Sortir Grade Pilihan</span>
                    </div>
                </div>

            </div>

            {{-- Visual Showcase / Harvest Bounty Card --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    @if ($landingPage->hero_image_url)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-emerald-100 dark:border-emerald-900">
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-auto max-h-[480px] object-cover">
                        </div>
                    @else
                        {{-- Farm Harvest Graphic Card --}}
                        <div class="rounded-3xl p-8 bg-gradient-to-br from-emerald-900 to-green-950 text-white shadow-2xl border border-emerald-800 relative overflow-hidden">
                            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-emerald-600/20 rounded-full blur-2xl"></div>

                            <div class="relative z-10 space-y-6">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-600/40 border border-emerald-400/40 flex items-center justify-center text-emerald-200">
                                    <i data-lucide="trees" class="w-7 h-7 text-emerald-300"></i>
                                </div>

                                <div>
                                    <span class="text-xs font-bold uppercase tracking-widest text-emerald-300">Farm-to-Table Freshness</span>
                                    <h3 class="font-heading font-normal text-3xl mt-1 text-white">Segar Dari Petani Langsung</h3>
                                    <p class="text-xs text-emerald-200/80 leading-relaxed mt-2">
                                        Rantai pasok ringkas tanpa tengkulak memastikan Anda menerima komoditas terbaik dengan harga yang adil bagi petani kami.
                                    </p>
                                </div>

                                <div class="space-y-2.5 pt-2">
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/10 backdrop-blur-xs border border-white/10 text-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>Proses Sortir Manual Memisahkan Kualitas Terbaik</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/10 backdrop-blur-xs border border-white/10 text-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>Pengemasan Aman &amp; Ventilasi Terjaga Selama Transit</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/10 backdrop-blur-xs border border-white/10 text-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>Garansi Ganti Barang Jika Tiba Dalam Kondisi Rusak</span>
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
