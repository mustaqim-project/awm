{{-- ========================================================================= --}}
{{-- APPLE MOBILE BOTTOM TAB BAR: Professional Services (Konsultan Bisnis, Akuntansi, Pajak & Legal) --}}
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
@endphp

<nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/90 dark:bg-[#121620]/90 backdrop-blur-xl border-t border-black/5 dark:border-white/10 pb-[env(safe-area-inset-bottom,0px)]">
    <div class="grid grid-cols-4 h-14 items-center text-[10px]">
        {{-- Home Tab --}}
        <a href="{{ url('/' . $bSlug) }}"
            class="flex flex-col items-center justify-center gap-1 transition-colors"
            style="color: {{ $isHome ? '#1E293B' : 'rgba(156, 163, 175, 1)' }};">
            <i data-lucide="home" class="w-5 h-5 {{ $isHome ? 'stroke-[2.2]' : 'stroke-[1.6]' }}"></i>
            <span class="font-medium {{ $isHome ? 'font-bold' : '' }}">{{ __('storefront.nav_home') }}</span>
        </a>

        {{-- Catalog Tab --}}
        <a href="{{ url('/' . $bSlug . '/katalog') }}"
            class="flex flex-col items-center justify-center gap-1 transition-colors"
            style="color: {{ $isCatalog ? '#1E293B' : 'rgba(156, 163, 175, 1)' }};">
            <i data-lucide="store" class="w-5 h-5 {{ $isCatalog ? 'stroke-[2.2]' : 'stroke-[1.6]' }}"></i>
            <span class="font-medium {{ $isCatalog ? 'font-bold' : '' }}">Katalog</span>
        </a>

        {{-- Cart Tab --}}
        <a href="{{ url('/' . $bSlug . '/checkout') }}"
            class="flex flex-col items-center justify-center gap-1 relative transition-colors"
            style="color: {{ $isCart ? '#1E293B' : 'rgba(156, 163, 175, 1)' }};">
            <div class="relative">
                <i data-lucide="shopping-bag" class="w-5 h-5 {{ $isCart ? 'stroke-[2.2]' : 'stroke-[1.6]' }}"></i>
                <template x-if="$store.cart.count() > 0">
                    <span class="absolute -top-1.5 -right-2 w-4 h-4 rounded-full text-white text-[9px] font-bold flex items-center justify-center shadow-xs"
                          style="background-color: #1E293B;"
                          x-text="$store.cart.count()"></span>
                </template>
            </div>
            <span class="font-medium {{ $isCart ? 'font-bold' : '' }}">{{ __('storefront.nav_orders') }}</span>
        </a>

        {{-- WA / Profile Tab --}}
        @php
            $waNum = preg_replace('/[^0-9]/', '', (string) ($landingPage->whatsapp_number ?: $business->phone));
            if (str_starts_with($waNum, '0')) {
                $waNum = '62' . substr($waNum, 1);
            }
            $waLink = $waNum ? 'https://wa.me/' . $waNum : url('/' . $bSlug . '/tentang');
        @endphp
        <a href="{{ $waLink }}" target="{{ $waNum ? '_blank' : '_self' }}"
            class="flex flex-col items-center justify-center gap-1 transition-colors text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200">
            <i data-lucide="message-circle" class="w-5 h-5 stroke-[1.6]"></i>
            <span class="font-medium">{{ __('storefront.nav_contact') }}</span>
        </a>
    </div>
</nav>