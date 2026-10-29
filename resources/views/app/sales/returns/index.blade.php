@extends('layouts.app', [
    'title' => __('sales.returns.page_title'),
    'headerTitle' => __('sales.returns.header_title'),
    'headerSubtitle' => __('sales.returns.header_subtitle'),
])

@section('content')
    @php
        $totalReturnsCount = $returns->total();
        $draftReturns = $returns->filter(fn($r) => $r->status === 'draft');
        $approvedReturns = $returns->filter(fn($r) => $r->status === 'approved');
        $completedReturns = $returns->filter(fn($r) => $r->status === 'completed');
        $pageTotalAmount = $returns->sum('total_amount');
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
                    const form = document.getElementById('form-delete-return');
                    form.action = this.deleteTarget.url;
                    form.submit();
                }
            }
        }">

        <x-module-header
            module="sales"
            :title="__('sales.returns.module_title')"
            :subtitle="__('sales.returns.module_subtitle')">
            @if (\App\Support\Context::hasPermission('sales.returns') || \App\Support\Context::hasPermission('sales.pipeline'))
                <a href="{{ route('sales.returns.create') }}"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-2 shadow-sm shadow-[#007AFF]/25 w-full sm:w-auto">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('sales.returns.btn_create') }}</span>
                </a>
            @endif
        </x-module-header>

        <x-module-tabs module="sales" />

        <!-- Flash Notifications (Apple Banner Style) -->
        @if (session('success'))
            <div
                class="rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
                <div class="flex-1 font-medium">{{ session('success') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div
                class="rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 px-4 py-3 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-[#FF3B30] shrink-0"></span>
                <div class="flex-1 font-medium">{{ session('error') }}</div>
            </div>
        @endif

        <!-- ===================================================== -->
        <!-- 2. KPI SUMMARY (Flat Neutral Material, Apple Standard) -->
        <!-- ===================================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Tile 1: Nilai Retur (System Red) -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('sales.returns.kpi_page_amount') }}</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                        Rp {{ number_format($pageTotalAmount, 0, ',', '.') }}
                    </span>
                    <span class="text-[11px] font-medium text-[#FF3B30] dark:text-[#FF453A]">{{ __('sales.returns.kpi_refund') }}</span>
                </div>
            </div>

            <!-- Tile 2: Total Retur (Neutral) -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('sales.returns.kpi_total_returns') }}</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span
                        class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ number_format($totalReturnsCount, 0, ',', '.') }}</span>
                    <span class="text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('sales.returns.kpi_recorded') }}</span>
                </div>
            </div>

            <!-- Tile 3: Menunggu Persetujuan (System Orange) -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('sales.returns.kpi_pending_approval') }}</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span
                        class="text-[24px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">{{ $draftReturns->count() }}</span>
                    <span class="text-[11px] font-medium text-[#FF9500] dark:text-[#FF9F0A]">{{ __('sales.returns.status_draft') }}</span>
                </div>
            </div>

            <!-- Tile 4: Selesai & Restock (System Green) -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('sales.returns.kpi_completed_returns') }}</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span
                        class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $completedReturns->count() }}</span>
                    <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">{{ __('sales.returns.stock_returned') }}</span>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 3. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
        <!-- ===================================================== -->
        <div
            class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr
                            class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 text-[11px] font-semibold uppercase tracking-wide">
                            <th class="py-2.5 px-4">{{ __('sales.returns.col_return_number') }}</th>
                            <th class="py-2.5 px-4">{{ __('sales.returns.col_transaction_ref') }}</th>
                            <th class="py-2.5 px-4">{{ __('sales.returns.col_customer') }}</th>
                            <th class="py-2.5 px-4">{{ __('sales.returns.col_return_date') }}</th>
                            <th class="py-2.5 px-4">{{ __('sales.returns.col_compensation_type') }}</th>
                            <th class="py-2.5 px-4 text-right">{{ __('sales.returns.col_total_amount') }}</th>
                            <th class="py-2.5 px-4 text-center">{{ __('sales.returns.col_status') }}</th>
                            <th class="py-2.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('sales.returns.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($returns as $return)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors group">
                                <td class="py-3 px-4 font-semibold whitespace-nowrap">
                                    <a href="{{ route('sales.returns.show', $return) }}"
                                        class="text-[#007AFF] hover:underline tabular-nums">
                                        {{ $return->return_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if ($return->invoice)
                                        <span
                                            class="inline-flex items-center gap-1 text-[12px] font-medium text-black/70 dark:text-white/70">
                                            <span
                                                class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">INV</span>
                                            <span class="tabular-nums">{{ $return->invoice->invoice_number }}</span>
                                        </span>
                                    @elseif($return->posOrder)
                                        <span
                                            class="inline-flex items-center gap-1 text-[12px] font-medium text-black/70 dark:text-white/70">
                                            <span
                                                class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-[#5856D6]/10 text-[#5856D6]">POS</span>
                                            <span class="tabular-nums">{{ $return->posOrder->order_number }}</span>
                                        </span>
                                    @else
                                        <span class="text-black/40 dark:text-white/40">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-medium text-black dark:text-white">
                                    @if ($return->invoice)
                                        {{ $return->invoice->customer?->name ?? __('sales.returns.general_customer') }}
                                    @elseif($return->posOrder)
                                        {{ $return->posOrder->customer?->name ?? ($return->posOrder->customer_name_guest ?: __('sales.returns.guest_customer')) }}
                                    @else
                                        {{ $return->customer?->name ?? __('sales.returns.general_customer') }}
                                    @endif
                                </td>
                                <td class="py-3 px-4 tabular-nums text-black/60 dark:text-white/60 whitespace-nowrap">
                                    {{ $return->return_date ? $return->return_date->format('d/m/Y') : $return->created_at->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[11px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70">
                                        @if ($return->refund_method === 'credit_note')
                                            {{ __('sales.returns.comp_credit_note') }}
                                        @elseif($return->refund_method === 'cash_refund')
                                            {{ __('sales.returns.comp_cash_refund') }}
                                        @elseif($return->refund_method === 'exchange')
                                            {{ __('sales.returns.comp_exchange') }}
                                        @else
                                            {{ str_replace('_', ' ', $return->refund_method ?? 'cash_refund') }}
                                        @endif
                                    </span>
                                </td>
                                <td
                                    class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white whitespace-nowrap">
                                    Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if ($return->status === 'completed')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('sales.returns.status_completed') }}
                                        </span>
                                    @elseif($return->status === 'approved')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span> {{ __('sales.returns.status_approved') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> {{ __('sales.returns.status_draft') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('sales.returns.pdf', $return) }}" target="_blank"
                                            class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="Unduh PDF"
                                            aria-label="Unduh PDF">
                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                        </a>
                                        @if ($return->status === 'draft' && (\App\Support\Context::hasPermission('sales.returns') || \App\Support\Context::hasPermission('sales.pipeline')))
                                        <a href="{{ route('sales.returns.edit', $return) }}"
                                            class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="Edit Retur"
                                            aria-label="Edit Retur">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('sales.returns.show', $return) }}"
                                            class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="{{ __('sales.returns.btn_detail') }}"
                                            aria-label="{{ __('sales.returns.btn_detail') }}">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        @if ($return->status === 'draft' && (\App\Support\Context::hasPermission('sales.returns') || \App\Support\Context::hasPermission('sales.pipeline')))
                                        <button type="button"
                                            @click="openDelete('{{ route('sales.returns.destroy', $return) }}', '{{ $return->return_number }}')"
                                            class="w-8 h-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="Hapus Retur Penjualan"
                                            aria-label="Hapus Retur Penjualan">
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
                                                d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                        </svg>
                                        <h4 class="text-[15px] font-medium text-black dark:text-white">{{ __('sales.returns.empty_title') }}</h4>
                                        <p class="text-[13px] text-black/50 dark:text-white/50 max-w-sm">{{ __('sales.returns.empty_desc') }}</p>
                                         @if (\App\Support\Context::hasPermission('sales.returns'))
                                         <a href="{{ route('sales.returns.create') }}"
                                             class="mt-2 h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] transition flex items-center gap-1.5">
                                             <span>{{ __('sales.returns.btn_create_first') }}</span>
                                         </a>
                                         @endif
                                     </div>
                                 </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 4. MOBILE GROUPED INSET LIST (Standar iOS 18)         -->
        <!-- ===================================================== -->
        <div
            class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
            @forelse($returns as $return)
                <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
                    <a href="{{ route('sales.returns.show', $return) }}" class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="text-[15px] font-semibold text-black dark:text-white truncate tabular-nums">
                                {{ $return->return_number }}</p>
                            @if ($return->status === 'completed')
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                            @elseif($return->status === 'approved')
                                <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] shrink-0"></span>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] shrink-0"></span>
                            @endif
                        </div>
                        <p class="text-[13px] text-black/60 dark:text-white/60 truncate">
                            @if ($return->invoice)
                                {{ $return->invoice->customer?->name ?? __('sales.returns.general_customer') }}
                            @elseif($return->posOrder)
                                {{ $return->posOrder->customer?->name ?? ($return->posOrder->customer_name_guest ?: __('sales.returns.guest_customer')) }}
                            @else
                                {{ $return->customer?->name ?? __('sales.returns.general_customer') }}
                            @endif
                        </p>
                        <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">
                            {{ $return->return_date ? $return->return_date->format('d/m/Y') : $return->created_at->format('d/m/Y') }}
                            &bull; Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                        </p>
                    </a>
                    <div class="flex items-center gap-1 shrink-0">
                        <a href="{{ route('sales.returns.show', $return) }}" class="p-1.5 text-[#007AFF]" title="Detail">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                        @if ($return->status === 'draft' && (\App\Support\Context::hasPermission('sales.returns') || \App\Support\Context::hasPermission('sales.pipeline')))
                        <button type="button"
                            @click="openDelete('{{ route('sales.returns.destroy', $return) }}', '{{ $return->return_number }}')"
                            class="p-1.5 text-[#FF3B30] hover:bg-[#FF3B30]/10 rounded-md transition-colors cursor-pointer"
                            title="Hapus Retur Penjualan">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px]">
                    {{ __('sales.returns.empty_mobile') }}
                </div>
            @endforelse
        </div>

        <!-- ===================================================== -->
        <!-- 5. PAGINATION (Minimalist Apple HIG Stepper)          -->
        <!-- ===================================================== -->
        @if ($returns->hasPages())
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 px-4 py-3 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
                <div>
                    {{ __('sales.invoices.pagination_showing') }} <span
                        class="font-medium text-black dark:text-white tabular-nums">{{ $returns->firstItem() ?? 0 }}–{{ $returns->lastItem() ?? 0 }}</span>
                    {{ __('sales.invoices.pagination_of') }} <span class="font-medium text-black dark:text-white tabular-nums">{{ $returns->total() }}</span>
                    {{ __('sales.returns.pagination_returns') }}
                </div>
                <div>
                    {{ $returns->links() }}
                </div>
            </div>
        @endif

        <!-- Delete Sales Return Modal Dialog (Apple HIG Action Confirmation) -->
        <template x-teleport="body">
            <div x-show="deleteModalOpen"
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="return-delete-dialog-title">

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
                            <p id="return-delete-dialog-title" class="text-[17px] font-semibold text-black dark:text-white">Hapus Retur Penjualan?</p>
                            <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                                Apakah Anda yakin ingin menghapus draf retur <span class="font-medium text-black dark:text-white" x-text="deleteTarget.name"></span>? Tindakan ini tidak dapat dibatalkan.
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

        <!-- Hidden Sales Return Delete Form -->
        <form id="form-delete-return" method="POST" action="" class="hidden">
            @csrf
            @method('DELETE')
        </form>

    </div>
@endsection
