{{-- ========================================================================= --}}
{{-- Titan Kinetic (Gym & Fitness Center) FOOTER (`service_fitness`)                                 --}}
{{-- Bespoke footer with brand identity, quick links, open hours and trust badges --}}
{{-- ========================================================================= --}}
<footer class="mt-auto bg-neutral-900 text-neutral-300 dark:bg-[#0B0F19] dark:text-neutral-400 border-t border-black/10 dark:border-white/10 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Bisnis --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-2xl object-cover ring-2 shadow-2xs"
                            style="--tw-ring-color: #10B981;">
                    @else
                        <div class="w-10 h-10 rounded-2xl text-white flex items-center justify-center font-heading text-lg shadow-xs"
                             style="background-color: #10B981;">
                            <span class="font-bold">{{ strtoupper(substr($business->name, 0, 2)) }}</span>
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-bold text-lg text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[11px] font-medium" style="color: #10B981;">
                            Pusat Gym & Kebugaran
                        </span>
                    </div>
                </div>

                <p class="text-xs text-neutral-400 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Fasilitas fitness center modern dengan peralatan beban lengkap, studio kelas cardio, loker steril, dan program bimbingan latihan terarah.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-neutral-400 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 shrink-0 mt-0.5" style="color: #10B981;"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Navigasi Cepat & Katalog --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Navigasi &amp; Layanan
                </h4>
                <ul class="space-y-2 text-xs text-neutral-400">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-white transition-colors">Beranda</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-white transition-colors">Paket Membership & Kelas</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-white transition-colors">Fasilitas & Trainer</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-white transition-colors">Konsultasi Kebugaran</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$store.orderTracking.open()" class="hover:text-white transition-colors text-left flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="package-search" class="w-3.5 h-3.5" style="color: #10B981;"></i>
                            <span>Lacak Status Pesanan</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Jam Operasional Bisnis --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Jam Operasional
                </h4>
                @php
                    $hours = (array) ($landingPage->operational_hours ?? []);
                @endphp
                @if (!empty($hours))
                    <ul class="space-y-1.5 text-xs text-neutral-400 font-mono">
                        @foreach (array_slice($hours, 0, 5) as $h)
                            <li class="flex items-center justify-between">
                                <span>{{ $h['day'] ?? '' }}</span>
                                <span class="font-semibold text-neutral-300">{{ !empty($h['is_open']) ? ($h['hours'] ?? 'Buka') : 'Tutup' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Senin - Sabtu: 08:00 - 21:00 WIB<br>
                        Minggu: 09:00 - 20:00 WIB
                    </p>
                @endif
            </div>

            {{-- Col 4: Kepercayaan & Hubungi Kami --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-white">
                    Pusat Kontak Resmi
                </h4>
                <div class="space-y-2 text-xs text-neutral-400">
                    @if ($landingPage->whatsapp_number || $business->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400"></i>
                            <span>{{ $landingPage->whatsapp_number ?: $business->phone }}</span>
                        </div>
                    @endif
                    @if ($landingPage->custom_email || $business->email)
                        <div class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4" style="color: #10B981;"></i>
                            <span>{{ $landingPage->custom_email ?: $business->email }}</span>
                        </div>
                    @endif
                </div>

                {{-- Official Trust Pill --}}
                <div class="pt-3">
                    <div class="p-3 rounded-2xl bg-white/[0.04] border border-white/5 flex items-center gap-2.5">
                        <i data-lucide="shield-check" class="w-5 h-5 text-emerald-400 shrink-0"></i>
                        <div>
                            <span class="text-xs font-bold text-white block">Toko Resmi Terverifikasi</span>
                            <span class="text-[10px] text-neutral-400">Transaksi Aman &amp; Tepercaya</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-8 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-neutral-500">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak Cipta Dilindungi.</p>
            <p class="flex items-center gap-1.5">
                <span>Platform Sistem Toko oleh</span>
                <strong class="text-white font-semibold tracking-wide">COOCA</strong>
            </p>
        </div>
    </div>
</footer>