{{-- ========================================================================= --}}
{{-- ARTISAN BAKERY & PATISSERIE STOREFRONT FOOTER (`bakery_cake`)             --}}
{{-- Warm Espresso Velvet Ambiance, Craftsmanship Pillars & Kitchen Schedule   --}}
{{-- ========================================================================= --}}
<footer class="mt-auto bg-[#17120E] text-stone-300/80 border-t border-[#261C16] pt-14 pb-12 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12 mb-12">

            {{-- Col 1: Profil Toko & Dapur Roti --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-2xl object-cover ring-1 ring-amber-500/20 border border-stone-800">
                    @else
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-700 to-amber-900 text-amber-200 flex items-center justify-center font-heading text-lg font-bold shadow-xs border border-amber-600/30">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-medium text-xl text-stone-100 block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-amber-400/90 font-semibold block mt-0.5">
                            Artisan Bakery &amp; Cake Shop
                        </span>
                    </div>
                </div>

                <p class="text-xs text-stone-400 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Menyajikan aneka roti lembut artisanal, pastry mentega murni, dan kue perayaan custom dari oven segar setiap pagi dengan resep klasik pilihan.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-stone-400 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Etalase Roti & Pre-Order --}}
            <div class="space-y-3">
                <h4 class="font-heading font-semibold text-xs uppercase tracking-widest text-stone-100">
                    Etalase &amp; Navigasi
                </h4>
                <ul class="space-y-2.5 text-xs text-stone-400">
                    <li>
                        <a href="{{ url('/' . $business->slug) }}" class="hover:text-amber-300 transition-colors flex items-center gap-2">
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-amber-600"></i>
                            <span>{{ $landingPage->getNavLabel('home', 'Beranda Utama') }}</span>
                        </a>
                    </li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-amber-300 transition-colors flex items-center gap-2">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-amber-600"></i>
                                <span>{{ $landingPage->getNavLabel('catalog', 'Katalog Roti & Pastry') }}</span>
                            </a>
                        </li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-amber-300 transition-colors flex items-center gap-2">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-amber-600"></i>
                                <span>{{ $landingPage->getNavLabel('about', 'Dapur Pembuat & Cerita') }}</span>
                            </a>
                        </li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-amber-300 transition-colors flex items-center gap-2">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-amber-600"></i>
                                <span>{{ $landingPage->getNavLabel('contact', 'Pesanan Khusus & Outlet') }}</span>
                            </a>
                        </li>
                    @endif
                    @if ($landingPage->isPageActive('reservation'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/reservasi') }}" class="hover:text-amber-300 transition-colors flex items-center gap-2">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-amber-600"></i>
                                <span>{{ $landingPage->getNavLabel('reservation', 'Reservasi Acara') }}</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-amber-300 transition-colors text-left flex items-center gap-2">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-500"></i>
                            <span>{{ __('storefront.status_pre_order_kue') }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Pre-Order Hotline & Jam Dapur --}}
            <div class="space-y-3">
                <h4 class="font-heading font-semibold text-xs uppercase tracking-widest text-stone-100">
                    Layanan &amp; Jadwal
                </h4>
                <div class="space-y-2.5 text-xs text-stone-400">
                    @if (!empty($hasWhatsapp))
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-stone-900/80 hover:bg-stone-800 text-stone-200 font-semibold transition border border-stone-800">
                                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>{{ $landingPage->cta_secondary_text ?: 'Konsultasi via WhatsApp' }}</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2 pt-1">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-amber-500 shrink-0"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    @php
                        $dayMapIndo = [
                            'monday' => 'Senin',
                            'tuesday' => 'Selasa',
                            'wednesday' => 'Rabu',
                            'thursday' => 'Kamis',
                            'friday' => 'Jumat',
                            'saturday' => 'Sabtu',
                            'sunday' => 'Minggu',
                        ];
                        $opDays = (array) ($storeSetting?->operating_days ?? []);
                        $formattedDays = !empty($opDays)
                            ? implode(', ', array_map(fn($d) => $dayMapIndo[strtolower($d)] ?? ucfirst($d), array_slice($opDays, 0, 4))) . (count($opDays) > 4 ? ' dkk.' : '')
                            : 'Setiap Hari';
                        $cutOff = $storeSetting?->cut_off_time ? ' (Batas Order: ' . substr($storeSetting->cut_off_time, 0, 5) . ' WIB)' : ' (Batch Pagi Siap 06:30 WIB)';
                    @endphp
                    <div class="p-3.5 rounded-2xl bg-stone-900/60 border border-stone-800/80 text-[11px] text-stone-300 space-y-1">
                        <span class="font-bold text-amber-200 block">Jadwal Buka &amp; Panggang:</span>
                        <p class="leading-relaxed">{{ $formattedDays }}{{ $cutOff }}</p>
                    </div>
                </div>
            </div>

            {{-- Col 4: Standar Mutu Patisserie --}}
            @php
                $universalValues = $landingPage->getUniversalValues();
            @endphp
            <div class="space-y-3">
                <h4 class="font-heading font-semibold text-xs uppercase tracking-widest text-stone-100">
                    Standar Mutu Bakery
                </h4>
                <div class="space-y-2 text-xs text-stone-400">
                    @foreach (array_slice($universalValues, 0, 3) as $val)
                        <div class="p-2.5 rounded-xl bg-stone-900/50 border border-stone-800/60 flex items-center gap-2.5">
                            <i data-lucide="{{ $val['icon'] ?? 'award' }}" class="w-4 h-4 text-amber-400 shrink-0"></i>
                            <span class="truncate"><strong>{{ $val['title'] }}</strong> &ndash; {{ $val['desc'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-stone-800/80 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-stone-500">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-2 text-[11px]">
                <span>Tradisi Roti Artisan &amp; Pastry &bull;</span>
                <a href="https://cooca.id" target="_blank" rel="noopener" class="font-medium text-stone-300 hover:text-white transition">
                    COOCA Storefront
                </a>
            </div>
        </div>
    </div>
</footer>
