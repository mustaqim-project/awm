{{-- TAB 1: BUKU KAS & MUTASI PEMBAYARAN (CASHFLOW) --}}
<div x-show="activeTab === 'cashflow'" class="space-y-4">
    <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-[13px] text-left">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">
                    <tr>
                        <th class="px-5 py-3.5">No. Transaksi &amp; Tanggal</th>
                        <th class="px-4 py-3.5">Tipe &amp; Kategori</th>
                        <th class="px-4 py-3.5">Judul &amp; Referensi</th>
                        <th class="px-4 py-3.5">Metode / Rekening</th>
                        <th class="px-4 py-3.5 text-right">Nominal (IDR)</th>
                        <th class="px-4 py-3.5 text-center">Lampiran Bukti</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                            {{-- 1. No TRX & Tanggal --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-mono text-[13px] font-bold text-black dark:text-white">
                                    {{ $trx->transaction_number }}
                                </div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                    {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}
                                </div>
                            </td>

                            {{-- 2. Tipe & Kategori --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($trx->isInflow())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                        <i data-lucide="arrow-down-left" class="w-3 h-3"></i> Kas Masuk
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]">
                                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i> Kas Keluar
                                    </span>
                                @endif
                                <div class="text-[11.5px] font-medium text-black/60 dark:text-white/60 mt-1">
                                    {{ $categoryLabels[$trx->category] ?? ucfirst(str_replace('_', ' ', $trx->category)) }}
                                </div>
                            </td>

                            {{-- 3. Judul & Keterangan --}}
                            <td class="px-4 py-4 min-w-[220px]">
                                <div class="font-semibold text-black dark:text-white leading-snug line-clamp-1">
                                    {{ $trx->title }}
                                </div>
                                @if($trx->notes)
                                    <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 line-clamp-1">
                                        {{ $trx->notes }}
                                    </div>
                                @endif
                                <div class="flex items-center gap-2 mt-1">
                                    @if($trx->source_type === 'subscription_payment')
                                        <a href="{{ route('admin.subscriptions.show', $trx->source_id) }}"
                                            class="inline-flex items-center gap-1 text-[10.5px] font-bold text-[#007AFF] hover:underline">
                                            <i data-lucide="receipt" class="w-3 h-3"></i> Subscriptions
                                        </a>
                                    @elseif($trx->source_type === 'payment_settlement')
                                        <a href="{{ route('admin.settlements.show', $trx->source_id) }}"
                                            class="inline-flex items-center gap-1 text-[10.5px] font-bold text-[#AF52DE] hover:underline">
                                            <i data-lucide="arrow-down-to-dot" class="w-3 h-3"></i> Settlement
                                        </a>
                                    @else
                                        <span class="text-[10px] text-black/35 dark:text-white/35 font-mono">Manual Entry</span>
                                    @endif
                                    @if($trx->journal_entry_id)
                                        <span class="text-[10px] text-black/30 dark:text-white/30">•</span>
                                        <span class="inline-flex items-center gap-0.5 text-[10.5px] font-mono text-[#34C759]">
                                            <i data-lucide="check" class="w-2.5 h-2.5"></i> Jurnal #{{ $trx->journalEntry?->entry_number }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- 4. Metode Pembayaran --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-[12.5px] font-medium text-black dark:text-white">
                                    {{ $trx->payment_method ?? 'Kas / Transfer' }}
                                </div>
                                <div class="text-[10.5px] text-black/40 dark:text-white/40">
                                    Oleh: {{ $trx->admin?->name ?? 'Sistem Cooca' }}
                                </div>
                            </td>

                            {{-- 5. Nominal (IDR) --}}
                            <td class="px-4 py-4 whitespace-nowrap text-right">
                                <span class="font-mono text-[14px] font-bold {{ $trx->isInflow() ? 'text-[#248A3D] dark:text-[#30D158]' : 'text-[#C41E17] dark:text-[#FF453A]' }} tabular-nums">
                                    {{ $trx->isInflow() ? '+' : '-' }} Rp {{ number_format((float) $trx->amount, 0, ',', '.') }}
                                </span>
                            </td>

                            {{-- 6. Lampiran Gambar (Optional Preview & Lightbox) --}}
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($trx->attachment_url)
                                    <button type="button"
                                        @click="previewImage('{{ $trx->attachment_url }}', '{{ addslashes($trx->title) }} ({{ $trx->transaction_number }})')"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-[11px] font-semibold text-black/70 dark:text-white/70 transition-all cursor-pointer group"
                                        title="Klik untuk melihat bukti gambar kuitansi/struk">
                                        <img src="{{ $trx->attachment_url }}" alt="Thumbnail" class="w-4.5 h-4.5 rounded-[4px] object-cover border border-black/10 dark:border-white/10 shrink-0">
                                        <span class="group-hover:text-[#007AFF]">Lihat Bukti</span>
                                        <i data-lucide="maximize-2" class="w-3 h-3 text-black/40 dark:text-white/40 group-hover:text-[#007AFF]"></i>
                                    </button>
                                @else
                                    <span class="text-[11px] text-black/30 dark:text-white/30 italic">Tanpa Lampiran</span>
                                @endif
                            </td>

                            {{-- 7. Aksi Edit / Hapus --}}
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.manage'))
                                        <button type="button"
                                            @click="openEdit({{ json_encode($trx) }})"
                                            class="w-8 h-8 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center transition-all cursor-pointer"
                                            title="Ubah Transaksi">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        </button>

                                        <button type="button"
                                            @click="openDelete({{ json_encode($trx) }})"
                                            class="w-8 h-8 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-[#FF3B30]/15 text-black/60 dark:text-white/60 hover:text-[#FF3B30] flex items-center justify-center transition-all cursor-pointer"
                                            title="Hapus Transaksi">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-black/40 dark:text-white/40">
                                <div class="w-12 h-12 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.05] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="inbox" class="w-6 h-6"></i>
                                </div>
                                <div class="text-[14px] font-bold text-black/70 dark:text-white/70">Belum ada transaksi pada periode ini</div>
                                <p class="text-[12px] text-black/45 dark:text-white/45 mt-1 max-w-sm mx-auto">
                                    Gunakan tombol "Catat Transaksi" untuk memasukkan transaksi manual atau "Sinkronisasi Otomatis" untuk menarik mutasi billing &amp; settlement.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($transactions->hasPages())
            <div class="px-5 py-3 border-t border-black/5 dark:border-white/5 bg-black/[0.01] dark:bg-white/[0.01]">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
