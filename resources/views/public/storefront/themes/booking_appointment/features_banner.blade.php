{{--
    Features / Trust Banner (`booking_appointment`)
--}}
@php
    $pillars = $landingPage->getUniversalValues();
@endphp

<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-5 sm:p-6 rounded-[28px] bg-white/80 dark:bg-[#161224]/80 backdrop-blur-md border border-purple-200/70 dark:border-purple-900/50 shadow-sm transition-colors">
        @foreach ($pillars as $pillar)
            <div class="p-3 rounded-2xl hover:bg-purple-500/5 transition flex items-start gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950/80 border border-purple-200 dark:border-purple-800/80 flex items-center justify-center text-purple-700 dark:text-purple-300 shrink-0 shadow-2xs">
                    <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-heading font-extrabold text-xs sm:text-sm text-slate-900 dark:text-purple-100 mb-0.5">
                        {{ $pillar['title'] }}
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-purple-300/70 leading-relaxed">
                        {{ $pillar['desc'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</section>
