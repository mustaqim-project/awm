{{-- ========================================================================= --}}
{{-- AGRICULTURE & FARM STOREFRONT FOOTER (`agriculture_farm`)                --}}
{{-- Agrarian trust pillars, wholesale hotline, farm location & quality promise--}}
{{-- ========================================================================= --}}
<footer class="mt-auto bg-[#050F08] text-emerald-200/80 border-t border-emerald-950 pt-14 pb-10 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10 mb-12">

            {{-- Col 1: Profil Kebun & Sentra Tani --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-2xl object-cover ring-2 ring-emerald-500/20">
                    @else
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-600 to-green-900 text-white flex items-center justify-center font-heading text-lg shadow-xs border border-emerald-500/30">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-normal text-xl text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-amber-400 font-bold block mt-0.5">
                            Sentra Tani &amp; Hasil Bumi
                        </span>
                    </div>
                </div>

                <p class="text-xs text-emerald-200/70 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Menyediakan hasil panen segar berkualitas, bibit tanaman unggul terverifikasi, dan sarana pertanian ramah lingkungan langsung dari petani binaan Nusantara.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-emerald-300/70 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Komoditas & Navigasi Panen --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-emerald-100">
                    Navigasi Panen &amp; Bibit
                </h4>
                <ul class="space-y-2.5 text-xs text-emerald-200/70">
                    <li>
                        <a href="{{ url('/' . $business->slug) }}" class="hover:text-emerald-300 transition-colors flex items-center gap-1.5">
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-emerald-500"></i>
                            <span>{{ __('storefront.home_main') }}</span>
                        </a>
                    </li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-emerald-300 transition-colors flex items-center gap-1.5">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-emerald-500"></i>
                                <span>Katalog Komoditas &amp; Bibit</span>
                            </a>
                        </li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-emerald-300 transition-colors flex items-center gap-1.5">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-emerald-500"></i>
                                <span>{{ __('storefront.profil_kebun_mitra_tani') }}</span>
                            </a>
                        </li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li>
                            <a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-emerald-300 transition-colors flex items-center gap-1.5">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-emerald-500"></i>
                                <span>Pemesanan Partai Besar / B2B</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-emerald-300 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="truck" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <span>Lacak Kiriman Hasil Panen</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Posko Petani & Hotline Panen --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-emerald-100">
                    Posko &amp; Hotline Petani
                </h4>
                <div class="space-y-2.5 text-xs text-emerald-200/70">
                    @if (!empty($hasWhatsapp))
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-800/40 text-emerald-200 font-bold hover:bg-emerald-800/60 transition border border-emerald-600/30">
                                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>WhatsApp Posko Tani</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-emerald-400 shrink-0"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-2xl bg-black/40 border border-emerald-900/40 text-[11px] text-emerald-300/80 space-y-1">
                        <span class="font-bold text-emerald-100 block">Jadwal Petik &amp; Kirim:</span>
                        <p class="leading-relaxed">Senin &ndash; Sabtu: Mulai 05:00 WIB (Kondisi Segar Embun Pagi)</p>
                    </div>
                </div>
            </div>

            {{-- Col 4: Jaminan Standar Mutu Panen --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-emerald-100">
                    Standar Mutu Panen
                </h4>
                <div class="space-y-2 text-xs text-emerald-200/70">
                    <div class="p-2.5 rounded-xl bg-black/40 border border-emerald-900/40 flex items-center gap-2.5">
                        <i data-lucide="sprout" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Sortir Grade Panen Teliti &amp; Higienis</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-black/40 border border-emerald-900/40 flex items-center gap-2.5">
                        <i data-lucide="leaf" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Pertanian Alami Bebas Residu Kimia</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-black/40 border border-emerald-900/40 flex items-center gap-2.5">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Garansi Ganti Baru Jika Rusak Saat Kirim</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright & Attribution Bar --}}
        <div class="pt-6 border-t border-emerald-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-emerald-300/60">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-2 text-[11px]">
                <span>Rantai Pasok Pangan Berkelanjutan &bull;</span>
                <a href="https://cooca.id" target="_blank" rel="noopener" class="font-semibold text-emerald-200 hover:text-white transition">
                    COOCA Agri Commerce
                </a>
            </div>
        </div>
    </div>
</footer>
