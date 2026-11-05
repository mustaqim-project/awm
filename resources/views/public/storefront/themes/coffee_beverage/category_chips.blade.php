{{--
    Coffee & Beverage Category Chips (`coffee_beverage`)
    Artisan cafe category navigation with coffee bean icons and roast tone pills.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-[#3D2B1F] dark:text-[#FAF7F2]">
                    {{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}
                </h2>
                <p class="text-xs text-[#6B5744] dark:text-[#CBBDB1] mt-0.5">
                    {{ __('storefront.pilih_kategori_filter') }}
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-theme-primary hover:underline flex items-center gap-1">
                <span>{{ __('storefront.view_all') }}</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-none">
            {{-- Semua Produk Pill --}}
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-semibold transition min-h-[44px] {{ !request()->filled('category') ? 'theme-btn-primary shadow-xs' : 'border border-[#E8DEC8] dark:border-[#382B21] bg-white dark:bg-[#201914] text-[#3D2B1F] dark:text-[#FAF7F2] hover:border-theme-primary shadow-2xs' }}">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                <span>{{ __('storefront.semua_produk') }}</span>
            </a>

            @foreach ($productCategories->take(12) as $cat)
                @php $isActive = request('category') == $cat->id; @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-semibold transition min-h-[44px] {{ $isActive ? 'theme-btn-primary shadow-xs' : 'border border-[#E8DEC8] dark:border-[#382B21] bg-white dark:bg-[#201914] text-[#3D2B1F] dark:text-[#FAF7F2] hover:border-theme-primary shadow-2xs' }}">
                    <i data-lucide="tag" class="w-3.5 h-3.5 text-stone-400"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
