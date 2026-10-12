{{--
    Coffee & Beverage Category Chips (`coffee_beverage`)
    Artisan cafe category navigation with coffee bean icons and roast tone pills.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-[#3D2B1F] dark:text-[#FAF7F2]">
                    Bar &amp; Brew Menu
                </h2>
                <p class="text-xs text-[#6B5744] dark:text-[#CBBDB1]">Pilih sajian espresso, manual brew, artisan tea &amp; beans</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-[#8B5A2B] dark:text-[#C88A58] hover:underline flex items-center gap-1">
                <span>Semua Menu</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold transition border border-[#E8DEC8] dark:border-[#382B21] bg-white dark:bg-[#201914] text-[#3D2B1F] dark:text-[#FAF7F2] hover:border-[#8B5A2B] hover:text-[#8B5A2B] dark:hover:text-[#C88A58] shadow-2xs">
                    <i data-lucide="coffee" class="w-3.5 h-3.5 text-[#8B5A2B]"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
