{{--
    Online Store Footer (`online_store`)
    Modern e-commerce footer with shopping assistance, shipping info, return policy, and order tracking.
--}}
<footer class="mt-auto bg-zinc-950 text-zinc-300 border-t border-zinc-800 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Toko & Brand --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-xl object-cover border border-zinc-700">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center font-heading font-black text-lg text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-bold text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[11px] text-zinc-400 font-medium">Toko Online Resmi</span>
                    </div>
                </div>

                <p class="text-xs text-zinc-400 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Belanja produk kebutuhan berkualitas dengan harga kompetitif dan jaminan keamanan transaksi.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-zinc-400 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Belanja & Kategori --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Pusat Belanja
                </h4>
                <ul class="space-y-2 text-xs text-zinc-400">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-indigo-400 transition-colors">{{ __('storefront.main_page') }}</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-indigo-400 transition-colors">{{ __('storefront.semua_produk') }}</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-indigo-400 transition-colors">{{ __('storefront.tentang_toko') }}</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-indigo-400 transition-colors">{{ __('storefront.bantuan_pembeli') }}</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-indigo-400 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="package-search" class="w-3.5 h-3.5 text-indigo-400"></i>
                            <span>{{ __('storefront.lacak_pengiriman_pesanan') }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Customer Care & Chat --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Customer Care
                </h4>
                <div class="space-y-2.5 text-xs text-zinc-400">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-500/20 text-emerald-300 font-semibold hover:bg-emerald-500/30 transition">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp Customer Service</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_email || $business->email)
                        <div class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0 text-zinc-500"></i>
                            <span>{{ $landingPage->custom_email ?: $business->email }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-xl bg-zinc-900 border border-zinc-800 text-[11px] text-zinc-400">
                        <span class="font-semibold text-white block mb-0.5">Jadwal Pengiriman Toko:</span>
                        Senin - Sabtu (Pesanan sebelum 15:00 WIB dikirim di hari yang sama)
                    </div>
                </div>
            </div>

            {{-- Col 4: Keamanan & Pembayaran --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Jaminan Belanja
                </h4>
                <div class="space-y-2 text-xs text-zinc-400">
                    <div class="p-2.5 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>100% Proteksi Pembeli</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center gap-2">
                        <i data-lucide="truck" class="w-4 h-4 text-indigo-400 shrink-0"></i>
                        <span>Pengiriman Ekspedisi Terintegrasi</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center gap-2">
                        <i data-lucide="credit-card" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>QRIS, Transfer Bank &amp; E-Wallet</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Footer Bar --}}
        <div class="pt-6 border-t border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-zinc-400">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Toko Resmi di</span>
                <span class="font-semibold text-white">COOCA Platform</span>
            </div>
        </div>
    </div>
</footer>
