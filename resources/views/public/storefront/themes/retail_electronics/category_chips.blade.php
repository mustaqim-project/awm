{{--
    Gadget & Elektronik Category Chips (`retail_electronics` / `nexus_cyber`)
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-neutral-900 dark:text-white">
                    Kategori Perangkat &amp; Komponen
                </h2>
                <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                    Smartphone, laptop, audio, gaming gear, dan aksesoris
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs sm:text-sm font-semibold flex items-center gap-1 hover:underline text-cyan-600 dark:text-cyan-400">
                <span>Lihat Semua</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap scrollbar-hide">
            @php
                $catIcons = [
                    'hp' => 'smartphone',
                    'smartphone' => 'smartphone',
                    'handphone' => 'smartphone',
                    'laptop' => 'laptop',
                    'komputer' => 'monitor',
                    'pc' => 'monitor',
                    'tablet' => 'tablet',
                    'audio' => 'headphones',
                    'earphone' => 'headphones',
                    'speaker' => 'volume-2',
                    'gaming' => 'gamepad-2',
                    'aksesoris' => 'cable',
                    'casing' => 'smartphone',
                    'charger' => 'battery-charging',
                    'storage' => 'hard-drive',
                ];
            @endphp

            @foreach ($productCategories->take(8) as $cat)
                @php
                    $catNameLower = strtolower($cat->name);
                    $catIcon = 'cpu';
                    foreach ($catIcons as $key => $icon) {
                        if (str_contains($catNameLower, $key)) {
                            $catIcon = $icon;
                            break;
                        }
                    }
                @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-sm font-semibold transition-all duration-200 hover:shadow-md hover:scale-[1.02] bg-white dark:bg-[#111827] border border-cyan-100 dark:border-cyan-900/40 hover:border-cyan-500 text-neutral-800 dark:text-neutral-200 min-h-[46px]">
                    <div class="w-7 h-7 rounded-lg bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                        <i data-lucide="{{ $catIcon }}" class="w-4 h-4"></i>
                    </div>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
