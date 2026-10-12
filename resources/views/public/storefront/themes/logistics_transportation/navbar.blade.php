{{--
    Logistics & Transportation Navbar (`logistics_transportation`)
    Fleet logistics & cargo dispatch navigation with waybill tracking modal, cargo cart, and dark mode toggle.
--}}
<header class="sticky top-0 z-50 bg-white/95 dark:bg-[#0F141C]/95 backdrop-blur-md border-b border-amber-200/80 dark:border-amber-950/70 transition-colors duration-300">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- 1. Brand Logo & Freight Identity --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 group shrink-0">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl object-cover ring-2 ring-amber-500/20 group-hover:ring-amber-500 transition">
                @else
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br from-amber-500 to-amber-700 text-slate-950 font-black text-base sm:text-lg flex items-center justify-center shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-black text-base sm:text-lg text-slate-900 dark:text-white leading-tight tracking-wider uppercase group-hover:text-amber-600 dark:group-hover:text-amber-400 transition">
                        {{ $business->name }}
                    </span>
                    <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400 tracking-widest uppercase">
                        Kargo &bull; Ekspedisi &bull; Logistik
                    </span>
                </div>
            </a>

            {{-- 2. Desktop Navigation Links --}}
            <nav class="hidden md:flex items-center gap-1 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-amber-700 dark:hover:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-950/60 transition uppercase tracking-wide">
                    Beranda
                </a>

                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-amber-700 dark:hover:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-950/60 transition uppercase tracking-wide flex items-center gap-1.5">
                        <i data-lucide="truck" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Armada &amp; Layanan</span>
                    </a>
                @endif

                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-amber-700 dark:hover:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-950/60 transition uppercase tracking-wide">
                        Jangkauan Rute
                    </a>
                @endif

                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-amber-700 dark:hover:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-950/60 transition uppercase tracking-wide">
                        Pool &amp; Kontak
                    </a>
                @endif

                {{-- Status Resi / Muatan Modal Trigger --}}
                <button type="button" @click="$dispatch('open-order-track-modal')"
                    class="px-3.5 py-2 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-amber-700 dark:hover:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-950/60 transition uppercase tracking-wide flex items-center gap-1.5"
                    title="Lacak Nomor Resi / Surat Jalan">
                    <i data-lucide="scan-line" class="w-3.5 h-3.5 text-amber-600"></i>
                    <span class="hidden lg:inline">Lacak Resi / Muatan</span>
                </button>
            </nav>

            {{-- 3. Header Action Icons & Cart --}}
            <div class="flex items-center gap-2 sm:gap-2.5">

                {{-- Dark Mode Toggle --}}
                <button type="button" @click="toggleDarkMode()"
                    class="p-2 sm:p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-amber-50 dark:hover:bg-amber-950/50 transition"
                    aria-label="Toggle Dark Mode">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block text-amber-400"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden text-amber-600"></i>
                </button>

                {{-- Cart Badge --}}
                <a href="{{ url('/' . $business->slug . '/keranjang') }}"
                    class="relative p-2 sm:p-2.5 rounded-xl border border-amber-300 dark:border-amber-900 bg-amber-500/10 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 hover:bg-amber-500/20 transition"
                    title="Daftar Sewa Armada Dipilih">
                    <i data-lucide="package-search" class="w-4 h-4"></i>
                    <span x-show="$store.cart.count() > 0" x-text="$store.cart.count()"
                        class="absolute -top-1.5 -right-1.5 bg-red-600 text-white font-black text-[10px] w-5 h-5 rounded-full flex items-center justify-center shadow-xs">
                        {{ $cartCount ?? 0 }}
                    </span>
                </a>

                {{-- WhatsApp Dispatcher --}}
                @if (!empty($hasWhatsapp))
                    <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                        class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-black uppercase tracking-wider shadow-xs hover:shadow transition">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                        <span>Dispatch Hotline</span>
                    </a>
                @endif

                {{-- Mobile Menu Hamburger --}}
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200"
                    aria-label="Buka Menu">
                    <i data-lucide="menu" class="w-5 h-5" x-show="!mobileMenuOpen"></i>
                    <i data-lucide="x" class="w-5 h-5" x-show="mobileMenuOpen" style="display:none"></i>
                </button>
            </div>

        </div>
    </div>

    {{-- Mobile Navigation Dropdown --}}
    <div x-show="mobileMenuOpen" x-transition.origin.top class="md:hidden border-t border-amber-200 dark:border-amber-950 bg-white dark:bg-[#0F141C] px-4 py-4 space-y-2 shadow-lg" style="display:none">
        <a href="{{ url('/' . $business->slug) }}" class="block px-3 py-2 rounded-lg text-sm font-bold uppercase text-slate-800 dark:text-slate-100 hover:bg-amber-50 dark:hover:bg-amber-950/60">
            Beranda
        </a>
        @if ($landingPage->isPageActive('catalog'))
            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="block px-3 py-2 rounded-lg text-sm font-bold uppercase text-slate-800 dark:text-slate-100 hover:bg-amber-50 dark:hover:bg-amber-950/60">
                Armada &amp; Layanan
            </a>
        @endif
        @if ($landingPage->isPageActive('about'))
            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="block px-3 py-2 rounded-lg text-sm font-bold uppercase text-slate-800 dark:text-slate-100 hover:bg-amber-50 dark:hover:bg-amber-950/60">
                Jangkauan Rute
            </a>
        @endif
        @if ($landingPage->isPageActive('contact'))
            <a href="{{ url('/' . $business->slug . '/kontak') }}" class="block px-3 py-2 rounded-lg text-sm font-bold uppercase text-slate-800 dark:text-slate-100 hover:bg-amber-50 dark:hover:bg-amber-950/60">
                Pool &amp; Kontak
            </a>
        @endif
        <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
            class="w-full text-left px-3 py-2 rounded-lg text-sm font-bold uppercase text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/60 flex items-center gap-2">
            <i data-lucide="scan-line" class="w-4 h-4"></i>
            <span>Lacak Resi / Muatan</span>
        </button>
        @if (!empty($hasWhatsapp))
            <div class="pt-2">
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 text-slate-950 text-xs font-black uppercase tracking-wider">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Hubungi Dispatcher via WA</span>
                </a>
            </div>
        @endif
    </div>
</header>
