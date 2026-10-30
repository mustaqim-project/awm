{{--
    Fitness & Gym Category Chips (`service_fitness` / `titan_kinetic`)
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="font-heading font-black text-2xl text-neutral-900 dark:text-white uppercase tracking-tight">{{ __('storefront.program_paket_latihan') }}</h2>
                <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                    Membership gym, personal trainer, kelas yoga, suplemen &amp; whey protein
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs sm:text-sm font-bold flex items-center gap-1 hover:underline text-lime-600 dark:text-lime-400">
                <span>{{ __('storefront.semua_paket') }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap scrollbar-hide">
            @php
                $catIcons = [
                    'membership' => 'credit-card',
                    'gym' => 'dumbbell',
                    'trainer' => 'user-check',
                    'kelas' => 'users',
                    'yoga' => 'smile',
                    'pilates' => 'smile',
                    'zumba' => 'music',
                    'suplemen' => 'shield-plus',
                    'protein' => 'cup-soda',
                    'whey' => 'cup-soda',
                    'merchandise' => 'shirt',
                ];
            @endphp

            @foreach ($productCategories->take(8) as $cat)
                @php
                    $catNameLower = strtolower($cat->name);
                    $catIcon = 'dumbbell';
                    foreach ($catIcons as $key => $icon) {
                        if (str_contains($catNameLower, $key)) {
                            $catIcon = $icon;
                            break;
                        }
                    }
                @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-sm font-black uppercase transition-all duration-200 hover:shadow-md hover:scale-105 bg-white dark:bg-[#262626] border border-lime-200 dark:border-white/10 hover:border-lime-500 text-neutral-800 dark:text-neutral-200 min-h-[46px]">
                    <div class="w-7 h-7 rounded-lg bg-lime-100 dark:bg-lime-950/60 text-lime-600 dark:text-lime-400 flex items-center justify-center">
                        <i data-lucide="{{ $catIcon }}" class="w-4 h-4"></i>
                    </div>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
