{{--
    Wholesale & Distributor Footer (`wholesale_distributor`)
    B2B wholesale distributor footer with warehouse docks, fleet distribution areas, and shipment tracking.
--}}
<footer class="mt-auto bg-[#0A1319] text-slate-300 border-t border-slate-800 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Gudang Distribusi --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-xl object-cover border border-slate-700">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center font-heading font-black text-sm text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-bold text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-emerald-400 font-bold">
                            Distributor &bull; Pemasok B2B
                        </span>
                    </div>
                </div>

                <p class="text-xs text-slate-400 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Pusat distribusi grosir terpercaya dengan komitmen pasokan stabil, kualitas mutu terjamin, dan pengiriman armada terintegrasi.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-slate-400 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Katalog & Navigasi --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Pusat Grosir
                </h4>
                <ul class="space-y-2 text-xs text-slate-400">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-emerald-400 transition-colors">Beranda Gudang</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-emerald-400 transition-colors">Katalog Harga Grosir</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-emerald-400 transition-colors">Armada &amp; Jangkauan Rute</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-emerald-400 transition-colors">Pendaftaran Agen / Reseller</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-emerald-400 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="truck" class="w-3.5 h-3.5 text-blue-400"></i>
                            <span>Lacak Muatan / Surat Jalan</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Layanan Agen & Hotline --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Hotline Penjualan Grosir
                </h4>
                <div class="space-y-2.5 text-xs text-slate-400">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600/20 text-emerald-200 font-bold hover:bg-emerald-600/30 transition border border-emerald-500/30">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp Divisi Grosir</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_email || $business->email)
                        <div class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0 text-slate-400"></i>
                            <span>{{ $landingPage->custom_email ?: $business->email }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-[11px] text-slate-400">
                        <span class="font-bold text-white block mb-0.5">Jadwal Muat &amp; Bongkar:</span>
                        Senin - Sabtu: 07:30 - 18:00 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Keamanan Pasokan & Armada --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Jaminan Distribusi
                </h4>
                <div class="space-y-2 text-xs text-slate-400">
                    <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Jaminan Pasokan Asli &amp; Bersegel</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="truck" class="w-4 h-4 text-blue-400 shrink-0"></i>
                        <span>Ekspedisi Armada Truk Pendingin/Box</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="file-check" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Faktur Pajak PPN &amp; Legalitas Sah</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Distribusi didukung</span>
                <span class="font-bold text-white">COOCA Omnichannel ERP</span>
            </div>
        </div>
    </div>
</footer>
