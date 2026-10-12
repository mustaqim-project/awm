{{--
    Service Business Category Chips (`service_business`)
    Clean service category pills for laundry, care, and maintenance solutions.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-bold text-lg sm:text-xl text-slate-900 dark:text-white">
                    Pilihan Paket Layanan
                </h2>
                <p class="text-xs text-slate-500 dark:text-sky-300/70">Pilih kategori layanan kiloan, satuan, atau perawatan khusus</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1">
                <span>Seluruh Layanan</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-semibold transition border border-sky-200 dark:border-sky-900/60 bg-white dark:bg-[#0D1C2A] text-slate-800 dark:text-sky-100 hover:border-sky-500 hover:text-sky-600 dark:hover:text-sky-300 shadow-2xs">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-sky-500"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
