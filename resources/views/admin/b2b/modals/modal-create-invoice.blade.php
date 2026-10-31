{{-- MODAL CREATE B2B COMMERCIAL INVOICE --}}
<div x-show="showCreateInvoiceModal" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showCreateInvoiceModal = false"
        class="w-full max-w-2xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden my-8">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-[#0284c7]/15 text-[#0284c7] flex items-center justify-center">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.terbitkan_faktur_penjualan_langsung') }}</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Menerbitkan invoice komersial resmi tanpa pesanan sebelumnya</p>
                </div>
            </div>
            <button type="button" @click="showCreateInvoiceModal = false"
                class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/60 dark:text-white/60 hover:bg-black/10 transition-colors cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Form --}}
        <form action="{{ route('admin.b2b.invoices.store') }}" method="POST" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto"
            x-data="{
                selectedBiz: '{{ $businessId ?? ($businesses->first()?->id ?? '') }}',
                items: [{ item_name: '', unit_price: 0, quantity: 1 }],
                discountAmount: 0,
                taxPercent: 11,
                addItem() {
                    this.items.push({ item_name: '', unit_price: 0, quantity: 1 });
                },
                removeItem(idx) {
                    if (this.items.length > 1) this.items.splice(idx, 1);
                },
                subtotal() {
                    return this.items.reduce((sum, it) => sum + (Number(it.quantity || 0) * Number(it.unit_price || 0)), 0);
                },
                taxable() {
                    return Math.max(0, this.subtotal() - Number(this.discountAmount || 0));
                },
                taxAmount() {
                    return this.taxable() * (Number(this.taxPercent || 0) / 100);
                },
                total() {
                    return this.taxable() + this.taxAmount();
                }
            }">
            @csrf

            {{-- Tenant & Customer Selection --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Tenant Bisnis <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="business_id" x-model="selectedBiz" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                        <option value="">-- Pilih Tenant --</option>
                        @foreach ($businesses as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Klien / Pelanggan <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="customer_id" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                        <option value="">-- Pilih Klien --</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" x-show="!selectedBiz || '{{ $c->business_id }}' === selectedBiz">
                                {{ $c->name }} {{ $c->company_name ? '(' . $c->company_name . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Date & Due Date & Terms --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Tanggal Faktur <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Jatuh Tempo <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Termin Pembayaran
                    </label>
                    <select name="payment_terms"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                        <option value="Net 30">Net 30 Hari</option>
                        <option value="Net 14">Net 14 Hari</option>
                        <option value="Net 60">Net 60 Hari</option>
                        <option value="Cash On Delivery">Cash on Delivery (COD)</option>
                    </select>
                </div>
            </div>

            {{-- Line Items --}}
            <div class="pt-2">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-[13px] font-bold text-black dark:text-white flex items-center gap-1.5">
                        <i data-lucide="layers" class="w-4 h-4 text-[#0284c7]"></i>
                        Rincian Item Faktur
                    </label>
                    <button type="button" @click="addItem()"
                        class="px-2.5 py-1 rounded-[8px] bg-[#0284c7]/10 text-[#0284c7] text-[12px] font-semibold hover:bg-[#0284c7]/20 transition-colors flex items-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Item
                    </button>
                </div>

                <div class="space-y-2.5">
                    <template x-for="(it, idx) in items" :key="idx">
                        <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                                <div class="sm:col-span-6">
                                    <input type="text" :name="'items[' + idx + '][item_name]'" x-model="it.item_name" required
                                        placeholder="{{ __('admin.nama_produk_jasa_tagihan') }}"
                                        class="w-full h-9 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                                </div>
                                <div class="sm:col-span-2">
                                    <input type="number" :name="'items[' + idx + '][quantity]'" x-model="it.quantity" required min="0.01" step="any"
                                        placeholder="Qty"
                                        class="w-full h-9 px-2 text-center rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                                </div>
                                <div class="sm:col-span-3">
                                    <input type="number" :name="'items[' + idx + '][unit_price]'" x-model="it.unit_price" required min="0"
                                        placeholder="{{ __('admin.harga_rp') }}"
                                        class="w-full h-9 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                                </div>
                                <div class="sm:col-span-1 text-right">
                                    <button type="button" @click="removeItem(idx)" x-show="items.length > 1"
                                        class="w-8 h-8 rounded-full text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Tax & Discount --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Potongan Diskon (Rp)</label>
                    <input type="number" name="discount_amount" x-model="discountAmount" min="0"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">PPN / Pajak (%)</label>
                    <input type="number" name="tax_percentage" x-model="taxPercent" min="0" max="100" step="0.1"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                </div>
            </div>

            {{-- Summary Totals --}}
            <div class="rounded-[16px] p-4 bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2">
                <div class="flex justify-between text-[12.5px] text-black/60 dark:text-white/60">
                    <span>Subtotal:</span>
                    <span class="font-mono font-medium" x-text="'Rp ' + Math.max(0, subtotal()).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between text-[12.5px] text-black/60 dark:text-white/60">
                    <span>Estimasi PPN:</span>
                    <span class="font-mono font-medium" x-text="'Rp ' + taxAmount().toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between text-[14px] font-bold text-black dark:text-white pt-2 border-t border-black/5 dark:border-white/5">
                    <span>Total Tagihan Faktur:</span>
                    <span class="font-mono text-[#0284c7] text-[16px]" x-text="'Rp ' + total().toLocaleString('id-ID')"></span>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Petunjuk Pembayaran / Catatan</label>
                <textarea name="notes" rows="2" placeholder="{{ __('admin.rekening_transfer_batas_toleransi_pembayaran') }}"
                    class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]"></textarea>
            </div>

            {{-- Action Buttons --}}
            <div class="pt-2 flex items-center justify-end gap-3 border-t border-black/5 dark:border-white/5">
                <button type="button" @click="showCreateInvoiceModal = false"
                    class="px-4 py-2 rounded-[12px] bg-black/5 dark:bg-white/5 hover:bg-black/10 text-[13px] font-medium text-black/70 dark:text-white/70 transition-colors">{{ __('common.cancel') }}</button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-[12px] bg-[#0284c7] hover:bg-[#0369a1] text-white text-[13.5px] font-semibold shadow-lg shadow-[#0284c7]/20 transition-all flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> Terbitkan Faktur
                </button>
            </div>
        </form>
    </div>
</div>
