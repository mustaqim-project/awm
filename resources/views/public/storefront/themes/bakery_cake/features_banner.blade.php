{{-- ========================================================================= --}}
{{-- ARTISAN BAKERY & PATISSERIE FEATURES BANNER (`bakery_cake`)               --}}
{{-- 4 Pillars of Excellence: Dynamic values from CMS with warm amber styling  --}}
{{-- ========================================================================= --}}
@php
    $pillars = $landingPage->getUniversalValues();
@endphp

<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-5 sm:p-7 rounded-3xl bg-white/85 dark:bg-[#221810]/85 backdrop-blur-md border border-amber-900/10 dark:border-amber-500/15 shadow-[0_4px_24px_rgba(217,119,6,0.04)] transition-colors">
        @foreach ($pillars as $pillar)
            <div class="group flex items-start gap-4 p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/40 border border-amber-900/5 dark:border-amber-500/10 hover:bg-amber-100/50 dark:hover:bg-amber-950/70 hover:border-amber-600/30 transition-all duration-200">
                <div class="w-12 h-12 rounded-2xl bg-amber-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 group-hover:rotate-1 transition-transform">
                    <i data-lucide="{{ $pillar['icon'] ?? 'sparkles' }}" class="w-6 h-6"></i>
                </div>
                <div class="space-y-0.5 min-w-0">
                    <h4 class="font-heading font-normal text-base text-neutral-900 dark:text-amber-100 leading-snug truncate">
                        {{ $pillar['title'] }}
                    </h4>
                    <p class="text-xs text-neutral-600 dark:text-amber-200/70 leading-relaxed line-clamp-2">
                        {{ $pillar['desc'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</section>
