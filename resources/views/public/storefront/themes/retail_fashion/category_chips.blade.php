{{--
    Fashion & Butik Category Chips (`retail_fashion` / `vogue_minimalist`)
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="font-heading font-light text-2xl text-neutral-900 dark:text-white tracking-wide">{{ __('storefront.kategori_koleksi') }}</h2>
                <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                    Jelajahi lini busana pria, wanita, kasual, dan formal
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs sm:text-sm font-medium flex items-center gap-1 hover:underline text-neutral-800 dark:text-neutral-200">
                <span>{{ __('storefront.semua_koleksi') }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap scrollbar-hide">
            @php
                $catIcons = [
                    'atasan' => 'shirt',
                    'top' => 'shirt',
                    'kemeja' => 'shirt',
                    'bawahan' => 'scissors',
                    'celana' => 'scissors',
                    'pants' => 'scissors',
                    'dress' => 'sparkles',
                    'outer' => 'shield',
                    'jaket' => 'shield',
                    'hijab' => 'heart',
                    'tas' => 'shopping-bag',
                    'aksesoris' => 'watch',
                    'sepatu' => 'footprints',
                ];
            @endphp

            @foreach ($productCategories->take(8) as $cat)
                @php
                    $catNameLower = strtolower($cat->name);
                    $catIcon = 'shirt';
                    foreach ($catIcons as $key => $icon) {
                        if (str_contains($catNameLower, $key)) {
                            $catIcon = $icon;
                            break;
                        }
                    }
                @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full text-xs sm:text-sm font-medium transition-all duration-200 hover:shadow-sm bg-white dark:bg-[#181E2A] border border-black/10 dark:border-white/10 hover:border-black dark:hover:border-white text-neutral-800 dark:text-neutral-200 min-h-[42px]">
                    <i data-lucide="{{ $catIcon }}" class="w-3.5 h-3.5 text-neutral-500"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
