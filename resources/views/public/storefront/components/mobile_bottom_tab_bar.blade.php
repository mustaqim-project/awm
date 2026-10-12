{{-- ========================================================================= --}}
{{-- APPLE iOS PWA MOBILE BOTTOM TAB BAR (§PRD-07 & §web-ios-pwa-redesign)     --}}
{{-- Mobile-first, touch-friendly navigation bar adhering to Apple HIG rules   --}}
{{-- ========================================================================= --}}
@php
    $bundleDir = $activeTheme['bundle_dir'] ?? 'modern_umkm';
    $isCatalogActive = $landingPage->isPageActive('catalog');

    $catalogLabel = match($bundleDir) {
        'food_restaurant', 'bakery_cake' => 'Menu',
        'coffee_beverage' => 'Kopi & Menu',
        'hospitality_accommodation' => 'Kamar',
        'booking_appointment' => 'Jadwal',
        'logistics_transportation' => 'Armada',
        'education_courses' => 'Kelas',
        'health_beauty' => 'Treatment',
        'event_wedding' => 'Paket',
        'service_business' => 'Layanan',
        'portfolio_creative', 'personal_brand' => 'Karya',
        default => 'Katalog',
    };

    $catalogIcon = match($bundleDir) {
        'food_restaurant', 'bakery_cake' => 'utensils',
        'coffee_beverage' => 'coffee',
        'hospitality_accommodation' => 'bed-double',
        'booking_appointment' => 'calendar',
        'logistics_transportation' => 'truck',
        'education_courses' => 'graduation-cap',
        'health_beauty', 'event_wedding' => 'sparkles',
        'service_business' => 'wrench',
        'portfolio_creative', 'personal_brand' => 'sparkles',
        default => 'layout-grid',
    };

    $isHomeActive = request()->is($business->slug);
    $isCatalogRoute = request()->is($business->slug . '/katalog*') || request()->is($business->slug . '/produk*');
    $isCartRoute = request()->is($business->slug . '/keranjang*') || request()->is($business->slug . '/checkout*');
@endphp

