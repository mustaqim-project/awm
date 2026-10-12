{{--
    Food & Restaurant Features Banner (`food_restaurant`)
    Authentic culinary pillars: heritage recipes, 100% halal ingredients, cooked fresh to order, kitchen hygiene.
--}}
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 sm:p-6 rounded-3xl bg-[#FFF9ED] dark:bg-[#1D140F] border border-amber-200/70 dark:border-amber-950/70 transition-colors">

        {{-- 1: Authentic Recipe --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-red-100 dark:bg-red-950/60 border border-red-200/60 dark:border-red-900/60 flex items-center justify-center text-red-700 dark:text-red-400 shrink-0">
                <i data-lucide="flame" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-amber-100">Rempah Otentik</h4>
                <p class="text-[11px] text-slate-500 dark:text-amber-300/60">Racikan resep turun-temurun</p>
            </div>
        </div>

        {{-- 2: Halal Certified --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950/60 border border-amber-200/60 dark:border-amber-900/60 flex items-center justify-center text-amber-700 dark:text-amber-400 shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-amber-100">100% Halal</h4>
                <p class="text-[11px] text-slate-500 dark:text-amber-300/60">Bahan segar &amp; terjamin berkah</p>
            </div>
        </div>

        {{-- 3: Made Fresh --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-red-100 dark:bg-red-950/60 border border-red-200/60 dark:border-red-900/60 flex items-center justify-center text-red-700 dark:text-red-400 shrink-0">
                <i data-lucide="soup" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-amber-100">Masak Hangat</h4>
                <p class="text-[11px] text-slate-500 dark:text-amber-300/60">Dimasak langsung saat dipesan</p>
            </div>
        </div>

        {{-- 4: Kitchen Hygiene --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950/60 border border-amber-200/60 dark:border-amber-900/60 flex items-center justify-center text-amber-700 dark:text-amber-400 shrink-0">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-amber-100">Standar Higienis</h4>
                <p class="text-[11px] text-slate-500 dark:text-amber-300/60">Dapur bersih &amp; terinspeksi</p>
            </div>
        </div>

    </div>
</section>
