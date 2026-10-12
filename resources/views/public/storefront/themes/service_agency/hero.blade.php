{{--
    Konsultan, Hukum, Agensi & Jasa B2B Hero Section (`service_agency` / `sovereign_enterprise`)
    Corporate prestige navy, Cormorant Garamond typography, RFQ proposal requests & NDA guarantees.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold"
                    style="background: {{ $activeTheme['badge_bg'] ?? '#0F172A10' }}; color: {{ $activeTheme['badge_text'] ?? '#0F172A' }};">
                    <i data-lucide="briefcase" class="w-4 h-4"></i>
                    <span>{{ $activeTheme['industry'] ?? 'Konsultan Bisnis, Hukum & Solusi B2B' }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-neutral-900 dark:text-white tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Solusi Strategis Berintegritas untuk Pertumbuhan Perusahaan' }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    {{ $landingPage->subheadline ?: 'Layanan konsultasi manajemen, audit kepatuhan hukum, transformasi digital, riset pasar dan pendampingan korporasi profesional dengan standar kerahasiaan tinggi.' }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        <span>Kerahasiaan NDA Terjamin</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/40">
                        <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                        <span>SLA &amp; Kontrak Resmi</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                        <i data-lucide="award" class="w-3.5 h-3.5"></i>
                        <span>Konsultan Berlisensi</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-semibold rounded-2xl text-white shadow-lg transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5"
                            style="background: linear-gradient(135deg, #0F172A, #1E293B);">
                            <span>{{ $landingPage->cta_primary_text ?: 'Daftar Layanan & Retainer' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl border-2 shadow-sm transition-all duration-300 flex items-center gap-2 min-h-[48px] bg-white dark:bg-[#181E2A] text-neutral-800 dark:text-white border-slate-300 dark:border-slate-700 hover:bg-slate-50/50">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Jadwalkan Diskusi Awal (Free 30m)</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-slate-200 dark:border-white/10 aspect-square bg-slate-100 dark:bg-slate-900">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border border-white/20 text-xs shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span>
                                <span class="font-bold text-neutral-900 dark:text-white">Partner Korporasi Terpercaya</span>
                            </div>
                            <span class="text-blue-700 dark:text-blue-400 font-bold font-mono">Enterprise B2B</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
