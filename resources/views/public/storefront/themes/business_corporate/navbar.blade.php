{{--
    Business Corporate Navbar (`business_corporate`)
    Sharp, architectural corporate header with structured navigation, client access, and cart.
--}}
@php
    $currentLocale = app()->getLocale();
    $currentUrl = url()->current();
    $queryParams = request()->query();
    $idUrl = $currentUrl . '?' . http_build_query(array_merge($queryParams, ['lang' => 'id']));
    $enUrl = $currentUrl . '?' . http_build_query(array_merge($queryParams, ['lang' => 'en']));
@endphp

<header class="sticky top-0 z-40 w-full bg-slate-900 text-white border-b border-slate-800 transition-colors duration-200">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-4">

        {{-- 1. Corporate Identity --}}
        <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group focus:outline-none">
            @if ($business->logo_url || $landingPage->logo_url)
                <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                    class="w-10 h-10 rounded-lg object-cover border border-slate-700 shadow-xs">
            @else
                <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center font-heading font-extrabold text-base text-white shadow-xs">
                    {{ strtoupper(substr($business->name, 0, 2)) }}
                </div>
            @endif
            <div class="flex flex-col min-w-0 text-left">
                <span class="font-heading font-bold text-sm sm:text-base tracking-tight text-white leading-tight truncate max-w-[160px] sm:max-w-[240px]">
                    {{ $business->name }}
                </span>
                <span class="text-[11px] text-slate-400 font-mono tracking-wider truncate uppercase">
                    {{ $activeTheme['industry'] ?? 'Corporate Enterprise' }}
                </span>
            </div>
        </a>

        {{-- 2. Desktop Corporate Navigation --}}
        <nav class="hidden lg:flex items-center gap-1 font-medium text-xs">
            <a href="{{ url('/' . $business->slug) }}"
                class="px-3.5 py-2 rounded-md transition-colors {{ request()->is($business->slug) ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                {{ $landingPage->getNavLabel('home', '{{ __('storefront.nav_home') }}') }}
            </a>

            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="px-3.5 py-2 rounded-md transition-colors {{ request()->is($business->slug . '/katalog*') || request()->is($business->slug . '/produk*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    {{ $landingPage->getNavLabel('catalog', 'Katalog & Solusi') }}
                </a>
            @endif

            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="px-3.5 py-2 rounded-md transition-colors {{ request()->is($business->slug . '/tentang-kami*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    {{ $landingPage->getNavLabel('about', 'Profil Perusahaan') }}
                </a>
            @endif

            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="px-3.5 py-2 rounded-md transition-colors {{ request()->is($business->slug . '/kontak*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    {{ $landingPage->getNavLabel('contact', '{{ __('storefront.nav_contact') }} & Kantor') }}
                </a>
            @endif

            @if ($landingPage->isPageActive('blog'))
                <a href="{{ url('/' . $business->slug . '/artikel') }}"
                    class="px-3.5 py-2 rounded-md transition-colors {{ request()->is($business->slug . '/artikel*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    {{ $landingPage->getNavLabel('blog', 'Insight & Berita') }}
                </a>
            @endif
        </nav>

        {{-- 3. Utility Actions & Cart --}}
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">

            {{-- i18n Pill --}}
            <div class="flex items-center bg-slate-800 p-0.5 rounded-md border border-slate-700 text-[11px] font-mono">
                <a href="{{ $idUrl }}" class="px-2 py-1 rounded transition {{ $currentLocale === 'id' ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">ID</a>
                <a href="{{ $enUrl }}" class="px-2 py-1 rounded transition {{ $currentLocale === 'en' ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">EN</a>
            </div>

            {{-- Dark Mode Toggle --}}
            <button type="button"
                @click="
                    document.documentElement.classList.toggle('dark');
                    const isDark = document.documentElement.classList.contains('dark');
                    localStorage.setItem('storefront_dark_mode', isDark ? 'true' : 'false');
                    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
                "
                class="p-2 rounded-md text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700 transition"
                title="Toggle Mode Tampilan" aria-label="Toggle Mode Tampilan">
                <i data-lucide="sun" class="w-4 h-4 hidden dark:inline"></i>
                <i data-lucide="moon" class="w-4 h-4 inline dark:hidden"></i>
            </button>

            {{-- Order Tracking Trigger --}}
            <button type="button" @click="$dispatch('open-order-track-modal')"
                class="hidden sm:flex items-center p-2 rounded-md text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700 transition"
                title="Lacak Surat Jalan / Order">
                <i data-lucide="search" class="w-4 h-4"></i>
            </button>

            {{-- Customer Auth / Portal --}}
            @if (auth('customer')->check())
                <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-2 px-3 py-1.5 rounded-md bg-slate-800 border border-slate-700 text-xs font-semibold text-white">
                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-blue-400"></i>
                    <span class="hidden md:inline">{{ auth('customer')->user()->name }}</span>
                </a>
            @else
                <a href="{{ route('customer.login', ['store' => $business->slug, 'redirect' => url()->current()]) }}"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold text-slate-200 bg-slate-800 hover:bg-slate-700 border border-slate-700 transition">
                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                    <span>Portal Klien</span>
                </a>
            @endif

            {{-- Cart Button --}}
            <a href="{{ url('/' . $business->slug . '/checkout') }}"
                class="relative p-2 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white transition flex items-center justify-center">
                <i data-lucide="briefcase" class="w-4 h-4"></i>
                <span x-show="$store.cart.count() > 0" x-cloak x-text="$store.cart.count()"
                    class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-blue-600 text-white text-[10px] font-bold flex items-center justify-center font-mono">
                </span>
            </a>

            {{-- Mobile Menu Hamburger --}}
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                class="lg:hidden p-2 rounded-md bg-slate-800 text-white border border-slate-700">
                <i data-lucide="menu" class="w-4 h-4" x-show="!mobileMenuOpen"></i>
                <i data-lucide="x" class="w-4 h-4" x-show="mobileMenuOpen" x-cloak></i>
            </button>
        </div>
    </div>

    {{-- Mobile Drawer --}}
    <div x-show="mobileMenuOpen" x-cloak class="lg:hidden border-t border-slate-800 bg-slate-900 px-4 py-4 space-y-1">
        <a href="{{ url('/' . $business->slug) }}" class="block px-3 py-2 rounded-md text-xs font-semibold text-white">{{ __('storefront.nav_home') }}</a>
        @if ($landingPage->isPageActive('catalog'))
            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="block px-3 py-2 rounded-md text-xs font-semibold text-slate-300">Katalog</a>
        @endif
        @if ($landingPage->isPageActive('about'))
            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="block px-3 py-2 rounded-md text-xs font-semibold text-slate-300">{{ __('storefront.tentang_kami') }}</a>
        @endif
        @if ($landingPage->isPageActive('contact'))
            <a href="{{ url('/' . $business->slug . '/kontak') }}" class="block px-3 py-2 rounded-md text-xs font-semibold text-slate-300">{{ __('storefront.nav_contact') }}</a>
        @endif
    </div>
</header>
