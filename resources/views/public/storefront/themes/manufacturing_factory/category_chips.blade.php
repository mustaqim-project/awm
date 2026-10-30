{{--
    Manufacturing & Factory Category Chips (`manufacturing_factory`)
    Utilitarian industrial division filters for manufactured goods and OEM services.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-black text-lg sm:text-xl text-slate-950 dark:text-white uppercase">{{ __('storefront.divisi_manufaktur_produk') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih lini produk atau spesifikasi produksi yang dibutuhkan</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-bold uppercase tracking-wider text-orange-600 dark:text-orange-400 hover:underline flex items-center gap-1">
                <span>Seluruh Katalog</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded text-xs font-bold uppercase tracking-wider transition border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 hover:border-orange-500 hover:text-orange-600 dark:hover:text-orange-400 shadow-2xs">
                    <span class="w-2 h-2 bg-orange-500"></span>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
