{{--
    Education & Courses Hero Section (`education_courses`)
    Scholarly & edtech hero for vocational academies, tutoring centers, bootcamps, and professional courses.
--}}
<section class="relative overflow-hidden bg-gradient-to-b from-indigo-50/70 via-white to-white dark:from-[#0F172A] dark:via-[#0D1526] dark:to-[#0A101D] border-b border-indigo-100 dark:border-indigo-950/60 transition-colors">
    {{-- Glow Accents --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-indigo-500/10 dark:bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-orange-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Academic Value Proposition --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-100 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800/60 text-xs font-bold text-indigo-800 dark:text-indigo-300">
                    <i data-lucide="award" class="w-3.5 h-3.5 text-indigo-600"></i>
                    <span class="tracking-wider uppercase text-[10px]">Kurikulum Standar Industri &bull; Sertifikasi Resmi</span>
                </div>

                <h1 class="font-heading font-extrabold text-4xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.15]">
                    {{ $landingPage->headline ?: 'Tingkatkan Keahlian, Raih Karir Impian Bersama Mentor Ahli' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-indigo-200/70 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Program belajar intensif terstruktur dari dasar hingga mahir dengan kurikulum aplikatif, bimbingan studi kasus nyata, dan sertifikat kompetensi terpercaya.' }}
                </p>

                {{-- Trust Pillars Pills --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900/60 shadow-2xs">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-indigo-600"></i>
                        <span>Mentoring Interaktif</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-orange-800 dark:text-orange-300 border border-orange-200 dark:border-orange-900/60 shadow-2xs">
                        <i data-lucide="file-badge" class="w-3.5 h-3.5 text-orange-600"></i>
                        <span>Sertifikat Kelulusan</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60 shadow-2xs">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Akses Materi Fleksibel</span>
                    </span>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-md shadow-indigo-600/25 transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                            <span>Jelajahi Pilihan Kelas</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-indigo-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white border border-indigo-200 dark:border-indigo-800 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>Konsultasi Penjurusan / Silabus</span>
                        </a>
                    @endif
                </div>

                {{-- Student Metrics Stats Bar --}}
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-indigo-100 dark:border-indigo-950/80 max-w-lg">
                    <div>
                        <span class="block font-extrabold text-2xl text-indigo-700 dark:text-indigo-400">98%</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Tingkat Kepuasan</span>
                    </div>
                    <div>
                        <span class="block font-extrabold text-2xl text-orange-600 dark:text-orange-400">100+</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Modul Praktik</span>
                    </div>
                    <div>
                        <span class="block font-extrabold text-2xl text-slate-900 dark:text-white">Seumur Hidup</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Akses Rekaman Kelas</span>
                    </div>
                </div>

            </div>

            {{-- Visual Showcase / Course Highlight Board --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    @if ($landingPage->hero_image_url)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-indigo-100 dark:border-indigo-900">
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-auto max-h-[480px] object-cover">
                        </div>
                    @else
                        {{-- Academic Classroom & Mentor Graphic --}}
                        <div class="rounded-3xl p-8 bg-gradient-to-br from-indigo-900 to-indigo-950 text-white shadow-2xl border border-indigo-800 relative overflow-hidden">
                            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-indigo-600/30 rounded-full blur-2xl"></div>

                            <div class="relative z-10 space-y-6">
                                <div class="w-14 h-14 rounded-2xl bg-indigo-600/50 border border-indigo-400/40 flex items-center justify-center text-indigo-200">
                                    <i data-lucide="book-open-check" class="w-7 h-7 text-amber-400"></i>
                                </div>

                                <div>
                                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-300">Jalur Belajar Terbimbing</span>
                                    <h3 class="font-heading font-bold text-2xl mt-1 text-white">{{ __('storefront.metode_belajar_praktik_langsung') }}</h3>
                                    <p class="text-xs text-indigo-200/80 leading-relaxed mt-2">
                                        Setiap modul dirancang selaras dengan studi kasus dunia nyata sehingga siswa siap terjun ke dunia industri maupun wirausaha mandiri.
                                    </p>
                                </div>

                                <div class="space-y-2.5 pt-2">
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/10 backdrop-blur-xs border border-white/10 text-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>Proyek Portofolio Nyata Siap Kerja</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/10 backdrop-blur-xs border border-white/10 text-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>Grup Komunitas &amp; Diskusi Bareng Mentor</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/10 backdrop-blur-xs border border-white/10 text-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>E-Sertifikat dengan ID Verifikasi Resmi</span>
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
