{{-- ========================================================================= --}}
{{-- FLOATING CART PILL: Bakery & Cake (Bakery, Cake Shop, Pastry, Dessert & Custom Cake)      --}}
{{-- ========================================================================= --}}
<div x-show="$store.cart.count() > 0 && !window.location.pathname.endsWith('/checkout')" x-cloak
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-8 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200 transform"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-8 scale-95"
    class="fixed bottom-[calc(4.75rem+env(safe-area-inset-bottom,0px))] inset-x-4 sm:inset-x-auto sm:right-6 sm:bottom-6 sm:max-w-md z-40">
    
    <div class="bg-[#17120E]/95 text-white backdrop-blur-2xl px-4 sm:px-5 py-3 rounded-2xl sm:rounded-3xl shadow-[0_16px_40px_rgba(0,0,0,0.4)] border border-stone-800 flex items-center justify-between gap-4">
        
        {{-- Item Count & Dynamic Subtotal --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-700 text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0 font-mono tabular-nums">
                <span x-text="$store.cart.count()"></span>
            </div>
            <div>
                <div class="text-[11px] text-stone-400 font-medium flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>Kotak Pesanan</span>
                </div>
                <div class="text-sm sm:text-base font-bold font-mono tracking-tight tabular-nums text-white"
                    x-text="'Rp ' + $store.cart.subtotal().toLocaleString('id-ID')"></div>
            </div>
        </div>

        {{-- Direct Checkout CTA --}}
        <a href="{{ url('/' . $business->slug . '/checkout') }}"
            class="px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white bg-amber-700 hover:bg-amber-800 shadow-xs flex items-center gap-2 active:scale-95 transition-all duration-150 shrink-0 min-h-[44px]">
            <span>{{ __('storefront.checkout.pay_now') }}</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
</div>