{{--
    Booking & Appointment Navbar (`booking_appointment`)
    Live reservation header with slot scheduling trigger, treatment catalog, and booking cart.
--}}
<header class="sticky top-0 z-40 bg-[#F5F3FF]/95 dark:bg-[#110E1C]/95 backdrop-blur-md border-b border-purple-200/60 dark:border-purple-950/60 transition-colors"
    x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- Brand / Logo --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 rounded-2xl object-cover border border-purple-300 dark:border-purple-800 shadow-2xs">
                @else
                    <div class="w-10 h-10 rounded-2xl bg-purple-700 text-white flex items-center justify-center font-heading font-extrabold text-base shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-extrabold text-base sm:text-lg text-slate-900 dark:text-white leading-tight group-hover:text-purple-600 transition-colors">
                        {{ $business->name }}
                    </span>
                    <span class="text-[9px] uppercase tracking-widest text-purple-600 dark:text-purple-400 font-bold">Booking &amp; Reservasi</span>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1.5 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-purple-200/90 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-100/50 dark:hover:bg-purple-950/40 transition">
                    Beranda
                </a>
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-purple-200/90 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-100/50 dark:hover:bg-purple-950/40 transition flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-purple-600"></i>
                        <span>Menu Layanan &amp; Durasi</span>
                    </a>
                @endif
                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-purple-200/90 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-100/50 dark:hover:bg-purple-950/40 transition">
                        Staf &amp; Fasilitas
                    </a>
                @endif
                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-purple-200/90 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-100/50 dark:hover:bg-purple-950/40 transition">
                        Lokasi &amp; Kontak
                    </a>
                @endif
            </nav>

            {{-- Right Actions --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" @click="toggleDarkMode()"
                    class="p-2 sm:p-2.5 rounded-xl border border-purple-200 dark:border-purple-900/60 text-slate-700 dark:text-purple-300 hover:bg-purple-100/50 dark:hover:bg-purple-950/40 transition"
                    title="Ganti Mode">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden"></i>
                </button>

                <button type="button" @click="$dispatch('open-order-track-modal')"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-purple-200 dark:border-purple-900/60 text-xs font-bold text-slate-700 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-950/40 transition">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-purple-600"></i>
                    <span>Cek Jadwal Temu</span>
                </button>

                {{-- Cart Trigger --}}
                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs shadow-md shadow-purple-700/20 transition active:scale-95"
                    title="Daftar Booking">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Booking</span>
                    <span x-text="$store.cart ? $store.cart.count() : 0"
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-white text-purple-900 min-w-[18px] text-center">
                        0
                    </span>
                </a>

                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded-xl border border-purple-200 dark:border-purple-900/60 text-slate-700 dark:text-purple-200">
                    <i :data-lucide="mobileMenuOpen ? 'x' : 'menu'" class="w-5 h-5"></i>
                </button>
            </div>

        </div>

        {{-- Mobile Drawer --}}
        <div x-show="mobileMenuOpen" x-transition.origin.top.duration.200ms
            class="md:hidden py-4 border-t border-purple-200/60 dark:border-purple-950/60 space-y-2">
            <a href="{{ url('/' . $business->slug) }}"
                class="block px-3 py-2.5 rounded-xl text-sm font-bold text-slate-800 dark:text-purple-100 hover:bg-purple-100/50 dark:hover:bg-purple-950/40">
                Beranda
            </a>
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-bold text-slate-800 dark:text-purple-100 hover:bg-purple-100/50 dark:hover:bg-purple-950/40">
                    Layanan &amp; Slot Jam
                </a>
            @endif
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-bold text-slate-800 dark:text-purple-100 hover:bg-purple-100/50 dark:hover:bg-purple-950/40">
                    Staf Profesional &amp; Tempat
                </a>
            @endif
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-bold text-slate-800 dark:text-purple-100 hover:bg-purple-100/50 dark:hover:bg-purple-950/40">
                    Kontak &amp; Alamat
                </a>
            @endif
            <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
                class="w-full text-left px-3 py-2.5 rounded-xl text-sm font-bold text-purple-700 dark:text-purple-300 hover:bg-purple-100/50 dark:hover:bg-purple-950/40 flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4"></i>
                <span>Cek Status Tiket Reservasi</span>
            </button>
        </div>
    </div>
</header>
