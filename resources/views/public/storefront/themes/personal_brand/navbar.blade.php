{{--
    Personal Brand Navbar (`personal_brand`)
    Avant-garde creator & keynote speaker navigation with consultation cart, session tracker, and dark mode toggle.
--}}
<header class="sticky top-0 z-50 bg-white/95 dark:bg-[#09090B]/95 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800/80 transition-colors duration-300">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- 1. Brand Logo / Signature Identity --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 group shrink-0">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 sm:w-11 sm:h-11 rounded-full object-cover ring-2 ring-cyan-500/30 group-hover:ring-cyan-500 transition">
                @else
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-zinc-900 text-cyan-400 font-heading font-extrabold text-base sm:text-lg flex items-center justify-center border border-zinc-700 shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-extrabold text-base sm:text-lg text-zinc-900 dark:text-white leading-tight group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition">
                        {{ $business->name }}
                    </span>
                    <span class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400 tracking-widest uppercase">
                        Kreator &bull; Konsultan Mandiri
                    </span>
                </div>
            </a>

            {{-- 2. Desktop Navigation Links --}}
            <nav class="hidden md:flex items-center gap-1 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded-full text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                    {{ __('storefront.nav_home') }}
                </a>

                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-cyan-500"></i>
                        <span>Program &amp; Karya</span>
                    </a>
                @endif

                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                        Biografi &amp; Media Kit
                    </a>
                @endif

                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                        {{ __('storefront.kolaborasi_kontak') }}
                    </a>
                @endif

                {{-- Status Sesi / Invoice Modal Trigger --}}
                <button type="button" @click="$dispatch('open-order-track-modal')"
                    class="px-3.5 py-2 rounded-full text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition flex items-center gap-1.5"
                    title="{{ __('storefront.check_booking_session_invoice') }}">
                    <i data-lucide="receipt" class="w-3.5 h-3.5 text-cyan-500"></i>
                    <span class="hidden lg:inline">{{ __('storefront.status_sesi_invoice_2') }}</span>
                </button>
            </nav>

            {{-- 3. Header Action Icons & Cart --}}
            <div class="flex items-center gap-2 sm:gap-2.5">

                {{-- Dark Mode Toggle --}}
                <button type="button" @click="toggleDarkMode()"
                    class="p-2 sm:p-2.5 rounded-full border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition"
                    aria-label="Toggle Dark Mode">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block text-amber-400"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden text-zinc-800"></i>
                </button>

                {{-- Cart Badge --}}
                <a href="{{ url('/' . $business->slug . '/keranjang') }}"
                    class="relative p-2 sm:p-2.5 rounded-full border border-zinc-300 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 transition"
                    title="{{ __('storefront.digital_programs_products') }}">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span x-show="$store.cart.count() > 0" x-text="$store.cart.count()"
                        class="absolute -top-1.5 -right-1.5 bg-cyan-600 text-white font-extrabold text-[10px] w-5 h-5 rounded-full flex items-center justify-center shadow-xs">
                        {{ $cartCount ?? 0 }}
                    </span>
                </a>

                {{-- WhatsApp Collaboration --}}
                @if (!empty($hasWhatsapp))
                    <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                        class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-zinc-900 hover:bg-black dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-900 text-xs font-bold shadow-xs hover:shadow transition">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5 text-cyan-400 dark:text-cyan-600"></i>
                        <span>Diskusi Project</span>
                    </a>
                @endif

                {{-- Mobile Menu Hamburger --}}
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded-full border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-200"
                    aria-label="{{ __('storefront.open_menu') }}">
                    <i data-lucide="menu" class="w-5 h-5" x-show="!mobileMenuOpen"></i>
                    <i data-lucide="x" class="w-5 h-5" x-show="mobileMenuOpen" style="display:none"></i>
                </button>
            </div>

        </div>
    </div>

    {{-- Mobile Navigation Dropdown --}}
    <div x-show="mobileMenuOpen" x-transition.origin.top class="md:hidden border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-[#09090B] px-4 py-4 space-y-2 shadow-lg" style="display:none">
        <a href="{{ url('/' . $business->slug) }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-zinc-800 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-900">
            {{ __('storefront.nav_home') }}
        </a>
        @if ($landingPage->isPageActive('catalog'))
            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-zinc-800 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-900">
                Program &amp; Karya
            </a>
        @endif
        @if ($landingPage->isPageActive('about'))
            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-zinc-800 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-900">
                Biografi &amp; Media Kit
            </a>
        @endif
        @if ($landingPage->isPageActive('contact'))
            <a href="{{ url('/' . $business->slug . '/kontak') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-zinc-800 dark:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-900">
                {{ __('storefront.kolaborasi_kontak') }}
            </a>
        @endif
        <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
            class="w-full text-left px-3 py-2 rounded-lg text-sm font-semibold text-cyan-600 dark:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 flex items-center gap-2">
            <i data-lucide="receipt" class="w-4 h-4"></i>
            <span>{{ __('storefront.cek_status_sesi_invoice') }}</span>
        </button>
        @if (!empty($hasWhatsapp))
            <div class="pt-2">
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-full bg-zinc-900 text-white text-xs font-bold">
                    <i data-lucide="message-circle" class="w-4 h-4 text-cyan-400"></i>
                    <span>Hubungi via WhatsApp</span>
                </a>
            </div>
        @endif
    </div>
</header>
