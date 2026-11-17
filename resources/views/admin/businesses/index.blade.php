@extends('layouts.admin', [
    'title' => __('admin.kelola_bisnis_tenants_admin_console'),
    'headerTitle' => 'Manajemen Tenant Bisnis UMKM',
    'headerSubtitle' => 'Kelola seluruh workspace bisnis yang beroperasi di platform Cooca',
])

@section('content')
    <div class="space-y-6 pb-28 lg:pb-10" x-data="adminBusinessesManager()">

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
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-4 shadow-sm flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.businesses.index') }}"
                class="flex flex-col md:flex-row items-stretch md:items-center flex-1 gap-3">
                <div class="relative flex-1 max-w-md">
                    <i data-lucide="search"
                        class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3.5 top-1/2 -translate-y-1/2"
                        stroke-width="1.5"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('admin.cari_nama_bisnis_slug_email_telepon') }}"
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
                            <span>{{ __('common.reset') }}</span>
                        </a>
                    @endif
                </div>
            </form>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="openCreateModal()"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-2 shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4" stroke-width="2"></i>
                    <span>Tambah Bisnis</span>
                </button>
            </div>
        </div>

        <!-- Bento Table Container -->
        <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02]">
                            <th class="w-10 px-4 py-3.5 text-center">
                                <label class="flex items-center justify-center cursor-pointer m-0 p-0" title="{{ __('admin.pilih_semua_di_halaman_ini') }}">
                                    <input type="checkbox"
                                        @click="toggleSelectAll()"
                                        :checked="isAllSelected()"
                                        x-effect="$el.indeterminate = isPartiallySelected()"
                                        class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]/30 border-black/20 dark:border-white/20 bg-white dark:bg-[#1C1C1E] cursor-pointer">
                                </label>
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
                                :class="selectedIds.includes('{{ $biz->id }}') ? 'bg-[#007AFF]/[0.04] dark:bg-[#007AFF]/[0.08]' : ''">
                                <td class="w-10 px-4 py-4 text-center">
                                    <label class="flex items-center justify-center cursor-pointer m-0 p-0">
                                        <input type="checkbox"
                                            value="{{ $biz->id }}"
                                            x-model="selectedIds"
                                            class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]/30 border-black/20 dark:border-white/20 bg-white dark:bg-[#1C1C1E] cursor-pointer">
                                    </label>
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
                                            title="{{ __('admin.lihat_detail_workspace') }}">
                                            <span>{{ __('common.detail') }}</span>
                                            <i data-lucide="arrow-right" class="w-3 h-3" stroke-width="2"></i>
                                        </a>
                                        @endif
                                        @if(Auth::guard('admin')->user()?->hasPermission('admin.businesses.impersonate') || Auth::guard('admin')->user()?->hasPermission('admin.businesses.view'))
                                        <form method="POST" action="{{ route('admin.businesses.impersonate', $biz->id) }}" class="inline"
                                            onsubmit="return AppAlert.confirmSubmit(event, this, 'Masuk ke workspace {{ addslashes($biz->name) }} untuk investigasi bugs dan diagnosa sistem?', 'Masuk ke Bisnis?', 'info')">
                                            @csrf
                                            <button type="submit"
                                                class="h-8 px-2.5 rounded-[10px] text-[12px] font-bold text-[#FF9500] dark:text-[#FF9F0A] bg-[#FF9500]/10 hover:bg-[#FF9500]/20 active:scale-95 transition-all inline-flex items-center gap-1 cursor-pointer"
                                                title="{{ __('admin.masuk_ke_workspace_cek_bug') }}">
                                                <i data-lucide="log-in" class="w-3.5 h-3.5" stroke-width="2"></i>
                                                <span>{{ __('admin.masuk') }}</span>
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

                                        @if(Auth::guard('admin')->user()?->hasPermission('admin.businesses.delete'))
                                        <button type="button"
                                            @click="openDeleteModal('{{ $biz->id }}', '{{ addslashes($biz->name) }}', '{{ addslashes($biz->slug) }}', '{{ route('admin.businesses.destroy', $biz->id) }}')"
                                            class="h-8 w-8 rounded-[10px] text-[#FF3B30] dark:text-[#FF453A] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 active:scale-95 transition-all inline-flex items-center justify-center cursor-pointer shrink-0"
                                            title="Hapus Permanen Bisnis (Hard Delete dengan 2FA)">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5" stroke-width="2"></i>
                                        </button>
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
                                    <p class="text-[15px] font-bold text-black dark:text-white">{{ __('admin.tidak_ada_data_bisnis_ditemukan') }}</p>
                                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-1">Coba sesuaikan kata kunci pencarian atau reset filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($businesses->total() > 0)
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
                <form method="POST" action="{{ route('admin.businesses.bulk-status') }}" class="inline" @submit="syncBulkInputs($el)"
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

                <!-- Bulk Hard Delete Button (Opens 2FA Modal) -->
                <button type="button"
                    @click="openBulkDeleteModal()"
                    class="h-9 px-3.5 rounded-[12px] text-[12.5px] font-bold text-white bg-[#D70015] hover:bg-[#FF3B30] active:scale-95 transition-all inline-flex items-center gap-1.5 cursor-pointer shadow-xs"
                    title="Hapus permanen seluruh bisnis terpilih beserta database dan asetnya">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5" stroke-width="2"></i>
                    <span>Hapus Masal</span>
                </button>

                <!-- Cancel Selection -->
                <button type="button" @click="selectedIds = []"
                    class="h-9 px-3 rounded-[12px] text-[12.5px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">{{ __('common.cancel') }}</button>
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
                            <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.suspend_masal_tenant_bisnis') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                                <span class="font-bold text-[#FF3B30]" x-text="`${selectedIds.length} bisnis`"></span> akan dibekukan sekaligus dan transaksi publiknya dinonaktifkan.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.businesses.bulk-status') }}" class="space-y-4" @submit="syncBulkInputs($el)">
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
                                class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">{{ __('common.cancel') }}</button>
                            <button type="submit"
                                class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF3B30] hover:bg-[#E0352B] transition shadow-sm cursor-pointer inline-flex items-center gap-1.5">
                                <i data-lucide="ban" class="w-4 h-4"></i>
                                <span>{{ __('admin.konfirmasi_suspend_masal') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Bulk Hard Delete Confirmation with 2FA -->
        <div x-show="showBulkDeleteModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-md flex items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] rounded-[28px] shadow-2xl p-6 sm:p-7 overflow-hidden"
                @click.outside="showBulkDeleteModal = false">
                <div class="space-y-5">
                    <!-- Modal Header -->
                    <div class="flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-[16px] bg-[#FF3B30]/10 flex items-center justify-center text-[#FF3B30] shrink-0">
                            <i data-lucide="trash-2" class="w-6 h-6" stroke-width="2"></i>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] text-[11px] font-bold uppercase tracking-wider mb-1">
                                <span>Tindakan Berisiko Tinggi</span>
                            </div>
                            <h3 class="text-[17px] font-bold text-black dark:text-white leading-snug">
                                Hapus Masal Permanen Tenant Bisnis
                            </h3>
                            <p class="text-[12.5px] text-black/50 dark:text-white/50 mt-0.5">
                                Tindakan pemusnahan total (hard delete) pada database dan berkas aset fisik penyimpanan seluruh bisnis terpilih.
                            </p>
                        </div>
                    </div>

                    <!-- 2FA Check Condition -->
                    <template x-if="!adminHas2FA">
                        <div class="space-y-4">
                            <div class="p-4 rounded-[16px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[13px] text-black/80 dark:text-white/90 space-y-2">
                                <div class="font-bold text-[#C93400] dark:text-[#FF9F0A] flex items-center gap-2">
                                    <i data-lucide="shield-alert" class="w-4 h-4" stroke-width="2"></i>
                                    <span>Google Authenticator (2FA) Wajib Diaktifkan</span>
                                </div>
                                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                                    Demi melindungi platform dari risiko kehilangan data fatal, fitur hard delete masal hanya dapat dieksekusi oleh administrator yang telah mengaktifkan 2FA Google Authenticator.
                                </p>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <button type="button" @click="showBulkDeleteModal = false"
                                    class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                                    Tutup
                                </button>
                                <a href="{{ route('admin.profile.index') }}"
                                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] transition inline-flex items-center gap-2 shadow-sm">
                                    <i data-lucide="key" class="w-4 h-4"></i>
                                    <span>Aktifkan 2FA di Profil</span>
                                </a>
                            </div>
                        </div>
                    </template>

                    <template x-if="adminHas2FA">
                        <form method="POST" action="{{ route('admin.businesses.bulk-destroy') }}" class="space-y-4" @submit="syncBulkInputs($el)">
                            @csrf
                            @method('DELETE')
                            <template x-for="id in selectedIds" :key="'bulk-del-' + id">
                                <input type="hidden" name="ids[]" :value="id">
                            </template>

                            <!-- Danger Warning Banner -->
                            <div class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[12px] text-black/75 dark:text-white/80 leading-relaxed space-y-1">
                                <div class="font-bold text-[#FF3B30] dark:text-[#FF453A] flex items-center gap-1.5">
                                    <i data-lucide="alert-octagon" class="w-4 h-4" stroke-width="2"></i>
                                    <span>Peringatan Kritis: Penghapusan Tidak Dapat Dibatalkan</span>
                                </div>
                                <p class="text-black/60 dark:text-white/60">
                                    Sebanyak <strong class="text-black dark:text-white" x-text="`${selectedIds.length} bisnis`"></strong> beserta seluruh produk, transaksi POS, inventori, resep, akun WhatsApp, dan seluruh file storage akan dihapus selamanya dari server.
                                </p>
                            </div>

                            <!-- Input 1: Confirmation Text -->
                            <div>
                                <label class="block text-[12.5px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                    Ketik <span class="font-bold text-[#FF3B30]">HAPUS MASAL</span> untuk konfirmasi:
                                </label>
                                <input type="text" name="confirm_text" required x-model="bulkDeleteConfirmInput"
                                    placeholder="Ketik HAPUS MASAL di sini"
                                    class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50 transition">
                            </div>

                            <!-- Input 2: 2FA Google Authenticator Code -->
                            <div>
                                <label class="block text-[12.5px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                    Kode 6 Digit Google Authenticator <span class="text-[#FF3B30]">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="totp_code" maxlength="6" pattern="[0-9]{6}" required x-model="bulkDeleteTotpCode"
                                        placeholder="000000"
                                        class="w-full h-11 px-4 text-center font-mono text-[18px] tracking-[0.3em] font-bold bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50 transition">
                                </div>
                                <span class="text-[11px] text-black/45 dark:text-white/45 mt-1 block">
                                    Buka aplikasi Google Authenticator di ponsel Anda dan masukkan kode 6-digit terkini.
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <button type="button" @click="showBulkDeleteModal = false"
                                    class="h-11 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                                    {{ __('common.cancel') }}
                                </button>
                                <button type="submit"
                                    :disabled="!isBulkDeleteFormValid()"
                                    :class="isBulkDeleteFormValid() ? 'bg-[#D70015] hover:bg-[#FF3B30] active:scale-[0.98] cursor-pointer' : 'bg-black/20 dark:bg-white/20 text-black/40 dark:text-white/40 cursor-not-allowed'"
                                    class="h-11 px-5 rounded-[12px] text-[13px] font-bold text-white transition-all inline-flex items-center gap-2 shadow-sm">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    <span>Hapus Masal Permanen</span>
                                </button>
                            </div>
                        </form>
                    </template>
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
                            <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.tangguhkan_bisnis') }}</h3>
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
                                class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">{{ __('common.cancel') }}</button>
                            <button type="submit"
                                class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF3B30] hover:bg-[#E0352B] transition shadow-sm cursor-pointer">{{ __('admin.konfirmasi_suspend') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Apple HIG Bento Hard Delete Business Modal with 2FA Verification -->
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] p-6 sm:p-7 shadow-2xl space-y-5">
                    
                    <!-- Header -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-[16px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center shrink-0">
                            <i data-lucide="trash-2" class="w-6 h-6" stroke-width="2"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[17px] font-bold text-black dark:text-white">Hapus Permanen Tenant Bisnis</h3>
                            <p class="text-[12px] text-black/55 dark:text-white/55 mt-0.5 leading-relaxed">
                                Tindakan pemusnahan total (hard delete) pada database dan berkas aset fisik penyimpanan.
                            </p>
                        </div>
                    </div>

                    <!-- 2FA Check Condition -->
                    <template x-if="!adminHas2FA">
                        <div class="space-y-4">
                            <div class="p-4 rounded-[16px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[13px] text-black/80 dark:text-white/90 space-y-2">
                                <div class="font-bold text-[#C93400] dark:text-[#FF9F0A] flex items-center gap-2">
                                    <i data-lucide="shield-alert" class="w-4 h-4" stroke-width="2"></i>
                                    <span>Google Authenticator (2FA) Wajib Diaktifkan</span>
                                </div>
                                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                                    Demi melindungi platform dari risiko kehilangan data fatal, fitur hard delete hanya dapat dieksekusi oleh administrator yang telah mengaktifkan 2FA Google Authenticator.
                                </p>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <button type="button" @click="showDeleteModal = false"
                                    class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                                    Tutup
                                </button>
                                <a href="{{ route('admin.profile.index') }}"
                                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] transition inline-flex items-center gap-2 shadow-sm">
                                    <i data-lucide="key" class="w-4 h-4"></i>
                                    <span>Aktifkan 2FA di Profil</span>
                                </a>
                            </div>
                        </div>
                    </template>

                    <template x-if="adminHas2FA">
                        <form method="POST" :action="deleteActionUrl" class="space-y-4">
                            @csrf
                            @method('DELETE')

                            <!-- Danger Warning Banner -->
                            <div class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[12px] text-black/75 dark:text-white/80 leading-relaxed space-y-1">
                                <div class="font-bold text-[#FF3B30] dark:text-[#FF453A] flex items-center gap-1.5">
                                    <i data-lucide="alert-octagon" class="w-4 h-4" stroke-width="2"></i>
                                    <span>Peringatan Kritis: Penghapusan Tidak Dapat Dibatalkan</span>
                                </div>
                                <p class="text-black/60 dark:text-white/60">
                                    Bisnis <strong class="text-black dark:text-white" x-text="deleteBizName"></strong> beserta seluruh produk, transaksi POS, invoice, inventori, resep, akun WA, dan berkas storage akan dihapus selamanya.
                                </p>
                            </div>

                            <!-- Input 1: Confirmation Name -->
                            <div>
                                <label class="block text-[12.5px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                    Ketik nama bisnis <span class="font-bold text-[#FF3B30]" x-text="'\'' + deleteBizName + '\''"></span> untuk konfirmasi:
                                </label>
                                <input type="text" name="confirm_name" required x-model="deleteConfirmInput"
                                    placeholder="Ketik nama atau slug bisnis di sini"
                                    class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50 transition">
                            </div>

                            <!-- Input 2: 2FA Google Authenticator Code -->
                            <div>
                                <label class="block text-[12.5px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                    Kode 6 Digit Google Authenticator <span class="text-[#FF3B30]">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="totp_code" maxlength="6" pattern="[0-9]{6}" required x-model="deleteTotpCode"
                                        placeholder="000000"
                                        class="w-full h-11 px-4 text-center font-mono text-[18px] tracking-[0.3em] font-bold bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50 transition">
                                </div>
                                <span class="text-[11px] text-black/45 dark:text-white/45 mt-1 block">
                                    Buka aplikasi Google Authenticator di ponsel Anda dan masukkan kode 6-digit terkini.
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <button type="button" @click="showDeleteModal = false"
                                    class="h-11 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                                    {{ __('common.cancel') }}
                                </button>
                                <button type="submit"
                                    :disabled="!isDeleteFormValid()"
                                    :class="isDeleteFormValid() ? 'bg-[#FF3B30] hover:bg-[#D70015] active:scale-[0.98] cursor-pointer' : 'bg-black/20 dark:bg-white/20 text-black/40 dark:text-white/40 cursor-not-allowed'"
                                    class="h-11 px-5 rounded-[12px] text-[13px] font-bold text-white transition-all inline-flex items-center gap-2 shadow-sm">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    <span>Hapus Permanen Sekarang</span>
                                </button>
                            </div>
                        </form>
                    </template>

                </div>
            </div>
        </div>

        <!-- Apple HIG Bento Modal: Tambah Bisnis Baru & Paket Langganan Bebas Biaya (2FA Verified) -->
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-md transition-opacity" @click="showCreateModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-3 sm:p-5">
                <div class="relative w-full max-w-2xl rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] p-5 sm:p-7 shadow-2xl space-y-5 my-6 max-h-[92vh] overflow-y-auto"
                    @click.outside="showCreateModal = false">
                    
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-3 pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-start gap-3.5">
                            <div class="w-12 h-12 rounded-[16px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="building-2" class="w-6 h-6" stroke-width="2"></i>
                            </div>
                            <div>
                                <h3 class="text-[18px] font-bold text-black dark:text-white leading-snug">
                                    Tambah Bisnis &amp; Paket Langganan
                                </h3>
                                <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">
                                    Admin dapat membuat bisnis baru dengan paket dan durasi berapapun tanpa biaya, diotorisasi via Google 2FA.
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showCreateModal = false"
                            class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/20 text-black/60 dark:text-white/60 flex items-center justify-center transition cursor-pointer shrink-0">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- 2FA Check Condition -->
                    <template x-if="!adminHas2FA">
                        <div class="space-y-4">
                            <div class="p-4 rounded-[16px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[13px] text-black/80 dark:text-white/90 space-y-2">
                                <div class="font-bold text-[#C93400] dark:text-[#FF9F0A] flex items-center gap-2">
                                    <i data-lucide="shield-alert" class="w-4 h-4" stroke-width="2"></i>
                                    <span>Google Authenticator (2FA) Wajib Diaktifkan</span>
                                </div>
                                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                                    Demi kepatuhan keamanan platform, penambahan bisnis dan pemberian paket khusus langsung dari admin console wajib menggunakan verifikasi kode 2FA. Silakan aktifkan 2FA di profil akun administrator Anda terlebih dahulu.
                                </p>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <button type="button" @click="showCreateModal = false"
                                    class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                                    Tutup
                                </button>
                                <a href="{{ route('admin.profile.index') }}"
                                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] transition inline-flex items-center gap-2 shadow-sm">
                                    <i data-lucide="key" class="w-4 h-4"></i>
                                    <span>Aktifkan 2FA di Profil</span>
                                </a>
                            </div>
                        </div>
                    </template>

                    <template x-if="adminHas2FA">
                        <form method="POST" action="{{ route('admin.businesses.store') }}" class="space-y-5">
                            @csrf

                            <!-- Section 1: Profil & Identitas Bisnis -->
                            <div class="p-4 sm:p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.05] dark:border-white/[0.06] space-y-3.5">
                                <div class="flex items-center gap-2 pb-1.5 border-b border-black/[0.04] dark:border-white/[0.05]">
                                    <i data-lucide="store" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white">1. Identitas &amp; Jenis Usaha</h4>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div class="sm:col-span-2">
                                        <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                            Nama Bisnis <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <input type="text" name="name" required x-model="createName"
                                            placeholder="Contoh: Kopi Kenangan Senja"
                                            class="w-full h-10 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    </div>

                                    <div>
                                        <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                            Slug URL Bisnis <span class="text-black/40 dark:text-white/40 font-normal">(Opsional)</span>
                                        </label>
                                        <input type="text" name="slug" x-model="createSlug"
                                            placeholder="otomatis dari nama jika kosong"
                                            class="w-full h-10 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:font-sans focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    </div>

                                    <div>
                                        <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                            Skala Bisnis
                                        </label>
                                        <select name="business_scale" x-model="createBusinessScale"
                                            class="w-full h-10 px-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                            <option value="umkm">Skala UMKM (Fokus POS, Resep &amp; Operasional)</option>
                                            <option value="corporate">Skala Korporasi (Fitur Multi-Gudang &amp; B2B)</option>
                                        </select>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                            Template Sektor Industri <span class="text-black/40 dark:text-white/40 font-normal">(Opsional)</span>
                                        </label>
                                        <select name="template_code" x-model="createTemplateCode"
                                            class="w-full h-10 px-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                            <option value="">Tanpa Template (Usaha Kosong / Standar)</option>
                                            @foreach($templates as $tmpl)
                                                <option value="{{ $tmpl->code }}">{{ $tmpl->name }} ({{ strtoupper($tmpl->industry_category) }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Pemilik Bisnis (Owner) -->
                            <div class="p-4 sm:p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.05] dark:border-white/[0.06] space-y-3.5">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/[0.04] dark:border-white/[0.05]">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="user-check" class="w-4 h-4 text-[#5856D6] dark:text-[#5E5CE6]"></i>
                                        <h4 class="text-[13px] font-bold text-black dark:text-white">2. Akun Pemilik Bisnis (Owner)</h4>
                                    </div>

                                    <!-- Segmented Switch -->
                                    <div class="inline-flex p-0.5 rounded-[10px] bg-black/[0.06] dark:bg-white/[0.08] text-[11px] font-bold">
                                        <button type="button" @click="createOwnerMode = 'new_user'"
                                            :class="createOwnerMode === 'new_user' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/50 dark:text-white/50'"
                                            class="px-2.5 py-1 rounded-[8px] transition cursor-pointer">
                                            Buat Akun Baru
                                        </button>
                                        <button type="button" @click="createOwnerMode = 'existing_user'"
                                            :class="createOwnerMode === 'existing_user' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/50 dark:text-white/50'"
                                            class="px-2.5 py-1 rounded-[8px] transition cursor-pointer">
                                            Gunakan User Terdaftar
                                        </button>
                                    </div>
                                </div>

                                <input type="hidden" name="owner_mode" :value="createOwnerMode">

                                <!-- Sub-view: Buat Pengguna Baru -->
                                <div x-show="createOwnerMode === 'new_user'" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                            Nama Pemilik <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <input type="text" name="owner_name" :required="createOwnerMode === 'new_user'" x-model="createOwnerName"
                                            placeholder="Nama lengkap pemilik"
                                            class="w-full h-10 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]/50 transition">
                                    </div>

                                    <div>
                                        <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                            Email Pemilik <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <input type="email" name="owner_email" :required="createOwnerMode === 'new_user'" x-model="createOwnerEmail"
                                            placeholder="owner@domain.com"
                                            class="w-full h-10 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]/50 transition">
                                    </div>

                                    <div>
                                        <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                            No Telepon / WhatsApp <span class="text-black/40 dark:text-white/40 font-normal">(Opsional)</span>
                                        </label>
                                        <input type="text" name="owner_phone" x-model="createOwnerPhone"
                                            placeholder="08123456789"
                                            class="w-full h-10 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]/50 transition">
                                    </div>

                                    <div>
                                        <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                            Password Akun <span class="text-black/40 dark:text-white/40 font-normal">(Opsional)</span>
                                        </label>
                                        <input type="text" name="owner_password" x-model="createOwnerPassword"
                                            placeholder="Kosongkan untuk password acak otomatis"
                                            class="w-full h-10 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white font-mono placeholder:font-sans focus:outline-none focus:ring-2 focus:ring-[#5856D6]/50 transition">
                                    </div>
                                </div>

                                <!-- Sub-view: Pilih Pengguna Terdaftar -->
                                <div x-show="createOwnerMode === 'existing_user'" class="space-y-2">
                                    <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                        Pilih Pengguna Terdaftar Sebagai Pemilik <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <select name="existing_user_id" :required="createOwnerMode === 'existing_user'" x-model="createExistingUserId"
                                        class="w-full h-10 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]/50">
                                        <option value="">-- Pilih User Terdaftar --</option>
                                        @foreach($existingUsers as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-[11px] text-black/45 dark:text-white/45">
                                        Pengguna yang dipilih akan secara otomatis ditugaskan sebagai Owner pada bisnis baru ini.
                                    </p>
                                </div>
                            </div>

                            <!-- Section 3: Paket Langganan & Durasi (Bebas Biaya) -->
                            <div class="p-4 sm:p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.05] dark:border-white/[0.06] space-y-3.5">
                                <div class="flex items-center gap-2 pb-1.5 border-b border-black/[0.04] dark:border-white/[0.05]">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white">3. Paket Langganan &amp; Durasi (Bebas Biaya)</h4>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                            Pilihan Paket <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <select name="plan_code" x-model="createPlanCode" required
                                            class="w-full h-10 px-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[13px] font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]/50">
                                            <option value="standard_monthly">Paket Standard Bulanan (Rp 39.000 / bln)</option>
                                            <option value="standard_annual">Paket Standard Tahunan (Rp 390.000 / thn)</option>
                                            <option value="premium_monthly">Paket Premium Bulanan (Rp 89.000 / bln)</option>
                                            <option value="premium_annual">Paket Premium Tahunan (Rp 890.000 / thn)</option>
                                            <option value="prestige_monthly">Paket Prestige Bulanan (Rp 199.000 / bln)</option>
                                            <option value="prestige_annual">Paket Prestige Tahunan (Rp 1.990.000 / thn)</option>
                                            <option value="core_monthly">Cooca Core Bulanan</option>
                                            <option value="core_annual">Cooca Core Tahunan</option>
                                            <option value="free">Free Plan (Gratis Selamanya Solo Owner)</option>
                                            <option value="custom">Paket Kustom (Input Kode Sendiri)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                            Durasi Paket <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <select name="duration_type" x-model="createDurationType" required
                                            class="w-full h-10 px-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[13px] font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]/50">
                                            <option value="30">1 Bulan (30 Hari)</option>
                                            <option value="90">3 Bulan (90 Hari)</option>
                                            <option value="180">6 Bulan (180 Hari)</option>
                                            <option value="365">1 Tahun (365 Hari)</option>
                                            <option value="730">2 Tahun (730 Hari)</option>
                                            <option value="custom">Kustom Hari (Tentukan Sendiri)</option>
                                            <option value="lifetime">Lifetime (Tanpa Batas Waktu)</option>
                                        </select>
                                    </div>

                                    <!-- Custom Plan Code Input -->
                                    <div x-show="createPlanCode === 'custom'" class="sm:col-span-2">
                                        <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                            Kode Paket Kustom <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <input type="text" name="custom_plan_code" x-model="createCustomPlanCode"
                                            :required="createPlanCode === 'custom'"
                                            placeholder="Contoh: vip_partner_2026"
                                            class="w-full h-10 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]/50">
                                    </div>

                                    <!-- Custom Days Input -->
                                    <div x-show="createDurationType === 'custom'" class="sm:col-span-2">
                                        <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                            Jumlah Hari Kustom <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <input type="number" name="custom_days" x-model.number="createCustomDays" min="1" max="36500"
                                            :required="createDurationType === 'custom'"
                                            placeholder="Contoh: 45 (hari)"
                                            class="w-full h-10 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[13px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]/50">
                                    </div>
                                </div>

                                <!-- Free Callout Banner -->
                                <div class="p-3.5 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/25 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center shrink-0">
                                        <i data-lucide="gift" class="w-4 h-4"></i>
                                    </div>
                                    <div class="text-[12px] text-black/80 dark:text-white/90">
                                        <span class="font-bold text-[#248A3D] dark:text-[#30D158]">Biaya: Rp 0 (Bebas Biaya / Admin Grant)</span>
                                        <div class="text-[11px] text-black/55 dark:text-white/55 mt-0.5">
                                            Paket langsung aktif seketika tanpa pembayaran dan tercatat di riwayat tagihan sebagai grant resmi administrator.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4: Catatan & 2FA Google Authenticator -->
                            <div class="p-4 sm:p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.05] dark:border-white/[0.06] space-y-3.5">
                                <div class="flex items-center gap-2 pb-1.5 border-b border-black/[0.04] dark:border-white/[0.05]">
                                    <i data-lucide="key" class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]"></i>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white">4. Catatan &amp; Otorisasi Google 2FA</h4>
                                </div>

                                <div>
                                    <label class="block text-[12px] font-semibold text-black/75 dark:text-white/75 mb-1">
                                        Catatan / Alasan Admin <span class="text-black/40 dark:text-white/40 font-normal">(Opsional)</span>
                                    </label>
                                    <textarea name="admin_notes" rows="2" x-model="createAdminNotes"
                                        placeholder="Contoh: Pemberian paket khusus kemitraan / Uji coba program binaan UMKM..."
                                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                                </div>

                                <div>
                                    <label class="block text-[12.5px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                        Kode 6 Digit Google Authenticator <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <input type="text" name="totp_code" maxlength="6" pattern="[0-9]{6}" required x-model="createTotpCode"
                                        placeholder="000000"
                                        class="w-full h-11 px-4 text-center font-mono text-[18px] tracking-[0.3em] font-bold bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <span class="text-[11px] text-black/45 dark:text-white/45 mt-1 block">
                                        Buka aplikasi Google Authenticator di ponsel Anda dan masukkan kode 6-digit terkini.
                                    </span>
                                </div>
                            </div>

                            <!-- Modal Actions -->
                            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <button type="button" @click="showCreateModal = false"
                                    class="h-11 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                                    {{ __('common.cancel') }}
                                </button>
                                <button type="submit"
                                    :disabled="!isCreateFormValid()"
                                    :class="isCreateFormValid() ? 'bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] cursor-pointer' : 'bg-black/20 dark:bg-white/20 text-black/40 dark:text-white/40 cursor-not-allowed'"
                                    class="h-11 px-5 rounded-[12px] text-[13px] font-bold text-white transition-all inline-flex items-center gap-2 shadow-sm">
                                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                    <span>Tambahkan Bisnis Sekarang</span>
                                </button>
                            </div>
                        </form>
                    </template>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function adminBusinessesManager() {
    return {
        showCreateModal: false,
        createName: '',
        createSlug: '',
        createTemplateCode: '',
        createBusinessScale: 'umkm',
        createOwnerMode: 'new_user',
        createOwnerName: '',
        createOwnerEmail: '',
        createOwnerPhone: '',
        createOwnerPassword: '',
        createExistingUserId: '',
        createPlanCode: 'standard_monthly',
        createCustomPlanCode: '',
        createDurationType: '30',
        createCustomDays: 30,
        createAdminNotes: '',
        createTotpCode: '',
        openCreateModal() {
            this.createName = '';
            this.createSlug = '';
            this.createTemplateCode = '';
            this.createBusinessScale = 'umkm';
            this.createOwnerMode = 'new_user';
            this.createOwnerName = '';
            this.createOwnerEmail = '';
            this.createOwnerPhone = '';
            this.createOwnerPassword = '';
            this.createExistingUserId = '';
            this.createPlanCode = 'standard_monthly';
            this.createCustomPlanCode = '';
            this.createDurationType = '30';
            this.createCustomDays = 30;
            this.createAdminNotes = '';
            this.createTotpCode = '';
            this.showCreateModal = true;
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },
        isCreateFormValid() {
            if (!this.adminHas2FA) return false;
            if (!this.createName || this.createName.trim().length === 0) return false;
            if (this.createOwnerMode === 'new_user') {
                if (!this.createOwnerName || this.createOwnerName.trim().length === 0) return false;
                if (!this.createOwnerEmail || !this.createOwnerEmail.includes('@')) return false;
            } else {
                if (!this.createExistingUserId) return false;
            }
            if (this.createPlanCode === 'custom' && (!this.createCustomPlanCode || this.createCustomPlanCode.trim().length === 0)) {
                return false;
            }
            if (this.createDurationType === 'custom' && (!this.createCustomDays || this.createCustomDays < 1)) {
                return false;
            }
            const isTotpValid = (this.createTotpCode.trim().length === 6 && /^\d+$/.test(this.createTotpCode.trim()));
            return isTotpValid;
        },
        showSuspendModal: false,
        suspendBizName: '',
        suspendActionUrl: '',
        showDeleteModal: false,
        deleteBizId: '',
        deleteBizName: '',
        deleteBizSlug: '',
        deleteActionUrl: '',
        deleteConfirmInput: '',
        deleteTotpCode: '',
        adminHas2FA: @js(Auth::guard('admin')->user()?->hasTwoFactorEnabled() ?? false),
        selectedIds: [],
        pageBusinessIds: @js($businesses->pluck('id')->map(fn($id) => (string)$id)->values()),
        showBulkSuspendModal: false,
        bulkSuspendReason: '',
        showBulkDeleteModal: false,
        bulkDeleteConfirmInput: '',
        bulkDeleteTotpCode: '',
        openBulkDeleteModal() {
            this.bulkDeleteConfirmInput = '';
            this.bulkDeleteTotpCode = '';
            this.showBulkDeleteModal = true;
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },
        isBulkDeleteFormValid() {
            if (!this.adminHas2FA) return false;
            const confirm = this.bulkDeleteConfirmInput.trim().toUpperCase();
            const isTextValid = (confirm === 'HAPUS MASAL');
            const isTotpValid = (this.bulkDeleteTotpCode.trim().length === 6 && /^\d+$/.test(this.bulkDeleteTotpCode.trim()));
            return isTextValid && isTotpValid && this.selectedIds.length > 0;
        },
        openDeleteModal(id, name, slug, url) {
            this.deleteBizId = id;
            this.deleteBizName = name;
            this.deleteBizSlug = slug;
            this.deleteActionUrl = url;
            this.deleteConfirmInput = '';
            this.deleteTotpCode = '';
            this.showDeleteModal = true;
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },
        isDeleteFormValid() {
            if (!this.adminHas2FA) return false;
            const confirm = this.deleteConfirmInput.trim().toLowerCase();
            const expectedName = this.deleteBizName.trim().toLowerCase();
            const expectedSlug = this.deleteBizSlug.trim().toLowerCase();
            const isNameValid = (confirm === expectedName || confirm === expectedSlug);
            const isTotpValid = (this.deleteTotpCode.trim().length === 6 && /^\d+$/.test(this.deleteTotpCode.trim()));
            return isNameValid && isTotpValid;
        },
        toggleSelectAll() {
            if (this.selectedIds.length === this.pageBusinessIds.length) {
                this.selectedIds = [];
            } else {
                this.selectedIds = [...this.pageBusinessIds];
            }
        },
        isAllSelected() {
            return this.pageBusinessIds.length > 0 && this.selectedIds.length === this.pageBusinessIds.length;
        },
        isPartiallySelected() {
            return this.selectedIds.length > 0 && this.selectedIds.length < this.pageBusinessIds.length;
        },
        syncBulkInputs(form) {
            form.querySelectorAll("input[name='ids[]']").forEach(el => el.remove());
            this.selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
        }
    };
}
</script>
@endpush
