@extends('layouts.app', [
    'title' => 'Transfer Stok Antar Outlet',
    'headerTitle' => 'Transfer Stok Antar Outlet',
    'headerSubtitle' => 'Kirim dan terima barang antar outlet atau gudang pusat dengan pencatatan mutasi otomatis'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showCreateModal: false,
    receiveModalOpen: false,
    receiveTarget: { id: null, number: '' },
    items: [
        @if($products->count() > 0)
            { product_id: '{{ $products->first()->id }}', quantity: 1 }
        @endif
    ],
    addItem() {
        this.items.push({ product_id: '{{ $products->first()?->id ?? '' }}', quantity: 1 });
    },
    removeItem(idx) {
        if (this.items.length > 1) {
            this.items.splice(idx, 1);
        }
    },
    openReceive(id, number) {
        this.receiveTarget = { id, number };
        this.receiveModalOpen = true;
    },
    closeReceive() {
        this.receiveModalOpen = false;
        this.receiveTarget = { id: null, number: '' };
    },
    submitReceive() {
        if (this.receiveTarget.id) {
            document.getElementById('form-receive-' + this.receiveTarget.id).submit();
        }
    }
}">

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
                Transfer Antar Cabang
            </span>
        </div>

        {{-- Baris 2: H1 Title + Status Badge + Action Buttons --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-black dark:text-white">
                    Transfer Stok Antar Outlet
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] border border-[#007AFF]/20">
                    {{ $transfers->total() }} Dokumen
                </span>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                @if(\App\Support\Context::hasPermission('inventory.manage'))
                <button type="button" @click="showCreateModal = true" class="min-h-[44px] sm:min-h-0 h-10 sm:h-9 px-4 rounded-xl text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Kirim Transfer Baru</span>
                </button>
                @endif
            </div>
        </div>

        {{-- Baris 3: Subtitle Faktual Ringkas (10-15 Kata) --}}
        <p class="mt-2 text-sm text-black/60 dark:text-white/60 max-w-3xl leading-relaxed">
            Kirim dan terima barang antar cabang atau gudang pusat dengan mutasi buku stok otomatis.
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
    <!-- 3. SORTABLE DENSE DATA TABLE (DESKTOP & TABLET)       -->
    <!-- ===================================================== -->
    <div x-data="coocaTableSort()" class="hidden sm:block rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] whitespace-nowrap bg-black/[0.015] dark:bg-white/[0.015]">
                        <x-table-sort-th :col="0" type="string" title="No. Transfer" />
                        <x-table-sort-th :col="1" type="date" title="Tanggal" />
                        <x-table-sort-th :col="2" type="string" title="Rute (Asal → Tujuan)" />
                        <x-table-sort-th :col="3" type="string" title="Pengirim / Penerima" />
                        <x-table-sort-th :col="4" type="number" title="Item" align="center" />
                        <x-table-sort-th :col="5" type="string" title="Status" align="center" />
                        <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($transfers as $tr)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3.5 px-4" data-sort-value="{{ $tr->transfer_number }}">
                            <div class="font-bold text-black dark:text-white tabular-nums text-[13px]">#{{ $tr->transfer_number }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">{{ $tr->notes ?? 'Mutasi internal' }}</div>
                        </td>
                        <td class="py-3.5 px-4 tabular-nums text-black/60 dark:text-white/60" data-sort-value="{{ $tr->transfer_date->format('Y-m-d') }}">
                            {{ $tr->transfer_date->format('d/m/Y') }}
                        </td>
                        <td class="py-3.5 px-4" data-sort-value="{{ strtolower(($tr->sourceLocation->name ?? '') . ' ' . ($tr->destinationLocation->name ?? '')) }}">
                            <div class="font-medium text-black dark:text-white flex items-center gap-1.5">
                                <span>{{ $tr->sourceLocation->name ?? 'Asal' }}</span>
                                <span class="text-[#007AFF] font-bold">→</span>
                                <span>{{ $tr->destinationLocation->name ?? 'Tujuan' }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-black/70 dark:text-white/70 text-[12px]" data-sort-value="{{ strtolower($tr->creator->name ?? '') }}">
                            <div>Kirim: <span class="font-semibold text-black dark:text-white">{{ $tr->creator->name ?? '-' }}</span></div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">Terima: {{ $tr->receiver->name ?? 'Belum' }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-center tabular-nums font-bold text-black dark:text-white" data-sort-value="{{ $tr->items->count() }}">
                            {{ $tr->items->count() }} Produk
                        </td>
                        <td class="py-3.5 px-4 text-center" data-sort-value="{{ $tr->status }}">
                            @if($tr->status === 'received')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Diterima
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Dalam Pengiriman
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($tr->status !== 'received')
                                @if(\App\Support\Context::hasPermission('inventory.manage'))
                                <button type="button" @click="openReceive('{{ $tr->id }}', '{{ $tr->transfer_number }}')" class="min-h-[44px] sm:min-h-0 h-8 px-3 rounded-lg text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition-all cursor-pointer">
                                    Konfirmasi Terima
                                </button>
                                <form id="form-receive-{{ $tr->id }}" action="{{ route('inventory.transfers.receive', $tr->id) }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                                @endif
                            @else
                                <span class="text-black/40 dark:text-white/40 text-[12px]">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr data-empty-row="true">
                        <td colspan="7" class="py-12 text-center text-black/40 dark:text-white/40">
                            <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center mx-auto mb-2.5 text-black/40 dark:text-white/40">
                                <i data-lucide="arrow-left-right" class="w-6 h-6"></i>
                            </div>
                            <p class="font-medium">Belum ada transfer stok antar outlet tercatat.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
        <div class="px-5 py-3.5 border-t border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.01]">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 4. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06] shadow-xs">
        @forelse($transfers as $tr)
        <div class="p-4 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.04] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <p class="text-[15px] font-bold text-black dark:text-white tabular-nums">#{{ $tr->transfer_number }}</p>
                    <span class="w-2 h-2 rounded-full {{ $tr->status === 'received' ? 'bg-[#34C759]' : 'bg-[#FF9500]' }} shrink-0"></span>
                </div>
                <p class="text-[13px] text-black/70 dark:text-white/70 mt-0.5">
                    {{ $tr->sourceLocation->name ?? 'Asal' }} <span class="text-[#007AFF] font-bold">→</span> {{ $tr->destinationLocation->name ?? 'Tujuan' }}
                </p>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5 tabular-nums">
                    {{ $tr->transfer_date->format('d/m/Y') }} · {{ $tr->items->count() }} produk ditransfer
                </p>
            </div>
            <div class="shrink-0">
                @if($tr->status !== 'received')
                    @if(\App\Support\Context::hasPermission('inventory.manage'))
                    <button type="button" @click="openReceive('{{ $tr->id }}', '{{ $tr->transfer_number }}')" class="min-h-[44px] h-10 px-3.5 rounded-xl text-[12px] font-semibold text-white bg-[#007AFF] active:scale-[0.97] transition-all cursor-pointer">
                        Terima
                    </button>
                    <form id="form-receive-{{ $tr->id }}" action="{{ route('inventory.transfers.receive', $tr->id) }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    @endif
                @else
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        Selesai
                    </span>
                @endif
            </div>
        </div>
        @empty
        <div class="py-10 text-center text-black/40 dark:text-white/40 text-[13px]">Belum ada transfer stok antar outlet.</div>
        @endforelse

        @if($transfers->hasPages())
        <div class="p-3 border-t border-black/[0.06] dark:border-white/[0.08]">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. APPLE MODAL SHEET: KIRIM TRANSFER STOK BARU        -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('inventory.manage'))
    <template x-teleport="body">
        <div x-show="showCreateModal"
            x-cloak
            class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4"
            role="dialog"
            aria-modal="true">

            {{-- Backdrop Blur Overlay --}}
            <div x-show="showCreateModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showCreateModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 w-full sm:max-w-xl rounded-t-[26px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto"
                @click.away="showCreateModal = false"
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
                            <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white">Kirim Transfer Stok Baru</h3>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 text-black/50 hover:text-black dark:text-white/50 dark:hover:text-white transition flex items-center justify-center cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('inventory.transfers.store') }}" method="POST" class="space-y-4 text-[13px]">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Lokasi Asal (Pengirim) *</label>
                            <select name="source_location_id" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-xl px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Lokasi Tujuan (Penerima) *</label>
                            <select name="destination_location_id" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-xl px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ $loop->last ? 'selected' : '' }} class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Tanggal Transfer *</label>
                            <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-xl px-3.5 text-[16px] sm:text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Catatan / No. Surat Jalan</label>
                            <input type="text" name="notes" placeholder="Misal: Surat Jalan #SJ-01..." class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-xl px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <!-- Items Row -->
                    <div class="space-y-3 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center justify-between">
                            <label class="text-[12px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">Produk yang Ditransfer:</label>
                            <button type="button" @click="addItem()" class="min-h-[44px] sm:min-h-0 h-8 px-3 rounded-lg text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all flex items-center gap-1 cursor-pointer">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Tambah Produk</span>
                            </button>
                        </div>

                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="flex items-center gap-2">
                                <select :name="'items[' + idx + '][product_id]'" x-model="item.product_id" required class="flex-1 h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-xl px-3 text-[16px] sm:text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" step="any" min="0.0001" :name="'items[' + idx + '][quantity]'" x-model.number="item.quantity" required placeholder="Qty" class="h-11 sm:h-10 w-28 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-xl px-2.5 text-[16px] sm:text-[13px] text-center tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <button type="button" @click="removeItem(idx)" :disabled="items.length <= 1" class="min-w-[40px] min-h-[40px] flex items-center justify-center text-black/30 hover:text-[#FF3B30] dark:text-white/30 dark:hover:text-[#FF453A] disabled:opacity-20 transition-colors cursor-pointer">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3.5 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="button" @click="showCreateModal = false" class="min-h-[44px] sm:min-h-0 h-10 px-4 rounded-xl text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.98] transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="min-h-[44px] sm:min-h-0 h-10 px-5 rounded-xl text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition-all shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                            Kirim Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
    @endif

    <!-- ===================================================== -->
    <!-- 6. APPLE ALERT DIALOG: KONFIRMASI TERIMA TRANSFER     -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('inventory.manage'))
    <template x-teleport="body">
        <div x-show="receiveModalOpen"
            x-cloak
            class="fixed inset-0 z-[220] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true">

            {{-- Backdrop Blur Overlay --}}
            <div x-show="receiveModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="closeReceive()"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 w-full max-w-[340px] rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden text-center shadow-2xl"
                @click.away="closeReceive()"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <div class="px-6 pt-6 pb-5">
                    <div class="w-12 h-12 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="package-check" class="w-6 h-6"></i>
                    </div>
                    <p class="text-[17px] font-bold text-black dark:text-white">Konfirmasi Terima?</p>
                    <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-relaxed">
                        Pastikan barang transfer <span x-text="'#' + receiveTarget.number" class="font-bold text-black dark:text-white tabular-nums"></span> sudah sampai dan dihitung fisik dengan benar di lokasi tujuan.
                    </p>
                </div>

                <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[14px] font-semibold">
                    <button type="button" @click="closeReceive()" class="min-h-[48px] py-3 text-black/70 dark:text-white/70 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer flex items-center justify-center">
                        Batal
                    </button>
                    <button type="button" @click="submitReceive()" class="min-h-[48px] py-3 text-[#007AFF] dark:text-[#0A84FF] font-bold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer flex items-center justify-center">
                        Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </template>
    @endif

</div>
@endsection
