{{-- MODAL CREATE ACCOUNTS RECEIVABLE INVOICE --}}
<div x-show="showCreateArModal" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showCreateArModal = false"
        class="w-full max-w-xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden">
        
        {{-- Header --}}
        <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Terbitkan Invoice Piutang (AR)</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Tagihan piutang tenant/klien korporat dengan auto-posting ke Akun 1150</p>
                </div>
            </div>
            <button type="button" @click="showCreateArModal = false"
                class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/60 dark:text-white/60 hover:bg-black/10 transition-colors cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Form --}}
        <form action="{{ route('admin.finance.receivables.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4"
            x-data="{
                arSubtotal: 0,
                arTax: 0,
                selectedBusiness: '',
                businessesList: {{ json_encode($businesses) }},
                onBusinessChange() {
                    if (!this.selectedBusiness) return;
                    const b = this.businessesList.find(x => x.id === this.selectedBusiness);
                    if (b) {
                        this.$refs.clientName.value = b.name;
                        if (b.email) this.$refs.clientEmail.value = b.email;
                        if (b.phone) this.$refs.clientPhone.value = b.phone;
                    }
                }
            }">
            @csrf

            {{-- Tenant Picker or Manual Client --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Pilih Tenant Terdaftar (Opsional)
                    </label>
                    <select name="business_id" x-model="selectedBusiness" @change="onBusinessChange()"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <option value="">-- Klien Baru / Luar Platform --</option>
                        @foreach($businesses as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Nama Klien / Perusahaan <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="text" name="client_name" x-ref="clientName" required placeholder="PT Contoh Digital Nusantara"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>
            </div>

            {{-- Email & Phone --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Email Klien (Opsional)
                    </label>
                    <input type="email" name="client_email" x-ref="clientEmail" placeholder="billing@klien.com"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        No. Telepon / WhatsApp
                    </label>
                    <input type="text" name="client_phone" x-ref="clientPhone" placeholder="0812xxxxxxxx"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>
            </div>

            {{-- Category & Title --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Kategori Tagihan <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="category" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <option value="subscription">Langganan SaaS Paket Bulanan</option>
                        <option value="enterprise_annual">Kontrak Enterprise Tahunan</option>
                        <option value="custom_setup">Custom Setup &amp; Onboarding</option>
                        <option value="consulting">Konsultasi Bisnis &amp; POS Setup</option>
                        <option value="other">Tagihan Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Perihal / Judul Invoice <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="text" name="title" required placeholder="Paket Scale 12 Bulan + Setup 5 Cabang"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>
            </div>

            {{-- Subtotal, Tax & Calculated Total --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Subtotal (IDR) <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="number" name="subtotal" x-model.number="arSubtotal" min="1" step="1000" required placeholder="5000000"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] font-bold text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        PPN / Pajak (IDR)
                    </label>
                    <input type="number" name="tax_amount" x-model.number="arTax" min="0" step="1000" placeholder="0"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] font-bold text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Total Tagihan
                    </label>
                    <div class="w-full h-10 px-3 rounded-[12px] bg-[#007AFF]/10 border border-[#007AFF]/25 flex items-center text-[13.5px] font-bold text-[#007AFF] tabular-nums">
                        Rp <span x-text="(Number(arSubtotal || 0) + Number(arTax || 0)).toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </div>

            {{-- Dates --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Tanggal Invoice <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Jatuh Tempo (Due Date) <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+14 days')) }}" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Catatan / Syarat Pembayaran
                </label>
                <textarea name="notes" rows="2" placeholder="Term of Payment 14 Hari kerja. Transfer ke Rekening BCA PT Cooca Digital Solusi..."
                    class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"></textarea>
            </div>

            {{-- Attachment (PDF/Image) --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Lampiran Kontrak / PO / Dokumen Penagihan (Opsional)
                </label>
                <input type="file" name="attachment" accept="image/*,application/pdf"
                    class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:py-2 file:px-3 file:rounded-[10px] file:border-0 file:text-[12px] file:font-bold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20 cursor-pointer">
            </div>

            {{-- Buttons --}}
            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end gap-2.5">
                <button type="button" @click="showCreateArModal = false"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/5 hover:bg-black/10 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-sm shadow-[#007AFF]/25 cursor-pointer flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Terbitkan &amp; Jurnal Otomatis</span>
                </button>
            </div>
        </form>
    </div>
</div>
