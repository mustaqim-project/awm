{{--
    Portfolio & Creative Navbar (`portfolio_creative`)
    Editorial modern studio header with portfolio work navigation, brief consultation, and cart.
--}}
<header class="sticky top-0 z-40 bg-[#F8FAFC]/95 dark:bg-[#09090B]/95 backdrop-blur-md border-b border-zinc-200/80 dark:border-zinc-800 transition-colors"
    x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- Brand / Studio Monogram --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 rounded-xl object-cover border border-zinc-300 dark:border-zinc-700 shadow-2xs">
                @else
                    <div class="w-10 h-10 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 flex items-center justify-center font-heading font-black text-sm shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-extrabold text-base sm:text-lg text-zinc-950 dark:text-white leading-tight group-hover:text-violet-600 transition-colors">
                        {{ $business->name }}
                    </span>
                    <span class="text-[9px] uppercase tracking-widest text-violet-600 dark:text-violet-400 font-bold">Studio &bull; Portfolio</span>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1.5 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded-lg text-xs font-bold text-zinc-700 dark:text-zinc-200 hover:text-violet-600 dark:hover:text-violet-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                    Studio
                </a>
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded-lg text-xs font-bold text-zinc-700 dark:text-zinc-200 hover:text-violet-600 dark:hover:text-violet-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition flex items-center gap-1.5">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-violet-500"></i>
                        <span>{{ __('storefront.koleksi_paket') }}</span>
                    </a>
                @endif
                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded-lg text-xs font-bold text-zinc-700 dark:text-zinc-200 hover:text-violet-600 dark:hover:text-violet-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                        {{ __('storefront.profil_tim') }}
                    </a>
                @endif
                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded-lg text-xs font-bold text-zinc-700 dark:text-zinc-200 hover:text-violet-600 dark:hover:text-violet-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                        Hubungi Studio
                    </a>
                @endif
            </nav>

            {{-- Right Actions --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" @click="toggleDarkMode()"
                    class="p-2 sm:p-2.5 rounded-lg border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition"
                    title="Ganti Mode">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden"></i>
                </button>

                <button type="button" @click="$dispatch('open-order-track-modal')"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-800 text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                    <i data-lucide="folder-search" class="w-3.5 h-3.5 text-violet-500"></i>
                    <span>{{ __('storefront.cek_status_order') }}</span>
                </button>

                {{-- Cart Trigger --}}
                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 hover:bg-violet-700 dark:hover:bg-violet-600 dark:hover:text-white transition font-bold text-xs shadow-xs active:scale-95"
                    title="{{ __('storefront.order_service') }}">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Paket</span>
                    <span x-text="$store.cart ? $store.cart.count() : 0"
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-violet-600 text-white min-w-[18px] text-center">
                        0
                    </span>
                </a>

                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded-lg border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-200">
                    <i :data-lucide="mobileMenuOpen ? 'x' : 'menu'" class="w-5 h-5"></i>
                </button>
            </div>

        </div>

        {{-- Mobile Drawer --}}
        <div x-show="mobileMenuOpen" x-transition.origin.top.duration.200ms
            class="md:hidden py-4 border-t border-zinc-200 dark:border-zinc-800 space-y-2">
            <a href="{{ url('/' . $business->slug) }}"
                class="block px-3 py-2 rounded-lg text-sm font-bold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-900">
                {{ __('storefront.home_studio') }}
            </a>
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="block px-3 py-2 rounded-lg text-sm font-bold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-900">
                    {{ __('storefront.karya_paket_layanan') }}
                </a>
            @endif
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="block px-3 py-2 rounded-lg text-sm font-bold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-900">
                    {{ __('storefront.tentang_studio_tim') }}
                </a>
            @endif
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="block px-3 py-2 rounded-lg text-sm font-bold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-900">
                    {{ __('storefront.kontak_alamat_studio') }}
                </a>
            @endif
            <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
                class="w-full text-left px-3 py-2 rounded-lg text-sm font-bold text-violet-600 dark:text-violet-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 flex items-center gap-2">
                <i data-lucide="folder-search" class="w-4 h-4"></i>
                <span>Lacak Progres Proyek &amp; Invoice</span>
            </button>
        </div>
    </div>
</header>
