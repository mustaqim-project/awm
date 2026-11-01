@php
    $total = method_exists($paginator, 'total') ? $paginator->total() : $paginator->count();
    $currentPerPage = (int) $paginator->perPage();
    $options = \App\Support\PaginationHelper::options($currentPerPage);
    $firstItem = method_exists($paginator, 'firstItem') ? ($paginator->firstItem() ?? ($total > 0 ? 1 : 0)) : 1;
    $lastItem = method_exists($paginator, 'lastItem') ? ($paginator->lastItem() ?? $total) : min($total, $currentPerPage);
@endphp

@if ($total > 0)
    <nav role="navigation" aria-label="{{ __('pagination.navigation', ['default' => 'Navigasi Halaman']) }}"
         class="flex flex-col md:flex-row items-center justify-between gap-3.5 w-full py-2 text-[13px] text-black/70 dark:text-white/70 select-none">
        
        <!-- Left: Summary of items count -->
        <div class="flex items-center gap-2 text-xs text-black/60 dark:text-white/60 order-2 md:order-1 self-start sm:self-center">
            <span>
                {{ __('Menampilkan') }}
                <strong class="font-semibold text-black dark:text-white tabular-nums">{{ number_format($firstItem) }}</strong>
                {{ __('sampai') }}
                <strong class="font-semibold text-black dark:text-white tabular-nums">{{ number_format($lastItem) }}</strong>
                {{ __('dari') }}
                <strong class="font-semibold text-black dark:text-white tabular-nums">{{ number_format($total) }}</strong>
                {{ __('data') }}
            </span>
        </div>

        <!-- Center: Dynamic Per-Page Row Selector (10, 25, 50, 100, 500, 1000, 5000, 10000) -->
        <div class="flex items-center gap-2 text-xs order-1 md:order-2 self-start sm:self-center">
            <label for="per_page_select_{{ md5($paginator->getPageName()) }}" class="text-black/50 dark:text-white/50 font-medium whitespace-nowrap">
                {{ __('Tampilkan:') }}
            </label>
            <div class="relative inline-block">
                <select id="per_page_select_{{ md5($paginator->getPageName()) }}"
                        onchange="
                            const url = new URL(window.location.href);
                            url.searchParams.set('per_page', this.value);
                            url.searchParams.set('{{ $paginator->getPageName() }}', '1');
                            window.location.href = url.toString();
                        "
                        class="min-h-[30px] pl-2.5 pr-7 py-1 text-xs font-semibold rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 transition-all cursor-pointer appearance-none">
                    @foreach ($options as $opt)
                        <option value="{{ $opt }}" {{ $currentPerPage === $opt ? 'selected' : '' }} class="bg-white dark:bg-[#1C1C1E] text-black dark:text-white">
                            {{ number_format($opt) }} {{ $opt === 25 ? '(' . __('Default') . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-1.5 text-black/40 dark:text-white/40">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
            <span class="text-black/40 dark:text-white/40 text-[11px] whitespace-nowrap">{{ __('baris') }}</span>
        </div>

        <!-- Right: Page Navigation (Numbered Buttons + Prev/Next) -->
        <div class="flex items-center gap-1 order-3 self-end sm:self-center">
            @if ($paginator->hasPages())
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}"
                          class="min-h-[30px] px-2.5 py-1 text-xs font-medium rounded-[8px] text-black/30 dark:text-white/30 bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 cursor-not-allowed flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        <span class="hidden sm:inline">{{ __('Prev') }}</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}"
                       class="min-h-[30px] px-2.5 py-1 text-xs font-medium rounded-[8px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/10 dark:border-white/10 transition-all flex items-center gap-1 active:scale-[0.97]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        <span class="hidden sm:inline">{{ __('Prev') }}</span>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                <div class="hidden sm:flex items-center gap-1">
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="px-1.5 py-1 text-xs text-black/40 dark:text-white/40 cursor-default">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page"
                                          class="min-w-[30px] min-h-[30px] px-2 py-1 text-xs font-bold rounded-[8px] bg-[#007AFF] text-white shadow-sm flex items-center justify-center tabular-nums">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}"
                                       class="min-w-[30px] min-h-[30px] px-2 py-1 text-xs font-medium rounded-[8px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition-all flex items-center justify-center tabular-nums active:scale-[0.97]">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                {{-- Mobile Simple Current Page Indicator --}}
                <span class="sm:hidden text-xs font-semibold text-black/70 dark:text-white/70 px-2 tabular-nums">
                    {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                </span>

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}"
                       class="min-h-[30px] px-2.5 py-1 text-xs font-medium rounded-[8px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/10 dark:border-white/10 transition-all flex items-center gap-1 active:scale-[0.97]">
                        <span class="hidden sm:inline">{{ __('Next') }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}"
                          class="min-h-[30px] px-2.5 py-1 text-xs font-medium rounded-[8px] text-black/30 dark:text-white/30 bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 cursor-not-allowed flex items-center gap-1">
                        <span class="hidden sm:inline">{{ __('Next') }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                @endif
            @else
                <span class="text-xs text-black/40 dark:text-white/40 font-medium px-2 bg-black/[0.02] dark:bg-white/[0.02] py-1 rounded-[8px] border border-black/5 dark:border-white/5">
                    {{ __('Halaman 1 dari 1') }}
                </span>
            @endif
        </div>
    </nav>
@endif
