{{--
    Wholesale & Distributor Features Banner (`wholesale_distributor`)
    Wholesale distributor pillars: tiered volume pricing, dedicated fleet, continuous stock guarantee, official tax invoices.
--}}
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 sm:p-6 rounded-3xl bg-white dark:bg-[#0F1B24] border border-emerald-100 dark:border-emerald-950/70 shadow-2xs transition-colors">

        {{-- 1: Tiered Pricing --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/60 dark:border-emerald-800/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <i data-lucide="tag" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">{{ __('storefront.harga_bertingkat') }}</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Makin banyak makin hemat</p>
            </div>
        </div>

        {{-- 2: Dedicated Fleet --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/60 dark:border-emerald-800/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <i data-lucide="truck" class="w-5 h-5 text-blue-600"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">Armada Mandiri</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Jadwal distribusi teratur</p>
            </div>
        </div>

        {{-- 3: Guaranteed Stock --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/60 dark:border-emerald-800/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <i data-lucide="boxes" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">{{ __('storefront.stok_berkelanjutan') }}</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Gudang kapasitas buffer aman</p>
            </div>
        </div>

        {{-- 4: Tax & B2B Terms --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/60 dark:border-emerald-800/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <i data-lucide="file-check" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">Faktur &amp; Legal</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Syarat kerja sama B2B resmi</p>
            </div>
        </div>

    </div>
</section>
