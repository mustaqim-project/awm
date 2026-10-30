{{--
    Wholesale & Distributor Navbar (`wholesale_distributor`)
    Commercial B2B distributor header with bulk order link, shipment tracker, and cart drawer.
--}}
<header class="sticky top-0 z-40 bg-[#F8FAFC]/95 dark:bg-[#0B141B]/95 backdrop-blur-md border-b border-emerald-200/60 dark:border-emerald-950/60 transition-colors"
    x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- Brand / Logo --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 rounded-xl object-cover border border-emerald-300 dark:border-emerald-800 shadow-2xs">
                @else
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-heading font-black text-sm shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-extrabold text-base sm:text-lg text-slate-900 dark:text-white leading-tight group-hover:text-emerald-600 transition-colors">
                        {{ $business->name }}
                    </span>
                    <span class="text-[9px] uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-bold">Pusat Distribusi &amp; Grosir</span>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1.5 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-slate-800 transition">
                    {{ __('storefront.gudang_pusat') }}
                </a>
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-slate-800 transition flex items-center gap-1.5">
                        <i data-lucide="boxes" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Katalog Grosir</span>
                    </a>
                @endif
                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-slate-800 transition">
                        Armada &amp; Jangkauan
                    </a>
                @endif
                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-slate-800 transition">
                        {{ __('storefront.kontak_agen_toko') }}
                    </a>
                @endif
            </nav>

            {{-- Right Actions --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" @click="toggleDarkMode()"
                    class="p-2 sm:p-2.5 rounded-xl border border-emerald-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-slate-800 transition"
                    title="Ganti Mode">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden"></i>
                </button>

                <button type="button" @click="$dispatch('open-order-track-modal')"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-emerald-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-slate-800 transition">
                    <i data-lucide="truck" class="w-3.5 h-3.5 text-blue-600"></i>
                    <span>{{ __('storefront.lacak_pengiriman') }}</span>
                </button>

                {{-- Cart Trigger --}}
                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition active:scale-95"
                    title="Order Grosir">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Order Grosir</span>
                    <span x-text="$store.cart ? $store.cart.count() : 0"
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-white text-emerald-800 min-w-[18px] text-center">
                        0
                    </span>
                </a>

                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded-xl border border-emerald-200 dark:border-slate-800 text-slate-700 dark:text-slate-200">
                    <i :data-lucide="mobileMenuOpen ? 'x' : 'menu'" class="w-5 h-5"></i>
                </button>
            </div>

        </div>

        {{-- Mobile Drawer --}}
        <div x-show="mobileMenuOpen" x-transition.origin.top.duration.200ms
            class="md:hidden py-4 border-t border-emerald-200/60 dark:border-emerald-950/60 space-y-2">
            <a href="{{ url('/' . $business->slug) }}"
                class="block px-3 py-2 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-800">
                {{ __('storefront.gudang_pusat') }}
            </a>
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="block px-3 py-2 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-800">
                    {{ __('storefront.katalog_harga_grosir') }}
                </a>
            @endif
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="block px-3 py-2 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-800">
                    Armada &amp; Area Pasokan
                </a>
            @endif
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="block px-3 py-2 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-800">
                    {{ __('storefront.pusat_kontak_grosir') }}
                </a>
            @endif
            <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
                class="w-full text-left px-3 py-2 rounded-xl text-sm font-bold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-slate-800 flex items-center gap-2">
                <i data-lucide="truck" class="w-4 h-4 text-blue-600"></i>
                <span>{{ __('storefront.lacak_status_surat_jalan_do') }}</span>
            </button>
        </div>
    </div>
</header>
