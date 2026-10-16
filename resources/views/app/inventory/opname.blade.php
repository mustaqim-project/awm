@extends('layouts.app', [
    'title' => 'Stock Opname Fisik',
    'headerTitle' => 'Stock Opname Fisik',
    'headerSubtitle' => 'Hitung fisik bahan baku atau produk, tinjau selisih, lalu rekonsiliasi stok secara terkontrol'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showCreateModal: false,
    reconcileModalOpen: false,
    reconcileTarget: { id: null, number: '' },
    selectedLocationId: '{{ $locations->first()?->id ?? '' }}',
    items: [
        { material_id: '', product_id: '', item_type: 'material', physical_quantity: 0 }
    ],
    addItem() {
        this.items.push({ material_id: '', product_id: '', item_type: 'material', physical_quantity: 0 });
    },
    removeItem(idx) {
        if (this.items.length > 1) {
            this.items.splice(idx, 1);
        }
    },
    changeItemType(item) {
        item.material_id = '';
        item.product_id = '';
    },
    openReconcile(id, number) {
        this.reconcileTarget = { id, number };
        this.reconcileModalOpen = true;
    },
    closeReconcile() {
        this.reconcileModalOpen = false;
        this.reconcileTarget = { id: null, number: '' };
    },
    submitReconcile() {
        if (this.reconcileTarget.id) {
            document.getElementById('form-reconcile-' + this.reconcileTarget.id).submit();
        }
    },

    // Import Physical Opname Excel
    showImportModal: false,
    importLocationId: '{{ $locations->first()?->id ?? '' }}',
    importNotes: '',
    importFile: null,
    importFileName: '',
    importLoading: false,
    importPreview: null,
    importFilter: 'all',
    importExecuting: false,

    handleImportFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            this.importFile = file;
            this.importFileName = file.name;
            this.uploadAndPreviewOpname();
        }
    },

    uploadAndPreviewOpname() {
        if (!this.importFile) return;
        this.importLoading = true;
        const formData = new FormData();
        formData.append('file', this.importFile);
        formData.append('location_id', this.importLocationId);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route('inventory.opnames.import-preview') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw new Error(err.message || 'Gagal memproses file Excel.'); });
            }
            return res.json();
        })
        .then(res => {
            this.importLoading = false;
            if (res.success) {
                this.importPreview = res.data;
            } else {
                alert(res.message || 'Gagal menganalisis file opname.');
            }
        })
        .catch(err => {
            this.importLoading = false;
            alert(err.message || 'Terjadi kesalahan saat mengunggah file.');
        });
    },

    async executeOpnameImport() {
        if (!this.importPreview || !this.importPreview.rows || this.importPreview.rows.length === 0) {
            return;
        }

        if (this.importPreview.valid_count <= 0) {
            alert('Tidak ada item valid untuk diimport.');
            return;
        }

        if (!confirm('Apakah Anda yakin ingin menyimpan dan membuat dokumen stock opname fisik ini?')) {
            return;
        }

        this.importExecuting = true;
        fetch('{{ route('inventory.opnames.import-execute') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                location_id: this.importLocationId,
                notes: this.importNotes,
                rows: this.importPreview.rows
            })
        })
        .then(res => res.json())
        .then(res => {
            this.importExecuting = false;
            if (res.success) {
                alert(res.message);
                window.location.reload();
            } else {
                alert(res.message || 'Gagal menyimpan stock opname.');
            }
        })
        .catch(err => {
            this.importExecuting = false;
            alert(err.message || 'Terjadi kesalahan sistem saat menyimpan opname.');
        });
    },

    resetImport() {
        this.importFile = null;
        this.importFileName = '';
        this.importPreview = null;
        this.importFilter = 'all';
        this.importLoading = false;
        this.importExecuting = false;
    }
}">

    <!-- ===================================================== -->
    <!-- 1. STANDAR OWNER PANEL: 3-BARIS BENTO APPLE HIG HEADER-->
    <!-- ===================================================== -->
    <header class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 lg:p-7 shadow-[0_2px_12px_rgba(0,0,0,0.03)] dark:shadow-[0_2px_12px_rgba(0,0,0,0.2)]">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1.5 min-w-0">
                <!-- Baris 1: Overline Typographic Hierarchy -->
                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                        <span>Katalog &amp; Inventori</span>
                    </span>
                    <span class="text-black/20 dark:text-white/20">•</span>
                    <span>Fisik &amp; Rekonsiliasi Saldo</span>
                </div>

                <!-- Baris 2: H1 Title + Entity Status Badge -->
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#1C1C1E] dark:text-[#F2F2F7]">
                        Stock Opname Fisik
                    </h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                        Audit Fisik
                    </span>
                </div>

                <!-- Baris 3: Subtitle Faktual -->
                <p class="text-xs sm:text-[13px] text-black/55 dark:text-white/55 leading-relaxed max-w-3xl">
                    Pencatatan hitung fisik persediaan bahan baku dan produk secara berkala serta audit rekonsiliasi selisih kuantitas ke buku stok sistem.
                </p>
            </div>

            <!-- Toolbar Actions -->
            <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap shrink-0 self-start lg:self-center">
                @if(\App\Support\Context::hasPermission('inventory.manage'))
                <a :href="'{{ route('inventory.opnames.export-blank') }}?location_id=' + (selectedLocationId || '{{ $locations->first()?->id ?? '' }}')"
                   class="min-h-[44px] h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[13px] font-medium border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition flex items-center justify-center gap-2 cursor-pointer"
                   title="Unduh formulir Excel kosong dengan daftar item persediaan saat ini">
                    <i data-lucide="download" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    <span class="hidden sm:inline">Unduh Blanko (.xlsx)</span>
                    <span class="sm:hidden">Blanko</span>
                </a>

                <button type="button" @click="showImportModal = true; importLocationId = selectedLocationId;"
                   class="min-h-[44px] h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[13px] font-medium border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition flex items-center justify-center gap-2 cursor-pointer"
                   title="Unggah hasil hitungan fisik dari file Excel">
                    <i data-lucide="upload" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Import Excel</span>
                </button>

                <button type="button" @click="showCreateModal = true"
                   class="min-h-[44px] h-11 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Mulai Opname</span>
                </button>
                @endif
            </div>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. MODULE TABS (SSOT)                                 -->
    <!-- ===================================================== -->
    <x-module-tabs module="inventory" />

    <!-- Session Banners -->
    @if(session('success'))
    <div class="rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2.5">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 p-4 text-[13px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
        <div class="flex items-center gap-2 font-semibold">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span>Stock opname belum dapat disimpan:</span>
        </div>
        <ul class="list-disc pl-6 text-[12px] space-y-0.5 opacity-90">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 3. SORTABLE DATA TABLE (DESKTOP & TABLET)             -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-xs"
         x-data="coocaTableSort()">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.015] dark:bg-white/[0.02] whitespace-nowrap">
                        <x-table-sort-th :col="0" type="string" title="No. Opname" />
                        <x-table-sort-th :col="1" type="date" title="Tanggal & Lokasi" />
                        <x-table-sort-th :col="2" type="string" title="Pelaksana" />
                        <x-table-sort-th :col="3" type="number" title="Jumlah Item" align="center" />
                        <x-table-sort-th :col="4" type="string" title="Status" align="center" />
                        <th class="py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 text-right">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($opnames as $op)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td data-sort-value="{{ $op->opname_number }}" class="py-3.5 px-4">
                            <div class="font-semibold text-black dark:text-white tabular-nums">#{{ $op->opname_number }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">{{ $op->notes ?? 'Stock taking rutin' }}</div>
                        </td>
                        <td data-sort-value="{{ $op->opname_date->format('Y-m-d') }}" class="py-3.5 px-4">
                            <div class="tabular-nums font-medium text-black dark:text-white">{{ $op->opname_date->format('d/m/Y') }}</div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[10px] font-medium bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 mt-0.5">
                                {{ $op->location->name ?? 'Outlet' }}
                            </span>
                        </td>
                        <td data-sort-value="{{ strtolower($op->conductor->name ?? '') }}" class="py-3.5 px-4 text-black/70 dark:text-white/70 font-medium">
                            {{ $op->conductor->name ?? 'Staff' }}
                        </td>
                        <td data-sort-value="{{ (int) $op->items->count() }}" class="py-3.5 px-4 text-center tabular-nums font-bold text-black dark:text-white">
                            {{ $op->items->count() }} Item
                        </td>
                        <td data-sort-value="{{ $op->status }}" class="py-3.5 px-4 text-center">
                            @if($op->status === 'reconciled')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Direkonsiliasi
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Sedang Dihitung
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($op->status !== 'reconciled')
                                @if(\App\Support\Context::hasPermission('inventory.manage'))
                                <button type="button" @click="openReconcile('{{ $op->id }}', '{{ $op->opname_number }}')" class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-white bg-[#34C759] hover:bg-[#2FB350] active:scale-[0.97] transition-all shadow-xs cursor-pointer">
                                    Rekonsiliasi
                                </button>
                                <form id="form-reconcile-{{ $op->id }}" action="{{ route('inventory.opnames.reconcile', $op->id) }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                                @endif
                            @else
                                <span class="text-black/40 dark:text-white/40 text-[12px] font-medium">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-black/40 dark:text-white/40">
                            <i data-lucide="clipboard-check" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                            <div>Belum ada sesi stock opname.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($opnames->hasPages())
        <div class="px-5 py-4 border-t border-black/[0.06] dark:border-white/[0.08] bg-black/[0.015] dark:bg-white/[0.02]">
            {{ $opnames->links() }}
        </div>
        @endif
    <!-- ===================================================== -->
    <!-- 4. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06] shadow-xs">
        @forelse($opnames as $op)
        <div class="p-4 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.04] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <p class="text-[15px] font-semibold text-black dark:text-white tabular-nums">#{{ $op->opname_number }}</p>
                    <span class="w-2 h-2 rounded-full {{ $op->status === 'reconciled' ? 'bg-[#34C759]' : 'bg-[#FF9500]' }} shrink-0"></span>
                </div>
                <p class="text-[13px] text-black/55 dark:text-white/55 mt-0.5 tabular-nums">
                    {{ $op->opname_date->format('d/m/Y') }} · {{ $op->location->name ?? 'Outlet' }}
                </p>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">
                    {{ $op->items->count() }} item dihitung · Pelaksana: {{ $op->conductor->name ?? 'Staff' }}
                </p>
            </div>
            <div class="shrink-0">
                @if($op->status !== 'reconciled')
                    @if(\App\Support\Context::hasPermission('inventory.manage'))
                    <button type="button" @click="openReconcile('{{ $op->id }}', '{{ $op->opname_number }}')" class="min-h-[44px] px-3.5 rounded-[10px] text-[12px] font-semibold text-white bg-[#34C759] active:scale-[0.97] transition-all flex items-center justify-center shadow-xs">
                        Rekonsiliasi
                    </button>
                    <form id="form-reconcile-{{ $op->id }}" action="{{ route('inventory.opnames.reconcile', $op->id) }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    @endif
                @else
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        Selesai
                    </span>
                @endif
            </div>
        </div>
        @empty
        <div class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
            <i data-lucide="clipboard-check" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
            <div>Belum ada sesi stock opname.</div>
        </div>
        @endforelse

        @if($opnames->hasPages())
        <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
            {{ $opnames->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. APPLE SHEET: MULAI STOCK OPNAME BARU               -->
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

            <div class="relative z-10 w-full sm:max-w-xl rounded-t-[24px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] max-h-[90vh] overflow-y-auto"
                @click.away="showCreateModal = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0">

                <!-- Mobile Grabber Handle -->
                <div class="sm:hidden w-10 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto mb-2"></div>

                <div class="flex items-center justify-between pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                            <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[17px] font-bold text-black dark:text-white">Mulai Stock Opname Baru</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Formulir hitung fisik persediaan bahan dan produk</p>
                        </div>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white transition-colors cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('inventory.opnames.store') }}" method="POST" class="space-y-4 text-[13px]">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Lokasi Outlet / Gudang *</label>
                            <select name="location_id" x-model="selectedLocationId" required class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Tanggal Opname *</label>
                            <input type="date" name="opname_date" value="{{ date('Y-m-d') }}" required class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Catatan Dokumen</label>
                        <input type="text" name="notes" placeholder="Misal: Opname fisik bulanan persediaan..." class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <!-- Items Row -->
                    <div class="space-y-3 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center justify-between">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Item yang Dihitung Fisik:</label>
                            <button type="button" @click="addItem()" class="min-h-[44px] sm:min-h-0 h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center gap-1 cursor-pointer">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Tambah Item</span>
                            </button>
                        </div>

                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="grid grid-cols-[5.5rem_minmax(0,1fr)_6.5rem_auto] items-center gap-2 p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                                <select x-model="item.item_type" @change="changeItemType(item)" class="h-10 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2 text-[12px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <option value="material" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Bahan</option>
                                    <option value="product" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">Produk</option>
                                </select>
                                <select x-show="item.item_type === 'material'" :name="'items[' + idx + '][material_id]'" x-model="item.material_id" class="h-10 min-w-0 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <option value="" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">-- Pilih Bahan Baku --</option>
                                    @foreach($materials as $m)
                                        <option value="{{ $m->id }}" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">{{ $m->name }} ({{ $m->unit->code ?? 'unit' }})</option>
                                    @endforeach
                                </select>
                                <select x-show="item.item_type === 'product'" :name="'items[' + idx + '][product_id]'" x-model="item.product_id" class="h-10 min-w-0 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <option value="" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">{{ $product->name }}{{ $product->code ? ' (' . $product->code . ')' : '' }}</option>
                                    @endforeach
                                </select>
                                <input type="number" step="any" :name="'items[' + idx + '][physical_quantity]'" x-model.number="item.physical_quantity" placeholder="Qty Fisik" class="h-10 w-24 bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[8px] px-2 text-[13px] text-center tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <button type="button" @click="removeItem(idx)" :disabled="items.length <= 1" class="min-w-[44px] min-h-[44px] sm:min-w-0 sm:min-h-0 p-2 text-black/30 hover:text-[#FF3B30] dark:text-white/30 dark:hover:text-[#FF453A] disabled:opacity-20 transition-colors cursor-pointer">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="button" @click="showCreateModal = false" class="min-h-[44px] h-10 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.98] transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="min-h-[44px] h-10 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                            Simpan Data Opname
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
    @endif

    <!-- ===================================================== -->
    <!-- 6. APPLE ALERT DIALOG: REKONSILIASI OPNAME             -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('inventory.manage'))
    <template x-teleport="body">
        <div x-show="reconcileModalOpen"
            x-cloak
            class="fixed inset-0 z-[220] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true">

            {{-- Backdrop Blur Overlay --}}
            <div x-show="reconcileModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="closeReconcile()"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 w-full max-w-[320px] rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.35)]"
                @click.away="closeReconcile()"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <div class="px-5 pt-6 pb-5">
                    <div class="w-12 h-12 rounded-full bg-[#34C759]/15 text-[#34C759] flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="check-check" class="w-6 h-6"></i>
                    </div>
                    <p class="text-[17px] font-bold text-black dark:text-white">Rekonsiliasi Stok?</p>
                    <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-snug">
                        Terapkan selisih hitungan fisik opname <span x-text="'#' + reconcileTarget.number" class="font-bold text-black dark:text-white tabular-nums"></span> langsung ke saldo stok sistem.
                    </p>
                </div>

                <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[14px] font-medium">
                    <button type="button" @click="closeReconcile()" class="min-h-[48px] py-3 text-black/70 dark:text-white/70 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="submitReconcile()" class="min-h-[48px] py-3 text-[#34C759] font-bold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                        Terapkan
                    </button>
                </div>
            </div>
        </div>
    </template>
    <!-- ===================================================== -->
    <!-- 5. APPLE BENTO XXL PRE-COMMIT PREVIEW MODAL: OPNAME   -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showImportModal"
            x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6"
            role="dialog"
            aria-modal="true">

            {{-- Backdrop Blur Overlay --}}
            <div x-show="showImportModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="if (!importExecuting) showImportModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 w-full max-w-5xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col max-h-[92vh] overflow-hidden"
                @click.away="if (!importExecuting) showImportModal = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <!-- Modal Header -->
                <div class="px-6 py-4.5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F2F2F7] dark:bg-[#252528]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[17px] font-bold text-black dark:text-white">Import &amp; Analisis Hitungan Fisik Opname</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Unggah berkas Excel opname fisik untuk audit real-time sebelum dikomit ke buku stok</p>
                        </div>
                    </div>

                    <button type="button" @click="if (!importExecuting) showImportModal = false"
                        class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center transition cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-5 flex-1">

                <!-- Location & Notes selector -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Lokasi Gudang / Outlet *</label>
                        <select x-model="importLocationId" :disabled="importPreview !== null || importLoading" class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" class="bg-white dark:bg-[#2C2C2E] text-black dark:text-white">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Catatan Dokumen Opname</label>
                        <input type="text" x-model="importNotes" :disabled="importExecuting" placeholder="Misal: Opname fisik tutup buku bulanan..." class="w-full h-11 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <!-- State 1: Upload Zone (Belum ada preview) -->
                <div x-show="!importPreview" class="space-y-4">
                    <!-- Langkah 1: Unduh Lembar Blanko Sesuai Lokasi Terpilih -->
                    <div class="p-4 rounded-[16px] bg-[#FF9500]/[0.08] dark:bg-[#FF9500]/[0.12] border border-[#FF9500]/25 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-[#B26A00] dark:text-[#FF9F0A] font-bold text-[13px]">
                                <span class="w-5 h-5 rounded-full bg-[#FF9500]/15 flex items-center justify-center text-[11px] font-bold">1</span>
                                <span>Langkah 1: Unduh Lembar Opname Blanko</span>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60">Unduh lembar hitung fisik resmi yang telah memuat seluruh katalog produk & bahan aktif di lokasi gudang yang dipilih.</p>
                        </div>
                        <a :href="'{{ route('inventory.opnames.export-blank') }}?location_id=' + importLocationId"
                           class="h-9 px-4 rounded-[10px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[12px] font-semibold transition flex items-center justify-center gap-1.5 shadow-sm shrink-0 active:scale-[0.98]">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>Unduh Lembar Opname (.xlsx)</span>
                        </a>
                    </div>

                    <!-- Langkah 2: Upload Dropzone -->
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-2 text-black/80 dark:text-white/80 font-bold text-[13px]">
                            <span class="w-5 h-5 rounded-full bg-black/10 dark:bg-white/10 flex items-center justify-center text-[11px] font-bold">2</span>
                            <span>Langkah 2: Unggah Berkas Excel Hasil Hitungan Fisik</span>
                        </div>
                        <span class="text-[11px] text-black/40 dark:text-white/40">Maksimal 10 MB (.xlsx, .csv)</span>
                    </div>

                    <label class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#FF9500] dark:hover:border-[#FF9F0A] rounded-[20px] p-8 flex flex-col items-center justify-center text-center cursor-pointer transition bg-black/[0.01] dark:bg-white/[0.01] hover:bg-[#FF9500]/[0.02]">
                        <input type="file" class="hidden" accept=".xlsx, .xls, .csv" @change="handleImportFileSelect($event)" :disabled="importLoading">
                        
                        <div class="space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-full bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                                <i data-lucide="upload-cloud" class="w-7 h-7" x-show="!importLoading"></i>
                                <i data-lucide="loader" class="w-7 h-7 animate-spin" x-show="importLoading"></i>
                            </div>
                            <div class="space-y-1">
                                <div class="text-[15px] font-bold text-black dark:text-white" x-text="importLoading ? 'Menganalisis Hitungan Fisik vs Sistem...' : 'Pilih Berkas Excel Opname atau Tarik ke Sini'"></div>
                                <div class="text-[12px] text-black/50 dark:text-white/50">Mendukung file .XLSX hasil unduhan lembar opname sistem</div>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- State 2: Interactive Pre-Commit Preview -->
                <div x-show="importPreview" class="space-y-5">
                    
                    <!-- KPI Summary Bento Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
                            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Total Item</span>
                            <div class="text-xl font-bold font-mono text-black dark:text-white" x-text="importPreview ? importPreview.total_rows : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-emerald-500/[0.08] border border-emerald-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Sesuai (Cocok)</span>
                            <div class="text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400" x-text="importPreview ? importPreview.match_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-blue-500/[0.08] border border-blue-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Surplus (+)</span>
                            <div class="text-xl font-bold font-mono text-blue-600 dark:text-blue-400" x-text="importPreview ? importPreview.surplus_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-amber-500/[0.08] border border-amber-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Defisit (-)</span>
                            <div class="text-xl font-bold font-mono text-amber-600 dark:text-amber-400" x-text="importPreview ? importPreview.deficit_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-rose-500/[0.08] border border-rose-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wider">Error / Tidak Ditemukan</span>
                            <div class="text-xl font-bold font-mono text-rose-600 dark:text-rose-400" x-text="importPreview ? importPreview.error_count : 0"></div>
                        </div>
                    </div>

                    <!-- Filter Control Bar -->
                    <div class="p-3.5 rounded-[16px] bg-[#F5F5F7] dark:bg-[#252528] border border-black/[0.05] dark:border-white/[0.06] flex items-center justify-between">
                        <div class="flex items-center gap-1 bg-black/[0.05] dark:bg-white/[0.08] p-1 rounded-[12px] text-[12px] font-medium overflow-x-auto">
                            <button type="button" @click="importFilter = 'all'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'all' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60'">
                                Semua (<span x-text="importPreview ? importPreview.total_rows : 0"></span>)
                            </button>
                            <button type="button" @click="importFilter = 'match'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'match' ? 'bg-white dark:bg-[#1C1C1E] text-emerald-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                Cocok (<span x-text="importPreview ? importPreview.match_count : 0"></span>)
                            </button>
                            <button type="button" @click="importFilter = 'surplus'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'surplus' ? 'bg-white dark:bg-[#1C1C1E] text-blue-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                Lebih (+) (<span x-text="importPreview ? importPreview.surplus_count : 0"></span>)
                            </button>
                            <button type="button" @click="importFilter = 'deficit'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'deficit' ? 'bg-white dark:bg-[#1C1C1E] text-amber-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                Kurang (-) (<span x-text="importPreview ? importPreview.deficit_count : 0"></span>)
                            </button>
                            <button type="button" @click="importFilter = 'error'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importFilter === 'error' ? 'bg-white dark:bg-[#1C1C1E] text-rose-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                Error (<span x-text="importPreview ? importPreview.error_count : 0"></span>)
                            </button>
                        </div>
                    </div>

                    <!-- Preview Table -->
                    <div class="border border-black/[0.08] dark:border-white/[0.1] rounded-[18px] overflow-hidden bg-white dark:bg-[#1C1C1E]">
                        <div class="max-h-[380px] overflow-y-auto">
                            <table class="w-full text-left text-[12px] border-collapse">
                                <thead class="sticky top-0 bg-[#F5F5F7] dark:bg-[#252528] border-b border-black/[0.08] dark:border-white/[0.1] text-black/60 dark:text-white/60 font-semibold z-10">
                                    <tr>
                                        <th class="py-2.5 px-3 w-16 text-center">Status</th>
                                        <th class="py-2.5 px-3">Tipe</th>
                                        <th class="py-2.5 px-3">Nama Item</th>
                                        <th class="py-2.5 px-3">Kode / SKU</th>
                                        <th class="py-2.5 px-3">Satuan</th>
                                        <th class="py-2.5 px-3 text-right">Stok Sistem</th>
                                        <th class="py-2.5 px-3 text-right">Hitungan Fisik</th>
                                        <th class="py-2.5 px-3 text-right">Selisih</th>
                                        <th class="py-2.5 px-3">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                                    <template x-for="(row, idx) in (importPreview ? importPreview.rows.filter(r => importFilter === 'all' || r.status === importFilter) : [])" :key="idx">
                                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                                            <td class="py-2.5 px-3 text-center">
                                                <span x-show="row.status === 'match'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">Cocok</span>
                                                <span x-show="row.status === 'surplus'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-600 border border-blue-500/20">Surplus</span>
                                                <span x-show="row.status === 'deficit'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20">Defisit</span>
                                                <span x-show="row.status === 'error'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20">Error</span>
                                            </td>
                                            <td class="py-2.5 px-3 text-black/70 dark:text-white/70">
                                                <span class="inline-block uppercase text-[10px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/5 font-semibold" x-text="row.item_type === 'material' ? 'Bahan' : 'Produk'"></span>
                                            </td>
                                            <td class="py-2.5 px-3 font-semibold text-black dark:text-white" x-text="row.item_name || '-'"></td>
                                            <td class="py-2.5 px-3 font-mono text-black/70 dark:text-white/70" x-text="row.item_code || '-'"></td>
                                            <td class="py-2.5 px-3 text-black/70 dark:text-white/70" x-text="row.unit || '-'"></td>
                                            <td class="py-2.5 px-3 text-right font-mono text-black/80 dark:text-white/80" x-text="row.system_qty"></td>
                                            <td class="py-2.5 px-3 text-right font-mono font-bold text-black dark:text-white" x-text="row.physical_qty"></td>
                                            <td class="py-2.5 px-3 text-right font-mono font-bold"
                                                :class="row.difference > 0 ? 'text-blue-600 dark:text-blue-400' : (row.difference < 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400')">
                                                <span x-text="row.difference > 0 ? ('+' + row.difference) : row.difference"></span>
                                            </td>
                                            <td class="py-2.5 px-3">
                                                <div class="space-y-0.5">
                                                    <template x-for="(msg, mi) in row.messages" :key="mi">
                                                        <div class="text-[11px]" :class="row.status === 'error' ? 'text-rose-600 dark:text-rose-400' : 'text-black/60 dark:text-white/60'" x-text="msg"></div>
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F2F2F7] dark:bg-[#252528]">
                <div>
                    <button type="button" x-show="importPreview" @click="resetImport()"
                        class="h-10 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer flex items-center gap-1.5"
                        :disabled="importExecuting">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>Ganti Berkas</span>
                    </button>
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" @click="if (!importExecuting) showImportModal = false"
                        class="h-10 px-5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer"
                        :disabled="importExecuting">
                        Batal
                    </button>

                    <button type="button" x-show="importPreview" @click="executeOpnameImport()"
                        class="h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-sm shadow-[#007AFF]/25 active:scale-[0.98] transition cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="importExecuting || !importPreview || importPreview.valid_count === 0">
                        <i data-lucide="loader" class="w-4 h-4 animate-spin" x-show="importExecuting"></i>
                        <i data-lucide="check" class="w-4 h-4" x-show="!importExecuting"></i>
                        <span x-text="importExecuting ? 'Menyimpan...' : ('Simpan Dokumen Opname (' + (importPreview ? importPreview.valid_count : 0) + ' Item)')"></span>
                    </button>
                </div>
            </div>

        </div>
    </div>
    </template>
    @endif

</div>
@endsection
