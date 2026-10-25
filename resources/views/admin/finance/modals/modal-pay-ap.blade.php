{{-- MODAL RECORD PAYMENT FOR ACCOUNTS PAYABLE (PELUNASAN VENDOR) --}}
<div x-show="showPayApModal" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showPayApModal = false"
        class="w-full max-w-lg rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden">
        
        {{-- Header --}}
        <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="send" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Pelunasan Tagihan Vendor (AP)</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 font-mono" x-text="selectedAp ? (selectedAp.bill_number + ' - ' + selectedAp.vendor_name) : ''"></p>
                </div>
            </div>
            <button type="button" @click="showPayApModal = false"
                class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/60 dark:text-white/60 hover:bg-black/10 transition-colors cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Form --}}
        <form :action="payApActionUrl" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf

            {{-- Summary of Bill Balance --}}
            <div class="rounded-[14px] p-3.5 bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-medium text-black/50 dark:text-white/50">Total Tagihan Vendor</div>
                    <div class="text-[14px] font-bold text-black dark:text-white tabular-nums" x-text="'Rp ' + (selectedAp ? Number(selectedAp.total_amount).toLocaleString('id-ID') : 0)"></div>
                </div>
                <div class="text-right">
                    <div class="text-[11px] font-medium text-[#FF3B30]">Sisa Kewajiban Hutang</div>
                    <div class="text-[16px] font-bold text-[#FF3B30] tabular-nums" x-text="'Rp ' + (selectedAp ? Number(selectedAp.balance_due).toLocaleString('id-ID') : 0)"></div>
                </div>
            </div>

            {{-- Payment Amount & Date --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Jumlah Pembayaran (IDR) <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="number" name="amount" required min="1" :max="selectedAp ? selectedAp.balance_due : ''"
                        :value="selectedAp ? selectedAp.balance_due : ''"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13.5px] font-bold text-[#007AFF] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Tanggal Pembayaran <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>
            </div>

            {{-- Source Account (Kas & Bank Keluar) & Payment Method --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Rekening Kas / Bank Keluar <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="account_id" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        @foreach($cashBankAccounts as $acc)
                            <option value="{{ $acc->id }}" {{ $acc->code === '1120' ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Metode Pembayaran <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="payment_method" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <option value="bank_transfer">Transfer Bank (BCA / Mandiri Corporate)</option>
                        <option value="credit_card">Kartu Kredit Perusahaan (Corporate Card)</option>
                        <option value="cash">Kas Tunai Platform</option>
                        <option value="other">Metode Lainnya</option>
                    </select>
                </div>
            </div>

            {{-- Reference Number --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Nomor Referensi Transfer / Ref Bank (Opsional)
                </label>
                <input type="text" name="reference_number" placeholder="Contoh: TRF-KL-882190"
                    class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Catatan Pelunasan
                </label>
                <textarea name="notes" rows="2" placeholder="Pelunasan tagihan server via klikBCA Bisnis..."
                    class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"></textarea>
            </div>

            {{-- Attachment (Struk Bukti Transfer Keluar) --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Lampiran Struk Bukti Transfer Bank (Opsional)
                </label>
                <input type="file" name="attachment" accept="image/*,application/pdf"
                    class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:py-2 file:px-3 file:rounded-[10px] file:border-0 file:text-[12px] file:font-bold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20 cursor-pointer">
            </div>

            {{-- Buttons --}}
            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end gap-2.5">
                <button type="button" @click="showPayApModal = false"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/5 hover:bg-black/10 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-sm shadow-[#007AFF]/25 cursor-pointer flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan &amp; Jurnal Pelunasan</span>
                </button>
            </div>
        </form>
    </div>
</div>
