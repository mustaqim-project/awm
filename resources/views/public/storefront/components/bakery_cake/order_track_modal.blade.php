{{-- ========================================================================= --}}
{{-- ORDER TRACK MODAL: Bakery & Cake (Bakery, Cake Shop, Pastry, Dessert & Custom Cake)       --}}
{{-- Responsive-ui-ux: Full-Size Canvas, Zero-Gap Backdrop ($y=0), Dual Mode   --}}
{{-- ========================================================================= --}}
@props([
    'business',
])

<div x-data="{
        isOpen: false,
        orderToken: '',
        loading: false,
        submitTrack() {
            const token = this.orderToken.trim();
            if (!token) return;
            this.loading = true;
            window.location.href = '{{ url('/' . $business->slug) }}/order/' + encodeURIComponent(token);
        }
    }"
    @open-order-track-modal.window="isOpen = true"
    @keydown.escape.window="isOpen = false"
    x-show="isOpen" x-cloak
    class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4"
    aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    {{-- 1. Backdrop Edge-to-Edge ($y=0 Coverage) --}}
    <div x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-md transition-opacity"
        @click="isOpen = false"></div>

    {{-- 2. Modal Dialog: Adaptive Bottom Sheet on Mobile & Bento Card on Tablet/Desktop --}}
    <div x-show="isOpen"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
        class="relative w-full sm:max-w-lg rounded-t-[28px] sm:rounded-3xl bg-white dark:bg-[#1D1714] text-left shadow-2xl transition-all border-t sm:border border-stone-200 dark:border-stone-800 px-6 pt-3 pb-8 sm:p-7 z-10 space-y-5"
        @click.stop>
        
        {{-- Mobile Pull Handle Pill --}}
        <div class="sm:hidden w-12 h-1.5 bg-stone-300 dark:bg-stone-700 rounded-full mx-auto mb-2"></div>

        {{-- Header Bar --}}
        <div class="flex items-center justify-between pb-3.5 border-b border-stone-200/80 dark:border-stone-800">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-700/10 dark:bg-amber-400/10 border border-amber-700/20 dark:border-amber-400/20 text-amber-800 dark:text-amber-400 flex items-center justify-center font-bold shrink-0 shadow-2xs">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-heading font-medium text-lg sm:text-xl text-stone-900 dark:text-stone-100 leading-tight">{{ __('storefront.lacak_pesanan_roti_kue') }}</h3>
                    <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">
                        Cek status memanggang, dekorasi kue, atau jadwal kurir
                    </p>
                </div>
            </div>
            <button type="button" @click="isOpen = false"
                class="p-2 rounded-xl text-stone-400 hover:text-stone-700 dark:hover:text-stone-200 bg-stone-100 dark:bg-stone-800 transition min-w-[36px] min-h-[36px] flex items-center justify-center cursor-pointer"
                aria-label="{{ __('storefront.close') }}">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Tracking Form --}}
        <form @submit.prevent="submitTrack" class="space-y-4">
            <div>
                <label for="track-token-input-bakery" class="block text-xs font-semibold text-stone-700 dark:text-stone-300 mb-1.5">
                    Nomor Nota / Invoice Pemesanan Kue
                </label>
                <div class="relative">
                    <i data-lucide="receipt" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-amber-700 dark:text-amber-400"></i>
                    <input type="text" id="track-token-input-bakery" x-model="orderToken" required
                        placeholder="{{ __('storefront.track_input_placeholder_wa') }}"
                        class="w-full pl-10 pr-4 py-3 sm:py-2.5 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-[#150F0C] text-stone-900 dark:text-stone-100 text-base sm:text-sm placeholder:text-stone-400 dark:placeholder:text-stone-500 focus:outline-none focus:ring-2 focus:ring-amber-700 transition">
                </div>
                <p class="text-[11px] text-stone-500 dark:text-stone-400 mt-1.5">
                    Masukkan nomor nota atau invoice yang diterima saat memesan roti atau kue tart.
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" @click="isOpen = false"
                    class="px-4 py-3 sm:py-2.5 rounded-xl text-xs font-semibold text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 transition min-h-[44px] cursor-pointer">{{ __('storefront.cancel') }}</button>
                <button type="submit" :disabled="loading"
                    class="px-5 py-3 sm:py-2.5 rounded-xl text-xs font-semibold text-white bg-amber-700 hover:bg-amber-800 shadow-xs flex items-center gap-2 active:scale-95 transition disabled:opacity-50 min-h-[44px] cursor-pointer">
                    <span x-show="!loading" class="flex items-center gap-2">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Lacak Sekarang</span>
                    </span>
                    <span x-show="loading" class="flex items-center gap-2" x-cloak>
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Mencari...</span>
                    </span>
                </button>
            </div>
        </form>
                        </svg>
                        <span>Memeriksa Resi...</span>
                    </span>
                </button>
            </div>
        </form>

    </div>
</div>