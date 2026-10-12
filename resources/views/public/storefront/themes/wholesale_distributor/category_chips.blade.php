{{--
    Wholesale & Distributor Category Chips (`wholesale_distributor`)
    B2B wholesale category navigation for bulk stock and distribution lines.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-black text-lg sm:text-xl text-slate-900 dark:text-white">
                    Kategori Pasokan Grosir
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih komoditas atau lini produk untuk melihat daftar harga karton</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                <span>Seluruh Katalog</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition border border-emerald-200 dark:border-emerald-900/60 bg-white dark:bg-[#0F1B24] text-slate-800 dark:text-slate-200 hover:border-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400 shadow-2xs">
                    <i data-lucide="boxes" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
