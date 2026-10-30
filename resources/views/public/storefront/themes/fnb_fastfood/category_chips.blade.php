{{--
    Fast Food Category Chips (`fnb_fastfood` / `neon_crunch`)
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="font-heading font-black text-2xl text-neutral-900 dark:text-white uppercase tracking-tight">{{ __('storefront.kategori_menu_kilat') }}</h2>
                <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                    Pilih burger, ayam krispi, snack, atau paket hemat
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs sm:text-sm font-bold flex items-center gap-1 hover:underline text-red-600 dark:text-red-400">
                <span>{{ __('storefront.view_all') }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap scrollbar-hide">
            @php
                $catIcons = [
                    'burger' => 'sandwich',
                    'ayam' => 'drumstick',
                    'chicken' => 'drumstick',
                    'kentang' => 'layers',
                    'fries' => 'layers',
                    'snack' => 'popcorn',
                    'combo' => 'sparkles',
                    'paket' => 'package',
                    'minuman' => 'cup-soda',
                    'drink' => 'cup-soda',
                    'es krim' => 'ice-cream',
                ];
            @endphp

            @foreach ($productCategories->take(8) as $cat)
                @php
                    $catNameLower = strtolower($cat->name);
                    $catIcon = 'utensils';
                    foreach ($catIcons as $key => $icon) {
                        if (str_contains($catNameLower, $key)) {
                            $catIcon = $icon;
                            break;
                        }
                    }
                @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-sm font-extrabold transition-all duration-200 hover:shadow-lg hover:scale-105 bg-white dark:bg-[#181E2A] border-2 border-red-100 dark:border-white/10 hover:border-red-500 text-neutral-900 dark:text-neutral-100 min-h-[46px]">
                    <div class="w-7 h-7 rounded-lg bg-red-100 dark:bg-red-950/60 text-red-600 flex items-center justify-center">
                        <i data-lucide="{{ $catIcon }}" class="w-4 h-4"></i>
                    </div>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
