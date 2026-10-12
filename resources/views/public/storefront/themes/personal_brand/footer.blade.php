{{--
    Personal Brand Footer (`personal_brand`)
    Avant-garde creator & keynote speaker footer with business management desk, confidentiality pledge, and session invoice tracking.
--}}
<footer class="mt-auto bg-[#070709] text-zinc-400 border-t border-zinc-900 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Kreator & Signature --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-full object-cover border border-zinc-700">
                    @else
                        <div class="w-10 h-10 rounded-full bg-zinc-800 text-cyan-400 flex items-center justify-center font-heading font-extrabold text-base border border-zinc-700">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-extrabold text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-cyan-400 font-bold">
                            Kreator &bull; Konsultan Mandiri
                        </span>
                    </div>
                </div>

                <p class="text-xs text-zinc-400 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Membantu individu dan organisasi mencapai lonjakan performa melalui strategi pemikiran terstruktur, sesi mentoring, dan produk pengetahuan terpilih.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-zinc-400 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-cyan-500 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Program & Pustaka Karya --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-zinc-200">
                    Program &amp; Navigasi
                </h4>
                <ul class="space-y-2 text-xs text-zinc-400">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-cyan-400 transition-colors">Beranda Utama</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-cyan-400 transition-colors">Daftar Sesi &amp; Toolkit Digital</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-cyan-400 transition-colors">Biografi, Prestasi &amp; Media Kit</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-cyan-400 transition-colors">Undang Sebagai Pembicara / Kolaborasi</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-cyan-400 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="receipt" class="w-3.5 h-3.5 text-cyan-500"></i>
                            <span>Status Sesi &amp; Invoice</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Management Desk & Booking --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-zinc-200">
                    Management Desk
                </h4>
                <div class="space-y-2.5 text-xs text-zinc-400">
                    @if (!empty($hasWhatsapp))
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-zinc-800 text-zinc-100 font-bold hover:bg-zinc-700 transition border border-zinc-700">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-cyan-400"></i>
                                <span>WhatsApp Manager / Asisten</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0 text-cyan-500"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-2xl bg-zinc-900/60 border border-zinc-800 text-[11px] text-zinc-400">
                        <span class="font-semibold text-zinc-200 block mb-0.5">Waktu Respon Email &amp; Chat:</span>
                        Senin - Jumat: 09:00 - 18:00 WIB (Respon Maks. 1x24 Jam)
                    </div>
                </div>
            </div>

            {{-- Col 4: Kepercayaan & Standar Profesional --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-zinc-200">
                    Standar Etika &amp; Privasi
                </h4>
                <div class="space-y-2 text-xs text-zinc-400">
                    <div class="p-2.5 rounded-2xl bg-zinc-900/60 border border-zinc-800 flex items-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Kerahasiaan Klien Terjamin (NDA)</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-zinc-900/60 border border-zinc-800 flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Framework Berbasis Hasil Nyata</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-zinc-900/60 border border-zinc-800 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-cyan-400 shrink-0"></i>
                        <span>Integritas &amp; Profesionalisme Tinggi</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-zinc-900 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-zinc-500">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Ekosistem Kreator Terpadu</span>
                <span class="font-semibold text-zinc-300">COOCA Creator Studio</span>
            </div>
        </div>
    </div>
</footer>
