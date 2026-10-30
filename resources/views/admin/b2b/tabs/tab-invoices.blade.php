{{-- TAB 3: FAKTUR PENJUALAN (COMMERCIAL INVOICES) --}}
<div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
    <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="text-[15px] font-bold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4 text-[#0284c7]"></i>
                <span>Daftar Faktur Penjualan Komersial (Invoices &amp; AR)</span>
            </h3>
            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                Pengawasan penagihan komersial, termin pembayaran (TOP), rekonsiliasi kas masuk, dan pencatatan pembayaran cicilan dengan bukti transfer.
            </p>
        </div>
        @if(Auth::guard('admin')->user()?->hasPermission('admin.b2b.create'))
            <button type="button" @click="showCreateInvoiceModal = true"
                class="h-9 px-3.5 rounded-[11px] bg-[#0284c7] hover:bg-[#0369a1] active:scale-[0.98] text-white text-[12px] font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer shrink-0">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Terbitkan Faktur</span>
            </button>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">
                    <th class="py-3 px-4">No. Faktur &amp; Tgl</th>
                    <th class="py-3 px-4">Bisnis / Tenant</th>
                    <th class="py-3 px-4">Klien / Pembeli</th>
                    <th class="py-3 px-4">Jatuh Tempo (TOP)</th>
                    <th class="py-3 px-4 text-right">Total Tagihan</th>
                    <th class="py-3 px-4 text-right">Sisa Piutang</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Aksi Dokumen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5 dark:divide-white/5 text-[12.5px]">
                @forelse($invoices as $inv)
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                        {{-- No Faktur & Tanggal --}}
                        <td class="py-3.5 px-4">
                            <div class="font-mono font-bold text-black dark:text-white text-[12.5px]">
                                {{ $inv->invoice_number }}
                            </div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                {{ \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') }}
                            </div>
                            @if($inv->salesOrder)
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-mono text-[#007AFF] bg-[#007AFF]/10 px-1.5 py-0.2 rounded" title="Nomor Pesanan SO Rujukan">
                                        SO: {{ $inv->salesOrder->so_number }}
                                    </span>
                                </div>
                            @endif
                        </td>

                        {{-- Bisnis / Tenant --}}
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-black dark:text-white">
                                {{ $inv->business?->name ?? 'Platform Cooca' }}
                            </div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">
                                {{ $inv->business?->email ?? '-' }}
                            </div>
                        </td>

                        {{-- Klien / Customer --}}
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-black dark:text-white">
                                {{ $inv->customer?->name ?? $inv->recipient_name }}
                            </div>
                            @if($inv->customer?->company_name)
                                <div class="text-[11px] text-[#007AFF] font-medium">
                                    {{ $inv->customer->company_name }}
                                </div>
                            @endif
                            <div class="text-[11px] text-black/45 dark:text-white/45">
                                {{ $inv->customer?->phone ?? '-' }}
                            </div>
                        </td>

                        {{-- Jatuh Tempo (Due Date) --}}
                        <td class="py-3.5 px-4">
                            <div class="font-medium {{ \Carbon\Carbon::parse($inv->due_date)->isPast() && (float) $inv->balance_due > 0.01 ? 'text-[#FF3B30] font-bold' : 'text-black dark:text-white' }}">
                                {{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}
                            </div>
                            <div class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5">
                                {{ $inv->payment_terms ?? 'Transfer Bank' }}
                            </div>
                        </td>

                        {{-- Total Nilai Tagihan --}}
                        <td class="py-3.5 px-4 text-right">
                            <div class="font-bold text-black dark:text-white tabular-nums">
                                Rp {{ number_format((float) $inv->total_amount, 0, ',', '.') }}
                            </div>
                            @if((float) $inv->paid_amount > 0)
                                <div class="text-[11px] text-[#248A3D] dark:text-[#30D158] tabular-nums mt-0.5">
                                    Bayar: Rp {{ number_format((float) $inv->paid_amount, 0, ',', '.') }}
                                </div>
                            @endif
                        </td>

                        {{-- Sisa Piutang (Balance Due) --}}
                        <td class="py-3.5 px-4 text-right">
                            <div class="text-[13px] font-bold {{ (float) $inv->balance_due > 0.01 ? 'text-[#FF3B30]' : 'text-[#34C759]' }} tabular-nums">
                                Rp {{ number_format((float) $inv->balance_due, 0, ',', '.') }}
                            </div>
                            @if($inv->payments->count() > 0)
                                <div class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5">
                                    {{ $inv->payments->count() }}x Pembayaran
                                </div>
                            @endif
                        </td>

                        {{-- Status Badge --}}
                        <td class="py-3.5 px-4 text-center">
                            @if($inv->status === 'paid')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                    Lunas
                                </span>
                            @elseif($inv->status === 'partially_paid')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]">
                                    Sebagian
                                </span>
                            @elseif($inv->status === 'overdue')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]">
                                    Overdue
                                </span>
                            @elseif($inv->status === 'unpaid' || $inv->status === 'sent')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF]">
                                    Belum Lunas
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">
                                    {{ ucfirst($inv->status) }}
                                </span>
                            @endif
                        </td>

                        {{-- Aksi Dokumen --}}
                        <td class="py-3.5 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- Tombol PDF --}}
                                <a href="{{ route('admin.b2b.invoices.pdf', $inv) }}" target="_blank"
                                    class="h-8 px-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-[#0284c7]/10 hover:text-[#0284c7] text-black/70 dark:text-white/70 text-[11.5px] font-bold transition-all inline-flex items-center gap-1"
                                    title="Unduh / Cetak Faktur Penjualan Komersial PDF (A4)" rel="noopener noreferrer">
                                    <i data-lucide="file-down" class="w-3.5 h-3.5"></i>
                                    <span>PDF</span>
                                </a>

                                {{-- Ubah Faktur --}}
                                @if(Auth::guard('admin')->user()?->hasPermission('admin.b2b.edit'))
                                    <button type="button" @click="openEdit('invoice', {
                                        number: '{{ $inv->invoice_number }}',
                                        due_date: '{{ \Carbon\Carbon::parse($inv->due_date)->format('Y-m-d') }}',
                                        payment_terms: '{{ addslashes($inv->payment_terms ?? '') }}',
                                        notes: '{{ addslashes($inv->notes ?? '') }}'
                                    }, '{{ route('admin.b2b.invoices.update', $inv) }}')"
                                        class="h-8 w-8 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-[#0284c7]/15 hover:text-[#0284c7] text-black/70 dark:text-white/70 transition-all inline-flex items-center justify-center cursor-pointer"
                                        title="Ubah Faktur">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                @endif

                                {{-- Catat Pembayaran jika belum lunas --}}
                                @if((float) $inv->balance_due > 0.01 && Auth::guard('admin')->user()?->hasPermission('admin.b2b.manage'))
                                    <button type="button" @click="openPayment(@js($inv))"
                                        class="h-8 px-2.5 rounded-[9px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-[11.5px] font-bold transition-all shadow-xs cursor-pointer inline-flex items-center gap-1"
                                        title="Catat Pembayaran Masuk">
                                        <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                                        <span>Catat Bayar</span>
                                    </button>
                                @endif

                                {{-- Void Faktur jika belum ada pembayaran dan belum void --}}
                                @if($inv->status !== 'void' && (float) $inv->paid_amount < 0.01 && Auth::guard('admin')->user()?->hasPermission('admin.b2b.edit'))
                                    <form action="{{ route('admin.b2b.invoices.void', $inv) }}" method="POST" class="inline"
                                        data-confirm="Batalkan (Void) Faktur {{ $inv->invoice_number }}?"
                                        onsubmit="return confirm(this.dataset.confirm);">
                                        @csrf
                                        <button type="submit"
                                            class="h-8 w-8 rounded-[9px] bg-[#FF9500]/10 hover:bg-[#FF9500]/20 text-[#B25E00] dark:text-[#FF9F0A] transition-all inline-flex items-center justify-center cursor-pointer"
                                            title="Batalkan (Void) Faktur">
                                            <i data-lucide="ban" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Hapus Faktur jika tidak ada pembayaran --}}
                                @if((float) $inv->paid_amount < 0.01 && Auth::guard('admin')->user()?->hasPermission('admin.b2b.delete'))
                                    <form action="{{ route('admin.b2b.invoices.destroy', $inv) }}" method="POST" class="inline"
                                        data-confirm="Hapus Faktur {{ $inv->invoice_number }}?"
                                        onsubmit="return confirm(this.dataset.confirm);">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="h-8 w-8 rounded-[9px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] transition-all inline-flex items-center justify-center cursor-pointer"
                                            title="Hapus Faktur">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                            <i data-lucide="receipt" class="w-10 h-10 mx-auto stroke-[1.2] opacity-40 mb-2"></i>
                            <div class="text-[13px] font-semibold">Tidak Ada Faktur Penjualan Komersial</div>
                            <div class="text-[12px] text-black/35 dark:text-white/35 mt-0.5">
                                Faktur penagihan yang diterbitkan akan tampil di sini.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices && $invoices->hasPages())
        <div class="p-4 border-t border-black/5 dark:border-white/5">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
