@extends('layouts.app', [
    'title' => 'Edit Pesanan Penjualan: ' . $salesOrder->so_number,
    'headerTitle' => 'Edit Pesanan Penjualan',
    'headerSubtitle' => 'Perbarui rincian pesanan penjualan, status pengerjaan, dan pengiriman'
])

@section('content')
<div class="max-w-[1100px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="salesOrderEditForm()">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER                              -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.sales_orders.breadcrumb_dashboard') }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <a href="{{ route('sales.orders.index') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.sales_orders.breadcrumb_sales_orders') }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <a href="{{ route('sales.orders.show', $salesOrder) }}" class="hover:text-[#007AFF] transition-colors">{{ $salesOrder->so_number }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-medium">Edit</span>
            </nav>
            <div class="flex items-center gap-2">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Edit Pesanan: {{ $salesOrder->so_number }}</h1>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wider
                    @if($salesOrder->status === 'draft') bg-neutral-500/10 text-neutral-600 dark:text-neutral-400
                    @elseif($salesOrder->status === 'confirmed') bg-blue-500/10 text-blue-600 dark:text-blue-400
                    @elseif($salesOrder->status === 'processing') bg-amber-500/10 text-amber-600 dark:text-amber-400
                    @elseif($salesOrder->status === 'completed') bg-emerald-500/10 text-emerald-600 dark:text-emerald-400
                    @else bg-red-500/10 text-red-600 dark:text-red-400 @endif">
                    {{ ucfirst($salesOrder->status) }}
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50">Sesuaikan item pesanan, alamat kirim, atau diskon sebelum tagihan ditagihkan</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('sales.orders.show', $salesOrder) }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center">
                <span>{{ __('sales.sales_orders.btn_cancel') }}</span>
            </a>
            <button type="submit" form="form-edit-sales-order" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </header>

    <!-- Error Summary Alerts -->
    @if ($errors->any())
        <div class="rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 p-4 text-[13px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
            <div class="flex items-center gap-2 font-semibold">
                <span class="w-2 h-2 rounded-full bg-[#FF3B30]"></span>
                <span>{{ __('sales.sales_orders.validation_errors_title') }}</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 pl-4 text-[12px]">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="form-edit-sales-order" action="{{ route('sales.orders.update', $salesOrder) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- STEP 1: INFORMASI PELANGGAN & NOMOR PESANAN -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="border-b border-black/5 dark:border-white/5 pb-3">
                <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.sales_orders.step2_title') }}</h2>
                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('sales.sales_orders.step2_desc') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('sales.sales_orders.customer_label') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="customer_id" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">{{ __('sales.sales_orders.customer_select_placeholder') }}</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id', $salesOrder->customer_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->company ?? $c->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('sales.sales_orders.so_number_label') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="text" name="so_number" value="{{ old('so_number', $salesOrder->so_number) }}" required
                           class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        Status Pesanan
                    </label>
                    <select name="status" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="draft" {{ old('status', $salesOrder->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed" {{ old('status', $salesOrder->status) == 'confirmed' ? 'selected' : '' }}>Confirmed (Dikonfirmasi)</option>
                        <option value="processing" {{ old('status', $salesOrder->status) == 'processing' ? 'selected' : '' }}>Processing (Sedang Diproses)</option>
                        <option value="partially_fulfilled" {{ old('status', $salesOrder->status) == 'partially_fulfilled' ? 'selected' : '' }}>Partially Fulfilled (Dikirim Sebagian)</option>
                        <option value="fulfilled" {{ old('status', $salesOrder->status) == 'fulfilled' ? 'selected' : '' }}>Fulfilled (Terkirim Penuh)</option>
                        <option value="completed" {{ old('status', $salesOrder->status) == 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                        <option value="cancelled" {{ old('status', $salesOrder->status) == 'cancelled' ? 'selected' : '' }}>Cancelled (Dibatalkan)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('sales.sales_orders.order_date_label') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="order_date" value="{{ old('order_date', $salesOrder->order_date?->format('Y-m-d') ?? date('Y-m-d')) }}" required
                           class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('sales.sales_orders.expected_delivery_label') }}
                    </label>
                    <input type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date', $salesOrder->expected_delivery_date?->format('Y-m-d')) }}"
                           class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        Tautkan Penawaran (Quotation)
                    </label>
                    <select name="quotation_id" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">Tanpa Penawaran (Direct)</option>
                        @foreach ($quotations as $q)
                            <option value="{{ $q->id }}" {{ old('quotation_id', $salesOrder->quotation_id) == $q->id ? 'selected' : '' }}>
                                {{ $q->quotation_number }} - {{ $q->customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('sales.sales_orders.shipping_address_label') }}
                    </label>
                    <textarea name="shipping_address" rows="2" placeholder="{{ __('sales.sales_orders.shipping_address_placeholder') }}"
                              class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">{{ old('shipping_address', $salesOrder->shipping_address) }}</textarea>
                </div>

                <div class="sm:col-span-1 lg:col-span-2">
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        Nama Penandatangan <span class="text-black/40 dark:text-white/40 font-normal">(Default)</span>
                    </label>
                    <input type="text" name="sender_name" value="{{ old('sender_name', $salesOrder->sender_name ?? (auth()->user()?->name ?: $business->name)) }}"
                           placeholder="{{ auth()->user()?->name ?: $business->name }}"
                           class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="sm:col-span-1 lg:col-span-2">
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        Nama Penerima <span class="text-black/40 dark:text-white/40 font-normal">(u.p. PIC Pelanggan)</span>
                    </label>
                    <input type="text" name="recipient_name" value="{{ old('recipient_name', $salesOrder->recipient_name) }}"
                           placeholder="u.p. Nama PIC Pelanggan"
                           class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>
        </div>

        <!-- STEP 2: RINCIAN ITEM PESANAN -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-3">
                <div>
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.sales_orders.step3_title') }}</h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('sales.sales_orders.step3_desc') }}</p>
                </div>
                <button type="button" @click="addItem()" class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>{{ __('sales.sales_orders.btn_add_item') }}</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('sales.sales_orders.col_product') }}</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">{{ __('sales.sales_orders.col_unit_price') }}</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-28">{{ __('sales.sales_orders.col_quantity') }}</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">{{ __('sales.sales_orders.col_subtotal') }}</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-3 py-2.5">
                                    <select :name="'items['+idx+'][product_id]'" x-model="item.product_id" @change="productSelected(idx, $event)"
                                            class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition font-medium">
                                        <option value="">{{ __('sales.sales_orders.select_product_placeholder') }}</option>
                                        @if(isset($goodsProducts) && $goodsProducts->isNotEmpty())
                                            <optgroup label="Barang Fisik">
                                                @foreach($goodsProducts as $p)
                                                <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-name="{{ $p->name }}" data-type="goods">
                                                    {{ $p->name }} (Rp {{ number_format($p->selling_price, 0, ',', '.') }})
                                                </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        @if(isset($serviceProducts) && $serviceProducts->isNotEmpty())
                                            <optgroup label="Layanan / Jasa">
                                                @foreach($serviceProducts as $p)
                                                <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-name="{{ $p->name }}" data-type="service">
                                                    [Jasa] {{ $p->name }} (Rp {{ number_format($p->selling_price, 0, ',', '.') }})
                                                </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        @if((!isset($goodsProducts) || $goodsProducts->isEmpty()) && (!isset($serviceProducts) || $serviceProducts->isEmpty()) && isset($products))
                                            @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-name="{{ $p->name }}" data-type="{{ $p->isService() ? 'service' : 'goods' }}">
                                                {{ $p->name }} (Rp {{ number_format($p->selling_price, 0, ',', '.') }})
                                            </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <input type="hidden" :name="'items['+idx+'][product_name]'" x-model="item.product_name">
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <input type="number" :name="'items['+idx+'][unit_price]'" x-model.number="item.unit_price" @input="calculateTotals()" min="0" step="100"
                                           class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <input type="number" :name="'items['+idx+'][quantity]'" x-model.number="item.quantity" @input="calculateTotals()" min="0.01" step="any"
                                           class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </td>
                                <td class="px-3 py-2.5 text-right font-semibold tabular-nums text-black dark:text-white" x-text="'Rp ' + (item.quantity * item.unit_price).toLocaleString('id-ID')"></td>
                                <td class="px-3 py-2.5 text-center">
                                    <button type="button" @click="removeItem(idx)" :disabled="items.length <= 1" class="w-7 h-7 rounded-[6px] text-black/40 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 disabled:opacity-20 transition-all inline-flex items-center justify-center">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Summary & Tax -->
            <div class="pt-4 border-t border-black/5 dark:border-white/10 flex flex-col sm:flex-row justify-between items-start gap-5">
                <div class="w-full sm:w-1/2 space-y-1.5">
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60">{{ __('sales.sales_orders.notes_label') }}</label>
                    <textarea name="notes" rows="3" placeholder="{{ __('sales.sales_orders.notes_placeholder') }}"
                              class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">{{ old('notes', $salesOrder->notes) }}</textarea>
                </div>

                <div class="w-full sm:w-80 space-y-2 text-[13px]">
                    <div class="flex justify-between text-black/60 dark:text-white/60">
                        <span>{{ __('sales.sales_orders.subtotal_label') }}</span>
                        <span class="font-semibold tabular-nums text-black dark:text-white" x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex justify-between items-center gap-2 text-black/60 dark:text-white/60">
                        <span>{{ __('sales.sales_orders.discount_label') }}</span>
                        <input type="number" name="discount_amount" x-model.number="discountAmount" @input="calculateTotals()" min="0"
                               class="w-32 h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div class="flex justify-between items-center gap-2 text-black/60 dark:text-white/60">
                        <span>{{ __('sales.sales_orders.tax_vat_label') }}</span>
                        <input type="number" name="tax_amount" x-model.number="taxAmount" @input="calculateTotals()" min="0"
                               class="w-32 h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between text-[15px] font-bold text-black dark:text-white">
                        <span>{{ __('sales.sales_orders.total_so_label') }}</span>
                        <span class="text-[#007AFF] dark:text-[#0A84FF] tabular-nums" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function salesOrderEditForm() {
        return {
            items: @json($salesOrder->items->map(fn($it) => [
                'product_id' => (string) ($it->product_id ?? ''),
                'product_name' => $it->product_name,
                'unit_price' => (float) $it->unit_price,
                'quantity' => (float) $it->quantity,
                'is_service' => $it->product?->isService() ?? false,
            ])),
            subtotal: {{ (float) $salesOrder->subtotal }},
            discountAmount: {{ (float) ($salesOrder->discount_amount ?? 0) }},
            taxAmount: {{ (float) ($salesOrder->tax_amount ?? 0) }},
            grandTotal: {{ (float) $salesOrder->total_amount }},

            init() {
                if (this.items.length === 0) {
                    this.addItem();
                }
                this.calculateTotals();
            },

            addItem() {
                this.items.push({ product_id: '', product_name: '', unit_price: 0, quantity: 1, is_service: false });
            },

            removeItem(idx) {
                if (this.items.length > 1) {
                    this.items.splice(idx, 1);
                    this.calculateTotals();
                }
            },

            productSelected(idx, ev) {
                const sel = ev ? ev.target : event.target;
                const opt = sel ? sel.options[sel.selectedIndex] : null;
                if (opt && opt.value) {
                    this.items[idx].unit_price = parseFloat(opt.dataset.price || 0);
                    this.items[idx].product_name = opt.dataset.name || '';
                    this.items[idx].is_service = opt.dataset.type === 'service';
                } else {
                    this.items[idx].unit_price = 0;
                    this.items[idx].product_name = '';
                    this.items[idx].is_service = false;
                }
                this.calculateTotals();
            },

            calculateTotals() {
                this.subtotal = this.items.reduce((sum, it) => sum + ((it.quantity || 0) * (it.unit_price || 0)), 0);
                this.grandTotal = Math.max(0, (this.subtotal - (this.discountAmount || 0)) + (this.taxAmount || 0));
            }
        };
    }
</script>
@endsection
