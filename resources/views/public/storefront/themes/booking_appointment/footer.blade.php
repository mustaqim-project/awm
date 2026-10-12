{{-- ========================================================================= --}}
{{-- FOOTER: Booking & Appointment (Salon, Klinik, Studio, Jasa Profesional)  --}}
{{-- ========================================================================= --}}
<footer class="mt-auto bg-[#0E0B17] text-purple-200/80 border-t border-purple-950/80 pt-14 pb-12 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Layanan & Reservasi --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-2xl object-cover border border-purple-800">
                    @else
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-700 flex items-center justify-center font-heading font-extrabold text-base text-white shadow-xs">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-extrabold text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[9.5px] uppercase tracking-widest text-purple-400 font-bold">
                            Pusat Reservasi &bull; Janji Temu
                        </span>
                    </div>
                </div>

                <p class="text-xs text-purple-200/70 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Layanan profesional dengan jadwal terencana, staf terlatih berlisensi, dan pengalaman perawatan bebas antrean.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2.5 text-xs text-purple-300/80 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-purple-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Navigasi Jadwal & Sesi --}}
            <div class="space-y-3.5">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-purple-100">
                    Menu &amp; Sesi
                </h4>
                <ul class="space-y-2.5 text-xs text-purple-200/70">
                    <li>
                        <a href="{{ url('/' . $business->slug) }}" class="hover:text-purple-300 transition-colors flex items-center gap-1.5">
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-500"></i>
                            <span>Beranda Reservasi</span>
                        </a>
                    </li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-purple-300 transition-colors flex items-center gap-1.5">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-500"></i>
                                <span>Daftar Sesi Layanan</span>
                            </a>
                        </li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-purple-300 transition-colors flex items-center gap-1.5">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-500"></i>
                                <span>Profil Staf &amp; Ahli</span>
                            </a>
                        </li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-purple-300 transition-colors flex items-center gap-1.5">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-500"></i>
                                <span>Lokasi Studio &amp; Peta</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-purple-300 transition-colors text-left flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-purple-400"></i>
                            <span class="font-semibold text-purple-300">Cek Jadwal Tiket Janji Temu</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Hotline Reservasi & Jam Operasional --}}
            <div class="space-y-3.5">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-purple-100">
                    Hotline Reservasi WA
                </h4>
                <div class="space-y-3 text-xs text-purple-200/70">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-purple-700/40 text-purple-200 font-bold hover:bg-purple-700/60 transition border border-purple-600/40">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp Concierge Booking</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2 text-purple-300/80">
                            <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0 text-purple-400"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-3.5 rounded-2xl bg-black/40 border border-purple-900/50 text-[11px] text-purple-300/80 leading-relaxed">
                        <span class="font-bold text-purple-100 block mb-1">Jam Praktik &amp; Kunjungan:</span>
                        Senin - Minggu: 09:00 - 21:00 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Kebijakan Booking & Jaminan Layanan --}}
            <div class="space-y-3.5">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-purple-100">
                    Jaminan Layanan
                </h4>
                <div class="space-y-2 text-xs text-purple-200/70">
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-purple-900/50 flex items-center gap-2.5">
                        <i data-lucide="clock" class="w-4 h-4 text-purple-400 shrink-0"></i>
                        <span>On-Time Guarantee (Tanpa Antrean)</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-purple-900/50 flex items-center gap-2.5">
                        <i data-lucide="sparkles" class="w-4 h-4 text-rose-400 shrink-0"></i>
                        <span>Peralatan Steril &amp; Higienis Standar</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-purple-900/50 flex items-center gap-2.5">
                        <i data-lucide="refresh-cw" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Reschedule Fleksibel H-1 Kunjungan</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-purple-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-purple-300/60">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Seluruh hak cipta dilindungi.</p>
            <div class="flex items-center gap-2 text-[11px]">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Sistem Reservasi Didukung oleh</span>
                <span class="font-semibold text-purple-200">COOCA Storefront</span>
            </div>
        </div>
    </div>
</footer>
