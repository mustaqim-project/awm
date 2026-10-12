{{--
    Product Showcase Navbar (`product_showcase`)
    Luxury boutique navigation with delicate borders, brand monogram, and VIP order access.
--}}
<header class="sticky top-0 z-40 bg-[#FAF6F3]/90 dark:bg-[#140F12]/90 backdrop-blur-md border-b border-rose-200/50 dark:border-rose-950/60 transition-colors"
    x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- Brand / Monogram --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 rounded-full object-cover border-2 border-rose-300 dark:border-rose-800 shadow-2xs">
                @else
                    <div class="w-10 h-10 rounded-full bg-rose-700 text-white flex items-center justify-center font-heading font-extrabold text-sm tracking-wider shadow-sm border border-rose-400">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-bold text-base sm:text-lg text-slate-900 dark:text-rose-50 tracking-tight group-hover:text-rose-600 transition-colors">
                        {{ $business->name }}
                    </span>
                    <span class="text-[9px] uppercase tracking-widest text-rose-500 font-semibold">Haute Collection</span>
                </div>
            </a>

            {{-- Navigation Links --}}
            <nav class="hidden md:flex items-center gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded-full text-xs font-semibold text-slate-700 dark:text-rose-200/90 hover:text-rose-700 dark:hover:text-rose-300 hover:bg-rose-100/50 dark:hover:bg-rose-950/40 transition">
                    Beranda
                </a>
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-slate-700 dark:text-rose-200/90 hover:text-rose-700 dark:hover:text-rose-300 hover:bg-rose-100/50 dark:hover:bg-rose-950/40 transition">
                        Koleksi Seri
                    </a>
                @endif
                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-slate-700 dark:text-rose-200/90 hover:text-rose-700 dark:hover:text-rose-300 hover:bg-rose-100/50 dark:hover:bg-rose-950/40 transition">
                        Cerita Brand
                    </a>
                @endif
                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-slate-700 dark:text-rose-200/90 hover:text-rose-700 dark:hover:text-rose-300 hover:bg-rose-100/50 dark:hover:bg-rose-950/40 transition">
                        Boutique Concierge
                    </a>
                @endif
            </nav>

            {{-- Actions --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" @click="toggleDarkMode()"
                    class="p-2 sm:p-2.5 rounded-full border border-rose-200 dark:border-rose-900/60 text-slate-600 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition"
                    title="Ganti Mode">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden"></i>
                </button>

                <button type="button" @click="$dispatch('open-order-track-modal')"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-full border border-rose-200 dark:border-rose-900/60 text-xs font-semibold text-slate-700 dark:text-rose-200 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span>Lacak Pesanan</span>
                </button>

                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="relative inline-flex items-center gap-2 px-4 py-2 rounded-full bg-rose-700 hover:bg-rose-800 text-white font-semibold text-xs shadow-md shadow-rose-700/20 transition active:scale-95"
                    title="Tas Belanja">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Tas Belanja</span>
                    <span x-text="$store.cart ? $store.cart.count() : 0"
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-white text-rose-800 min-w-[18px] text-center">
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
                class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-800 dark:text-rose-100 hover:bg-rose-50 dark:hover:bg-rose-950/40">
                Beranda
            </a>
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-800 dark:text-rose-100 hover:bg-rose-50 dark:hover:bg-rose-950/40">
                    Koleksi Seri
                </a>
            @endif
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-800 dark:text-rose-100 hover:bg-rose-50 dark:hover:bg-rose-950/40">
                    Cerita Brand
                </a>
            @endif
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-800 dark:text-rose-100 hover:bg-rose-50 dark:hover:bg-rose-950/40">
                    Concierge &amp; Kontak
                </a>
            @endif
            <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
                class="w-full text-left px-3 py-2 rounded-xl text-sm font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center gap-2">
                <i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i>
                <span>Lacak Pesanan Saya</span>
            </button>
        </div>
    </div>
</header>
