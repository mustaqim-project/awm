{{--
    Construction & Property Footer (`construction_property`)
    Contractor & property developer footer with office studio, engineering license, structural warranty, and SPK tracking.
--}}
<footer class="mt-auto bg-[#131210] text-slate-300 border-t border-slate-800 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Kontraktor --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded object-cover border border-slate-700">
                    @else
                        <div class="w-10 h-10 rounded bg-amber-600 flex items-center justify-center font-heading font-black text-sm text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-black text-base text-white block leading-tight uppercase">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-amber-400 font-bold">
                            Kontraktor &amp; Arsitektur
                        </span>
                    </div>
                </div>

                <p class="text-xs text-slate-400 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Biro rancang bangun dan pengembang properti terpercaya dengan legalitas resmi, tim teknik ahli, dan garansi pemeliharaan.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-slate-400 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Portofolio Proyek --}}
            <div class="space-y-3">
                <h4 class="font-heading font-black text-xs uppercase tracking-wider text-white">
                    Portofolio &amp; Layanan
                </h4>
                <ul class="space-y-2 text-xs text-slate-400 uppercase font-semibold">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-amber-400 transition-colors">Beranda Perusahaan</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-amber-400 transition-colors">Proyek Terbangun &amp; Unit</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-amber-400 transition-colors">Metode Konstruksi &amp; SIPIL</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-amber-400 transition-colors">Konsultasi Site Plan</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-amber-400 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="file-search" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Lacak SPK &amp; Progres Lapangan</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Hotline Proyek & Kantor --}}
            <div class="space-y-3">
                <h4 class="font-heading font-black text-xs uppercase tracking-wider text-white">
                    Kantor &amp; Konsultasi RAB
                </h4>
                <div class="space-y-2.5 text-xs text-slate-400">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded bg-amber-600/20 text-amber-200 font-bold hover:bg-amber-600/30 transition border border-amber-500/30 uppercase text-[11px]">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp Estimator RAB</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_email || $business->email)
                        <div class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0 text-slate-400"></i>
                            <span>{{ $landingPage->custom_email ?: $business->email }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded bg-slate-900 border border-slate-800 text-[11px] text-slate-400">
                        <span class="font-bold text-white block mb-0.5">Jam Kerja Studio &amp; Kantor:</span>
                        Senin - Sabtu: 08:00 - 17:00 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Standar Bangunan & Mutu --}}
            <div class="space-y-3">
                <h4 class="font-heading font-black text-xs uppercase tracking-wider text-white">
                    Standar Konstruksi
                </h4>
                <div class="space-y-2 text-xs text-slate-400">
                    <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Sertifikasi Badan Usaha Jasa Konstruksi (SBUJK)</span>
                    </div>
                    <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Garansi Pemeliharaan Pasca Serah Terima</span>
                    </div>
                    <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center gap-2">
                        <i data-lucide="compass" class="w-4 h-4 text-sky-400 shrink-0"></i>
                        <span>Gambar Kerja Arsitektur &amp; Struktur Lengkap</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Sistem Terpadu Kontraktor</span>
                <span class="font-bold text-white">COOCA Properti</span>
            </div>
        </div>
    </div>
</footer>
