@extends('layouts.app', [
    'title' => 'Kartu Stok & Mutasi',
    'headerTitle' => 'Kartu Stok & Mutasi',
    'headerSubtitle' => 'Audit trail lengkap seluruh pergerakan barang (penjualan kasir, retur, transfer, opname, dan penyesuaian)'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12">
    
@extends('layouts.app', [
    'title' => 'Kartu Stok & Mutasi',
    'headerTitle' => 'Kartu Stok & Mutasi',
    'headerSubtitle' => 'Audit trail lengkap seluruh pergerakan barang (penjualan kasir, retur, transfer, opname, dan penyesuaian)'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12">

    <!-- ===================================================== -->
    <!-- 1. STANDAR OWNER PANEL: 3-BARIS BENTO APPLE HIG HEADER-->
    <!-- ===================================================== -->
    <header class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 lg:p-7 shadow-[0_2px_12px_rgba(0,0,0,0.03)] dark:shadow-[0_2px_12px_rgba(0,0,0,0.2)]">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1.5 min-w-0">
                <!-- Baris 1: Overline Typographic Hierarchy -->
                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                        <span>Katalog &amp; Inventori</span>
                    </span>
                    <span class="text-black/20 dark:text-white/20">•</span>
                    <span>Audit Log &amp; Mutasi Stok</span>
                </div>

                <!-- Baris 2: H1 Title + Entity Status Badge -->
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#1C1C1E] dark:text-[#F2F2F7]">
                        Kartu Stok &amp; Riwayat Mutasi
                    </h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] animate-pulse"></span>
                        Audit Log Real-Time
                    </span>
                </div>

                <!-- Baris 3: Subtitle Faktual -->
                <p class="text-xs sm:text-[13px] text-black/55 dark:text-white/55 leading-relaxed max-w-3xl">
                    Audit trail seluruh transaksi keluar-masuk stok dari penjualan kasir POS, retur penjualan, transfer antar cabang, opname fisik, dan penyesuaian manual.
                </p>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-2.5 shrink-0 self-start lg:self-center">
                <a href="{{ route('inventory.stocks') }}"
                   class="min-h-[44px] h-11 px-4 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[13px] font-medium border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition flex items-center justify-center gap-2">
                    <i data-lucide="boxes" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                    <span>Lihat Saldo Stok</span>
                </a>
            </div>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. MODULE TABS (SSOT)                                 -->
    <!-- ===================================================== -->
    <x-module-tabs module="inventory" />

    <!-- ===================================================== -->
    <!-- 3. FILTER TOOLBAR (Bento Squircle Controls)           -->
    <!-- ===================================================== -->
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 shadow-xs">
        <form method="GET" action="{{ route('inventory.movements') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <!-- Product Filter -->
            <div class="sm:col-span-4">
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                    Produk
                </label>
                <div class="relative">
                    <select name="product_id" class="w-full h-11 bg-black/[0.03] dark:bg-[#2C2C2E]/60 border border-black/[0.08] dark:border-white/[0.12] rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition appearance-none">
                        <option value="" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Semua Produk</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') === $p->id ? 'selected' : '' }} class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-black/40 dark:text-white/40">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <!-- Location Filter -->
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                    Lokasi / Gudang
                </label>
                <div class="relative">
                    <select name="location_id" class="w-full h-11 bg-black/[0.03] dark:bg-[#2C2C2E]/60 border border-black/[0.08] dark:border-white/[0.12] rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition appearance-none">
                        <option value="" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Semua Lokasi / Outlet</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') === $loc->id ? 'selected' : '' }} class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-black/40 dark:text-white/40">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <!-- Movement Type Filter -->
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                    Tipe Mutasi
                </label>
                <div class="relative">
                    <select name="movement_type" class="w-full h-11 bg-black/[0.03] dark:bg-[#2C2C2E]/60 border border-black/[0.08] dark:border-white/[0.12] rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition appearance-none">
                        <option value="" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Semua Tipe Mutasi</option>
                        <option value="pos_sale" {{ request('movement_type') === 'pos_sale' ? 'selected' : '' }} class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Penjualan POS</option>
                        <option value="pos_refund" {{ request('movement_type') === 'pos_refund' ? 'selected' : '' }} class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Retur POS</option>
                        <option value="transfer_in" {{ request('movement_type') === 'transfer_in' ? 'selected' : '' }} class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Transfer Masuk</option>
                        <option value="transfer_out" {{ request('movement_type') === 'transfer_out' ? 'selected' : '' }} class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Transfer Keluar</option>
                        <option value="opname" {{ request('movement_type') === 'opname' ? 'selected' : '' }} class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Stock Opname</option>
                        <option value="adjustment" {{ request('movement_type') === 'adjustment' ? 'selected' : '' }} class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Penyesuaian Manual</option>
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-black/40 dark:text-white/40">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="sm:col-span-2 flex items-center gap-2 pt-1 sm:pt-5.5">
                <button type="submit" class="flex-1 min-h-[44px] h-11 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('inventory.movements') }}" class="min-h-[44px] h-11 px-3.5 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition-all flex items-center justify-center" title="Reset filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 4. SORTABLE DATA TABLE (DESKTOP & TABLET)             -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs"
         x-data="{
             sortCol: null,
             sortAsc: true,
             sortBy(colIndex, type = 'string') {
                 if (this.sortCol === colIndex) {
                     this.sortAsc = !this.sortAsc;
                 } else {
                     this.sortCol = colIndex;
                     this.sortAsc = true;
                 }
                 const tbody = this.$refs.tableBody;
                 const rows = Array.from(tbody.querySelectorAll('tr[data-sort-row]'));
                 rows.sort((a, b) => {
                     let valA = a.getAttribute(`data-sort-${colIndex}`) || '';
                     let valB = b.getAttribute(`data-sort-${colIndex}`) || '';
                     if (type === 'number') {
                         valA = parseFloat(valA) || 0;
                         valB = parseFloat(valB) || 0;
                         return this.sortAsc ? valA - valB : valB - valA;
                     }
                     return this.sortAsc
                         ? valA.localeCompare(valB, undefined, { numeric: true, sensitivity: 'base' })
                         : valB.localeCompare(valA, undefined, { numeric: true, sensitivity: 'base' });
                 });
                 rows.forEach(row => tbody.appendChild(row));
             }
         }">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.015] dark:bg-white/[0.02] whitespace-nowrap">
                        <!-- Col 0: Waktu -->
                        <th class="py-3 px-4">
                            <button type="button" @click="sortBy(0, 'number')" class="group inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition-colors">
                                <span>Waktu</span>
                                <span class="transition-colors">
                                    <svg x-show="sortCol !== 0" class="w-3.5 h-3.5 text-black/25 dark:text-white/25 group-hover:text-black/50 dark:group-hover:text-white/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    <svg x-show="sortCol === 0 && sortAsc" class="w-3.5 h-3.5 text-[#007AFF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m18 15-6-6-6 6"/></svg>
                                    <svg x-show="sortCol === 0 && !sortAsc" class="w-3.5 h-3.5 text-[#007AFF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                                </span>
                            </button>
                        </th>

                        <!-- Col 1: Produk -->
                        <th class="py-3 px-4">
                            <button type="button" @click="sortBy(1, 'string')" class="group inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition-colors">
                                <span>Produk</span>
                                <span class="transition-colors">
                                    <svg x-show="sortCol !== 1" class="w-3.5 h-3.5 text-black/25 dark:text-white/25 group-hover:text-black/50 dark:group-hover:text-white/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    <svg x-show="sortCol === 1 && sortAsc" class="w-3.5 h-3.5 text-[#007AFF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m18 15-6-6-6 6"/></svg>
                                    <svg x-show="sortCol === 1 && !sortAsc" class="w-3.5 h-3.5 text-[#007AFF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                                </span>
                            </button>
                        </th>

                        <!-- Col 2: Lokasi -->
                        <th class="py-3 px-4">
                            <button type="button" @click="sortBy(2, 'string')" class="group inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition-colors">
                                <span>Lokasi</span>
                                <span class="transition-colors">
                                    <svg x-show="sortCol !== 2" class="w-3.5 h-3.5 text-black/25 dark:text-white/25 group-hover:text-black/50 dark:group-hover:text-white/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    <svg x-show="sortCol === 2 && sortAsc" class="w-3.5 h-3.5 text-[#007AFF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m18 15-6-6-6 6"/></svg>
                                    <svg x-show="sortCol === 2 && !sortAsc" class="w-3.5 h-3.5 text-[#007AFF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                                </span>
                            </button>
                        </th>

                        <!-- Col 3: Tipe Mutasi -->
                        <th class="py-3 px-4">
                            <button type="button" @click="sortBy(3, 'string')" class="group inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition-colors">
                                <span>Tipe Mutasi</span>
                                <span class="transition-colors">
                                    <svg x-show="sortCol !== 3" class="w-3.5 h-3.5 text-black/25 dark:text-white/25 group-hover:text-black/50 dark:group-hover:text-white/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    <svg x-show="sortCol === 3 && sortAsc" class="w-3.5 h-3.5 text-[#007AFF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m18 15-6-6-6 6"/></svg>
                                    <svg x-show="sortCol === 3 && !sortAsc" class="w-3.5 h-3.5 text-[#007AFF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                                </span>
                            </button>
                        </th>

                        <!-- Col 4: Perubahan Qty -->
                        <th class="py-3 px-4 text-right">
                            <button type="button" @click="sortBy(4, 'number')" class="group inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition-colors ml-auto">
                                <span>Perubahan Qty</span>
                                <span class="transition-colors">
                                    <svg x-show="sortCol !== 4" class="w-3.5 h-3.5 text-black/25 dark:text-white/25 group-hover:text-black/50 dark:group-hover:text-white/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    <svg x-show="sortCol === 4 && sortAsc" class="w-3.5 h-3.5 text-[#007AFF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m18 15-6-6-6 6"/></svg>
                                    <svg x-show="sortCol === 4 && !sortAsc" class="w-3.5 h-3.5 text-[#007AFF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                                </span>
                            </button>
                        </th>

                        <!-- Col 5: Saldo Akhir -->
                        <th class="py-3 px-4 text-right">
                            <button type="button" @click="sortBy(5, 'number')" class="group inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition-colors ml-auto">
                                <span>Saldo Akhir</span>
                                <span class="transition-colors">
                                    <svg x-show="sortCol !== 5" class="w-3.5 h-3.5 text-black/25 dark:text-white/25 group-hover:text-black/50 dark:group-hover:text-white/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    <svg x-show="sortCol === 5 && sortAsc" class="w-3.5 h-3.5 text-[#007AFF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m18 15-6-6-6 6"/></svg>
                                    <svg x-show="sortCol === 5 && !sortAsc" class="w-3.5 h-3.5 text-[#007AFF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                                </span>
                            </button>
                        </th>

                        <!-- Col 6: Keterangan / Ref -->
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">
                            Keterangan / Ref
                        </th>
                    </tr>
                </thead>
                <tbody x-ref="tableBody" class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($movements as $m)
                    <tr data-sort-row
                        data-sort-0="{{ $m->created_at->timestamp }}"
                        data-sort-1="{{ strtolower($m->product->name ?? '') }}"
                        data-sort-2="{{ strtolower($m->location->name ?? '') }}"
                        data-sort-3="{{ $m->movement_type }}"
                        data-sort-4="{{ (float)$m->quantity_change }}"
                        data-sort-5="{{ (float)$m->balance_after }}"
                        class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3.5 px-4 text-black/60 dark:text-white/60 tabular-nums">
                            {{ $m->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-black dark:text-white">
                            {{ $m->product->name ?? 'Produk' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70">
                                {{ $m->location->name ?? 'Outlet' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $isPositive = $m->quantity_change > 0;
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $isPositive ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isPositive ? 'bg-[#34C759]' : 'bg-[#FF3B30]' }}"></span>
                                {{ str_replace('_', ' ', $m->movement_type) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right tabular-nums font-bold {{ $m->quantity_change > 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                            {{ $m->quantity_change > 0 ? '+' : '' }}{{ rtrim(rtrim((string)$m->quantity_change, '0'), '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right tabular-nums font-bold text-black dark:text-white">
                            {{ rtrim(rtrim((string)$m->balance_after, '0'), '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-black/60 dark:text-white/60">
                            <div>{{ $m->notes ?? '-' }}</div>
                            @if($m->reference_number)
                                <div class="text-[11px] text-black/40 dark:text-white/40 tabular-nums">Ref: {{ $m->reference_number }}</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-black/40 dark:text-white/40">
                            <i data-lucide="history" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                            <div>Belum ada riwayat mutasi stok.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
        <div class="px-5 py-4 border-t border-black/[0.06] dark:border-white/[0.08] bg-black/[0.015] dark:bg-white/[0.02]">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06] shadow-xs">
        @forelse($movements as $m)
        <div class="p-4 flex items-start justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.04] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <p class="text-[15px] font-semibold text-black dark:text-white truncate">{{ $m->product->name ?? 'Produk' }}</p>
                    <span class="w-2 h-2 rounded-full {{ $m->quantity_change > 0 ? 'bg-[#34C759]' : 'bg-[#FF3B30]' }} shrink-0"></span>
                </div>
                <p class="text-[13px] text-black/55 dark:text-white/55 mt-0.5">
                    {{ $m->location->name ?? 'Outlet' }} · {{ str_replace('_', ' ', $m->movement_type) }}
                </p>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-1 tabular-nums">
                    {{ $m->created_at->format('d/m/Y H:i') }}
                    @if($m->notes) · {{ $m->notes }} @endif
                </p>
            </div>
            <div class="text-right shrink-0">
                <div class="text-[15px] font-bold tabular-nums {{ $m->quantity_change > 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                    {{ $m->quantity_change > 0 ? '+' : '' }}{{ rtrim(rtrim((string)$m->quantity_change, '0'), '.') }}
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50 tabular-nums mt-0.5">
                    Saldo: {{ rtrim(rtrim((string)$m->balance_after, '0'), '.') }}
                </div>
            </div>
        </div>
        @empty
        <div class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
            <i data-lucide="history" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
            <div>Belum ada riwayat mutasi stok.</div>
        </div>
        @endforelse

        @if($movements->hasPages())
        <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
            {{ $movements->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
