{{--
    Portfolio & Creative Footer (`portfolio_creative`)
    Editorial modern studio footer with brief consultation, legal copyright protection, and project tracking.
--}}
<footer class="mt-auto bg-[#09090B] text-zinc-300 border-t border-zinc-850 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Studio Profile --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-xl object-cover border border-zinc-800">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-violet-600 flex items-center justify-center font-heading font-black text-sm text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-black text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-violet-400 font-bold">
                            Creative Studio &amp; Agency
                        </span>
                    </div>
                </div>

                <p class="text-xs text-zinc-400 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Biro desain & kreatif yang fokus membangun citra brand berkarakter kuat, relevan, dan menghasilkan konversi.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-zinc-400 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-violet-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Navigasi Proyek --}}
            <div class="space-y-3">
                <h4 class="font-heading font-black text-xs uppercase tracking-wider text-white">
                    Eksplorasi Studio
                </h4>
                <ul class="space-y-2 text-xs text-zinc-400">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-violet-400 transition-colors">{{ __('storefront.home_studio') }}</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-violet-400 transition-colors">{{ __('storefront.portofolio_layanan') }}</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-violet-400 transition-colors">{{ __('storefront.tentang_kami_tim') }}</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-violet-400 transition-colors">{{ __('storefront.brief_kontak_studio') }}</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-violet-400 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="folder-search" class="w-3.5 h-3.5 text-violet-400"></i>
                            <span>Lacak Progres Proyek &amp; Invoice</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Brief & Chat Hotline --}}
            <div class="space-y-3">
                <h4 class="font-heading font-black text-xs uppercase tracking-wider text-white">
                    Brief &amp; Konsultasi
                </h4>
                <div class="space-y-2.5 text-xs text-zinc-400">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-violet-600/20 text-violet-300 font-bold hover:bg-violet-600/30 transition border border-violet-500/30">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp Creative Desk</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_email || $business->email)
                        <div class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0 text-zinc-400"></i>
                            <span>{{ $landingPage->custom_email ?: $business->email }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-xl bg-zinc-900 border border-zinc-800 text-[11px] text-zinc-400">
                        <span class="font-bold text-white block mb-0.5">Jam Operasional Studio:</span>
                        Senin - Jumat: 09:00 - 18:00 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Standar IP & Keamanan --}}
            <div class="space-y-3">
                <h4 class="font-heading font-black text-xs uppercase tracking-wider text-white">
                    Standar Legalitas &amp; Hak Cipta
                </h4>
                <div class="space-y-2 text-xs text-zinc-400">
                    <div class="p-2.5 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>100% Hak Komersial &amp; Non-Disclosure (NDA)</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center gap-2">
                        <i data-lucide="layers" class="w-4 h-4 text-violet-400 shrink-0"></i>
                        <span>File Asli Vector / Figma / Raw Deliverable</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center gap-2">
                        <i data-lucide="file-check" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Kontrak &amp; Invoice Resmi Perusahaan</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-zinc-400">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Didukung oleh</span>
                <span class="font-bold text-white">COOCA Creative Hub</span>
            </div>
        </div>
    </div>
</footer>
