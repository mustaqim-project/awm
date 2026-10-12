{{-- ========================================================================= --}}
{{-- ORDER / BOOKING TRACK MODAL: Booking & Appointment                        --}}
{{-- ========================================================================= --}}
@props([
    'business',
])

<div x-data="{
    open: false,
    orderCode: '',
    loading: false,
    errorMessage: '',
    init() {
        window.addEventListener('open-order-track-modal', () => {
            this.open = true;
            this.errorMessage = '';
            this.$nextTick(() => {
                if (this.$refs.orderInput) this.$refs.orderInput.focus();
            });
        });
        if (this.$store && this.$store.orderTracking) {
            this.$watch('$store.orderTracking.isOpen', (val) => {
                this.open = val;
            });
        }
    },
    close() {
        this.open = false;
        if (this.$store && this.$store.orderTracking) {
            this.$store.orderTracking.isOpen = false;
        }
    },
    submitTrack() {
        const query = this.orderCode.trim();
        if (!query) {
            this.errorMessage = 'Silakan masukkan nomor invoice atau nomor HP Anda.';
            return;
        }
        this.loading = true;
        this.errorMessage = '';
        window.location.href = '{{ url('/' . $business->slug . '/pesanan') }}/' + encodeURIComponent(query);
    }
}"
x-show="open"
x-cloak
@keydown.escape.window="close()"
class="fixed inset-0 z-[200] overflow-y-auto"
aria-labelledby="modal-booking-track-title" 
role="dialog" 
aria-modal="true">

    {{-- Zero-Gap Independent Backdrop (§responsive-ui-ux) --}}
    <div x-show="open"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/65 backdrop-blur-md"
         @click="close()"></div>

    {{-- Modal Card Wrapper (Mobile: Adaptive Bottom Sheet, Desktop: Centered Bento Card) --}}
    <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-center">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="relative w-full sm:max-w-md transform overflow-hidden rounded-t-[28px] sm:rounded-[28px] bg-white dark:bg-[#161224] text-left shadow-2xl border border-purple-200/80 dark:border-purple-900/60 p-6 sm:p-7 space-y-5 pb-24 sm:pb-7 transition-all"
             @click.stop>
            
            {{-- Mobile Pull Handle Bar --}}
            <div class="sm:hidden w-12 h-1.5 rounded-full bg-slate-300 dark:bg-purple-900/80 mx-auto -mt-1 mb-2"></div>

            {{-- Header --}}
            <div class="flex items-center justify-between pb-3.5 border-b border-purple-100 dark:border-purple-900/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 flex items-center justify-center font-bold shadow-2xs">
                        <i data-lucide="calendar-search" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 id="modal-booking-track-title" class="font-heading font-extrabold text-base text-slate-900 dark:text-purple-100 leading-tight">
                            Cek Status Reservasi
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-purple-300/70">
                            Masukkan No. Tiket, Invoice, atau No. WhatsApp
                        </p>
                    </div>
                </div>
                <button type="button" 
                        @click="close()"
                        class="w-8 h-8 rounded-full bg-slate-100 dark:bg-purple-950/60 text-slate-400 hover:text-slate-700 dark:hover:text-purple-200 flex items-center justify-center transition cursor-pointer"
                        aria-label="Tutup modal">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Form Body --}}
            <form @submit.prevent="submitTrack()" class="space-y-4">
                <div>
                    <label for="booking-track-input" class="block text-xs font-bold text-slate-700 dark:text-purple-200 mb-1.5">
                        Nomor Tiket / Invoice / WhatsApp
                    </label>
                    <div class="relative">
                        <input id="booking-track-input"
                               type="text" 
                               x-ref="orderInput"
                               x-model="orderCode" 
                               required
                               placeholder="Contoh: INV/2026/... atau 08123456789"
                               class="w-full h-12 pl-11 pr-4 rounded-2xl border border-purple-200 dark:border-purple-900/60 bg-purple-50/50 dark:bg-[#0E0B17] text-slate-900 dark:text-purple-100 text-sm focus:outline-hidden focus:ring-2 focus:ring-purple-600 transition">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-purple-500">
                            <i data-lucide="ticket" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-purple-300/60 mt-1.5">
                        Format nomor dapat ditemukan pada pesan konfirmasi WhatsApp atau email.
                    </p>
                </div>

                <template x-if="errorMessage">
                    <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        <span x-text="errorMessage"></span>
                    </div>
                </template>

                <button type="submit" 
                        :disabled="loading"
                        class="w-full min-h-[48px] py-3 px-4 rounded-2xl text-white font-bold text-sm bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 shadow-md shadow-purple-600/25 flex items-center justify-center gap-2 transition-all active:scale-95 disabled:opacity-50 cursor-pointer">
                    <span x-show="!loading" class="flex items-center gap-2">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Lacak Jadwal Reservasi</span>
                    </span>
                    <span x-show="loading" class="inline-flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memeriksa Tiket...</span>
                    </span>
                </button>
            </form>

        </div>
    </div>
</div>