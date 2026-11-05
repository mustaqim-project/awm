{{--
    Features / Trust Banner (`personal_brand`)
--}}
@php
    $pillars = $landingPage->getUniversalValues();
@endphp

<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 sm:p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 transition-colors">
        @foreach ($pillars as $pillar)
            <div class="flex items-center gap-3.5 p-2">
                <div class="w-11 h-11 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-900 dark:text-zinc-100 shrink-0">
                    <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-heading font-bold text-xs sm:text-sm text-zinc-950 dark:text-white">
                        {{ $pillar['title'] }}
                    </h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                        {{ $pillar['desc'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</section>
