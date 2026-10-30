{{-- ========================================================================= --}}
{{-- AGRICULTURE & FARM STOREFRONT NAVBAR (`agriculture_farm`)                --}}
{{-- Farm-to-Table organic identity, harvest tracking & morning dispatch alert --}}
{{-- ========================================================================= --}}
@php
    $currentLocale = app()->getLocale();
    $currentUrl = url()->current();
    $queryParams = request()->query();
    $idUrl = $currentUrl . '?' . http_build_query(array_merge($queryParams, ['lang' => 'id']));
    $enUrl = $currentUrl . '?' . http_build_query(array_merge($queryParams, ['lang' => 'en']));
@endphp

<header class="sticky top-0 z-40 w-full bg-white/92 dark:bg-[#07130A]/92 backdrop-blur-2xl border-b border-emerald-900/10 dark:border-emerald-500/20 transition-colors duration-300">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-3 sm:gap-6">

        {{-- 1. Farm Brand Identity --}}
        <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group focus:outline-none">
            @if ($business->logo_url || $landingPage->logo_url)
                <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl object-cover ring-2 ring-emerald-600/25 dark:ring-emerald-400/30 group-hover:scale-105 transition-transform duration-300 shadow-xs">
            @else
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-800 to-green-950 text-white flex items-center justify-center font-heading text-lg sm:text-xl shadow-xs group-hover:scale-105 transition-transform duration-300 border border-emerald-500/25">
                    <span class="italic font-bold">{{ strtoupper(substr($business->name, 0, 2)) }}</span>
                </div>
            @endif
            <div class="flex flex-col min-w-0">
                <span class="font-heading font-normal text-base sm:text-lg lg:text-xl text-neutral-900 dark:text-emerald-50 leading-tight truncate max-w-[150px] sm:max-w-[240px] group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                    {{ $business->name }}
                </span>
                <span class="text-[10px] sm:text-[11px] font-semibold text-emerald-800 dark:text-emerald-400/90 tracking-wider uppercase flex items-center gap-1.5 truncate mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Sentra Tani &amp; Kebun Organik</span>
                </span>
            </div>
        </a>

        {{-- 2. Desktop Navigation Menu --}}
        <nav class="hidden lg:flex items-center gap-1 bg-emerald-950/[0.03] dark:bg-emerald-400/[0.04] p-1.5 rounded-2xl border border-emerald-900/[0.06] dark:border-emerald-400/[0.08]">
            {{-- Home --}}
            <a href="{{ url('/' . $business->slug) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug) ? 'bg-white dark:bg-emerald-900/60 text-emerald-900 dark:text-emerald-100 shadow-xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-emerald-800 dark:hover:text-emerald-200' }}">
                {{ $landingPage->getNavLabel('home', 'Beranda') }}
            </a>

            {{-- Catalog / Panen --}}
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-1.5 {{ request()->is($business->slug . '/katalog*') || request()->is($business->slug . '/produk*') ? 'bg-white dark:bg-emerald-900/60 text-emerald-900 dark:text-emerald-100 shadow-xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-emerald-800 dark:hover:text-emerald-200' }}">
                    <i data-lucide="sprout" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Hasil Panen &amp; Bibit</span>
                </a>
            @endif

            {{-- Kebun & Profil Tani --}}
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/tentang-kami*') ? 'bg-white dark:bg-emerald-900/60 text-emerald-900 dark:text-emerald-100 shadow-xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-emerald-800 dark:hover:text-emerald-200' }}">
                    Kebun &amp; Mitra Tani
                </a>
            @endif

            {{-- Kontak / Pasokan B2B --}}
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/kontak*') ? 'bg-white dark:bg-emerald-900/60 text-emerald-900 dark:text-emerald-100 shadow-xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-emerald-800 dark:hover:text-emerald-200' }}">
                    Grosir &amp; Kerjasama
                </a>
            @endif

            {{-- Artikel Tani / Panduan Berkebun --}}
            @if ($landingPage->isPageActive('blog'))
                <a href="{{ url('/' . $business->slug . '/artikel') }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is($business->slug . '/artikel*') ? 'bg-white dark:bg-emerald-900/60 text-emerald-900 dark:text-emerald-100 shadow-xs' : 'text-neutral-700 dark:text-neutral-300 hover:text-emerald-800 dark:hover:text-emerald-200' }}">
                    Info Panen
                </a>
            @endif
        </nav>

        {{-- 3. Action Tools: Tracking, Dark Mode, Cart, Petani WA --}}
        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">

            {{-- Lacak Kiriman Panen (Desktop) --}}
            <button type="button" @click="$dispatch('open-order-track-modal')"
                class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-emerald-900/10 dark:border-emerald-400/20 bg-emerald-900/[0.03] dark:bg-emerald-400/[0.05] text-xs font-semibold text-emerald-900 dark:text-emerald-200 hover:bg-emerald-900/[0.08] dark:hover:bg-emerald-400/[0.1] transition min-h-[40px]"
                title="Lacak Kiriman Hasil Panen">
                <i data-lucide="truck" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                <span class="hidden md:inline">Lacak Kiriman</span>
            </button>

            {{-- Dark Mode Toggle --}}
            <button type="button" @click="toggleDarkMode()"
                class="p-2 sm:p-2.5 rounded-xl border border-emerald-900/10 dark:border-emerald-400/20 bg-emerald-900/[0.03] dark:bg-emerald-400/[0.05] text-emerald-900 dark:text-emerald-200 hover:bg-emerald-900/[0.08] dark:hover:bg-emerald-400/[0.1] transition flex items-center justify-center min-h-[40px] min-w-[40px]"
                aria-label="{{ __('storefront.toggle_display_mode') }}"
                title="Mode Terang / Gelap">
                <i data-lucide="sun" class="w-4 h-4 hidden dark:inline text-amber-400"></i>
                <i data-lucide="moon" class="w-4 h-4 inline dark:hidden text-emerald-800"></i>
            </button>

            {{-- Keranjang Panen Trigger --}}
            <a href="{{ url('/' . $business->slug . '/checkout') }}"
                class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs shadow-md shadow-emerald-800/20 transition active:scale-95 min-h-[40px]"
                title="Keranjang Belanja Hasil Panen">
                <i data-lucide="shopping-basket" class="w-4 h-4"></i>
                <span class="hidden md:inline">Keranjang</span>
                <span x-show="$store.cart.count() > 0" x-cloak x-text="$store.cart.count()"
                    class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-400 text-emerald-950 min-w-[18px] text-center shadow-2xs">
                </span>
            </a>

            {{-- WhatsApp Hotline Petani (Desktop) --}}
            @if (!empty($hasWhatsapp))
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="hidden xl:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-amber-600/30 bg-amber-500/10 hover:bg-amber-500/20 text-amber-900 dark:text-amber-300 font-semibold text-xs transition active:scale-95 min-h-[40px]">
                    <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Tanya Petani</span>
                </a>
            @endif

            {{-- Mobile Drawer Trigger --}}
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                class="lg:hidden p-2 rounded-xl border border-emerald-900/10 dark:border-emerald-400/20 bg-emerald-900/[0.03] dark:bg-emerald-400/[0.05] text-neutral-800 dark:text-neutral-200 transition min-h-[40px] min-w-[40px] flex items-center justify-center"
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
        class="lg:hidden border-t border-emerald-900/10 dark:border-emerald-500/15 bg-white/95 dark:bg-[#07130A]/95 backdrop-blur-2xl px-4 py-4 space-y-1">

        <a href="{{ url('/' . $business->slug) }}" @click="mobileMenuOpen = false"
            class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->is($business->slug) ? 'bg-emerald-700/10 text-emerald-800 dark:text-emerald-300 font-bold' : 'text-neutral-700 dark:text-neutral-300' }}">
            {{ $landingPage->getNavLabel('home', 'Beranda') }}
        </a>

        @if ($landingPage->isPageActive('catalog'))
            <a href="{{ url('/' . $business->slug . '/katalog') }}" @click="mobileMenuOpen = false"
                class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 {{ request()->is($business->slug . '/katalog*') ? 'bg-emerald-700/10 text-emerald-800 dark:text-emerald-300 font-bold' : 'text-neutral-700 dark:text-neutral-300' }}">
                <i data-lucide="sprout" class="w-4 h-4 text-emerald-600"></i>
                <span>Hasil Panen &amp; Bibit</span>
            </a>
        @endif

        @if ($landingPage->isPageActive('about'))
            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" @click="mobileMenuOpen = false"
                class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->is($business->slug . '/tentang-kami*') ? 'bg-emerald-700/10 text-emerald-800 dark:text-emerald-300 font-bold' : 'text-neutral-700 dark:text-neutral-300' }}">
                Kebun &amp; Mitra Tani
            </a>
        @endif

        @if ($landingPage->isPageActive('contact'))
            <a href="{{ url('/' . $business->slug . '/kontak') }}" @click="mobileMenuOpen = false"
                class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->is($business->slug . '/kontak*') ? 'bg-emerald-700/10 text-emerald-800 dark:text-emerald-300 font-bold' : 'text-neutral-700 dark:text-neutral-300' }}">
                Grosir &amp; Kerjasama
            </a>
        @endif

        @if ($landingPage->isPageActive('blog'))
            <a href="{{ url('/' . $business->slug . '/artikel') }}" @click="mobileMenuOpen = false"
                class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->is($business->slug . '/artikel*') ? 'bg-emerald-700/10 text-emerald-800 dark:text-emerald-300 font-bold' : 'text-neutral-700 dark:text-neutral-300' }}">
                Info Panen &amp; Tips Berkebun
            </a>
        @endif

        <div class="pt-3 border-t border-emerald-900/10 dark:border-emerald-500/15 space-y-2">
            <button type="button" @click="mobileMenuOpen = false; $dispatch('open-order-track-modal')"
                class="flex items-center justify-center gap-2 w-full py-3 rounded-xl font-semibold text-xs bg-emerald-900/[0.04] dark:bg-emerald-400/[0.05] text-emerald-900 dark:text-emerald-200 min-h-[44px]">
                <i data-lucide="truck" class="w-4 h-4 text-emerald-600"></i>
                <span>Lacak Kiriman Hasil Panen</span>
            </button>

            @if (!empty($hasWhatsapp))
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="flex items-center justify-center gap-2 w-full py-3 rounded-xl font-semibold text-xs bg-emerald-700 text-white shadow-xs min-h-[44px]">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Tanya Pasokan Panen via WhatsApp</span>
                </a>
            @endif
        </div>
    </div>
</header>
