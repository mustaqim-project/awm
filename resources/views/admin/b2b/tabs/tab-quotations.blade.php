{{-- TAB 1: SURAT PENAWARAN (QUOTATIONS) --}}
<div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
    <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="text-[15px] font-bold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Daftar Surat Penawaran Harga (Sales Quotations)</span>
            </h3>
            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                Monitoring seluruh dokumen penawaran harga pra-transaksi B2B, batas masa berlaku, dan konversi ke Pesanan Penjualan (SO).
            </p>
        </div>
        @if(Auth::guard('admin')->user()?->hasPermission('admin.b2b.create'))
            <button type="button" @click="showCreateQuotationModal = true"
                class="h-9 px-3.5 rounded-[11px] bg-[#6366f1] hover:bg-[#4f46e5] active:scale-[0.98] text-white text-[12px] font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer shrink-0">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Buat Penawaran</span>
            </button>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">
                    <th class="py-3 px-4">No. Penawaran &amp; Tgl</th>
                    <th class="py-3 px-4">Bisnis / Tenant</th>
                    <th class="py-3 px-4">Klien / Calon Pembeli</th>
                    <th class="py-3 px-4">Perihal &amp; Petugas</th>
                    <th class="py-3 px-4">Masa Berlaku</th>
                    <th class="py-3 px-4 text-right">Nilai Total</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Aksi Dokumen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5 dark:divide-white/5 text-[12.5px]">
                @forelse($quotations as $q)
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                        {{-- No Penawaran & Tanggal --}}
                        <td class="py-3.5 px-4">
                            <div class="font-mono font-bold text-black dark:text-white text-[12.5px]">
                                {{ $q->quotation_number }}
                            </div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                {{ \Carbon\Carbon::parse($q->date)->format('d M Y') }}
                            </div>
                            @if($q->salesOrder)
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-mono font-semibold text-[#34C759] bg-[#34C759]/12 px-1.5 py-0.2 rounded" title="Telah Dikonversi ke SO">
                                        <i data-lucide="check" class="w-2.5 h-2.5"></i>
                                        SO: {{ $q->salesOrder->so_number }}
                                    </span>
                                </div>
                            @endif
                        </td>

                        {{-- Bisnis / Tenant --}}
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-black dark:text-white">
                                {{ $q->business?->name ?? 'Platform Cooca' }}
                            </div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">
                                {{ $q->business?->email ?? '-' }}
                            </div>
                        </td>

                        {{-- Klien / Customer --}}
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-black dark:text-white">
                                {{ $q->customer?->name ?? $q->recipient_name }}
                            </div>
                            @if($q->customer?->company_name)
                                <div class="text-[11px] text-[#007AFF] font-medium">
                                    {{ $q->customer->company_name }}
                                </div>
                            @endif
                            <div class="text-[11px] text-black/45 dark:text-white/45">
                                {{ $q->customer?->phone ?? '-' }}
                            </div>
                        </td>

                        {{-- Perihal & Creator --}}
                        <td class="py-3.5 px-4 max-w-xs">
                            <div class="text-[12px] text-black/80 dark:text-white/80 line-clamp-1">
                                {{ $q->attachment_info ?? 'Penawaran Solusi Bisnis' }}
                            </div>
                            <div class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5">
                                Disiapkan: {{ $q->creator?->name ?? 'Sales' }}
                            </div>
                        </td>

                        {{-- Masa Berlaku --}}
                        <td class="py-3.5 px-4">
                            @if($q->expiry_date)
                                <div class="font-medium {{ \Carbon\Carbon::parse($q->expiry_date)->isPast() && !in_array($q->status, ['accepted', 'cancelled']) ? 'text-[#FF3B30] font-bold' : 'text-black dark:text-white' }}">
                                    {{ \Carbon\Carbon::parse($q->expiry_date)->format('d M Y') }}
                                </div>
                                @if(\Carbon\Carbon::parse($q->expiry_date)->isPast() && !in_array($q->status, ['accepted', 'cancelled']))
                                    <div class="text-[10px] text-[#FF3B30] font-semibold mt-0.5">Sudah Kadaluarsa</div>
                                @endif
                            @else
                                <span class="text-black/40 dark:text-white/40">-</span>
                            @endif
                        </td>

                        {{-- Total Nilai --}}
                        <td class="py-3.5 px-4 text-right font-bold text-black dark:text-white tabular-nums">
                            Rp {{ number_format((float) $q->total_amount, 0, ',', '.') }}
                        </td>

                        {{-- Status Badge --}}
                        <td class="py-3.5 px-4 text-center">
                            @if($q->status === 'accepted')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                    Diterima
                                </span>
                            @elseif($q->status === 'sent')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF]">
                                    Terkirim
                                </span>
                            @elseif($q->status === 'draft')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                    Draf
                                </span>
                            @elseif($q->status === 'rejected')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]">
                                    Ditolak
                                </span>
                            @elseif($q->status === 'expired')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]">
                                    Kadaluarsa
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">
                                    {{ ucfirst($q->status) }}
                                </span>
                            @endif
                        </td>

                        {{-- Aksi Dokumen --}}
                        <td class="py-3.5 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- Tombol PDF --}}
                                <a href="{{ route('admin.b2b.quotations.pdf', $q) }}" target="_blank"
                                    class="h-8 px-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-[#007AFF]/10 hover:text-[#007AFF] text-black/70 dark:text-white/70 text-[11.5px] font-bold transition-all inline-flex items-center gap-1"
                                    title="Unduh / Cetak Surat Penawaran Resmi PDF (A4)">
                                    <i data-lucide="file-down" class="w-3.5 h-3.5"></i>
                                    <span>PDF</span>
                                </a>

                                {{-- Ubah Penawaran --}}
                                @if(Auth::guard('admin')->user()?->hasPermission('admin.b2b.edit'))
                                    <button type="button" @click="openEdit('quotation', {
                                        number: '{{ $q->quotation_number }}',
                                        status: '{{ $q->status }}',
                                        date: '{{ \Carbon\Carbon::parse($q->date)->format('Y-m-d') }}',
                                        expiry_date: '{{ $q->expiry_date ? \Carbon\Carbon::parse($q->expiry_date)->format('Y-m-d') : '' }}',
                                        notes: '{{ addslashes($q->notes ?? '') }}'
                                    }, '{{ route('admin.b2b.quotations.update', $q) }}')"
                                        class="h-8 w-8 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-[#6366f1]/15 hover:text-[#6366f1] text-black/70 dark:text-white/70 transition-all inline-flex items-center justify-center cursor-pointer"
                                        title="Ubah Penawaran">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                @endif

                                {{-- Konversi ke SO jika belum konversi --}}
                                @if(!$q->salesOrder && Auth::guard('admin')->user()?->hasPermission('admin.b2b.manage'))
                                    <form action="{{ route('admin.b2b.quotations.convert', $q) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Konversi Penawaran {{ $q->quotation_number }} menjadi Pesanan Penjualan (SO)?');">
                                        @csrf
                                        <button type="submit"
                                            class="h-8 px-2.5 rounded-[9px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-[11.5px] font-bold transition-all shadow-xs cursor-pointer inline-flex items-center gap-1"
                                            title="Konversi ke Pesanan Penjualan">
                                            <i data-lucide="arrow-right-circle" class="w-3.5 h-3.5"></i>
                                            <span>Jadikan SO</span>
                                        </button>
                                    </form>
                                @endif

                                {{-- Hapus Penawaran jika belum konversi --}}
                                @if(!$q->salesOrder && Auth::guard('admin')->user()?->hasPermission('admin.b2b.delete'))
                                    <form action="{{ route('admin.b2b.quotations.destroy', $q) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Hapus Surat Penawaran {{ $q->quotation_number }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="h-8 w-8 rounded-[9px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] transition-all inline-flex items-center justify-center cursor-pointer"
                                            title="Hapus Penawaran">
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
                            <i data-lucide="file-text" class="w-10 h-10 mx-auto stroke-[1.2] opacity-40 mb-2"></i>
                            <div class="text-[13px] font-semibold">Tidak Ada Dokumen Surat Penawaran</div>
                            <div class="text-[12px] text-black/35 dark:text-white/35 mt-0.5">
                                Dokumen penawaran harga B2B yang diterbitkan akan tampil di sini.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($quotations && $quotations->hasPages())
        <div class="p-4 border-t border-black/5 dark:border-white/5">
            {{ $quotations->links() }}
        </div>
    @endif
</div>
