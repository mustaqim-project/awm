{{--
    Business Corporate Features Banner (`business_corporate`)
    B2B Trust Pillars: dynamic universal value propositions from CMS / i18n fallbacks.
--}}
@php
    $pillars = $landingPage->getUniversalValues();
@endphp

<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 p-6 sm:p-8 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
        @foreach ($pillars as $pillar)
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg theme-badge flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $pillar['icon'] }}" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white leading-snug">
                        {{ $pillar['title'] }}
                    </h4>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2 leading-relaxed">
                        {{ $pillar['desc'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</section>
