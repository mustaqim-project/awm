{{--
    Features / Trust Banner (`service_business`)
--}}
@php
    $pillars = $landingPage->getUniversalValues();
@endphp

<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 sm:p-6 rounded-3xl bg-white dark:bg-[#111827] border border-blue-100 dark:border-blue-950/60 shadow-2xs transition-colors">
        @foreach ($pillars as $pillar)
            <div class="flex items-center gap-3.5 p-2">
                <div class="w-11 h-11 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200/60 dark:border-blue-900/60 flex items-center justify-center text-blue-700 dark:text-blue-300 shrink-0">
                    <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">
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
