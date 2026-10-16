@extends('layouts.app', [
    'title' => 'Inventori & Stok Multi-Gudang',
    'headerTitle' => 'Inventori & Stok Multi-Gudang',
    'headerSubtitle' => 'Pantau stok produk di seluruh cabang/outlet & gudang secara real-time dengan sistem kartu stok'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showAdjustModal: false,
    selectedStock: null,
    newQuantity: 0,
    unitCost: 0,
    reasonCode: 'opname_variance',
    notes: '',
    openAdjust(stockId) {
        const stock = (window.COOCA_STOCKS || []).find(s => s.id === stockId);
        if (!stock) return;
        this.selectedStock = stock;
        this.newQuantity = Number(stock.quantity);
        this.unitCost = Number(stock.last_cost || (stock.product ? stock.product.base_cost : 0) || 0);
        this.reasonCode = 'opname_variance';
        this.notes = '';
        this.showAdjustModal = true;
    }
}">
    <script>
        window.COOCA_STOCKS = @json($stocks->items());
    </script>

    {{-- ===================================================== --}}
    {{-- 1. OWNER PANEL PAGE HEADER (3-Baris Bento Apple HIG)   --}}
    {{-- ===================================================== --}}
    <div class="mb-2">
        {{-- Baris 1: Pure Typographic Overline (Dilarang Eyebrow Pills) --}}
        <div class="flex items-center gap-2 mb-1.5">
            <span class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">
                {{ __('inventory.nav_katalog') ?? 'Katalog & Inventori' }}
            </span>
            <span class="text-black/20 dark:text-white/20 text-xs">•</span>
            <span class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/60 dark:text-white/60">
                {{ __('inventory.nav_stocks') ?? 'Multi-Gudang & Stok' }}
            </span>
        </div>

        {{-- Baris 2: H1 Title + Entity Status Badge + Action Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-black dark:text-white">
                    Inventori &amp; Stok Real-Time
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                    {{ $stocks->total() }} Produk Terdata
                </span>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                <a href="{{ route('inventory.movements') }}"
                   class="min-h-[44px] sm:min-h-0 h-10 sm:h-9 px-3.5 rounded-xl text-[13px] font-medium text-black/80 dark:text-white/80 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] hover:bg-black/[0.02] dark:hover:bg-white/[0.04] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-xs">
                    <i data-lucide="history" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                    <span>Riwayat Mutasi</span>
                </a>
                @if(\App\Support\Context::hasPermission('inventory.manage'))
                    <a href="{{ route('import.index', ['tab' => 'inventory']) }}"
                       class="min-h-[44px] sm:min-h-0 h-10 sm:h-9 px-4 rounded-xl text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm shadow-[#007AFF]/25">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Import Stok</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- Baris 3: Subtitle Faktual Ringkas (10-15 Kata) --}}
        <p class="mt-2 text-sm text-black/60 dark:text-white/60 max-w-3xl leading-relaxed">
            Pantau pergerakan kuantitas fisik, nilai valuasi HPP, dan peringatan stok menipis di seluruh outlet cabang.
        </p>
    </div>

    {{-- ===================================================== --}}
    {{-- 2. MODULE TABS (SSOT)                                   --}}
    {{-- ===================================================== --}}
    <x-module-tabs module="inventory" />

    <!-- Session Feedback Banner -->
    @if(session('success'))
    <div class="rounded-2xl bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2.5">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 text-[#34C759]"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 3. KPI SUMMARY ROW (Bento Apple HIG Squircle Cards)   -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-4">
        <!-- Tile 1: Nilai Aset (System Green for Valuation) -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-black/50 dark:text-white/50">Total Nilai Aset Inventori</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">HPP</span>
            </div>
            <div class="mt-3">
                <div class="text-[26px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">
                    Rp {{ number_format($totalValuation, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Berdasarkan HPP / Biaya Pokok terakhir</p>
            </div>
        </div>

        <!-- Tile 2: Peringatan Stok Minimum (System Orange Warning) -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-black/50 dark:text-white/50">Peringatan Stok Minimum</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $lowStockCount > 0 ? 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]' : 'bg-black/5 dark:bg-white/8 text-black/50 dark:text-white/50' }}">
                    {{ $lowStockCount > 0 ? 'Perlu Restock' : 'Aman' }}
                </span>
            </div>
            <div class="mt-3">
                <div class="text-[26px] font-bold tabular-nums {{ $lowStockCount > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-black dark:text-white' }} tracking-tight">
                    {{ $lowStockCount }} Produk
                </div>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Kuantitas di bawah batas stok minimum</p>
            </div>
        </div>

        <!-- Tile 3: Total Cabang / Gudang (System Blue) -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-black/50 dark:text-white/50">Total Cabang / Outlet</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF]">Lokasi</span>
            </div>
            <div class="mt-3">
                <div class="text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ $locations->count() }} Lokasi
                </div>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Outlet penjualan &amp; gudang logistik</p>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. FILTER TOOLBAR (Bento Apple HIG Controls)          -->
    <!-- ===================================================== -->
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 shadow-xs">
        <form method="GET" action="{{ route('inventory.stocks') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <!-- Search Field -->
            <div class="sm:col-span-6 relative">
                <i data-lucide="search" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk atau kode SKU..."
                    class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-xl pl-10 pr-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <!-- Location Dropdown -->
            <div class="sm:col-span-4">
                <select name="location_id" class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-xl px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Semua Lokasi / Outlet</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') === $loc->id ? 'selected' : '' }} class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">
                            {{ $loc->name }} ({{ strtoupper($loc->type ?? 'Outlet') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 h-11 sm:h-10 px-4 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[13px] font-semibold active:scale-[0.98] transition-all flex items-center justify-center shadow-xs cursor-pointer">
                    Filter
                </button>
                <a href="{{ route('inventory.stocks') }}" class="h-11 sm:h-10 px-3.5 rounded-xl text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.98] transition-all flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 5. SORTABLE DENSE DATA TABLE (DESKTOP & TABLET)       -->
    <!-- ===================================================== -->
    <div x-data="coocaTableSort()" class="hidden sm:block rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] whitespace-nowrap bg-black/[0.015] dark:bg-white/[0.015]">
                        <x-table-sort-th :col="0" type="string" title="Produk & SKU" />
                        <x-table-sort-th :col="1" type="string" title="Lokasi / Outlet" />
                        <x-table-sort-th :col="2" type="number" title="Stok Fisik" align="right" />
                        <x-table-sort-th :col="3" type="number" title="Stok Min." align="right" />
                        <x-table-sort-th :col="4" type="number" title="HPP / Unit" align="right" />
                        <x-table-sort-th :col="5" type="number" title="Total Nilai" align="right" />
                        <x-table-sort-th :col="6" type="number" title="Status" align="center" />
                        <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($stocks as $st)
                    @php 
                        $isLow = $st->quantity <= ($st->product->min_stock ?? 0); 
                        $val = $st->quantity * ($st->last_cost > 0 ? $st->last_cost : ($st->product->base_cost ?? 0));
                        $statusCode = $st->quantity <= 0 ? 0 : ($isLow ? 1 : 2);
                    @endphp
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3.5 px-4" data-sort-value="{{ strtolower($st->product->name ?? '') }}">
                            <div class="font-semibold text-black dark:text-white text-[13px]">{{ $st->product->name ?? 'Produk' }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums font-mono">{{ $st->product->code ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4" data-sort-value="{{ strtolower($st->location->name ?? '') }}">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                                {{ $st->location->name ?? 'Outlet Utama' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right tabular-nums font-bold text-black dark:text-white text-[14px]" data-sort-value="{{ (float)$st->quantity }}">
                            {{ rtrim(rtrim((string)$st->quantity, '0'), '.') }} <span class="text-[12px] font-normal text-black/50 dark:text-white/50">{{ $st->product->outputUnit->code ?? '' }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-right tabular-nums text-black/50 dark:text-white/50" data-sort-value="{{ (float)($st->product->min_stock ?? 0) }}">
                            {{ $st->product->min_stock ?? 0 }}
                        </td>
                        <td class="py-3.5 px-4 text-right tabular-nums text-black/70 dark:text-white/70 font-medium" data-sort-value="{{ (float)($st->last_cost > 0 ? $st->last_cost : ($st->product->base_cost ?? 0)) }}">
                            Rp {{ number_format($st->last_cost > 0 ? $st->last_cost : ($st->product->base_cost ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right tabular-nums font-bold text-[#34C759] dark:text-[#30D158]" data-sort-value="{{ (float)$val }}">
                            Rp {{ number_format($val, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center" data-sort-value="{{ $statusCode }}">
                            @if($st->quantity <= 0)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Habis
                                </span>
                            @elseif($isLow)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Menipis
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Aman
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if (\App\Support\Context::hasPermission('inventory.manage'))
                            <button type="button" @click="openAdjust('{{ $st->id }}')" class="min-h-[44px] sm:min-h-0 h-8 px-3 rounded-lg text-[12px] font-semibold text-[#007AFF] hover:bg-[#007AFF]/10 active:scale-[0.97] transition-all cursor-pointer">
                                Sesuaikan
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr data-empty-row="true">
                        <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                            <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center mx-auto mb-2.5 text-black/40 dark:text-white/40">
                                <i data-lucide="package" class="w-6 h-6"></i>
                            </div>
                            <p class="font-medium">Belum ada data stok tercatat pada filter ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stocks->hasPages())
        <div class="px-5 py-3.5 border-t border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.01]">
            {{ $stocks->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 6. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06] shadow-xs">
        @forelse($stocks as $st)
        @php 
            $isLow = $st->quantity <= ($st->product->min_stock ?? 0); 
            $val = $st->quantity * ($st->last_cost > 0 ? $st->last_cost : ($st->product->base_cost ?? 0));
        @endphp
        <div class="p-4 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.04] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <p class="text-[15px] font-semibold text-black dark:text-white truncate">{{ $st->product->name ?? 'Produk' }}</p>
                    @if($st->quantity <= 0)
                        <span class="w-2 h-2 rounded-full bg-[#FF3B30] shrink-0"></span>
                    @elseif($isLow)
                        <span class="w-2 h-2 rounded-full bg-[#FF9500] shrink-0"></span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
                    @endif
                </div>
                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5 tabular-nums">
                    <strong class="text-black dark:text-white">{{ rtrim(rtrim((string)$st->quantity, '0'), '.') }} {{ $st->product->outputUnit->code ?? '' }}</strong> · {{ $st->location->name ?? 'Outlet Utama' }}
                </p>
                <p class="text-[11px] text-[#34C759] dark:text-[#30D158] mt-0.5 tabular-nums font-semibold">
                    Nilai: Rp {{ number_format($val, 0, ',', '.') }}
                </p>
            </div>
            <div class="shrink-0">
                @if (\App\Support\Context::hasPermission('inventory.manage'))
                <button type="button" @click="openAdjust('{{ $st->id }}')" class="min-h-[44px] h-10 px-3.5 rounded-xl text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 active:scale-[0.97] transition-all cursor-pointer">
                    Sesuaikan
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="py-10 text-center text-black/40 dark:text-white/40 text-[13px]">Belum ada data stok tercatat.</div>
        @endforelse

        @if($stocks->hasPages())
        <div class="p-3 border-t border-black/[0.06] dark:border-white/[0.08]">
            {{ $stocks->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 7. APPLE MODAL SHEET: PENYESUAIAN STOK CEPAT          -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('inventory.manage'))
    <template x-teleport="body">
        <div x-show="showAdjustModal"
            x-cloak
            class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4"
            role="dialog"
            aria-modal="true">

            {{-- Backdrop Blur Overlay --}}
            <div x-show="showAdjustModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showAdjustModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 w-full sm:max-w-lg rounded-t-[26px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto"
                @click.away="showAdjustModal = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0">

                <!-- Mobile Grabber Handle -->
                <div class="sm:hidden w-12 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto mb-2"></div>

                <div class="flex items-center justify-between pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="sliders" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white">Penyesuaian Stok Cepat</h3>
                    </div>
                    <button type="button" @click="showAdjustModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 text-black/50 hover:text-black dark:text-white/50 dark:hover:text-white transition flex items-center justify-center cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('inventory.stocks.adjust') }}" method="POST" class="space-y-4 text-[13px]">
                    @csrf
                    <input type="hidden" name="location_id" :value="selectedStock ? selectedStock.location_id : ''">
                    <input type="hidden" name="product_id" :value="selectedStock ? selectedStock.product_id : ''">

                    <div class="p-3.5 rounded-xl bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08]">
                        <div class="font-bold text-black dark:text-white text-[14px]" x-text="selectedStock ? selectedStock.product.name : ''"></div>
                        <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5 flex items-center gap-1.5">
                            <span x-text="selectedStock ? selectedStock.location.name : ''"></span>
                            <span class="text-black/30 dark:text-white/30">•</span>
                            <span>Stok Saat Ini: <strong class="text-black dark:text-white font-mono tabular-nums" x-text="selectedStock ? selectedStock.quantity : 0"></strong></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Jumlah Stok Fisik Baru *</label>
                        <input type="number" step="any" name="new_quantity" x-model.number="newQuantity" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-xl px-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Biaya Pokok (HPP) per Unit (Rp)</label>
                        <input type="number" name="unit_cost" x-model.number="unitCost"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-xl px-3.5 text-[16px] sm:text-[14px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                            Kode Berita Acara (Alasan Penyesuaian) *
                        </label>
                        <select name="reason_code" x-model="reasonCode" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-xl px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="opname_variance" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Selisih Hitung Rutin (Stock Opname)</option>
                            <option value="damaged" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Barang Rusak / Cacat Fisik / Basi</option>
                            <option value="expired" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Melewati Tanggal Kadaluarsa</option>
                            <option value="theft_loss" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Kehilangan / Dugaan Pencurian</option>
                            <option value="initial_balance" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Input Saldo Awal Gudang</option>
                            <option value="other" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Lainnya (Wajib tulis alasan min. 10 karakter)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                            Keterangan / Catatan Penjelasan <span x-show="reasonCode === 'other'" class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="notes" x-model="notes"
                            :placeholder="reasonCode === 'other' ? 'Wajib tulis alasan detail (minimal 10 karakter)...' : 'Misal: Koreksi saldo awal, barang rusak...'"
                            :required="reasonCode === 'other'"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-xl px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <p x-show="reasonCode === 'other'" class="text-[11px] text-amber-600 dark:text-amber-400 mt-1">
                            * Alasan "Lainnya" mewajibkan catatan penjelasan minimal 10 karakter untuk audit trail.
                        </p>
                    </div>

                    {{-- Supervisor PIN Verification Prompt on High-Value / Volume Shrinkage --}}
                    <div x-show="(Number(newQuantity) - Number(selectedStock?.quantity || 0)) < 0 && (Math.abs(Number(newQuantity) - Number(selectedStock?.quantity || 0)) > 10 || Math.abs((Number(newQuantity) - Number(selectedStock?.quantity || 0)) * Number(unitCost || 0)) > 100000)"
                        x-transition
                        class="p-4 rounded-xl bg-red-500/10 dark:bg-red-500/15 border border-red-500/25 dark:border-red-500/35 space-y-2.5">
                        <div class="flex items-center gap-2 text-red-600 dark:text-red-400 font-bold text-[12px]">
                            <i data-lucide="shield-alert" class="w-4 h-4 shrink-0 text-red-500"></i>
                            <span>Otorisasi Supervisor Diperlukan</span>
                        </div>
                        <p class="text-[11px] text-red-700 dark:text-red-300 leading-relaxed">
                            Pengurangan stok melebihi batas toleransi (&gt; 10 unit atau nilai &gt; Rp 100.000). Masukkan PIN Supervisor untuk memverifikasi berita acara ini.
                        </p>
                        <div>
                            <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                PIN Supervisor <span class="text-[#FF3B30]">*</span>
                            </label>
                            <input type="password" name="supervisor_pin" maxlength="10" placeholder="Masukkan PIN Supervisor"
                                :required="(Number(newQuantity) - Number(selectedStock?.quantity || 0)) < 0 && (Math.abs(Number(newQuantity) - Number(selectedStock?.quantity || 0)) > 10 || Math.abs((Number(newQuantity) - Number(selectedStock?.quantity || 0)) * Number(unitCost || 0)) > 100000)"
                                class="w-full h-10 bg-white dark:bg-[#2C2C2E] border border-red-300 dark:border-red-500/40 rounded-lg px-3 text-[14px] font-mono tracking-widest text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3.5 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="button" @click="showAdjustModal = false" class="min-h-[44px] sm:min-h-0 h-10 px-4 rounded-xl text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.98] transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="min-h-[44px] sm:min-h-0 h-10 px-5 rounded-xl text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition-all shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
    @endif
</div>
@endsection
