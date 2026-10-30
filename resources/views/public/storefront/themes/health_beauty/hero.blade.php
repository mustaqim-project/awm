{{--
    Health & Beauty Hero Section (`health_beauty`)
    Dermatological glow & clinical wellness hero for aesthetic clinics, skincare brands, spas, and wellness studios.
--}}
<section class="relative overflow-hidden bg-gradient-to-b from-teal-50/60 via-rose-50/20 to-white dark:from-[#0F171A] dark:via-[#111C20] dark:to-[#0A1214] border-b border-teal-100 dark:border-teal-950/60 transition-colors">
    {{-- Glow Accents --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-teal-400/15 dark:bg-teal-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-rose-400/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Clinical Story --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-teal-100/80 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-800/60 text-xs font-semibold text-teal-800 dark:text-teal-300">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-teal-600"></i>
                    <span class="tracking-wider uppercase text-[10px] font-bold">Teruji Klinis &bull; Bersertifikat BPOM &amp; Halal</span>
                </div>

                <h1 class="font-heading font-extrabold text-4xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.15]">
                    {{ $landingPage->headline ?: 'Pancarkan Pesona Alami dengan Sentuhan Perawatan Medis & Estetika' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-teal-200/70 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Formula dermatologi terbaik dan rangkaian treatment profesional oleh tenaga medis ahli untuk merawat kulit sehat, cerah alami, dan awet muda.' }}
                </p>

                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-900/60 shadow-2xs">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-teal-600"></i>
                        <span>100% Terdaftar BPOM</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60 shadow-2xs">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-rose-600"></i>
                        <span>Pengawasan Dokter Spesialis</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60 shadow-2xs">
                        <i data-lucide="check" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Steril &amp; Higienis Standar Medis</span>
                    </span>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-full bg-teal-600 hover:bg-teal-700 text-white font-semibold text-sm shadow-md shadow-teal-600/25 transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span>{{ __('storefront.pilih_produk_treatment') }}</span>
                        </a>
                    @endif

                    @if (!empty($hasWhatsapp))
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-full bg-white dark:bg-slate-900 hover:bg-teal-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white border border-teal-200 dark:border-teal-800 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>Konsultasi Dokter Kulit Online</span>
                        </a>
                    @endif
                </div>

                {{-- Metric Badges --}}
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-teal-100 dark:border-teal-950/80 max-w-lg">
                    <div>
                        <span class="block font-extrabold text-2xl text-teal-700 dark:text-teal-400">10k+</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Pasien Terlayani</span>
                    </div>
                    <div>
                        <span class="block font-extrabold text-2xl text-rose-600 dark:text-rose-400">100%</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Bebas Merkuri</span>
                    </div>
                    <div>
                        <span class="block font-extrabold text-2xl text-slate-900 dark:text-white">Resmi</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Sertifikasi BPOM &amp; Halal</span>
                    </div>
                </div>

            </div>

            {{-- Visual Showcase / Clinic Aesthetic Card --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    @if ($landingPage->hero_image_url)
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-teal-100 dark:border-teal-900">
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-auto max-h-[480px] object-cover">
                        </div>
                    @else
                        {{-- Wellness Aesthetic Graphic --}}
                        <div class="rounded-3xl p-8 bg-gradient-to-br from-teal-900 via-teal-950 to-slate-950 text-white shadow-2xl border border-teal-800 relative overflow-hidden">
                            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-teal-500/20 rounded-full blur-2xl"></div>

                            <div class="relative z-10 space-y-6">
                                <div class="w-14 h-14 rounded-2xl bg-teal-600/40 border border-teal-400/40 flex items-center justify-center text-teal-200">
                                    <i data-lucide="flask-conical" class="w-7 h-7 text-teal-300"></i>
                                </div>

                                <div>
                                    <span class="text-xs font-bold uppercase tracking-widest text-teal-300">Dermatology Science</span>
                                    <h3 class="font-heading font-bold text-2xl mt-1 text-white">Perawatan Personalisasi Kulit</h3>
                                    <p class="text-xs text-teal-200/80 leading-relaxed mt-2">
                                        Analisis jenis kulit mendalam untuk merekomendasikan kombinasi serum aktif dan protokol treatment yang tepat sasaran.
                                    </p>
                                </div>

                                <div class="space-y-2.5 pt-2">
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10 text-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>Protokol Sterilisasi Standar Bedah Medis</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10 text-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>Bahan Aktif Premium Teruji Klinis Dermatologi</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10 text-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>Konsultasi Pasca Treatment Berkelanjutan</span>
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
