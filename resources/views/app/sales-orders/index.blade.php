@extends('layouts.app', [
    'title' => __('sales.sales_orders.page_title'),
    'headerTitle' => __('sales.sales_orders.header_title'),
    'headerSubtitle' => __('sales.sales_orders.header_subtitle'),
])

@section('content')
    @php
        $totalCount = $salesOrders->total();
        $confirmedOrders = $salesOrders->filter(fn($so) => $so->status === 'confirmed');
        $partialOrders = $salesOrders->filter(fn($so) => $so->status === 'partially_fulfilled');
        $fulfilledOrders = $salesOrders->filter(fn($so) => $so->status === 'fulfilled');
        $currentStatus = request('status', '');
        $currentSearch = request('search', '');
    @endphp

    <div class="max-w-[1360px] mx-auto space-y-6 pb-12"
        x-data="{
            deleteModalOpen: false,
            deleteTarget: { url: '', name: '' },
            openDelete(url, name) {
                this.deleteTarget = { url, name };
                this.deleteModalOpen = true;
            },
            closeDelete() {
                this.deleteModalOpen = false;
                this.deleteTarget = { url: '', name: '' };
            },
            submitDelete() {
                if (this.deleteTarget.url) {
                    const form = document.getElementById('form-delete-sales-order');
                    form.action = this.deleteTarget.url;
                    form.submit();
                }
            }
        }">

        <x-module-header
            module="sales"
            :title="__('sales.sales_orders.module_title')"
            :subtitle="__('sales.sales_orders.module_subtitle')">
            @if (\App\Support\Context::hasPermission('sales.pipeline'))
                <a href="{{ route('sales.orders.create') }}"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-2 shadow-sm shadow-[#007AFF]/25 w-full sm:w-auto">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('sales.sales_orders.btn_create') }}</span>
                </a>
            @endif
        </x-module-header>

        <x-module-tabs module="sales" />

        <!-- ===================================================== -->
        <!-- 2. KPI SUMMARY (Flat Neutral Material, Apple Standard) -->
        <!-- ===================================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Tile 1: Total Orders (Neutral) -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('sales.sales_orders.kpi_total_orders') }}</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span
                        class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ number_format($totalCount, 0, ',', '.') }}</span>
                    <span class="text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('sales.sales_orders.kpi_recorded') }}</span>
                </div>
            </div>

            <!-- Tile 2: Terkonfirmasi (System Blue) -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('sales.sales_orders.kpi_confirmed') }}</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span
                        class="text-[24px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ $confirmedOrders->count() }}</span>
                    <span class="text-[11px] font-medium text-[#007AFF] dark:text-[#0A84FF">{{ __('sales.sales_orders.kpi_ready_process') }}</span>
                </div>
            </div>

            <!-- Tile 3: Sebagian Terpenuhi (System Orange) -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('sales.sales_orders.kpi_partially_fulfilled') }}</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span
                        class="text-[24px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">{{ $partialOrders->count() }}</span>
                    <span class="text-[11px] font-medium text-[#FF9500] dark:text-[#FF9F0A">{{ __('sales.sales_orders.kpi_partial') }}</span>
                </div>
            </div>

            <!-- Tile 4: Selesai / Fulfilled (System Green) -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('sales.sales_orders.kpi_fulfilled') }}</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span
                        class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $fulfilledOrders->count() }}</span>
                    <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158">{{ __('sales.sales_orders.kpi_completed') }}</span>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 3. CONTROLS: SEARCH & APPLE SEGMENTED CONTROL         -->
        <!-- ===================================================== -->
        <div
            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Search Form (macOS Style: Clean Fill) -->
            <form method="GET" action="{{ route('sales.orders.index') }}" class="relative flex-1 max-w-md">
                @if ($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="search" value="{{ $currentSearch }}"
                    placeholder="{{ __('sales.sales_orders.search_placeholder') }}"
                    class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </form>

            <!-- Segmented Control Filters -->
            <div
                class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium self-start sm:self-auto overflow-x-auto max-w-full">
                <a href="{{ route('sales.orders.index', ['search' => $currentSearch]) }}"
                    class="px-3 py-1 rounded-[7px] transition-all whitespace-nowrap {{ empty($currentStatus) ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55' }}">
                    {{ __('sales.sales_orders.filter_all', ['count' => $totalCount]) }}
                </a>
                <a href="{{ route('sales.orders.index', ['status' => 'confirmed', 'search' => $currentSearch]) }}"
                    class="px-3 py-1 rounded-[7px] transition-all whitespace-nowrap {{ $currentStatus === 'confirmed' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55' }}">
                    {{ __('sales.sales_orders.filter_confirmed', ['count' => $confirmedOrders->count()]) }}
                </a>
                <a href="{{ route('sales.orders.index', ['status' => 'partially_fulfilled', 'search' => $currentSearch]) }}"
                    class="px-3 py-1 rounded-[7px] transition-all whitespace-nowrap {{ $currentStatus === 'partially_fulfilled' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55' }}">
                    {{ __('sales.sales_orders.filter_partially_fulfilled', ['count' => $partialOrders->count()]) }}
                </a>
                <a href="{{ route('sales.orders.index', ['status' => 'fulfilled', 'search' => $currentSearch]) }}"
                    class="px-3 py-1 rounded-[7px] transition-all whitespace-nowrap {{ $currentStatus === 'fulfilled' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55' }}">
                    {{ __('sales.sales_orders.filter_fulfilled', ['count' => $fulfilledOrders->count()]) }}
                </a>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 4. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
        <!-- ===================================================== -->
        <div
            class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr
                            class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 text-[11px] font-semibold uppercase tracking-wide">
                            <th class="py-2.5 px-4">{{ __('sales.sales_orders.col_so_number') }}</th>
                            <th class="py-2.5 px-4">{{ __('sales.sales_orders.col_customer') }}</th>
                            <th class="py-2.5 px-4">{{ __('sales.sales_orders.col_order_date') }}</th>
                            <th class="py-2.5 px-4">{{ __('sales.sales_orders.col_expected_delivery') }}</th>
                            <th class="py-2.5 px-4 text-right">{{ __('sales.sales_orders.col_tax') }}</th>
                            <th class="py-2.5 px-4 text-right">{{ __('sales.sales_orders.col_total_amount') }}</th>
                            <th class="py-2.5 px-4 text-center">{{ __('sales.sales_orders.col_fulfillment_status') }}</th>
                            <th class="py-2.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('sales.sales_orders.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($salesOrders as $so)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors group">
                                <td class="py-3 px-4 font-semibold">
                                    <a href="{{ route('sales.orders.show', $so) }}"
                                        class="text-[#007AFF] hover:underline tabular-nums">
                                        {{ $so->so_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-black dark:text-white">
                                        {{ $so->customer->name }}
                                    </div>
                                    @if ($so->customer->company)
                                        <div class="text-[11px] text-black/45 dark:text-white/45">
                                            {{ $so->customer->company }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 tabular-nums text-black/60 dark:text-white/60 whitespace-nowrap">
                                    {{ $so->order_date->format('d M Y') }}
                                </td>
                                <td class="py-3 px-4 tabular-nums text-black/60 dark:text-white/60 whitespace-nowrap">
                                    {{ $so->expected_delivery_date ? $so->expected_delivery_date->format('d M Y') : '-' }}
                                </td>
                                <td
                                    class="py-3 px-4 text-right tabular-nums text-black/60 dark:text-white/60 whitespace-nowrap">
                                    <div>Rp {{ number_format($so->tax_amount, 0, ',', '.') }}</div>
                                    <span
                                        class="text-[10px] text-black/40 dark:text-white/40">({{ number_format($so->tax_percentage, 1) }}%)</span>
                                </td>
                                <td
                                    class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white whitespace-nowrap">
                                    Rp {{ number_format($so->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if ($so->status === 'fulfilled')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('sales.sales_orders.status_fulfilled') }}
                                        </span>
                                    @elseif($so->status === 'partially_fulfilled')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> {{ __('sales.sales_orders.status_partially_fulfilled') }}
                                        </span>
                                    @elseif($so->status === 'draft')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-black/40 dark:bg-white/40"></span> Draf
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span> {{ __('sales.sales_orders.status_confirmed') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('sales.orders.pdf', $so) }}" target="_blank"
                                            class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="Unduh PDF"
                                            aria-label="Unduh PDF">
                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                        </a>
                                        @if (\App\Support\Context::hasPermission('sales.pipeline') || \App\Support\Context::hasPermission('sales.view'))
                                        <a href="{{ route('sales.orders.edit', $so) }}"
                                            class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="Edit Pesanan"
                                            aria-label="Edit Pesanan">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('sales.orders.show', $so) }}"
                                            class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="{{ __('sales.sales_orders.btn_detail') }}"
                                            aria-label="{{ __('sales.sales_orders.btn_detail') }}">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        @if ($so->status === 'draft' && \App\Support\Context::hasPermission('sales.pipeline'))
                                        <button type="button"
                                            @click="openDelete('{{ route('sales.orders.destroy', $so) }}', '{{ $so->so_number }}')"
                                            class="w-8 h-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="Hapus Pesanan Penjualan"
                                            aria-label="Hapus Pesanan Penjualan">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-14 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-10 h-10 text-black/20 dark:text-white/20 stroke-current"
                                            fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                        <h4 class="text-[15px] font-medium text-black dark:text-white">{{ __('sales.sales_orders.empty_title') }}</h4>
                                        <p class="text-[13px] text-black/50 dark:text-white/50 max-w-sm">{{ __('sales.sales_orders.empty_desc') }}</p>
                                        <a href="{{ route('sales.orders.create') }}"
                                            class="mt-2 h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] transition flex items-center gap-1.5">
                                            <span>{{ __('sales.sales_orders.btn_create_first') }}</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 5. MOBILE GROUPED INSET LIST (Standar iOS 18)         -->
        <!-- ===================================================== -->
        <div
            class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
            @forelse($salesOrders as $so)
                <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
                    <a href="{{ route('sales.orders.show', $so) }}" class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="text-[15px] font-semibold text-black dark:text-white truncate tabular-nums">
                                {{ $so->so_number }}</p>
                            @if ($so->status === 'fulfilled')
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                            @elseif($so->status === 'partially_fulfilled')
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] shrink-0"></span>
                            @elseif($so->status === 'draft')
                                <span class="w-1.5 h-1.5 rounded-full bg-black/40 dark:bg-white/40 shrink-0"></span>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] shrink-0"></span>
                            @endif
                        </div>
                        <p class="text-[13px] text-black/60 dark:text-white/60 truncate">{{ $so->customer->name }}</p>
                        <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">
                            {{ $so->order_date->format('d M Y') }} &bull; Rp
                            {{ number_format($so->total_amount, 0, ',', '.') }}
                        </p>
                    </a>
                    <div class="flex items-center gap-1 shrink-0">
                        <a href="{{ route('sales.orders.show', $so) }}" class="p-1.5 text-[#007AFF]" title="Detail">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                        @if ($so->status === 'draft' && \App\Support\Context::hasPermission('sales.pipeline'))
                        <button type="button"
                            @click="openDelete('{{ route('sales.orders.destroy', $so) }}', '{{ $so->so_number }}')"
                            class="p-1.5 text-[#FF3B30] hover:bg-[#FF3B30]/10 rounded-md transition-colors cursor-pointer"
                            title="Hapus Pesanan Penjualan">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px]">
                    {{ __('sales.sales_orders.empty_mobile') }}
                </div>
            @endforelse
        </div>

        <!-- ===================================================== -->
        <!-- 6. PAGINATION (Minimalist Apple HIG Stepper)          -->
        <!-- ===================================================== -->
        @if ($salesOrders->hasPages())
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 px-4 py-3 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
                <div>
                    {{ __('sales.sales_orders.pagination_showing') }} <span
                        class="font-medium text-black dark:text-white tabular-nums">{{ $salesOrders->firstItem() ?? 0 }}–{{ $salesOrders->lastItem() ?? 0 }}</span>
                    {{ __('sales.sales_orders.pagination_of') }} <span
                        class="font-medium text-black dark:text-white tabular-nums">{{ $salesOrders->total() }}</span>
                    {{ __('sales.sales_orders.pagination_orders') }}
                </div>
                <div>
                    {{ $salesOrders->links() }}
                </div>
            </div>
        @endif

        <!-- Delete Sales Order Modal Dialog (Apple HIG Action Confirmation) -->
        <template x-teleport="body">
            <div x-show="deleteModalOpen"
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="so-delete-dialog-title">

                <div x-show="deleteModalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"
                    @click="closeDelete()"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div x-show="deleteModalOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="w-full max-w-[320px] rounded-[18px] bg-white/90 dark:bg-[#2C2C2E]/90 backdrop-blur-2xl border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden transition-all text-center">

                        <div class="px-4 pt-5 pb-4">
                            <p id="so-delete-dialog-title" class="text-[17px] font-semibold text-black dark:text-white">Hapus Pesanan Penjualan?</p>
                            <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                                Apakah Anda yakin ingin menghapus draf pesanan <span class="font-medium text-black dark:text-white" x-text="deleteTarget.name"></span>? Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                            <button type="button" @click="closeDelete()"
                                    class="min-h-[44px] py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="button" @click="submitDelete()"
                                    class="min-h-[44px] py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Hidden Sales Order Delete Form -->
        <form id="form-delete-sales-order" method="POST" action="" class="hidden">
            @csrf
            @method('DELETE')
        </form>

    </div>
@endsection
