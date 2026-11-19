@extends('layouts.app', [
    'title' => __('production.page_title_edit', ['order_number' => $productionOrder->order_number]),
])

@section('content')
<div class="max-w-[1100px] mx-auto w-full px-4 sm:px-6 space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="{
    isDraft: {{ $productionOrder->isDraft() ? 'true' : 'false' }},
    isInProgress: {{ $productionOrder->isInProgress() ? 'true' : 'false' }},
    plannedQuantity: {{ old('planned_quantity', (float) $productionOrder->planned_quantity) }},
    laborCost: {{ old('total_labor_cost', (float) $productionOrder->total_labor_cost) }},
    machineCost: {{ old('total_machine_cost', (float) $productionOrder->total_machine_cost) }},
    items: {{ Js::from($productionOrder->items->map(function ($it) {
        return [
            'id' => (string) $it->id,
            'name' => $it->material?->name ?? 'Bahan Baku',
            'code' => $it->material?->code ?? '-',
            'unit' => $it->unit?->name ?? $it->material?->unit?->name ?? 'Unit',
            'planned_quantity' => (float) $it->planned_quantity,
            'actual_consumed_quantity' => (float) $it->actual_consumed_quantity,
            'unit_cost' => (float) $it->unit_cost,
            'notes' => $it->notes ?? '',
        ];
    })->values()) }},

    get totalMaterialCost() {
        if (this.isInProgress) {
            return this.items.reduce((acc, it) => {
                const qty = parseFloat(it.actual_consumed_quantity) || 0;
                return acc + (qty * it.unit_cost);
            }, 0);
        }
        return this.items.reduce((acc, it) => {
            const qty = parseFloat(it.planned_quantity) || 0;
            return acc + (qty * it.unit_cost);
        }, 0);
    },

    get totalEstimatedCost() {
        return this.totalMaterialCost + (parseFloat(this.laborCost) || 0) + (parseFloat(this.machineCost) || 0);
    },

    get estimatedHppPerUnit() {
        const qty = parseFloat(this.plannedQuantity) || 0;
        return qty > 0 ? (this.totalEstimatedCost / qty) : 0;
    },

    formatNumber(num) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(num || 0);
    },

    formatRupiah(num) {
        return '{{ $business->currency_symbol }} ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(num || 0));
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER                              -->
    <!-- ===================================================== -->
    <x-module-header
        :title="__('production.title_edit')"
        :subtitle="__('production.subtitle_edit')"
        :breadcrumbs="[
            ['label' => __('common.dashboard'), 'url' => route('dashboard')],
            ['label' => __('production.nav_breadcrumb_production'), 'url' => route('production.index')],
            ['label' => $productionOrder->order_number, 'url' => route('production.show', $productionOrder)],
            ['label' => __('production.btn_edit'), 'url' => null],
        ]">
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('production.show', $productionOrder) }}"
                class="min-h-[44px] sm:min-h-0 h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition flex items-center justify-center gap-1.5 shrink-0">
                <i data-lucide="arrow-left" class="w-4 h-4 text-black/60 dark:text-white/60 shrink-0"></i>
                <span>{{ __('production.btn_back') }}</span>
            </a>

            <a href="{{ route('production.print', $productionOrder) }}" target="_blank"
                class="min-h-[44px] sm:min-h-0 h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition flex items-center justify-center gap-1.5 shrink-0">
                <i data-lucide="printer" class="w-4 h-4 text-black/60 dark:text-white/60 shrink-0"></i>
                <span>{{ __('production.btn_print') }}</span>
            </a>
        </div>
    </x-module-header>

    <!-- ===================================================== -->
    <!-- 2. FORM EDIT PRODUCTION ORDER                         -->
    <!-- ===================================================== -->
    <form method="POST" action="{{ route('production.update', $productionOrder) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @if($errors->any())
            <div class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[13px] space-y-1">
                <div class="font-bold flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span>{{ __('production.validation_errors_heading') }}</span>
                </div>
                <ul class="list-disc pl-5 text-[12px]">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card 1: Produk Target & Target Kuantitas -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 space-y-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between pb-2 border-b border-black/[0.06] dark:border-white/[0.08]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="package" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('production.section_target_product_title') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">
                            {{ $productionOrder->isDraft() ? __('production.section_target_product_desc') : __('production.section_target_product_edit_hint') }}
                        </p>
                    </div>
                </div>

                <!-- Status Badge -->
                <div>
                    @if($productionOrder->isDraft())
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-black/40 dark:bg-white/40"></span>
                            {{ __('production.status_draft') }}
                        </span>
                    @elseif($productionOrder->isInProgress())
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#FF9500]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] animate-pulse"></span>
                            {{ __('production.status_in_progress') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Product Information -->
                <div>
                    <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('production.label_target_product') }}
                    </label>
                    <div class="p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] flex items-center justify-between">
                        <div>
                            <span class="font-bold text-black dark:text-white text-[13.5px] block">
                                {{ $productionOrder->product?->name ?? __('production.product_deleted') }}
                            </span>
                            <span class="text-[11px] text-black/45 dark:text-white/45 font-mono">
                                SKU: {{ $productionOrder->product?->code ?? '-' }}
                            </span>
                        </div>
                        <span class="text-[11.5px] font-medium text-black/50 dark:text-white/50 bg-black/[0.04] dark:bg-white/[0.08] px-2 py-0.5 rounded-[8px]">
                            {{ $productionOrder->product?->outputUnit?->name ?? 'pcs' }}
                        </span>
                    </div>
                    <input type="hidden" name="product_id" value="{{ $productionOrder->product_id }}">
                </div>

                <!-- Planned Quantity -->
                <div>
                    <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('production.label_planned_quantity') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" step="any" min="0.0001" name="planned_quantity" x-model="plannedQuantity" required
                            {{ $productionOrder->isInProgress() ? 'readonly' : '' }}
                            class="w-full h-11 pl-4 pr-16 rounded-[12px] text-[14px] font-bold tabular-nums {{ $productionOrder->isInProgress() ? 'bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60 cursor-not-allowed' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black dark:text-white' }} border border-black/[0.08] dark:border-white/[0.1] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 focus:border-[#007AFF]">
                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[12px] font-medium text-black/40 dark:text-white/40">
                            {{ $productionOrder->product?->outputUnit?->name ?? 'pcs' }}
                        </span>
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">
                        @if($productionOrder->isInProgress())
                            <span>Kuantitas target terkunci karena bahan telah dirilis ke lantai pabrik.</span>
                        @else
                            {{ __('production.label_recipe_batch_basis') }} <span class="font-semibold text-black dark:text-white">{{ $productionOrder->bomHeader?->output_quantity ?? 1 }} {{ $productionOrder->product?->outputUnit?->name ?? 'pcs' }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 2: Routing Gudang & Tanggal -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 space-y-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center gap-2.5 pb-2 border-b border-black/[0.06] dark:border-white/[0.08]">
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                    <i data-lucide="warehouse" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('production.section_routing_title') }}</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('production.section_routing_desc') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Source Location (Bahan Baku) -->
                <div>
                    <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('production.label_source_location') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    @if($productionOrder->isDraft())
                        <x-searchable-select>
                            <select name="source_location_id" required class="sr-only">
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('source_location_id', $productionOrder->source_location_id) == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }} ({{ strtoupper($loc->type ?? 'Gudang') }})
                                    </option>
                                @endforeach
                            </select>
                        </x-searchable-select>
                    @else
                        <div class="h-11 px-3.5 rounded-[12px] bg-black/[0.06] dark:bg-white/[0.08] border border-black/[0.08] dark:border-white/[0.1] text-black/60 dark:text-white/60 flex items-center text-[13px] font-medium">
                            {{ $productionOrder->sourceLocation?->name ?? 'Gudang Utama' }}
                        </div>
                        <input type="hidden" name="source_location_id" value="{{ $productionOrder->source_location_id }}">
                    @endif
                </div>

                <!-- Target Location (Barang Jadi) -->
                <div>
                    <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('production.label_target_location') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <x-searchable-select>
                        <select name="target_location_id" required class="sr-only">
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('target_location_id', $productionOrder->target_location_id) == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }} ({{ strtoupper($loc->type ?? 'Gudang') }})
                                </option>
                            @endforeach
                        </select>
                    </x-searchable-select>
                </div>

                <!-- Planned Start Date -->
                <div>
                    <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('production.label_planned_start_date') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="planned_start_date"
                        value="{{ old('planned_start_date', $productionOrder->planned_start_date ? \Carbon\Carbon::parse($productionOrder->planned_start_date)->toDateString() : now()->toDateString()) }}"
                        required
                        class="w-full h-11 px-3.5 rounded-[12px] text-[13px] font-medium bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20">
                </div>
            </div>

            <!-- Optional Batch Number & Expiry Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div>
                    <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('production.label_batch_number_optional') }}
                    </label>
                    <input type="text" name="batch_number"
                        value="{{ old('batch_number', $productionOrder->batch_number) }}"
                        placeholder="{{ __('production.placeholder_batch_number') }}"
                        class="w-full h-11 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20">
                </div>

                <div>
                    <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('production.label_expiry_date_optional') }}
                    </label>
                    <input type="date" name="expiry_date"
                        value="{{ old('expiry_date', $productionOrder->expiry_date ? \Carbon\Carbon::parse($productionOrder->expiry_date)->toDateString() : '') }}"
                        class="w-full h-11 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20">
                </div>
            </div>
        </div>

        <!-- Card 3: Rincian Alokasi Bahan Baku & Pengeluaran Aktual (WIP) -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 space-y-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between pb-2 border-b border-black/[0.06] dark:border-white/[0.08]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                        <i data-lucide="boxes" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('production.section_materials_edit_title') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('production.section_materials_edit_desc') }}</p>
                    </div>
                </div>

                <div class="text-right">
                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">{{ __('production.label_estimated_material_cost') }}</span>
                    <div class="text-[15px] font-bold text-black dark:text-white tabular-nums" x-text="formatRupiah(totalMaterialCost)"></div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[12.5px] min-w-[700px]">
                    <thead class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase border-b border-black/[0.06] dark:border-white/[0.08]">
                        <tr>
                            <th class="py-2.5 px-3">{{ __('production.col_mat_name') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('production.col_mat_planned') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('production.col_mat_actual') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('production.col_mat_purchase_price') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('production.col_mat_total') }}</th>
                            <th class="py-2.5 px-3">{{ __('production.print_table_notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                        <template x-for="(item, idx) in items" :key="item.id">
                            <tr>
                                <!-- Hidden Item ID -->
                                <input type="hidden" :name="'items[' + idx + '][id]'" :value="item.id">

                                <!-- Nama Bahan Baku -->
                                <td class="py-2.5 px-3">
                                    <div class="font-medium text-black dark:text-white" x-text="item.name"></div>
                                    <div class="text-[10.5px] text-black/45 dark:text-white/45 font-mono" x-text="item.code"></div>
                                </td>

                                <!-- Rencana Standar (+Waste) -->
                                <td class="py-2.5 px-3 text-right">
                                    <template x-if="isDraft">
                                        <div class="inline-flex items-center gap-1 justify-end">
                                            <input type="number" step="any" min="0"
                                                :name="'items[' + idx + '][planned_quantity]'"
                                                x-model="item.planned_quantity"
                                                class="w-24 h-9 px-2.5 text-right rounded-[8px] text-[13px] font-bold tabular-nums bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            <span class="text-[11px] text-black/50 dark:text-white/50 w-8 text-left" x-text="item.unit"></span>
                                        </div>
                                    </template>
                                    <template x-if="!isDraft">
                                        <div class="tabular-nums font-semibold text-black dark:text-white">
                                            <span x-text="formatNumber(item.planned_quantity)"></span>
                                            <span class="text-[11px] font-normal text-black/45 dark:text-white/45" x-text="item.unit"></span>
                                        </div>
                                    </template>
                                </td>

                                <!-- Pengeluaran Aktual (WIP) -->
                                <td class="py-2.5 px-3 text-right">
                                    <template x-if="isInProgress">
                                        <div class="inline-flex items-center gap-1 justify-end">
                                            <input type="number" step="any" min="0"
                                                :name="'items[' + idx + '][actual_consumed_quantity]'"
                                                x-model="item.actual_consumed_quantity"
                                                class="w-24 h-9 px-2.5 text-right rounded-[8px] text-[13px] font-bold tabular-nums bg-[#FF9500]/10 border border-[#FF9500]/30 text-[#FF9500] focus:outline-none focus:ring-1 focus:ring-[#FF9500]">
                                            <span class="text-[11px] text-black/50 dark:text-white/50 w-8 text-left" x-text="item.unit"></span>
                                        </div>
                                    </template>
                                    <template x-if="!isInProgress">
                                        <span class="text-black/30 dark:text-white/30 text-[11.5px]">{{ __('production.material_not_released') }}</span>
                                    </template>
                                </td>

                                <!-- Harga Satuan -->
                                <td class="py-2.5 px-3 text-right tabular-nums text-black/70 dark:text-white/70" x-text="formatRupiah(item.unit_cost)"></td>

                                <!-- Subtotal -->
                                <td class="py-2.5 px-3 text-right tabular-nums font-semibold text-black dark:text-white"
                                    x-text="formatRupiah((isInProgress ? (parseFloat(item.actual_consumed_quantity) || 0) : (parseFloat(item.planned_quantity) || 0)) * item.unit_cost)">
                                </td>

                                <!-- Notes -->
                                <td class="py-2.5 px-3">
                                    <input type="text" :name="'items[' + idx + '][notes]'" x-model="item.notes"
                                        placeholder="Keterangan..."
                                        class="w-full h-8 px-2 text-[12px] rounded-[8px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] text-black dark:text-white">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Labor & Machine Overhead Costs -->
            <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        {{ __('production.label_labor_cost') }}
                    </label>
                    <input type="number" step="any" min="0" name="total_labor_cost" x-model="laborCost"
                        class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        {{ __('production.label_machine_cost') }}
                    </label>
                    <input type="number" step="any" min="0" name="total_machine_cost" x-model="machineCost"
                        class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                </div>
            </div>

            <!-- Grand Summary Bento Banner -->
            <div class="p-4 rounded-[16px] bg-[#007AFF]/[0.05] dark:bg-[#007AFF]/[0.1] border border-[#007AFF]/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-[13px]">
                <div>
                    <span class="text-black/50 dark:text-white/50 text-[11.5px] block">{{ __('production.banner_estimated_hpp') }}</span>
                    <span class="text-xl font-bold text-[#007AFF] tabular-nums" x-text="formatRupiah(estimatedHppPerUnit) + ' / ' + '{{ $productionOrder->product?->outputUnit?->name ?? 'pcs' }}'"></span>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-black/50 dark:text-white/50 text-[11.5px] block">{{ __('production.banner_total_estimated_budget') }}</span>
                    <span class="text-lg font-bold text-black dark:text-white tabular-nums" x-text="formatRupiah(totalEstimatedCost)"></span>
                </div>
            </div>
        </div>

        <!-- Card 4: Catatan / Instruksi Kerja -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                {{ __('production.label_notes_optional') }}
            </label>
            <textarea name="notes" rows="2"
                placeholder="{{ __('production.placeholder_notes') }}"
                class="w-full p-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20">{{ old('notes', $productionOrder->notes) }}</textarea>
        </div>

        <!-- Submit Button Toolbar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('production.show', $productionOrder) }}"
                class="min-h-[44px] h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.05] transition flex items-center justify-center">
                {{ __('production.btn_cancel') }}
            </a>
            <button type="submit"
                class="min-h-[44px] h-11 px-6 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>{{ __('production.btn_save_changes') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection
