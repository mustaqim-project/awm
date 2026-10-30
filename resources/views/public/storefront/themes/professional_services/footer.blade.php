{{--
    Professional Services Footer (`professional_services`)
    Formal advisory firm footer with legal disclaimer, partner credentials, document tracker, and office hours.
--}}
<footer class="mt-auto bg-[#0B111E] text-slate-300 border-t border-slate-800 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Kantor --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-lg object-cover border border-slate-700">
                    @else
                        <div class="w-10 h-10 rounded-lg bg-amber-600 flex items-center justify-center font-heading font-bold text-sm text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-bold text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-amber-400 font-semibold">
                            Kantor Konsultan &amp; Advokat
                        </span>
                    </div>
                </div>

                <p class="text-xs text-slate-400 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Layanan konsultasi manajemen, audit pajak, dan pendampingan hukum terintegrasi dengan kepatuhan kode etik profesi.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-slate-400 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Navigasi Kantor --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Bidang Penugasan
                </h4>
                <ul class="space-y-2 text-xs text-slate-400">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-amber-400 transition-colors">{{ __('storefront.home_office') }}</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-amber-400 transition-colors">Praktik &amp; Retainer</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-amber-400 transition-colors">Partner &amp; Legalitas</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-amber-400 transition-colors">{{ __('storefront.kontak_sekretariat') }}</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-amber-400 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="file-search" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>{{ __('storefront.lacak_status_dokumen_perkara') }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Konsultasi & Kontak --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Sekretariat &amp; Hotline
                </h4>
                <div class="space-y-2.5 text-xs text-slate-400">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-amber-500/20 text-amber-200 font-semibold hover:bg-amber-500/30 transition border border-amber-500/30">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp Sekretariat</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_email || $business->email)
                        <div class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0 text-slate-400"></i>
                            <span>{{ $landingPage->custom_email ?: $business->email }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-lg bg-slate-900 border border-slate-800 text-[11px] text-slate-400">
                        <span class="font-semibold text-white block mb-0.5">Waktu Konsultasi Kantor:</span>
                        Senin - Jumat: 08:30 - 17:30 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Standar Etik & Kepatuhan --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Kepatuhan &amp; Integritas
                </h4>
                <div class="space-y-2 text-xs text-slate-400">
                    <div class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Kerahasiaan Klien (Attorney-Client Privilege)</span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Lisensi Praktisi Izin Resmi Republik Indonesia</span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="file-check" class="w-4 h-4 text-sky-400 shrink-0"></i>
                        <span>Faktur &amp; Kwitansi Resmi Perusahaan</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Sistem Advisory didukung</span>
                <span class="font-semibold text-white">COOCA ERP</span>
            </div>
        </div>
    </div>
</footer>
