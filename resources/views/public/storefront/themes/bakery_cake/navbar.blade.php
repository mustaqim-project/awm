{{--
    Bakery & Cake Navbar (`bakery_cake`)
    Charming patisserie navigation with custom cake request link, cart, and mobile menu.
--}}
<header class="sticky top-0 z-40 bg-[#FFFDF5]/95 dark:bg-[#1A130D]/95 backdrop-blur-md border-b border-amber-200/60 dark:border-amber-950/60 transition-colors"
    x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- Brand / Logo --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 rounded-2xl object-cover border border-amber-300 dark:border-amber-900 shadow-2xs">
                @else
                    <div class="w-10 h-10 rounded-2xl bg-amber-600 text-white flex items-center justify-center font-heading text-xl shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-normal text-xl sm:text-2xl text-slate-900 dark:text-amber-50 leading-tight group-hover:text-amber-700 transition-colors">
                        {{ $business->name }}
                    </span>
                    <span class="text-[9px] uppercase tracking-widest text-amber-700 dark:text-amber-400 font-bold">Bakery &amp; Patisserie</span>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1.5 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-amber-200/90 hover:text-amber-700 dark:hover:text-amber-400 hover:bg-amber-100/50 dark:hover:bg-amber-950/40 transition">
                    Beranda
                </a>
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-amber-200/90 hover:text-amber-700 dark:hover:text-amber-400 hover:bg-amber-100/50 dark:hover:bg-amber-950/40 transition flex items-center gap-1.5">
                        <i data-lucide="cake" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Etalase Roti &amp; Kue</span>
                    </a>
                @endif
                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-amber-200/90 hover:text-amber-700 dark:hover:text-amber-400 hover:bg-amber-100/50 dark:hover:bg-amber-950/40 transition">
                        Dapur Pembuat
                    </a>
                @endif
                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-amber-200/90 hover:text-amber-700 dark:hover:text-amber-400 hover:bg-amber-100/50 dark:hover:bg-amber-950/40 transition">
                        Outlet &amp; Kontak
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
                    <span>Cek Pesanan</span>
                </button>

                {{-- Cart Trigger --}}
                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-md shadow-amber-600/20 transition active:scale-95"
                    title="Keranjang Roti">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Pesanan</span>
                    <span x-text="$store.cart ? $store.cart.count() : 0"
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-white text-amber-900 min-w-[18px] text-center">
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
                Beranda
            </a>
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-amber-100 hover:bg-amber-100/50 dark:hover:bg-amber-950/40">
                    Katalog Roti &amp; Pastry
                </a>
            @endif
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-amber-100 hover:bg-amber-100/50 dark:hover:bg-amber-950/40">
                    Dapur Pembuat
                </a>
            @endif
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-amber-100 hover:bg-amber-100/50 dark:hover:bg-amber-950/40">
                    Outlet &amp; Kontak
                </a>
            @endif
            <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
                class="w-full text-left px-3 py-2.5 rounded-xl text-sm font-semibold text-amber-700 dark:text-amber-400 hover:bg-amber-100/50 dark:hover:bg-amber-950/40 flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i>
                <span>Cek Status Pre-Order Kue</span>
            </button>
        </div>
    </div>
</header>
