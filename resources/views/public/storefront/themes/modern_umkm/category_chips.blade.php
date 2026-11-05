{{--
    Modern UMKM Category Chips (`modern_umkm`)
    Friendly, accessible category pill navigation for local business offerings.
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-theme-primary animate-pulse"></span>
                <div>
                    <h2 class="font-heading font-bold text-lg sm:text-xl text-neutral-900 dark:text-white">
                        {{ $landingPage->services_title ?: __('storefront.pilihan_produk_layanan') }}
                    </h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        {{ __('storefront.pilih_kategori_filter') }}
                    </p>
                </div>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs font-semibold text-theme-primary hover:underline flex items-center gap-1 group">
                <span>{{ __('storefront.view_all') }}</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
        </div>

        <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-none">
            {{-- Semua Produk Chip --}}
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-medium transition-all duration-200 min-h-[44px] {{ !request()->filled('category') ? 'theme-btn-primary shadow-xs' : 'border border-black/10 dark:border-white/10 bg-white dark:bg-neutral-900 text-neutral-700 dark:text-neutral-200 hover:border-theme-primary/40' }}">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                <span>{{ __('storefront.semua_produk') }}</span>
            </a>

            @foreach ($productCategories->take(12) as $cat)
                @php $isActive = request('category') == $cat->id; @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-medium transition-all duration-200 min-h-[44px] {{ $isActive ? 'theme-btn-primary shadow-xs' : 'border border-black/10 dark:border-white/10 bg-white dark:bg-neutral-900 text-neutral-700 dark:text-neutral-200 hover:border-theme-primary/40' }}">
                    <span class="w-2 h-2 rounded-full {{ $isActive ? 'bg-white' : 'bg-theme-primary' }}"></span>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
