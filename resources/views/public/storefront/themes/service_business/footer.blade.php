{{--
    Service Business Footer (`service_business`)
    Clean service footer with operational hours, pickup hotline, care guarantees, and nota tracker.
--}}
<footer class="mt-auto bg-[#07131F] text-sky-200/80 border-t border-sky-950/80 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Layanan --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-2xl object-cover border border-sky-800">
                    @else
                        <div class="w-10 h-10 rounded-2xl bg-sky-600 flex items-center justify-center font-heading font-extrabold text-base text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-bold text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-sky-400 font-semibold">
                            Layanan Jasa &amp; Perawatan
                        </span>
                    </div>
                </div>

                <p class="text-xs text-sky-200/70 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Layanan profesional dengan standar pengerjaan teruji, peralatan higienis, dan jaminan kepuasan pelanggan.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-sky-300/70 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-sky-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Navigasi Layanan --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-sky-100">{{ __('storefront.navigation_services') }}</h4>
                <ul class="space-y-2 text-xs text-sky-200/70">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-sky-300 transition-colors">{{ __('storefront.nav_home') }}</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-sky-300 transition-colors">{{ __('storefront.daftar_paket_tarif') }}</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-sky-300 transition-colors">Standar Higienis Kerja</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-sky-300 transition-colors">Outlet &amp; Jadwal Antar</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-sky-300 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-sky-400"></i>
                            <span>Lacak Progres Nota Saya</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Antar-Jemput & Hotline --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-sky-100">
                    Hotline &amp; Antar-Jemput
                </h4>
                <div class="space-y-2.5 text-xs text-sky-200/70">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-sky-500/20 text-sky-200 font-semibold hover:bg-sky-500/30 transition border border-sky-500/30">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>Pesan Antar-Jemput WA</span>
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
                        <span class="font-semibold text-sky-100 block mb-0.5">Jam Layanan Outlet:</span>
                        Senin - Minggu: 07:00 - 21:00 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Keamanan & Garansi --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-sky-100">{{ __('storefront.service_guarantee') }}</h4>
                <div class="space-y-2 text-xs text-sky-200/70">
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-sky-900/40 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Garansi Anti-Tertukar (Nota Barcode)</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-sky-900/40 flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-4 h-4 text-sky-400 shrink-0"></i>
                        <span>Servis Ulang Gratis Jika Tidak Bersih</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-sky-900/40 flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Jaminan Selesai Tepat Waktu</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-sky-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-sky-300/60">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Sistem Kasir Jasa oleh</span>
                <span class="font-semibold text-sky-200">COOCA ERP</span>
            </div>
        </div>
    </div>
</footer>
