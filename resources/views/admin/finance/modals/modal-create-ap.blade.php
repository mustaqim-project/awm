{{-- MODAL CREATE ACCOUNTS PAYABLE BILL (TAGIHAN HUTANG VENDOR) --}}
<div x-show="showCreateApModal" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showCreateApModal = false"
        class="w-full max-w-xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden">
        
        {{-- Header --}}
        <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center">
                    <i data-lucide="file-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Catat Tagihan Hutang Vendor (AP)</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Tagihan biaya server, AI API, lisensi, atau operasional dengan auto-posting ke Akun 2130</p>
                </div>
            </div>
            <button type="button" @click="showCreateApModal = false"
                class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/60 dark:text-white/60 hover:bg-black/10 transition-colors cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Form --}}
        <form action="{{ route('admin.finance.payables.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4"
            x-data="{
                apSubtotal: 0,
                apTax: 0,
            }">
            @csrf

            {{-- Vendor Name & Category --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Nama Vendor / Pihak Ketiga <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="text" name="vendor_name" required placeholder="Hostinger / OpenAI / AWS / Meta"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Kategori Vendor <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="vendor_category" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                        <option value="infrastructure">Infrastruktur Server &amp; Cloud (Hostinger/AWS)</option>
                        <option value="ai_api">Konsumsi Token AI (OpenAI / Google Gemini API)</option>
                        <option value="whatsapp_gateway">WhatsApp Gateway &amp; Notifikasi SMS</option>
                        <option value="office_rent">Sewa Kantor &amp; Utilitas</option>
                        <option value="salary">Gaji &amp; Honor Tim Platform</option>
                        <option value="marketing">Pemasaran &amp; Iklan Digital (Google/Meta)</option>
                        <option value="legal_tax">Legalitas, Perizinan &amp; Pajak</option>
                        <option value="other">Beban Vendor Lainnya</option>
                    </select>
                </div>
            </div>

            {{-- Vendor Reference Number & Bill Title --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        No. Faktur / Invoice Asli Vendor
                    </label>
                    <input type="text" name="reference_number" placeholder="Contoh: INV-HST-2026-9921"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Perihal / Judul Tagihan <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="text" name="title" required placeholder="{{ __('admin.tagihan_cloud_vps_production_bulan_ini') }}"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                </div>
            </div>

            {{-- Expense Account (COA) --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Alokasi Akun Beban (Buku Besar)
                </label>
                <select name="expense_account_id"
                    class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                    <option value="">-- Otomatis sesuai kategori vendor --</option>
                    @foreach($expenseAccounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Subtotal, Tax & Calculated Total --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Subtotal (IDR) <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="number" name="subtotal" x-model.number="apSubtotal" min="1" step="1000" required placeholder="2500000"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] font-bold text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        PPN / Pajak (IDR)
                    </label>
                    <input type="number" name="tax_amount" x-model.number="apTax" min="0" step="1000" placeholder="0"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] font-bold text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Total Kewajiban
                    </label>
                    <div class="w-full h-10 px-3 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 flex items-center text-[13.5px] font-bold text-[#FF3B30] tabular-nums">
                        Rp <span x-text="(Number(apSubtotal || 0) + Number(apTax || 0)).toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </div>

            {{-- Dates --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Tanggal Tagihan <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="bill_date" value="{{ date('Y-m-d') }}" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Jatuh Tempo (Due Date) <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Catatan Tagihan
                </label>
                <textarea name="notes" rows="2" placeholder="{{ __('admin.biaya_perpanjangan_server_tahunan_kuota_token') }}"
                    class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30] resize-none"></textarea>
            </div>

            {{-- Attachment (PDF/Image Faktur Vendor) --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Lampiran Faktur / Invoice Asli Vendor (Opsional)
                </label>
                <input type="file" name="attachment" accept="image/*,application/pdf"
                    class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:py-2 file:px-3 file:rounded-[10px] file:border-0 file:text-[12px] file:font-bold file:bg-[#FF3B30]/10 file:text-[#FF3B30] hover:file:bg-[#FF3B30]/20 cursor-pointer">
            </div>

            {{-- Buttons --}}
            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end gap-2.5">
                <button type="button" @click="showCreateApModal = false"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/5 hover:bg-black/10 transition-colors cursor-pointer">{{ __('common.cancel') }}</button>
                <button type="submit"
                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF3B30] hover:bg-[#D70015] active:scale-[0.98] transition-all shadow-sm shadow-[#FF3B30]/25 cursor-pointer flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Catat &amp; Jurnal Hutang</span>
                </button>
            </div>
        </form>
    </div>
</div>
