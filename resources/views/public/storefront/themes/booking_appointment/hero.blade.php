{{-- ========================================================================= --}}
{{-- HERO: Booking & Appointment (Salon, Klinik, Studio, Konsultasi & Bengkel) --}}
{{-- ========================================================================= --}}
<section class="relative overflow-hidden bg-gradient-to-b from-[#F5F3FF] via-[#EDE9FE]/40 to-[#F5F3FF] dark:from-[#0E0B17] dark:via-[#161224] dark:to-[#0E0B17] border-b border-purple-200/60 dark:border-purple-900/40 transition-colors">
    {{-- Ambient Violet & Rose Glows --}}
    <div class="absolute -top-32 right-8 w-96 h-96 bg-purple-500/15 dark:bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 -left-20 w-80 h-80 bg-rose-500/10 dark:bg-rose-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

            {{-- Text & Reservation Proposition --}}
            <div class="lg:col-span-7 space-y-6">
                {{-- Live Slot Badge --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-purple-100 dark:bg-purple-950/80 border border-purple-300/80 dark:border-purple-800/80 text-xs font-semibold text-purple-900 dark:text-purple-300 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400"></i>
                    <span class="tracking-wide uppercase text-[10px] font-extrabold">Sistem Reservasi Slot Jam Real-Time</span>
                </div>

                {{-- Headline --}}
                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-purple-100 tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Pilih Jadwal, Tentukan Terapis / Ahli, & Nikmati Layanan Terbaik' }}
                </h1>

                {{-- Subheadline --}}
                <p class="text-sm sm:text-base text-slate-600 dark:text-purple-200/80 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Kemudahan memilih jam kedatangan, praktisi ahli bersertifikat, durasi layanan transparan, serta konfirmasi instan langsung via WhatsApp tanpa perlu antre di tempat.' }}
                </p>

                {{-- Key Trust Metrics --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-white dark:bg-[#161224] text-purple-900 dark:text-purple-300 border border-purple-200 dark:border-purple-900/70 shadow-2xs">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400"></i>
                        <span>Slot Jam On-Time</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-white dark:bg-[#161224] text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-900/70 shadow-2xs">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-rose-500"></i>
                        <span>Staf Berpengalaman</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-white dark:bg-[#161224] text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/70 shadow-2xs">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Konfirmasi WA Instan</span>
                    </span>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="min-h-[48px] px-7 py-3.5 rounded-2xl bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-sm shadow-md shadow-purple-600/30 transition-all flex items-center justify-center gap-2.5 active:scale-95">
                            <i data-lucide="calendar-plus" class="w-4.5 h-4.5"></i>
                            <span>Pilih Sesi &amp; Layanan</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener noreferrer"
                            class="min-h-[48px] px-6 py-3.5 rounded-2xl bg-white dark:bg-[#161224] hover:bg-purple-50 dark:hover:bg-purple-950/60 text-slate-800 dark:text-purple-200 border border-purple-200 dark:border-purple-800/80 font-bold text-sm transition-all flex items-center justify-center gap-2.5 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4.5 h-4.5 text-emerald-500"></i>
                            <span>Konsultasi via WhatsApp</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Live Interactive Appointment Bento Showcase --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    
                    {{-- Decorative Glow Layer --}}
                    <div class="absolute -inset-1.5 rounded-[32px] bg-gradient-to-r from-purple-600/30 to-rose-500/30 blur-lg opacity-75"></div>

                    <div class="relative rounded-[28px] overflow-hidden shadow-2xl border border-purple-200/80 dark:border-purple-900/60 bg-white dark:bg-[#161224] p-5 sm:p-6 space-y-5">
                        
                        {{-- Bento Header: Live Studio Desk --}}
                        <div class="flex items-center justify-between pb-4 border-b border-purple-100 dark:border-purple-900/50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 flex items-center justify-center font-bold">
                                    <i data-lucide="calendar-check" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h2 class="font-heading font-extrabold text-sm text-slate-900 dark:text-purple-100 leading-tight">
                                        Papan Jadwal Temu
                                    </h2>
                                    <span class="text-[11px] text-purple-700 dark:text-purple-300 font-medium">
                                        {{ $business->name }}
                                    </span>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Slot Buka
                            </span>
                        </div>

                        {{-- Date Quick Select Pills --}}
                        <div>
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-purple-300/70 block mb-2">
                                Pilih Tanggal Kunjungan:
                            </span>
                            <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                <div class="p-2.5 rounded-xl bg-purple-600 text-white font-bold shadow-xs">
                                    <div class="text-[10px] opacity-80 uppercase">Hari Ini</div>
                                    <div class="text-xs font-mono tabular-nums">{{ date('d M') }}</div>
                                </div>
                                <div class="p-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-slate-700 dark:text-purple-200 font-semibold border border-purple-100 dark:border-purple-900/50">
                                    <div class="text-[10px] text-slate-400 dark:text-purple-400 uppercase">Besok</div>
                                    <div class="text-xs font-mono tabular-nums">{{ date('d M', strtotime('+1 day')) }}</div>
                                </div>
                                <div class="p-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-slate-700 dark:text-purple-200 font-semibold border border-purple-100 dark:border-purple-900/50">
                                    <div class="text-[10px] text-slate-400 dark:text-purple-400 uppercase">Lusa</div>
                                    <div class="text-xs font-mono tabular-nums">{{ date('d M', strtotime('+2 days')) }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Time Slot Matrix Preview --}}
                        <div>
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-purple-300/70 block mb-2">
                                Rekomendasi Jam Kedatangan:
                            </span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
                                <div class="py-2 px-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-slate-700 dark:text-purple-200 border border-purple-100 dark:border-purple-900/50 font-mono tabular-nums font-semibold">
                                    09:30 WIB
                                </div>
                                <div class="py-2 px-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 font-mono tabular-nums font-bold relative">
                                    11:00 WIB
                                    <span class="absolute -top-1.5 -right-1 px-1 rounded-full text-[8px] bg-emerald-600 text-white font-sans">Tersedia</span>
                                </div>
                                <div class="py-2 px-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-slate-700 dark:text-purple-200 border border-purple-100 dark:border-purple-900/50 font-mono tabular-nums font-semibold">
                                    14:30 WIB
                                </div>
                                <div class="py-2 px-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-slate-700 dark:text-purple-200 border border-purple-100 dark:border-purple-900/50 font-mono tabular-nums font-semibold">
                                    16:00 WIB
                                </div>
                            </div>
                        </div>

                        {{-- Specialist Trust Snapshot --}}
                        <div class="p-3.5 rounded-2xl bg-purple-500/8 dark:bg-purple-500/10 border border-purple-500/15 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold text-xs">
                                    <i data-lucide="user-check" class="w-4.5 h-4.5"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-purple-100">
                                        Praktisi &amp; Terapis Ahli
                                    </div>
                                    <div class="text-[10px] text-purple-700 dark:text-purple-300">
                                        Berlisensi &bull; Protokol Steril
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 text-xs font-extrabold text-amber-500 font-mono">
                                <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                                <span>4.9</span>
                            </div>
                        </div>

                        {{-- Direct Booking Action --}}
                        @if ($landingPage->isPageActive('catalog'))
                            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                                class="w-full py-3 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 dark:bg-purple-600 dark:hover:bg-purple-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition active:scale-98">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                                <span>Lihat Seluruh Layanan &amp; Tarif</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-auto"></i>
                            </a>
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
