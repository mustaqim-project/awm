@extends('layouts.app', [
    'title' => __('sales.sales_orders.create_page_title'),
    'headerTitle' => __('sales.sales_orders.create_header_title'),
    'headerSubtitle' => __('sales.sales_orders.create_header_subtitle'),
])

@section('content')
    <div class="max-w-[1100px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="salesOrderForm()">

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
        <!-- ===================================================== -->
        <header
            class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1"
                    aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.sales_orders.breadcrumb_dashboard') }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <a href="{{ route('sales.orders.index') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.sales_orders.breadcrumb_sales_orders') }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <span class="text-black dark:text-white font-medium">{{ __('sales.sales_orders.breadcrumb_create') }}</span>
                </nav>
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">{{ __('sales.sales_orders.form_title') }}</h1>
                <p class="text-[13px] text-black/50 dark:text-white/50">{{ __('sales.sales_orders.form_subtitle') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('sales.orders.index') }}"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center gap-1.5">
                    <span>{{ __('sales.sales_orders.btn_cancel') }}</span>
                </a>
                <button type="button" @click="promptSubmitOrder()"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>{{ __('sales.sales_orders.btn_confirm_order') }}</span>
                </button>
            </div>
        </header>

        <!-- Error Summary Alerts -->
        @if ($errors->any())
            <div
                class="rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 p-4 text-[13px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
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

        <form id="form-create-sales-order" action="{{ route('sales.orders.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- STEP 1: REFERENSI QUOTATION -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
                <div class="border-b border-black/5 dark:border-white/5 pb-3">
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.sales_orders.step1_title') }}</h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('sales.sales_orders.step1_desc') }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('sales.sales_orders.quotation_ref_label') }} <span
                                class="text-black/40 dark:text-white/40 font-normal">{{ __('sales.sales_orders.optional_label') }}</span>
                        </label>
                        <select name="quotation_id" x-model="selectedQuotationId" @change="onQuotationChange()"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                            <option value="">{{ __('sales.sales_orders.quotation_direct_order') }}</option>
                            @foreach ($quotations as $q)
                                <option value="{{ $q->id }}">{{ $q->quotation_number }} - {{ $q->customer->name }}
                                    (Rp {{ number_format($q->total_amount, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="selectedQuotationId" x-cloak class="flex items-end">
                        <div
                            class="w-full p-3.5 rounded-[10px] bg-[#007AFF]/8 border border-[#007AFF]/15 text-[12px] space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-semibold text-[#007AFF] uppercase tracking-wider">{{ __('sales.sales_orders.quotation_selected_badge') }}</span>
                                <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                            </div>
                            <p class="text-black dark:text-white font-semibold tabular-nums"
                                x-text="getSelectedQuotationLabel()"></p>
                            <p class="text-black/50 dark:text-white/50">{{ __('sales.sales_orders.quotation_auto_loaded') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: INFORMASI PELANGGAN & PESANAN -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
                <div class="border-b border-black/5 dark:border-white/5 pb-3">
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.sales_orders.step2_title') }}</h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('sales.sales_orders.step2_desc') }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Pelanggan -->
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('sales.sales_orders.customer_label') }} <span class="text-[#FF3B30]">*</span>
                        </label>
                        <select name="customer_id" x-model="customerId" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer @error('customer_id') ring-2 ring-[#FF3B30] @enderror">
                            <option value="">{{ __('sales.sales_orders.customer_select_placeholder') }}</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">
                                    {{ $c->name }}{{ $c->company ? ' (' . $c->company . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- No SO -->
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('sales.sales_orders.so_number_label') }}
                        </label>
                        <input type="text" name="so_number" value="{{ old('so_number', $nextNumber) }}"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] font-semibold tabular-nums text-[#007AFF] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <!-- Tanggal Pesanan -->
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('sales.sales_orders.order_date_label') }} <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <!-- Estimasi Kirim -->
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('sales.sales_orders.expected_delivery_label') }}
                        </label>
                        <input type="date" name="expected_delivery_date"
                            value="{{ old('expected_delivery_date', date('Y-m-d', strtotime('+3 days'))) }}"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <!-- Alamat Pengiriman -->
                    <div class="col-span-1 sm:col-span-2 lg:col-span-4">
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('sales.sales_orders.shipping_address_label') }} <span class="text-black/40 dark:text-white/40 font-normal">{{ __('sales.sales_orders.optional_label') }}</span>
                        </label>
                        <input type="text" name="shipping_address" value="{{ old('shipping_address') }}"
                            placeholder="{{ __('sales.sales_orders.shipping_address_placeholder') }}"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <!-- Nama Penandatangan / Pengirim -->
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('sales.sender_name_default') }}
                        </label>
                        <input type="text" name="sender_name" value="{{ old('sender_name', auth()->user()?->name ?: $business->name) }}"
                            placeholder="{{ auth()->user()?->name ?: $business->name }}"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <!-- Nama Penerima / Kontak Pelanggan -->
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('sales.recipient_name_pic') }}
                        </label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name') }}"
                            placeholder="{{ __('sales.u_p_nama_pic_pelanggan') }}"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <!-- Lampiran Surat Pesanan -->
                    <div class="col-span-1 sm:col-span-2 lg:col-span-4">
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('sales.attachment_default') }}
                        </label>
                        <input type="text" name="attachment_info" value="{{ old('attachment_info', '-') }}"
                            placeholder="-"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>
            </div>

            <!-- STEP 3: RINCIAN ITEM PESANAN -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                    <div>
                        <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.sales_orders.step3_title') }}</h2>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('sales.sales_orders.step3_desc') }}</p>
                    </div>

                    <button type="button" @click="addItem()"
                        class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>{{ __('sales.sales_orders.btn_add_item') }}</span>
                    </button>
                </div>

                <!-- Dynamic Table Items -->
                <div class="overflow-x-auto rounded-[10px] border border-black/5 dark:border-white/10">
                    <table class="w-full text-left text-[13px] min-w-[700px]">
                        <thead>
                            <tr
                                class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 text-[11px] font-semibold uppercase tracking-wide">
                                <th class="py-2.5 px-3">{{ __('sales.sales_orders.col_item_product') }}</th>
                                <th class="py-2.5 px-3 text-right w-36 whitespace-nowrap">{{ __('sales.sales_orders.col_item_unit_price') }}</th>
                                <th class="py-2.5 px-3 text-right w-24 whitespace-nowrap">{{ __('sales.sales_orders.col_item_qty') }}</th>
                                <th class="py-2.5 px-3 text-right w-32 whitespace-nowrap">{{ __('sales.sales_orders.col_item_discount') }}</th>
                                <th class="py-2.5 px-3 text-right w-36 whitespace-nowrap">{{ __('sales.sales_orders.col_item_subtotal') }}</th>
                                <th class="py-2.5 px-3 text-center w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            <template x-for="(item, idx) in items" :key="idx">
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <!-- Product Select -->
                                    <td class="py-2 px-3">
                                        <select :name="'items[' + idx + '][product_id]'" x-model="item.product_id"
                                            @change="productSelected(idx, $event)"
                                            class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer font-medium">
                                            <option value="">{{ __('sales.sales_orders.select_product_placeholder') }}</option>
                                            @if (isset($goodsProducts) && $goodsProducts->isNotEmpty())
                                                <optgroup label="{{ __('sales.sales_orders.optgroup_goods') }}">
                                                    @foreach ($goodsProducts as $p)
                                                        <option value="{{ $p->id }}"
                                                            data-price="{{ $p->selling_price }}"
                                                            data-name="{{ $p->name }}" data-type="goods">
                                                            {{ $p->name }} (Rp
                                                            {{ number_format($p->selling_price, 0, ',', '.') }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                            @if (isset($serviceProducts) && $serviceProducts->isNotEmpty())
                                                <optgroup label="{{ __('sales.sales_orders.optgroup_services') }}">
                                                    @foreach ($serviceProducts as $p)
                                                        <option value="{{ $p->id }}"
                                                            data-price="{{ $p->selling_price }}"
                                                            data-name="{{ $p->name }}" data-type="service">
                                                            [{{ __('sales.sales_orders.badge_service_item') }}] {{ $p->name }} (Rp
                                                            {{ number_format($p->selling_price, 0, ',', '.') }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                            @if (!isset($goodsProducts) || ($goodsProducts->isEmpty() && (!isset($serviceProducts) || $serviceProducts->isEmpty())))
                                                @foreach ($products as $p)
                                                    <option value="{{ $p->id }}"
                                                        data-price="{{ $p->selling_price }}"
                                                        data-name="{{ $p->name }}"
                                                        data-type="{{ $p->type ?? 'goods' }}">
                                                        {{ $p->isService() ? '[' . __('sales.sales_orders.badge_service_item') . '] ' : '' }}{{ $p->name }} (Rp
                                                        {{ number_format($p->selling_price, 0, ',', '.') }})
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <template x-if="item.is_service">
                                                <span
                                                    class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold bg-purple-500/15 text-purple-700 dark:text-purple-300">
                                                    {{ __('sales.sales_orders.badge_service_item') }}
                                                </span>
                                            </template>
                                            <template x-if="item.product_id && !item.is_service">
                                                <span
                                                    class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10.5px] font-medium text-black/50 dark:text-white/50">
                                                    {{ __('sales.sales_orders.badge_goods_item') }}
                                                </span>
                                            </template>
                                        </div>
                                        <input type="hidden" :name="'items[' + idx + '][product_name]'"
                                            x-model="item.product_name">
                                    </td>

                                    <!-- Unit Price -->
                                    <td class="py-2 px-3">
                                        <input type="number" :name="'items[' + idx + '][unit_price]'"
                                            x-model.number="item.unit_price" @input="calculateTotals()" min="0"
                                            step="100"
                                            class="w-full h-9 text-right tabular-nums bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    </td>

                                    <!-- Qty -->
                                    <td class="py-2 px-3">
                                        <input type="number" :name="'items[' + idx + '][quantity]'"
                                            x-model.number="item.quantity" @input="calculateTotals()" min="0.01"
                                            step="any"
                                            class="w-full h-9 text-right tabular-nums bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    </td>

                                    <!-- Discount Item -->
                                    <td class="py-2 px-3">
                                        <input type="number" :name="'items[' + idx + '][discount_amount]'"
                                            x-model.number="item.discount_amount" @input="calculateTotals()"
                                            min="0" step="100"
                                            class="w-full h-9 text-right tabular-nums bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-[#FF3B30] focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50 transition">
                                    </td>

                                    <!-- Subtotal Item -->
                                    <td class="py-2 px-3 text-right font-semibold tabular-nums text-[#007AFF] whitespace-nowrap"
                                        x-text="'Rp ' + Math.max(0, ((item.quantity || 0) * (item.unit_price || 0)) - (item.discount_amount || 0)).toLocaleString('id-ID')">
                                    </td>

                                    <!-- Remove -->
                                    <td class="py-2 px-3 text-center">
                                        <button type="button" @click="removeItem(idx)" :disabled="items.length <= 1"
                                            class="h-7 w-7 rounded-[6px] text-black/30 dark:text-white/30 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 disabled:opacity-20 transition flex items-center justify-center cursor-pointer"
                                            title="{{ __('sales.sales_orders.btn_remove_item_tooltip') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Notes & Summary Box -->
                <div
                    class="pt-4 border-t border-black/5 dark:border-white/5 flex flex-col sm:flex-row justify-between items-start gap-6">
                    <!-- Notes -->
                    <div class="w-full sm:w-1/2 space-y-1.5">
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70">{{ __('sales.sales_orders.notes_label') }}</label>
                        <textarea name="notes" rows="3"
                            placeholder="{{ __('sales.sales_orders.notes_placeholder') }}"
                            class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Financial Calculation Box (macOS Grouped Inset) -->
                    <div
                        class="w-full sm:w-80 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-3 text-[13px]">
                        <div class="flex justify-between text-black/60 dark:text-white/60">
                            <span>{{ __('sales.sales_orders.subtotal_items_label') }}:</span>
                            <span class="font-semibold tabular-nums text-black dark:text-white"
                                x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                        </div>

                        <div class="flex justify-between items-center gap-2 text-black/60 dark:text-white/60">
                            <span>{{ __('sales.sales_orders.order_discount_label') }}:</span>
                            <input type="number" name="discount_amount" x-model.number="discountAmount"
                                @input="calculateTotals()" min="0"
                                class="w-28 h-8 text-right tabular-nums bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[6px] px-2 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50">
                        </div>

                        <div class="flex justify-between items-center gap-2 text-black/60 dark:text-white/60">
                            <span>{{ __('sales.sales_orders.tax_vat_label') }}:</span>
                            <input type="number" name="tax_percentage" x-model.number="taxPercentage"
                                @input="calculateTotals()" min="0" max="100" step="0.01"
                                class="w-28 h-8 text-right tabular-nums bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[6px] px-2 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]/50">
                        </div>
                        <input type="hidden" name="tax_amount" :value="taxAmount">

                        <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between items-baseline">
                            <span class="font-semibold text-black dark:text-white">{{ __('sales.sales_orders.total_order_value_label') }}:</span>
                            <span class="text-[18px] font-bold tabular-nums text-[#007AFF]"
                                x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Bar -->
            <div class="flex items-center justify-end gap-2 pt-2">
                <a href="{{ route('sales.orders.index') }}"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                    {{ __('sales.sales_orders.dialog_btn_cancel') }}
                </a>
                <button type="button" @click="promptSubmitOrder()"
                    class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    {{ __('sales.sales_orders.btn_submit_create') }}
                </button>
            </div>
        </form>

        <!-- ===================================================== -->
        <!-- APPLE ALERT DIALOG: KONFIRMASI PEMBUATAN SO           -->
        <!-- ===================================================== -->
        <template x-teleport="body">
            <div x-show="confirmModalOpen" x-cloak
                class="fixed inset-0 z-[220] overflow-y-auto"
                @keydown.escape.window="confirmModalOpen = false"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="so-create-confirm-dialog-title">

                <!-- Backdrop -->
                <div x-show="confirmModalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

                <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="confirmModalOpen = false">
                    <div class="relative w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
                        @click.away="confirmModalOpen = false"
                        x-show="confirmModalOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95">

                        <div class="px-4 pt-5 pb-4">
                            <p id="so-create-confirm-dialog-title" class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.sales_orders.dialog_confirm_title') }}</p>
                            <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                                {{ __('sales.sales_orders.dialog_confirm_prompt') }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                            <button type="button" @click="confirmModalOpen = false"
                                class="min-h-[44px] py-3 text-black/60 dark:text-white/60 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                {{ __('sales.sales_orders.dialog_btn_cancel') }}
                            </button>
                            <button type="button" @click="submitOrder()"
                                class="min-h-[44px] py-3 text-[#007AFF] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                {{ __('sales.sales_orders.dialog_btn_confirm') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
                    </div>
                </div>
            </div>
        </template>

    </div>

    @php
        $quotationsMapped = $quotations
            ->map(
                fn($q) => [
                    'id' => $q->id,
                    'label' => $q->quotation_number . ' - ' . $q->customer->name,
                    'customer_id' => $q->customer_id,
                    'discount' => (float) $q->discount_amount,
                    'tax' => (float) $q->tax_amount,
                    'notes' => $q->notes,
                    'items' => $q->items
                        ->map(
                            fn($i) => [
                                'product_id' => $i->product_id,
                                'product_name' => $i->product_name,
                                'unit_price' => (float) $i->unit_price,
                                'quantity' => (float) $i->quantity,
                                'discount_amount' => (float) $i->discount_amount,
                            ],
                        )
                        ->values()
                        ->all(),
                ],
            )
            ->values()
            ->all();
    @endphp

    <script>
        window.SO_QUOTATIONS_DATA = {!! json_encode($quotationsMapped, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
        window.SO_PREFILL_QUOTATION_ID = @json($prefillQuotation?->id);

        function salesOrderForm() {
            return {
                quotations: window.SO_QUOTATIONS_DATA || [],
                selectedQuotationId: window.SO_PREFILL_QUOTATION_ID ? String(window.SO_PREFILL_QUOTATION_ID) : '',
                customerId: '',

                items: [{
                    product_id: '',
                    product_name: '',
                    unit_price: 0,
                    quantity: 1,
                    discount_amount: 0,
                    is_service: false
                }],
                subtotal: 0,
                discountAmount: 0,
                taxPercentage: {{ $business->pos_enable_tax ? (float) $business->pos_tax_percent : 0 }},
                taxAmount: 0,
                grandTotal: 0,
                confirmModalOpen: false,

                init() {
                    if (this.selectedQuotationId) {
                        this.$nextTick(() => this.onQuotationChange());
                    }
                    this.calculateTotals();
                },

                promptSubmitOrder() {
                    if (!this.customerId) {
                        alert('{{ __('sales.sales_orders.alert_select_customer') }}');
                        return;
                    }
                    this.confirmModalOpen = true;
                },

                submitOrder() {
                    this.confirmModalOpen = false;
                    document.getElementById('form-create-sales-order').submit();
                },

                getSelectedQuotationLabel() {
                    const q = this.quotations.find(q => String(q.id) === String(this.selectedQuotationId));
                    return q ? q.label : '';
                },

                onQuotationChange() {
                    if (!this.selectedQuotationId) {
                        return;
                    }
                    const q = this.quotations.find(q => String(q.id) === String(this.selectedQuotationId));
                    if (!q) return;

                    // Auto-fill customer
                    this.customerId = String(q.customer_id);

                    // Auto-fill discount & tax
                    this.discountAmount = q.discount || 0;
                    this.taxAmount = q.tax || 0;

                    // Auto-fill items
                    if (q.items && q.items.length > 0) {
                        this.items = q.items.map(i => ({
                            product_id: i.product_id ? String(i.product_id) : '',
                            product_name: i.product_name || '',
                            unit_price: i.unit_price || 0,
                            quantity: i.quantity || 1,
                            discount_amount: i.discount_amount || 0,
                            is_service: false,
                        }));
                    }

                    this.$nextTick(() => {
                        this.calculateTotals();
                    });
                },

                addItem() {
                    this.items.push({
                        product_id: '',
                        product_name: '',
                        unit_price: 0,
                        quantity: 1,
                        discount_amount: 0,
                        is_service: false
                    });
                },

                removeItem(idx) {
                    if (this.items.length > 1) {
                        this.items.splice(idx, 1);
                        this.calculateTotals();
                    }
                },

                productSelected(idx, event) {
                    const sel = event ? event.target : null;
                    if (sel) {
                        const opt = sel.options[sel.selectedIndex];
                        if (opt && opt.value) {
                            this.items[idx].unit_price = parseFloat(opt.dataset.price || 0);
                            this.items[idx].product_name = opt.dataset.name || '';
                            this.items[idx].is_service = opt.dataset.type === 'service';
                        } else {
                            this.items[idx].unit_price = 0;
                            this.items[idx].product_name = '';
                            this.items[idx].is_service = false;
                        }
                    }
                    this.calculateTotals();
                },

                calculateTotals() {
                    this.subtotal = this.items.reduce((sum, it) => {
                        return sum + Math.max(0, ((it.quantity || 0) * (it.unit_price || 0)) - (it
                            .discount_amount || 0));
                    }, 0);
                    const taxableAmount = Math.max(0, this.subtotal - (this.discountAmount || 0));
                    this.taxAmount = taxableAmount * (Math.max(0, Number(this.taxPercentage || 0)) / 100);
                    this.grandTotal = Math.max(0, taxableAmount + this.taxAmount);
                }
            };
        }
    </script>
@endsection
