{{--
    Hospitality & Accommodation Category Chips (`hospitality_accommodation`)
    Resort & villa category navigation: Suite, Private Pool Villa, Family Cottage, Glamping Tent.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-normal text-2xl sm:text-3xl text-slate-900 dark:text-sky-50">
                    Tipe Kamar &amp; Villa
                </h2>
                <p class="text-xs text-slate-500 dark:text-sky-200/70">Pilih akomodasi yang sesuai dengan rencana liburan keluarga atau perjalanan Anda</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-sky-700 dark:text-sky-300 hover:underline flex items-center gap-1">
                <span>Semua Tipe Kamar</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-semibold transition border border-sky-200 dark:border-sky-900/60 bg-white dark:bg-[#0C1D24] text-slate-800 dark:text-sky-100 hover:border-sky-400 hover:text-sky-700 dark:hover:text-sky-300 shadow-2xs">
                    <i data-lucide="bed-double" class="w-3.5 h-3.5 text-sky-500"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
