{{-- ========================================================================= --}}
{{-- NAVBAR: Booking & Appointment (Salon, Klinik, Studio, Jasa Profesional)  --}}
{{-- ========================================================================= --}}
<header class="sticky top-0 z-40 bg-[#F5F3FF]/90 dark:bg-[#0E0B17]/90 backdrop-blur-xl border-b border-purple-200/70 dark:border-purple-900/40 transition-colors"
    x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- Brand / Studio Identity --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl object-cover border border-purple-300 dark:border-purple-800 shadow-xs group-hover:scale-105 transition-transform duration-200">
                @else
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-700 text-white flex items-center justify-center font-heading font-extrabold text-base shadow-xs shadow-purple-600/30 group-hover:scale-105 transition-transform duration-200">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-extrabold text-base sm:text-lg text-slate-900 dark:text-purple-100 leading-tight group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                        {{ $business->name }}
                    </span>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[9.5px] uppercase tracking-wider text-purple-700 dark:text-purple-300 font-bold">
                            Reservasi &bull; Janji Temu
                        </span>
                    </div>
                </div>
            </a>

            {{-- Desktop Nav Links --}}
            <nav class="hidden md:flex items-center gap-1 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold {{ request()->path() === $business->slug ? 'text-purple-700 dark:text-purple-300 bg-purple-100/70 dark:bg-purple-950/60' : 'text-slate-700 dark:text-purple-200/80 hover:text-purple-700 dark:hover:text-purple-300 hover:bg-purple-100/50 dark:hover:bg-purple-950/40' }} transition">
                    {{ __('storefront.nav_home') }}
                </a>
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-purple-200/80 hover:text-purple-700 dark:hover:text-purple-300 hover:bg-purple-100/50 dark:hover:bg-purple-950/40 transition flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400"></i>
                        <span>{{ __('storefront.menu_layanan_durasi') }}</span>
                    </a>
                @endif
                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-purple-200/80 hover:text-purple-700 dark:hover:text-purple-300 hover:bg-purple-100/50 dark:hover:bg-purple-950/40 transition">
                        {{ __('storefront.staf_fasilitas') }}
                    </a>
                @endif
                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-purple-200/80 hover:text-purple-700 dark:hover:text-purple-300 hover:bg-purple-100/50 dark:hover:bg-purple-950/40 transition">
                        {{ __('storefront.lokasi_kontak') }}
                    </a>
                @endif
            </nav>

            {{-- Right Actions --}}
            <div class="flex items-center gap-2 sm:gap-2.5">
                {{-- Dark Mode Toggle --}}
                <button type="button" @click="toggleDarkMode()"
                    class="w-10 h-10 rounded-2xl border border-purple-200 dark:border-purple-900/60 bg-white/70 dark:bg-purple-950/40 text-slate-700 dark:text-purple-200 hover:bg-purple-100/50 dark:hover:bg-purple-900/40 flex items-center justify-center transition cursor-pointer"
                    title="Ganti Mode Tampilan"
                    aria-label="Toggle dark mode">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block text-amber-400"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden text-purple-700"></i>
                </button>

                {{-- Track Booking Ticket Button --}}
                <button type="button" 
                    @click="$dispatch('open-order-track-modal')"
                    class="hidden sm:inline-flex items-center gap-2 h-10 px-3.5 rounded-2xl border border-purple-200 dark:border-purple-900/60 bg-white/70 dark:bg-purple-950/40 text-xs font-bold text-slate-800 dark:text-purple-200 hover:border-purple-400 hover:text-purple-700 dark:hover:text-purple-300 transition shadow-2xs cursor-pointer">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400"></i>
                    <span>Cek Tiket Reservasi</span>
                </button>

                {{-- Booking Cart Trigger --}}
                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="relative inline-flex items-center gap-2 h-10 px-4 rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md shadow-purple-600/25 transition active:scale-95"
                    title="{{ __('storefront.booking_sessions_list') }}">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Sesi Booking</span>
                    <span x-text="$store.cart ? $store.cart.count() : 0"
                        class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-white text-purple-900 min-w-[20px] text-center tabular-nums shadow-2xs">
                        0
                    </span>
                </a>

                {{-- Mobile Hamburger --}}
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden w-10 h-10 rounded-2xl border border-purple-200 dark:border-purple-900/60 bg-white/70 dark:bg-purple-950/40 text-slate-700 dark:text-purple-200 flex items-center justify-center cursor-pointer"
                    aria-label="{{ __('storefront.open_nav_menu') }}">
                    <i :data-lucide="mobileMenuOpen ? 'x' : 'menu'" class="w-5 h-5"></i>
                </button>
            </div>

        </div>

        {{-- Mobile Drawer --}}
        <div x-show="mobileMenuOpen" 
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden py-4 border-t border-purple-200/70 dark:border-purple-900/40 space-y-2">
            
            <a href="{{ url('/' . $business->slug) }}"
                class="flex items-center gap-2.5 px-3.5 py-3 rounded-2xl text-sm font-bold text-slate-800 dark:text-purple-100 hover:bg-purple-100/60 dark:hover:bg-purple-950/60 transition">
                <i data-lucide="home" class="w-4 h-4 text-purple-600"></i>
                <span>{{ __('storefront.nav_home') }} Reservasi</span>
            </a>

            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="flex items-center gap-2.5 px-3.5 py-3 rounded-2xl text-sm font-bold text-slate-800 dark:text-purple-100 hover:bg-purple-100/60 dark:hover:bg-purple-950/60 transition">
                    <i data-lucide="calendar" class="w-4 h-4 text-purple-600"></i>
                    <span>{{ __('storefront.menu_layanan_slot_jam') }}</span>
                </a>
            @endif

            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="flex items-center gap-2.5 px-3.5 py-3 rounded-2xl text-sm font-bold text-slate-800 dark:text-purple-100 hover:bg-purple-100/60 dark:hover:bg-purple-950/60 transition">
                    <i data-lucide="user-check" class="w-4 h-4 text-purple-600"></i>
                    <span>{{ __('storefront.staf_profesional_tempat') }}</span>
                </a>
            @endif

            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="flex items-center gap-2.5 px-3.5 py-3 rounded-2xl text-sm font-bold text-slate-800 dark:text-purple-100 hover:bg-purple-100/60 dark:hover:bg-purple-950/60 transition">
                    <i data-lucide="map-pin" class="w-4 h-4 text-purple-600"></i>
                    <span>{{ __('storefront.lokasi_kontak') }} Studio</span>
                </a>
            @endif

            <button type="button" 
                @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
                class="w-full text-left px-3.5 py-3 rounded-2xl text-sm font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/40 hover:bg-purple-100/60 flex items-center gap-2.5 transition">
                <i data-lucide="clock" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                <span>Cek Jadwal Tiket Janji Temu</span>
            </button>
        </div>
    </div>
</header>
