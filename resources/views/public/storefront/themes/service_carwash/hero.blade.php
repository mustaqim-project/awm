{{--
    Cuci Mobil, Motor & Auto Detailing Hero Section (`service_carwash` / `hydro_shield`)
    Glossy dark hydrophobic aesthetic, Orbitron typography, nano ceramic coating & snow wash.
--}}
<section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24 bg-[#0B0F19] text-white">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold tracking-wider uppercase bg-blue-950/70 text-blue-400 border border-blue-500/30">
                    <i data-lucide="droplet" class="w-4 h-4"></i>
                    <span>{{ $activeTheme['industry'] ?? 'Auto Detailing & Cuci Salju Premium' }}</span>
                </div>

                <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-white tracking-tight leading-[1.12]">
                    {{ $landingPage->headline ?: 'Kilau Sempurna & Proteksi Hidrofobik Maksimal' }}
                </h1>

                <p class="text-base sm:text-lg text-neutral-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    {{ $landingPage->subheadline ?: 'Cuci salju touchless pH-balance, poles bodi 3-step hilangkan baret halus, pembersihan interior uap anti bakteri hingga proteksi nano ceramic coating 9H.' }}
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-blue-950/80 text-blue-300 border border-blue-800/60">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        <span>Shampoo pH-Netral Bebas Baret</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-cyan-950/80 text-cyan-300 border border-cyan-800/60">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>Efek Daun Talas Hidrofobik</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-800/60">
                        <i data-lucide="wind" class="w-3.5 h-3.5"></i>
                        <span>Fogging Interior Steril</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-3">
                    @if ($landingPage->isPageActive('catalog'))
                        <a href="{{ url('/' . $business->slug . '/katalog') }}"
                            class="px-7 py-3.5 text-sm sm:text-base font-bold rounded-2xl text-white shadow-lg shadow-blue-600/30 transition-all duration-300 flex items-center gap-2 min-h-[48px] hover:-translate-y-0.5"
                            style="background: linear-gradient(135deg, #2563EB, #1D4ED8);">
                            <span>{{ $landingPage->cta_primary_text ?: 'Pilih Paket Cuci / Detailing' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if ($hasWhatsapp)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                            class="px-6 py-3.5 text-sm sm:text-base font-semibold rounded-2xl border-2 shadow-sm transition-all duration-300 flex items-center gap-2 min-h-[48px] bg-neutral-900 text-white border-blue-500/30 hover:bg-neutral-800">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400"></i>
                            <span>Booking Antrean Detailing</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    @php
                        $heroImg = $landingPage->hero_image_url ?: 'https://images.unsplash.com/photo-1601362840469-51e4d8d58785?w=1000&auto=format&fit=crop&q=80';
                    @endphp
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-blue-500/30 aspect-square bg-[#111827]">
                        <img src="{{ $heroImg }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0B0F19] via-transparent to-transparent"></div>

                        <div class="absolute bottom-4 left-4 right-4 p-4 rounded-2xl bg-neutral-900/95 backdrop-blur-md border border-blue-500/30 text-xs shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-ping"></span>
                                <span class="font-bold text-white">Bay Cuci Hidrolik Aktif</span>
                            </div>
                            <span class="text-blue-400 font-mono font-bold">Hydro Gloss</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
