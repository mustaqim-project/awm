{{--
    Construction & Property Navbar (`construction_property`)
    Architectural contractor header with project showcase links, RAB calculator access, and document tracking.
--}}
<header class="sticky top-0 z-40 bg-[#FAF5EE]/95 dark:bg-[#131210]/95 backdrop-blur-md border-b border-amber-200/60 dark:border-amber-950/60 transition-colors"
    x-data="{ mobileMenuOpen: false }">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- Brand / Contractor Logo --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 rounded object-cover border border-amber-400 dark:border-amber-800 shadow-2xs">
                @else
                    <div class="w-10 h-10 rounded bg-amber-600 text-white flex items-center justify-center font-heading font-black text-sm shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-black text-base sm:text-lg text-slate-900 dark:text-white leading-tight uppercase group-hover:text-amber-600 transition-colors">
                        {{ $business->name }}
                    </span>
                    <span class="text-[9px] uppercase tracking-widest text-amber-700 dark:text-amber-400 font-bold">Kontraktor &amp; Properti</span>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1.5 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-100/50 dark:hover:bg-slate-800 transition">
                    {{ __('storefront.nav_home') }}
                </a>
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-100/50 dark:hover:bg-slate-800 transition flex items-center gap-1.5">
                        <i data-lucide="building" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>{{ __('storefront.portofolio_layanan') }}</span>
                    </a>
                @endif
                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-100/50 dark:hover:bg-slate-800 transition">
                        Standar Teknik
                    </a>
                @endif
                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-100/50 dark:hover:bg-slate-800 transition">
                        {{ __('storefront.kontak_kantor_2') }}
                    </a>
                @endif
            </nav>

            {{-- Right Actions --}}
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" @click="toggleDarkMode()"
                    class="p-2 sm:p-2.5 rounded border border-amber-300 dark:border-amber-900/60 text-slate-700 dark:text-slate-300 hover:bg-amber-100/50 dark:hover:bg-slate-800 transition"
                    title="Ganti Mode">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden"></i>
                </button>

                <button type="button" @click="$dispatch('open-order-track-modal')"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded border border-amber-300 dark:border-amber-900/60 text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 hover:bg-amber-100/50 dark:hover:bg-slate-800 transition">
                    <i data-lucide="file-search" class="w-3.5 h-3.5 text-amber-600"></i>
                    <span>Lacak Dokumen Proyek</span>
                </button>

                {{-- Cart Trigger --}}
                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs uppercase tracking-wider shadow-sm transition active:scale-95"
                    title="Penawaran Proyek">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">RAB Proyek</span>
                    <span x-text="$store.cart ? $store.cart.count() : 0"
                        class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-white text-slate-900 min-w-[18px] text-center">
                        0
                    </span>
                </a>

                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded border border-amber-300 dark:border-amber-900/60 text-slate-700 dark:text-slate-200">
                    <i :data-lucide="mobileMenuOpen ? 'x' : 'menu'" class="w-5 h-5"></i>
                </button>
            </div>

        </div>

        {{-- Mobile Drawer --}}
        <div x-show="mobileMenuOpen" x-transition.origin.top.duration.200ms
            class="md:hidden py-4 border-t border-amber-200/60 dark:border-amber-950/60 space-y-2">
            <a href="{{ url('/' . $business->slug) }}"
                class="block px-3 py-2 rounded text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 hover:bg-amber-100/50 dark:hover:bg-slate-800">
                {{ __('storefront.nav_home') }} Perusahaan
            </a>
            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="block px-3 py-2 rounded text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 hover:bg-amber-100/50 dark:hover:bg-slate-800">
                    {{ __('storefront.portofolio_layanan') }}
                </a>
            @endif
            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                    class="block px-3 py-2 rounded text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 hover:bg-amber-100/50 dark:hover:bg-slate-800">
                    Standar Arsitektur &amp; Sipil
                </a>
            @endif
            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                    class="block px-3 py-2 rounded text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 hover:bg-amber-100/50 dark:hover:bg-slate-800">
                    {{ __('storefront.konsultasi_kontak') }}
                </a>
            @endif
            <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
                class="w-full text-left px-3 py-2 rounded text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 hover:bg-amber-100/50 dark:hover:bg-slate-800 flex items-center gap-2">
                <i data-lucide="file-search" class="w-4 h-4"></i>
                <span>Lacak Dokumen Proyek / SPK</span>
            </button>
        </div>
    </div>
</header>
