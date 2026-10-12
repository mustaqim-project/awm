{{--
    Agriculture & Farm Footer (`agriculture_farm`)
    Organic farm & agricultural supplier footer with wholesale hotline, fresh logistics guarantee, and delivery tracking.
--}}
<footer class="mt-auto bg-[#09130D] text-emerald-200/80 border-t border-emerald-950/80 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Kebun & Pertanian --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-2xl object-cover border border-emerald-800">
                    @else
                        <div class="w-10 h-10 rounded-2xl bg-emerald-700 flex items-center justify-center font-heading text-lg text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-normal text-xl text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-amber-400 font-bold">
                            Sentra Tani &amp; Hasil Bumi
                        </span>
                    </div>
                </div>

                <p class="text-xs text-emerald-200/70 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Penyedia hasil panen segar berkualitas, bibit tanaman unggul, dan sarana pertanian ramah lingkungan langsung dari petani binaan.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-emerald-300/70 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Komoditas & Navigasi --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-emerald-100">
                    Produk &amp; Panen
                </h4>
                <ul class="space-y-2 text-xs text-emerald-200/70">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-emerald-300 transition-colors">Beranda Utama</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-emerald-300 transition-colors">Daftar Komoditas &amp; Bibit</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-emerald-300 transition-colors">Profil Kebun &amp; Kemitraan Tani</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-emerald-300 transition-colors">Pemesanan Partai Besar / B2B</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-emerald-300 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="truck" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <span>Status Pengiriman Hasil Panen</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Posko Petani & Hotline --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-emerald-100">
                    Posko &amp; Hotline Tani
                </h4>
                <div class="space-y-2.5 text-xs text-emerald-200/70">
                    @if (!empty($hasWhatsapp))
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-700/30 text-emerald-200 font-bold hover:bg-emerald-700/50 transition border border-emerald-600/40">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp Posko Tani</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0 text-emerald-400"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-xl bg-black/40 border border-emerald-900/40 text-[11px] text-emerald-300/70">
                        <span class="font-semibold text-emerald-100 block mb-0.5">Jadwal Pengiriman Panen:</span>
                        Senin - Sabtu: Mulai 06:00 WIB (Petik &amp; Kirim Segar)
                    </div>
                </div>
            </div>

            {{-- Col 4: Standar Mutu & Lingkungan --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-emerald-100">
                    Standar Mutu Panen
                </h4>
                <div class="space-y-2 text-xs text-emerald-200/70">
                    <div class="p-2.5 rounded-xl bg-black/40 border border-emerald-900/40 flex items-center gap-2">
                        <i data-lucide="sprout" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Sortir Grade Panen Ketat &amp; Teliti</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-black/40 border border-emerald-900/40 flex items-center gap-2">
                        <i data-lucide="leaf" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Pertanian Berkelanjutan Bebas Residu</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-black/40 border border-emerald-900/40 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Garansi Kesegaran Hingga Tiba</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-emerald-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-emerald-300/60">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Rantai Pasok Pangan Berkelanjutan</span>
                <span class="font-semibold text-emerald-200">COOCA Agri Commerce</span>
            </div>
        </div>
    </div>
</footer>
