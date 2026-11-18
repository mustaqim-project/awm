@extends('layouts.app', [
    'title' => 'Perintah Kerja Produksi (SPKP) & Manufaktur',
])

@section('content')
<div class="max-w-[1440px] mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="{
    filterStatus: '{{ request('status', '') }}',
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER                              -->
    <!-- ===================================================== -->
    <x-module-header
        title="Perintah Produksi (SPKP)"
        subtitle="Manajemen jadwal kerja produksi batch, alokasi bahan baku (WIP), dan pencatatan barang jadi pabrik."
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Katalog Produk', 'url' => route('products.index')],
            ['label' => 'Perintah Produksi', 'url' => null],
        ]">
        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <a href="{{ route('products.index') }}"
                class="min-h-[44px] sm:min-h-0 h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shrink-0">
                <i data-lucide="package" class="w-4 h-4 text-black/60 dark:text-white/60 shrink-0"></i>
                <span>Katalog Produk</span>
            </a>

            @if(\App\Support\Context::hasPermission('production.create'))
            <a href="{{ route('production.create') }}"
                class="min-h-[44px] sm:min-h-0 h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-sm shadow-[#007AFF]/25 shrink-0">
                <i data-lucide="plus" class="w-4 h-4 shrink-0"></i>
                <span>Buat SPKP Baru</span>
            </a>
            @endif
        </div>
    </x-module-header>

    <!-- ===================================================== -->
    <!-- 2. UNIFIED SEGMENTED NAVIGATION                        -->
    <!-- ===================================================== -->
    <x-module-tabs module="production" />

    <!-- ===================================================== -->
    <!-- 3. KPI BENTO CARDS                                    -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
        <!-- Total SPKP -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4.5 sm:p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[12px] sm:text-[13px] font-medium text-black/50 dark:text-white/50">Total SPKP</span>
                <div class="w-8 h-8 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="factory" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ number_format($totalOrdersCount, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-medium text-black/40 dark:text-white/40">Seluruh Waktu</span>
            </div>
        </div>

        <!-- Sedang Dikerjakan (In Progress) -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4.5 sm:p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[12px] sm:text-[13px] font-medium text-black/50 dark:text-white/50">Di Lantai Pabrik (WIP)</span>
                <div class="w-8 h-8 rounded-full bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                    <i data-lucide="cog" class="w-4 h-4 animate-spin" style="animation-duration: 8s;"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold tabular-nums text-[#FF9500] tracking-tight">
                    {{ number_format($inProgressCount, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-medium text-[#FF9500]/80">Proses Aktif</span>
            </div>
        </div>

        <!-- Selesai Bulan Ini -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4.5 sm:p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[12px] sm:text-[13px] font-medium text-black/50 dark:text-white/50">Selesai Bulan Ini</span>
                <div class="w-8 h-8 rounded-full bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold tabular-nums text-[#34C759] tracking-tight">
                    {{ number_format($completedThisMonth, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-medium text-black/40 dark:text-white/40">{{ now()->translatedFormat('F Y') }}</span>
            </div>
        </div>

        <!-- Rata-rata Yield (%) -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4.5 sm:p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[12px] sm:text-[13px] font-medium text-black/50 dark:text-white/50">Efisiensi Output (Yield)</span>
                <div class="w-8 h-8 rounded-full bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center shrink-0">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ number_format($avgYield, 1, ',', '.') }}%
                </span>
                <span class="text-[11px] font-medium text-black/40 dark:text-white/40">Target >= 95%</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. SEARCH & FILTER TOOLBAR                             -->
    <!-- ===================================================== -->
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
        <form method="GET" action="{{ route('production.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nomor SPKP atau nama produk..."
                    class="w-full h-10 pl-9.5 pr-4 rounded-[12px] text-[13px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 focus:border-[#007AFF]">
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
                <select name="status" onchange="this.form.submit()"
                    class="h-10 px-3.5 rounded-[12px] text-[13px] font-medium bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan (WIP)</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('production.index') }}"
                        class="h-10 px-3 rounded-[12px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 5. TABLE PERINTAH PRODUKSI (SPKP)                     -->
    <!-- ===================================================== -->
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
        <div class="overflow-x-auto scrollbar-thin">
            <table class="w-full text-left text-[13px] min-w-[950px]">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08] text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">No. SPKP</th>
                        <th class="py-3 px-4">Produk Target</th>
                        <th class="py-3 px-4 text-right">Target Output</th>
                        <th class="py-3 px-4 text-right">Hasil Aktual</th>
                        <th class="py-3 px-4">Lokasi Pabrik / Gudang</th>
                        <th class="py-3 px-4 text-right">Estimasi HPP/Unit</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                    @forelse($orders as $order)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                            <!-- No SPKP -->
                            <td class="py-3.5 px-4 font-semibold text-black dark:text-white font-mono text-[12.5px]">
                                <a href="{{ route('production.show', $order) }}" class="text-[#007AFF] hover:underline">
                                    {{ $order->order_number }}
                                </a>
                                <div class="text-[11px] text-black/45 dark:text-white/45 font-sans mt-0.5">
                                    {{ $order->planned_start_date ? \Carbon\Carbon::parse($order->planned_start_date)->translatedFormat('d M Y') : '-' }}
                                </div>
                            </td>

                            <!-- Produk Target -->
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-black dark:text-white">
                                    {{ $order->product?->name ?? 'Produk Dihapus' }}
                                </div>
                                <div class="text-[11px] text-black/45 dark:text-white/45">
                                    SKU: {{ $order->product?->code ?? '-' }}
                                </div>
                            </td>

                            <!-- Target Output -->
                            <td class="py-3.5 px-4 text-right tabular-nums font-semibold text-black dark:text-white">
                                {{ number_format((float) $order->planned_quantity, 0, ',', '.') }}
                                <span class="text-[11px] font-normal text-black/50 dark:text-white/50">{{ $order->product?->outputUnit?->name ?? 'pcs' }}</span>
                            </td>

                            <!-- Hasil Aktual -->
                            <td class="py-3.5 px-4 text-right tabular-nums">
                                @if($order->isCompleted())
                                    <span class="font-bold text-[#34C759]">
                                        {{ number_format((float) $order->actual_quantity, 0, ',', '.') }}
                                    </span>
                                    <div class="text-[10.5px] text-black/45 dark:text-white/45">
                                        Yield: {{ $order->yield_percentage }}%
                                    </div>
                                @else
                                    <span class="text-black/40 dark:text-white/40">-</span>
                                @endif
                            </td>

                            <!-- Lokasi Gudang -->
                            <td class="py-3.5 px-4 text-[12px] text-black/70 dark:text-white/70">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-black/40 dark:text-white/40">Bahan:</span>
                                    <span class="font-medium truncate max-w-[140px]">{{ $order->sourceLocation?->name ?? 'Default' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-black/40 dark:text-white/40">Hasil FG:</span>
                                    <span class="font-medium truncate max-w-[140px] text-[#007AFF]">{{ $order->targetLocation?->name ?? 'Default' }}</span>
                                </div>
                            </td>

                            <!-- HPP / Unit -->
                            <td class="py-3.5 px-4 text-right tabular-nums font-semibold text-black dark:text-white">
                                {{ $business->currency_symbol }} {{ number_format((float) $order->unit_cost_hpp, 0, ',', '.') }}
                                <div class="text-[10.5px] text-black/45 dark:text-white/45">
                                    Total: {{ $business->currency_symbol }} {{ number_format((float) $order->total_production_cost, 0, ',', '.') }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                @if($order->isDraft())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-black/40 dark:bg-white/40"></span>
                                        Draf
                                    </span>
                                @elseif($order->isInProgress())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#FF9500]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] animate-pulse"></span>
                                        Sedang Dikerjakan (WIP)
                                    </span>
                                @elseif($order->isCompleted())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#34C759]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                        Selesai
                                    </span>
                                @elseif($order->isCancelled())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#FF3B30]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('production.show', $order) }}"
                                    class="h-8 px-3 rounded-[10px] text-[12px] font-medium bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/15 transition inline-flex items-center gap-1">
                                    <span>Kelola</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                                <div class="w-12 h-12 rounded-full bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="factory" class="w-6 h-6"></i>
                                </div>
                                <p class="text-[14px] font-semibold text-black/70 dark:text-white/70">Belum Ada Perintah Kerja Produksi (SPKP)</p>
                                <p class="text-[12px] mt-1 max-w-sm mx-auto">Mulai produksi skala pabrik untuk mengubah bahan baku menjadi barang jadi dengan resep BOM.</p>
                                @if(\App\Support\Context::hasPermission('production.create'))
                                    <div class="mt-4">
                                        <a href="{{ route('production.create') }}" class="h-9 px-4 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] transition inline-flex items-center gap-1.5 shadow-sm">
                                            <i data-lucide="plus" class="w-4 h-4"></i>
                                            <span>Buat SPKP Pertama</span>
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
