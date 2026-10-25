{{-- TAB 2: PESANAN PENJUALAN (SALES ORDERS) --}}
<div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
    <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="text-[15px] font-bold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="check-check" class="w-4 h-4 text-[#34C759]"></i>
                <span>Daftar Pesanan Penjualan (Sales Orders / SO)</span>
            </h3>
            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                Konfirmasi perjanjian pemenuhan pesanan B2B resmi, rujukan nomor penawaran, reservasi stok, dan penerbitan faktur penagihan komersial.
            </p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">
                    <th class="py-3 px-4">No. Pesanan SO &amp; Tgl</th>
                    <th class="py-3 px-4">Bisnis / Tenant</th>
                    <th class="py-3 px-4">Pemesan / Pembeli</th>
                    <th class="py-3 px-4">Ref. Penawaran</th>
                    <th class="py-3 px-4">Jadwal Kirim</th>
                    <th class="py-3 px-4 text-right">Nilai Pesanan</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Aksi Dokumen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5 dark:divide-white/5 text-[12.5px]">
                @forelse($orders as $o)
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                        {{-- No SO & Tanggal --}}
                        <td class="py-3.5 px-4">
                            <div class="font-mono font-bold text-black dark:text-white text-[12.5px]">
                                {{ $o->so_number }}
                            </div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                {{ \Carbon\Carbon::parse($o->order_date)->format('d M Y') }}
                            </div>
                            @if($o->invoices->count() > 0)
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-mono font-semibold text-[#0284c7] bg-[#0284c7]/12 px-1.5 py-0.2 rounded" title="Faktur Penjualan Telah Diterbitkan">
                                        <i data-lucide="receipt" class="w-2.5 h-2.5"></i>
                                        {{ $o->invoices->count() }}x Faktur
                                    </span>
                                </div>
                            @endif
                        </td>

                        {{-- Bisnis / Tenant --}}
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-black dark:text-white">
                                {{ $o->business?->name ?? 'Platform Cooca' }}
                            </div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">
                                {{ $o->business?->email ?? '-' }}
                            </div>
                        </td>

                        {{-- Pemesan / Customer --}}
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-black dark:text-white">
                                {{ $o->customer?->name ?? $o->recipient_name }}
                            </div>
                            @if($o->customer?->company_name)
                                <div class="text-[11px] text-[#007AFF] font-medium">
                                    {{ $o->customer->company_name }}
                                </div>
                            @endif
                            <div class="text-[11px] text-black/45 dark:text-white/45">
                                {{ $o->customer?->phone ?? '-' }}
                            </div>
                        </td>

                        {{-- Ref Penawaran --}}
                        <td class="py-3.5 px-4">
                            @if($o->quotation)
                                <span class="font-mono text-[11px] text-[#007AFF] bg-[#007AFF]/10 px-1.5 py-0.5 rounded">
                                    {{ $o->quotation->quotation_number }}
                                </span>
                            @else
                                <span class="text-black/40 dark:text-white/40 text-[11px]">Direct Order</span>
                            @endif
                        </td>

                        {{-- Jadwal Kirim --}}
                        <td class="py-3.5 px-4">
                            @if($o->expected_delivery_date)
                                <div class="font-medium text-black dark:text-white">
                                    {{ \Carbon\Carbon::parse($o->expected_delivery_date)->format('d M Y') }}
                                </div>
                            @else
                                <span class="text-black/40 dark:text-white/40 text-[11px]">Sesuai Jadwal</span>
                            @endif
                        </td>

                        {{-- Total Nilai --}}
                        <td class="py-3.5 px-4 text-right font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums">
                            Rp {{ number_format((float) $o->total_amount, 0, ',', '.') }}
                        </td>

                        {{-- Status Badge --}}
                        <td class="py-3.5 px-4 text-center">
                            @if($o->status === 'fulfilled')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                    Terpenuhi
                                </span>
                            @elseif($o->status === 'confirmed')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF]">
                                    Dikonfirmasi
                                </span>
                            @elseif($o->status === 'partially_fulfilled')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]">
                                    Kirim Sebagian
                                </span>
                            @elseif($o->status === 'draft')
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                    Draf
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]">
                                    {{ ucfirst($o->status) }}
                                </span>
                            @endif
                        </td>

                        {{-- Aksi Dokumen --}}
                        <td class="py-3.5 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- Tombol PDF --}}
                                <a href="{{ route('admin.b2b.orders.pdf', $o) }}" target="_blank"
                                    class="h-8 px-2.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-[#34C759]/10 hover:text-[#248A3D] text-black/70 dark:text-white/70 text-[11.5px] font-bold transition-all inline-flex items-center gap-1"
                                    title="Unduh / Cetak Konfirmasi Pesanan SO Resmi PDF (A4)">
                                    <i data-lucide="file-down" class="w-3.5 h-3.5"></i>
                                    <span>PDF</span>
                                </a>

                                {{-- Terbitkan Faktur dari SO --}}
                                @if(Auth::guard('admin')->user()?->hasPermission('admin.b2b.manage'))
                                    <form action="{{ route('admin.b2b.orders.generate-invoice', $o) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Terbitkan Faktur Penjualan (Invoice) dari Pesanan SO {{ $o->so_number }}?');">
                                        @csrf
                                        <button type="submit"
                                            class="h-8 px-2.5 rounded-[9px] bg-[#0284c7] hover:bg-[#0369a1] active:scale-[0.98] text-white text-[11.5px] font-bold transition-all shadow-xs cursor-pointer inline-flex items-center gap-1"
                                            title="Terbitkan Faktur Penagihan Komersial">
                                            <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                            <span>Buat Faktur</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                            <i data-lucide="check-check" class="w-10 h-10 mx-auto stroke-[1.2] opacity-40 mb-2"></i>
                            <div class="text-[13px] font-semibold">Tidak Ada Dokumen Pesanan Penjualan</div>
                            <div class="text-[12px] text-black/35 dark:text-white/35 mt-0.5">
                                Pesanan penjualan (SO) dari tenant dan korporat akan terdaftar di sini.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders && $orders->hasPages())
        <div class="p-4 border-t border-black/5 dark:border-white/5">
            {{ $orders->links() }}
        </div>
    @endif
</div>
