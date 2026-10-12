{{--
    Personal Brand Hero Section (`personal_brand`)
    Avant-garde creator, keynote speaker, and independent consultant hero with signature flair.
--}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-100 via-white to-white dark:from-[#09090B] dark:via-[#0F0F12] dark:to-[#09090B] border-b border-zinc-200 dark:border-zinc-800/80 transition-colors">
    {{-- Glow Accents --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-cyan-500/10 dark:bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-zinc-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Creator Persona --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 text-xs font-semibold text-zinc-900 dark:text-zinc-200">
                    <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span>
                    <span class="tracking-widest uppercase text-[10px] font-bold">Kreator &bull; Konsultan Mandiri &bull; Pembicara Publik</span>
                </div>

                <h1 class="font-heading font-extrabold text-4xl sm:text-5xl lg:text-6xl text-zinc-900 dark:text-white tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Membantu Anda Bertumbuh Lewat Gagasan, Karya & Strategi Berdampak' }}
                </h1>

                <p class="text-sm sm:text-base text-zinc-600 dark:text-zinc-300 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Sesi konsultasi privat 1-on-1, lokakarya eksklusif, dan produk digital terpilih untuk mengakselerasi potensi diri dan skala bisnis Anda ke level berikutnya.' }}
                </p>

                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-800 shadow-2xs">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-cyan-500"></i>
                        <span>1-on-1 Mentoring Intensif</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-800 shadow-2xs">
                        <i data-lucide="mic" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>Keynote &amp; Workshop Speaker</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-800 shadow-2xs">
                        <i data-lucide="download" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>Resource &amp; Digital Toolkits</span>
                    </span>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-full bg-zinc-900 hover:bg-black dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-900 font-bold text-sm shadow-md transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="sparkles" class="w-4 h-4 text-cyan-400 dark:text-cyan-600"></i>
                            <span>Jelajahi Program &amp; Karya</span>
                        </a>
                    @endif

                    @if (!empty($hasWhatsapp))
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-full bg-white dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-900 dark:text-white border border-zinc-300 dark:border-zinc-700 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-cyan-500"></i>
                            <span>Diskusi Kolaborasi / Project</span>
                        </a>
                    @endif
                </div>

                {{-- Metric Badges --}}
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-zinc-200 dark:border-zinc-800 max-w-lg">
                    <div>
                        <span class="block font-heading font-extrabold text-2xl text-zinc-900 dark:text-white">50k+</span>
                        <span class="text-[11px] text-zinc-500 dark:text-zinc-400 font-medium">Komunitas Pembaca</span>
                    </div>
                    <div>
                        <span class="block font-heading font-extrabold text-2xl text-cyan-600 dark:text-cyan-400">10+ Thn</span>
                        <span class="text-[11px] text-zinc-500 dark:text-zinc-400 font-medium">Jejak Pengalaman</span>
                    </div>
                    <div>
                        <span class="block font-heading font-extrabold text-2xl text-emerald-600 dark:text-emerald-400">100%</span>
                        <span class="text-[11px] text-zinc-500 dark:text-zinc-400 font-medium">Kepuasan Klien</span>
                    </div>
                </div>

            </div>

            {{-- Visual Showcase / Keynote Portrait Card --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    @if ($landingPage->hero_image_url)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-zinc-200 dark:border-zinc-800">
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-auto max-h-[480px] object-cover">
                        </div>
                    @else
                        {{-- Avant-garde Personal Identity Card --}}
                        <div class="rounded-3xl p-8 bg-gradient-to-br from-zinc-950 via-zinc-900 to-black text-white shadow-2xl border border-zinc-800 relative overflow-hidden">
                            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-cyan-500/15 rounded-full blur-2xl"></div>

                            <div class="relative z-10 space-y-6">
                                <div class="w-14 h-14 rounded-2xl bg-zinc-800/80 border border-zinc-700 flex items-center justify-center text-cyan-400">
                                    <i data-lucide="feather" class="w-7 h-7"></i>
                                </div>

                                <div>
                                    <span class="text-xs font-bold uppercase tracking-widest text-cyan-400">Personal Advisory &bull; Creator Space</span>
                                    <h3 class="font-heading font-extrabold text-2xl mt-1 text-white">Sinergi Pemikiran &amp; Eksekusi</h3>
                                    <p class="text-xs text-zinc-300 leading-relaxed mt-2">
                                        Menjembatani teori konseptual dan implementasi taktis melalui framework kerja yang teruji dan siap diterapkan pada organisasi Anda.
                                    </p>
                                </div>

                                <div class="space-y-2.5 pt-2">
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/5 backdrop-blur-xs border border-white/10 text-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-cyan-400 shrink-0"></i>
                                        <span>Diagnosa Masalah Mendalam Tanpa Template Usang</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/5 backdrop-blur-xs border border-white/10 text-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-cyan-400 shrink-0"></i>
                                        <span>Rencana Aksi Konkret Siap Eksekusi (Actionable Blueprint)</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/5 backdrop-blur-xs border border-white/10 text-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-cyan-400 shrink-0"></i>
                                        <span>Evaluasi Tindak Lanjut Pasca Program</span>
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
