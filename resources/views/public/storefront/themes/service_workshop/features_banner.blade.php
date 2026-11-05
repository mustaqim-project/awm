{{--
    Bengkel & Otomotif Features / Trust Banner (`service_workshop` / `apex_velocity`)
--}}
@php
    $featureItems = $landingPage->getUniversalValues();
@endphp
<section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach($featureItems as $item)
            <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1E293B] border border-orange-500/20 dark:border-white/10 shadow-sm flex items-start gap-3.5 transition-colors">
                <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $item['icon'] ?? 'shield-check' }}" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-heading text-[13px] sm:text-[14px] font-bold text-neutral-900 dark:text-white">{{ $item['title'] }}</h4>
                    <p class="text-[11px] sm:text-[12px] text-neutral-500 dark:text-neutral-400 mt-0.5 leading-relaxed">{{ $item['desc'] ?? ($item['description'] ?? '') }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

