{{--
    Food & Restaurant Features Banner (`food_restaurant`)
    Authentic culinary pillars: heritage recipes, 100% halal ingredients, cooked fresh to order, kitchen hygiene.
--}}
@php
    $featureItems = $landingPage->getUniversalValues();
@endphp
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 sm:p-6 rounded-3xl bg-[#FFF9ED] dark:bg-[#1D140F] border border-amber-200/70 dark:border-amber-950/70 transition-colors">
        @foreach($featureItems as $item)
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950/60 border border-amber-200/60 dark:border-amber-900/60 flex items-center justify-center text-amber-800 dark:text-amber-300 shrink-0">
                    <i data-lucide="{{ $item['icon'] ?? 'shield-check' }}" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-amber-100">{{ $item['title'] }}</h4>
                    <p class="text-[11px] text-slate-500 dark:text-amber-300/60 leading-relaxed">{{ $item['desc'] ?? ($item['description'] ?? '') }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

