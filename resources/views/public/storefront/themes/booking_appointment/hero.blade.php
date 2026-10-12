{{--
    Booking & Appointment Hero Section (`booking_appointment`)
    Interactive appointment booking flow for salons, clinics, automotive workshops, studios & expert consultations.
--}}
<section class="relative overflow-hidden bg-[#F5F3FF] dark:bg-[#110E1C] border-b border-purple-200/60 dark:border-purple-950/60 transition-colors">
    {{-- Electric Purple Glow --}}
    <div class="absolute -top-32 right-10 w-96 h-96 bg-purple-500/10 dark:bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-rose-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            {{-- Text & Booking Flow --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-purple-100 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800/60 text-xs font-semibold text-purple-800 dark:text-purple-300">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-purple-600"></i>
                    <span class="tracking-wider uppercase text-[10px] font-bold">Reservasi Instan &bull; Tanpa Antre di Tempat</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-slate-900 dark:text-white tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Pilih Jadwal, Reservasi Slot & Nikmati Layanan Terbaik' }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-purple-200/70 max-w-xl leading-relaxed">
                    {{ $landingPage->subheadline ?: 'Kemudahan memilih jam kunjungan, staf atau teknisi favorit, serta konfirmasi instan langsung ke WhatsApp untuk pengalaman bebas antrean.' }}
                </p>

                {{-- Key Trust Metrics --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-purple-900 dark:text-purple-300 border border-purple-200 dark:border-purple-900/60 shadow-2xs">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-purple-600"></i>
                        <span>Slot Jam Real-Time</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60 shadow-2xs">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-rose-500"></i>
                        <span>Pilih Terapis / Teknisi Ahli</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60 shadow-2xs">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Konfirmasi WhatsApp Otomatis</span>
                    </span>
                </div>

                {{-- Action Row --}}
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 rounded-2xl bg-purple-700 hover:bg-purple-800 text-white font-bold text-sm shadow-md shadow-purple-700/25 transition-all flex items-center gap-2 active:scale-95">
                            <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                            <span>Pilih Sesi &amp; Layanan</span>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-2xl bg-white dark:bg-slate-900 hover:bg-purple-50 dark:hover:bg-slate-800 text-slate-900 dark:text-white border border-purple-200 dark:border-purple-800 font-semibold text-sm transition-all flex items-center gap-2 shadow-2xs">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-500"></i>
                            <span>Reservasi via WhatsApp</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Booking Calendar Card Column --}}
            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="relative rounded-3xl overflow-hidden aspect-[4/3] sm:aspect-[5/4] shadow-2xl border border-purple-200/80 dark:border-purple-950/80 bg-white dark:bg-slate-900">
                        @if ($landingPage->hero_image_url)
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-purple-950 via-slate-950 to-rose-950 text-white">
                                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-3 text-purple-400">
                                    <i data-lucide="calendar" class="w-8 h-8"></i>
                                </div>
                                <h3 class="font-heading font-extrabold text-2xl text-white mb-1">{{ $business->name }}</h3>
                                <p class="text-xs text-purple-200/80 max-w-xs">Reservasi slot waktu nyaman dengan terapis dan staf profesional.</p>
                            </div>
                        @endif

                        {{-- Floating Booking Badge --}}
                        <div class="absolute bottom-4 left-4 right-4 p-3.5 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-purple-200/60 dark:border-purple-900/60 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-700">
                                    <i data-lucide="clock" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="font-heading font-bold text-xs text-slate-900 dark:text-white block">Slot Tersedia Hari Ini</span>
                                    <span class="text-[10px] text-slate-500">Pilih jam kedatangan Anda</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-purple-700 dark:text-purple-400">Pilih Slot &rarr;</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
