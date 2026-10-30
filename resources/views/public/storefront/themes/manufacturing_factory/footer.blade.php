{{--
    Manufacturing & Factory Footer (`manufacturing_factory`)
    Industrial manufacturing footer with factory coordinates, ISO certifications, bulk delivery, and PO tracking.
--}}
<footer class="mt-auto bg-[#0E131A] text-slate-300 border-t border-slate-800 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: {{ __('storefront.profil_pabrik') }} --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded object-cover border border-slate-700">
                    @else
                        <div class="w-10 h-10 rounded bg-orange-600 flex items-center justify-center font-heading font-black text-sm text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-black text-base text-white block leading-tight uppercase">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-orange-400 font-bold">
                            Fasilitas Manufaktur OEM &bull; ODM
                        </span>
                    </div>
                </div>

                <p class="text-xs text-slate-400 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Pabrik produsen skala industri dengan standar produksi otomatis, uji laboratorium terakreditasi, dan pasokan B2B terpercaya.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-slate-400 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-orange-500 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Navigasi Manufaktur --}}
            <div class="space-y-3">
                <h4 class="font-heading font-black text-xs uppercase tracking-wider text-white">
                    Navigasi Pabrik
                </h4>
                <ul class="space-y-2 text-xs text-slate-400 uppercase font-semibold">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-orange-400 transition-colors">{{ __('storefront.profil_pabrik') }}</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-orange-400 transition-colors">{{ __('storefront.katalog_produk_maklon') }}</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-orange-400 transition-colors">Fasilitas Mesin &amp; Mutu</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-orange-400 transition-colors">{{ __('storefront.kontak_divisi_b2b') }}</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-orange-400 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="truck" class="w-3.5 h-3.5 text-orange-400"></i>
                            <span>{{ __('storefront.lacak_surat_jalan_status_po') }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Hotline RFQ & Maklon --}}
            <div class="space-y-3">
                <h4 class="font-heading font-black text-xs uppercase tracking-wider text-white">
                    Divisi Penjualan Industri
                </h4>
                <div class="space-y-2.5 text-xs text-slate-400">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded bg-orange-600/20 text-orange-200 font-bold hover:bg-orange-600/30 transition border border-orange-500/30 uppercase text-[11px]">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp RFQ Pengadaan</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_email || $business->email)
                        <div class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0 text-slate-400"></i>
                            <span>{{ $landingPage->custom_email ?: $business->email }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded bg-slate-900 border border-slate-800 text-[11px] text-slate-400">
                        <span class="font-bold text-white block mb-0.5">Jam Kerja Pabrik &amp; Loading:</span>
                        Senin - Sabtu: 08:00 - 17:00 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Standar SNI & ISO --}}
            <div class="space-y-3">
                <h4 class="font-heading font-black text-xs uppercase tracking-wider text-white">
                    Sertifikasi &amp; Legalitas
                </h4>
                <div class="space-y-2 text-xs text-slate-400">
                    <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Sertifikasi Sistem Manajemen Mutu ISO</span>
                    </div>
                    <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-orange-400 shrink-0"></i>
                        <span>Standar Nasional Indonesia (SNI)</span>
                    </div>
                    <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-sky-400 shrink-0"></i>
                        <span>Faktur Pajak &amp; Legalitas Industri Sah</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Seluruh hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Sistem Manufaktur Terintegrasi</span>
                <span class="font-bold text-white">COOCA ERP</span>
            </div>
        </div>
    </div>
</footer>
