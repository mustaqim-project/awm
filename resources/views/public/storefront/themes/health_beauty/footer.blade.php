{{--
    Health & Beauty Footer (`health_beauty`)
    Aesthetic clinic & wellness footer with medical consultation hotline, sterile hygiene guarantee, and order tracking.
--}}
<footer class="mt-auto bg-[#071012] text-teal-200/80 border-t border-teal-950/80 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Klinik & Legalitas --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-full object-cover border border-teal-700">
                    @else
                        <div class="w-10 h-10 rounded-full bg-teal-700 flex items-center justify-center font-heading font-extrabold text-base text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-bold text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-rose-400 font-bold">
                            Klinik Estetika &amp; Skincare
                        </span>
                    </div>
                </div>

                <p class="text-xs text-teal-200/70 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Pusat perawatan kecantikan dan kesehatan kulit terpercaya dengan pendekatan medis dermatologis, teknologi modern, dan produk bersertifikasi resmi.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-teal-300/70 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-teal-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Menu Treatment & Skincare --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-teal-100">
                    Layanan &amp; Skincare
                </h4>
                <ul class="space-y-2 text-xs text-teal-200/70">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-teal-300 transition-colors">Beranda Utama</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-teal-300 transition-colors">Katalog Produk &amp; Treatment</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-teal-300 transition-colors">Profil Dokter &amp; Fasilitas Klinik</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-teal-300 transition-colors">Jadwal Praktek &amp; Lokasi</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-teal-300 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-teal-400"></i>
                            <span>Cek Status Pesanan &amp; Reservasi</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Beauty Desk & Jadwal Praktek --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-teal-100">
                    Konsultasi &amp; Reservasi
                </h4>
                <div class="space-y-2.5 text-xs text-teal-200/70">
                    @if (!empty($hasWhatsapp))
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-teal-700/30 text-teal-200 font-bold hover:bg-teal-700/50 transition border border-teal-600/40">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp Konsultasi Dokter</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0 text-teal-400"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-2xl bg-black/40 border border-teal-900/40 text-[11px] text-teal-300/70">
                        <span class="font-semibold text-teal-100 block mb-0.5">Jam Operasional Klinik:</span>
                        Selasa - Minggu: 09:00 - 18:30 WIB (Praktek Dokter)
                    </div>
                </div>
            </div>

            {{-- Col 4: Standar Medis & Keamanan --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-teal-100">
                    Standar Higienitas Medis
                </h4>
                <div class="space-y-2 text-xs text-teal-200/70">
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-teal-900/40 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-teal-400 shrink-0"></i>
                        <span>Produk 100% Teregistrasi BPOM</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-teal-900/40 flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-rose-400 shrink-0"></i>
                        <span>Sertifikasi Halal &amp; Bebas Zat Terlarang</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-teal-900/40 flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Sterilisasi Autoclave Alat Medis</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-teal-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-teal-300/60">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Sistem Klinik &amp; Apotek Digital</span>
                <span class="font-semibold text-teal-200">COOCA Wellness Platform</span>
            </div>
        </div>
    </div>
</footer>
