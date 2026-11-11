{{-- ========================================================================= --}}
{{-- AGRICULTURE & FARM FEATURES BANNER (`agriculture_farm`)                  --}}
{{-- Dynamic universal value propositions from CMS / i18n fallbacks            --}}
{{-- ========================================================================= --}}
@php
    $pillars = $landingPage->getUniversalValues();
@endphp

<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-5 sm:p-7 rounded-[28px] bg-white/85 dark:bg-[#0D1F12]/85 backdrop-blur-xl border border-emerald-900/10 dark:border-emerald-500/20 shadow-sm transition-colors">
        @foreach ($pillars as $pillar)
            <div class="group flex items-start gap-4 p-4.5 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/30 border border-emerald-900/5 dark:border-emerald-500/10 hover:bg-white dark:hover:bg-emerald-950/60 hover:border-emerald-600/30 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                <div class="w-12 h-12 rounded-2xl theme-btn-primary flex items-center justify-center shrink-0 shadow-xs group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="{{ !empty($pillar['icon']) ? $pillar['icon'] : 'sprout' }}" class="w-5 h-5 text-white"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="font-heading font-normal text-base text-neutral-900 dark:text-emerald-50 leading-snug group-hover:text-emerald-700 dark:group-hover:text-emerald-300 transition-colors">
                        {{ $pillar['title'] ?? 'Keunggulan Mutu Panen' }}
                    </h4>
                    <p class="text-xs text-neutral-600 dark:text-emerald-200/70 leading-relaxed">
                        {{ $pillar['desc'] ?? 'Standar kualitas panen terbaik langsung dari kebun binaan terpercaya.' }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</section>
