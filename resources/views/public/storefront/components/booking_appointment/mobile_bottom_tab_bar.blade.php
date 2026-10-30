{{-- ========================================================================= --}}
{{-- APPLE MOBILE BOTTOM TAB BAR: Booking & Appointment                        --}}
{{-- ========================================================================= --}}
@props([
    'business',
    'landingPage',
    'activeTheme',
    'hasWhatsapp' => false,
])

@php
    $currentPath = request()->path();
    $bSlug = $business->slug;
    $isHome = $currentPath === $bSlug || $currentPath === '/';
    $isCatalog = str_contains($currentPath, "{$bSlug}/katalog") || str_contains($currentPath, "{$bSlug}/produk");
    $isCart = str_contains($currentPath, "{$bSlug}/checkout");
    $isAbout = str_contains($currentPath, "{$bSlug}/tentang");

    $waNum = preg_replace('/[^0-9]/', '', (string) ($landingPage->whatsapp_number ?: $business->phone));
    if (str_starts_with($waNum, '0')) {
        $waNum = '62' . substr($waNum, 1);
    }
    $waGreeting = 'Halo ' . $business->name . ', saya ingin konsultasi layanan dan reservasi slot jadwal kunjungan.';
    $waLink = $waNum ? 'https://wa.me/' . $waNum . '?text=' . urlencode($waGreeting) : '#';
@endphp

<nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-[#0E0B17]/95 backdrop-blur-xl border-t border-purple-200/60 dark:border-purple-900/40 pb-[env(safe-area-inset-bottom,0px)] shadow-[0_-8px_20px_rgba(0,0,0,0.06)] dark:shadow-[0_-8px_20px_rgba(0,0,0,0.4)]">
    <div class="grid grid-cols-4 h-15 items-center text-[10px]">
        
        {{-- 1. Beranda Tab --}}
        <a href="{{ url('/' . $bSlug) }}"
            class="flex flex-col items-center justify-center gap-1 transition-colors min-h-[44px]"
            style="color: {{ $isHome ? '#7C3AED' : 'rgba(156, 163, 175, 1)' }};">
            <i data-lucide="home" class="w-5 h-5 {{ $isHome ? 'stroke-[2.2]' : 'stroke-[1.6]' }}"></i>
            <span class="font-medium {{ $isHome ? 'font-extrabold text-purple-700 dark:text-purple-400' : '' }}">{{ __('storefront.nav_home') }}</span>
        </a>

        {{-- 2. Sesi Layanan Tab --}}
        <a href="{{ url('/' . $bSlug . '/katalog') }}"
            class="flex flex-col items-center justify-center gap-1 transition-colors min-h-[44px]"
            style="color: {{ $isCatalog ? '#7C3AED' : 'rgba(156, 163, 175, 1)' }};">
            <i data-lucide="calendar" class="w-5 h-5 {{ $isCatalog ? 'stroke-[2.2]' : 'stroke-[1.6]' }}"></i>
            <span class="font-medium {{ $isCatalog ? 'font-extrabold text-purple-700 dark:text-purple-400' : '' }}">{{ __('storefront.nav_services') }}</span>
        </a>

        {{-- 3. Tiket Reservasi Tab (Cart) --}}
        <a href="{{ url('/' . $bSlug . '/checkout') }}"
            class="flex flex-col items-center justify-center gap-1 relative transition-colors min-h-[44px]"
            style="color: {{ $isCart ? '#7C3AED' : 'rgba(156, 163, 175, 1)' }};">
            <div class="relative">
                <i data-lucide="calendar-check" class="w-5 h-5 {{ $isCart ? 'stroke-[2.2]' : 'stroke-[1.6]' }}"></i>
                <template x-if="$store.cart && $store.cart.count() > 0">
                    <span class="absolute -top-1.5 -right-2.5 min-w-[18px] h-4 px-1 rounded-full text-white text-[9px] font-extrabold flex items-center justify-center shadow-xs tabular-nums bg-purple-600"
                          x-text="$store.cart.count()"></span>
                </template>
            </div>
            <span class="font-medium {{ $isCart ? 'font-extrabold text-purple-700 dark:text-purple-400' : '' }}">Tiket Sesi</span>
        </a>

        {{-- 4. Cek Jadwal / Concierge Tab --}}
        <button type="button" 
            @click="$dispatch('open-order-track-modal')"
            class="flex flex-col items-center justify-center gap-1 transition-colors min-h-[44px] text-slate-400 hover:text-purple-600 dark:hover:text-purple-400 cursor-pointer">
            <i data-lucide="clock" class="w-5 h-5 stroke-[1.6]"></i>
            <span class="font-medium">Cek Jadwal</span>
        </button>

    </div>
</nav>