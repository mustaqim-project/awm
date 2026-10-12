{{--
    Agriculture & Farm Category Chips (`agriculture_farm`)
    Harvest category pills for fresh vegetables, fruits, certified seeds, organic fertilizers, and livestock products.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-normal text-2xl sm:text-3xl text-slate-900 dark:text-emerald-50">
                    Kategori Hasil Panen &amp; Bibit
                </h2>
                <p class="text-xs text-slate-500 dark:text-emerald-200/70">Pilih komoditas pangan segar atau sarana penunjang kebun Anda</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:underline flex items-center gap-1">
                <span>Semua Komoditas</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold transition border border-emerald-200 dark:border-emerald-900/60 bg-white dark:bg-[#112117] text-slate-800 dark:text-emerald-100 hover:border-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300 shadow-2xs">
                    <i data-lucide="sprout" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
