{{--
    Construction & Property Category Chips (`construction_property`)
    Technical architectural division tabs for construction projects and property listings.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    @php
        $catHeading = $landingPage->services_title ?: __('storefront.kategori_pilihan');
        $catSubtitle = $landingPage->services_subtitle ?: __('storefront.kategori_pilihan_sub');
    @endphp

    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-black text-lg sm:text-xl text-slate-900 dark:text-white uppercase">
                    {{ $catHeading }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ $catSubtitle }}
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                <span>{{ __('storefront.view_all') }}</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($productCategories->take(10) as $cat)
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded text-xs font-bold uppercase tracking-wider transition border border-amber-300 dark:border-amber-900/60 bg-white dark:bg-[#181613] text-slate-800 dark:text-slate-200 hover:border-amber-500 hover:text-amber-600 dark:hover:text-amber-400 shadow-2xs">
                    <i data-lucide="building" class="w-3.5 h-3.5 text-amber-600"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
