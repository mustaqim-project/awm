{{-- MODAL RECORD PAYMENT FOR ACCOUNTS RECEIVABLE --}}
<div x-show="showPayArModal" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showPayArModal = false"
        class="w-full max-w-lg rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden">
        
        {{-- Header --}}
        <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Catat Pembayaran Piutang (AR)</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 font-mono" x-text="selectedAr ? (selectedAr.invoice_number + ' - ' + selectedAr.client_name) : ''"></p>
                </div>
            </div>
            <button type="button" @click="showPayArModal = false"
                class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/60 dark:text-white/60 hover:bg-black/10 transition-colors cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Form --}}
        <form :action="payArActionUrl" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf

            {{-- Summary of Invoice Balance --}}
            <div class="rounded-[14px] p-3.5 bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-medium text-black/50 dark:text-white/50">Total Tagihan</div>
                    <div class="text-[14px] font-bold text-black dark:text-white tabular-nums" x-text="'Rp ' + (selectedAr ? Number(selectedAr.total_amount).toLocaleString('id-ID') : 0)"></div>
                </div>
                <div class="text-right">
                    <div class="text-[11px] font-medium text-[#FF3B30]">Sisa Piutang Berjalan</div>
                    <div class="text-[16px] font-bold text-[#FF3B30] tabular-nums" x-text="'Rp ' + (selectedAr ? Number(selectedAr.balance_due).toLocaleString('id-ID') : 0)"></div>
                </div>
            </div>

            {{-- Payment Amount & Date --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Jumlah Pembayaran (IDR) <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="number" name="amount" required min="1" :max="selectedAr ? selectedAr.balance_due : ''"
                        :value="selectedAr ? selectedAr.balance_due : ''"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13.5px] font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Tanggal Diterima <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                </div>
            </div>

            {{-- Deposit Account (Kas & Bank) & Payment Method --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Rekening Kas / Bank Masuk <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="account_id" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
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
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                        <option value="bank_transfer">Transfer Bank (BCA / Mandiri / VA)</option>
                        <option value="qris">QRIS Standar</option>
                        <option value="cash">Tunai / Kas Langsung</option>
                        <option value="other">Metode Lainnya</option>
                    </select>
                </div>
            </div>

            {{-- Reference Number --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Nomor Referensi Bank / No. Bukti Transfer (Opsional)
                </label>
                <input type="text" name="reference_number" placeholder="Contoh: TRF-BCA-984210"
                    class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Catatan Pembayaran
                </label>
                <textarea name="notes" rows="2" placeholder="Pelunasan tahap 1 / pembayaran lunas invoice..."
                    class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759] resize-none"></textarea>
            </div>

            {{-- Attachment (Bukti Transfer) --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Lampiran Bukti Transfer / Slip Setoran (Opsional)
                </label>
                <input type="file" name="attachment" accept="image/*,application/pdf"
                    class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:py-2 file:px-3 file:rounded-[10px] file:border-0 file:text-[12px] file:font-bold file:bg-[#34C759]/10 file:text-[#248A3D] dark:file:text-[#30D158] hover:file:bg-[#34C759]/20 cursor-pointer">
            </div>

            {{-- Buttons --}}
            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end gap-2.5">
                <button type="button" @click="showPayArModal = false"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/5 hover:bg-black/10 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] transition-all shadow-sm shadow-[#34C759]/25 cursor-pointer flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan &amp; Jurnal Pelunasan</span>
                </button>
            </div>
        </form>
    </div>
</div>
