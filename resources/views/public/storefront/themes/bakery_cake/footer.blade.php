{{--
    Bakery & Cake Footer (`bakery_cake`)
    Patisserie footer with morning baking schedule, preorder policy, hampers service, and order tracking.
--}}
<footer class="mt-auto bg-[#18120D] text-amber-200/80 border-t border-amber-950/80 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Toko --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-2xl object-cover border border-amber-800">
                    @else
                        <div class="w-10 h-10 rounded-2xl bg-amber-600 flex items-center justify-center font-heading text-xl text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-normal text-xl text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[11px] text-amber-400 font-medium">Bakery &amp; Cake Shop</span>
                    </div>
                </div>

                <p class="text-xs text-amber-200/70 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Menyajikan aneka roti lembut, pastry renyah, dan kue ulang tahun lezat dengan resep warisan.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-amber-300/70 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Etalase & Pre-Order --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-amber-100">
                    Etalase Roti &amp; Kue
                </h4>
                <ul class="space-y-2 text-xs text-amber-200/70">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-amber-300 transition-colors">Beranda Toko</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-amber-300 transition-colors">Daftar Roti &amp; Cake</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-amber-300 transition-colors">Dapur Pembuat</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-amber-300 transition-colors">Lokasi Outlet</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-amber-300 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Status Pre-Order Kue</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Pre-Order & Chat Cepat --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-amber-100">
                    Pre-Order &amp; Konsultasi Cake
                </h4>
                <div class="space-y-2.5 text-xs text-amber-200/70">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-amber-500/20 text-amber-200 font-semibold hover:bg-amber-500/30 transition border border-amber-500/30">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp Admin Toko</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0 text-amber-400"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-2xl bg-black/40 border border-amber-900/40 text-[11px] text-amber-300/70">
                        <span class="font-semibold text-amber-100 block mb-0.5">Jam Buka Toko:</span>
                        Setiap Hari: 07:00 - 21:00 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Keamanan & Kualitas --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-amber-100">
                    Standar Mutu
                </h4>
                <div class="space-y-2 text-xs text-amber-200/70">
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-amber-900/40 flex items-center gap-2">
                        <i data-lucide="sun" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Dipanggang Segar Setiap Hari</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-amber-900/40 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>100% Halal &amp; Tanpa Pengawet</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-amber-900/40 flex items-center gap-2">
                        <i data-lucide="box" class="w-4 h-4 text-rose-400 shrink-0"></i>
                        <span>Kotak Kue Kokoh &amp; Rapi</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-amber-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-amber-300/60">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Toko Roti Resmi di</span>
                <span class="font-semibold text-amber-200">COOCA ERP</span>
            </div>
        </div>
    </div>
</footer>
