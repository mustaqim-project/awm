{{-- ========================================================================= --}}
{{-- ARTISAN BAKERY & PATISSERIE STOREFRONT NAVBAR (`bakery_cake`)             --}}
{{-- Warm Hearthstone Elegance, Pre-Order Status & Custom Cake Hotline          --}}
{{-- ========================================================================= --}}
@php
    $currentLocale = app()->getLocale();
    $currentUrl = url()->current();
    $queryParams = request()->query();
    $idUrl = $currentUrl . '?' . http_build_query(array_merge($queryParams, ['lang' => 'id']));
    $enUrl = $currentUrl . '?' . http_build_query(array_merge($queryParams, ['lang' => 'en']));
@endphp

<header class="sticky top-0 z-40 w-full bg-[#FFFDF5]/92 dark:bg-[#18110B]/92 backdrop-blur-2xl border-b border-amber-200/70 dark:border-amber-950/70 transition-colors duration-300">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-3 sm:gap-6">

        {{-- 1. Bakery Brand Identity --}}
        <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group focus:outline-none">
            @if ($business->logo_url || $landingPage->logo_url)
                <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl object-cover ring-2 ring-amber-500/25 dark:ring-amber-400/30 group-hover:scale-105 transition-transform duration-300 shadow-xs border border-amber-200/50">
            @else
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-br from-amber-600 via-amber-700 to-yellow-900 text-white flex items-center justify-center font-heading text-lg sm:text-xl shadow-xs group-hover:scale-105 transition-transform duration-300 border border-amber-400/30">
                    <span class="italic font-bold">{{ strtoupper(substr($business->name, 0, 2)) }}</span>
                </div>
            @endif
            <div class="flex flex-col min-w-0">
                <span class="font-heading font-medium text-lg sm:text-xl lg:text-2xl text-neutral-900 dark:text-amber-50 leading-tight truncate max-w-[150px] sm:max-w-[240px] group-hover:text-amber-700 dark:group-hover:text-amber-300 transition-colors">
                    {{ $business->name }}
                </span>
                <span class="text-[10px] sm:text-[11px] font-semibold text-amber-800 dark:text-amber-400 tracking-wider uppercase flex items-center gap-1.5 truncate mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>Artisan Bakery &amp; Patisserie</span>
                </span>
            </div>
        </a>

        {{-- 2. Desktop Navigation Menu --}}
        <nav class="hidden lg:flex items-center gap-1 bg-amber-950/[0.03] dark:bg-amber-400/[0.04] p-1.5 rounded-2xl border border-amber-900/[0.06] dark:border-amber-400/[0.08]">
            {{-- Home --}}
            <a href="{{ url('/' . $business->slug) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug) ? 'bg-white dark:bg-amber-900/60 text-amber-900 dark:text-amber-100 shadow-xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-amber-800 dark:hover:text-amber-200' }}">
                {{ $landingPage->getNavLabel('home', 'Beranda') }}
            </a>

            {{-- Catalog / Etalase Roti & Kue --}}
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-1.5 {{ request()->is($business->slug . '/katalog*') || request()->is($business->slug . '/produk*') ? 'bg-white dark:bg-amber-900/60 text-amber-900 dark:text-amber-100 shadow-xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-amber-800 dark:hover:text-amber-200' }}">
                    <i data-lucide="cake-slice" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400"></i>
                    <span>Etalase Roti &amp; Cake</span>
                </a>
            @endif

            {{-- Dapur Pembuat / About --}}
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/tentang-kami*') ? 'bg-white dark:bg-amber-900/60 text-amber-900 dark:text-amber-100 shadow-xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-amber-800 dark:hover:text-amber-200' }}">
                    Dapur Pembuat
                </a>
            @endif

            {{-- Custom Cake & Pre-Order / Contact --}}
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/kontak*') ? 'bg-white dark:bg-amber-900/60 text-amber-900 dark:text-amber-100 shadow-xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-amber-800 dark:hover:text-amber-200' }}">
                    Custom Cake &amp; Outlet
                </a>
            @endif

            {{-- Resep & Info Oven / Blog --}}
            @if ($landingPage->isPageActive('blog'))
                <a href="{{ url('/' . $business->slug . '/artikel') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/artikel*') ? 'bg-white dark:bg-amber-900/60 text-amber-900 dark:text-amber-100 shadow-xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-amber-800 dark:hover:text-amber-200' }}">
                    Kisah Dapur
                </a>
            @endif
        </nav>

        {{-- 3. Action Tools: Order Status, Dark Mode, Cart, Chat Pastry --}}
        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">

            {{-- Status Pre-Order (Desktop) --}}
            <button type="button" @click="$dispatch('open-order-track-modal')"
                class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-amber-900/10 dark:border-amber-400/20 bg-amber-900/[0.03] dark:bg-amber-400/[0.05] text-xs font-semibold text-amber-900 dark:text-amber-200 hover:bg-amber-900/[0.08] dark:hover:bg-amber-400/[0.1] transition min-h-[40px]"
                title="Lacak Status Pre-Order &amp; Pengiriman">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400"></i>
                <span class="hidden md:inline">Cek Pesanan</span>
            </button>

            {{-- Dark Mode Toggle --}}
            <button type="button" @click="toggleDarkMode()"
                class="p-2 sm:p-2.5 rounded-xl border border-amber-900/10 dark:border-amber-400/20 bg-amber-900/[0.03] dark:bg-amber-400/[0.05] text-amber-900 dark:text-amber-200 hover:bg-amber-900/[0.08] dark:hover:bg-amber-400/[0.1] transition flex items-center justify-center min-h-[40px] min-w-[40px]"
                aria-label="Ubah Mode Tampilan"
                title="Mode Terang / Gelap">
                <i data-lucide="sun" class="w-4 h-4 hidden dark:inline text-amber-400"></i>
                <i data-lucide="moon" class="w-4 h-4 inline dark:hidden text-amber-800"></i>
            </button>

            {{-- Keranjang Bakery Trigger --}}
            <a href="{{ url('/' . $business->slug . '/checkout') }}"
                class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-md shadow-amber-700/20 transition active:scale-95 min-h-[40px]"
                title="Kotak Pesanan Roti &amp; Kue">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                <span class="hidden md:inline">Pesanan</span>
                <span x-show="$store.cart.count() > 0" x-cloak x-text="$store.cart.count()"
                    class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-white text-amber-950 min-w-[18px] text-center shadow-2xs">
                </span>
            </a>

            {{-- WhatsApp Hotline Toko (Desktop) --}}
            @if (!empty($hasWhatsapp))
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="hidden xl:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-rose-600/30 bg-rose-500/10 hover:bg-rose-500/20 text-rose-900 dark:text-rose-300 font-semibold text-xs transition active:scale-95 min-h-[40px]">
                    <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Pesan Custom Cake</span>
                </a>
            @endif

            {{-- Mobile Drawer Trigger --}}
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                class="lg:hidden p-2 rounded-xl border border-amber-900/10 dark:border-amber-400/20 bg-amber-900/[0.03] dark:bg-amber-400/[0.05] text-neutral-800 dark:text-neutral-200 transition min-h-[40px] min-w-[40px] flex items-center justify-center"
                aria-label="Menu Navigasi">
                <i data-lucide="menu" class="w-5 h-5" x-show="!mobileMenuOpen"></i>
                <i data-lucide="x" class="w-5 h-5" x-show="mobileMenuOpen" x-cloak></i>
            </button>
        </div>
    </div>

    {{-- Mobile Slide-Down Drawer --}}
    <div x-show="mobileMenuOpen" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="lg:hidden border-t border-amber-200/70 dark:border-amber-950/70 bg-[#FFFDF5]/95 dark:bg-[#18110B]/95 backdrop-blur-2xl px-4 py-4 space-y-1">

        <a href="{{ url('/' . $business->slug) }}" @click="mobileMenuOpen = false"
            class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->is($business->slug) ? 'bg-amber-600/10 text-amber-800 dark:text-amber-300 font-bold' : 'text-neutral-700 dark:text-neutral-300' }}">
            {{ $landingPage->getNavLabel('home', 'Beranda') }}
        </a>

        @if ($landingPage->isPageActive('catalog'))
            <a href="{{ url('/' . $business->slug . '/katalog') }}" @click="mobileMenuOpen = false"
                class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 {{ request()->is($business->slug . '/katalog*') ? 'bg-amber-600/10 text-amber-800 dark:text-amber-300 font-bold' : 'text-neutral-700 dark:text-neutral-300' }}">
                <i data-lucide="cake-slice" class="w-4 h-4 text-amber-600"></i>
                <span>Etalase Roti &amp; Cake</span>
            </a>
        @endif

        @if ($landingPage->isPageActive('about'))
            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" @click="mobileMenuOpen = false"
                class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->is($business->slug . '/tentang-kami*') ? 'bg-amber-600/10 text-amber-800 dark:text-amber-300 font-bold' : 'text-neutral-700 dark:text-neutral-300' }}">
                Dapur Pembuat
            </a>
        @endif

        @if ($landingPage->isPageActive('contact'))
            <a href="{{ url('/' . $business->slug . '/kontak') }}" @click="mobileMenuOpen = false"
                class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->is($business->slug . '/kontak*') ? 'bg-amber-600/10 text-amber-800 dark:text-amber-300 font-bold' : 'text-neutral-700 dark:text-neutral-300' }}">
                Custom Cake &amp; Outlet
            </a>
        @endif

        @if ($landingPage->isPageActive('blog'))
            <a href="{{ url('/' . $business->slug . '/artikel') }}" @click="mobileMenuOpen = false"
                class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->is($business->slug . '/artikel*') ? 'bg-amber-600/10 text-amber-800 dark:text-amber-300 font-bold' : 'text-neutral-700 dark:text-neutral-300' }}">
                Kisah Dapur &amp; Resep
            </a>
        @endif

        <div class="pt-3 border-t border-amber-200/70 dark:border-amber-950/70 space-y-2">
            <button type="button" @click="mobileMenuOpen = false; $dispatch('open-order-track-modal')"
                class="flex items-center justify-center gap-2 w-full py-3 rounded-xl font-semibold text-xs bg-amber-900/[0.04] dark:bg-amber-400/[0.05] text-amber-900 dark:text-amber-200 min-h-[44px]">
                <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i>
                <span>Cek Status Pre-Order Kue</span>
            </button>

            @if (!empty($hasWhatsapp))
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="flex items-center justify-center gap-2 w-full py-3 rounded-xl font-semibold text-xs bg-amber-600 text-white shadow-xs min-h-[44px]">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Konsultasi Custom Cake via WhatsApp</span>
                </a>
            @endif
        </div>
    </div>
</header>
