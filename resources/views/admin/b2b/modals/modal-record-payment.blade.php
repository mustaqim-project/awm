{{-- MODAL RECORD PAYMENT FOR B2B INVOICE --}}
<div x-show="showPaymentModal" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showPaymentModal = false"
        class="w-full max-w-lg rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden">
        
        {{-- Header --}}
        <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-[#0284c7]/15 text-[#0284c7] flex items-center justify-center">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.catat_pembayaran_faktur') }}</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 font-mono" x-text="selectedInvoice ? (selectedInvoice.invoice_number + ' - ' + (selectedInvoice.customer ? selectedInvoice.customer.name : selectedInvoice.recipient_name)) : ''"></p>
                </div>
            </div>
            <button type="button" @click="showPaymentModal = false"
                class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/60 dark:text-white/60 hover:bg-black/10 transition-colors cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Form --}}
        <form :action="paymentActionUrl" method="POST" class="p-6 space-y-4">
            @csrf

            {{-- Summary of Invoice Balance --}}
            <div class="rounded-[14px] p-3.5 bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-medium text-black/50 dark:text-white/50">Total Nilai Faktur</div>
                    <div class="text-[14px] font-bold text-black dark:text-white tabular-nums" x-text="'Rp ' + (selectedInvoice ? Number(selectedInvoice.total_amount).toLocaleString('id-ID') : 0)"></div>
                </div>
                <div class="text-right">
                    <div class="text-[11px] font-medium text-[#FF3B30]">Sisa Piutang Berjalan</div>
                    <div class="text-[16px] font-bold text-[#FF3B30] tabular-nums" x-text="'Rp ' + (selectedInvoice ? Number(selectedInvoice.balance_due).toLocaleString('id-ID') : 0)"></div>
                </div>
            </div>

            {{-- Amount & Date --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Jumlah Pembayaran (IDR) <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="number" name="amount" required min="1" :max="selectedInvoice ? selectedInvoice.balance_due : ''"
                        :value="selectedInvoice ? selectedInvoice.balance_due : ''"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13.5px] font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Tanggal Diterima <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                </div>
            </div>

            {{-- Payment Method --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Metode Pembayaran <span class="text-[#FF3B30]">*</span>
                </label>
                <select name="payment_method" required
                    class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                    <option value="bank_transfer">Transfer Bank (BCA / Mandiri / Virtual Account)</option>
                    <option value="qris">QRIS Standar</option>
                    <option value="cash">Tunai / Kas Langsung</option>
                    <option value="credit_card">Kartu Kredit Perusahaan</option>
                    <option value="cheque">Cek / Bilyet Giro</option>
                </select>
            </div>

            {{-- Reference Number --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Nomor Referensi Bank / Bukti Setoran (Opsional)
                </label>
                <input type="text" name="reference_number" placeholder="Contoh: TRF-BCA-202610-8812"
                    class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Catatan Pembayaran
                </label>
                <textarea name="notes" rows="2" placeholder="{{ __('admin.pelunasan_termin_1_pembayaran_lunas_faktur') }}"
                    class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7] resize-none"></textarea>
            </div>

            {{-- Buttons --}}
            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end gap-2.5">
                <button type="button" @click="showPaymentModal = false"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/5 hover:bg-black/10 transition-colors cursor-pointer">{{ __('common.cancel') }}</button>
                <button type="submit"
                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#0284c7] hover:bg-[#0369a1] active:scale-[0.98] transition-all shadow-sm shadow-[#0284c7]/25 cursor-pointer flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>{{ __('admin.simpan_pembayaran') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
