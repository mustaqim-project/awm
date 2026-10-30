{{--
    Professional Services Features Banner (`professional_services`)
    Advisory pillars: strict NDA confidentiality, certified partners, regulatory compliance, transparent retainers.
--}}
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 transition-colors">

        {{-- 1: NDA Confidentiality --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-800 dark:text-slate-200 shrink-0">
                <i data-lucide="lock" class="w-5 h-5 text-amber-600"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">Kerahasiaan NDA</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Data perusahaan terlindungi penuh</p>
            </div>
        </div>

        {{-- 2: Certified Partners --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-800 dark:text-slate-200 shrink-0">
                <i data-lucide="award" class="w-5 h-5 text-amber-600"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">Praktisi Berlisensi</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Konsultan bersertifikasi resmi</p>
            </div>
        </div>

        {{-- 3: Regulatory Compliance --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-800 dark:text-slate-200 shrink-0">
                <i data-lucide="scale" class="w-5 h-5 text-amber-600"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">Audit Regulasi</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Kepatuhan hukum &amp; perpajakan</p>
            </div>
        </div>

        {{-- 4: Transparent SLA --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-800 dark:text-slate-200 shrink-0">
                <i data-lucide="file-check" class="w-5 h-5 text-amber-600"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">{{ __('storefront.kontrak_resmi') }}</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Perjanjian penugasan profesional</p>
            </div>
        </div>

    </div>
</section>
