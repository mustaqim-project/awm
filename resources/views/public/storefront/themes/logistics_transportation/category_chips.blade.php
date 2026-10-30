{{--
    Logistics & Transportation Category Chips (`logistics_transportation`)
    Fleet type and logistics service pills for CDD, FTL, reefer trucks, inter-island cargo, and moving services.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-black text-2xl sm:text-3xl text-slate-900 dark:text-white uppercase">{{ __('storefront.kategori_armada_layanan_kargo') }}</h2>
                <p class="text-xs text-slate-500 dark:text-amber-100/70">Pilih jenis armada pengangkutan atau rute pengiriman yang sesuai dengan muatan bisnis Anda</p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-bold text-amber-700 dark:text-amber-300 hover:underline flex items-center gap-1 uppercase">
                <span>{{ __('storefront.semua_armada') }}</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition border border-amber-300/80 dark:border-amber-900/60 bg-white dark:bg-[#131B26] text-slate-800 dark:text-amber-100 hover:border-amber-500 hover:text-amber-700 dark:hover:text-amber-300 shadow-2xs uppercase tracking-wide">
                    <i data-lucide="truck" class="w-3.5 h-3.5 text-amber-600"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
