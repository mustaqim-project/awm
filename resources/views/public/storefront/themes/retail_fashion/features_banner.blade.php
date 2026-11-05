{{--
    Fashion & Butik Features / Trust Banner (`retail_fashion` / `vogue_minimalist`)
    Dynamic universal value propositions from CMS / i18n fallbacks.
--}}
@php
    $pillars = $landingPage->getUniversalValues();
@endphp

<section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($pillars as $pillar)
            <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-xs flex items-start gap-3">
                <div class="w-10 h-10 rounded-full theme-badge flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $pillar['icon'] }}" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="text-[13px] sm:text-[14px] font-semibold text-neutral-900 dark:text-white leading-snug">
                        {{ $pillar['title'] }}
                    </h4>
                    <p class="text-[11px] sm:text-[12px] text-neutral-500 dark:text-neutral-400 mt-0.5 leading-relaxed">
                        {{ $pillar['desc'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</section>
