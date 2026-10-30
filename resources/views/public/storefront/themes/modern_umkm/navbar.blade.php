{{--
    Modern UMKM Navbar (`modern_umkm`)
    Warm, friendly, easy-to-use storefront header for local businesses and shoppers.
--}}
@php
    $currentLocale = app()->getLocale();
    $currentUrl = url()->current();
    $queryParams = request()->query();
    $idUrl = $currentUrl . '?' . http_build_query(array_merge($queryParams, ['lang' => 'id']));
    $enUrl = $currentUrl . '?' . http_build_query(array_merge($queryParams, ['lang' => 'en']));
@endphp

<header class="sticky top-0 z-40 w-full bg-white/95 dark:bg-[#071912]/95 backdrop-blur-md border-b border-emerald-900/10 dark:border-emerald-500/20 transition-colors">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-3">

        {{-- Brand --}}
        <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group focus:outline-none">
            @if ($business->logo_url || $landingPage->logo_url)
                <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                    class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl object-cover border border-emerald-500/20 shadow-xs">
            @else
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-emerald-600 flex items-center justify-center font-heading font-extrabold text-base text-white shadow-xs">
                    {{ strtoupper(substr($business->name, 0, 2)) }}
                </div>
            @endif
            <div class="flex flex-col min-w-0 text-left">
                <span class="font-heading font-extrabold text-sm sm:text-base text-neutral-900 dark:text-white leading-tight truncate max-w-[150px] sm:max-w-[220px]">
                    {{ $business->name }}
                </span>
                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold truncate flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ __('storefront.toko_umkm_resmi') }}
                </span>
            </div>
        </a>

        {{-- Desktop Nav --}}
        <nav class="hidden lg:flex items-center gap-1.5 bg-emerald-50/80 dark:bg-emerald-950/40 p-1.5 rounded-xl border border-emerald-500/10">
            <a href="{{ url('/' . $business->slug) }}"
                class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ request()->is($business->slug) ? 'bg-emerald-600 text-white shadow-2xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-emerald-600' }}">
                {{ $landingPage->getNavLabel('home', '{{ __('storefront.nav_home') }}') }}
            </a>

            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ request()->is($business->slug . '/katalog*') || request()->is($business->slug . '/produk*') ? 'bg-emerald-600 text-white shadow-2xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-emerald-600' }}">
                    {{ $landingPage->getNavLabel('catalog', 'Produk & Belanja') }}
                </a>
            @endif

            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ request()->is($business->slug . '/tentang-kami*') ? 'bg-emerald-600 text-white shadow-2xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-emerald-600' }}">
                    {{ $landingPage->getNavLabel('about', '{{ __('storefront.tentang_toko') }}') }}
                </a>
            @endif

            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ request()->is($business->slug . '/kontak*') ? 'bg-emerald-600 text-white shadow-2xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-emerald-600' }}">
                    {{ $landingPage->getNavLabel('contact', 'Alamat & Kontak') }}
                </a>
            @endif
        </nav>

        {{-- Actions --}}
        <div class="flex items-center gap-2 shrink-0">
            {{-- Dark Mode Toggle --}}
            <button type="button"
                @click="
                    document.documentElement.classList.toggle('dark');
                    const isDark = document.documentElement.classList.contains('dark');
                    localStorage.setItem('storefront_dark_mode', isDark ? 'true' : 'false');
                    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
                "
                class="p-2 rounded-xl text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-800 transition"
                title="Ganti Tema">
                <i data-lucide="sun" class="w-4 h-4 hidden dark:inline"></i>
                <i data-lucide="moon" class="w-4 h-4 inline dark:hidden"></i>
            </button>

            {{-- Tracking --}}
            <button type="button" @click="$dispatch('open-order-track-modal')"
                class="p-2 rounded-xl text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-800 transition"
                title="{{ __('storefront.order_tracking') }}">
                <i data-lucide="search" class="w-4 h-4"></i>
            </button>

            {{-- Cart --}}
            <a href="{{ url('/' . $business->slug . '/checkout') }}"
                class="relative p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 transition flex items-center justify-center">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                <span x-show="$store.cart.count() > 0" x-cloak x-text="$store.cart.count()"
                    class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-emerald-600 text-white text-[10px] font-bold flex items-center justify-center font-mono">
                </span>
            </a>

            {{-- Mobile Trigger --}}
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                class="lg:hidden p-2 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-white">
                <i data-lucide="menu" class="w-4 h-4" x-show="!mobileMenuOpen"></i>
                <i data-lucide="x" class="w-4 h-4" x-show="mobileMenuOpen" x-cloak></i>
            </button>
        </div>
    </div>

    {{-- Mobile Drawer --}}
    <div x-show="mobileMenuOpen" x-cloak class="lg:hidden border-t border-emerald-900/10 bg-white dark:bg-[#071912] px-4 py-4 space-y-1">
        <a href="{{ url('/' . $business->slug) }}" class="block px-3 py-2 rounded-lg text-xs font-bold text-emerald-700 dark:text-emerald-300">{{ __('storefront.nav_home') }}</a>
        @if ($landingPage->isPageActive('catalog'))
            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="block px-3 py-2 rounded-lg text-xs font-bold text-neutral-700 dark:text-neutral-300">Katalog</a>
        @endif
        @if ($landingPage->isPageActive('about'))
            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="block px-3 py-2 rounded-lg text-xs font-bold text-neutral-700 dark:text-neutral-300">{{ __('storefront.tentang_toko') }}</a>
        @endif
        @if ($landingPage->isPageActive('contact'))
            <a href="{{ url('/' . $business->slug . '/kontak') }}" class="block px-3 py-2 rounded-lg text-xs font-bold text-neutral-700 dark:text-neutral-300">{{ __('storefront.alamat_kontak') }}</a>
        @endif
    </div>
</header>
