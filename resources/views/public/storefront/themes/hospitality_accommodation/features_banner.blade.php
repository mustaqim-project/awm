{{--
    Hospitality & Accommodation Features Banner (`hospitality_accommodation`)
    Resort amenities: infinity pool & scenic nature, fast Wi-Fi, gourmet dining, 24/7 concierge.
--}}
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 sm:p-6 rounded-3xl bg-white dark:bg-[#0C1D24] border border-sky-100 dark:border-sky-950/70 shadow-2xs transition-colors">

        {{-- 1: Panorama & Nature --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-sky-50 dark:bg-sky-950/60 border border-sky-200/60 dark:border-sky-900/60 flex items-center justify-center text-sky-700 dark:text-sky-300 shrink-0">
                <i data-lucide="waves" class="w-5 h-5 text-sky-500"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">Panorama Eksotis</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Suasana asri &amp; menenangkan</p>
            </div>
        </div>

        {{-- 2: High-Speed Internet --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-sky-50 dark:bg-sky-950/60 border border-sky-200/60 dark:border-sky-900/60 flex items-center justify-center text-sky-700 dark:text-sky-300 shrink-0">
                <i data-lucide="wifi" class="w-5 h-5 text-sky-600"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">High-Speed Wi-Fi</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Koneksi stabil di setiap sudut</p>
            </div>
        </div>

        {{-- 3: Breakfast & Dining --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-sky-50 dark:bg-sky-950/60 border border-sky-200/60 dark:border-sky-900/60 flex items-center justify-center text-sky-700 dark:text-sky-300 shrink-0">
                <i data-lucide="coffee" class="w-5 h-5 text-amber-500"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">Breakfast Lezat</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Pilihan hidangan nusantara &amp; barat</p>
            </div>
        </div>

        {{-- 4: 24/7 Front Desk --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-sky-50 dark:bg-sky-950/60 border border-sky-200/60 dark:border-sky-900/60 flex items-center justify-center text-sky-700 dark:text-sky-300 shrink-0">
                <i data-lucide="bell" class="w-5 h-5 text-sky-600"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">{{ __('storefront.layanan_24_jam') }}</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Resepsionis &amp; staf sigap melayani</p>
            </div>
        </div>

    </div>
</section>
