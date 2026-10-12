{{--
    Food & Restaurant Category Chips (`food_restaurant`)
    Culinary menu section navigation with warm ceramic-style pills.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-normal text-xl sm:text-2xl text-slate-900 dark:text-amber-50">
                    Daftar Hidangan
                </h2>
                <p class="text-xs text-slate-500 dark:text-amber-200/70">Pilih jenis sajian untuk melihat menu lengkap</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-red-700 dark:text-amber-400 hover:underline flex items-center gap-1">
                <span>Seluruh Menu</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-semibold transition border border-amber-200 dark:border-amber-900/60 bg-white dark:bg-[#1D140F] text-slate-800 dark:text-amber-100 hover:border-red-500 hover:text-red-700 dark:hover:text-amber-300 shadow-2xs">
                    <i data-lucide="utensils" class="w-3.5 h-3.5 text-amber-600"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
