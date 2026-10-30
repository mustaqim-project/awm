{{-- ========================================================================= --}}
{{-- ORDER TRACK MODAL: Coffee & Beverage (Coffee Shop, Cafe, Bubble Tea & Kedai Minuman)       --}}
{{-- ========================================================================= --}}
<div x-show="$store.orderTracking.isOpen" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    <div class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm transition-opacity"
        @click="$store.orderTracking.close()"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-[#181E2A] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-black/10 dark:border-white/10 p-6 space-y-4"
            @click.stop>
            
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white"
                         style="background-color: #8B5A2B;">
                        <i data-lucide="package-search" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-neutral-900 dark:text-white leading-tight">{{ __('storefront.lacak_pesanan_kopi') }}</h3>
                        <p class="text-[11px] text-neutral-500">Masukkan No. Invoice atau No. HP Anda</p>
                    </div>
                </div>
                <button type="button" @click="$store.orderTracking.close()"
                    class="w-7 h-7 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form @submit.prevent="$store.orderTracking.submit('{{ $business->slug }}')" class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
                        Nomor Pesanan / Invoice
                    </label>
                    <input type="text" x-model="$store.orderTracking.identifier" required
                        placeholder="{{ __('storefront.track_input_placeholder') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-neutral-900 dark:text-white text-sm focus:outline-hidden focus:ring-2 focus:ring-[#8B5A2B] transition">
                </div>

                <button type="submit" :disabled="$store.orderTracking.loading"
                    class="w-full py-2.5 px-4 rounded-xl text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-xs transition active:scale-95 disabled:opacity-50"
                    style="background-color: #8B5A2B;">
                    <span x-show="!$store.orderTracking.loading">{{ __('storefront.check_status_now') }}</span>
                    <span x-show="$store.orderTracking.loading" class="inline-flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memeriksa...
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>