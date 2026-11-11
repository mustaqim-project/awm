{{-- ========================================================================= --}}
{{-- APPLE MOBILE BOTTOM TAB BAR: Bakery & Cake (Bakery, Cake Shop, Pastry, Dessert & Custom Cake) --}}
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

<nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-[#FAF8F5]/96 dark:bg-[#14100E]/96 backdrop-blur-2xl border-t border-stone-200/80 dark:border-stone-800 pb-[env(safe-area-inset-bottom,0px)] shadow-[0_-4px_24px_rgba(0,0,0,0.04)]">
    <div class="grid grid-cols-4 h-15 items-center text-[10px]">
        {{-- Home Tab --}}
        <a href="{{ url('/' . $bSlug) }}"
            class="flex flex-col items-center justify-center gap-1 transition-all duration-150 active:scale-95 min-h-[48px] {{ $isHome ? 'text-amber-800 dark:text-amber-400 font-bold' : 'text-stone-500 dark:text-stone-400 hover:text-stone-800' }}">
            <i data-lucide="home" class="w-5 h-5 {{ $isHome ? 'stroke-[2.2]' : 'stroke-[1.6]' }}"></i>
            <span>{{ __('storefront.nav_home') }}</span>
        </a>

        {{-- Catalog / Roti & Kue Tab --}}
        <a href="{{ url('/' . $bSlug . '/katalog') }}"
            class="flex flex-col items-center justify-center gap-1 transition-all duration-150 active:scale-95 min-h-[48px] {{ $isCatalog ? 'text-amber-800 dark:text-amber-400 font-bold' : 'text-stone-500 dark:text-stone-400 hover:text-stone-800' }}">
            <i data-lucide="cake-slice" class="w-5 h-5 {{ $isCatalog ? 'stroke-[2.2]' : 'stroke-[1.6]' }}"></i>
            <span>{{ $landingPage->getNavLabel('catalog', 'Roti & Kue') }}</span>
        </a>

        {{-- Cart Tab --}}
        <a href="{{ url('/' . $bSlug . '/checkout') }}"
            class="flex flex-col items-center justify-center gap-1 relative transition-all duration-150 active:scale-95 min-h-[48px] {{ $isCart ? 'text-amber-800 dark:text-amber-400 font-bold' : 'text-stone-500 dark:text-stone-400 hover:text-stone-800' }}">
            <div class="relative">
                <i data-lucide="shopping-bag" class="w-5 h-5 {{ $isCart ? 'stroke-[2.2]' : 'stroke-[1.6]' }}"></i>
                <template x-if="$store.cart.count() > 0">
                    <span class="absolute -top-1.5 -right-2.5 px-1 min-w-[16px] h-4 rounded-full bg-amber-700 text-white text-[9px] font-bold font-mono tabular-nums flex items-center justify-center shadow-2xs"
                          x-text="$store.cart.count()"></span>
                </template>
            </div>
            <span>{{ __('storefront.nav_orders') }}</span>
        </a>

        {{-- WA / Tanya Baker Tab --}}
        @php
            $waNum = preg_replace('/[^0-9]/', '', (string) ($landingPage->whatsapp_number ?: $business->phone));
            if (str_starts_with($waNum, '0')) {
                $waNum = '62' . substr($waNum, 1);
            }
            $waLink = $waNum ? 'https://wa.me/' . $waNum : url('/' . $bSlug . '/tentang-kami');
        @endphp
        <a href="{{ $waLink }}" target="{{ $waNum ? '_blank' : '_self' }}"
            class="flex flex-col items-center justify-center gap-1 transition-all duration-150 active:scale-95 min-h-[48px] text-stone-500 dark:text-stone-400 hover:text-stone-800">
            <i data-lucide="message-circle" class="w-5 h-5 stroke-[1.6]"></i>
            <span>Tanya Baker</span>
        </a>
    </div>
</nav>