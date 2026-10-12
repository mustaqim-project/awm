{{--
    Logistics & Transportation Footer (`logistics_transportation`)
    Industrial cargo & transport footer with 24/7 dispatch hotline, fleet safety guarantees, and waybill tracking.
--}}
<footer class="mt-auto bg-[#0A0E14] text-amber-100/80 border-t border-amber-950/80 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Ekspedisi & Izin Resmi --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-xl object-cover border border-amber-500">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-heading font-black text-lg">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-black text-base text-white block leading-tight uppercase">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-amber-400 font-black">
                            Ekspedisi Kargo &bull; Armada
                        </span>
                    </div>
                </div>

                <p class="text-xs text-slate-300 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Penyedia jasa transportasi kargo, sewa armada truk, dan pengiriman barang terintegrasi dengan jangkauan antar kota dan pulau di Indonesia.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-amber-200/70 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Layanan & Rute --}}
            <div class="space-y-3">
                <h4 class="font-heading font-black text-xs uppercase tracking-wider text-amber-100">
                    Layanan &amp; Rute
                </h4>
                <ul class="space-y-2 text-xs text-slate-300">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-amber-400 transition-colors">Beranda Utama</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-amber-400 transition-colors">Daftar Armada &amp; Tarif</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-amber-400 transition-colors">Jaringan Hub &amp; Rute</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-amber-400 transition-colors">Pool Armada &amp; Kontak</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-amber-400 transition-colors text-left flex items-center gap-1.5 uppercase font-bold">
                            <i data-lucide="scan-line" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Lacak Nomor Resi / Muatan</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Dispatch Center 24 Jam --}}
            <div class="space-y-3">
                <h4 class="font-heading font-black text-xs uppercase tracking-wider text-amber-100">
                    Dispatch Center 24/7
                </h4>
                <div class="space-y-2.5 text-xs text-slate-300">
                    @if (!empty($hasWhatsapp))
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500 text-slate-950 font-black uppercase hover:bg-amber-400 transition">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0"></i>
                                <span>WhatsApp Dispatcher</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0 text-amber-400"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-xl bg-black/50 border border-amber-900/40 text-[11px] text-amber-200/80">
                        <span class="font-black text-white uppercase block mb-0.5">Operasional Pool Armada:</span>
                        Senin - Minggu: 24 Jam Nonstop (Standby Dispatch)
                    </div>
                </div>
            </div>

            {{-- Col 4: Standar Armada & Safety --}}
            <div class="space-y-3">
                <h4 class="font-heading font-black text-xs uppercase tracking-wider text-amber-100">
                    Standar Keselamatan Armada
                </h4>
                <div class="space-y-2 text-xs text-slate-300">
                    <div class="p-2.5 rounded-xl bg-black/50 border border-amber-900/40 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Uji KIR &amp; Perawatan Unit Terjadwal</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-black/50 border border-amber-900/40 flex items-center gap-2">
                        <i data-lucide="file-check" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Surat Jalan &amp; Asuransi Pengangkutan Resmi</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-black/50 border border-amber-900/40 flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4 text-red-400 shrink-0"></i>
                        <span>Driver Berpengalaman &amp; Bersertifikat SIM B2</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-amber-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-amber-200/60">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Sistem Dispatch &amp; Logistik Terintegrasi</span>
                <span class="font-bold text-amber-300 uppercase">COOCA Freight Platform</span>
            </div>
        </div>
    </div>
</footer>
