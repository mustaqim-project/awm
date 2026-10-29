@extends('layouts.admin', [
    'title' => 'Kelola Bisnis (Tenants) - Admin Console',
    'headerTitle' => 'Manajemen Tenant Bisnis UMKM',
    'headerSubtitle' => 'Kelola seluruh workspace bisnis yang beroperasi di platform Cooca',
])

@section('content')
    <div class="space-y-6" x-data="{
        showSuspendModal: false,
        suspendBizName: '',
        suspendActionUrl: '',
        selectedIds: [],
        showBulkSuspendModal: false,
        bulkSuspendReason: '',
        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedIds = {{ Js::from($businesses->pluck('id')) }}.map(id => String(id));
            } else {
                this.selectedIds = [];
            }
        },
        isAllSelected() {
            const pageIds = {{ Js::from($businesses->pluck('id')) }}.map(id => String(id));
            return pageIds.length > 0 && pageIds.every(id => this.selectedIds.includes(id));
        }
    }">

        <!-- Bento KPI Summary Grid (Adaptive 2-Column Micro-Bento Mobile, 4-Column Desktop) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            
            <!-- Total Workspace -->
            <div class="rounded-[18px] sm:rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[11px] sm:text-[12px] font-bold text-black/50 dark:text-white/50 truncate">Total Workspace</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[10px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF] shrink-0">
                        <i data-lucide="building-2" class="w-3.5 h-3.5 sm:w-4 sm:h-4" stroke-width="1.8"></i>
                    </div>
                </div>
                <div class="my-2 sm:my-3">
                    <div class="text-[20px] sm:text-[26px] font-extrabold tabular-nums tracking-tight text-black dark:text-white">
                        {{ number_format($totalBusinesses, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-black/45 dark:text-white/45 mt-0.5 truncate">Tenant terdaftar di sistem</div>
                </div>
                <div class="text-[10px] sm:text-[11px] text-[#007AFF] dark:text-[#0A84FF] font-semibold pt-1 border-t border-black/[0.04] dark:border-white/[0.06] truncate">
                    Platform SaaS UMKM
                </div>
            </div>

            <!-- Workspace Aktif -->
            <div class="rounded-[18px] sm:rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[11px] sm:text-[12px] font-bold text-black/50 dark:text-white/50 truncate">Workspace Aktif</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[10px] bg-[#34C759]/10 flex items-center justify-center text-[#34C759] dark:text-[#30D158] shrink-0">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4" stroke-width="1.8"></i>
                    </div>
                </div>
                <div class="my-2 sm:my-3">
                    <div class="text-[20px] sm:text-[26px] font-extrabold tabular-nums tracking-tight text-[#34C759] dark:text-[#30D158]">
                        {{ number_format($activeBusinesses, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-black/45 dark:text-white/45 mt-0.5 truncate">Transaksi normal</div>
                </div>
                <div class="text-[10px] sm:text-[11px] text-[#34C759] dark:text-[#30D158] font-semibold pt-1 border-t border-black/[0.04] dark:border-white/[0.06] truncate">
                    Status operasional aktif
                </div>
            </div>

            <!-- Langganan Core -->
            <div class="rounded-[18px] sm:rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[11px] sm:text-[12px] font-bold text-black/50 dark:text-white/50 truncate">Langganan Core</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[10px] bg-[#AF52DE]/10 flex items-center justify-center text-[#AF52DE] dark:text-[#BF5AF2] shrink-0">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 sm:w-4 sm:h-4" stroke-width="1.8"></i>
                    </div>
                </div>
                <div class="my-2 sm:my-3">
                    <div class="text-[20px] sm:text-[26px] font-extrabold tabular-nums tracking-tight text-[#AF52DE] dark:text-[#BF5AF2]">
                        {{ number_format($coreBusinesses, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-black/45 dark:text-white/45 mt-0.5 truncate">Pelanggan aktif</div>
                </div>
                <div class="text-[10px] sm:text-[11px] text-[#AF52DE] dark:text-[#BF5AF2] font-semibold pt-1 border-t border-black/[0.04] dark:border-white/[0.06] truncate">
                    Paket berbayar aktif
                </div>
            </div>

            <!-- Ditangguhkan -->
            <div class="rounded-[18px] sm:rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-3.5 sm:p-5 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[11px] sm:text-[12px] font-bold text-black/50 dark:text-white/50 truncate">Ditangguhkan</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[10px] bg-[#FF3B30]/10 flex items-center justify-center text-[#FF3B30] dark:text-[#FF453A] shrink-0">
                        <i data-lucide="ban" class="w-3.5 h-3.5 sm:w-4 sm:h-4" stroke-width="1.8"></i>
                    </div>
                </div>
                <div class="my-2 sm:my-3">
                    <div class="text-[20px] sm:text-[26px] font-extrabold tabular-nums tracking-tight text-[#FF3B30] dark:text-[#FF453A]">
                        {{ number_format($suspendedBusinesses, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-black/45 dark:text-white/45 mt-0.5 truncate">Dibekukan sementara</div>
                </div>
                <div class="text-[10px] sm:text-[11px] text-[#FF3B30] dark:text-[#FF453A] font-semibold pt-1 border-t border-black/[0.04] dark:border-white/[0.06] truncate">
                    Perlu verifikasi
                </div>
            </div>

        </div>

        <!-- Filter & Search Bento Toolbar -->
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-4 shadow-sm">
            <form method="GET" action="{{ route('admin.businesses.index') }}"
                class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="relative flex-1 max-w-md">
                    <i data-lucide="search"
                        class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3.5 top-1/2 -translate-y-1/2"
                        stroke-width="1.5"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama bisnis, slug, email, telepon..."
                        class="w-full h-10 pl-10 pr-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.06] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <select name="plan" onchange="this.form.submit()"
                        class="h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.06] rounded-[12px] text-[13px] font-medium text-black/75 dark:text-white/75 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="">Semua Paket</option>
                        <option value="core" {{ request('plan') === 'core' ? 'selected' : '' }}>Cooca (Core)</option>
                        <option value="free" {{ request('plan') === 'free' ? 'selected' : '' }}>Free Plan</option>
                    </select>
                    <select name="status" onchange="this.form.submit()"
                        class="h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.06] rounded-[12px] text-[13px] font-medium text-black/75 dark:text-white/75 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
                    </select>
                    @if (request()->hasAny(['search', 'plan', 'status']))
                        <a href="{{ route('admin.businesses.index') }}"
                            class="h-10 px-3 rounded-[12px] text-[12px] font-bold text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors inline-flex items-center gap-1.5">
                            <i data-lucide="x" class="w-3.5 h-3.5" stroke-width="2"></i>
                            <span>Reset Filter</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Bento Table Container -->
        <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02]">
                            <th class="w-10 px-4 py-3.5 text-center">
                                <input type="checkbox"
                                    @change="toggleSelectAll($event)"
                                    :checked="isAllSelected()"
                                    class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]/30 border-black/20 dark:border-white/20 bg-white dark:bg-[#1C1C1E] cursor-pointer"
                                    title="Pilih Semua di Halaman Ini">
                            </th>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Bisnis &amp; Identitas</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Paket Langganan</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Pengguna &amp; Tim</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Katalog &amp; Resep</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Status</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($businesses as $biz)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors"
                                :class="selectedIds.includes(String({{ $biz->id }})) ? 'bg-[#007AFF]/[0.04] dark:bg-[#007AFF]/[0.08]' : ''">
                                <td class="w-10 px-4 py-4 text-center">
                                    <input type="checkbox"
                                        :value="String({{ $biz->id }})"
                                        x-model="selectedIds"
                                        class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]/30 border-black/20 dark:border-white/20 bg-white dark:bg-[#1C1C1E] cursor-pointer">
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-black dark:text-white text-[14px]">
                                        <a href="{{ route('admin.businesses.show', $biz->id) }}"
                                            class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors">{{ $biz->name }}</a>
                                    </div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 font-mono mt-0.5">
                                        slug: {{ $biz->slug }} · {{ $biz->currency ?? 'IDR' }}
                                    </div>
                                    @if ($biz->email || $biz->phone)
                                        <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">
                                            {{ $biz->email ?? $biz->phone }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if ($biz->subscription?->isCorePlan())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5" stroke-width="2"></i>
                                            <span>CORE ({{ strtoupper($biz->subscription->plan_code) }})</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55">
                                            <i data-lucide="gift" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                                            <span>Free Plan</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-black/75 dark:text-white/75">
                                    <div class="font-bold">{{ $biz->users->count() }} Pengguna</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">
                                        {{ $biz->users->count() <= 1 ? 'Solo Owner' : 'Mode Tim Delegasi' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-black/75 dark:text-white/75">
                                    <div class="tabular-nums font-bold">{{ $biz->products_count }} Produk</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">
                                        {{ $biz->bom_headers_count }} Resep BOM
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    @if ($biz->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Ditangguhkan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if(Auth::guard('admin')->user()?->hasPermission('admin.businesses.view'))
                                        <a href="{{ route('admin.businesses.show', $biz->id) }}"
                                            class="h-8 px-2.5 rounded-[10px] text-[12px] font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-95 transition-all inline-flex items-center gap-1"
                                            title="Lihat Detail Workspace">
                                            <span>Detail</span>
                                            <i data-lucide="arrow-right" class="w-3 h-3" stroke-width="2"></i>
                                        </a>
                                        @endif
                                        @if(Auth::guard('admin')->user()?->hasPermission('admin.businesses.impersonate') || Auth::guard('admin')->user()?->hasPermission('admin.businesses.view'))
                                        <form method="POST" action="{{ route('admin.businesses.impersonate', $biz->id) }}" class="inline"
                                            onsubmit="return AppAlert.confirmSubmit(event, this, 'Masuk ke workspace {{ addslashes($biz->name) }} untuk investigasi bugs dan diagnosa sistem?', 'Masuk ke Bisnis?', 'info')">
                                            @csrf
                                            <button type="submit"
                                                class="h-8 px-2.5 rounded-[10px] text-[12px] font-bold text-[#FF9500] dark:text-[#FF9F0A] bg-[#FF9500]/10 hover:bg-[#FF9500]/20 active:scale-95 transition-all inline-flex items-center gap-1 cursor-pointer"
                                                title="Masuk ke Workspace (Cek Bug)">
                                                <i data-lucide="log-in" class="w-3.5 h-3.5" stroke-width="2"></i>
                                                <span>Masuk</span>
                                            </button>
                                        </form>
                                        @endif
                                        @if($biz->is_active && Auth::guard('admin')->user()?->hasPermission('admin.businesses.toggle_status'))
                                            <button type="button"
                                                @click="suspendBizName = '{{ addslashes($biz->name) }}'; suspendActionUrl = '{{ route('admin.businesses.toggle-status', $biz->id) }}'; showSuspendModal = true;"
                                                class="h-8 px-2.5 rounded-[10px] text-[12px] font-bold transition-all inline-flex items-center gap-1 text-[#FF3B30] hover:bg-[#FF3B30]/10 active:scale-95 cursor-pointer">
                                                Suspend
                                            </button>
                                        @elseif(! $biz->is_active && Auth::guard('admin')->user()?->hasPermission('admin.businesses.toggle_status'))
                                        <form method="POST"
                                            action="{{ route('admin.businesses.toggle-status', $biz->id) }}"
                                            onsubmit="return AppAlert.confirmSubmit(event, this, 'Apakah Anda yakin ingin mengaktifkan kembali bisnis {{ addslashes($biz->name) }}?', 'Aktifkan Bisnis?', 'info')">
                                            @csrf
                                            <button type="submit"
                                                class="h-8 px-2.5 rounded-[10px] text-[12px] font-bold transition-all inline-flex items-center gap-1 text-[#34C759] hover:bg-[#34C759]/10 active:scale-95 cursor-pointer">
                                                Aktifkan
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <div class="w-12 h-12 mx-auto rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center text-black/30 dark:text-white/30 mb-2.5">
                                        <i data-lucide="building" class="w-6 h-6" stroke-width="1.5"></i>
                                    </div>
                                    <p class="text-[15px] font-bold text-black dark:text-white">Tidak ada data bisnis ditemukan</p>
                                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-1">Coba sesuaikan kata kunci pencarian atau reset filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($businesses->hasPages())
                <div class="px-5 py-3.5 border-t border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02]">
                    {{ $businesses->links() }}
                </div>
            @endif
        </div>

        <!-- Floating Bulk Action Toolbar (Bento Apple HIG Floating Pill) -->
        <div x-show="selectedIds.length > 0"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            x-cloak
            class="fixed bottom-6 inset-x-4 max-w-xl mx-auto z-40 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] rounded-[20px] p-3 shadow-[0_12px_32px_rgba(0,0,0,0.18)] flex items-center justify-between gap-3">
            <div class="flex items-center gap-2 pl-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#007AFF] animate-pulse"></span>
                <span class="text-[13px] font-bold text-black dark:text-white" x-text="`${selectedIds.length} bisnis dipilih`"></span>
            </div>
            <div class="flex items-center gap-2">
                <!-- Form Bulk Activate -->
                <form method="POST" action="{{ route('admin.businesses.bulk-status') }}" class="inline"
                    onsubmit="return AppAlert.confirmSubmit(event, this, 'Aktifkan seluruh bisnis terpilih?', 'Aktifkan Masal?', 'info')">
                    @csrf
                    <input type="hidden" name="action" value="activate">
                    <template x-for="id in selectedIds" :key="'act-' + id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <button type="submit"
                        class="h-9 px-3.5 rounded-[12px] text-[12.5px] font-bold text-white bg-[#34C759] hover:bg-[#30D158] active:scale-95 transition-all inline-flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5" stroke-width="2"></i>
                        <span>Aktifkan Masal</span>
                    </button>
                </form>

                <!-- Bulk Suspend Button (Opens Modal) -->
                <button type="button"
                    @click="showBulkSuspendModal = true"
                    class="h-9 px-3.5 rounded-[12px] text-[12.5px] font-bold text-white bg-[#FF3B30] hover:bg-[#FF453A] active:scale-95 transition-all inline-flex items-center gap-1.5 cursor-pointer shadow-xs">
                    <i data-lucide="ban" class="w-3.5 h-3.5" stroke-width="2"></i>
                    <span>Suspend Masal</span>
                </button>

                <!-- Cancel Selection -->
                <button type="button" @click="selectedIds = []"
                    class="h-9 px-3 rounded-[12px] text-[12.5px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                    Batal
                </button>
            </div>
        </div>

        <!-- Modal Bulk Suspend Confirmation -->
        <div x-show="showBulkSuspendModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] rounded-[24px] shadow-2xl p-6 overflow-hidden"
                @click.outside="showBulkSuspendModal = false">
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#FF3B30]/10 flex items-center justify-center text-[#FF3B30] shrink-0 mt-0.5">
                            <i data-lucide="alert-triangle" class="w-5 h-5" stroke-width="2"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] font-bold text-black dark:text-white">Suspend Masal Tenant Bisnis</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                                <span class="font-bold text-[#FF3B30]" x-text="`${selectedIds.length} bisnis`"></span> akan dibekukan sekaligus dan transaksi publiknya dinonaktifkan.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.businesses.bulk-status') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="action" value="suspend">
                        <template x-for="id in selectedIds" :key="'susp-' + id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>

                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                Alasan Penangguhan Masal <span class="text-[#FF3B30]">* (Wajib Diisi)</span>
                            </label>
                            <textarea name="reason" rows="3" required minlength="5" x-model="bulkSuspendReason"
                                placeholder="Contoh: Pembekuan operasional platform / audit kepatuhan masal..."
                                class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/40"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                            <button type="button" @click="showBulkSuspendModal = false"
                                class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF3B30] hover:bg-[#E0352B] transition shadow-sm cursor-pointer inline-flex items-center gap-1.5">
                                <i data-lucide="ban" class="w-4 h-4"></i>
                                <span>Konfirmasi Suspend Masal</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Apple HIG Suspend Modal with Mandatory Reason -->
        <div x-show="showSuspendModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="showSuspendModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] p-6 shadow-2xl space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center shrink-0">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] font-bold text-black dark:text-white">Tangguhkan Bisnis?</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">
                                Bisnis <strong class="text-black dark:text-white" x-text="suspendBizName"></strong> akan dibekukan dan notifikasi resmi dikirimkan via email ke pemilik bisnis.
                            </p>
                        </div>
                    </div>

                    <form method="POST" :action="suspendActionUrl" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                Alasan Penangguhan <span class="text-[#FF3B30]">* (Wajib Diisi)</span>
                            </label>
                            <textarea name="reason" rows="3" required minlength="5" placeholder="Contoh: Terdeteksi pelanggaran syarat & ketentuan platform / indikasi fraud..."
                                class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/40"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                            <button type="button" @click="showSuspendModal = false"
                                class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF3B30] hover:bg-[#E0352B] transition shadow-sm cursor-pointer">
                                Konfirmasi Suspend
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
