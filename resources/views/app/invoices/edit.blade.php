@extends('layouts.app', [
    'title' => __('sales.invoices.edit_title', ['number' => $invoice->invoice_number]),
    'headerTitle' => __('sales.invoices.edit_header_title'),
    'headerSubtitle' => __('sales.invoices.edit_header_subtitle')
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
    customerId: '{{ $invoice->customer_id }}',
    paymentTerms: '{{ addslashes($invoice->payment_terms ?? 'Net 30') }}',
    invoiceDate: '{{ $invoice->invoice_date?->format('Y-m-d') ?? date('Y-m-d') }}',
    dueDate: '{{ $invoice->due_date?->format('Y-m-d') ?? date('Y-m-d', strtotime('+30 days')) }}',
    senderName: '{{ addslashes(old('sender_name', $invoice->sender_name ?: ($invoice->creator?->name ?: $business->name))) }}',
    recipientName: '{{ addslashes(old('recipient_name', $invoice->recipient_name ?: ($invoice->customer?->name ?? ''))) }}',
    
    items: [
        @forelse($invoice->items as $it)
        {
            product_id: '{{ $it->product_id ?? '' }}',
            item_name: '{{ addslashes($it->item_name) }}',
            sku: '{{ addslashes($it->sku ?? '') }}',
            description: '{{ addslashes($it->description ?? '') }}',
            quantity: {{ (float) $it->quantity }},
            unit_id: '{{ $it->unit_id }}',
            unit_price: {{ (float) $it->unit_price }},
            unit_hpp: {{ (float) $it->unit_hpp }}
        },
        @empty
        {
            product_id: '',
            item_name: '',
            sku: '',
            description: '',
            quantity: 1,
            unit_id: '{{ $units->first()?->id }}',
            unit_price: 0,
            unit_hpp: 0
        }
        @endforelse
    ],

    discountType: '{{ $invoice->discount_type ?? 'fixed' }}',
    discountValue: {{ (float) ($invoice->discount_value ?? 0) }},
    taxPercentage: {{ (float) ($invoice->tax_percentage ?? 0) }},
    shippingCost: {{ (float) ($invoice->shipping_cost ?? 0) }},

    customers: {{ Js::from($customers) }},
    products: {{ Js::from($products) }},
    units: {{ Js::from($units) }},

    onCustomerChange() {
        const cust = this.customers.find(c => c.id === this.customerId);
        if (cust) {
            if (!this.recipientName || this.recipientName === '') {
                this.recipientName = cust.name || cust.company_name || '';
            }
            if (cust.payment_terms_days) {
                const days = parseInt(cust.payment_terms_days) || 30;
                this.paymentTerms = 'Net ' + days;
                const d = new Date(this.invoiceDate || new Date());
                d.setDate(d.getDate() + days);
                this.dueDate = d.toISOString().split('T')[0];
            }
        }
    },

    addItem() {
        this.items.push({
            product_id: '',
            item_name: '',
            sku: '',
            description: '',
            quantity: 1,
            unit_id: this.units.length > 0 ? this.units[0].id : '',
            unit_price: 0,
            unit_hpp: 0
        });
    },

    removeItem(index) {
        if (this.items.length > 1) {
            this.items.splice(index, 1);
        }
    },

    onProductChange(index) {
        const pId = this.items[index].product_id;
        const prod = this.products.find(p => p.id === pId);
        if (prod) {
            this.items[index].item_name = prod.name;
            this.items[index].sku = prod.code || prod.sku || '';
            this.items[index].unit_id = prod.output_unit_id;
            this.items[index].unit_price = parseFloat(prod.selling_price) || (parseFloat(prod.base_cost) > 0 ? parseFloat(prod.base_cost) * 1.4 : 0);
            this.items[index].unit_hpp = parseFloat(prod.base_cost) || 0;
        }
    },

    get subtotal() {
        return this.items.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0);
    },

    get totalHppCost() {
        return this.items.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_hpp) || 0)), 0);
    },

    get discountAmount() {
        if (this.discountType === 'percentage') {
            return (parseFloat(this.discountValue) || 0) / 100 * this.subtotal;
        }
        return Math.min(parseFloat(this.discountValue) || 0, this.subtotal);
    },

    get taxAmount() {
        const taxable = Math.max(0, this.subtotal - this.discountAmount);
        return (parseFloat(this.taxPercentage) || 0) / 100 * taxable;
    },

    get grandTotal() {
        return Math.max(0, this.subtotal - this.discountAmount) + this.taxAmount + (parseFloat(this.shippingCost) || 0);
    },

    get totalGrossProfit() {
        return this.subtotal - this.totalHppCost;
    },

    get grossMarginPercentage() {
        return this.subtotal > 0 ? (this.totalGrossProfit / this.subtotal) * 100 : 0;
    },

    formatCurrency(num) {
        return '{{ $business->currency_symbol }} ' + (new Intl.NumberFormat('id-ID').format(Math.round(num || 0)));
    }
}">

    <form method="POST" action="{{ route('invoices.update', $invoice->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR / STUDIO HEADER (macOS Window Pattern)      -->
        <!-- ===================================================== -->
        <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <!-- Minimal Breadcrumb -->
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.invoices.breadcrumb_dashboard') }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <a href="{{ route('invoices.index') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.invoices.breadcrumb_invoices') }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <a href="{{ route('invoices.show', $invoice->id) }}" class="hover:text-[#007AFF] transition-colors">{{ $invoice->invoice_number }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <span class="text-black dark:text-white font-medium">{{ __('sales.invoices.breadcrumb_edit') }}</span>
                </nav>
                <div class="flex items-center gap-2">
                    <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">{{ __('sales.invoices.edit_title', ['number' => $invoice->invoice_number]) }}</h1>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                        {{ __('sales.invoices.badge_draft') }}
                    </span>
                </div>
                <p class="text-[13px] text-black/50 dark:text-white/50">{{ __('sales.invoices.edit_subtitle') }}</p>
            </div>

            <!-- Toolbar Actions -->
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <a href="{{ route('invoices.show', $invoice->id) }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all flex items-center justify-center">
                    {{ __('sales.invoices.btn_cancel') }}
                </a>
                <button type="submit" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>{{ __('sales.invoices.btn_save_changes') }}</span>
                </button>
            </div>
        </header>

        <!-- ===================================================== -->
        <!-- 2. DOCUMENT INFO PANEL                                -->
        <!-- ===================================================== -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                <div>
                    <h2 class="text-[15px] font-semibold text-black dark:text-white">{{ __('sales.invoices.sec_client_info') }}</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('sales.invoices.edit_doc_desc') }}</p>
                </div>
                @if($invoice->purchaseOrder)
                <div class="text-[12px] text-black/50 dark:text-white/50">
                    {{ __('sales.invoices.po_reference') }} <span class="font-semibold text-black dark:text-white tabular-nums">{{ $invoice->purchaseOrder->po_number }}</span>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-[13px]">
                <!-- Customer Select -->
                <div class="sm:col-span-2">
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('sales.invoices.customer_select_label') }}</label>
                    <select name="customer_id" x-model="customerId" @change="onCustomerChange()" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">{{ __('sales.invoices.customer_select_placeholder') }}</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}" {{ $invoice->customer_id === $cust->id ? 'selected' : '' }}>
                                {{ $cust->name }} {{ $cust->company_name ? "({$cust->company_name})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Nama Pengirim -->
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('sales.invoices.sender_name_label') }}</label>
                    <input type="text" name="sender_name" x-model="senderName" placeholder="{{ $business->name }}" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <!-- Nama Penerima -->
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('sales.invoices.recipient_name_label') }}</label>
                    <input type="text" name="recipient_name" x-model="recipientName" placeholder="{{ __('sales.invoices.recipient_name_placeholder') }}" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('sales.invoices.invoice_date_label') }} *</label>
                    <input type="date" name="invoice_date" x-model="invoiceDate" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('sales.invoices.due_date_label') }} *</label>
                    <input type="date" name="due_date" x-model="dueDate" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('sales.invoices.payment_terms_col_label') }}</label>
                    <input type="text" name="payment_terms" x-model="paymentTerms" placeholder="Net 30 / COD" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <!-- Gudang / Lokasi Pengeluaran Barang -->
                @if($locations->isNotEmpty())
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        {{ __('sales.invoices.warehouse_location_label') }}
                    </label>
                    <select name="location_id" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">{{ __('sales.invoices.warehouse_auto_option') }}</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $invoice->location_id === $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} {{ $loc->type ? "({$loc->type})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 3. ITEMS TABLE (Dynamic Table / Dense List)           -->
        <!-- ===================================================== -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white">{{ __('sales.invoices.sec_items_title') }}</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('sales.invoices.edit_items_desc') }}</p>
                </div>

                <button type="button" @click="addItem()" class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>{{ __('sales.invoices.btn_add_item') }}</span>
                </button>
            </div>

            <div class="rounded-[12px] border border-black/5 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto sm:overflow-x-visible">
                    <table class="block sm:table w-full text-left text-[13px]">
                        <thead class="hidden sm:table-header-group">
                            <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] whitespace-nowrap">
                                <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-2/5">{{ __('sales.invoices.col_product_item') }}</th>
                                <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-24">{{ __('sales.invoices.col_unit') }}</th>
                                <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-20 text-right">{{ __('sales.invoices.col_qty') }}</th>
                                <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-32 text-right">{{ __('sales.invoices.col_selling_price') }}</th>
                                <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-[#FF9500] dark:text-[#FF9F0A] w-28 text-right">{{ __('sales.invoices.col_hpp') }}</th>
                                <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-32 text-right">{{ __('sales.invoices.col_subtotal') }}</th>
                                <th class="py-2.5 px-2 w-8 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="block sm:table-row-group divide-y-0 sm:divide-y divide-black/[0.04] dark:divide-white/[0.06] space-y-3 sm:space-y-0 p-3 sm:p-0">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="block sm:table-row p-3.5 sm:p-0 rounded-[12px] sm:rounded-none bg-black/[0.02] sm:bg-transparent dark:bg-white/[0.02] sm:dark:bg-transparent border sm:border-0 border-black/5 dark:border-white/10 space-y-3 sm:space-y-0 hover:bg-black/[0.03] sm:hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <td class="block sm:table-cell py-0 sm:py-2.5 px-0 sm:px-3">
                                        <div class="flex sm:hidden items-center justify-between mb-2">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50" x-text="'Item #' + (index + 1)"></span>
                                            <button type="button" @click="removeItem(index)" :disabled="items.length <= 1" class="h-6 px-2 rounded-[5px] text-[#FF3B30] bg-[#FF3B30]/10 text-[11px] font-medium disabled:opacity-30 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>{{ __('sales.invoices.btn_delete') }}</span>
                                            </button>
                                        </div>
                                        <span class="block sm:hidden text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 mb-1">{{ __('sales.invoices.select_product_label') }}</span>
                                        <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" @change="onProductChange(index)" 
                                                class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                            <option value="">{{ __('sales.invoices.product_select_placeholder') }}</option>
                                            <template x-for="prod in products" :key="prod.id">
                                                <option :value="prod.id" x-text="(prod.type === 'service' ? '[Jasa] ' : '') + prod.name + (prod.selling_price > 0 ? ' (Rp ' + new Intl.NumberFormat('id-ID').format(prod.selling_price) + ')' : '')"></option>
                                            </template>
                                        </select>
                                        <div class="mt-0.5 flex items-center gap-1" x-show="item.product_id">
                                            <template x-if="products.find(p => p.id === item.product_id)?.type === 'service'">
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-500/15 text-purple-700 dark:text-purple-300">
                                                    {{ __('sales.invoices.badge_service') }}
                                                </span>
                                            </template>
                                        </div>
                                        <input type="text" :name="'items[' + index + '][item_name]'" x-model="item.item_name" required placeholder="{{ __('sales.invoices.item_name_placeholder') }}"
                                            class="w-full h-7 bg-transparent border-b border-black/10 dark:border-white/10 px-1 text-[12px] text-black dark:text-white mt-1.5 placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:border-[#007AFF] transition">
                                        <input type="hidden" :name="'items[' + index + '][sku]'" x-model="item.sku">
                                    </td>
                                    <td class="inline-block sm:table-cell w-[48%] sm:w-24 py-0 sm:py-2.5 px-0 sm:px-3 align-top">
                                        <span class="block sm:hidden text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 mb-1">{{ __('sales.invoices.col_unit') }}</span>
                                        <select :name="'items[' + index + '][unit_id]'" x-model="item.unit_id" required class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                            <template x-for="u in units" :key="u.id">
                                                <option :value="u.id" x-text="u.name + ' (' + u.code + ')'"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="inline-block sm:table-cell w-[48%] sm:w-20 ml-[4%] sm:ml-0 text-left sm:text-right py-0 sm:py-2.5 px-0 sm:px-3 align-top">
                                        <span class="block sm:hidden text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 mb-1 text-left">{{ __('sales.invoices.col_qty') }}</span>
                                        <input type="number" step="any" min="0.0001" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required
                                            class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-[13px] text-left sm:text-right tabular-nums font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    </td>
                                    <td class="inline-block sm:table-cell w-[48%] sm:w-32 text-left sm:text-right py-0 sm:py-2.5 px-0 sm:px-3 align-top">
                                        <span class="block sm:hidden text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 mb-1 text-left">{{ __('sales.invoices.col_selling_price') }}</span>
                                        <input type="number" step="any" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" required
                                            class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-[13px] text-left sm:text-right tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    </td>
                                    <td class="inline-block sm:table-cell w-[48%] sm:w-28 ml-[4%] sm:ml-0 text-left sm:text-right py-0 sm:py-2.5 px-0 sm:px-3 align-top">
                                        <span class="block sm:hidden text-[10px] uppercase font-semibold text-[#FF9500] dark:text-[#FF9F0A] mb-1 text-left">{{ __('sales.invoices.col_hpp') }}</span>
                                        <input type="number" step="any" min="0" :name="'items[' + index + '][unit_hpp]'" x-model="item.unit_hpp" title="{{ __('sales.invoices.col_hpp') }}"
                                            class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-[13px] text-left sm:text-right tabular-nums text-[#FF9500] dark:text-[#FF9F0A] focus:outline-none focus:ring-2 focus:ring-[#FF9500]/50 transition">
                                    </td>
                                    <td class="block sm:table-cell py-1.5 sm:py-2.5 px-0 sm:px-3 text-right tabular-nums font-semibold text-black dark:text-white text-[13px] border-t sm:border-0 border-black/[0.04] dark:border-white/[0.06]">
                                        <div class="flex sm:block items-center justify-between">
                                            <span class="block sm:hidden text-[11px] font-semibold text-black/50 dark:text-white/50">{{ __('sales.invoices.col_row_subtotal') }}</span>
                                            <span class="text-[14px] sm:text-[13px] text-[#007AFF] sm:text-black dark:sm:text-white font-bold sm:font-semibold" x-text="formatCurrency(item.quantity * item.unit_price)"></span>
                                        </div>
                                    </td>
                                    <td class="hidden sm:table-cell py-2.5 px-2 w-8 text-center">
                                        <button type="button" @click="removeItem(index)" :disabled="items.length <= 1" class="p-1 rounded-[6px] text-black/30 hover:text-[#FF3B30] dark:text-white/30 dark:hover:text-[#FF453A] disabled:opacity-20 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Profitability Summary & Financial Calculation -->
            <div class="flex flex-col sm:flex-row justify-between items-start pt-4 border-t border-black/5 dark:border-white/10 gap-6">
                <!-- Left: Estimated Profit & Notes -->
                <div class="space-y-3 w-full sm:max-w-md text-[13px]">
                    <div class="p-4 rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] text-[#248A3D] dark:text-[#30D158] font-semibold uppercase tracking-wider">{{ __('sales.invoices.est_gross_profit_label') }}</div>
                            <div class="text-[20px] font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums" x-text="formatCurrency(totalGrossProfit)"></div>
                        </div>
                        <div class="text-right">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] tabular-nums" x-text="'{{ __('sales.invoices.margin_label') }} ' + Math.round(grossMarginPercentage) + '%'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('sales.invoices.notes_label') }}</label>
                        <textarea name="notes" rows="3" placeholder="{{ __('sales.invoices.notes_placeholder') }}" class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition leading-relaxed">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('sales.invoices.terms_warranty_label') }}</label>
                        <textarea name="terms_conditions" rows="2" placeholder="{{ __('sales.invoices.terms_warranty_placeholder') }}" class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">{{ old('terms_conditions', $invoice->terms_conditions) }}</textarea>
                    </div>
                </div>

                <!-- Right: Totals Breakdown -->
                <div class="w-full sm:w-80 p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2.5 text-[13px]">
                    <div class="flex justify-between text-black/60 dark:text-white/60">
                        <span>{{ __('sales.invoices.subtotal_label') }}</span>
                        <span class="tabular-nums font-medium text-black dark:text-white" x-text="formatCurrency(subtotal)"></span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-black/60 dark:text-white/60">{{ __('sales.invoices.discount_label') }}</span>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="any" min="0" name="discount_value" x-model="discountValue" class="w-20 h-7 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[6px] px-2 text-[12px] tabular-nums text-right text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <select name="discount_type" x-model="discountType" class="h-7 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[6px] px-1.5 text-[11px] text-black/70 dark:text-white/70 focus:outline-none">
                                <option value="fixed">{{ $business->currency_symbol }}</option>
                                <option value="percentage">%</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-black/60 dark:text-white/60">{{ __('sales.invoices.tax_vat_label') }}</span>
                        <input type="number" step="any" min="0" max="100" name="tax_percentage" x-model="taxPercentage" placeholder="11" class="w-20 h-7 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[6px] px-2 text-[12px] tabular-nums text-right text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-black/60 dark:text-white/60">{{ __('sales.invoices.shipping_cost_label') }}</span>
                        <input type="number" step="any" min="0" name="shipping_cost" x-model="shippingCost" placeholder="0" class="w-24 h-7 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[6px] px-2 text-[12px] tabular-nums text-right text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between items-center text-[15px] font-bold text-black dark:text-white">
                        <span>{{ __('sales.invoices.grand_total_label') }}</span>
                        <span class="tabular-nums text-[18px] text-[#007AFF]" x-text="formatCurrency(grandTotal)"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky/Bottom Form Actions -->
        <div class="flex items-center justify-end gap-2 pt-2">
            <a href="{{ route('invoices.show', $invoice->id) }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all flex items-center justify-center">
                {{ __('sales.invoices.btn_cancel') }}
            </a>
            @if(\App\Support\Context::hasPermission('invoices.edit') || \App\Support\Context::hasPermission('invoices.create'))
            <button type="submit" class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>{{ __('sales.invoices.btn_save_changes') }}</span>
            </button>
            @endif
        </div>
    </form>
</div>
@endsection
