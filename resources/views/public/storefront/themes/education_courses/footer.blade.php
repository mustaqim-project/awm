{{--
    Education & Courses Footer (`education_courses`)
    Academic institution footer with admission hotline, certificate verification info, and student portal tracker.
--}}
<footer class="mt-auto bg-[#0A101D] text-indigo-200/80 border-t border-indigo-950/80 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Lembaga Pendidikan --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-xl object-cover border border-indigo-700">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-indigo-700 flex items-center justify-center font-heading font-extrabold text-base text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-bold text-base text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-orange-400 font-bold">
                            Akademi &amp; Pusat Pelatihan
                        </span>
                    </div>
                </div>

                <p class="text-xs text-indigo-200/70 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Lembaga pendidikan dan pelatihan keterampilan terpercaya dengan bimbingan mentor praktisi profesional dan sertifikasi terakreditasi.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-indigo-300/70 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Program Belajar & Navigasi --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-indigo-100">
                    Program Akademik
                </h4>
                <ul class="space-y-2 text-xs text-indigo-200/70">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-indigo-300 transition-colors">Beranda Utama</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-indigo-300 transition-colors">Daftar Program Kursus</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-indigo-300 transition-colors">Kurikulum &amp; Profil Mentor</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-indigo-300 transition-colors">Konsultasi Admisi &amp; Beasiswa</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-indigo-300 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-indigo-400"></i>
                            <span>Cek Status Pendaftaran &amp; Kelas</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Admisi & Konseling --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-indigo-100">
                    Admisi &amp; Konseling
                </h4>
                <div class="space-y-2.5 text-xs text-indigo-200/70">
                    @if (!empty($hasWhatsapp))
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-700/30 text-indigo-200 font-bold hover:bg-indigo-700/50 transition border border-indigo-600/40">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp Konselor Siswa</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0 text-indigo-400"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-xl bg-black/40 border border-indigo-900/40 text-[11px] text-indigo-300/70">
                        <span class="font-semibold text-indigo-100 block mb-0.5">Jam Layanan Admisi:</span>
                        Senin - Sabtu: 08:30 - 17:30 WIB
                    </div>
                </div>
            </div>

            {{-- Col 4: Standar Mutu Akademik --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-indigo-100">
                    Jaminan Mutu Kelulusan
                </h4>
                <div class="space-y-2 text-xs text-indigo-200/70">
                    <div class="p-2.5 rounded-xl bg-black/40 border border-indigo-900/40 flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Sertifikat Berlisensi &amp; QR Verifikasi</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-black/40 border border-indigo-900/40 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-indigo-400 shrink-0"></i>
                        <span>Jejaring Komunitas Alumni &amp; Karir</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-black/40 border border-indigo-900/40 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Kurikulum Terakreditasi Industri</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-indigo-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-indigo-300/60">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Platform Pembelajaran Terpadu</span>
                <span class="font-semibold text-indigo-200">COOCA Education Suite</span>
            </div>
        </div>
    </div>
</footer>
