{{--
    Coffee & Beverage Features Banner (`coffee_beverage`)
    Artisan pillars: dynamic universal value propositions from CMS / i18n fallbacks.
--}}
@php
    $pillars = $landingPage->getUniversalValues();
@endphp

<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 sm:p-6 rounded-3xl bg-[#F4EDE2] dark:bg-[#1D1713] border border-[#E8DEC8] dark:border-[#382B21] transition-colors">
        @foreach ($pillars as $pillar)
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-full bg-[#8B5A2B]/15 dark:bg-[#8B5A2B]/30 border border-[#8B5A2B]/30 flex items-center justify-center text-[#8B5A2B] dark:text-[#C88A58] shrink-0">
                    <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-heading font-bold text-xs sm:text-sm text-[#3D2B1F] dark:text-[#FAF7F2] leading-snug">
                        {{ $pillar['title'] }}
                    </h4>
                    <p class="text-[11px] text-[#6B5744] dark:text-[#CBBDB1] mt-0.5 leading-relaxed">
                        {{ $pillar['desc'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</section>
