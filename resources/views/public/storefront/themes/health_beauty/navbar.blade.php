{{--
    Health & Beauty Navbar (`health_beauty`)
    Dermatological wellness & clinical aesthetic navigation with skin consultation, cart store, and dark mode toggle.
--}}
<header class="sticky top-0 z-50 bg-white/95 dark:bg-[#0F171A]/95 backdrop-blur-md border-b border-teal-100 dark:border-teal-950/70 transition-colors duration-300">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">

            {{-- 1. Brand Logo & Clinic Identity --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 group shrink-0">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 sm:w-11 sm:h-11 rounded-full object-cover ring-2 ring-teal-500/20 group-hover:ring-teal-500 transition">
                @else
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gradient-to-br from-teal-600 to-rose-500 text-white font-extrabold text-base sm:text-lg flex items-center justify-center shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-heading font-bold text-base sm:text-lg text-slate-900 dark:text-white leading-tight group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">
                        {{ $business->name }}
                    </span>
                    <span class="text-[10px] font-semibold text-teal-700 dark:text-teal-400 tracking-wider uppercase">
                        Klinik Estetika &amp; Skincare
                    </span>
                </div>
            </a>

            {{-- 2. Desktop Navigation Links --}}
            <nav class="hidden md:flex items-center gap-1 lg:gap-2">
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3.5 py-2 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-teal-700 dark:hover:text-teal-300 hover:bg-teal-50 dark:hover:bg-teal-950/60 transition">
                    Beranda
                </a>

                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-teal-700 dark:hover:text-teal-300 hover:bg-teal-50 dark:hover:bg-teal-950/60 transition flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-teal-600"></i>
                        <span>Produk &amp; Treatment</span>
                    </a>
                @endif

                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-teal-700 dark:hover:text-teal-300 hover:bg-teal-50 dark:hover:bg-teal-950/60 transition">
                        Profil Dokter &amp; Klinik
                    </a>
                @endif

                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3.5 py-2 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-teal-700 dark:hover:text-teal-300 hover:bg-teal-50 dark:hover:bg-teal-950/60 transition">
                        Lokasi &amp; Reservasi
                    </a>
                @endif

                {{-- Status Janji / Pesanan Modal Trigger --}}
                <button type="button" @click="$dispatch('open-order-track-modal')"
                    class="px-3.5 py-2 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-teal-700 dark:hover:text-teal-300 hover:bg-teal-50 dark:hover:bg-teal-950/60 transition flex items-center gap-1.5"
                    title="Cek Status Pesanan / Treatment">
                    <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-teal-600"></i>
                    <span class="hidden lg:inline">Cek Status Treatment</span>
                </button>
            </nav>

            {{-- 3. Header Action Icons & Cart --}}
            <div class="flex items-center gap-2 sm:gap-2.5">

                {{-- Dark Mode Toggle --}}
                <button type="button" @click="toggleDarkMode()"
                    class="p-2 sm:p-2.5 rounded-full border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-teal-50 dark:hover:bg-teal-950/50 transition"
                    aria-label="Toggle Dark Mode">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block text-amber-400"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden text-teal-700"></i>
                </button>

                {{-- Cart Badge --}}
                <a href="{{ url('/' . $business->slug . '/keranjang') }}"
                    class="relative p-2 sm:p-2.5 rounded-full border border-teal-200 dark:border-teal-900 bg-teal-50/70 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 hover:bg-teal-100 transition"
                    title="Tas Belanja Skincare / Booking Treatment">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span x-show="$store.cart.count() > 0" x-text="$store.cart.count()"
                        class="absolute -top-1.5 -right-1.5 bg-rose-600 text-white font-extrabold text-[10px] w-5 h-5 rounded-full flex items-center justify-center shadow-xs">
                        {{ $cartCount ?? 0 }}
                    </span>
                </a>

                {{-- WhatsApp Skin Consultation --}}
                @if (!empty($hasWhatsapp))
                    <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                        class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-xs hover:shadow transition">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                        <span>Konsultasi Dokter Kulit</span>
                    </a>
                @endif

                {{-- Mobile Menu Hamburger --}}
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded-full border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200"
                    aria-label="Buka Menu">
                    <i data-lucide="menu" class="w-5 h-5" x-show="!mobileMenuOpen"></i>
                    <i data-lucide="x" class="w-5 h-5" x-show="mobileMenuOpen" style="display:none"></i>
                </button>
            </div>

        </div>
    </div>

    {{-- Mobile Navigation Dropdown --}}
    <div x-show="mobileMenuOpen" x-transition.origin.top class="md:hidden border-t border-teal-100 dark:border-teal-950 bg-white dark:bg-[#0F171A] px-4 py-4 space-y-2 shadow-lg" style="display:none">
        <a href="{{ url('/' . $business->slug) }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 dark:text-slate-100 hover:bg-teal-50 dark:hover:bg-teal-950/60">
            Beranda
        </a>
        @if ($landingPage->isPageActive('catalog'))
            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 dark:text-slate-100 hover:bg-teal-50 dark:hover:bg-teal-950/60">
                Produk &amp; Treatment
            </a>
        @endif
        @if ($landingPage->isPageActive('about'))
            <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 dark:text-slate-100 hover:bg-teal-50 dark:hover:bg-teal-950/60">
                Profil Dokter &amp; Klinik
            </a>
        @endif
        @if ($landingPage->isPageActive('contact'))
            <a href="{{ url('/' . $business->slug . '/kontak') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 dark:text-slate-100 hover:bg-teal-50 dark:hover:bg-teal-950/60">
                Lokasi &amp; Reservasi
            </a>
        @endif
        <button type="button" @click="$dispatch('open-order-track-modal'); mobileMenuOpen = false"
            class="w-full text-left px-3 py-2 rounded-lg text-sm font-semibold text-teal-700 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-950/60 flex items-center gap-2">
            <i data-lucide="clipboard-check" class="w-4 h-4"></i>
            <span>Cek Status Pesanan / Treatment</span>
        </button>
        @if (!empty($hasWhatsapp))
            <div class="pt-2">
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-full bg-teal-600 text-white text-xs font-bold">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Konsultasi Dokter via WA</span>
                </a>
            </div>
        @endif
    </div>
</header>
