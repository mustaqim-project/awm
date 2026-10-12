{{--
    Hospitality & Accommodation Footer (`hospitality_accommodation`)
    Resort, hotel & villa footer with front desk hotline, check-in/out policies, and room reservation tracking.
--}}
<footer class="mt-auto bg-[#071318] text-sky-200/80 border-t border-sky-950/80 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Akomodasi --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-full object-cover border border-sky-800">
                    @else
                        <div class="w-10 h-10 rounded-full bg-sky-700 flex items-center justify-center font-heading text-xl text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-normal text-xl text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-amber-400 font-bold">
                            Resort &bull; Villa &bull; Suites
                        </span>
                    </div>
                </div>

                <p class="text-xs text-sky-200/70 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Suaka ketenangan dan kenyamanan istimewa untuk liburan keluarga, perjalanan bisnis, atau bulan madu dengan panorama memikat.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-sky-300/70 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-sky-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Pilihan Kamar & Navigasi --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-sky-100">
                    Navigasi Tamu
                </h4>
                <ul class="space-y-2 text-xs text-sky-200/70">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-sky-300 transition-colors">Beranda Utama</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-sky-300 transition-colors">Daftar Kamar &amp; Villa</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-sky-300 transition-colors">Fasilitas &amp; Galeri</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-sky-300 transition-colors">Lokasi &amp; Penjemputan</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-sky-300 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-sky-400"></i>
                            <span>Cek Status Reservasi</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Front Desk Hotline --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-sky-100">
                    Front Desk &amp; Bantuan
                </h4>
                <div class="space-y-2.5 text-xs text-sky-200/70">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-sky-700/30 text-sky-200 font-bold hover:bg-sky-700/50 transition border border-sky-600/40">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp Concierge</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0 text-sky-400"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-2xl bg-black/40 border border-sky-900/40 text-[11px] text-sky-300/70">
                        <span class="font-semibold text-sky-100 block mb-0.5">Waktu Check-in &amp; Check-out:</span>
                        Check-in: Mulai 14:00 WIB &bull; Check-out: Maks. 12:00 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Kebijakan & Keamanan --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-sky-100">
                    Kebijakan &amp; Standar
                </h4>
                <div class="space-y-2 text-xs text-sky-200/70">
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-sky-900/40 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Protokol Sanitasi Kamar Ketat</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-sky-900/40 flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-sky-400 shrink-0"></i>
                        <span>Keamanan &amp; Resepsionis 24 Jam</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-sky-900/40 flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Layanan Kebersihan Harian (Housekeeping)</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-sky-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-sky-300/60">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Sistem Reservasi Terintegrasi</span>
                <span class="font-semibold text-sky-200">COOCA Hospitality Engine</span>
            </div>
        </div>
    </div>
</footer>
