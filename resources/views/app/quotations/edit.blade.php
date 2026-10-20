@extends('layouts.app', [
    'title' => 'Edit Surat Penawaran: ' . $quotation->quotation_number,
    'headerTitle' => 'Edit Surat Penawaran',
    'headerSubtitle' => 'Perbarui rincian harga, kuantitas item, atau masa berlaku penawaran harga'
])

@section('content')
<div class="max-w-[1080px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="quotationEditForm()">
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / HEADER (macOS Sonoma Style)              -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.quotations.breadcrumb_dashboard') }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <a href="{{ route('sales.quotations.index') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.quotations.breadcrumb_quotations') }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <a href="{{ route('sales.quotations.show', $quotation) }}" class="hover:text-[#007AFF] transition-colors">{{ $quotation->quotation_number }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-medium">Edit</span>
            </nav>
            <div class="flex items-center gap-2">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Edit Penawaran: {{ $quotation->quotation_number }}</h1>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wider
                    @if($quotation->status === 'draft') bg-neutral-500/10 text-neutral-600 dark:text-neutral-400
                    @elseif($quotation->status === 'sent') bg-blue-500/10 text-blue-600 dark:text-blue-400
                    @elseif($quotation->status === 'accepted') bg-emerald-500/10 text-emerald-600 dark:text-emerald-400
                    @elseif($quotation->status === 'converted') bg-indigo-500/10 text-indigo-600 dark:text-indigo-400
                    @else bg-red-500/10 text-red-600 dark:text-red-400 @endif">
                    {{ ucfirst($quotation->status) }}
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50">Sesuaikan item atau syarat penawaran sebelum dikirim atau dikonversi ke pesanan</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('sales.quotations.show', $quotation) }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center">
                <span>Kembali</span>
            </a>
            <button type="submit" form="form-edit-quotation" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
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
                <span>Terdapat kesalahan pengisian formulir</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 pl-4 text-[12px]">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. FORM BODY                                          -->
    <!-- ===================================================== -->
    <form id="form-edit-quotation" action="{{ route('sales.quotations.update', $quotation) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Section 1: Data Klien & Tanggal -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4">
            <h3 class="text-[14px] font-semibold text-black dark:text-white">{{ __('sales.quotations.sec1_title') }}</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('sales.quotations.customer_label') }} <span class="text-[#FF3B30]">*</span></label>
                    <select name="customer_id" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">{{ __('sales.quotations.customer_select_placeholder') }}</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id', $quotation->customer_id) == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->company ?? $c->phone }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('sales.quotations.quotation_number_label') }}</label>
                    <input type="text" name="quotation_number" value="{{ old('quotation_number', $quotation->quotation_number) }}"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Status Penawaran</label>
                    <select name="status" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="draft" {{ old('status', $quotation->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ old('status', $quotation->status) == 'sent' ? 'selected' : '' }}>Sent (Terkirim)</option>
                        <option value="accepted" {{ old('status', $quotation->status) == 'accepted' ? 'selected' : '' }}>Accepted (Disetujui Klien)</option>
                        <option value="rejected" {{ in_array(old('status', $quotation->status), ['rejected', 'declined']) ? 'selected' : '' }}>Rejected / Declined (Ditolak)</option>
                        @if($quotation->status === 'converted')
                        <option value="converted" selected>Converted (Telah Jadi SO)</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('sales.quotations.date_issued_label') }} <span class="text-[#FF3B30]">*</span></label>
                    <input type="date" name="date" value="{{ old('date', $quotation->date?->format('Y-m-d') ?? date('Y-m-d')) }}" required
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('sales.quotations.expiry_date_label') }}</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date', $quotation->expiry_date?->format('Y-m-d')) }}"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Nama Penandatangan <span class="text-black/40 dark:text-white/40 font-normal">(Default)</span></label>
                    <input type="text" name="sender_name" value="{{ old('sender_name', $quotation->sender_name ?? (auth()->user()?->name ?: $business->name)) }}"
                           placeholder="{{ auth()->user()?->name ?: $business->name }}"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Nama Penerima <span class="text-black/40 dark:text-white/40 font-normal">(u.p. PIC Pelanggan)</span></label>
                    <input type="text" name="recipient_name" value="{{ old('recipient_name', $quotation->recipient_name) }}"
                           placeholder="u.p. Nama PIC Pelanggan"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Lampiran <span class="text-black/40 dark:text-white/40 font-normal">(Default: -)</span></label>
                    <input type="text" name="attachment_info" value="{{ old('attachment_info', $quotation->attachment_info ?? '-') }}"
                           placeholder="-"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>
        </div>

        <!-- Section 2: Rincian Item Penawaran -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <div>
                    <h3 class="text-[14px] font-semibold text-black dark:text-white">{{ __('sales.quotations.sec2_title') }}</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('sales.quotations.sec2_desc') }}</p>
                </div>
                <button type="button" @click="addItem()" class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>{{ __('sales.quotations.btn_add_item') }}</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('sales.quotations.col_item_product') }}</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">{{ __('sales.quotations.col_item_price') }}</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-28">{{ __('sales.quotations.col_item_qty') }}</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">{{ __('sales.quotations.col_item_total') }}</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-3 py-2.5">
                                    <select :name="'items['+idx+'][product_id]'" x-model="item.product_id" @change="productSelected(idx, $event)"
                                            class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition font-medium">
                                        <option value="">{{ __('sales.quotations.select_product_placeholder') }}</option>
                                        @if(isset($goodsProducts) && $goodsProducts->isNotEmpty())
                                            <optgroup label="{{ __('sales.quotations.optgroup_goods') }}">
                                                @foreach($goodsProducts as $p)
                                                <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-name="{{ $p->name }}" data-type="goods">
                                                    {{ $p->name }} (Rp {{ number_format($p->selling_price, 0, ',', '.') }})
                                                </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        @if(isset($serviceProducts) && $serviceProducts->isNotEmpty())
                                            <optgroup label="{{ __('sales.quotations.optgroup_services') }}">
                                                @foreach($serviceProducts as $p)
                                                <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-name="{{ $p->name }}" data-type="service">
                                                    [{{ __('sales.quotations.badge_service') }}] {{ $p->name }} (Rp {{ number_format($p->selling_price, 0, ',', '.') }})
                                                </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        @if(!isset($goodsProducts) || ($goodsProducts->isEmpty() && (!isset($serviceProducts) || $serviceProducts->isEmpty())))
                                            @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-name="{{ $p->name }}" data-type="{{ $p->type ?? 'goods' }}">
                                                {{ $p->isService() ? '[' . __('sales.quotations.badge_service') . '] ' : '' }}{{ $p->name }} (Rp {{ number_format($p->selling_price, 0, ',', '.') }})
                                            </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="mt-1 flex items-center gap-1.5">
                                        <template x-if="item.is_service">
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold bg-purple-500/15 text-purple-700 dark:text-purple-300">
                                                {{ __('sales.quotations.badge_service') }}
                                            </span>
                                        </template>
                                        <template x-if="item.product_id && !item.is_service">
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10.5px] font-medium text-black/50 dark:text-white/50">
                                                {{ __('sales.quotations.badge_goods') }}
                                            </span>
                                        </template>
                                    </div>
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
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Summary & Tax -->
            <div class="pt-4 border-t border-black/5 dark:border-white/10 flex flex-col sm:flex-row justify-between items-start gap-5">
                <div class="w-full sm:w-1/2 space-y-3">
                    <div>
                        <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('sales.quotations.notes_terms_label') }}</label>
                        <textarea name="notes" rows="2" placeholder="{{ __('sales.quotations.notes_terms_placeholder') }}"
                                  class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">{{ old('notes', $quotation->notes) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Syarat & Ketentuan Tambahan (Terms & Conditions)</label>
                        <textarea name="terms_and_conditions" rows="2" placeholder="Ketentuan garansi, SLA pengiriman, atau batas DP..."
                                  class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">{{ old('terms_and_conditions', $quotation->terms_and_conditions) }}</textarea>
                    </div>
                </div>

                <div class="w-full sm:w-80 space-y-2 text-[13px]">
                    <div class="flex justify-between text-black/60 dark:text-white/60">
                        <span>{{ __('sales.quotations.subtotal_label') }}</span>
                        <span class="font-semibold tabular-nums text-black dark:text-white" x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex justify-between items-center gap-2 text-black/60 dark:text-white/60">
                        <span>{{ __('sales.quotations.discount_label') }}</span>
                        <input type="number" name="discount_amount" x-model.number="discountAmount" @input="calculateTotals()" min="0"
                               class="w-32 h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div class="flex justify-between items-center gap-2 text-black/60 dark:text-white/60">
                        <span>{{ __('sales.quotations.tax_vat_label') }}</span>
                        <input type="number" name="tax_amount" x-model.number="taxAmount" @input="calculateTotals()" min="0"
                               class="w-32 h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between text-[15px] font-bold text-black dark:text-white">
                        <span>{{ __('sales.quotations.total_quotation_label') }}</span>
                        <span class="text-[#007AFF] dark:text-[#0A84FF] tabular-nums" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('sales.quotations.show', $quotation) }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] active:opacity-80 transition-all flex items-center">
                {{ __('sales.quotations.btn_cancel') }}
            </a>
            <button type="submit" class="h-9 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>

<script>
    function quotationEditForm() {
        @php
            $quoInitialItems = $quotation->items->map(function ($it) {
                return [
                    'product_id' => (string) ($it->product_id ?? ''),
                    'product_name' => $it->product_name,
                    'unit_price' => (float) $it->unit_price,
                    'quantity' => (float) $it->quantity,
                    'is_service' => $it->product?->isService() ?? false,
                ];
            })->values();
        @endphp
        return {
            items: @json($quoInitialItems),
            subtotal: {{ (float) $quotation->subtotal }},
            discountAmount: {{ (float) ($quotation->discount_amount ?? 0) }},
            taxAmount: {{ (float) ($quotation->tax_amount ?? 0) }},
            grandTotal: {{ (float) $quotation->total_amount }},

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