<nav aria-label="Mobile Bottom Navigation"
    class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/92 dark:bg-[#0E131F]/92 backdrop-blur-2xl border-t border-black/[0.08] dark:border-white/[0.1] shadow-[0_-8px_30px_rgba(0,0,0,0.08)] transition-colors duration-300"
    style="padding-bottom: env(safe-area-inset-bottom, 0px);">
    <div class="max-w-md mx-auto grid {{ $isCatalogActive ? 'grid-cols-5' : 'grid-cols-4' }} items-center h-16 px-1">

        {{-- 1. Beranda / Home --}}
        <a href="{{ url('/' . $business->slug) }}"
            class="group flex flex-col items-center justify-center min-h-[48px] py-1 text-center transition-all duration-150 active:scale-95 focus:outline-none"
            aria-label="Beranda">
            <div class="relative p-1 rounded-xl transition-colors {{ $isHomeActive ? 'text-theme-primary dark:text-theme-primary' : 'text-neutral-500 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-white' }}">
                <i data-lucide="home" class="w-5 h-5 transition-transform group-hover:scale-110"></i>
                @if ($isHomeActive)
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-theme-primary"></span>
                @endif
            </div>
            <span class="text-[10px] tracking-tight font-semibold mt-0.5 leading-none {{ $isHomeActive ? 'text-theme-primary font-bold' : 'text-neutral-500 dark:text-neutral-400' }}">
                Beranda
            </span>
        </a>

        {{-- 2. Katalog / Menu / Layanan (If Active) --}}
        @if ($isCatalogActive)
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="group flex flex-col items-center justify-center min-h-[48px] py-1 text-center transition-all duration-150 active:scale-95 focus:outline-none"
                aria-label="{{ $catalogLabel }}">
                <div class="relative p-1 rounded-xl transition-colors {{ $isCatalogRoute ? 'text-theme-primary dark:text-theme-primary' : 'text-neutral-500 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-white' }}">
                    <i data-lucide="{{ $catalogIcon }}" class="w-5 h-5 transition-transform group-hover:scale-110"></i>
                    @if ($isCatalogRoute)
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-theme-primary"></span>
                    @endif
                </div>
                <span class="text-[10px] tracking-tight font-semibold mt-0.5 leading-none truncate max-w-[64px] {{ $isCatalogRoute ? 'text-theme-primary font-bold' : 'text-neutral-500 dark:text-neutral-400' }}">
                    {{ $catalogLabel }}
                </span>
            </a>
        @endif

        {{-- 3. Keranjang / Cart --}}
        <a href="{{ url('/' . $business->slug . '/keranjang') }}"
            class="group flex flex-col items-center justify-center min-h-[48px] py-1 text-center transition-all duration-150 active:scale-95 focus:outline-none"
            aria-label="Keranjang">
            <div class="relative p-1 rounded-xl transition-colors {{ $isCartRoute ? 'text-theme-primary dark:text-theme-primary' : 'text-neutral-500 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-white' }}">
                <i data-lucide="shopping-bag" class="w-5 h-5 transition-transform group-hover:scale-110"></i>
                {{-- Alpine Dynamic Cart Badge --}}
                <span x-show="$store.cart.count() > 0" x-text="$store.cart.count()" x-cloak
                    class="absolute -top-1 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white font-mono text-[9px] font-bold flex items-center justify-center shadow-xs">
                </span>
                @if ($isCartRoute)
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-theme-primary"></span>
                @endif
            </div>
            <span class="text-[10px] tracking-tight font-semibold mt-0.5 leading-none {{ $isCartRoute ? 'text-theme-primary font-bold' : 'text-neutral-500 dark:text-neutral-400' }}">
                Keranjang
            </span>
        </a>

        {{-- 4. Status / Lacak Pesanan --}}
        <button type="button" @click="$dispatch('open-order-track-modal')"
            class="group flex flex-col items-center justify-center min-h-[48px] py-1 text-center transition-all duration-150 active:scale-95 focus:outline-none"
            aria-label="Lacak Pesanan">
            <div class="relative p-1 rounded-xl text-neutral-500 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-white transition-colors">
                <i data-lucide="clipboard-check" class="w-5 h-5 transition-transform group-hover:scale-110"></i>
            </div>
            <span class="text-[10px] tracking-tight font-semibold mt-0.5 leading-none text-neutral-500 dark:text-neutral-400">
                Lacak
            </span>
        </button>

        {{-- 5. Hubungi / WhatsApp / Kontak --}}
        @if (!empty($hasWhatsapp))
            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                class="group flex flex-col items-center justify-center min-h-[48px] py-1 text-center transition-all duration-150 active:scale-95 focus:outline-none"
                aria-label="Hubungi WhatsApp">
                <div class="relative p-1 rounded-xl text-emerald-600 dark:text-emerald-400 transition-colors">
                    <i data-lucide="message-circle" class="w-5 h-5 transition-transform group-hover:scale-110"></i>
                    <span class="absolute top-0.5 right-0.5 w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
                <span class="text-[10px] tracking-tight font-semibold mt-0.5 leading-none text-emerald-600 dark:text-emerald-400 font-bold">
                    Chat WA
                </span>
            </a>
        @elseif ($landingPage->isPageActive('contact'))
            <a href="{{ url('/' . $business->slug . '/kontak') }}"
                class="group flex flex-col items-center justify-center min-h-[48px] py-1 text-center transition-all duration-150 active:scale-95 focus:outline-none"
                aria-label="Kontak">
                <div class="relative p-1 rounded-xl text-neutral-500 dark:text-neutral-400 group-hover:text-neutral-800 dark:group-hover:text-white transition-colors">
                    <i data-lucide="phone-call" class="w-5 h-5 transition-transform group-hover:scale-110"></i>
                </div>
                <span class="text-[10px] tracking-tight font-semibold mt-0.5 leading-none text-neutral-500 dark:text-neutral-400">
                    Kontak
                </span>
            </a>
        @else
            <button type="button" @click="toggleDarkMode()"
                class="group flex flex-col items-center justify-center min-h-[48px] py-1 text-center transition-all duration-150 active:scale-95 focus:outline-none"
                aria-label="Mode Tampilan">
                <div class="relative p-1 rounded-xl text-neutral-500 dark:text-neutral-400 transition-colors">
                    <i data-lucide="moon" class="w-5 h-5 block dark:hidden"></i>
                    <i data-lucide="sun" class="w-5 h-5 hidden dark:block text-amber-400"></i>
                </div>
                <span class="text-[10px] tracking-tight font-semibold mt-0.5 leading-none text-neutral-500 dark:text-neutral-400">
                    Tema
                </span>
            </button>
        @endif

    </div>
</nav>
