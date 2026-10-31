@extends('layouts.admin', [
    'title' => __('admin.hak_akses_peran_admin_platform_rbac'),
    'headerTitle' => 'Manajemen Peran & Hak Akses Platform',
    'headerSubtitle' => 'Atur hak akses granular, peran staf administrator, dan audit izin operasional platform COOCA',
])

@section('content')
@php
    $categoryLabels = [
        'admin_dashboard' => 'Platform Dashboard & Ringkasan',
        'admin_tenants'   => 'Bisnis & Pengguna Platform',
        'admin_finance'   => 'Finansial, Billing, Rekening & Settlement',
        'admin_support'   => 'Pemulihan Akun, Feedback, Leads & CS Hub',
        'admin_content'   => 'Event, Artikel, Template & Moderasi',
        'admin_system'    => 'WhatsApp, Medsos, Token AI & Konfigurasi Sistem',
    ];
@endphp

<div class="space-y-6 pb-28 lg:pb-12" x-data="{
    activeTab: 'roles',
    showGuide: false,
    showRoleModal: false,
    isEditing: false,
    roleFormAction: '{{ route('admin.roles.store') }}',
    roleFormMethod: 'POST',
    roleName: '',
    roleDescription: '',
    roleSlug: '',
    isPresetRole: false,
    searchPermission: '',
    selectedPermissions: [],

    // Member role change state
    showMemberModal: false,
    selectedAdmin: null,
    memberFormAction: '',
    targetRoleId: '',

    // Delete role state
    showDeleteModal: false,
    deleteTargetRole: null,

    openCreateRole() {
        this.isEditing = false;
        this.roleFormAction = '{{ route('admin.roles.store') }}';
        this.roleFormMethod = 'POST';
        this.roleName = '';
        this.roleDescription = '';
        this.roleSlug = '';
        this.isPresetRole = false;
        this.searchPermission = '';
        this.selectedPermissions = [];
        this.showRoleModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openEditRole(role, actionUrl, permSlugs) {
        this.isEditing = true;
        this.roleFormAction = actionUrl;
        this.roleFormMethod = 'PUT';
        this.roleName = role.name;
        this.roleDescription = role.description || '';
        this.roleSlug = role.slug;
        this.isPresetRole = Boolean(role.is_preset || role.slug === 'super_admin');
        this.searchPermission = '';
        this.selectedPermissions = [...permSlugs];
        this.showRoleModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openChangeAdminRole(admin, actionUrl, currentRoleSlug) {
        this.selectedAdmin = admin;
        this.memberFormAction = actionUrl;
        this.targetRoleId = currentRoleSlug;
        this.showMemberModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    openDeleteRole(role) {
        this.deleteTargetRole = role;
        this.showDeleteModal = true;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },

    toggleCategory(categorySlugs, checked) {
        if (checked) {
            categorySlugs.forEach(slug => {
                if (!this.selectedPermissions.includes(slug)) {
                    this.selectedPermissions.push(slug);
                }
            });
        } else {
            this.selectedPermissions = this.selectedPermissions.filter(slug => !categorySlugs.includes(slug));
        }
    },

    isCategoryAllSelected(categorySlugs) {
        return categorySlugs.length > 0 && categorySlugs.every(slug => this.selectedPermissions.includes(slug));
    }
}">

    {{-- 1. TOP TOOLBAR & ACTION HEADER --}}
    <header class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/45 dark:text-white/45 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#007AFF] transition-colors">Admin Console</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black/60 dark:text-white/60 font-medium">Keamanan &amp; Platform</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-semibold">Hak Akses &amp; Peran</span>
            </nav>
            <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">Peran &amp; Hak Akses Administrator</h1>
            <p class="text-[13px] text-black/55 dark:text-white/55 mt-0.5">Kelola batasan izin modular, audit matriks RBAC, dan penugasan peran staf internal.</p>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0">
            @if(Auth::guard('admin')->user()?->hasPermission('admin.roles.manage'))
                <button type="button" @click="openCreateRole()"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm shadow-[#007AFF]/25 w-full sm:w-auto cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Tambah Peran Kustom</span>
                </button>
            @endif
        </div>
    </header>

    {{-- ALERT FLASH MESSAGES --}}
    @if(session('success'))
        <div class="rounded-[16px] bg-[#34C759]/12 border border-[#34C759]/25 p-4 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-3 shadow-xs">
            <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
            <div class="flex-1 font-semibold">{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-[16px] bg-[#FF3B30]/12 border border-[#FF3B30]/25 p-4 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-start gap-3 shadow-xs">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <div class="flex-1 font-semibold space-y-1">
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 2. BENTO STATS / KPI SUMMARY --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Total Peran Platform</span>
                <i data-lucide="shield" class="w-4 h-4 text-[#007AFF]"></i>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[26px] font-bold text-black dark:text-white tabular-nums">{{ $roles->count() }}</span>
                <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Preset &amp; Kustom</span>
            </div>
        </div>

        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>{{ __('admin.staf_administrator') }}</span>
                <i data-lucide="users" class="w-4 h-4 text-[#34C759]"></i>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[26px] font-bold text-[#34C759] dark:text-[#30D158] tabular-nums">{{ $admins->count() }}</span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">{{ $admins->where('is_active', true)->count() }} Aktif</span>
            </div>
        </div>

        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Granular Permissions</span>
                <i data-lucide="key" class="w-4 h-4 text-[#AF52DE]"></i>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[26px] font-bold text-[#AF52DE] dark:text-[#BF5AF2] tabular-nums">{{ collect($permissions)->flatten(1)->count() }}</span>
                <span class="text-[11px] font-medium text-[#AF52DE] dark:text-[#BF5AF2]">{{ count($permissions) }} Modul Inti</span>
            </div>
        </div>

        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Guard Arsitektur</span>
                <i data-lucide="lock" class="w-4 h-4 text-[#FF9500]"></i>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[18px] font-bold text-[#FF9500] dark:text-[#FF9F0A]">Controller RBAC</span>
                <span class="text-[11px] font-semibold text-[#34C759] bg-[#34C759]/12 px-2 py-0.5 rounded-full">Enforced</span>
            </div>
        </div>
    </div>

    {{-- 3. SEGMENTED TAB SWITCHER --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
        <div class="inline-flex p-1 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-[13px] font-medium">
            <button type="button" @click="activeTab = 'roles'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'roles' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Peran Platform</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] font-bold bg-[#007AFF]/12 text-[#007AFF] tabular-nums">{{ $roles->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'members'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'members' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="users" class="w-4 h-4 text-[#34C759]"></i>
                <span>{{ __('admin.staf_administrator') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] tabular-nums">{{ $admins->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'matrix'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                :class="activeTab === 'matrix' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="table-properties" class="w-4 h-4 text-[#AF52DE]"></i>
                <span>Matriks Hak Akses</span>
            </button>
        </div>

        <button type="button" @click="showGuide = !showGuide"
            class="text-[12px] text-[#007AFF] hover:underline font-semibold flex items-center gap-1.5">
            <i data-lucide="help-circle" class="w-4 h-4"></i>
            <span x-text="showGuide ? 'Sembunyikan Panduan RBAC' : 'Pelajari Cara Kerja RBAC Admin'"></span>
        </button>
    </div>

    {{-- INTERACTIVE RBAC CALLOUT --}}
    <div x-show="showGuide" x-transition
        class="rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-2.5 text-[13px] leading-relaxed">
        <div class="font-bold text-black dark:text-white flex items-center gap-2">
            <i data-lucide="shield-alert" class="w-4.5 h-4.5 text-[#007AFF]"></i>
            <span>Prinsip Keamanan Berlapis (Defense-in-Depth) Platform COOCA</span>
        </div>
        <p class="text-black/70 dark:text-white/70">
            Setiap tombol aksi dan rute Controller di Platform Admin dilindungi langsung di level Controller (`HasMiddleware`) serta pemeriksaan visibilitas UI Blade.
            Pengguna dengan peran <strong class="text-black dark:text-white">Super Administrator</strong> memiliki bypass otomatis ke seluruh fitur sistem. Staf internal lainnya hanya dapat mengakses modul dan mengeksekusi aksi yang telah dicentang pada peran masing-masing.
        </p>
    </div>

    {{-- ===================================================== --}}
    {{-- TAB 1: DAFTAR PERAN PLATFORM                          --}}
    {{-- ===================================================== --}}
    <div x-show="activeTab === 'roles'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($roles as $role)
                @php
                    $isSuper = $role->slug === 'super_admin';
                    $isPreset = $role->is_preset || $isSuper;
                    $assignedCount = $adminCountsByRole[$role->slug] ?? 0;
                    $rolePermSlugs = $isSuper
                        ? collect($permissions)->flatten(1)->pluck('slug')->all()
                        : $role->permissions->pluck('slug')->all();
                @endphp
                <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 flex flex-col justify-between shadow-xs hover:border-black/15 dark:hover:border-white/20 transition-all">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight">{{ $role->name }}</h3>
                                    @if($isPreset)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">Preset Sistem</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">Kustom</span>
                                    @endif
                                </div>
                                <p class="text-[11px] font-mono text-black/40 dark:text-white/40 mt-0.5">slug: {{ $role->slug }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 tabular-nums shrink-0">
                                {{ $assignedCount }} Staf
                            </span>
                        </div>

                        <p class="text-[12.5px] text-black/60 dark:text-white/60 line-clamp-2 leading-relaxed">
                            {{ $role->description ?: 'Tidak ada deskripsi peran.' }}
                        </p>

                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11.5px] text-black/50 dark:text-white/50">
                            <span>Cakupan Hak Akses:</span>
                            <span class="font-bold text-black dark:text-white tabular-nums">
                                @if($isSuper)
                                    Semua Izin (Full)
                                @else
                                    {{ count($rolePermSlugs) }} dari {{ collect($permissions)->flatten(1)->count() }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 mt-4 border-t border-black/5 dark:border-white/5 flex items-center gap-2">
                        @if(Auth::guard('admin')->user()?->hasPermission('admin.roles.manage'))
                            <button type="button"
                                @click="openEditRole(@js($role), '{{ route('admin.roles.update', $role) }}', @js($rolePermSlugs))"
                                class="flex-1 h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 font-bold text-[12px] flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                                <span>{{ $isSuper ? 'Lihat Izin Lengkap' : 'Ubah Hak Akses' }}</span>
                            </button>

                            @if(! $isPreset)
                                <button type="button" @click="openDeleteRole(@js($role))"
                                    class="h-9 px-3 rounded-[10px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] font-bold text-[12px] flex items-center justify-center transition-all cursor-pointer"
                                    title="{{ __('admin.hapus_peran_kustom') }}">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            @endif
                        @else
                            <button type="button"
                                @click="openEditRole(@js($role), '{{ route('admin.roles.update', $role) }}', @js($rolePermSlugs))"
                                class="w-full h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 font-bold text-[12px] flex items-center justify-center gap-1.5">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>{{ __('admin.lihat_rincian_izin') }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- TAB 2: STAF ADMINISTRATOR & PENUGASAN                  --}}
    {{-- ===================================================== --}}
    <div x-show="activeTab === 'members'" class="space-y-4">
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
            <div class="px-5 py-4 border-b border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('admin.daftar_akun_administrator_platform') }}</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Seluruh staf internal yang memiliki akses ke Admin Center ini.</p>
                </div>
                <div class="text-[12px] font-semibold text-black/60 dark:text-white/60">
                    Total: <strong class="text-black dark:text-white">{{ $admins->count() }} Staf</strong>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[13px] text-left">
                    <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">
                        <tr>
                            <th class="px-5 py-3.5">Administrator</th>
                            <th class="px-5 py-3.5">Peran Aktif</th>
                            <th class="px-5 py-3.5">Status Akun</th>
                            <th class="px-5 py-3.5">Terdaftar Sejak</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @foreach($admins as $adm)
                            @php
                                $admRole = $roles->firstWhere('slug', $adm->role);
                            @endphp
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#007AFF] to-[#5856D6] text-white font-bold text-[12px] flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($adm->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-black dark:text-white truncate flex items-center gap-1.5">
                                                <span>{{ $adm->name }}</span>
                                                @if(Auth::guard('admin')->id() === $adm->id)
                                                    <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-[#007AFF]/12 text-[#007AFF]">(Anda)</span>
                                                @endif
                                            </div>
                                            <div class="text-[12px] text-black/45 dark:text-white/45 truncate">{{ $adm->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $adm->role === 'super_admin' ? 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]' : 'bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]' }}">
                                        <i data-lucide="shield" class="w-3 h-3"></i>
                                        <span>{{ $admRole?->name ?? ucfirst(str_replace('_', ' ', $adm->role)) }}</span>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    @if($adm->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                            <span>Aktif</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                            <span>Nonaktif</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-black/50 dark:text-white/50 text-[12px]">
                                    {{ $adm->created_at?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if(Auth::guard('admin')->user()?->hasPermission('admin.roles.manage'))
                                        <button type="button"
                                            @click="openChangeAdminRole(@js($adm), '{{ route('admin.roles.admins.role', $adm) }}', '{{ $adm->role }}')"
                                            class="h-8 px-3 rounded-[9px] text-[12px] font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 active:scale-95 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                            <i data-lucide="user-cog" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('admin.ubah_peran') }}</span>
                                        </button>
                                    @else
                                        <span class="text-[12px] text-black/30 dark:text-white/30 italic">Terkunci</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- TAB 3: MATRIKS HAK AKSES LENGKAP                       --}}
    {{-- ===================================================== --}}
    <div x-show="activeTab === 'matrix'" class="space-y-4">
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
            <div class="px-5 py-4 border-b border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Matriks Hak Akses Seluruh Peran Platform</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Pemetaan izin modular terhadap seluruh peran aktif administrator.</p>
                </div>
            </div>

            <div class="overflow-x-auto max-h-[75vh]">
                <table class="w-full text-[12.5px] text-left border-collapse">
                    <thead class="sticky top-0 z-20 bg-white dark:bg-[#1C1C1E] border-b border-black/10 dark:border-white/10 text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 shadow-xs">
                        <tr>
                            <th class="px-5 py-3.5 min-w-[320px] bg-white dark:bg-[#1C1C1E]">Kategori &amp; Nama Izin</th>
                            @foreach($roles as $r)
                                <th class="px-4 py-3.5 text-center min-w-[140px] bg-white dark:bg-[#1C1C1E] border-l border-black/5 dark:border-white/5">
                                    <div class="truncate font-bold text-black dark:text-white">{{ $r->name }}</div>
                                    <div class="text-[9.5px] font-mono font-normal opacity-50">{{ $r->slug }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @foreach($permissions as $cat => $permGroup)
                            {{-- Category Divider Row --}}
                            <tr class="bg-black/[0.03] dark:bg-white/[0.04] font-bold text-[12px] text-black dark:text-white">
                                <td colspan="{{ $roles->count() + 1 }}" class="px-5 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="folder" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                        <span>{{ $categoryLabels[$cat] ?? ucfirst(str_replace(['admin_', '_'], ['', ' '], $cat)) }}</span>
                                        <span class="text-[10px] font-semibold text-black/45 dark:text-white/45 tabular-nums">({{ $permGroup->count() }} izin)</span>
                                    </div>
                                </td>
                            </tr>

                            {{-- Permission Rows --}}
                            @foreach($permGroup as $p)
                                <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                                    <td class="px-5 py-3">
                                        <div class="font-semibold text-black dark:text-white">{{ $p->name }}</div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">{{ $p->description }}</div>
                                        <div class="text-[10px] font-mono text-[#007AFF] mt-0.5">{{ $p->slug }}</div>
                                    </td>
                                    @foreach($roles as $r)
                                        @php
                                            $hasPerm = $r->slug === 'super_admin' || $r->permissions->contains('id', $p->id);
                                        @endphp
                                        <td class="px-4 py-3 text-center border-l border-black/5 dark:border-white/5">
                                            @if($hasPerm)
                                                <div class="w-6 h-6 mx-auto rounded-full bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                                                    <i data-lucide="check" class="w-3.5 h-3.5" stroke-width="2.5"></i>
                                                </div>
                                            @else
                                                <span class="text-black/20 dark:text-white/20 text-[14px]"> </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- MODAL: TAMBAH / UBAH HAK AKSES PERAN                   --}}
    {{-- ===================================================== --}}
    <div x-show="showRoleModal" x-cloak
        class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-5 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showRoleModal = false">
        
        <div class="w-full max-w-4xl max-h-[90vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col overflow-hidden"
            @click.outside="showRoleModal = false">
            
            {{-- Modal Header --}}
            <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between shrink-0">
                <div>
                    <h2 class="text-[18px] font-bold text-black dark:text-white" x-text="isEditing ? (isPresetRole ? 'Hak Akses Peran Preset: ' + roleName : 'Ubah Peran Kustom: ' + roleName) : 'Tambah Peran Kustom Baru'"></h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50" x-text="isPresetRole ? 'Peran bawaan sistem hanya dapat dimodifikasi daftar izinnya.' : 'Konfigurasikan nama dan daftar izin modular peran ini.'"></p>
                </div>
                <button type="button" @click="showRoleModal = false" class="p-1.5 rounded-full hover:bg-black/5 dark:hover:bg-white/10 text-black/40 dark:text-white/40">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Modal Body Form --}}
            <form :action="roleFormAction" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <template x-if="roleFormMethod === 'PUT'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="p-6 space-y-5 overflow-y-auto flex-1">
                    {{-- Role Metadata Inputs --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Nama Peran *</label>
                            <input type="text" name="name" x-model="roleName" required :disabled="isPresetRole"
                                placeholder="Contoh: Admin Pemasaran Khusus"
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] disabled:opacity-60">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-bold text-black dark:text-white">Deskripsi Peran</label>
                            <input type="text" name="description" x-model="roleDescription"
                                placeholder="Jelaskan ruang lingkup tanggung jawab peran ini..."
                                class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    {{-- Search Permission Filter Bar --}}
                    <div class="pt-2 border-t border-black/5 dark:border-white/5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-[13px] font-bold text-black dark:text-white">Daftar Hak Akses Modular</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/12 text-[#007AFF] tabular-nums"
                                    x-text="selectedPermissions.length + ' Dipilih'"></span>
                            </div>

                            <div class="relative w-full sm:w-64">
                                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                                <input type="text" x-model="searchPermission" placeholder="{{ __('admin.cari_nama_atau_slug_izin') }}"
                                    class="w-full h-8.5 pl-8 pr-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                            </div>
                        </div>

                        {{-- Permission Accordions / Checkboxes --}}
                        <div class="space-y-4">
                            @foreach($permissions as $categoryKey => $items)
                                @php
                                    $catSlugs = $items->pluck('slug')->all();
                                @endphp
                                <div class="rounded-[16px] bg-black/[0.015] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 overflow-hidden">
                                    <div class="px-4 py-3 bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/5 dark:border-white/5 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="folder-key" class="w-4 h-4 text-[#007AFF]"></i>
                                            <span class="text-[13px] font-bold text-black dark:text-white">
                                                {{ $categoryLabels[$categoryKey] ?? ucfirst(str_replace(['admin_', '_'], ['', ' '], $categoryKey)) }}
                                            </span>
                                            <span class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">({{ count($catSlugs) }})</span>
                                        </div>

                                        <template x-if="roleSlug !== 'super_admin'">
                                            <label class="inline-flex items-center gap-1.5 text-[11.5px] font-bold text-[#007AFF] cursor-pointer">
                                                <input type="checkbox"
                                                    :checked="isCategoryAllSelected(@js($catSlugs))"
                                                    @change="toggleCategory(@js($catSlugs), $event.target.checked)"
                                                    class="rounded border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                                <span>Pilih Semua</span>
                                            </label>
                                        </template>
                                    </div>

                                    <div class="p-3.5 grid grid-cols-1 md:grid-cols-2 gap-2">
                                        @foreach($items as $item)
                                            <label x-show="!searchPermission || '{{ strtolower($item->name . ' ' . $item->slug . ' ' . $item->description) }}'.includes(searchPermission.toLowerCase())"
                                                class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 flex items-start gap-2.5 hover:border-[#007AFF]/40 cursor-pointer transition-colors">
                                                <input type="checkbox" name="permissions[]" value="{{ $item->slug }}"
                                                    x-model="selectedPermissions"
                                                    :disabled="roleSlug === 'super_admin'"
                                                    class="mt-1 rounded border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-[12.5px] font-bold text-black dark:text-white leading-snug">{{ $item->name }}</div>
                                                    <div class="text-[11px] text-black/50 dark:text-white/50 leading-relaxed mt-0.5">{{ $item->description }}</div>
                                                    <div class="text-[10px] font-mono text-black/40 dark:text-white/40 mt-1">{{ $item->slug }}</div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-3 shrink-0 bg-black/[0.01] dark:bg-white/[0.01]">
                    <button type="button" @click="showRoleModal = false"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">{{ __('common.cancel') }}</button>
                    @if(Auth::guard('admin')->user()?->hasPermission('admin.roles.manage'))
                        <button type="submit"
                            class="h-10 px-6 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-2 shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span x-text="isEditing ? 'Simpan Perubahan Hak Akses' : 'Buat Peran Sekarang'"></span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- MODAL: UBAH PERAN STAF ADMINISTRATOR                   --}}
    {{-- ===================================================== --}}
    <div x-show="showMemberModal" x-cloak
        class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showMemberModal = false">
        
        <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden"
            @click.outside="showMemberModal = false">
            
            <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-[17px] font-bold text-black dark:text-white">{{ __('admin.ubah_peran_staf_administrator') }}</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50" x-text="selectedAdmin ? selectedAdmin.name + ' (' + selectedAdmin.email + ')' : ''"></p>
                </div>
                <button type="button" @click="showMemberModal = false" class="p-1 rounded-full hover:bg-black/5 dark:hover:bg-white/10 text-black/40">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="memberFormAction" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PUT">

                <div class="space-y-2">
                    <label class="block text-[12px] font-bold text-black dark:text-white">Pilih Peran Baru:</label>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        @foreach($roles as $r)
                            <label class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between cursor-pointer hover:border-[#007AFF]/40 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <input type="radio" name="role" value="{{ $r->slug }}" x-model="targetRoleId"
                                        class="text-[#007AFF] focus:ring-[#007AFF]">
                                    <div>
                                        <div class="text-[13px] font-bold text-black dark:text-white">{{ $r->name }}</div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50 line-clamp-1">{{ $r->description }}</div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40 tabular-nums">{{ $r->slug }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showMemberModal = false"
                        class="h-9.5 px-4 rounded-[10px] text-[13px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5">{{ __('common.cancel') }}</button>
                    @if(Auth::guard('admin')->user()?->hasPermission('admin.roles.manage'))
                        <button type="submit"
                            class="h-9.5 px-5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13px] shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>{{ __('admin.simpan_peran') }}</span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- MODAL: KONFIRMASI HAPUS PERAN KUSTOM                   --}}
    {{-- ===================================================== --}}
    <div x-show="showDeleteModal" x-cloak
        class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showDeleteModal = false">
        
        <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-4"
            @click.outside="showDeleteModal = false">
            
            <div class="w-12 h-12 rounded-[16px] bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center">
                <i data-lucide="trash-2" class="w-6 h-6"></i>
            </div>

            <div>
                <h3 class="text-[17px] font-bold text-black dark:text-white">{{ __('admin.hapus_peran_kustom_2') }}</h3>
                <p class="text-[12.5px] text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                    Apakah Anda yakin ingin menghapus peran <strong class="text-black dark:text-white" x-text="deleteTargetRole?.name"></strong>?
                    Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2.5">
                <button type="button" @click="showDeleteModal = false"
                    class="h-9.5 px-4 rounded-[10px] text-[13px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5">{{ __('common.cancel') }}</button>
                @if(Auth::guard('admin')->user()?->hasPermission('admin.roles.manage'))
                    <form :action="'/admin/roles/' + deleteTargetRole?.id" method="POST">
                        @csrf
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit"
                            class="h-9.5 px-5 rounded-[10px] bg-[#FF3B30] hover:bg-[#E02D23] text-white font-bold text-[13px] shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>{{ __('admin.ya_hapus_peran') }}</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endpush
@endsection
