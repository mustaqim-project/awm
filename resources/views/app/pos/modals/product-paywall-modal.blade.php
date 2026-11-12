{{-- ======================================================== --}}
{{-- MODAL VISUAL PAYWALL: ACTIVE INVENTORY LIMIT (FREE / EXPIRED) --}}
{{-- ======================================================== --}}
<div x-show="showProductPaywallModal"
     x-cloak
     class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
     @keydown.escape.window="showProductPaywallModal = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-3xl p-6 sm:p-7 shadow-2xl border border-amber-500/30 text-center relative overflow-hidden"
         @click.outside="showProductPaywallModal = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2">

        {{-- Background Ambient Glow --}}
        <div class="absolute -top-12 -right-12 w-36 h-36 bg-amber-500/15 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-36 h-36 bg-[#007AFF]/15 rounded-full blur-2xl pointer-events-none"></div>

        {{-- Close Button --}}
        <button type="button" 
                @click="showProductPaywallModal = false"
                class="absolute top-4 right-4 w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Lock Icon Badge --}}
        <div class="w-16 h-16 mx-auto mb-4 rounded-3xl bg-amber-500/15 border border-amber-500/30 text-amber-500 flex items-center justify-center shadow-inner">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>

        {{-- Headline --}}
        <h3 class="text-lg sm:text-xl font-bold font-heading text-neutral-900 dark:text-white leading-tight">
            Produk Ini Terkunci
        </h3>
        <p class="text-xs text-amber-700 dark:text-amber-400 font-semibold mt-1">
            Batas Kuota Paket Free: Maksimal 10 Produk Aktif
        </p>

        {{-- Product Details Card --}}
        <template x-if="lockedProductCandidate">
            <div class="mt-4 p-3 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center gap-3 text-left">
                <div class="w-12 h-12 rounded-xl bg-black/10 dark:bg-black/40 flex items-center justify-center shrink-0 text-amber-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold text-neutral-900 dark:text-white truncate" x-text="lockedProductCandidate.name"></div>
                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400 font-mono" x-text="formatRupiah(getProductPrice(lockedProductCandidate))"></div>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-400 shrink-0">
                    LOCKED
                </span>
            </div>
        </template>

        {{-- Explanation Note --}}
        <p class="mt-4 text-xs text-neutral-600 dark:text-neutral-300 leading-relaxed">
            Masa aktif paket unlimited toko Anda telah berakhir. Pada paket gratis, kasir hanya dapat mentransaksikan <strong>10 produk teratas</strong>.
            <br><br>
            Segera perpanjang paket langganan Anda untuk membuka kunci seluruh katalog produk dan kembali bertransaksi tanpa batasan.
        </p>

        {{-- CTA Actions --}}
        <div class="mt-6 flex flex-col sm:flex-row items-center gap-2.5">
            <button type="button"
                    @click="showProductPaywallModal = false"
                    class="w-full sm:w-1/2 py-2.5 px-4 rounded-xl text-xs font-semibold text-neutral-600 dark:text-neutral-400 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                Tutup Sementara
            </button>
            <a :href="billingUpgradeUrl"
               class="w-full sm:w-1/2 py-2.5 px-4 rounded-xl text-xs font-bold bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white shadow-[0_4px_16px_rgba(0,122,255,0.3)] transition flex items-center justify-center gap-1.5 cursor-pointer">
                <span>Perpanjang Paket</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>
    </div>
</div>

