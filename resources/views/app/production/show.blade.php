@extends('layouts.app', [
    'title' => __('production.page_title_show', ['order_number' => $productionOrder->order_number]),
])

@section('content')
<div class="max-w-[1200px] mx-auto w-full px-4 sm:px-6 space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="{
    showIssueModal: false,
    showCompleteModal: false,
    showCancelModal: false,
    actualQty: {{ old('actual_quantity', $productionOrder->actual_quantity > 0 ? $productionOrder->actual_quantity : $productionOrder->planned_quantity) }},
    rejectedQty: {{ old('rejected_quantity', $productionOrder->rejected_quantity ?? 0) }},
    laborCost: {{ old('total_labor_cost', $productionOrder->total_labor_cost) }},
    machineCost: {{ old('total_machine_cost', $productionOrder->total_machine_cost) }},
    materialCost: {{ (float) $productionOrder->total_material_cost }},

    get totalCost() {
        return this.materialCost + (parseFloat(this.laborCost) || 0) + (parseFloat(this.machineCost) || 0);
    },

    get hppPerUnit() {
        const qty = parseFloat(this.actualQty) || 0;
        return qty > 0 ? (this.totalCost / qty) : 0;
    },

    formatRupiah(num) {
        return '{{ $business->currency_symbol }} ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(num || 0));
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER                              -->
    <!-- ===================================================== -->
    <x-module-header
        :title="__('production.title_show', ['order_number' => $productionOrder->order_number])"
        :subtitle="__('production.subtitle_show', ['product' => $productionOrder->product?->name, 'creator' => $productionOrder->creator?->name ?? 'Sistem', 'date' => $productionOrder->created_at?->translatedFormat('d F Y H:i')])"
        :breadcrumbs="[
            ['label' => __('common.dashboard'), 'url' => route('dashboard')],
            ['label' => __('production.nav_breadcrumb_production'), 'url' => route('production.index')],
            ['label' => $productionOrder->order_number, 'url' => null],
        ]">
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('production.index') }}"
                class="min-h-[44px] sm:min-h-0 h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition flex items-center justify-center gap-1.5 shrink-0">
                <i data-lucide="arrow-left" class="w-4 h-4 text-black/60 dark:text-white/60 shrink-0"></i>
                <span>{{ __('production.btn_back') }}</span>
            </a>

            @if($productionOrder->canIssueMaterials() && \App\Support\Context::hasPermission('production.execute'))
                <button type="button" @click="showIssueModal = true"
                    class="min-h-[44px] sm:min-h-0 h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#FF9500] hover:bg-[#E08500] active:scale-[0.98] transition flex items-center justify-center gap-1.5 shadow-sm shadow-[#FF9500]/25 shrink-0">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>{{ __('production.btn_issue_materials') }}</span>
                </button>
            @endif

            @if($productionOrder->canComplete() && \App\Support\Context::hasPermission('production.complete'))
                <button type="button" @click="showCompleteModal = true"
                    class="min-h-[44px] sm:min-h-0 h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#30B34E] active:scale-[0.98] transition flex items-center justify-center gap-1.5 shadow-sm shadow-[#34C759]/25 shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>{{ __('production.btn_complete_yield') }}</span>
                </button>
            @endif

            @if($productionOrder->canCancel() && \App\Support\Context::hasPermission('production.cancel'))
                <button type="button" @click="showCancelModal = true"
                    class="min-h-[44px] sm:min-h-0 h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition flex items-center justify-center gap-1.5 shrink-0">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    <span>{{ __('production.btn_cancel_order') }}</span>
                </button>
            @endif
        </div>
    </x-module-header>

    <!-- ===================================================== -->
    <!-- 2. WORKFLOW STEPPER STATUS                            -->
    <!-- ===================================================== -->
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-[13px]">
            <!-- Step 1: Draf -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold shrink-0 {{ $productionOrder->isCancelled() ? 'bg-[#FF3B30]/10 text-[#FF3B30]' : 'bg-[#007AFF] text-white' }}">
                    1
                </div>
                <div>
                    <span class="font-bold text-black dark:text-white block">{{ __('production.stepper_step_1_title') }}</span>
                    <span class="text-[11px] text-black/50 dark:text-white/50">{{ __('production.stepper_step_1_desc') }}</span>
                </div>
            </div>

            <!-- Step 2: Bahan Baku Dikeluarkan -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold shrink-0 {{ $productionOrder->isInProgress() || $productionOrder->isCompleted() ? 'bg-[#FF9500] text-white' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/40 dark:text-white/40' }}">
                    2
                </div>
                <div>
                    <span class="font-bold text-black dark:text-white block">{{ __('production.stepper_step_2_title') }}</span>
                    <span class="text-[11px] text-black/50 dark:text-white/50">{{ $productionOrder->isInProgress() || $productionOrder->isCompleted() ? __('production.stepper_step_2_issued') : __('production.stepper_step_2_pending') }}</span>
                </div>
            </div>

            <!-- Step 3: Proses Pabrik -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold shrink-0 {{ $productionOrder->isInProgress() ? 'bg-[#FF9500] text-white animate-pulse' : ($productionOrder->isCompleted() ? 'bg-[#34C759] text-white' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/40 dark:text-white/40') }}">
                    3
                </div>
                <div>
                    <span class="font-bold text-black dark:text-white block">{{ __('production.stepper_step_3_title') }}</span>
                    <span class="text-[11px] text-black/50 dark:text-white/50">{{ $productionOrder->isCompleted() ? __('production.stepper_step_3_completed') : ($productionOrder->isInProgress() ? __('production.stepper_step_3_in_progress') : __('production.stepper_step_3_waiting')) }}</span>
                </div>
            </div>

            <!-- Step 4: Barang Jadi Diterima -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold shrink-0 {{ $productionOrder->isCompleted() ? 'bg-[#34C759] text-white' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/40 dark:text-white/40' }}">
                    4
                </div>
                <div>
                    <span class="font-bold text-black dark:text-white block">{{ __('production.stepper_step_4_title') }}</span>
                    <span class="text-[11px] text-black/50 dark:text-white/50">{{ $productionOrder->isCompleted() ? __('production.stepper_step_4_received', ['qty' => number_format($productionOrder->actual_quantity, 0, ',', '.'), 'unit' => ($productionOrder->product?->outputUnit?->name ?? 'pcs')]) : __('production.stepper_step_4_pending') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. BENTO HIGHLIGHTS                                   -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Target Output -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">{{ __('production.highlight_planned_output') }}</span>
            <span class="text-2xl font-bold tabular-nums text-black dark:text-white mt-1 block">
                {{ number_format((float) $productionOrder->planned_quantity, 0, ',', '.') }}
                <span class="text-[13px] font-normal text-black/45 dark:text-white/45">{{ $productionOrder->product?->outputUnit?->name ?? 'pcs' }}</span>
            </span>
            <span class="text-[11px] text-black/40 dark:text-white/40 mt-1 block">
                {{ __('production.highlight_start_date', ['date' => \Carbon\Carbon::parse($productionOrder->planned_start_date)->translatedFormat('d M Y')]) }}
            </span>
        </div>

        <!-- Output Aktual & Yield -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">{{ __('production.highlight_actual_output') }}</span>
            <span class="text-2xl font-bold tabular-nums mt-1 block {{ $productionOrder->isCompleted() ? 'text-[#34C759]' : 'text-black/40 dark:text-white/40' }}">
                {{ $productionOrder->isCompleted() ? number_format((float) $productionOrder->actual_quantity, 0, ',', '.') : '0' }}
                <span class="text-[13px] font-normal text-black/45 dark:text-white/45">{{ $productionOrder->product?->outputUnit?->name ?? 'pcs' }}</span>
            </span>
            <span class="text-[11px] mt-1 block {{ (float)$productionOrder->rejected_quantity > 0 ? 'text-[#FF3B30] font-medium' : 'text-black/40 dark:text-white/40' }}">
                {{ __('production.highlight_reject_yield', ['rejected' => number_format((float) $productionOrder->rejected_quantity, 0, ',', '.'), 'yield' => $productionOrder->yield_percentage]) }}
            </span>
        </div>

        <!-- HPP Pabrik Final per Unit -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">{{ __('production.highlight_final_hpp') }}</span>
            <span class="text-2xl font-bold tabular-nums text-[#007AFF] mt-1 block">
                {{ $business->currency_symbol }} {{ number_format((float) $productionOrder->unit_cost_hpp, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-black/40 dark:text-white/40 mt-1 block">
                {{ __('production.highlight_selling_price', ['price' => $business->currency_symbol . ' ' . number_format((float) ($productionOrder->product?->selling_price ?? 0), 0, ',', '.')]) }}
            </span>
        </div>

        <!-- Total Biaya Batch -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">{{ __('production.highlight_total_batch_cost') }}</span>
            <span class="text-2xl font-bold tabular-nums text-black dark:text-white mt-1 block">
                {{ $business->currency_symbol }} {{ number_format((float) $productionOrder->total_production_cost, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-black/40 dark:text-white/40 mt-1 block">
                {{ __('production.highlight_cost_breakdown', ['material' => number_format((float) $productionOrder->total_material_cost, 0, ',', '.'), 'labor' => number_format((float) $productionOrder->total_labor_cost, 0, ',', '.')]) }}
            </span>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. RINCIAN LOKASI GUDANG & BATCH                      -->
    <!-- ===================================================== -->
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
        <h3 class="text-[14px] font-bold text-black dark:text-white mb-3">{{ __('production.traceability_title') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-[12.5px]">
            <div>
                <span class="text-black/45 dark:text-white/45 block text-[11px]">{{ __('production.traceability_source') }}</span>
                <span class="font-semibold text-black dark:text-white">{{ $productionOrder->sourceLocation?->name ?? 'Gudang Utama' }}</span>
            </div>
            <div>
                <span class="text-black/45 dark:text-white/45 block text-[11px]">{{ __('production.traceability_target') }}</span>
                <span class="font-semibold text-[#007AFF]">{{ $productionOrder->targetLocation?->name ?? 'Gudang Utama' }}</span>
            </div>
            <div>
                <span class="text-black/45 dark:text-white/45 block text-[11px]">{{ __('production.traceability_batch') }}</span>
                <span class="font-mono font-semibold text-black dark:text-white">{{ $productionOrder->batch_number ?: '-' }}</span>
            </div>
            <div>
                <span class="text-black/45 dark:text-white/45 block text-[11px]">{{ __('production.traceability_expiry') }}</span>
                <span class="font-medium text-black dark:text-white">{{ $productionOrder->expiry_date ? \Carbon\Carbon::parse($productionOrder->expiry_date)->translatedFormat('d M Y') : '-' }}</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. TABLE KEBUTUHAN & PENGELUARAN BAHAN BAKU           -->
    <!-- ===================================================== -->
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
        <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('production.table_materials_allocation') }}</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('production.table_materials_allocation_sub') }}</p>
            </div>
            <span class="text-[12px] font-bold text-[#007AFF] tabular-nums">
                {{ __('production.count_materials', ['count' => $productionOrder->items->count()]) }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] min-w-[750px]">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">{{ __('production.col_mat_name') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('production.col_mat_planned') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('production.col_mat_actual') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('production.col_mat_purchase_price') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('production.col_mat_total') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                    @foreach($productionOrder->items as $item)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-black dark:text-white">
                                    {{ $item->material?->name ?? __('production.material_deleted') }}
                                </div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 font-mono">
                                    {{ $item->material?->code ?? '-' }}
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-right tabular-nums font-semibold text-black dark:text-white">
                                {{ number_format((float) $item->planned_quantity, 2, ',', '.') }}
                                <span class="text-[11px] font-normal text-black/45 dark:text-white/45">{{ $item->unit?->name ?? $item->material?->unit?->name ?? 'Unit' }}</span>
                            </td>

                            <td class="py-3.5 px-4 text-right tabular-nums font-bold">
                                @if((float) $item->actual_consumed_quantity > 0)
                                    <span class="text-[#FF9500]">
                                        {{ number_format((float) $item->actual_consumed_quantity, 2, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-black/30 dark:text-white/30 font-normal">{{ __('production.material_not_released') }}</span>
                                @endif
                                <span class="text-[11px] font-normal text-black/45 dark:text-white/45">{{ $item->unit?->name ?? $item->material?->unit?->name ?? 'Unit' }}</span>
                            </td>

                            <td class="py-3.5 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                                {{ $business->currency_symbol }} {{ number_format((float) $item->unit_cost, 0, ',', '.') }}
                            </td>

                            <td class="py-3.5 px-4 text-right tabular-nums font-semibold text-black dark:text-white">
                                {{ $business->currency_symbol }} {{ number_format((float) $item->total_cost, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL 1: ISSUE MATERIALS (RILIS BAHAN BAKU KE WIP)    -->
    <!-- ===================================================== -->
    <div x-show="showIssueModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="showIssueModal = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('production.modal_issue_title') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('production.modal_issue_desc') }}</p>
                    </div>
                </div>

                <p class="text-[13px] text-black/70 dark:text-white/70 leading-relaxed">
                    {{ __('production.modal_issue_warning', ['warehouse' => $productionOrder->sourceLocation?->name]) }}
                </p>

                <form method="POST" action="{{ route('production.issue-materials', $productionOrder) }}" class="space-y-4 pt-2">
                    @csrf
                    <div class="flex items-center justify-end gap-2.5">
                        <button type="button" @click="showIssueModal = false" class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.05]">
                            {{ __('production.btn_cancel') }}
                        </button>
                        <button type="submit" class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF9500] hover:bg-[#E08500] shadow-md shadow-[#FF9500]/25">
                            {{ __('production.modal_issue_btn_confirm') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL 2: COMPLETE PRODUCTION (CATAT HASIL YIELD)      -->
    <!-- ===================================================== -->
    <div x-show="showCompleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="showCompleteModal = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('production.modal_complete_title') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('production.modal_complete_desc') }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('production.complete', $productionOrder) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1">
                            {{ __('production.modal_complete_label_actual') }} <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="number" step="any" min="0" name="actual_quantity" x-model="actualQty" required
                            class="w-full h-11 px-3.5 rounded-[12px] text-[15px] font-bold tabular-nums bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                        <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">
                            {!! __('production.modal_complete_hint_stock', ['warehouse' => '<strong>' . e($productionOrder->targetLocation?->name) . '</strong>']) !!}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                                {{ __('production.modal_complete_label_rejected') }}
                            </label>
                            <input type="number" step="any" min="0" name="rejected_quantity" x-model="rejectedQty"
                                class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                        </div>

                        <div>
                            <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                                {{ __('production.modal_complete_label_batch') }}
                            </label>
                            <input type="text" name="batch_number" value="{{ $productionOrder->batch_number ?: 'BATCH-' . now()->format('Ymd') }}"
                                class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                        </div>
                    </div>

                    <div class="p-3.5 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 text-[12.5px]">
                        <span class="text-black/60 dark:text-white/60 block">{{ __('production.modal_complete_banner_hpp') }}</span>
                        <span class="text-lg font-bold text-[#34C759] tabular-nums" x-text="formatRupiah(hppPerUnit) + ' / unit'"></span>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="updateHpp" name="update_product_base_cost" value="1" checked
                            class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                        <label for="updateHpp" class="text-[12px] text-black/80 dark:text-white/80 select-none">
                            {{ __('production.modal_complete_checkbox_update') }}
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" @click="showCompleteModal = false" class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.05]">
                            {{ __('production.btn_cancel') }}
                        </button>
                        <button type="submit" class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#30B34E] shadow-md shadow-[#34C759]/25">
                            {{ __('production.modal_complete_btn_submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL 3: CANCEL SPKP                                  -->
    <!-- ===================================================== -->
    <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="showCancelModal = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center shrink-0">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('production.modal_cancel_title') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('production.modal_cancel_desc') }}</p>
                    </div>
                </div>

                <p class="text-[13px] text-black/70 dark:text-white/70 leading-relaxed">
                    {{ __('production.modal_cancel_warning') }}
                </p>

                <form method="POST" action="{{ route('production.cancel', $productionOrder) }}" class="space-y-4 pt-1">
                    @csrf
                    <div class="flex items-center justify-end gap-2.5">
                        <button type="button" @click="showCancelModal = false" class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.05]">
                            {{ __('production.modal_cancel_btn_no') }}
                        </button>
                        <button type="submit" class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF3B30] hover:bg-[#D93025]">
                            {{ __('production.modal_cancel_btn_confirm') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
