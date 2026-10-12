{{--
    Business Corporate Category Navigation (`business_corporate`)
    Segmented corporate category tabs for rapid solution exploration.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4 pb-2 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h2 class="font-heading font-bold text-lg sm:text-xl text-slate-900 dark:text-white">
                    Divisi Produk &amp; Solusi
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih kategori untuk memfilter spesifikasi pengadaan</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                <span>Seluruh Katalog</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(8) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 shadow-2xs">
                    <i data-lucide="folder" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
