{{--
    Food & Restaurant Navbar (`food_restaurant`)
    Warm dining header with reservation trigger, digital menu navigation, and order cart.
--}}
<header class="sticky top-0 z-40 bg-[#FFFDF5]/95 dark:bg-[#18110D]/95 backdrop-blur-md border-b border-amber-200/60 dark:border-amber-950/60 transition-colors"
    x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- Brand / Logo --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 rounded-2xl object-cover border border-amber-300 dark:border-amber-900 shadow-2xs">
                @else
                    <div class="w-10 h-10 rounded-2xl bg-red-700 text-white flex items-center justify-center font-heading text-lg shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-normal text-lg sm:text-xl text-slate-900 dark:text-amber-50 leading-tight group-hover:text-red-700 transition-colors">
                        {{ $business->name }}
                    </span>
                    <span class="text-[10px] text-amber-700 dark:text-amber-400 font-semibold uppercase tracking-wider">Resto &amp; Kuliner</span>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1.5 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-amber-200/90 hover:text-red-700 dark:hover:text-red-400 hover:bg-amber-100/50 dark:hover:bg-amber-950/40 transition">
                    {{ __('storefront.nav_home') }}
                </a>
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-amber-200/90 hover:text-red-700 dark:hover:text-red-400 hover:bg-amber-100/50 dark:hover:bg-amber-950/40 transition flex items-center gap-1.5">
                        <i data-lucide="book-open" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Buku Menu</span>
                    </a>
                @endif
                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-amber-200/90 hover:text-red-700 dark:hover:text-red-400 hover:bg-amber-100/50 dark:hover:bg-amber-950/40 transition">
                        Dapur &amp; Kisah
                    </a>
                @endif
                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-amber-200/90 hover:text-red-700 dark:hover:text-red-400 hover:bg-amber-100/50 dark:hover:bg-amber-950/40 transition">
                        {{ __('storefront.lokasi_kontak') }}
                    </a>
                @endif
            </nav>

            {{-- Right Actions --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" @click="toggleDarkMode()"
                    class="p-2 sm:p-2.5 rounded-xl border border-amber-200 dark:border-amber-900/60 text-slate-700 dark:text-amber-300 hover:bg-amber-100/50 dark:hover:bg-amber-950/40 transition"
                    title="Ganti Mode">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden"></i>
                </button>

                <button type="button" @click="$dispatch('open-order-track-modal')"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-amber-200 dark:border-amber-900/60 text-xs font-semibold text-slate-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600"></i>
                    <span>{{ __('storefront.check_order') }}</span>
                </button>

                {{-- Cart Trigger --}}
                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white font-semibold text-xs shadow-md shadow-red-700/20 transition active:scale-95"
                    title="Menu Dipesan">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Pesanan</span>
                    <span x-text="$store.cart ? $store.cart.count() : 0"
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-slate-900 min-w-[18px] text-center">
                        0
                    </span>
                </a>

                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded-xl border border-amber-200 dark:border-amber-900/60 text-slate-700 dark:text-amber-200">
                    <i :data-lucide="mobileMenuOpen ? 'x' : 'menu'" class="w-5 h-5"></i>
                </button>
            </div>

        </div>

        {{-- Mobile Drawer --}}
        <div x-show="mobileMenuOpen" x-transition.origin.top.duration.200ms
            class="md:hidden py-4 border-t border-amber-200/60 dark:border-amber-950/60 space-y-2">
            <a href="{{ url('/' . $business->slug) }}"
                class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-amber-100 hover:bg-amber-100/50 dark:hover:bg-amber-950/40">
                {{ __('storefront.nav_home') }}
            </a>
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-amber-100 hover:bg-amber-100/50 dark:hover:bg-amber-950/40">
                    Buku Menu Makanan
                </a>
            @endif
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-amber-100 hover:bg-amber-100/50 dark:hover:bg-amber-950/40">
                    Cerita Dapur Kami
                </a>
            @endif
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-amber-100 hover:bg-amber-100/50 dark:hover:bg-amber-950/40">
                    {{ __('storefront.reservasi_kontak') }}
                </a>
            @endif
            <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
                class="w-full text-left px-3 py-2.5 rounded-xl text-sm font-semibold text-red-700 dark:text-amber-400 hover:bg-amber-100/50 dark:hover:bg-amber-950/40 flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i>
                <span>{{ __('storefront.lacak_status_pesanan_meja_antaran') }}</span>
            </button>
        </div>
    </div>
</header>
