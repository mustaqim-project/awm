{{-- ========================================================================= --}}
{{-- ARTISAN BAKERY & PATISSERIE STOREFRONT NAVBAR (`bakery_cake`)             --}}
{{-- Culinary Atelier Refinement, Clean Single-Line Desktop & Apple Ergonomics --}}
{{-- ========================================================================= --}}
@php
    $currentLocale = app()->getLocale();
    $currentUrl = url()->current();
    $queryParams = request()->query();
@endphp

<header class="sticky top-0 z-40 w-full bg-[#FAF8F5]/92 dark:bg-[#14100E]/92 backdrop-blur-xl border-b border-stone-200/60 dark:border-stone-800/80 transition-colors duration-300">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-18 flex items-center justify-between gap-4">

        {{-- 1. Bakery Brand Identity --}}
        <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group focus:outline-none">
            @if ($business->logo_url || $landingPage->logo_url)
                <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                    class="w-9 h-9 sm:w-11 sm:h-11 rounded-2xl object-cover ring-1 ring-stone-900/10 dark:ring-stone-100/15 group-hover:scale-105 transition-transform duration-300 shadow-2xs">
            @else
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-2xl bg-[#2D1B10] text-amber-300 flex items-center justify-center font-heading text-lg font-bold shadow-2xs group-hover:scale-105 transition-transform duration-300 border border-amber-900/30">
                    <span>{{ strtoupper(substr($business->name, 0, 2)) }}</span>
                </div>
            @endif
            <div class="flex flex-col min-w-0">
                <span class="font-heading font-medium text-lg sm:text-xl text-stone-900 dark:text-stone-50 leading-tight truncate max-w-[160px] sm:max-w-[240px] group-hover:text-amber-800 dark:group-hover:text-amber-300 transition-colors">
                    {{ $business->name }}
                </span>
                <span class="text-[10px] font-semibold text-stone-500 dark:text-stone-400 tracking-wider uppercase truncate mt-0.5">
                    {{ $landingPage->subheadline ? \Illuminate\Support\Str::limit($landingPage->subheadline, 28) : ($activeTheme['industry'] ?? 'Artisan Bakery & Patisserie') }}
                </span>
            </div>
        </a>

        {{-- 2. Desktop Navigation Menu (Single Line, Max Polish) --}}
        <nav class="hidden lg:flex items-center gap-1 bg-stone-900/[0.03] dark:bg-stone-100/[0.04] p-1 rounded-2xl border border-stone-900/[0.05] dark:border-stone-100/[0.06]">
            {{-- Home --}}
            <a href="{{ url('/' . $business->slug) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 min-h-[38px] flex items-center {{ request()->is($business->slug) ? 'bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 shadow-2xs' : 'text-stone-600 dark:text-stone-300 hover:text-stone-900 dark:hover:text-white' }}">
                {{ $landingPage->getNavLabel('home', 'Beranda') }}
            </a>

            {{-- Catalog / Etalase Roti & Kue --}}
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-1.5 min-h-[38px] {{ request()->is($business->slug . '/katalog*') || request()->is($business->slug . '/produk*') ? 'bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 shadow-2xs' : 'text-stone-600 dark:text-stone-300 hover:text-stone-900 dark:hover:text-white' }}">
                    <i data-lucide="cake-slice" class="w-3.5 h-3.5 text-amber-700 dark:text-amber-400"></i>
                    <span>{{ $landingPage->getNavLabel('catalog', 'Katalog Roti & Cake') }}</span>
                </a>
            @endif

            {{-- Dapur Pembuat / About --}}
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 min-h-[38px] flex items-center {{ request()->is($business->slug . '/tentang-kami*') ? 'bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 shadow-2xs' : 'text-stone-600 dark:text-stone-300 hover:text-stone-900 dark:hover:text-white' }}">
                    {{ $landingPage->getNavLabel('about', 'Dapur & Resep') }}
                </a>
            @endif

            {{-- Custom Cake & Pre-Order / Contact --}}
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 min-h-[38px] flex items-center {{ request()->is($business->slug . '/kontak*') ? 'bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 shadow-2xs' : 'text-stone-600 dark:text-stone-300 hover:text-stone-900 dark:hover:text-white' }}">
                    {{ $landingPage->getNavLabel('contact', 'Pesanan Khusus & Outlet') }}
                </a>
            @endif

            {{-- Reservation / Pemesanan Meja / Acara --}}
            @if ($landingPage->isPageActive('reservation'))
                <a href="{{ url('/' . $business->slug . '/reservasi') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 min-h-[38px] flex items-center {{ request()->is($business->slug . '/reservasi*') ? 'bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 shadow-2xs' : 'text-stone-600 dark:text-stone-300 hover:text-stone-900 dark:hover:text-white' }}">
                    {{ $landingPage->getNavLabel('reservation', 'Reservasi Acara') }}
                </a>
            @endif

            {{-- Resep & Info Oven / Blog --}}
            @if ($landingPage->isPageActive('blog'))
                <a href="{{ url('/' . $business->slug . '/artikel') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 min-h-[38px] flex items-center {{ request()->is($business->slug . '/artikel*') ? 'bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 shadow-2xs' : 'text-stone-600 dark:text-stone-300 hover:text-stone-900 dark:hover:text-white' }}">
                    {{ $landingPage->getNavLabel('blog', 'Kisah Dapur') }}
                </a>
            @endif
        </nav>

        {{-- 3. Action Tools: Order Tracking, Dark Mode, Cart, Chat --}}
        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">

            {{-- Status Pre-Order (Desktop) --}}
            <button type="button" @click="$dispatch('open-order-track-modal')"
                class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/70 text-xs font-semibold text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 transition min-h-[44px]"
                title="Cek Status Pesanan">
                <i data-lucide="clock" class="w-4 h-4 text-amber-700 dark:text-amber-400"></i>
                <span class="hidden md:inline">{{ __('storefront.check_order') }}</span>
            </button>

            {{-- Dark Mode Toggle --}}
            <button type="button" @click="toggleDarkMode()"
                class="p-2.5 rounded-xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/70 text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 transition flex items-center justify-center min-h-[44px] min-w-[44px]"
                aria-label="{{ __('storefront.toggle_display_mode') }}"
                title="Mode Terang / Gelap">
                <i data-lucide="sun" class="w-4 h-4 hidden dark:inline text-amber-400"></i>
                <i data-lucide="moon" class="w-4 h-4 inline dark:hidden text-stone-700"></i>
            </button>

            {{-- Keranjang Bakery Trigger (Refined Badge) --}}
            <a href="{{ url('/' . $business->slug . '/checkout') }}"
                class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white font-semibold text-xs shadow-xs transition active:scale-[0.98] min-h-[44px]"
                title="Kotak Pesanan Roti">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                <span class="hidden md:inline">{{ __('storefront.nav_orders') }}</span>
                <span x-show="$store.cart.count() > 0" x-cloak x-text="$store.cart.count()"
                    class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-white text-stone-900 min-w-[18px] text-center shadow-2xs">
                </span>
            </a>

            {{-- WhatsApp Hotline Toko (Desktop) --}}
            @if (!empty($hasWhatsapp))
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="hidden xl:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-800 dark:text-stone-200 font-semibold text-xs transition active:scale-[0.98] min-h-[44px]">
                    <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    <span>{{ $landingPage->cta_secondary_text ?: 'Pesan Custom Cake' }}</span>
                </a>
            @endif

            {{-- Mobile Drawer Trigger --}}
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                class="lg:hidden p-2.5 rounded-xl border border-stone-200 dark:border-stone-800 bg-white/70 dark:bg-stone-900/70 text-stone-800 dark:text-stone-200 transition min-h-[44px] min-w-[44px] flex items-center justify-center"
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
        class="lg:hidden border-t border-stone-200 dark:border-stone-800 bg-[#FAF8F5]/98 dark:bg-[#14100E]/98 backdrop-blur-2xl px-4 py-4 space-y-1">

        <a href="{{ url('/' . $business->slug) }}" @click="mobileMenuOpen = false"
            class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold min-h-[44px] flex items-center {{ request()->is($business->slug) ? 'bg-amber-700/10 text-amber-900 dark:text-amber-300 font-bold' : 'text-stone-700 dark:text-stone-300' }}">
            {{ $landingPage->getNavLabel('home', 'Beranda') }}
        </a>

        @if ($landingPage->isPageActive('catalog'))
            <a href="{{ url('/' . $business->slug . '/katalog') }}" @click="mobileMenuOpen = false"
                class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 min-h-[44px] {{ request()->is($business->slug . '/katalog*') ? 'bg-amber-700/10 text-amber-900 dark:text-amber-300 font-bold' : 'text-stone-700 dark:text-stone-300' }}">
                <i data-lucide="cake-slice" class="w-4 h-4 text-amber-700"></i>
                <span>{{ $landingPage->getNavLabel('catalog', 'Katalog Roti & Cake') }}</span>
            </a>
        @endif

        @if ($landingPage->isPageActive('about'))
            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" @click="mobileMenuOpen = false"
                class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold min-h-[44px] flex items-center {{ request()->is($business->slug . '/tentang-kami*') ? 'bg-amber-700/10 text-amber-900 dark:text-amber-300 font-bold' : 'text-stone-700 dark:text-stone-300' }}">
                {{ $landingPage->getNavLabel('about', 'Dapur & Resep') }}
            </a>
        @endif

        @if ($landingPage->isPageActive('contact'))
            <a href="{{ url('/' . $business->slug . '/kontak') }}" @click="mobileMenuOpen = false"
                class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold min-h-[44px] flex items-center {{ request()->is($business->slug . '/kontak*') ? 'bg-amber-700/10 text-amber-900 dark:text-amber-300 font-bold' : 'text-stone-700 dark:text-stone-300' }}">
                {{ $landingPage->getNavLabel('contact', 'Pesanan Khusus & Outlet') }}
            </a>
        @endif

        @if ($landingPage->isPageActive('reservation'))
            <a href="{{ url('/' . $business->slug . '/reservasi') }}" @click="mobileMenuOpen = false"
                class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold min-h-[44px] flex items-center {{ request()->is($business->slug . '/reservasi*') ? 'bg-amber-700/10 text-amber-900 dark:text-amber-300 font-bold' : 'text-stone-700 dark:text-stone-300' }}">
                {{ $landingPage->getNavLabel('reservation', 'Reservasi Acara') }}
            </a>
        @endif

        @if ($landingPage->isPageActive('blog'))
            <a href="{{ url('/' . $business->slug . '/artikel') }}" @click="mobileMenuOpen = false"
                class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold min-h-[44px] flex items-center {{ request()->is($business->slug . '/artikel*') ? 'bg-amber-700/10 text-amber-900 dark:text-amber-300 font-bold' : 'text-stone-700 dark:text-stone-300' }}">
                {{ $landingPage->getNavLabel('blog', 'Kisah Dapur') }}
            </a>
        @endif

        <div class="pt-3 border-t border-stone-200 dark:border-stone-800 space-y-2">
            <button type="button" @click="mobileMenuOpen = false; $dispatch('open-order-track-modal')"
                class="flex items-center justify-center gap-2 w-full py-3 rounded-xl font-semibold text-xs bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-stone-200 min-h-[44px]">
                <i data-lucide="clock" class="w-4 h-4 text-amber-700"></i>
                <span>{{ __('storefront.cek_status_pre_order_kue') }}</span>
            </button>

            @if (!empty($hasWhatsapp))
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="flex items-center justify-center gap-2 w-full py-3 rounded-xl font-semibold text-xs bg-amber-700 text-white shadow-xs min-h-[44px]">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>{{ $landingPage->cta_secondary_text ?: 'Konsultasi Custom Cake via WhatsApp' }}</span>
                </a>
            @endif
        </div>
    </div>
</header>
