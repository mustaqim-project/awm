@extends('layouts.app', [
    'title' => 'Edit Draf Retur: ' . $return->return_number,
    'headerTitle' => 'Edit Draf Retur Penjualan',
    'headerSubtitle' => 'Perbarui item retur, jumlah barang, alasan, atau metode pengembalian dana'
])

@section('content')
<div class="max-w-[1100px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="salesReturnEditForm()">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER                              -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.returns.breadcrumb_dashboard') }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <a href="{{ route('sales.returns.index') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.returns.breadcrumb_sales_returns') }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <a href="{{ route('sales.returns.show', $return) }}" class="hover:text-[#007AFF] transition-colors">{{ $return->return_number }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-medium">Edit</span>
            </nav>
            <div class="flex items-center gap-2">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Edit Retur: {{ $return->return_number }}</h1>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wider bg-neutral-500/10 text-neutral-600 dark:text-neutral-400">
                    Draft
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50">Hanya draf retur yang dapat diedit sebelum disetujui (Approve) atau diselesaikan (Complete)</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('sales.returns.show', $return) }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center">
                <span>{{ __('sales.returns.btn_cancel') }}</span>
            </a>
            <button type="submit" form="form-edit-sales-return" :disabled="getSelectedCount() === 0"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-40 disabled:cursor-not-allowed active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </header>

    <!-- Error Alert Box -->
    @if ($errors->any())
        <div class="rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 p-4 text-[13px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
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

    <form id="form-edit-sales-return" method="POST" action="{{ route('sales.returns.update', $return) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. DOKUMEN ASAL -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="border-b border-black/5 dark:border-white/5 pb-3">
                <h2 class="text-[17px] font-semibold text-black dark:text-white">Dokumen Asal Transaksi</h2>
                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Retur ini terikat pada dokumen transaksi di bawah</p>
            </div>

            <div class="p-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold uppercase tracking-wider
                            {{ $sourceType === 'invoice' ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-[#34C759]/10 text-[#34C759]' }}">
                            {{ $sourceType === 'invoice' ? 'Faktur Penjualan (B2B)' : 'Pesanan Kasir (POS)' }}
                        </span>
                        <span class="text-[14px] font-semibold text-black dark:text-white">
                            {{ $return->invoice?->invoice_number ?? $return->posOrder?->order_number ?? '-' }}
                        </span>
                    </div>
                    <p class="text-[12px] text-black/60 dark:text-white/60">
                        Pelanggan: <strong class="text-black dark:text-white">{{ $return->customer?->name ?? 'Pelanggan Umum' }}</strong>
                        &bull; Tanggal Transaksi: {{ $return->invoice?->invoice_date?->format('d M Y') ?? $return->posOrder?->order_date ?? '-' }}
                    </p>
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Tanggal Retur</label>
                    <input type="date" name="return_date" value="{{ old('return_date', $return->return_date?->format('Y-m-d') ?? date('Y-m-d')) }}"
                           class="h-9 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>
        </div>

        <!-- 2. PILIH ITEM RETUR -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-black/5 dark:border-white/5 pb-3">
                <div>
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.returns.sec_items_select_title') }}</h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('sales.returns.sec_items_select_desc') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="selectAll()" class="text-[12px] font-medium text-[#007AFF] hover:underline cursor-pointer">Pilih Semua</button>
                    <span class="text-black/30 dark:text-white/30">&bull;</span>
                    <button type="button" @click="unselectAll()" class="text-[12px] font-medium text-black/50 dark:text-white/50 hover:underline cursor-pointer">Batal Semua</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/5 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">
                            <th class="py-2.5 px-3 w-10 text-center">Pilih</th>
                            <th class="py-2.5 px-3">{{ __('sales.returns.col_product_name') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('sales.returns.col_price_each') }}</th>
                            <th class="py-2.5 px-3 text-center">{{ __('sales.returns.col_initial_qty') }}</th>
                            <th class="py-2.5 px-3 text-center">Maks Retur</th>
                            <th class="py-2.5 px-3 text-right w-28">{{ __('sales.returns.col_return_qty') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('sales.returns.col_subtotal_return') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr :class="item.selected ? 'bg-[#007AFF]/[0.02] dark:bg-[#007AFF]/[0.05]' : ''" class="transition-colors">
                                <!-- Checkbox -->
                                <td class="py-3 px-3 text-center">
                                    <input type="checkbox" :name="'items[' + idx + '][selected]'" value="1"
                                           x-model="item.selected" @change="onToggleItem(item)"
                                           class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]/50 cursor-pointer">
                                    <template x-if="sourceType === 'invoice'">
                                        <input type="hidden" :name="'items[' + idx + '][invoice_item_id]'" :value="item.item_id">
                                    </template>
                                    <template x-if="sourceType === 'pos_order'">
                                        <input type="hidden" :name="'items[' + idx + '][pos_order_item_id]'" :value="item.item_id">
                                    </template>
                                </td>

                                <!-- Nama Produk -->
                                <td class="py-3 px-3">
                                    <div class="font-medium text-black dark:text-white" x-text="item.product_name"></div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums" x-show="item.sku && item.sku !== '-'" x-text="'SKU: ' + item.sku"></div>
                                </td>

                                <!-- Harga Satuan -->
                                <td class="py-3 px-3 text-right tabular-nums text-black/60 dark:text-white/60" x-text="'Rp ' + item.unit_price.toLocaleString('id-ID')"></td>

                                <!-- Qty Asal -->
                                <td class="py-3 px-3 text-center tabular-nums text-black/50 dark:text-white/50" x-text="item.original_qty"></td>

                                <!-- Sisa Yang Bisa Diretur -->
                                <td class="py-3 px-3 text-center tabular-nums font-semibold" :class="item.max_qty > 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black/30 dark:text-white/30'">
                                    <span x-text="item.max_qty"></span>
                                </td>

                                <!-- Input Qty Retur -->
                                <td class="py-3 px-3">
                                    <input type="number" :name="'items[' + idx + '][quantity]'"
                                           x-model.number="item.quantity" :disabled="!item.selected"
                                           @input="validateItemQty(item)" min="0.01" :max="item.max_qty" step="any"
                                           class="w-full h-8 text-right tabular-nums bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[6px] px-2 text-[12px] font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 disabled:opacity-30">
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
            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex justify-between items-center text-[13px]">
                <div class="text-black/50 dark:text-white/50">
                    <span>Item Dipilih: </span>
                    <strong class="text-black dark:text-white tabular-nums" x-text="getSelectedCount()"></strong> baris
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-black/60 dark:text-white/60">Total Nilai Retur:</span>
                    <span class="text-[18px] font-bold tabular-nums text-[#FF3B30]" x-text="'Rp ' + Math.round(calculateTotalReturn()).toLocaleString('id-ID')"></span>
                </div>
            </div>
        </div>

        <!-- 3. METODE PENGEMBALIAN & ALASAN -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
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
                    <input name="reason" value="{{ old('reason', $return->reason) }}"
                        placeholder="{{ __('sales.returns.reason_return_placeholder') }}" required
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>
        </div>

        <!-- Action Submit Bar -->
        <div class="flex items-center justify-end gap-2 pt-2">
            <a href="{{ route('sales.returns.show', $return) }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                {{ __('sales.returns.btn_cancel') }}
            </a>
            <button type="submit" :disabled="getSelectedCount() === 0"
                class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-40 disabled:cursor-not-allowed active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
    function salesReturnEditForm() {
        return {
            sourceType: '{{ $sourceType }}',
            refundMethod: '{{ old('refund_method', $return->refund_method ?? 'credit_note') }}',
            items: @json($itemsData),

            onToggleItem(item) {
                if (item.selected) {
                    if (!item.quantity || item.quantity <= 0) {
                        item.quantity = Math.min(1, item.max_qty);
                    }
                }
            },

            validateItemQty(item) {
                if (item.quantity > item.max_qty) {
                    item.quantity = item.max_qty;
                }
                if (item.quantity < 0.01) {
                    item.quantity = 0.01;
                }
            },

            selectAll() {
                this.items.forEach(it => {
                    if (it.max_qty > 0) {
                        it.selected = true;
                        if (!it.quantity || it.quantity <= 0) {
                            it.quantity = Math.min(1, it.max_qty);
                        }
                    }
                });
            },

            unselectAll() {
                this.items.forEach(it => {
                    it.selected = false;
                });
            },

            getSelectedCount() {
                return this.items.filter(it => it.selected && (parseFloat(it.quantity) || 0) > 0).length;
            },

            calculateTotalReturn() {
                return this.items
                    .filter(it => it.selected)
                    .reduce((sum, it) => sum + ((parseFloat(it.quantity) || 0) * (parseFloat(it.unit_price) || 0)), 0);
            }
        };
    }
</script>
@endsection
