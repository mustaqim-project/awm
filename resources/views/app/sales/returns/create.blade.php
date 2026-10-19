@extends('layouts.app', [
    'title' => __('sales.returns.create_title'),
    'headerTitle' => __('sales.returns.create_heading'),
    'headerSubtitle' => __('sales.returns.create_subtitle_desc'),
])

@section('content')
    <div class="max-w-[1100px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="salesReturnForm()">

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
        <!-- ===================================================== -->
        <header
            class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1"
                    aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.returns.breadcrumb_dashboard') }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <a href="{{ route('sales.returns.index') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.returns.breadcrumb_sales_returns') }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <span class="text-black dark:text-white font-medium">{{ __('sales.returns.breadcrumb_create') }}</span>
                </nav>
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">{{ __('sales.returns.create_heading') }}
                </h1>
                <p class="text-[13px] text-black/50 dark:text-white/50">{{ __('sales.returns.create_subheading') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('sales.returns.index') }}"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center">
                    <span>{{ __('sales.returns.btn_cancel') }}</span>
                </a>
                @if (\App\Support\Context::hasPermission('sales.returns') || \App\Support\Context::hasPermission('sales.pipeline'))
                    <button type="button" @click="promptSaveReturn()"
                        :disabled="!hasSelectedSource() || getSelectedCount() === 0"
                        class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-40 disabled:cursor-not-allowed active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>{{ __('sales.returns.btn_save_draft') }}</span>
                    </button>
                @endif
            </div>
        </header>

        <!-- Error Alert Box -->
        @if ($errors->any())
            <div
                class="rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 p-4 text-[13px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
                <div class="flex items-center gap-2 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-[#FF3B30]"></span>
                    <span>{{ __('sales.returns.form_check_notice') }}</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-4 text-[12px]">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="form-create-sales-return" method="POST" action="{{ route('sales.returns.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="source_type" :value="sourceType">

            <!-- 1. PILIH SUMBER TRANSAKSI -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                    <div>
                        <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.returns.sec_ref_source_title') }}
                        </h2>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('sales.returns.sec_ref_source_desc') }}</p>
                    </div>

                    <!-- Source Switcher Segmented Control -->
                    <div
                        class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium shrink-0">
                        <button type="button" @click="setSourceType('invoice')"
                            :class="sourceType === 'invoice' ?
                                'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' :
                                'text-black/55 dark:text-white/55'"
                            class="px-3 py-1 rounded-[7px] transition-all flex items-center gap-1.5 cursor-pointer">
                            <span>{{ __('sales.returns.source_invoice_btn') }}</span>
                        </button>
                        <button type="button" @click="setSourceType('pos_order')"
                            :class="sourceType === 'pos_order' ?
                                'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' :
                                'text-black/55 dark:text-white/55'"
                            class="px-3 py-1 rounded-[7px] transition-all flex items-center gap-1.5 cursor-pointer">
                            <span>{{ __('sales.returns.source_pos_btn') }}</span>
                        </button>
                    </div>
                </div>

                <!-- Invoice Selection Dropdown -->
                <div x-show="sourceType === 'invoice'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('sales.returns.select_invoice_doc_label') }} <span class="text-[#FF3B30]">*</span>
                        </label>
                        <select name="invoice_id" x-model="selectedInvoiceId" @change="onInvoiceChange()"
                            :required="sourceType === 'invoice'"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                            <option value="">{{ __('sales.returns.select_invoice_doc_placeholder') }}</option>
                            @foreach ($invoices as $invoice)
                                <option value="{{ $invoice->id }}">
                                    {{ $invoice->invoice_number }} - {{ $invoice->customer?->name ?? __('sales.returns.general_customer') }} (Rp
                                    {{ number_format($invoice->total_amount, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Info Box Invoice Terpilih -->
                    <div x-show="selectedRecord && sourceType === 'invoice'" x-cloak class="flex items-end">
                        <div
                            class="w-full p-3.5 rounded-[10px] bg-[#007AFF]/8 border border-[#007AFF]/15 text-[12px] space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-semibold text-[#007AFF] uppercase tracking-wider">{{ __('sales.returns.selected_invoice_badge') }}</span>
                                <span class="font-mono text-black dark:text-white font-bold"
                                    x-text="selectedRecord?.number"></span>
                            </div>
                            <div class="flex justify-between text-black/60 dark:text-white/60 text-[12px]">
                                <span>{{ __('sales.returns.customer_label') }} <strong class="text-black dark:text-white"
                                        x-text="selectedRecord?.customer_name"></strong></span>
                                <span>{{ __('sales.returns.date_short_label') }} <span class="tabular-nums" x-text="selectedRecord?.date"></span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POS Order Selection Dropdown -->
                <div x-show="sourceType === 'pos_order'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('sales.returns.select_pos_order_label') }} <span class="text-[#FF3B30]">*</span>
                        </label>
                        <select name="pos_order_id" x-model="selectedPosOrderId" @change="onPosOrderChange()"
                            :required="sourceType === 'pos_order'"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                            <option value="">{{ __('sales.returns.select_pos_order_placeholder') }}</option>
                            @foreach ($posOrders as $order)
                                <option value="{{ $order->id }}">
                                    {{ $order->order_number }} -
                                    {{ $order->customer?->name ?? ($order->customer_name_guest ?: __('sales.returns.guest_customer')) }} (Rp
                                    {{ number_format($order->total_amount, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Info Box POS Order Terpilih -->
                    <div x-show="selectedRecord && sourceType === 'pos_order'" x-cloak class="flex items-end">
                        <div
                            class="w-full p-3.5 rounded-[10px] bg-[#5856D6]/8 border border-[#5856D6]/15 text-[12px] space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-semibold text-[#5856D6] uppercase tracking-wider">{{ __('sales.returns.selected_pos_badge') }}</span>
                                <span class="font-mono text-black dark:text-white font-bold"
                                    x-text="selectedRecord?.number"></span>
                            </div>
                            <div class="flex justify-between text-black/60 dark:text-white/60 text-[12px]">
                                <span>{{ __('sales.returns.customer_label') }} <strong class="text-black dark:text-white"
                                        x-text="selectedRecord?.customer_name"></strong></span>
                                <span>{{ __('sales.returns.time_label') }} <span class="tabular-nums" x-text="selectedRecord?.date"></span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. RINCIAN ITEM YANG DIRETUR -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                    <div>
                        <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.returns.sec_select_items_title') }}
                        </h2>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('sales.returns.sec_select_items_desc') }}</p>
                    </div>
                    <div class="flex items-center gap-2" x-show="availableItems.length > 0">
                        <button type="button" @click="toggleSelectAll()"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition cursor-pointer">
                            <span x-text="isAllSelected() ? '{{ __('sales.returns.btn_deselect_all') }}' : '{{ __('sales.returns.btn_select_all') }}'"></span>
                        </button>
                    </div>
                </div>

                <!-- State Belum Pilih Transaksi -->
                <div x-show="!hasSelectedSource()" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <svg class="w-8 h-8 text-black/25 dark:text-white/25 stroke-current" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        <span>{{ __('sales.returns.select_source_first_hint') }}</span>
                    </div>
                </div>

                <!-- State Dipilih tapi Tidak Ada Item Tersisa -->
                <div x-show="hasSelectedSource() && availableItems.length === 0" x-cloak
                    class="p-4 rounded-[10px] bg-[#FF9500]/10 border border-[#FF9500]/20 text-[13px] text-[#B25E00] dark:text-[#FF9F0A] text-center">
                    {{ __('sales.returns.no_items_remaining_hint') }}
                </div>

                <!-- Tabel Item Transaksi -->
                <div x-show="availableItems.length > 0" x-cloak
                    class="overflow-x-auto rounded-[10px] border border-black/5 dark:border-white/10">
                    <table class="w-full text-left text-[13px] min-w-[700px]">
                        <thead>
                            <tr
                                class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 text-[11px] font-semibold uppercase tracking-wide">
                                <th class="py-2.5 px-3 w-12 text-center">{{ __('sales.returns.col_return_checkbox') }}</th>
                                <th class="py-2.5 px-3">{{ __('sales.returns.col_product_item') }}</th>
                                <th class="py-2.5 px-3 text-right w-28">{{ __('sales.returns.col_unit_price') }}</th>
                                <th class="py-2.5 px-3 text-center w-24">{{ __('sales.returns.col_purchased_qty') }}</th>
                                <th class="py-2.5 px-3 text-center w-28">{{ __('sales.returns.col_max_return_qty') }}</th>
                                <th class="py-2.5 px-3 text-right w-32">{{ __('sales.returns.col_return_qty_input') }}</th>
                                <th class="py-2.5 px-3 text-right w-36">{{ __('sales.returns.col_subtotal_return') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            <template x-for="(item, idx) in availableItems" :key="item.item_id">
                                <tr :class="item.selected ? 'bg-[#007AFF]/[0.03] dark:bg-[#007AFF]/[0.05]' :
                                    'hover:bg-black/[0.02] dark:hover:bg-white/[0.03]'"
                                    class="transition-colors">
                                    <!-- Checkbox -->
                                    <td class="py-3 px-3 text-center">
                                        <input type="checkbox" :name="'items[' + idx + '][selected]'" value="1"
                                            x-model="item.selected" :disabled="item.max_qty <= 0"
                                            @change="onItemToggle(item)"
                                            class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF] w-4 h-4 cursor-pointer disabled:opacity-20">

                                        <!-- Input hidden item id sesuai sourceType -->
                                        <template x-if="sourceType === 'invoice'">
                                            <input type="hidden" :name="'items[' + idx + '][invoice_item_id]'"
                                                 :value="item.item_id">
                                        </template>
                                        <template x-if="sourceType === 'pos_order'">
                                            <input type="hidden" :name="'items[' + idx + '][pos_order_item_id]'"
                                                :value="item.item_id">
                                        </template>
                                    </td>

                                    <!-- Nama Produk -->
                                    <td class="py-3 px-3">
                                        <div class="font-medium text-black dark:text-white" x-text="item.product_name">
                                        </div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums"
                                            x-show="item.sku && item.sku !== '-'" x-text="'SKU: ' + item.sku"></div>
                                    </td>

                                    <!-- Harga Satuan -->
                                    <td class="py-3 px-3 text-right tabular-nums text-black/60 dark:text-white/60"
                                        x-text="'Rp ' + item.unit_price.toLocaleString('id-ID')"></td>

                                    <!-- Qty Asal -->
                                    <td class="py-3 px-3 text-center tabular-nums text-black/50 dark:text-white/50"
                                        x-text="item.original_qty"></td>

                                    <!-- Sisa Yang Bisa Diretur -->
                                    <td class="py-3 px-3 text-center tabular-nums font-semibold"
                                        :class="item.max_qty > 0 ? 'text-[#34C759] dark:text-[#30D158]' :
                                            'text-black/30 dark:text-white/30'">
                                        <span x-text="item.max_qty"></span>
                                        <template x-if="item.returned_qty > 0">
                                            <span class="text-[10px] text-black/40 dark:text-white/40 block font-normal"
                                                x-text="'{{ __('sales.returns.returned_previously_label', ['qty' => '__QTY__']) }}'.replace('__QTY__', item.returned_qty)"></span>
                                        </template>
                                    </td>

                                    <!-- Input Qty Retur -->
                                    <td class="py-3 px-3">
                                        <template x-if="item.max_qty > 0">
                                            <input type="number" :name="'items[' + idx + '][quantity]'"
                                                x-model.number="item.quantity" :disabled="!item.selected"
                                                @input="validateItemQty(item)" min="0.01" :max="item.max_qty"
                                                step="any"
                                                class="w-full h-8 text-right tabular-nums bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[6px] px-2 text-[12px] font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 disabled:opacity-30">
                                        </template>
                                        <template x-if="item.max_qty <= 0">
                                            <span class="text-[11px] text-[#FF3B30] block text-right font-medium">{{ __('sales.returns.exhausted_returned') }}</span>
                                        </template>
                                    </td>

                                    <!-- Subtotal Retur Baris -->
                                    <td class="py-3 px-3 text-right font-semibold tabular-nums text-[#FF3B30] whitespace-nowrap"
                                        x-text="item.selected && item.quantity > 0 ? 'Rp ' + Math.round(item.quantity * item.unit_price).toLocaleString('id-ID') : 'Rp 0'">
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Total Retur Ringkasan -->
                <div x-show="availableItems.length > 0" x-cloak
                    class="pt-3 border-t border-black/5 dark:border-white/5 flex justify-between items-center text-[13px]">
                    <div class="text-black/50 dark:text-white/50">
                        <span>{{ __('sales.returns.items_selected_count') }} </span>
                        <strong class="text-black dark:text-white tabular-nums" x-text="getSelectedCount()"></strong> {{ __('sales.returns.items_unit') }}
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-black/60 dark:text-white/60">{{ __('sales.returns.total_return_value_label') }}</span>
                        <span class="text-[18px] font-bold tabular-nums text-[#FF3B30]"
                            x-text="'Rp ' + Math.round(calculateTotalReturn()).toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </div>

            <!-- 3. METODE PENGEMBALIAN & ALASAN -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
                <div class="border-b border-black/5 dark:border-white/5 pb-3">
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.returns.sec_compensation_title') }}</h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('sales.returns.sec_compensation_subtitle') }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('sales.returns.refund_method_label') }} <span class="text-[#FF3B30]">*</span>
                        </label>
                        <select name="refund_method" x-model="refundMethod" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                            <option value="cash_refund">{{ __('sales.returns.opt_cash_refund_direct') }}</option>
                            <option value="credit_note">{{ __('sales.returns.opt_credit_note_next') }}</option>
                            <option value="store_credit">{{ __('sales.returns.opt_store_credit_deposit') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('sales.returns.reason_return_label') }} <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input name="reason"
                            placeholder="{{ __('sales.returns.reason_return_placeholder') }}" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>
            </div>

            <!-- Action Submit Bar -->
            <div class="flex items-center justify-end gap-2 pt-2">
                <a href="{{ route('sales.returns.index') }}"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                    {{ __('sales.returns.btn_cancel') }}
                </a>
                <button type="button" @click="promptSaveReturn()"
                    :disabled="!hasSelectedSource() || getSelectedCount() === 0"
                    class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-40 disabled:cursor-not-allowed active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    {{ __('sales.returns.btn_save_draft') }}
                </button>
            </div>
        </form>

        <!-- ===================================================== -->
        <!-- APPLE ALERT DIALOG: KONFIRMASI DRAFT RETUR            -->
        <!-- ===================================================== -->
        <template x-teleport="body">
            <div x-show="confirmReturnModalOpen" x-cloak
                class="fixed inset-0 z-[220] overflow-y-auto"
                @keydown.escape.window="confirmReturnModalOpen = false"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="sales-return-confirm-dialog-title">

                <!-- Backdrop -->
                <div x-show="confirmReturnModalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

                <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="confirmReturnModalOpen = false">
                    <div class="relative w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
                        @click.away="confirmReturnModalOpen = false"
                        x-show="confirmReturnModalOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95">

                        <div class="px-4 pt-5 pb-4">
                            <p id="sales-return-confirm-dialog-title" class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.returns.dialog_confirm_draft_title') }}</p>
                            <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                                {{ __('sales.returns.dialog_confirm_draft_desc') }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                            <button type="button" @click="confirmReturnModalOpen = false"
                                class="min-h-[44px] py-3 text-black/60 dark:text-white/60 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                {{ __('sales.returns.btn_cancel') }}
                            </button>
                            <button type="button" @click="submitReturn()"
                                class="min-h-[44px] py-3 text-[#007AFF] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                {{ __('sales.returns.dialog_save_btn') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>

    <script>
        window.RETURN_INVOICES_DATA = {!! json_encode($invoicesData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
        window.RETURN_POS_ORDERS_DATA = {!! json_encode($posOrdersData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
        window.RETURN_INIT_SOURCE = @json($initialSourceType);
        window.RETURN_PREFILL_INV = @json($prefillInvoiceId);
        window.RETURN_PREFILL_POS = @json($prefillPosOrderId);

        function salesReturnForm() {
            return {
                sourceType: window.RETURN_INIT_SOURCE || 'invoice',
                invoices: window.RETURN_INVOICES_DATA || [],
                posOrders: window.RETURN_POS_ORDERS_DATA || [],
                selectedInvoiceId: window.RETURN_PREFILL_INV || '',
                selectedPosOrderId: window.RETURN_PREFILL_POS || '',
                selectedRecord: null,
                availableItems: [],
                refundMethod: 'cash_refund',
                confirmReturnModalOpen: false,

                init() {
                    if (this.sourceType === 'pos_order' && this.selectedPosOrderId) {
                        this.onPosOrderChange();
                    } else if (this.sourceType === 'invoice' && this.selectedInvoiceId) {
                        this.onInvoiceChange();
                    }
                },

                setSourceType(type) {
                    this.sourceType = type;
                    this.selectedRecord = null;
                    this.availableItems = [];
                    if (type === 'invoice') {
                        this.refundMethod = 'credit_note';
                        if (this.selectedInvoiceId) this.onInvoiceChange();
                    } else {
                        this.refundMethod = 'cash_refund';
                        if (this.selectedPosOrderId) this.onPosOrderChange();
                    }
                },

                hasSelectedSource() {
                    return this.sourceType === 'invoice' ? !!this.selectedInvoiceId : !!this.selectedPosOrderId;
                },

                onInvoiceChange() {
                    if (!this.selectedInvoiceId) {
                        this.selectedRecord = null;
                        this.availableItems = [];
                        return;
                    }
                    const found = this.invoices.find(inv => String(inv.id) === String(this.selectedInvoiceId));
                    if (found) {
                        this.selectedRecord = found;
                        this.availableItems = found.items.map(it => ({
                            ...it,
                            selected: false
                        }));
                    } else {
                        this.selectedRecord = null;
                        this.availableItems = [];
                    }
                },

                onPosOrderChange() {
                    if (!this.selectedPosOrderId) {
                        this.selectedRecord = null;
                        this.availableItems = [];
                        return;
                    }
                    const found = this.posOrders.find(ord => String(ord.id) === String(this.selectedPosOrderId));
                    if (found) {
                        this.selectedRecord = found;
                        this.availableItems = found.items.map(it => ({
                            ...it,
                            selected: false
                        }));
                    } else {
                        this.selectedRecord = null;
                        this.availableItems = [];
                    }
                },

                onItemToggle(item) {
                    if (item.selected && (!item.quantity || item.quantity <= 0)) {
                        item.quantity = item.max_qty > 0 ? Math.min(1, item.max_qty) : 0;
                    }
                },

                validateItemQty(item) {
                    if (item.quantity > item.max_qty) {
                        item.quantity = item.max_qty;
                    }
                    if (item.quantity < 0) {
                        item.quantity = 0;
                    }
                    if (item.quantity > 0) {
                        item.selected = true;
                    }
                },

                toggleSelectAll() {
                    const allSelected = this.isAllSelected();
                    this.availableItems.forEach(it => {
                        if (it.max_qty > 0) {
                            it.selected = !allSelected;
                            if (it.selected && (!it.quantity || it.quantity <= 0)) {
                                it.quantity = Math.min(1, item.max_qty);
                            }
                        }
                    });
                },

                isAllSelected() {
                    const selectable = this.availableItems.filter(it => it.max_qty > 0);
                    if (selectable.length === 0) return false;
                    return selectable.every(it => it.selected);
                },

                getSelectedCount() {
                    return this.availableItems.filter(it => it.selected && it.quantity > 0).length;
                },

                calculateTotalReturn() {
                    return this.availableItems.reduce((acc, it) => {
                        if (it.selected && it.quantity > 0) {
                            return acc + (it.quantity * it.unit_price);
                        }
                        return acc;
                    }, 0);
                },

                promptSaveReturn() {
                    const count = this.getSelectedCount();
                    if (count === 0) {
                        alert('{{ __('sales.returns.select_one_item_prompt') }}');
                        return;
                    }
                    this.confirmReturnModalOpen = true;
                },

                submitReturn() {
                    this.confirmReturnModalOpen = false;
                    document.getElementById('form-create-sales-return').submit();
                }
            };
        }
    </script>
@endsection
