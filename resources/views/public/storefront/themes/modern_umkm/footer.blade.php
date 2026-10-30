{{--
    Modern UMKM Footer (`modern_umkm`)
    Warm, approachable local business footer with store hours, direct chat, and community trust.
--}}
<footer class="mt-auto bg-slate-900 text-slate-300 border-t border-slate-800 pt-12 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">

            {{-- Col 1: Profil Usaha --}}
            <div class="space-y-3.5">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-xl object-cover border border-slate-700">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center font-heading font-extrabold text-base text-white shadow-sm">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-bold text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[11px] text-emerald-400 font-medium">
                            Karya &amp; Usaha Lokal Indonesia
                        </span>
                    </div>
                </div>

                <p class="text-xs text-slate-400 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Melayani sepenuh hati dengan mutu produk terjamin dan harga yang bersahabat untuk semua.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-slate-400 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Halaman --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Navigasi Toko
                </h4>
                <ul class="space-y-2 text-xs text-slate-400">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-emerald-400 transition-colors">{{ __('storefront.nav_home') }}</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-emerald-400 transition-colors">{{ __('storefront.daftar_produk') }}</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-emerald-400 transition-colors">{{ __('storefront.tentang_kami') }}</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-emerald-400 transition-colors">Hubungi Kami</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-emerald-400 transition-colors text-left">{{ __('storefront.lacak_pesanan_anda') }}</button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Kontak & Chat Cepat --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">{{ __('storefront.pemesanan_layanan') }}</h4>
                <div class="space-y-2 text-xs text-slate-400">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-300 font-semibold hover:bg-emerald-500/30 transition">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>Order via WhatsApp</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0 text-slate-500"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-2.5 rounded-xl bg-slate-800/80 border border-slate-700/50 text-[11px] text-slate-400">
                        <span class="font-semibold text-white block">Jam Operasional:</span>
                        Buka setiap hari: 08:00 - 21:00 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Keunggulan Lokal --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">{{ __('storefront.service_guarantee') }}</h4>
                <div class="space-y-2 text-xs text-slate-400">
                    <div class="p-2.5 rounded-xl bg-slate-800/60 border border-slate-700/50 flex items-center gap-2">
                        <i data-lucide="heart" class="w-4 h-4 text-rose-400 shrink-0"></i>
                        <span>Pelayanan Ramah &amp; Jujur</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-800/60 border border-slate-700/50 flex items-center gap-2">
                        <i data-lucide="truck" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Pengiriman Cepat &amp; Aman</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1 text-[11px] text-slate-400">
                <span>Dikelola dengan</span>
                <span class="font-semibold text-white">COOCA ERP</span>
            </div>
        </div>
    </div>
</footer>
