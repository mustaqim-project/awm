{{--
    Coffee Shop & Cafe Features / Trust Banner (`fnb_cafe` / `artisan_brew`)
--}}
@php
    $pillars = $landingPage->getUniversalValues();
@endphp

<section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($pillars as $pillar)
            <div class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#181E2A] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-700 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-[13px] sm:text-[14px] font-bold text-neutral-900 dark:text-white leading-snug">
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
