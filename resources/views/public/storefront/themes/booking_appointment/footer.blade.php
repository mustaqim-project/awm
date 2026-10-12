{{--
    Booking & Appointment Footer (`booking_appointment`)
    Appointment booking footer with service schedule, cancellation policy, slot inquiry, and booking tracker.
--}}
<footer class="mt-auto bg-[#0E0B17] text-purple-200/80 border-t border-purple-950/80 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Layanan & Reservasi --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-2xl object-cover border border-purple-800">
                    @else
                        <div class="w-10 h-10 rounded-2xl bg-purple-700 flex items-center justify-center font-heading font-extrabold text-base text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-bold text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-purple-400 font-bold">
                            Pusat Reservasi &bull; Janji Temu
                        </span>
                    </div>
                </div>

                <p class="text-xs text-purple-200/70 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Layanan profesional dengan jadwal terencana, staf terlatih berlisensi, dan pengalaman perawatan bebas antrean.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-purple-300/70 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-purple-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Navigasi Jadwal --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-purple-100">
                    Menu &amp; Sesi
                </h4>
                <ul class="space-y-2 text-xs text-purple-200/70">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-purple-300 transition-colors">Beranda</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-purple-300 transition-colors">Daftar Sesi Layanan</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-purple-300 transition-colors">Profil Staf &amp; Ahli</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-purple-300 transition-colors">Lokasi Studio &amp; Peta</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-purple-300 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-purple-400"></i>
                            <span>Cek Jadwal Tiket Janji Temu</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Hotline Reservasi --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-purple-100">
                    Hotline Reservasi WA
                </h4>
                <div class="space-y-2.5 text-xs text-purple-200/70">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-purple-700/30 text-purple-200 font-bold hover:bg-purple-700/50 transition border border-purple-600/40">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp Concierge Booking</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0 text-purple-400"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-2xl bg-black/40 border border-purple-900/40 text-[11px] text-purple-300/70">
                        <span class="font-semibold text-purple-100 block mb-0.5">Jam Praktik &amp; Kunjungan:</span>
                        Selasa - Minggu: 09:00 - 20:00 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Kebijakan Booking --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-purple-100">
                    Jaminan Layanan
                </h4>
                <div class="space-y-2 text-xs text-purple-200/70">
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-purple-900/40 flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-purple-400 shrink-0"></i>
                        <span>On-Time Guarantee (Tanpa Antrean)</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-purple-900/40 flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-4 h-4 text-rose-400 shrink-0"></i>
                        <span>Peralatan Steril &amp; Higienis Standar</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-purple-900/40 flex items-center gap-2">
                        <i data-lucide="refresh-cw" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Reschedule Fleksibel H-1 Kunjungan</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-purple-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-purple-300/60">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Sistem Reservasi didukung</span>
                <span class="font-semibold text-purple-200">COOCA Booking</span>
            </div>
        </div>
    </div>
</footer>
