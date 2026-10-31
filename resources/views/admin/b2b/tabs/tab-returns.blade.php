{{-- TAB 4: RETUR PENJUALAN (SALES RETURNS) --}}
<div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
    <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="text-[15px] font-bold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="rotate-ccw" class="w-4 h-4 text-[#e11d48]"></i>
                <span>Daftar Retur Penjualan &amp; Nota Kredit (Sales Returns)</span>
            </h3>
            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                Pengawasan retur pengembalian barang, verifikasi nota kredit pemotong tagihan (*credit note*), dan pemulihan stok inventori gudang.
            </p>
        </div>
        @if(Auth::guard('admin')->user()?->hasPermission('admin.b2b.create'))
            <button type="button" @click="showCreateReturnModal = true"
                class="h-9 px-3.5 rounded-[11px] bg-[#d97706] hover:bg-[#b45309] active:scale-[0.98] text-white text-[12px] font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer shrink-0">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Buat Pengajuan Retur</span>
            </button>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">
                    <th class="py-3 px-4">No. Retur &amp; Tgl</th>
                    <th class="py-3 px-4">Bisnis / Tenant</th>
                    <th class="py-3 px-4">Klien / Pengembali</th>
                    <th class="py-3 px-4">Faktur Rujukan</th>
                    <th class="py-3 px-4">Alasan &amp; Kompensasi</th>
                    <th class="py-3 px-4 text-right">Nilai Retur</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Aksi Dokumen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5 dark:divide-white/5 text-[12.5px]">
                @forelse($returns as $r)
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                        {{-- No Retur & Tanggal --}}
                        <td class="py-3.5 px-4">
                            <div class="font-mono font-bold text-black dark:text-white text-[12.5px]">
                                {{ $r->return_number }}
                            </div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                {{ \Carbon\Carbon::parse($r->return_date)->format('d M Y') }}
                            </div>
                        </td>

                        {{-- Bisnis / Tenant --}}
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-black dark:text-white">
                                {{ $r->business?->name ?? 'Platform Cooca' }}
                            </div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">
                                Lokasi: {{ $r->location?->name ?? 'Gudang Utama' }}
                            </div>
                        </td>

                        {{-- Klien / Customer --}}
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-black dark:text-white">
                                {{ $r->customer?->name ?? $r->recipient_name }}
                            </div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">
                                {{ $r->customer?->phone ?? '-' }}
                            </div>
                        </td>

                        {{-- Faktur Rujukan --}}
                        <td class="py-3.5 px-4">
                            @if($r->invoice)
                                <span class="font-mono text-[11px] text-[#0284c7] bg-[#0284c7]/10 px-1.5 py-0.5 rounded">
                                    {{ $r->invoice->invoice_number }}
                                </span>
                            @else
                                <span class="text-black/40 dark:text-white/40 text-[11px]">-</span>
                            @endif
                        </td>

                        {{-- Alasan & Metode Kompensasi --}}
                        <td class="py-3.5 px-4 max-w-xs">
                            <div class="text-[12px] text-black/80 dark:text-white/80 line-clamp-1">
                                {{ $r->reason ?? 'Klaim Retur' }}
                            </div>
                            <div class="text-[10.5px] font-semibold text-[#e11d48] uppercase mt-0.5">
                                {{ str_replace('_', ' ', $r->refund_method) }}
                            </div>
                        </td>

                        {{-- Total Nilai --}}
                        <td class="py-3.5 px-4 text-right font-bold text-[#e11d48] tabular-nums">
                            Rp {{ number_format((float) $r->total_amount, 0, ',', '.') }}
                        </td>

                        {{-- Status Badge --}}
                        <td class="py-3.5 px-4 text-center">
                            @if($r->status === 'completed')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                    Selesai
                                </span>
                            @elseif($r->status === 'approved')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF]">
                                    Disetujui
                                </span>
                            @elseif($r->status === 'draft')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                    Draf
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]">
                                    {{ ucfirst($r->status) }}
                                </span>
                            @endif
                        </td>

                        {{-- Aksi Dokumen --}}
                        <td class="py-3.5 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- Tombol PDF --}}
                                <a href="{{ route('admin.b2b.returns.pdf', $r) }}" target="_blank"
                                    class="h-8 px-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-[#e11d48]/10 hover:text-[#e11d48] text-black/70 dark:text-white/70 text-[11.5px] font-bold transition-all inline-flex items-center gap-1"
                                    title="{{ __('admin.unduh_cetak_nota_retur_penjualan_resmi') }}" rel="noopener noreferrer">
                                    <i data-lucide="file-down" class="w-3.5 h-3.5"></i>
                                    <span>PDF</span>
                                </a>

                                {{-- Ubah Retur --}}
                                @if(Auth::guard('admin')->user()?->hasPermission('admin.b2b.edit'))
                                    <button type="button" @click="openEdit('return', {
                                        number: '{{ $r->return_number }}',
                                        reason: '{{ addslashes($r->reason ?? '') }}',
                                        refund_method: '{{ $r->refund_method }}'
                                    }, '{{ route('admin.b2b.returns.update', $r) }}')"
                                        class="h-8 w-8 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-[#d97706]/15 hover:text-[#d97706] text-black/70 dark:text-white/70 transition-all inline-flex items-center justify-center cursor-pointer"
                                        title="{{ __('admin.ubah_retur') }}">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                @endif

                                {{-- Otorisasi Approve / Complete --}}
                                @if(Auth::guard('admin')->user()?->hasPermission('admin.b2b.manage'))
                                    @if($r->status === 'draft' || $r->status === 'pending')
                                        <form action="{{ route('admin.b2b.returns.approve', $r) }}" method="POST" class="inline"
                                            data-confirm="Setujui (Approve) retur penjualan {{ $r->return_number }}?"
                                        onsubmit="return confirm(this.dataset.confirm);">
                                            @csrf
                                            <button type="submit"
                                                class="h-8 px-2.5 rounded-[9px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[11.5px] font-bold transition-all shadow-xs cursor-pointer inline-flex items-center gap-1"
                                                title="Setujui Retur">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                <span>Setujui</span>
                                            </button>
                                        </form>
                                    @elseif($r->status === 'approved')
                                        <form action="{{ route('admin.b2b.returns.complete', $r) }}" method="POST" class="inline"
                                            data-confirm="Selesaikan (Complete) retur penjualan {{ $r->return_number }} dan pulihkan stok inventori?"
                                        onsubmit="return confirm(this.dataset.confirm);">
                                            @csrf
                                            <button type="submit"
                                                class="h-8 px-2.5 rounded-[9px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-[11.5px] font-bold transition-all shadow-xs cursor-pointer inline-flex items-center gap-1"
                                                title="{{ __('admin.selesaikan_retur_pulihkan_stok') }}">
                                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                                <span>Selesaikan</span>
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                {{-- Tolak Retur jika belum selesai --}}
                                @if($r->status !== 'completed' && $r->status !== 'rejected' && Auth::guard('admin')->user()?->hasPermission('admin.b2b.edit'))
                                    <form action="{{ route('admin.b2b.returns.reject', $r) }}" method="POST" class="inline"
                                        data-confirm="Tolak klaim retur {{ $r->return_number }}?"
                                        onsubmit="return confirm(this.dataset.confirm);">
                                        @csrf
                                        <button type="submit"
                                            class="h-8 w-8 rounded-[9px] bg-[#FF9500]/10 hover:bg-[#FF9500]/20 text-[#B25E00] dark:text-[#FF9F0A] transition-all inline-flex items-center justify-center cursor-pointer"
                                            title="Tolak Retur">
                                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Hapus Retur jika belum selesai --}}
                                @if($r->status !== 'completed' && Auth::guard('admin')->user()?->hasPermission('admin.b2b.delete'))
                                    <form action="{{ route('admin.b2b.returns.destroy', $r) }}" method="POST" class="inline"
                                        data-confirm="Hapus klaim retur {{ $r->return_number }}?"
                                        onsubmit="return confirm(this.dataset.confirm);">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="h-8 w-8 rounded-[9px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] transition-all inline-flex items-center justify-center cursor-pointer"
                                            title="{{ __('admin.hapus_retur') }}">
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
                            <i data-lucide="rotate-ccw" class="w-10 h-10 mx-auto stroke-[1.2] opacity-40 mb-2"></i>
                            <div class="text-[13px] font-semibold">Tidak Ada Dokumen Retur Penjualan</div>
                            <div class="text-[12px] text-black/35 dark:text-white/35 mt-0.5">
                                Dokumen retur barang dan nota kredit akan terdaftar di sini.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($returns && $returns->hasPages())
        <div class="p-4 border-t border-black/5 dark:border-white/5">
            {{ $returns->links() }}
        </div>
    @endif
</div>
