{{--
    Gadget & Elektronik Category Chips (`retail_electronics` / `nexus_cyber`)
--}}
@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    @php
        $catHeading = $landingPage->services_title ?: __('storefront.kategori_pilihan');
        $catSubtitle = $landingPage->services_subtitle ?: __('storefront.kategori_pilihan_sub');
    @endphp
    <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl text-neutral-900 dark:text-white">
                    {{ $catHeading }}
                </h2>
                <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                    {{ $catSubtitle }}
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs sm:text-sm font-semibold flex items-center gap-1 hover:underline text-cyan-600 dark:text-cyan-400">
                <span>{{ __('storefront.view_all') }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap scrollbar-hide">
            {{-- Semua Produk Chip --}}
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="flex-shrink-0 inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-sm font-semibold transition-all duration-200 hover:shadow-md min-h-[46px] {{ !request()->filled('category') ? 'bg-cyan-500 text-black font-bold shadow-md shadow-cyan-500/20' : 'bg-white dark:bg-[#111827] border border-cyan-100 dark:border-cyan-900/40 text-neutral-800 dark:text-neutral-200 hover:border-cyan-500' }}">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ !request()->filled('category') ? 'bg-black/20 text-black' : 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400' }}">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                </div>
                <span>{{ __('storefront.semua_produk') }}</span>
            </a>

            @foreach ($productCategories->take(12) as $cat)
                @php
                    $isActive = request('category') == $cat->id;
                @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl text-sm font-semibold transition-all duration-200 hover:shadow-md hover:scale-[1.02] min-h-[46px] {{ $isActive ? 'bg-cyan-500 text-black font-bold shadow-md shadow-cyan-500/20' : 'bg-white dark:bg-[#111827] border border-cyan-100 dark:border-cyan-900/40 text-neutral-800 dark:text-neutral-200 hover:border-cyan-500' }}">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ $isActive ? 'bg-black/20 text-black' : 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400' }}">
                        <i data-lucide="tag" class="w-4 h-4"></i>
                    </div>
                    <span>{{ $cat->name }}</span>
                    @if (isset($cat->products_count))
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-mono tabular-nums {{ $isActive ? 'bg-black/20 text-black' : 'bg-cyan-950/40 text-cyan-300' }}">
                            {{ $cat->products_count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
