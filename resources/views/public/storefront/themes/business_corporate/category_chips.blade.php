{{--
    Business Corporate Category Navigation (`business_corporate`)
    Segmented corporate category tabs for rapid solution exploration.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between gap-4 mb-4 pb-2 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h2 class="font-heading font-bold text-lg sm:text-xl text-slate-900 dark:text-white">
                    {{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ __('storefront.pilih_kategori_filter') }}
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-theme-primary hover:underline flex items-center gap-1">
                <span>{{ __('storefront.view_all') }}</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none">
            {{-- Semua Produk Tab --}}
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="flex-shrink-0 inline-flex items-center gap-2 px-3.5 py-2.5 rounded-lg text-xs font-semibold transition min-h-[44px] {{ !request()->filled('category') ? 'theme-btn-primary shadow-xs' : 'border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:border-theme-primary shadow-2xs' }}">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                <span>{{ __('storefront.semua_produk') }}</span>
            </a>

            @foreach ($productCategories->take(12) as $cat)
                @php $isActive = request('category') == $cat->id; @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-3.5 py-2.5 rounded-lg text-xs font-semibold transition min-h-[44px] {{ $isActive ? 'theme-btn-primary shadow-xs' : 'border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:border-theme-primary shadow-2xs' }}">
                    <i data-lucide="folder" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
