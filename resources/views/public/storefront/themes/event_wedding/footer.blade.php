{{--
    Event & Wedding Footer (`event_wedding`)
    Romantic wedding organizer footer with date consultation, venue coordination guidelines, and event contract tracker.
--}}
<footer class="mt-auto bg-[#140D11] text-rose-200/80 border-t border-rose-950/80 pt-14 pb-8 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

            {{-- Col 1: Profil Wedding Organizer --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    @if ($business->logo_url || $landingPage->logo_url)
                        <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                            class="w-10 h-10 rounded-full object-cover border border-rose-800">
                    @else
                        <div class="w-10 h-10 rounded-full bg-rose-700 flex items-center justify-center font-heading text-xl text-white">
                            {{ strtoupper(substr($business->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-heading font-normal text-xl text-white block leading-tight">
                            {{ $business->name }}
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-amber-400 font-bold">
                            Wedding &amp; Event Organizer
                        </span>
                    </div>
                </div>

                <p class="text-xs text-rose-200/70 leading-relaxed">
                    {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Mitra terpercaya dalam merancang perayaan pernikahan berkelas dengan keindahan dekorasi dan ketenangan pikiran bagi kedua mempelai.')) }}
                </p>

                @if ($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-2 text-xs text-rose-300/70 pt-1">
                        <i data-lucide="map-pin" class="w-4 h-4 text-rose-400 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ $landingPage->custom_address ?: $business->address }}</span>
                    </div>
                @endif
            </div>

            {{-- Col 2: Paket & Layanan --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-rose-100">{{ __('storefront.pilihan_paket_perayaan') }}</h4>
                <ul class="space-y-2 text-xs text-rose-200/70">
                    <li><a href="{{ url('/' . $business->slug) }}" class="hover:text-rose-300 transition-colors">{{ __('storefront.home_main') }}</a></li>
                    @if ($landingPage->isPageActive('catalog'))
                        <li><a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-rose-300 transition-colors">{{ __('storefront.daftar_paket_all_in') }}</a></li>
                    @endif
                    @if ($landingPage->isPageActive('about'))
                        <li><a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="hover:text-rose-300 transition-colors">{{ __('storefront.profil_tim_perencana') }}</a></li>
                    @endif
                    @if ($landingPage->isPageActive('contact'))
                        <li><a href="{{ url('/' . $business->slug . '/kontak') }}" class="hover:text-rose-300 transition-colors">Konsultasi Tanggal Bahagia</a></li>
                    @endif
                    <li>
                        <button type="button" @click="$dispatch('open-order-track-modal')" class="hover:text-rose-300 transition-colors text-left flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-rose-400"></i>
                            <span>{{ __('storefront.status_booking_kontrak_event') }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Col 3: Hotline Wedding Desk --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-rose-100">
                    Wedding Desk &amp; Janji Temu
                </h4>
                <div class="space-y-2.5 text-xs text-rose-200/70">
                    @if ($hasWhatsapp)
                        <div>
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-rose-700/30 text-rose-200 font-bold hover:bg-rose-700/50 transition border border-rose-600/40">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                <span>WhatsApp Wedding Planner</span>
                            </a>
                        </div>
                    @endif

                    @if ($landingPage->custom_phone || $business->phone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 shrink-0 text-rose-400"></i>
                            <span>{{ $landingPage->custom_phone ?: $business->phone }}</span>
                        </div>
                    @endif

                    <div class="p-3 rounded-2xl bg-black/40 border border-rose-900/40 text-[11px] text-rose-300/70">
                        <span class="font-semibold text-rose-100 block mb-0.5">Sesi Konsultasi Studio:</span>
                        Selasa - Minggu: 10:00 - 19:00 WIB (Dengan Janji Temu)
                    </div>
                </div>
            </div>

            {{-- Col 4: Jaminan Acara --}}
            <div class="space-y-3">
                <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-rose-100">
                    Standar Penyelenggaraan
                </h4>
                <div class="space-y-2 text-xs text-rose-200/70">
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-rose-900/40 flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>Sertifikasi Profesional Asosiasi WO</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-rose-900/40 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-rose-400 shrink-0"></i>
                        <span>Kru Koordinasi Hari-H Berseragam Rapi</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-black/40 border border-rose-900/40 flex items-center gap-2">
                        <i data-lucide="file-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Surat Perjanjian Kerja Sama Resmi</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="pt-6 border-t border-rose-950 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-rose-300/60">
            <p>&copy; {{ date('Y') }} {{ $business->name }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-1.5 text-[11px]">
                <span>Sistem Acara Terintegrasi</span>
                <span class="font-semibold text-rose-200">COOCA Event Management</span>
            </div>
        </div>
    </div>
</footer>
