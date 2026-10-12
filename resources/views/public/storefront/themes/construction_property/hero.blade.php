{{--
    Construction & Property Hero Section (`construction_property`)
    Architectural blueprint precision for contractors, renovation, architecture firms, and property developers.
--}}
<section class="relative overflow-hidden bg-[#FAF5EE] dark:bg-[#131210] border-b border-amber-200/60 dark:border-amber-950/60 transition-colors">
    {{-- Blueprint Grid Ambient --}}
    <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.05]"
        style="background-image: radial-gradient(#B45309 1px, transparent 1px); background-size: 24px 24px;">
    </div>
    <div class="absolute -top-32 right-10 w-96 h-96 bg-amber-600/10 dark:bg-amber-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Architectural Standards --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-amber-600/10 dark:bg-amber-600/20 border border-amber-600/30 text-xs font-bold text-amber-800 dark:text-amber-400">
                    <i data-lucide="compass" class="w-3.5 h-3.5 text-amber-600"></i>
                    <span class="tracking-widest uppercase text-[10px]">Kontraktor, Arsitektur &amp; Pengembangan Properti</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.08] uppercase">
                    {{ $landingPage->headline ?: 'Membangun Hunian & Ruang Komersial Berkualitas Presisi' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-amber-200/70 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Layanan rancang bangun terintegrasi mulai dari konsep arsitektur, perhitungan RAB transparan, hingga pelaksanaan konstruksi kokoh bergaransi struktur.' }}
                </p>

                {{-- Key Trust Metrics --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-bold bg-white dark:bg-slate-900 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-900/60 shadow-2xs">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Garansi Konstruksi Struktur</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-bold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-800 shadow-2xs">
                        <i data-lucide="calculator" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Estimasi RAB Terbuka &amp; Rinci</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-bold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-800 shadow-2xs">
                        <i data-lucide="award" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>Material Standar PU / SNI</span>
                    </span>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-md transition-all flex items-center gap-2 active:scale-95 uppercase tracking-wider">
                            <i data-lucide="home" class="w-4 h-4"></i>
                            <span>Portofolio &amp; Tipe Unit</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-amber-50 dark:hover:bg-slate-800 text-slate-900 dark:text-white border border-amber-300 dark:border-amber-800 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Konsultasi RAB &amp; Desain</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Blueprint Showcase Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-2xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-amber-300 dark:border-amber-900/60 bg-white dark:bg-slate-900">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-amber-950 via-slate-950 to-neutral-950 text-white">
                                <div class="w-16 h-16 rounded-xl bg-amber-600/20 border border-amber-500/30 flex items-center justify-center mb-3 text-amber-400">
                                    <i data-lucide="building-2" class="w-8 h-8"></i>
                                </div>
                                <h3 class="font-heading font-black text-2xl text-white mb-1 uppercase">{{ $business->name }}</h3>
                                <p class="text-xs text-amber-200/80 max-w-xs">Konstruksi bangunan komersial, perumahan, dan renovasi arsitektural.</p>
                            </div>
                        @endif

                        {{-- Floating Precision Badge --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-xl bg-slate-900/95 text-white backdrop-blur-md border border-amber-900/60 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-amber-600/30 flex items-center justify-center text-amber-400">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-heading font-bold text-xs block">Standar Teknik Presisi</span>
                                    <span class="text-[10px] text-slate-400">Pengawasan arsitek &amp; sipil berlisensi</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-amber-400">Pelajari &rarr;</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
