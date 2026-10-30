{{--
    Event & Wedding Navbar (`event_wedding`)
    Romantic wedding organizer header with package explorer, order/booking tracker, and cart drawer.
--}}
<header class="sticky top-0 z-40 bg-[#FFFDF7]/95 dark:bg-[#161013]/95 backdrop-blur-md border-b border-rose-200/60 dark:border-rose-950/60 transition-colors"
    x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- Brand / Logo --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 rounded-full object-cover border border-rose-300 dark:border-rose-800 shadow-2xs">
                @else
                    <div class="w-10 h-10 rounded-full bg-rose-700 text-white flex items-center justify-center font-heading text-xl shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-normal text-xl sm:text-2xl text-slate-900 dark:text-rose-50 leading-tight group-hover:text-rose-700 transition-colors">
                        {{ $business->name }}
                    </span>
                    <span class="text-[9px] uppercase tracking-widest text-amber-600 dark:text-amber-400 font-bold">Wedding &bull; Event Planner</span>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1.5 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded-full text-xs font-semibold text-slate-700 dark:text-rose-200/90 hover:text-rose-700 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                    {{ __('storefront.nav_home') }}
                </a>
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-slate-700 dark:text-rose-200/90 hover:text-rose-700 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>{{ __('storefront.paket_perayaan_dekor') }}</span>
                    </a>
                @endif
                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-slate-700 dark:text-rose-200/90 hover:text-rose-700 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                        Kisah &amp; Tim Kru
                    </a>
                @endif
                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-slate-700 dark:text-rose-200/90 hover:text-rose-700 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                        Konsultasi Tanggal
                    </a>
                @endif
            </nav>

            {{-- Right Actions --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" @click="toggleDarkMode()"
                    class="p-2 sm:p-2.5 rounded-full border border-rose-200 dark:border-rose-900/60 text-slate-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition"
                    title="Ganti Mode">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden"></i>
                </button>

                <button type="button" @click="$dispatch('open-order-track-modal')"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-full border border-rose-200 dark:border-rose-900/60 text-xs font-semibold text-slate-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-rose-600"></i>
                    <span>Cek Booking Event</span>
                </button>

                {{-- Cart Trigger --}}
                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-rose-700 hover:bg-rose-800 text-white font-semibold text-xs shadow-md shadow-rose-700/20 transition active:scale-95"
                    title="{{ __('storefront.selected_package') }}">
                    <i data-lucide="heart" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Paket</span>
                    <span x-text="$store.cart ? $store.cart.count() : 0"
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-slate-900 min-w-[18px] text-center">
                        0
                    </span>
                </a>

                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded-full border border-rose-200 dark:border-rose-900/60 text-slate-700 dark:text-rose-200">
                    <i :data-lucide="mobileMenuOpen ? 'x' : 'menu'" class="w-5 h-5"></i>
                </button>
            </div>

        </div>

        {{-- Mobile Drawer --}}
        <div x-show="mobileMenuOpen" x-transition.origin.top.duration.200ms
            class="md:hidden py-4 border-t border-rose-200/60 dark:border-rose-950/60 space-y-2">
            <a href="{{ url('/' . $business->slug) }}"
                class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-rose-100 hover:bg-rose-50 dark:hover:bg-rose-950/40">
                {{ __('storefront.nav_home') }} Perayaan
            </a>
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-rose-100 hover:bg-rose-50 dark:hover:bg-rose-950/40">
                    {{ __('storefront.pilihan_paket_dekorasi') }}
                </a>
            @endif
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-rose-100 hover:bg-rose-50 dark:hover:bg-rose-950/40">
                    {{ __('storefront.profil_wedding_planner') }}
                </a>
            @endif
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-rose-100 hover:bg-rose-50 dark:hover:bg-rose-950/40">
                    Konsultasi &amp; Cek Tanggal
                </a>
            @endif
            <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
                class="w-full text-left px-3 py-2.5 rounded-xl text-sm font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4 text-rose-600"></i>
                <span>{{ __('storefront.lacak_status_kontrak_booking_event') }}</span>
            </button>
        </div>
    </div>
</header>
