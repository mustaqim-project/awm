{{--
    Service Business Navbar (`service_business`)
    Clean fresh header with prominent nota tracker, services catalog, and pickup request.
--}}
<header class="sticky top-0 z-40 bg-[#F0F9FF]/95 dark:bg-[#0A1622]/95 backdrop-blur-md border-b border-sky-200/60 dark:border-sky-950/60 transition-colors"
    x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- Brand / Logo --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 rounded-2xl object-cover border border-sky-300 dark:border-sky-800 shadow-2xs">
                @else
                    <div class="w-10 h-10 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-heading font-extrabold text-base shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-extrabold text-base sm:text-lg text-slate-900 dark:text-white leading-tight group-hover:text-sky-600 transition-colors">
                        {{ $business->name }}
                    </span>
                    <span class="text-[9px] uppercase tracking-widest text-sky-600 dark:text-sky-400 font-bold">{{ __('storefront.layanan_jasa_perawatan') }}</span>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1.5 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-sky-200/90 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-100/50 dark:hover:bg-sky-950/40 transition">
                    {{ __('storefront.nav_home') }}
                </a>
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-sky-200/90 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-100/50 dark:hover:bg-sky-950/40 transition flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-sky-500"></i>
                        <span>{{ __('storefront.daftar_layanan_tarif') }}</span>
                    </a>
                @endif
                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-sky-200/90 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-100/50 dark:hover:bg-sky-950/40 transition">
                        Standar Kerja
                    </a>
                @endif
                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-sky-200/90 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-100/50 dark:hover:bg-sky-950/40 transition">
                        {{ __('storefront.outlet_kontak') }}
                    </a>
                @endif
            </nav>

            {{-- Right Actions --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" @click="toggleDarkMode()"
                    class="p-2 sm:p-2.5 rounded-xl border border-sky-200 dark:border-sky-900/60 text-slate-700 dark:text-sky-300 hover:bg-sky-100/50 dark:hover:bg-sky-950/40 transition"
                    title="Ganti Mode">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden"></i>
                </button>

                <button type="button" @click="$dispatch('open-order-track-modal')"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-sky-50 dark:bg-sky-950/80 border border-sky-200 dark:border-sky-800 text-xs font-semibold text-sky-700 dark:text-sky-300 hover:bg-sky-100 dark:hover:bg-sky-900 transition">
                    <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-sky-500"></i>
                    <span class="hidden sm:inline">Lacak Nota</span>
                </button>

                {{-- Cart Trigger --}}
                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs shadow-md shadow-sky-600/20 transition active:scale-95"
                    title="{{ __('storefront.order_service') }}">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Order</span>
                    <span x-text="$store.cart ? $store.cart.count() : 0"
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-white text-sky-900 min-w-[18px] text-center">
                        0
                    </span>
                </a>

                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded-xl border border-sky-200 dark:border-sky-900/60 text-slate-700 dark:text-sky-200">
                    <i :data-lucide="mobileMenuOpen ? 'x' : 'menu'" class="w-5 h-5"></i>
                </button>
            </div>

        </div>

        {{-- Mobile Drawer --}}
        <div x-show="mobileMenuOpen" x-transition.origin.top.duration.200ms
            class="md:hidden py-4 border-t border-sky-200/60 dark:border-sky-950/60 space-y-2">
            <a href="{{ url('/' . $business->slug) }}"
                class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-sky-100 hover:bg-sky-100/50 dark:hover:bg-sky-950/40">
                {{ __('storefront.nav_home') }}
            </a>
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-sky-100 hover:bg-sky-100/50 dark:hover:bg-sky-950/40">
                    {{ __('storefront.daftar_layanan_tarif') }}
                </a>
            @endif
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-sky-100 hover:bg-sky-100/50 dark:hover:bg-sky-950/40">
                    Standar Higienis &amp; Tim
                </a>
            @endif
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-800 dark:text-sky-100 hover:bg-sky-100/50 dark:hover:bg-sky-950/40">
                    Outlet &amp; Antar-Jemput
                </a>
            @endif
            <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
                class="w-full text-left px-3 py-2.5 rounded-xl text-sm font-semibold text-sky-600 dark:text-sky-400 hover:bg-sky-100/50 dark:hover:bg-sky-950/40 flex items-center gap-2">
                <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                <span>Lacak Progres Pengerjaan Nota</span>
            </button>
        </div>
    </div>
</header>
