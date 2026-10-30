{{-- ========================================================================= --}}
{{-- FLOATING CART PILL: Booking & Appointment (Sesi Jadwal Reservasi)         --}}
{{-- ========================================================================= --}}
<div x-show="$store.cart && $store.cart.count() > 0 && !window.location.pathname.endsWith('/checkout')" 
    x-cloak
    x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-8 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200 transform"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-8 scale-95"
    class="fixed bottom-[calc(4.75rem+env(safe-area-inset-bottom,0px))] inset-x-4 sm:inset-x-auto sm:right-6 sm:bottom-6 sm:max-w-md z-40">
    
    <div class="bg-[#0E0B17]/95 dark:bg-[#161224]/95 text-white backdrop-blur-2xl px-4 sm:px-5 py-3 rounded-2xl sm:rounded-3xl shadow-[0_20px_45px_rgba(124,58,237,0.25)] border border-purple-500/30 flex items-center justify-between gap-4">
        
        {{-- Item Count & Dynamic Subtotal --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center font-extrabold text-sm text-white shadow-xs shrink-0 font-mono tabular-nums">
                <span x-text="$store.cart.count()"></span>
            </div>
            <div>
                <div class="text-[11px] text-purple-300/80 font-medium">
                    Sesi Jadwal Dipilih
                </div>
                <div class="text-sm sm:text-base font-extrabold font-mono tracking-tight tabular-nums text-white"
                    x-text="'Rp ' + $store.cart.subtotal().toLocaleString('id-ID')"></div>
            </div>
        </div>

        {{-- Direct Checkout CTA --}}
        <a href="{{ url('/' . $business->slug . '/checkout') }}"
            class="min-h-[44px] px-4.5 py-2.5 rounded-2xl font-bold text-xs sm:text-sm text-white bg-purple-600 hover:bg-purple-700 shadow-sm flex items-center gap-2 hover:scale-[1.02] active:scale-95 transition-all duration-150 shrink-0">
            <span>{{ __('storefront.konfirmasi_jadwal') }}</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
</div>