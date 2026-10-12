{{-- ========================================================================= --}}
{{-- ARTISAN BAKERY & PATISSERIE STOREFRONT FOOTER (`bakery_cake`)             --}}
{{-- Warm Hearthstone Trust Pillars, Pre-Order Hotline, Kitchen Hours & Policy  --}}
{{-- ========================================================================= --}}
<footer class="mt-auto bg-[#18110B] text-amber-200/80 border-t border-amber-950 pt-14 pb-10 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10 mb-12">

            {{-- Col 1: Profil Toko & Dapur Roti --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-2xl object-cover ring-2 ring-amber-500/20 border border-amber-800">
                    @else
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-600 to-yellow-900 text-white flex items-center justify-center font-heading text-lg shadow-xs border border-amber-500/30">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-medium text-xl text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-amber-400 font-bold block mt-0.5">
                            Artisan Bakery &amp; Cake Shop
                        </span>
                    </div>
                </div>

                <p class="text-xs text-amber-200/70 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Menyajikan aneka roti lembut artisanal, pastry renyah, dan kue perayaan ulang tahun lezat dari oven segar setiap pagi dengan resep klasik pilihan.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-amber-300/70 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Etalase Roti & Pre-Order --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-amber-100">
                    Etalase Roti &amp; Kue
                </h4>
                <ul class="space-y-2.5 text-xs text-amber-200/70">
                    <li>
                        <a href="{{ url('/' . $business->slug) }}" class="hover:text-amber-300 transition-colors flex items-center gap-1.5">
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-amber-500"></i>
                            <span>Beranda Utama</span>
                        </a>
                    </li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-amber-300 transition-colors flex items-center gap-1.5">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-amber-500"></i>
                                <span>Katalog Roti &amp; Pastry</span>
                            </a>
                        </li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-amber-300 transition-colors flex items-center gap-1.5">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-amber-500"></i>
                                <span>Dapur Pembuat &amp; Resep</span>
                            </a>
                        </li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-amber-300 transition-colors flex items-center gap-1.5">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-amber-500"></i>
                                <span>Custom Cake &amp; Outlet</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-amber-300 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Status Pre-Order Kue</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Pre-Order Hotline & Jam Dapur --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-amber-100">
                    Pre-Order &amp; Konsultasi Cake
                </h4>
                <div class="space-y-2.5 text-xs text-amber-200/70">
                    @if (!empty($hasWhatsapp))
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-800/40 text-amber-200 font-bold hover:bg-amber-800/60 transition border border-amber-600/30">
                                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>WhatsApp Admin Toko</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-amber-400 shrink-0"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-2xl bg-black/40 border border-amber-900/40 text-[11px] text-amber-300/80 space-y-1">
                        <span class="font-bold text-amber-100 block">Jadwal Panggang &amp; Buka:</span>
                        <p class="leading-relaxed">Setiap Hari: 07:00 &ndash; 21:00 WIB (Batch Pagi Siap 06:30 WIB)</p>
                    </div>
                </div>
            </div>

            {{-- Col 4: Standar Mutu Patisserie --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-amber-100">
                    Standar Mutu Bakery
                </h4>
                <div class="space-y-2 text-xs text-amber-200/70">
                    <div class="p-2.5 rounded-xl bg-black/40 border border-amber-900/40 flex items-center gap-2.5">
                        <i data-lucide="sun" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Fresh Baked Setiap Pagi Hari</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-black/40 border border-amber-900/40 flex items-center gap-2.5">
                        <i data-lucide="award" class="w-4 h-4 text-rose-400 shrink-0"></i>
                        <span>100% Pure Butter Tanpa Pengawet</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-black/40 border border-amber-900/40 flex items-center gap-2.5">
                        <i data-lucide="package" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Box Kemasan Khusus Anti-Guncang</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright & Attribution Bar --}}
        <div class="pt-6 border-t border-amber-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-amber-300/60">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-2 text-[11px]">
                <span>Tradisi Roti Artisan &amp; Pastry &bull;</span>
                <a href="https://cooca.id" target="_blank" rel="noopener" class="font-semibold text-amber-200 hover:text-white transition">
                    COOCA Bakery Commerce
                </a>
            </div>
        </div>
    </div>
</footer>
