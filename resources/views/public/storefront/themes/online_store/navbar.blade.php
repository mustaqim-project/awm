{{--
    Online Store Navbar (`online_store`)
    Modern e-commerce header with search trigger, live cart badge, tracking modal, and mobile menu.
--}}
<header class="sticky top-0 z-40 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800 transition-colors"
    x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- Brand / Logo --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 rounded-xl object-cover border border-zinc-200 dark:border-zinc-700 shadow-2xs">
                @else
                    <div class="w-10 h-10 rounded-xl bg-zinc-900 dark:bg-zinc-100 flex items-center justify-center font-heading font-black text-lg text-white dark:text-zinc-950 shadow-2xs">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-extrabold text-base sm:text-lg text-zinc-950 dark:text-white leading-tight group-hover:text-indigo-600 transition-colors">
                        {{ $business->name }}
                    </span>
                    <span class="text-[10px] text-zinc-500 uppercase tracking-widest font-semibold">Storefront</span>
                </div>
            </a>

            {{-- Desktop Navigation --}}
            <nav class="hidden md:flex items-center gap-1.5 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3 py-2 rounded-lg text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                    {{ __('storefront.nav_home') }}
                </a>
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3 py-2 rounded-lg text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition flex items-center gap-1">
                        <span>Katalog</span>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400">Shop</span>
                    </a>
                @endif
                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3 py-2 rounded-lg text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                        {{ __('storefront.tentang_kami') }}
                    </a>
                @endif
                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3 py-2 rounded-lg text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                        {{ __('storefront.nav_contact') }}
                    </a>
                @endif
            </nav>

            {{-- Right Actions: Dark Mode, Track, Cart --}}
            <div class="flex items-center gap-2 sm:gap-3">
                {{-- Dark Mode Toggle --}}
                <button type="button" @click="toggleDarkMode()"
                    class="p-2 sm:p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition"
                    title="Ganti Tema Tampilan">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden"></i>
                </button>

                {{-- Track Order --}}
                <button type="button" @click="$dispatch('open-order-track-modal')"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                    <i data-lucide="package-search" class="w-3.5 h-3.5 text-indigo-500"></i>
                    <span>Lacak Resi</span>
                </button>

                {{-- Cart Trigger --}}
                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 hover:bg-indigo-600 dark:hover:bg-indigo-400 dark:hover:text-white transition font-semibold text-xs shadow-xs active:scale-95"
                    title="Keranjang Belanja">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Keranjang</span>
                    <span x-text="$store.cart ? $store.cart.count() : 0"
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-600 text-white dark:bg-zinc-900 dark:text-white min-w-[18px] text-center">
                        0
                    </span>
                </a>

                {{-- Mobile Hamburger --}}
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded-xl border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                    <i :data-lucide="mobileMenuOpen ? 'x' : 'menu'" class="w-5 h-5"></i>
                </button>
            </div>

        </div>

        {{-- Mobile Drawer --}}
        <div x-show="mobileMenuOpen" x-transition.origin.top.duration.200ms
            class="md:hidden py-4 border-t border-zinc-200 dark:border-zinc-800 space-y-2">
            <a href="{{ url('/' . $business->slug) }}"
                class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-900">
                {{ __('storefront.nav_home') }}
            </a>
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-900">
                    {{ __('storefront.katalog_produk') }}
                </a>
            @endif
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-900">
                    {{ __('storefront.tentang_kami') }}
                </a>
            @endif
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-900">
                    {{ __('storefront.nav_contact') }} &amp; Alamat
                </a>
            @endif
            <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
                class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 flex items-center gap-2">
                <i data-lucide="package-search" class="w-4 h-4"></i>
                <span>{{ __('storefront.lacak_pesanan_resi') }}</span>
            </button>
        </div>
    </div>
</header>
