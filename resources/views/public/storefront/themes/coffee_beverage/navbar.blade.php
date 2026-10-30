{{--
    Coffee & Beverage Navbar (`coffee_beverage`)
    Artisan coffee roastery header with barista menu links, live cart, and cozy ambiance.
--}}
<header class="sticky top-0 z-40 bg-[#FAF7F2]/95 dark:bg-[#15110E]/95 backdrop-blur-md border-b border-[#E8DEC8] dark:border-[#2C211A] transition-colors"
    x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- Brand / Logo --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 rounded-full object-cover border border-[#C88A58] shadow-2xs">
                @else
                    <div class="w-10 h-10 rounded-full bg-[#8B5A2B] text-white flex items-center justify-center font-heading font-bold text-sm shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-bold text-lg sm:text-xl text-[#3D2B1F] dark:text-[#FAF7F2] leading-tight group-hover:text-[#8B5A2B] transition-colors">
                        {{ $business->name }}
                    </span>
                    <span class="text-[9px] uppercase tracking-widest text-[#8B5A2B] dark:text-[#C88A58] font-bold">Coffee Roastery &amp; Bar</span>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1.5 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded-full text-xs font-semibold text-[#6B5744] dark:text-[#CBBDB1] hover:text-[#8B5A2B] dark:hover:text-[#FAF7F2] hover:bg-[#F2ECE3] dark:hover:bg-[#201914] transition">
                    {{ __('storefront.nav_home') }}
                </a>
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-[#6B5744] dark:text-[#CBBDB1] hover:text-[#8B5A2B] dark:hover:text-[#FAF7F2] hover:bg-[#F2ECE3] dark:hover:bg-[#201914] transition flex items-center gap-1.5">
                        <i data-lucide="coffee" class="w-3.5 h-3.5 text-[#8B5A2B]"></i>
                        <span>Menu Kopi &amp; Minuman</span>
                    </a>
                @endif
                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-[#6B5744] dark:text-[#CBBDB1] hover:text-[#8B5A2B] dark:hover:text-[#FAF7F2] hover:bg-[#F2ECE3] dark:hover:bg-[#201914] transition">
                        Kisah Biji Kopi
                    </a>
                @endif
                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-[#6B5744] dark:text-[#CBBDB1] hover:text-[#8B5A2B] dark:hover:text-[#FAF7F2] hover:bg-[#F2ECE3] dark:hover:bg-[#201914] transition">
                        Kafe &amp; Lokasi
                    </a>
                @endif
            </nav>

            {{-- Right Actions --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" @click="toggleDarkMode()"
                    class="p-2 sm:p-2.5 rounded-full border border-[#E8DEC8] dark:border-[#382B21] text-[#6B5744] dark:text-[#CBBDB1] hover:bg-[#F2ECE3] dark:hover:bg-[#201914] transition"
                    title="Ganti Mode">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden"></i>
                </button>

                <button type="button" @click="$dispatch('open-order-track-modal')"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-full border border-[#E8DEC8] dark:border-[#382B21] text-xs font-semibold text-[#6B5744] dark:text-[#CBBDB1] hover:bg-[#F2ECE3] dark:hover:bg-[#201914] transition">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-[#8B5A2B]"></i>
                    <span>{{ __('storefront.check_order') }}</span>
                </button>

                {{-- Cart Trigger --}}
                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-[#8B5A2B] hover:bg-[#724822] text-white font-semibold text-xs shadow-md shadow-[#8B5A2B]/20 transition active:scale-95"
                    title="{{ __('storefront.coffee_orders') }}">
                    <i data-lucide="cup-soda" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Pesanan</span>
                    <span x-text="$store.cart ? $store.cart.count() : 0"
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#C88A58] text-[#15110E] min-w-[18px] text-center">
                        0
                    </span>
                </a>

                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded-full border border-[#E8DEC8] dark:border-[#382B21] text-[#6B5744] dark:text-[#CBBDB1]">
                    <i :data-lucide="mobileMenuOpen ? 'x' : 'menu'" class="w-5 h-5"></i>
                </button>
            </div>

        </div>

        {{-- Mobile Drawer --}}
        <div x-show="mobileMenuOpen" x-transition.origin.top.duration.200ms
            class="md:hidden py-4 border-t border-[#E8DEC8] dark:border-[#2C211A] space-y-2">
            <a href="{{ url('/' . $business->slug) }}"
                class="block px-3 py-2 rounded-xl text-sm font-semibold text-[#3D2B1F] dark:text-[#FAF7F2] hover:bg-[#F2ECE3] dark:hover:bg-[#201914]">
                {{ __('storefront.nav_home') }}
            </a>
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="block px-3 py-2 rounded-xl text-sm font-semibold text-[#3D2B1F] dark:text-[#FAF7F2] hover:bg-[#F2ECE3] dark:hover:bg-[#201914]">
                    Menu Minuman &amp; Beans
                </a>
            @endif
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="block px-3 py-2 rounded-xl text-sm font-semibold text-[#3D2B1F] dark:text-[#FAF7F2] hover:bg-[#F2ECE3] dark:hover:bg-[#201914]">
                    Cerita Sangrai &amp; Petani
                </a>
            @endif
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="block px-3 py-2 rounded-xl text-sm font-semibold text-[#3D2B1F] dark:text-[#FAF7F2] hover:bg-[#F2ECE3] dark:hover:bg-[#201914]">
                    {{ __('storefront.lokasi_kedai_jam_buka') }}
                </a>
            @endif
            <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
                class="w-full text-left px-3 py-2 rounded-xl text-sm font-semibold text-[#8B5A2B] dark:text-[#C88A58] hover:bg-[#F2ECE3] dark:hover:bg-[#201914] flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4"></i>
                <span>{{ __('storefront.lacak_pesanan_antar_takeaway') }}</span>
            </button>
        </div>
    </div>
</header>
