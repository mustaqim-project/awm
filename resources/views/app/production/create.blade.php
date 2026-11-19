@extends('layouts.app', [
    'title' => __('production.page_title_create'),
])

@section('content')
<div class="max-w-[1000px] mx-auto w-full px-4 sm:px-6 space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="{
    products: {{ Js::from($products->map(function ($p) {
        $costModel = $p->activeCostModel ?? $p->costModels->first();
        $bomHeader = $costModel?->bomHeaders->first();
        return [
            'id' => (string) $p->id,
            'name' => $p->name,
            'code' => $p->code,
            'unit' => $p->outputUnit?->name ?? 'pcs',
            'has_bom' => $bomHeader !== null,
            'bom_id' => $bomHeader?->id,
            'bom_name' => $bomHeader?->name,
            'batch_output' => (float) ($bomHeader?->output_quantity ?? 1.0),
            'bom_items' => $bomHeader ? $bomHeader->items->map(fn($item) => [
                'material_id' => (string) $item->material_id,
                'name' => $item->material?->name ?? 'Bahan',
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit?->name ?? $item->material?->unit?->name ?? 'Unit',
                'waste_pct' => (float) ($item->waste_percentage ?? 0),
                'cost' => (float) ($item->material?->latestPrice?->purchase_price ?? $item->material?->price ?? 0),
            ])->values()->all() : [],
        ];
    })) }},
    selectedProductId: '{{ old('product_id', $preselectedProductId ?? ($products->first()?->id ?? '')) }}',
    plannedQuantity: {{ old('planned_quantity', 100) }},
    laborCost: {{ old('total_labor_cost', 0) }},
    machineCost: {{ old('total_machine_cost', 0) }},

    get selectedProduct() {
        return this.products.find(p => p.id === this.selectedProductId) || null;
    },

    get calculatedItems() {
        const prod = this.selectedProduct;
        if (!prod || !prod.bom_items || !prod.bom_items.length) return [];
        const mult = Math.max(0.0001, parseFloat(this.plannedQuantity) || 0) / Math.max(0.0001, prod.batch_output);
        return prod.bom_items.map(item => {
            const wasteMult = 1.0 + (item.waste_pct / 100.0);
            const neededQty = item.quantity * mult * wasteMult;
            const subtotalCost = neededQty * item.cost;
            return {
                name: item.name,
                unit: item.unit,
                neededQty: neededQty,
                unitCost: item.cost,
                subtotalCost: subtotalCost
            };
        });
    },

    get estimatedMaterialCost() {
        return this.calculatedItems.reduce((acc, it) => acc + it.subtotalCost, 0);
    },

    get totalEstimatedCost() {
        return this.estimatedMaterialCost + (parseFloat(this.laborCost) || 0) + (parseFloat(this.machineCost) || 0);
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
    <!-- 1. HEADER                                             -->
    <!-- ===================================================== -->
    <x-module-header
        :title="__('production.title_create')"
        :subtitle="__('production.subtitle_create')"
        :breadcrumbs="[
            ['label' => __('common.dashboard'), 'url' => route('dashboard')],
            ['label' => __('production.nav_breadcrumb_production'), 'url' => route('production.index')],
            ['label' => __('production.nav_breadcrumb_new'), 'url' => null],
        ]">
        <a href="{{ route('production.index') }}"
            class="min-h-[44px] sm:min-h-0 h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shrink-0">
            <i data-lucide="arrow-left" class="w-4 h-4 text-black/60 dark:text-white/60 shrink-0"></i>
            <span>{{ __('production.btn_back') }}</span>
        </a>
    </x-module-header>

    <!-- ===================================================== -->
    <!-- 2. FORM BODY                                          -->
    <!-- ===================================================== -->
    <form method="POST" action="{{ route('production.store') }}" class="space-y-6">
        @csrf

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

        <!-- Card 1: Produk & Kuantitas -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 space-y-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center gap-2.5 pb-2 border-b border-black/[0.06] dark:border-white/[0.08]">
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="package" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('production.section_target_product_title') }}</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('production.section_target_product_desc') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Product Selector -->
                <div>
                    <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('production.label_target_product') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <x-searchable-select>
                        <select name="product_id" x-model="selectedProductId" required
                        class="sr-only">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->code ?? '-' }})</option>
                        @endforeach
                        </select>
                    </x-searchable-select>

                    <template x-if="selectedProduct && !selectedProduct.has_bom">
                        <p class="text-[11.5px] text-[#FF9500] mt-1.5 flex items-center gap-1 font-medium">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>{{ __('production.alert_no_bom') }}</span>
                        </p>
                    </template>
                </div>

                <!-- Planned Quantity -->
                <div>
                    <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('production.label_planned_quantity') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" step="any" min="0.0001" name="planned_quantity" x-model="plannedQuantity" required
                            class="w-full h-11 pl-4 pr-16 rounded-[12px] text-[14px] font-bold tabular-nums bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 focus:border-[#007AFF]">
                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[12px] font-medium text-black/40 dark:text-white/40" x-text="selectedProduct ? selectedProduct.unit : 'pcs'"></span>
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">
                        {{ __('production.label_recipe_batch_basis') }} <span class="font-semibold text-black dark:text-white" x-text="(selectedProduct ? selectedProduct.batch_output : 1) + ' ' + (selectedProduct ? selectedProduct.unit : 'pcs')"></span>
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
                    <x-searchable-select>
                        <select name="source_location_id" required
                        class="sr-only">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('source_location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ strtoupper($loc->type ?? 'Gudang') }})
                            </option>
                        @endforeach
                        </select>
                    </x-searchable-select>
                </div>

                <!-- Target Location (Barang Jadi) -->
                <div>
                    <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('production.label_target_location') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <x-searchable-select>
                        <select name="target_location_id" required
                        class="sr-only">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('target_location_id') == $loc->id ? 'selected' : '' }}>
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
                    <input type="date" name="planned_start_date" value="{{ old('planned_start_date', now()->toDateString()) }}" required
                        class="w-full h-11 px-3.5 rounded-[12px] text-[13px] font-medium bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20">
                </div>
            </div>

            <!-- Optional Batch Number & Expiry Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('production.label_batch_number_optional') }}
                    </label>
                    <input type="text" name="batch_number" value="{{ old('batch_number', 'BATCH-' . now()->format('Ymd')) }}"
                        placeholder="{{ __('production.placeholder_batch_number') }}"
                        class="w-full h-11 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20">
                </div>

                <div>
                    <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('production.label_expiry_date_optional') }}
                    </label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                        class="w-full h-11 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20">
                </div>
            </div>
        </div>

        <!-- Card 3: Dynamic Breakdown Kebutuhan Bahan Baku (Live Preview) -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 space-y-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between pb-2 border-b border-black/[0.06] dark:border-white/[0.08]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                        <i data-lucide="boxes" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('production.section_materials_simulation_title') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('production.section_materials_simulation_desc') }}</p>
                    </div>
                </div>

                <div class="text-right">
                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">{{ __('production.label_estimated_material_cost') }}</span>
                    <div class="text-[15px] font-bold text-black dark:text-white tabular-nums" x-text="formatRupiah(estimatedMaterialCost)"></div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[12.5px]">
                    <thead class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase border-b border-black/[0.06] dark:border-white/[0.08]">
                        <tr>
                            <th class="py-2.5 px-3">{{ __('production.col_material_name') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('production.col_net_need_waste') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('production.col_unit_cost') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('production.col_subtotal_cost') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                        <template x-for="(item, idx) in calculatedItems" :key="idx">
                            <tr>
                                <td class="py-2.5 px-3 font-medium text-black dark:text-white" x-text="item.name"></td>
                                <td class="py-2.5 px-3 text-right tabular-nums font-semibold text-black dark:text-white">
                                    <span x-text="formatNumber(item.neededQty)"></span>
                                    <span class="text-[11px] text-black/45 dark:text-white/45 font-normal" x-text="item.unit"></span>
                                </td>
                                <td class="py-2.5 px-3 text-right tabular-nums text-black/70 dark:text-white/70" x-text="formatRupiah(item.unitCost)"></td>
                                <td class="py-2.5 px-3 text-right tabular-nums font-semibold text-black dark:text-white" x-text="formatRupiah(item.subtotalCost)"></td>
                            </tr>
                        </template>
                        <template x-if="!calculatedItems.length">
                            <tr>
                                <td colspan="4" class="py-6 text-center text-black/40 dark:text-white/40 text-[12px]">
                                    {{ __('production.empty_bom_simulation') }}
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Additional Labor & Machine Cost Inputs -->
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
                    <span class="text-xl font-bold text-[#007AFF] tabular-nums" x-text="formatRupiah(estimatedHppPerUnit) + ' / ' + (selectedProduct ? selectedProduct.unit : 'pcs')"></span>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-black/50 dark:text-white/50 text-[11.5px] block">{{ __('production.banner_total_estimated_budget') }}</span>
                    <span class="text-lg font-bold text-black dark:text-white tabular-nums" x-text="formatRupiah(totalEstimatedCost)"></span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <label class="block text-[12.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                {{ __('production.label_notes_optional') }}
            </label>
            <textarea name="notes" rows="2"
                placeholder="{{ __('production.placeholder_notes') }}"
                class="w-full p-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20"></textarea>
        </div>

        <!-- Submit Button Toolbar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('production.index') }}"
                class="min-h-[44px] h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.05] transition flex items-center justify-center">
                {{ __('production.btn_cancel') }}
            </a>
            <button type="submit"
                class="min-h-[44px] h-11 px-6 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>{{ __('production.btn_save_draft') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection
