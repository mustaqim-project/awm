{{--
    Features / Trust Banner (`product_showcase`)
--}}
@php
    $pillars = $landingPage->getUniversalValues();
@endphp

<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 rounded-3xl bg-white dark:bg-[#140F12] border border-rose-200/60 dark:border-rose-950/60 shadow-sm transition-colors">
        @foreach ($pillars as $pillar)
            <div class="flex items-center gap-3.5 p-2">
                <div class="w-11 h-11 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/60 dark:border-rose-900/60 flex items-center justify-center text-rose-700 dark:text-rose-300 shrink-0">
                    <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-rose-100">
                        {{ $pillar['title'] }}
                    </h4>
                    <p class="text-[11px] text-slate-500 dark:text-rose-300/60 mt-0.5 leading-relaxed">
                        {{ $pillar['desc'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</section>
