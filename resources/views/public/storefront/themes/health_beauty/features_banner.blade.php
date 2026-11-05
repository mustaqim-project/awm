{{--
    Health & Beauty Features Banner (`health_beauty`)
    Clinical wellness pillars: BPOM official certification, expert medical team, hypoallergenic formula, sterile hygiene.
--}}
@php
    $featureItems = $landingPage->getUniversalValues();
@endphp
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 sm:p-6 rounded-3xl bg-white dark:bg-[#111C20] border border-teal-100 dark:border-teal-950/70 shadow-2xs transition-colors">
        @foreach($featureItems as $item)
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-full bg-teal-50 dark:bg-teal-950/60 border border-teal-200/60 dark:border-teal-900/60 flex items-center justify-center text-teal-700 dark:text-teal-300 shrink-0">
                    <i data-lucide="{{ $item['icon'] ?? 'shield-check' }}" class="w-5 h-5 text-teal-600"></i>
                </div>
                <div>
                    <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">{{ $item['title'] }}</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ $item['desc'] ?? ($item['description'] ?? '') }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

