{{--
    Features / Trust Banner (`manufacturing_factory`)
--}}
@php
    $pillars = $landingPage->getUniversalValues();
@endphp

<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 sm:p-6 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 transition-colors">
        @foreach ($pillars as $pillar)
            <div class="flex items-center gap-3.5 p-2">
                <div class="w-11 h-11 rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 flex items-center justify-center text-slate-800 dark:text-slate-200 shrink-0">
                    <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-heading font-black text-xs sm:text-sm text-slate-950 dark:text-white uppercase">
                        {{ $pillar['title'] }}
                    </h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                        {{ $pillar['desc'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</section>
