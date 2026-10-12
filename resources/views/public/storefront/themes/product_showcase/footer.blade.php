{{--
    Product Showcase Footer (`product_showcase`)
    Boutique luxury footer with concierge care, beauty/innovation assurance, and order tracking.
--}}
<footer class="mt-auto bg-[#140F12] text-rose-200/80 border-t border-rose-950/80 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Brand & Filosofi --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-full object-cover border border-rose-800">
                    @else
                        <div class="w-10 h-10 rounded-full bg-rose-700 flex items-center justify-center font-heading font-extrabold text-sm text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-bold text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-amber-400 font-semibold">
                            Boutique Formulation
                        </span>
                    </div>
                </div>

                <p class="text-xs text-rose-200/70 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Menghadirkan kreasi produk terbaik dengan integritas bahan baku dan dedikasi pada kepuasan pelanggan.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-rose-300/70 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-rose-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Seri & Navigasi --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-rose-100">
                    Eksplorasi Seri
                </h4>
                <ul class="space-y-2 text-xs text-rose-200/70">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-rose-300 transition-colors">Beranda Utama</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-rose-300 transition-colors">Daftar Seri Produk</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-rose-300 transition-colors">Kisah Di Balik Brand</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-rose-300 transition-colors">Konsultasi Concierge</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-rose-300 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Lacak Paket Kiriman</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: VIP Concierge & Chat --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-rose-100">
                    Boutique Concierge
                </h4>
                <div class="space-y-2.5 text-xs text-rose-200/70">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-rose-900/50 text-rose-200 font-semibold hover:bg-rose-900/80 transition border border-rose-800/60">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>Konsultasi via WhatsApp</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_email || $business->email)
                        <div class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0 text-rose-400"></i>
                            <span>{{ $landingPage->custom_email ?: $business->email }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-2xl bg-black/40 border border-rose-900/40 text-[11px] text-rose-300/70">
                        <span class="font-semibold text-rose-200 block mb-0.5">Layanan Personal:</span>
                        Senin - Sabtu: 09:00 - 20:00 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Keaslian & Sertifikasi --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-rose-100">
                    Jaminan Kualitas
                </h4>
                <div class="space-y-2 text-xs text-rose-200/70">
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-rose-900/40 flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>100% Produk Asli &amp; Teruji</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-rose-900/40 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-rose-400 shrink-0"></i>
                        <span>Standar Kebersihan &amp; Keamanan</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-rose-900/40 flex items-center gap-2">
                        <i data-lucide="box" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Kemasan Khusus Anti-Rusak</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-rose-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-rose-300/60">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Didukung oleh</span>
                <span class="font-semibold text-rose-200">COOCA Enterprise</span>
            </div>
        </div>
    </div>
</footer>
