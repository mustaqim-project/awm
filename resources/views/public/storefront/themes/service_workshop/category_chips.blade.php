{{--
    Bengkel & Otomotif Category Chips (`service_workshop` / `apex_velocity`)
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-neutral-900 dark:text-white">
                    {{ $landingPage->services_title ?: __('storefront.kategori_pilihan') }}
                </h2>
                <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('storefront.pilih_kategori_filter') }}
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs sm:text-sm font-semibold flex items-center gap-1 hover:underline text-orange-600 dark:text-orange-400">
                <span>{{ __('storefront.view_all') }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap scrollbar-hide">
            {{-- All / Semua Produk Chip --}}
            @php
                $isAllActive = !request()->filled('category');
            @endphp
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="flex-shrink-0 inline-flex items-center gap-2.5 px-5 py-2.5 rounded-2xl text-sm font-semibold transition-all duration-200 min-h-[44px] {{ $isAllActive ? 'shadow-md' : 'bg-white dark:bg-[#1E293B] border border-orange-500/20 dark:border-white/10 text-neutral-800 dark:text-neutral-200 hover:border-orange-500' }}"
                @if($isAllActive) style="background: var(--theme-primary, #EA580C); color: #fff; border: 1px solid var(--theme-primary, #EA580C);" @endif>
                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ $isAllActive ? 'bg-white/20 text-white' : 'bg-orange-50 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400' }}">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                </div>
                <span>{{ __('storefront.semua_produk') }}</span>
            </a>

            @foreach ($productCategories->take(12) as $cat)
                @php
                    $isActive = request('category') == $cat->id;
                @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2.5 px-5 py-2.5 rounded-2xl text-sm font-semibold transition-all duration-200 min-h-[44px] {{ $isActive ? 'shadow-md' : 'bg-white dark:bg-[#1E293B] border border-orange-500/20 dark:border-white/10 hover:border-orange-500 text-neutral-800 dark:text-neutral-200' }}"
                    @if($isActive) style="background: var(--theme-primary, #EA580C); color: #fff; border: 1px solid var(--theme-primary, #EA580C);" @endif>
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ $isActive ? 'bg-white/20 text-white' : 'bg-orange-50 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400' }}">
                        <i data-lucide="tag" class="w-4 h-4"></i>
                    </div>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif

