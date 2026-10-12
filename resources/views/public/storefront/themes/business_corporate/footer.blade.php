{{--
    Business Corporate Footer (`business_corporate`)
    Multi-column enterprise footer with corporate legalities, registered address, and B2B contact.
--}}
<footer class="mt-auto bg-slate-950 text-slate-300 border-t border-slate-800 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10 mb-12">

            {{-- Col 1: Corporate Profile --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-lg object-cover border border-slate-700">
                    @else
                        <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center font-heading font-extrabold text-base text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-bold text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[11px] text-slate-400 font-mono tracking-wider uppercase">
                            Badan Usaha Terverifikasi
                        </span>
                    </div>
                </div>

                <p class="text-xs text-slate-400 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Penyedia solusi B2B dan pengadaan produk berstandar industri dengan legalitas resmi.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-slate-400 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-blue-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Navigasi Solusi --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Navigasi Perusahaan
                </h4>
                <ul class="space-y-2 text-xs text-slate-400">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-blue-400 transition-colors">Beranda Perusahaan</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-blue-400 transition-colors">Katalog &amp; Penawaran</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-blue-400 transition-colors">Profil &amp; Legalitas</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-blue-400 transition-colors">Kontak Kantor</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-blue-400 transition-colors text-left">
                            Lacak Status Dokumen / PO
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Layanan B2B & Kontak --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Layanan &amp; Account Support
                </h4>
                <div class="space-y-2.5 text-xs text-slate-400">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-1.5 text-blue-400 font-semibold hover:underline">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0"></i>
                                <span>WhatsApp: {{ $landingPage->whatsapp_number ?: $business->phone }}</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_email || $business->email)
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0 text-slate-500"></i>
                            <span>{{ $landingPage->custom_email ?: $business->email }}</span>
                        </div>
                    @endif

                    <div class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 text-[11px] text-slate-400">
                        <span class="font-semibold text-white block">Hari Kerja Kantor:</span>
                        Senin - Jumat: 08:30 - 17:00 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Standar & Kepatuhan --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Kepatuhan &amp; Keamanan
                </h4>
                <div class="space-y-2 text-xs text-slate-400">
                    <div class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Faktur Pajak Elektronik Sah</span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-blue-400 shrink-0"></i>
                        <span>Terdaftar Sistem COOCA ERP</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Copyright Bar --}}
        <div class="pt-6 border-t border-slate-800 text-center sm:flex sm:justify-between sm:items-center text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Seluruh hak cipta dilindungi.</p>
            <p class="mt-2 sm:mt-0 font-mono text-[11px]">Powered by COOCA Enterprise</p>
        </div>
    </div>
</footer>
