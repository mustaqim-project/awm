{{-- MODAL 3: TAMBAH / UBAH BAGAN AKUN (CHART OF ACCOUNT) --}}
{{-- Tambah Akun Modal --}}
<div x-show="showCreateAccountModal" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showCreateAccountModal = false"
        class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden">
        
        <div class="px-6 py-4 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.tambah_akun_pembukuan_baru') }}</h3>
            <button type="button" @click="showCreateAccountModal = false"
                class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/60 dark:text-white/60 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('admin.finance.accounts.store') }}" method="POST" class="p-6 space-y-3.5">
            @csrf

            <div>
                <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                    Kode Akun <span class="text-[#FF3B30]">*</span>
                </label>
                <input type="text" name="code" required placeholder="Contoh: 1160, 5240"
                    class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>

            <div>
                <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                    Nama Akun Perkiraan <span class="text-[#FF3B30]">*</span>
                </label>
                <input type="text" name="name" required placeholder="{{ __('admin.contoh_bank_syariah_indonesia_bsi') }}"
                    class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                        Klasifikasi Tipe <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="type" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                        <option value="asset">Aset / Aktiva</option>
                        <option value="liability">Kewajiban / Hutang</option>
                        <option value="equity">Ekuitas / Modal</option>
                        <option value="revenue">Pendapatan</option>
                        <option value="expense">Beban / Pengeluaran</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                        Posisi Normal <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="normal_balance" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                        <option value="debit">DEBIT</option>
                        <option value="credit">KREDIT</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                    Deskripsi / Kegunaan (Opsional)
                </label>
                <textarea name="description" rows="2" placeholder="{{ __('admin.fungsi_dan_tujuan_akun_ini') }}"
                    class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF] resize-none"></textarea>
            </div>

            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end gap-2.5">
                <button type="button" @click="showCreateAccountModal = false"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.04] cursor-pointer">{{ __('common.cancel') }}</button>
                <button type="submit"
                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-sm shadow-[#007AFF]/25 cursor-pointer">{{ __('admin.tambah_akun') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Ubah Akun Modal --}}
<div x-show="showEditAccountModal" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showEditAccountModal = false"
        class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden">
        
        <div class="px-6 py-4 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div>
                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.ubah_akun_pembukuan') }}</h3>
                <p class="text-[11.5px] text-black/50 dark:text-white/50 font-mono" x-text="editAccount ? editAccount.code : ''"></p>
            </div>
            <button type="button" @click="showEditAccountModal = false"
                class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/60 dark:text-white/60 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form :action="'/admin/finance/accounts/' + (editAccount ? editAccount.id : '')" method="POST" class="p-6 space-y-3.5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                    Nama Akun Perkiraan <span class="text-[#FF3B30]">*</span>
                </label>
                <input type="text" name="name" :value="editAccount ? editAccount.name : ''" required
                    class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
            </div>

            <div>
                <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                    Posisi Normal <span class="text-[#FF3B30]">*</span>
                </label>
                <select name="normal_balance" :value="editAccount ? editAccount.normal_balance : 'debit'" required
                    class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    <option value="debit">DEBIT</option>
                    <option value="credit">KREDIT</option>
                </select>
            </div>

            <div>
                <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                    Deskripsi / Kegunaan (Opsional)
                </label>
                <textarea name="description" rows="2" :value="editAccount ? editAccount.description : ''"
                    class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF] resize-none"></textarea>
            </div>

            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end gap-2.5">
                <button type="button" @click="showEditAccountModal = false"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.04] cursor-pointer">{{ __('common.cancel') }}</button>
                <button type="submit"
                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-sm shadow-[#007AFF]/25 cursor-pointer">{{ __('admin.perbarui_akun') }}</button>
            </div>
        </form>
    </div>
</div>
