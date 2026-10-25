{{-- MODAL 1: CATAT TRANSAKSI KAS BARU (INFLOW / OUTFLOW) --}}
<div x-show="showCreateModal" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showCreateModal = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="w-full max-w-lg rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden">
        
        <div class="px-6 py-4 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div>
                <h3 class="text-[16px] font-bold text-black dark:text-white">Catat Transaksi Kas Platform</h3>
                <p class="text-[11.5px] text-black/50 dark:text-white/50">Mencatat mutasi kas dan menjurnal otomatis ke buku besar</p>
            </div>
            <button type="button" @click="showCreateModal = false"
                class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('admin.finance.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf

            {{-- 1. Jenis Mutasi (Inflow vs Outflow) --}}
            <div>
                <label class="block text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1.5">
                    Jenis Mutasi Kas <span class="text-[#FF3B30]">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2 p-1 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06]">
                    <label class="flex items-center justify-center gap-2 py-2 rounded-[9px] text-[13px] font-bold cursor-pointer transition-all has-[:checked]:bg-[#34C759] has-[:checked]:text-white text-black/70 dark:text-white/70">
                        <input type="radio" name="type" value="in" class="sr-only" checked>
                        <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                        <span>Kas Masuk (+)</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 py-2 rounded-[9px] text-[13px] font-bold cursor-pointer transition-all has-[:checked]:bg-[#FF3B30] has-[:checked]:text-white text-black/70 dark:text-white/70">
                        <input type="radio" name="type" value="out" class="sr-only">
                        <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                        <span>Kas Keluar (-)</span>
                    </label>
                </div>
            </div>

            {{-- 2. Kategori & Tanggal Transaksi --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                        Kategori <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="category" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                        <optgroup label="Penerimaan Kas (Inflow)">
                            <option value="subscription_revenue">Pendapatan Langganan SaaS</option>
                            <option value="ai_token_revenue">Penjualan Kuota Token AI</option>
                            <option value="setup_fee">Biaya Onboarding &amp; Setup</option>
                            <option value="other_income">Penerimaan Kas Lainnya</option>
                        </optgroup>
                        <optgroup label="Pengeluaran Kas (Outflow)">
                            <option value="merchant_settlement">Pencairan Saldo Merchant</option>
                            <option value="infrastructure">Server, VPS &amp; Hosting</option>
                            <option value="ai_api_cost">Biaya API LLM (Gemini / Claude)</option>
                            <option value="whatsapp_gateway">Meta WhatsApp Cloud API</option>
                            <option value="salary">Gaji &amp; Insentif Tim</option>
                            <option value="marketing">Pemasaran &amp; Iklan</option>
                            <option value="operational" selected>Operasional Platform</option>
                            <option value="other_expense">Pengeluaran Lainnya</option>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                        Tanggal Transaksi <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                </div>
            </div>

            {{-- 3. Judul / Deskripsi Transaksi --}}
            <div>
                <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                    Judul / Uraian Transaksi <span class="text-[#FF3B30]">*</span>
                </label>
                <input type="text" name="title" required placeholder="Contoh: Pembayaran Server VPS Hostinger Oktober"
                    class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>

            {{-- 4. Nominal (IDR) & Metode Pembayaran --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                        Jumlah (IDR) <span class="text-[#FF3B30]">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-[13px] font-bold text-black/40 dark:text-white/40">Rp</span>
                        <input type="number" name="amount" min="1" step="any" required placeholder="0"
                            class="w-full h-10 pl-10 pr-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] font-mono font-bold text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                        Metode / Rekening
                    </label>
                    <select name="payment_method"
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                        <option value="BCA Transfer">Bank BCA Platform</option>
                        <option value="Mandiri Transfer">Bank Mandiri Platform</option>
                        <option value="QRIS / TriPay">TriPay Payment Gateway</option>
                        <option value="Kas Tunai">Kas Tunai Platform</option>
                        <option value="Kartu Kredit">Kartu Kredit Perusahaan</option>
                    </select>
                </div>
            </div>

            {{-- 5. Catatan Tambahan --}}
            <div>
                <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                    Catatan Internal (Opsional)
                </label>
                <textarea name="notes" rows="2" placeholder="Catatan nomor invoice, persetujuan direksi, atau nomor referensi..."
                    class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF] resize-none"></textarea>
            </div>

            {{-- 6. Upload Lampiran Bukti Gambar (Optional) --}}
            <div>
                <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                    Lampiran Bukti Kuitansi / Struk / Invoice (Opsional)
                </label>
                <div class="flex items-center gap-3">
                    <label class="h-10 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] border border-dashed border-black/20 dark:border-white/20 text-[12.5px] font-semibold text-black/75 dark:text-white/75 flex items-center justify-center gap-2 cursor-pointer transition-all">
                        <i data-lucide="image" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Pilih Gambar</span>
                        <input type="file" name="attachment" accept="image/*,.pdf" class="sr-only" @change="handleFileSelect($event, 'create')">
                    </label>
                    <span class="text-[11px] text-black/45 dark:text-white/45">JPG, PNG, WEBP, PDF (Maks. 5MB)</span>
                </div>

                {{-- Live Image Preview --}}
                <template x-if="createImagePreview">
                    <div class="mt-2.5 p-2 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <img :src="createImagePreview" alt="Preview" class="w-10 h-10 rounded-[8px] object-cover border border-black/10 dark:border-white/10">
                            <span class="text-[12px] font-semibold text-black dark:text-white truncate">Berkas gambar terpilih</span>
                        </div>
                        <button type="button" @click="createImagePreview = null" class="text-[11px] font-bold text-[#FF3B30] hover:underline px-2">Batal</button>
                    </div>
                </template>
            </div>

            {{-- Footer Tombol Aksi --}}
            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end gap-2.5">
                <button type="button" @click="showCreateModal = false"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                    Simpan &amp; Jurnal Otomatis
                </button>
            </div>
        </form>
    </div>
</div>
