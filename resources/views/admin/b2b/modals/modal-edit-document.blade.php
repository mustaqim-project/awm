{{-- MODAL EDIT B2B DOCUMENT --}}
<div x-show="showEditModal" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showEditModal = false"
        class="w-full max-w-lg rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden my-8">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-[#6366f1]/15 text-[#6366f1] flex items-center justify-center">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white" x-text="'Ubah ' + (editDoc ? editDoc.number : 'Dokumen')"></h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50" x-text="'Perbarui status dan data dokumen B2B'"></p>
                </div>
            </div>
            <button type="button" @click="showEditModal = false"
                class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/60 dark:text-white/60 hover:bg-black/10 transition-colors cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Form --}}
        <form :action="editDocUrl" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            {{-- 1. Fields for Quotation --}}
            <template x-if="editDocType === 'quotation'">
                <div class="space-y-3.5">
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Status Penawaran</label>
                        <select name="status" :value="editDoc ? editDoc.status : 'draft'"
                            class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
                            <option value="draft">Draf (Draft)</option>
                            <option value="sent">Terkirim (Sent)</option>
                            <option value="accepted">Disetujui (Accepted)</option>
                            <option value="rejected">Ditolak (Rejected)</option>
                            <option value="cancelled">Dibatalkan (Cancelled)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Tanggal</label>
                            <input type="date" name="date" :value="editDoc ? editDoc.date : ''" required
                                class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Masa Berlaku</label>
                            <input type="date" name="expiry_date" :value="editDoc ? editDoc.expiry_date : ''"
                                class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Catatan</label>
                        <textarea name="notes" rows="2" :value="editDoc ? editDoc.notes : ''"
                            class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#6366f1]"></textarea>
                    </div>
                </div>
            </template>

            {{-- 2. Fields for Sales Order --}}
            <template x-if="editDocType === 'order'">
                <div class="space-y-3.5">
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Status Pesanan (SO)</label>
                        <select name="status" :value="editDoc ? editDoc.status : 'confirmed'"
                            class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#2563eb]">
                            <option value="draft">Draf (Draft)</option>
                            <option value="confirmed">Dikonfirmasi (Confirmed)</option>
                            <option value="partially_fulfilled">Dipenuhi Sebagian</option>
                            <option value="fulfilled">Selesai / Terpenuhi (Fulfilled)</option>
                            <option value="cancelled">Dibatalkan (Cancelled)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Estimasi Pengiriman</label>
                        <input type="date" name="expected_delivery_date" :value="editDoc ? editDoc.expected_delivery_date : ''"
                            class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#2563eb]">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Alamat Pengiriman</label>
                        <input type="text" name="shipping_address" :value="editDoc ? editDoc.shipping_address : ''"
                            class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#2563eb]">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Catatan Pesanan</label>
                        <textarea name="notes" rows="2" :value="editDoc ? editDoc.notes : ''"
                            class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#2563eb]"></textarea>
                    </div>
                </div>
            </template>

            {{-- 3. Fields for Invoice --}}
            <template x-if="editDocType === 'invoice'">
                <div class="space-y-3.5">
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Tanggal Jatuh Tempo</label>
                        <input type="date" name="due_date" :value="editDoc ? editDoc.due_date : ''" required
                            class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Termin Pembayaran</label>
                        <input type="text" name="payment_terms" :value="editDoc ? editDoc.payment_terms : ''"
                            class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Catatan Faktur</label>
                        <textarea name="notes" rows="2" :value="editDoc ? editDoc.notes : ''"
                            class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0284c7]"></textarea>
                    </div>
                </div>
            </template>

            {{-- 4. Fields for Return --}}
            <template x-if="editDocType === 'return'">
                <div class="space-y-3.5">
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Alasan Retur</label>
                        <input type="text" name="reason" :value="editDoc ? editDoc.reason : ''" required
                            class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#d97706]">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Metode Kompensasi / Refund</label>
                        <select name="refund_method" :value="editDoc ? editDoc.refund_method : 'credit_note'"
                            class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#d97706]">
                            <option value="credit_note">Nota Kredit (Credit Note)</option>
                            <option value="bank_transfer">Transfer Bank Pengembalian Dana</option>
                            <option value="cash">Pengembalian Kas Tunai</option>
                        </select>
                    </div>
                </div>
            </template>

            {{-- Actions --}}
            <div class="pt-2 flex items-center justify-end gap-3 border-t border-black/5 dark:border-white/5">
                <button type="button" @click="showEditModal = false"
                    class="px-4 py-2 rounded-[12px] bg-black/5 dark:bg-white/5 hover:bg-black/10 text-[13px] font-medium text-black/70 dark:text-white/70 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-[12px] bg-[#0284c7] hover:bg-[#0369a1] text-white text-[13.5px] font-semibold shadow-lg shadow-[#0284c7]/20 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
