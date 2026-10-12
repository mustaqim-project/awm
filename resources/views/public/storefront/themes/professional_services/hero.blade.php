{{--
    Professional Services Hero Section (`professional_services`)
    High-trust advisory firm architecture for business consultants, tax specialists, accounting & legal practitioners.
--}}
<section class="relative overflow-hidden bg-[#F8FAFC] dark:bg-[#0B111E] border-b border-slate-200/80 dark:border-slate-800 transition-colors">
    {{-- Slate & Brass Ambient Shadow --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-slate-400/10 dark:bg-slate-700/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-amber-600/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Credibility Pillars --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900/10 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-800 dark:text-slate-200">
                    <i data-lucide="scale" class="w-3.5 h-3.5 text-amber-600"></i>
                    <span class="tracking-wider uppercase text-[10px] font-bold">Advisory &amp; Legal Compliance &bull; Integritas Terpercaya</span>
                </div>

                <h1 class="font-heading font-bold text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.14]">
                    {{ $landingPage->headline ?: 'Kepastian Hukum, Kepatuhan Pajak & Solusi Finansial Perusahaan' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Mendampingi entitas bisnis dan korporasi melalui advisory berbasis data, audit kepatuhan regulasi, dan mitigasi risiko yang prudent serta terukur.' }}
                </p>

                {{-- Credibility Badges --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-800 shadow-2xs">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Kerahasiaan NDA Ketat</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-800 shadow-2xs">
                        <i data-lucide="award" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Praktisi &amp; Konsultan Bersertifikat</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-800 shadow-2xs">
                        <i data-lucide="file-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>Legal Standing Resmi</span>
                    </span>
                </div>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-xl bg-slate-900 dark:bg-amber-600 hover:bg-slate-800 dark:hover:bg-amber-700 text-white font-semibold text-sm shadow-md transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                            <span>Daftar Layanan Advisory</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-900 dark:text-white border border-slate-300 dark:border-slate-700 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="calendar" class="w-4 h-4 text-amber-600"></i>
                            <span>Jadwalkan Konsultasi Awal</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Trust & Partner Showcase Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-2xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-slate-950 via-slate-900 to-amber-950 text-white">
                                <div class="w-16 h-16 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-3">
                                    <i data-lucide="scale" class="w-8 h-8 text-amber-400"></i>
                                </div>
                                <h3 class="font-heading font-bold text-2xl text-white mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-slate-300 max-w-xs">Konsultan bisnis, akuntansi, pajak dan kepatuhan hukum independen.</p>
                            </div>
                        @endif

                        {{-- Floating Confidentiality Badge --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-xl bg-slate-900/95 text-white backdrop-blur-md border border-slate-700 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-400">
                                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-heading font-bold text-xs block">Standar Kepatuhan Hukum</span>
                                    <span class="text-[10px] text-slate-400">Pemberian opini legal &amp; audit formal</span>
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
