{{--
    Coffee & Beverage Footer (`coffee_beverage`)
    Artisan coffee roastery footer with cafe opening hours, bean wholesale contact, and order tracking.
--}}
<footer class="mt-auto bg-[#15110E] text-[#CBBDB1] border-t border-[#2C211A] pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Kedai --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-full object-cover border border-[#C88A58]">
                    @else
                        <div class="w-10 h-10 rounded-full bg-[#8B5A2B] flex items-center justify-center font-heading font-bold text-sm text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-bold text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-[#C88A58] font-bold">
                            Specialty Coffee Bar
                        </span>
                    </div>
                </div>

                <p class="text-xs text-[#A8988B] leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Tempat bersantai menikmati kopi sangrai pilihan, mocktail spesial, dan camilan hangat buatan tangan.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-[#A8988B] pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#C88A58] shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Menu & Kisah --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Menu &amp; Roastery
                </h4>
                <ul class="space-y-2 text-xs text-[#A8988B]">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-[#C88A58] transition-colors">Beranda Kedai</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-[#C88A58] transition-colors">Daftar Minuman &amp; Beans</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-[#C88A58] transition-colors">Tentang Biji Kopi Kami</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-[#C88A58] transition-colors">Lokasi &amp; Jam Buka</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-[#C88A58] transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-[#C88A58]"></i>
                            <span>Status Pesanan Takeaway/Antar</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Pesanan & Jam Barista --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Pemesanan &amp; Jam Kedai
                </h4>
                <div class="space-y-2.5 text-xs text-[#A8988B]">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-[#8B5A2B]/30 text-[#FAF7F2] font-semibold hover:bg-[#8B5A2B]/50 transition border border-[#8B5A2B]/40">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>Pesan via WhatsApp</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0 text-[#C88A58]"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-2xl bg-[#201914] border border-[#2C211A] text-[11px] text-[#A8988B]">
                        <span class="font-semibold text-white block mb-0.5">Jam Buka Kedai:</span>
                        Senin - Minggu: 07:30 - 23:00 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Standar Rasa & Sertifikasi --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Standar Seduh
                </h4>
                <div class="space-y-2 text-xs text-[#A8988B]">
                    <div class="p-2.5 rounded-2xl bg-[#201914] border border-[#2C211A] flex items-center gap-2">
                        <i data-lucide="flame" class="w-4 h-4 text-[#C88A58] shrink-0"></i>
                        <span>Sangrai Mingguan Profil Presisi</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-[#201914] border border-[#2C211A] flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>100% Kopi Murni Tanpa Campuran</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-[#201914] border border-[#2C211A] flex items-center gap-2">
                        <i data-lucide="wifi" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Wi-Fi Cepat &amp; Tempat Duduk Nyaman</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-[#2C211A] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#8C7A6D]">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Sistem POS &amp; Toko oleh</span>
                <span class="font-semibold text-white">COOCA POS</span>
            </div>
        </div>
    </div>
</footer>
