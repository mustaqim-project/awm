{{--
    Bengkel & Otomotif Hero Section (`service_workshop` / `apex_velocity`)
    Racing carbon & mechanic orange aesthetic, Chakra Petch typography, genuine parts & service booking.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24 bg-[#0F172A] text-white">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold tracking-wider uppercase bg-orange-950/60 text-orange-400 border border-orange-500/30">
                    <i data-lucide="wrench" class="w-4 h-4"></i>
                    <span>{{ $activeTheme['industry'] ?? 'Bengkel Resmi & Sparepart Otomotif' }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-white tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Performa Mesin Prima & Servis Terpercaya' }}
                </h1>

                <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    {{ $landingPage->subheadline ?: 'Servis berkala, tune-up injeksi, ganti oli original, perbaikan rem & kaki-kaki, serta ketersediaan suku cadang asli bergaransi dengan mekanik bersertifikat.' }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-orange-950/80 text-orange-300 border border-orange-800/60">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        <span>100% Sparepart Original</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-slate-800 text-slate-200 border border-slate-700">
                        <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                        <span>Scanner Komputer Injeksi</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-800/60">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        <span>Booking Tanpa Antre</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-bold rounded-2xl text-white shadow-lg shadow-orange-600/30 transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5"
                            style="background: linear-gradient(135deg, #EA580C, #C2410C);">
                            <span>{{ $landingPage->cta_primary_text ?: 'Cari Sparepart & Servis' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl border-2 shadow-sm transition-all duration-300 flex items-center gap-2 min-h-[48px] bg-slate-900 text-white border-orange-500/30 hover:bg-slate-800">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400"></i>
                            <span>Konsultasi Keluhan Kendaraan</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1486006920555-c77dce18193b?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-orange-500/30 aspect-square bg-[#1E293B]">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0F172A] via-transparent to-transparent"></div>

                        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-slate-900/95 backdrop-blur-md border border-orange-500/30 text-xs shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-ping"></span>
                                <span class="font-bold text-white">Pit Stop Siap Melayani</span>
                            </div>
                            <span class="text-orange-400 font-mono font-bold">Mekanik Handal</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
