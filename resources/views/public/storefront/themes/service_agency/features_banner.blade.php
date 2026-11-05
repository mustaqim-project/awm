{{--
    Features / Trust Banner (`service_agency`)
--}}
@php
    $pillars = $landingPage->getUniversalValues();
@endphp

<section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 p-6 sm:p-8 rounded-3xl bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm">
        @foreach ($pillars as $pillar)
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl theme-badge flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-heading font-bold text-xs sm:text-sm text-neutral-900 dark:text-white">
                        {{ $pillar['title'] }}
                    </h4>
                    <p class="text-[11px] sm:text-xs text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                        {{ $pillar['desc'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</section>
