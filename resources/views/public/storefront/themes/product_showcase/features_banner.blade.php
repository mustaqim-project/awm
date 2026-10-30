{{--
    Product Showcase Features Banner (`product_showcase`)
    Boutique luxury pillars: pure formulation, clinical safety, bespoke concierge, VIP delivery.
--}}
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 rounded-3xl bg-white dark:bg-[#140F12] border border-rose-200/60 dark:border-rose-950/60 shadow-sm transition-colors">

        {{-- 1: Clinical Purity --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/60 dark:border-rose-900/60 flex items-center justify-center text-rose-700 dark:text-rose-300 shrink-0">
                <i data-lucide="gem" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-rose-100">Bahan Baku Murni</h4>
                <p class="text-[11px] text-slate-500 dark:text-rose-300/60">Terpilih dengan standar tinggi</p>
            </div>
        </div>

        {{-- 2: Tested & Certified --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/60 dark:border-rose-900/60 flex items-center justify-center text-rose-700 dark:text-rose-300 shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-rose-100">{{ __('storefront.izin_uji_resmi') }}</h4>
                <p class="text-[11px] text-slate-500 dark:text-rose-300/60">Sertifikasi &amp; uji lab berkala</p>
            </div>
        </div>

        {{-- 3: Bespoke Service --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/60 dark:border-rose-900/60 flex items-center justify-center text-rose-700 dark:text-rose-300 shrink-0">
                <i data-lucide="heart-handshake" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-rose-100">Konsultasi Pribadi</h4>
                <p class="text-[11px] text-slate-500 dark:text-rose-300/60">Rekomendasi formula tepat</p>
            </div>
        </div>

        {{-- 4: Premium Unboxing --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/60 dark:border-rose-900/60 flex items-center justify-center text-rose-700 dark:text-rose-300 shrink-0">
                <i data-lucide="gift" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-rose-100">Kemasan Eksklusif</h4>
                <p class="text-[11px] text-slate-500 dark:text-rose-300/60">Pengemasan aman &amp; elegan</p>
            </div>
        </div>

    </div>
</section>
