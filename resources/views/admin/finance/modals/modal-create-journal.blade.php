{{-- MODAL 2: INPUT JURNAL UMUM MANUAL (DOUBLE-ENTRY) --}}
<div x-show="showCreateJournalModal" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showCreateJournalModal = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="w-full max-w-2xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden"
        x-data="{
            journalDate: '{{ date('Y-m-d') }}',
            journalDesc: '',
            lines: [
                { account_id: '{{ $allAccounts->firstWhere('code', '1120')?->id ?? $allAccounts->first()?->id }}', type: 'debit', amount: '', notes: '' },
                { account_id: '{{ $allAccounts->firstWhere('code', '4110')?->id ?? $allAccounts->skip(1)->first()?->id }}', type: 'credit', amount: '', notes: '' }
            ],
            addLine() {
                this.lines.push({ account_id: '{{ $allAccounts->first()?->id }}', type: 'credit', amount: '', notes: '' });
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            },
            removeLine(index) {
                if (this.lines.length > 2) {
                    this.lines.splice(index, 1);
                }
            },
            totalDebit() {
                return this.lines.reduce((sum, line) => {
                    return line.type === 'debit' ? sum + (parseFloat(line.amount) || 0) : sum;
                }, 0);
            },
            totalCredit() {
                return this.lines.reduce((sum, line) => {
                    return line.type === 'credit' ? sum + (parseFloat(line.amount) || 0) : sum;
                }, 0);
            },
            isBalanced() {
                return Math.abs(this.totalDebit() - this.totalCredit()) < 0.01 && this.totalDebit() > 0;
            }
        }">
        
        <div class="px-6 py-4 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div>
                <h3 class="text-[16px] font-bold text-black dark:text-white">Input Jurnal Umum Manual</h3>
                <p class="text-[11.5px] text-black/50 dark:text-white/50">Mencatat jurnal penyesuaian, akrual, atau transaksi khusus double-entry</p>
            </div>
            <button type="button" @click="showCreateJournalModal = false"
                class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('admin.finance.journals.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                        Tanggal Jurnal <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="entry_date" x-model="journalDate" required
                        class="w-full h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1">
                        Keterangan Jurnal <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="text" name="description" x-model="journalDesc" required placeholder="Contoh: Jurnal Penyesuaian Beban Server Belum Tertagih"
                        class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                </div>
            </div>

            {{-- DAFTAR BARIS JURNAL (DEBIT & KREDIT) --}}
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <label class="text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                        Baris Akun Pembukuan (Minimal 2 Baris) <span class="text-[#FF3B30]">*</span>
                    </label>
                    <button type="button" @click="addLine()"
                        class="text-[12px] font-bold text-[#007AFF] hover:underline flex items-center gap-1 cursor-pointer">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Tambah Baris</span>
                    </button>
                </div>

                <div class="space-y-2 max-h-[260px] overflow-y-auto pr-1">
                    <template x-for="(line, index) in lines" :key="index">
                        <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                            <div class="grid grid-cols-12 gap-2 items-center">
                                {{-- Akun Selector --}}
                                <div class="col-span-12 sm:col-span-5">
                                    <select :name="'lines[' + index + '][account_id]'" x-model="line.account_id" required
                                        class="w-full h-9 px-2.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                        @foreach($allAccounts as $acc)
                                            <option value="{{ $acc->id }}">
                                                {{ $acc->code }} - {{ $acc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Tipe Posisi (Debit / Kredit) --}}
                                <div class="col-span-4 sm:col-span-2">
                                    <select :name="'lines[' + index + '][type]'" x-model="line.type" required
                                        class="w-full h-9 px-2 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[12px] font-bold uppercase text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                        <option value="debit">DEBIT</option>
                                        <option value="credit">KREDIT</option>
                                    </select>
                                </div>

                                {{-- Nominal Jumlah --}}
                                <div class="col-span-7 sm:col-span-4">
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-2 text-[11px] font-bold text-black/40 dark:text-white/40">Rp</span>
                                        <input type="number" :name="'lines[' + index + '][amount]'" x-model="line.amount" min="0.01" step="any" required placeholder="0"
                                            class="w-full h-9 pl-8 pr-2.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[12px] font-mono font-bold text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                    </div>
                                </div>

                                {{-- Hapus Baris --}}
                                <div class="col-span-1 text-center">
                                    <button type="button" @click="removeLine(index)" :disabled="lines.length <= 2"
                                        class="w-7 h-7 rounded-[8px] text-black/40 hover:text-[#FF3B30] disabled:opacity-20 flex items-center justify-center cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Catatan Baris Opsional --}}
                            <input type="text" :name="'lines[' + index + '][notes]'" x-model="line.notes" placeholder="Catatan baris khusus (opsional)..."
                                class="w-full h-7.5 px-2.5 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 text-[11.5px] text-black/70 dark:text-white/70 focus:outline-none">
                        </div>
                    </template>
                </div>
            </div>

            {{-- KESEIMBANGAN DEBIT VS KREDIT (CALCULATOR CARD) --}}
            <div class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 flex items-center justify-between font-mono text-[13px]">
                <div class="flex items-center gap-4">
                    <div>
                        <div class="text-[10px] font-sans uppercase font-bold text-black/50 dark:text-white/50">Total Debit</div>
                        <div class="font-bold text-[#007AFF] tabular-nums" x-text="'Rp ' + totalDebit().toLocaleString('id-ID')"></div>
                    </div>
                    <span class="text-black/30 dark:text-white/30">=</span>
                    <div>
                        <div class="text-[10px] font-sans uppercase font-bold text-black/50 dark:text-white/50">Total Kredit</div>
                        <div class="font-bold text-[#007AFF] tabular-nums" x-text="'Rp ' + totalCredit().toLocaleString('id-ID')"></div>
                    </div>
                </div>

                <div class="text-right">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold"
                        :class="isBalanced() ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]'">
                        <span x-text="isBalanced() ? 'Seimbang (Valid)' : 'Selisih Rp ' + Math.abs(totalDebit() - totalCredit()).toLocaleString('id-ID')"></span>
                    </span>
                </div>
            </div>

            {{-- Footer --}}
            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end gap-2.5">
                <button type="button" @click="showCreateJournalModal = false"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] cursor-pointer">
                    Batal
                </button>
                <button type="submit" :disabled="!isBalanced()"
                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-40 disabled:cursor-not-allowed active:scale-[0.98] shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                    Simpan Jurnal Umum
                </button>
            </div>
        </form>
    </div>
</div>
