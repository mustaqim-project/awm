{{-- ========================================================================= --}}
{{-- Artisan Brew (Cafe & Coffee) NAVBAR (`fnb_cafe`)                                 --}}
{{-- Bespoke header with brand identity, auto-hide navigation, cart and tools  --}}
{{-- ========================================================================= --}}
<header class="sticky top-0 z-40 w-full bg-white/90 dark:bg-[#121620]/90 backdrop-blur-2xl border-b border-black/5 dark:border-white/10 transition-colors duration-300"
    x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-3 sm:gap-6">

        {{-- 1. Brand Identity --}}
        <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group focus:outline-hidden">
            @if ($business->logo_url || $landingPage->logo_url)
                <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                    class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl object-cover ring-2 shadow-2xs group-hover:scale-105 transition-transform duration-200"
                    style="--tw-ring-color: #8B5A2B30;">
            @else
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl text-white flex items-center justify-center font-heading text-lg sm:text-xl shadow-xs group-hover:scale-105 transition-transform duration-200"
                     style="background-color: #8B5A2B;">
                    <span class="font-bold">{{ strtoupper(substr($business->name, 0, 2)) }}</span>
                </div>
            @endif
            <div class="flex flex-col min-w-0">
                <span class="font-heading font-bold text-base sm:text-lg text-neutral-900 dark:text-white leading-tight truncate max-w-[150px] sm:max-w-[220px] transition"
                      style="color: var(--theme-heading);">
                    {{ $business->name }}
                </span>
                <span class="text-[10px] sm:text-[11px] font-semibold tracking-wider uppercase flex items-center gap-1.5 truncate"
                      style="color: #8B5A2B;">
                    <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background-color: #8B5A2B;"></span>
                    <span>Cafe, Kopi & Tempat Santai</span>
                </span>
            </div>
        </a>

        {{-- 2. Desktop Navigation Menu --}}
        <nav class="hidden md:flex items-center gap-1 p-1.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
            {{-- Home --}}
            <a href="{{ url('/' . $business->slug) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug) ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-xs font-bold' : 'text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white' }}">
                {{ $landingPage->getNavLabel('home', '{{ __('storefront.nav_home') }}') }}
            </a>

            {{-- Catalog / Produk --}}
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-1.5 {{ request()->is($business->slug . '/katalog*') || request()->is($business->slug . '/produk*') ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-xs font-bold' : 'text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white' }}">
                    <i data-lucide="store" class="w-3.5 h-3.5" style="color: #8B5A2B;"></i>
                    <span>{{ $landingPage->getNavLabel('catalog', '{{ __('storefront.daftar_minuman_menu') }}') }}</span>
                </a>
            @endif

            {{-- About / Profil --}}
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/tentang-kami*') ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-xs font-bold' : 'text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white' }}">
                    {{ $landingPage->getNavLabel('about', 'Kisah Roastery') }}
                </a>
            @endif

            {{-- Contact / Hubungi --}}
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/kontak*') ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-xs font-bold' : 'text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white' }}">
                    {{ $landingPage->getNavLabel('contact', 'Reservasi Tempat') }}
                </a>
            @endif

            {{-- Blog / Artikel --}}
            @if ($landingPage->isPageActive('blog'))
                <a href="{{ url('/' . $business->slug . '/artikel') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/artikel*') ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-xs font-bold' : 'text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white' }}">
                    {{ $landingPage->getNavLabel('blog', 'Artikel & Info') }}
                </a>
            @endif
        </nav>

        {{-- 3. Action Tools: Tracking, Dark Mode, Cart, Petani WA --}}
        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">

            {{-- {{ __('storefront.order_tracking') }} Trigger --}}
            <button type="button" @click="$store.orderTracking.open()"
                class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-xs font-semibold text-neutral-800 dark:text-neutral-200 hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition min-h-[40px] cursor-pointer"
                title="{{ __('storefront.track_order_status') }}">
                <i data-lucide="package-search" class="w-3.5 h-3.5" style="color: #8B5A2B;"></i>
                <span class="hidden md:inline">{{ __('storefront.order_tracking') }}</span>
            </button>

            {{-- Dark Mode Toggle --}}
            <button type="button" @click="toggleDarkMode()"
                class="p-2 sm:p-2.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-neutral-800 dark:text-neutral-200 hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition flex items-center justify-center min-h-[40px] min-w-[40px] cursor-pointer"
                aria-label="{{ __('storefront.toggle_display_mode') }}"
                title="Mode Terang / Gelap">
                <i data-lucide="sun" class="w-4 h-4 hidden dark:inline text-amber-400"></i>
                <i data-lucide="moon" class="w-4 h-4 inline dark:hidden text-neutral-700"></i>
            </button>

            {{-- Cart Trigger --}}
            <a href="{{ url('/' . $business->slug . '/checkout') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-white font-semibold text-xs transition-all shadow-xs min-h-[40px] cursor-pointer active:scale-95"
                style="background-color: #8B5A2B;">
                <div class="relative">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <template x-if="$store.cart.count() > 0">
                        <span class="absolute -top-2 -right-2 w-4 h-4 rounded-full bg-white text-neutral-900 font-bold text-[9px] flex items-center justify-center shadow-xs"
                              x-text="$store.cart.count()"></span>
                    </template>
                </div>
                <span class="hidden sm:inline font-mono tabular-nums" x-text="'Rp ' + $store.cart.subtotal().toLocaleString('id-ID')">Rp 0</span>
            </a>

            {{-- Mobile Drawer Hamburger Button --}}
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                class="md:hidden p-2 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-neutral-800 dark:text-neutral-200"
                aria-label="Menu Navigasi">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    {{-- Mobile Slide-out Drawer --}}
    <div x-show="mobileMenuOpen" x-cloak
        class="md:hidden border-t border-black/5 dark:border-white/10 bg-white/95 dark:bg-[#121620]/95 backdrop-blur-xl px-4 py-4 space-y-2">
        <a href="{{ url('/' . $business->slug) }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-neutral-800 dark:text-neutral-200">
            {{ __('storefront.nav_home') }}
        </a>
        @if ($landingPage->isPageActive('catalog'))
            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                {{ __('storefront.daftar_minuman_menu') }}
            </a>
        @endif
        @if ($landingPage->isPageActive('about'))
            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                Kisah Roastery
            </a>
        @endif
        @if ($landingPage->isPageActive('contact'))
            <a href="{{ url('/' . $business->slug . '/kontak') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                Reservasi Tempat
            </a>
        @endif
        <div class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center justify-between">
            <button type="button" @click="$store.orderTracking.open(); mobileMenuOpen = false" class="text-xs font-semibold text-neutral-700 dark:text-neutral-300 flex items-center gap-2">
                <i data-lucide="package-search" class="w-4 h-4"></i>
                <span>{{ __('storefront.order_tracking') }}</span>
            </button>
        </div>
    </div>
</header>