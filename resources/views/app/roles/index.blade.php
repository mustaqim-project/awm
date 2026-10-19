@extends('layouts.app', [
    'title' => __('roles.page_title'),
    'headerTitle' => __('roles.header_title'),
    'headerSubtitle' => __('roles.header_subtitle'),
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
        activeTab: '{{ \App\Support\Context::hasPermission('roles.view') || \App\Support\Context::hasPermission('roles.manage') ? 'roles' : (\App\Support\Context::hasPermission('users.view') || \App\Support\Context::hasPermission('users.manage') ? 'members' : 'matrix') }}',
        showGuide: true,
        showCreateModal: false,
        showMemberRoleModal: false,
        editingRole: null,
        editingPreset: false,
        editFormAction: '',
        searchPermission: '',
        selectedPermissions: [],
        editingMember: null,
        memberRoleAction: '',
        memberSelectedRoleId: '',
    
        // Delete Role Dialog State
        deleteRoleModalOpen: false,
        deleteRoleTarget: { id: null, name: '' },
        openDeleteRole(id, name) {
            this.deleteRoleTarget = { id, name };
            this.deleteRoleModalOpen = true;
        },
        closeDeleteRole() {
            this.deleteRoleModalOpen = false;
            this.deleteRoleTarget = { id: null, name: '' };
        },
        submitDeleteRole() {
            if (this.deleteRoleTarget.id) {
                document.getElementById('form-delete-role-' + this.deleteRoleTarget.id).submit();
            }
        },
    
        // Remove Member Dialog State
        deleteMemberModalOpen: false,
        deleteMemberTarget: { id: null, name: '' },
        openDeleteMember(id, name) {
            this.deleteMemberTarget = { id, name };
            this.deleteMemberModalOpen = true;
        },
        closeDeleteMember() {
            this.deleteMemberModalOpen = false;
            this.deleteMemberTarget = { id: null, name: '' };
        },
        submitDeleteMember() {
            if (this.deleteMemberTarget.id) {
                document.getElementById('form-delete-member-' + this.deleteMemberTarget.id).submit();
            }
        },
    
        openCreateModal() {
            this.editingRole = null;
            this.editingPreset = false;
            this.selectedPermissions = [];
            this.showCreateModal = true;
        },
    
        openEditModal(role, actionUrl, permSlugs) {
            this.editingRole = role;
            this.editingPreset = false;
            this.editFormAction = actionUrl;
            this.selectedPermissions = [...permSlugs];
            this.showCreateModal = true;
        },
    
        openEditPresetModal(role, actionUrl, permSlugs) {
            this.editingRole = role;
            this.editingPreset = true;
            this.editFormAction = actionUrl;
            this.selectedPermissions = [...permSlugs];
            this.showCreateModal = true;
        },
    
        openChangeMemberRoleModal(member, actionUrl, currentRoleId) {
            this.editingMember = member;
            this.memberRoleAction = actionUrl;
            this.memberSelectedRoleId = currentRoleId;
            this.showMemberRoleModal = true;
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
            return categorySlugs.every(slug => this.selectedPermissions.includes(slug));
        }
    }">

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
        <!-- ===================================================== -->
        <header
            class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div>
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1"
                    aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">{{ __('roles.breadcrumb_dashboard') }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <span class="text-black/70 dark:text-white/70 font-medium">{{ __('roles.breadcrumb_settings') }}</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <span class="text-black dark:text-white font-medium">{{ __('roles.breadcrumb_rbac') }}</span>
                </nav>
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">{{ __('roles.title') }}</h1>
                <p class="text-[13px] text-black/50 dark:text-white/50">{{ __('roles.subtitle') }}</p>
            </div>

            <!-- Toolbar Actions & Primary Button -->
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                @if (\App\Support\Context::hasPermission('roles.create') || \App\Support\Context::hasPermission('roles.manage'))
                    <button type="button" @click="openCreateModal()"
                        class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] w-full sm:w-auto">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('roles.btn_create_custom_role') }}</span>
                    </button>
                @endif
            </div>
        </header>

        <!-- Flash Notifications (Apple Banner Style) -->
        @if (session('success'))
            <div
                class="rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
                <div class="flex-1 font-medium">{{ session('success') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div
                class="rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 px-4 py-3 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-[#FF3B30] shrink-0"></span>
                <div class="flex-1 font-medium">{{ session('error') }}</div>
            </div>
        @endif

        <!-- ===================================================== -->
        <!-- 2. KPI SUMMARY (Flat Neutral Material, Apple Standard) -->
        <!-- ===================================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Tile 1: Total Role -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('roles.kpi_total_roles') }}</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ $roles->count() }}</span>
                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">{{ __('roles.kpi_preset_custom') }}</span>
                </div>
            </div>

            <!-- Tile 2: Anggota Tim Aktif -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('roles.kpi_active_members') }}</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span
                        class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $members->count() }}</span>
                    <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">{{ __('roles.kpi_store_employees') }}</span>
                </div>
            </div>

            <!-- Tile 3: Cakupan Permissions -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('roles.kpi_granular_permissions') }}</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span
                        class="text-[24px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6]">{{ collect($permissions)->flatten(1)->count() }}</span>
                    <span class="text-[11px] font-medium text-[#5856D6] dark:text-[#5E5CE6]">{{ __('roles.kpi_system_modules') }}</span>
                </div>
            </div>

            <!-- Tile 4: Keamanan Data & HPP -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('roles.kpi_security_protection') }}</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-[18px] font-bold text-[#AF52DE] dark:text-[#BF5AF2]">{{ __('roles.kpi_rbac_active') }}</span>
                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">{{ __('roles.kpi_multi_user') }}</span>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 3. SEGMENTED NAVIGATION & VIEW SWITCHER               -->
        <!-- ===================================================== -->
        <div class="flex items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
            <div class="overflow-x-auto pb-1 scrollbar-none">
                <div class="inline-flex p-1 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-[13px] font-medium whitespace-nowrap">
                    @if (\App\Support\Context::hasPermission('roles.view') || \App\Support\Context::hasPermission('roles.manage'))
                        <button type="button" @click="activeTab = 'roles'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                            :class="activeTab === 'roles' ?
                                'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' :
                                'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                            class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-2 cursor-pointer">
                            <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>{{ __('roles.tab_roles') }}</span>
                            <span
                                class="px-1.5 py-0.2 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] tabular-nums">{{ $roles->count() }}</span>
                        </button>
                    @endif

                    @if (\App\Support\Context::hasPermission('users.view') || \App\Support\Context::hasPermission('users.manage'))
                        <button type="button" @click="activeTab = 'members'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                            :class="activeTab === 'members' ?
                                'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' :
                                'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                            class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-2 cursor-pointer">
                            <i data-lucide="users" class="w-4 h-4 text-[#34C759]"></i>
                            <span>{{ __('roles.tab_members') }}</span>
                            <span
                                class="px-1.5 py-0.2 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] tabular-nums">{{ $members->count() }}</span>
                        </button>
                    @endif

                    @if (\App\Support\Context::hasPermission('roles.view') || \App\Support\Context::hasPermission('roles.manage'))
                        <button type="button" @click="activeTab = 'matrix'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                            :class="activeTab === 'matrix' ?
                                'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' :
                                'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                            class="px-3.5 py-1.5 rounded-[10px] transition-all flex items-center gap-2 cursor-pointer">
                            <i data-lucide="table-properties" class="w-4 h-4 text-[#AF52DE]"></i>
                            <span>{{ __('roles.tab_matrix') }}</span>
                        </button>
                    @endif
                </div>
            </div>

            <button type="button" @click="showGuide = !showGuide"
                class="text-[12px] text-[#007AFF] hover:underline font-medium flex items-center gap-1 shrink-0">
                <i data-lucide="info" class="w-3.5 h-3.5"></i>
                <span x-text="showGuide ? '{{ __('roles.btn_hide_guide') }}' : '{{ __('roles.btn_show_guide') }}'"></span>
            </button>
        </div>

        <!-- Interactive Apple Callout: Panduan RBAC -->
        <div x-show="showGuide" x-transition
            class="rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                <h2 class="text-[13px] font-semibold uppercase tracking-wider text-black/60 dark:text-white/60">{{ __('roles.guide_title') }}</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div
                    class="rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 space-y-1">
                    <span class="text-[11px] font-semibold text-[#007AFF]">{{ __('roles.guide_1_title') }}</span>
                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">{{ __('roles.guide_1_desc') }}</p>
                </div>
                <div
                    class="rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 space-y-1">
                    <span class="text-[11px] font-semibold text-[#34C759]">{{ __('roles.guide_2_title') }}</span>
                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">{{ __('roles.guide_2_desc') }}</p>
                </div>
                <div
                    class="rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 space-y-1">
                    <span class="text-[11px] font-semibold text-[#FF9500]">{{ __('roles.guide_3_title') }}</span>
                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">{{ __('roles.guide_3_desc') }}</p>
                </div>
                <div
                    class="rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 space-y-1">
                    <span class="text-[11px] font-semibold text-[#AF52DE]">{{ __('roles.guide_4_title') }}</span>
                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">{{ __('roles.guide_4_desc') }}</p>
                </div>
            </div>
        </div>

        @if (\App\Support\Context::hasPermission('roles.view') || \App\Support\Context::hasPermission('roles.manage'))
            <!-- ===================================================== -->
            <!-- TAB 1: DAFTAR ROLE CARDS                              -->
            <!-- ===================================================== -->
            <div x-show="activeTab === 'roles'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($roles as $role)
                        @php
                            $isCustom = (bool) $role->business_id;
                            $permSlugs = $role->permissions->pluck('slug')->toArray();
                            $memberCount = $role->memberships()->count();
                        @endphp
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between gap-4">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-[16px] font-semibold text-black dark:text-white">
                                                {{ $role->name }}</h3>
                                            @if ($isCustom)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#007AFF]/12 text-[#007AFF]">
                                                    {{ __('roles.badge_custom') }}
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                                                    {{ __('roles.badge_preset') }}
                                                </span>
                                            @endif
                                        </div>
                                        <p
                                            class="text-[12px] text-black/50 dark:text-white/50 mt-1 line-clamp-2 leading-relaxed">
                                            {{ $role->description ?: __('roles.no_description') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Role Stats -->
                                <div
                                    class="flex items-center gap-4 text-[12px] text-black/50 dark:text-white/50 pt-3 border-t border-black/[0.04] dark:border-white/[0.06]">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="font-bold tabular-nums text-black dark:text-white">{{ $role->permissions->count() }}</span>
                                        <span>{{ __('roles.stat_permissions') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="font-bold tabular-nums text-black dark:text-white">{{ $memberCount }}</span>
                                        <span>{{ __('roles.stat_members') }}</span>
                                    </div>
                                </div>

                                <!-- Permissions Pill Preview -->
                                <div class="space-y-1.5">
                                    <span
                                        class="text-[10px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">{{ __('roles.scope_permissions') }}</span>
                                    <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto pr-1">
                                        @forelse($role->permissions->take(8) as $perm)
                                            <span
                                                class="text-[11px] px-2 py-0.5 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 tabular-nums">
                                                {{ \Illuminate\Support\Str::after($perm->slug, '.') }}
                                            </span>
                                        @empty
                                            <span class="text-[11px] text-black/40 dark:text-white/40 italic">{{ __('roles.no_permissions_assigned') }}</span>
                                        @endforelse
                                        @if ($role->permissions->count() > 8)
                                            <span
                                                class="text-[11px] px-1.5 py-0.5 rounded-[6px] bg-black/[0.08] dark:bg-white/[0.1] text-black/70 dark:text-white/70 font-semibold tabular-nums">
                                                {{ __('roles.more_permissions', ['count' => $role->permissions->count() - 8]) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Card Actions -->
                            <div
                                class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-2">
                                @if ($isCustom)
                                    @if (\App\Support\Context::hasPermission('roles.edit') || \App\Support\Context::hasPermission('roles.manage'))
                                        <button type="button"
                                            @click="openEditModal({{ Js::from($role) }}, '{{ route('roles.update', $role) }}', {{ Js::from($permSlugs) }})"
                                            class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                            <span>{{ __('roles.btn_edit_permission') }}</span>
                                        </button>
                                    @endif

                                    @if (\App\Support\Context::hasPermission('roles.delete') || \App\Support\Context::hasPermission('roles.manage'))
                                        <button type="button"
                                            @click="openDeleteRole({{ $role->id }}, '{{ addslashes($role->name) }}')"
                                            class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center"
                                            title="{{ __('roles.btn_delete_role') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>

                                        <form id="form-delete-role-{{ $role->id }}" method="POST"
                                            action="{{ route('roles.destroy', $role) }}" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                @else
                                    <div class="flex items-center justify-between gap-2 w-full">
                                        <span class="text-[12px] text-[#FF9500] flex items-center gap-1.5 font-medium">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                            </svg>
                                            {{ __('roles.badge_name_protected') }}
                                        </span>
                                        @if (\App\Support\Context::hasPermission('roles.edit') || \App\Support\Context::hasPermission('roles.manage'))
                                            <button type="button"
                                                @click="openEditPresetModal({{ Js::from($role) }}, '{{ route('roles.update', $role) }}', {{ Js::from($permSlugs) }})"
                                                class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                                <span>{{ __('roles.btn_edit_permission') }}</span>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if (\App\Support\Context::hasPermission('users.view') || \App\Support\Context::hasPermission('users.manage'))
            <!-- ===================================================== -->
            <!-- TAB 2: ANGGOTA TIM & TAMBAH KARYAWAN                  -->
            <!-- ===================================================== -->
            <div x-show="activeTab === 'members'" class="space-y-6" style="display: none;">
                @if (\App\Support\Context::hasPermission('users.create') || \App\Support\Context::hasPermission('users.manage'))
                    <!-- Form Tambah Karyawan Baru -->
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
                        <div class="border-b border-black/5 dark:border-white/5 pb-3">
                            <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('roles.add_member_title') }}</h2>
                            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('roles.add_member_subtitle') }}</p>
                        </div>

                        <form method="POST" action="{{ route('roles.members.store') }}"
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
                            @csrf
                            <div>
                                <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('roles.field_employee_name') }} <span class="text-[#FF3B30]">*</span></label>
                                <input type="text" name="name" required placeholder="{{ __('roles.field_employee_placeholder') }}"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <div>
                                <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('roles.field_email') }} <span class="text-[#FF3B30]">*</span></label>
                                <input type="email" name="email" required placeholder="{{ __('roles.field_email_placeholder') }}"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <div>
                                <label
                                    class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('roles.field_initial_password') }}</label>
                                <input type="password" name="password" minlength="6" placeholder="{{ __('roles.field_password_placeholder') }}"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <div>
                                <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('roles.field_role') }} <span class="text-[#FF3B30]">*</span></label>
                                <select name="role_id" required
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                                    @foreach ($roles as $roleOption)
                                        <option value="{{ $roleOption->id }}">
                                            {{ $roleOption->name }}{{ $roleOption->business_id ? __('roles.role_custom_tag') : __('roles.role_preset_tag') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit"
                                class="h-11 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.765z" />
                                </svg>
                                <span>{{ __('roles.btn_add_employee') }}</span>
                            </button>
                        </form>
                    </div>
                @endif

                <!-- Tabel Daftar Karyawan Aktif -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
                    <div class="p-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div>
                            <h3 class="text-[15px] font-semibold text-black dark:text-white">{{ __('roles.members_table_title', ['business' => $business->name]) }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('roles.members_table_subtitle') }}</p>
                        </div>
                        <span class="text-[12px] font-medium text-black/60 dark:text-white/60 tabular-nums">
                            {{ __('roles.members_count_tag', ['count' => $members->count()]) }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr
                                    class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 text-[11px] font-semibold uppercase tracking-wide">
                                    <th class="py-2.5 px-4">{{ __('roles.th_user_name') }}</th>
                                    <th class="py-2.5 px-4">{{ __('roles.th_email') }}</th>
                                    <th class="py-2.5 px-4">{{ __('roles.th_role') }}</th>
                                    @if (
                                        \App\Support\Context::hasPermission('users.edit') ||
                                            \App\Support\Context::hasPermission('users.delete') ||
                                            \App\Support\Context::hasPermission('users.manage'))
                                        <th class="py-2.5 px-4 text-right">{{ __('roles.th_action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                @foreach ($members as $member)
                                    @php
                                        $memberRoleId =
                                            $member->role_id ?? ($roles->firstWhere('slug', $member->role)?->id ?? '');
                                    @endphp
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-8 h-8 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-[12px] uppercase shrink-0">
                                                    {{ substr($member->user?->name ?? 'U', 0, 2) }}
                                                </div>
                                                <div>
                                                    <span
                                                        class="font-medium text-black dark:text-white">{{ $member->user?->name }}</span>
                                                    @if ($member->role === 'owner')
                                                        <span
                                                            class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#AF52DE]/12 text-[#AF52DE]">{{ __('roles.owner_badge') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 tabular-nums text-black/60 dark:text-white/60">
                                            {{ $member->user?->email }}</td>
                                        <td class="py-3 px-4">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-[6px] bg-black/[0.05] dark:bg-white/[0.08] text-black/80 dark:text-white/80 font-medium text-[12px]">
                                                {{ $member->customRole?->name ?? ucfirst($member->role) }}
                                            </span>
                                        </td>
                                        @if (
                                            \App\Support\Context::hasPermission('users.edit') ||
                                                \App\Support\Context::hasPermission('users.delete') ||
                                                \App\Support\Context::hasPermission('users.manage'))
                                            <td class="py-3 px-4 text-right">
                                                @if ($member->role !== 'owner')
                                                    <div class="flex items-center justify-end gap-1.5">
                                                        @if (\App\Support\Context::hasPermission('users.edit') || \App\Support\Context::hasPermission('users.manage'))
                                                             <button type="button"
                                                                @click="openChangeMemberRoleModal({{ Js::from($member) }}, '{{ route('roles.members.role', $member) }}', '{{ $memberRoleId }}')"
                                                                class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center gap-1">
                                                                <span>{{ __('roles.btn_change_role') }}</span>
                                                            </button>
                                                        @endif

                                                        @if (\App\Support\Context::hasPermission('users.delete') || \App\Support\Context::hasPermission('users.manage'))
                                                            <button type="button"
                                                                @click="openDeleteMember({{ $member->id }}, '{{ addslashes($member->user?->name ?? 'Karyawan') }}')"
                                                                class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors flex items-center"
                                                                title="{{ __('roles.title_remove_member') }}">
                                                                {{ __('roles.btn_remove_member') }}
                                                            </button>

                                                            <form id="form-delete-member-{{ $member->id }}"
                                                                method="POST"
                                                                action="{{ route('roles.members.destroy', $member->id) }}"
                                                                class="hidden">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-[11px] text-black/40 dark:text-white/40 italic">{{ __('roles.owner_primary_tag') }}</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if (\App\Support\Context::hasPermission('roles.view') || \App\Support\Context::hasPermission('roles.manage'))
            <!-- ===================================================== -->
            <!-- TAB 3: MATRIKS HAK AKSES LENGKAP                      -->
            <!-- ===================================================== -->
            <div x-show="activeTab === 'matrix'" class="space-y-4" style="display: none;">
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('roles.matrix_title') }}</h2>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('roles.matrix_subtitle') }}</p>
                    </div>

                    <!-- Search Permission Filter -->
                    <div class="relative w-full sm:w-64">
                        <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input type="text" x-model="searchPermission" placeholder="{{ __('roles.search_placeholder') }}"
                            class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                @if (($hiddenPermissionsCount ?? 0) > 0)
                    <div
                        class="rounded-[12px] bg-[#007AFF]/10 border border-[#007AFF]/20 p-3.5 flex items-start gap-3 text-[12px] text-[#007AFF] dark:text-[#64D2FF]">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <div class="flex-1">
                            {{ __('roles.hidden_permissions_notice', ['count' => $hiddenPermissionsCount]) }}
                            <a href="{{ route('settings.index', ['tab' => 'modules']) }}"
                                class="font-semibold underline hover:opacity-80">{{ __('roles.link_manage_modules') }}</a>.
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($permissions as $category => $categoryPermissions)
                        <div
                            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 space-y-3">
                            <div
                                class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                                <span
                                    class="text-[12px] font-semibold text-black dark:text-white uppercase tracking-wider">
                                    {{ $category ?: __('roles.default_category') }}
                                </span>
                                <span
                                    class="text-[11px] tabular-nums text-black/40 dark:text-white/40">{{ __('roles.category_item_count', ['count' => count($categoryPermissions)]) }}</span>
                            </div>

                            <div class="space-y-1.5">
                                @foreach ($categoryPermissions as $permission)
                                    <div class="p-2.5 rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5"
                                        x-show="!searchPermission || '{{ strtolower($permission->name . ' ' . $permission->slug) }}'.includes(searchPermission.toLowerCase())">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0 mt-1.5"></span>
                                        <div class="min-w-0">
                                            <div class="text-[13px] font-medium text-black dark:text-white">
                                                {{ $permission->name }}</div>
                                            <div class="text-[11px] font-mono text-black/40 dark:text-white/40 mt-0.5">
                                                {{ $permission->slug }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if (
            \App\Support\Context::hasPermission('roles.create') ||
                \App\Support\Context::hasPermission('roles.edit') ||
                \App\Support\Context::hasPermission('roles.manage'))
            <!-- ===================================================== -->
            <!-- MODAL 1: CREATE / EDIT CUSTOM ROLE (Bento Apple Sheet) -->
            <!-- ===================================================== -->
            <template x-teleport="body">
                <div x-show="showCreateModal" x-cloak
                    class="fixed inset-0 z-[200] overflow-y-auto"
                    @keydown.escape.window="showCreateModal = false"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="roles-create-title">

                    <!-- Backdrop -->
                    <div x-show="showCreateModal"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="showCreateModal = false"
                        class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

                    <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showCreateModal = false">
                        <div class="relative w-full max-w-2xl max-h-[92vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col overflow-hidden my-auto"
                            @click.stop
                            x-show="showCreateModal"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95">

                            <!-- Sheet Header -->
                            <div class="px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                                <div>
                                    <h3 id="roles-create-title" class="text-[17px] font-semibold text-black dark:text-white"
                                        x-text="editingRole ? (editingPreset ? '{{ __('roles.modal_edit_preset_title') }}' + editingRole.name : '{{ __('roles.modal_edit_title') }}' + editingRole.name) : '{{ __('roles.modal_create_title') }}'">
                                    </h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50"
                                        x-text="editingPreset ? '{{ __('roles.modal_edit_preset_subtitle') }}' : '{{ __('roles.modal_create_subtitle') }}'">
                                    </p>
                                </div>
                                <button type="button" @click="showCreateModal = false"
                                    class="w-10 h-10 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer"
                                    aria-label="{{ __('common.close') }}">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <!-- Form -->
                            <form :action="editingRole ? editFormAction : '{{ route('roles.store') }}'" method="POST"
                                class="flex flex-col flex-1 overflow-hidden">
                                @csrf
                                <template x-if="editingRole">
                                    <input type="hidden" name="_method" value="PUT">
                                </template>

                                <div class="p-6 space-y-5 overflow-y-auto flex-1">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                                {{ __('roles.field_role_name') }}
                                                <span class="text-[#FF3B30]" x-show="!editingPreset">*</span>
                                            </label>
                                            <input type="text" name="name" :required="!editingPreset"
                                                :value="editingRole ? editingRole.name : ''" :disabled="editingPreset"
                                                :class="editingPreset ? 'opacity-50 cursor-not-allowed' : ''"
                                                placeholder="{{ __('roles.field_role_name_placeholder') }}"
                                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                            <p class="text-[11px] text-[#FF9500] mt-1 flex items-center gap-1"
                                                x-show="editingPreset">
                                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                </svg>
                                                {{ __('roles.system_role_name_protected') }}
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('roles.field_role_description') }}</label>
                                            <input type="text" name="description"
                                                :value="editingRole ? editingRole.description : ''" :disabled="editingPreset"
                                                :class="editingPreset ? 'opacity-50 cursor-not-allowed' : ''"
                                                placeholder="{{ __('roles.field_role_description_placeholder') }}"
                                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        </div>
                                    </div>

                                    <!-- Permissions Selection -->
                                    <div class="space-y-3 pt-2">
                                        <div class="flex items-center justify-between">
                                            <label class="text-[13px] font-semibold text-black dark:text-white">{{ __('roles.select_permissions') }} <span class="text-[#FF3B30]">*</span></label>
                                            <span class="text-[12px] font-semibold text-[#007AFF] tabular-nums"
                                                x-text="selectedPermissions.length + ' {{ __('roles.permissions_selected_count') }}'"></span>
                                        </div>

                                        @if (($hiddenPermissionsCount ?? 0) > 0)
                                            <div
                                                class="rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 p-2.5 text-[11px] text-black/60 dark:text-white/60 flex items-center justify-between gap-2">
                                                <span>{{ __('roles.disabled_modules_notice') }}</span>
                                                <a href="{{ route('settings.index', ['tab' => 'modules']) }}" target="_blank"
                                                    class="text-[#007AFF] hover:underline font-medium shrink-0">{{ __('roles.link_open_module_settings') }}</a>
                                            </div>
                                        @endif

                                        <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                                            @foreach ($permissions as $category => $categoryPermissions)
                                                @php
                                                    $categorySlugs = $categoryPermissions->pluck('slug')->toArray();
                                                @endphp
                                                <div
                                                    class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 space-y-2.5">
                                                    <div class="flex items-center justify-between">
                                                        <span
                                                            class="text-[12px] font-semibold uppercase tracking-wide text-black/70 dark:text-white/70">
                                                            {{ $category ?: __('common.other') }}
                                                        </span>
                                                        <button type="button"
                                                            @click="toggleCategory({{ Js::from($categorySlugs) }}, !isCategoryAllSelected({{ Js::from($categorySlugs) }}))"
                                                            class="text-[11px] text-[#007AFF] hover:underline font-semibold">
                                                            <span
                                                                x-text="isCategoryAllSelected({{ Js::from($categorySlugs) }}) ? '{{ __('roles.btn_deselect_all') }}' : '{{ __('roles.btn_select_all') }}'"></span>
                                                        </button>
                                                    </div>

                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                        @foreach ($categoryPermissions as $permission)
                                                            <label
                                                                class="flex items-start gap-2.5 p-2 rounded-[8px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 hover:border-[#007AFF]/30 cursor-pointer transition select-none">
                                                                <input type="checkbox" name="permissions[]"
                                                                    value="{{ $permission->slug }}" x-model="selectedPermissions"
                                                                    class="mt-0.5 rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                                                <div class="min-w-0">
                                                                    <span
                                                                        class="font-medium text-black dark:text-white text-[12px] block">{{ $permission->name }}</span>
                                                                    <span
                                                                        class="text-[10px] font-mono text-black/40 dark:text-white/40 block truncate">{{ $permission->slug }}</span>
                                                                </div>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div
                                    class="px-6 py-3.5 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2.5">
                                    <button type="button" @click="showCreateModal = false"
                                        class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                                        {{ __('roles.btn_cancel') }}
                                    </button>
                                    <button type="submit"
                                        class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                                        <span
                                            x-text="editingPreset ? '{{ __('roles.btn_save_permissions') }}' : (editingRole ? '{{ __('roles.btn_save_changes') }}' : '{{ __('roles.btn_create_role') }}')"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </template>
        @endif

        @if (\App\Support\Context::hasPermission('users.edit') || \App\Support\Context::hasPermission('users.manage'))
            <!-- ===================================================== -->
            <!-- MODAL 2: CHANGE MEMBER ROLE (Bento Apple Sheet)        -->
            <!-- ===================================================== -->
            <template x-teleport="body">
                <div x-show="showMemberRoleModal" x-cloak
                    class="fixed inset-0 z-[200] overflow-y-auto"
                    @keydown.escape.window="showMemberRoleModal = false"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="change-member-role-title">

                    <!-- Backdrop -->
                    <div x-show="showMemberRoleModal"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="showMemberRoleModal = false"
                        class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

                    <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showMemberRoleModal = false">
                        <div class="relative w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden flex flex-col my-auto"
                            @click.stop
                            x-show="showMemberRoleModal"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95">

                            <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                                <div>
                                    <h3 id="change-member-role-title" class="text-[17px] font-semibold text-black dark:text-white">{{ __('roles.modal_change_role_title') }}</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50"
                                        x-text="editingMember ? (editingMember.user ? editingMember.user.name : '{{ __('roles.stat_members') }}') : ''">
                                    </p>
                                </div>
                                <button type="button" @click="showMemberRoleModal = false"
                                    class="w-10 h-10 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer"
                                    aria-label="{{ __('common.close') }}">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <form :action="memberRoleAction" method="POST" class="p-5 space-y-4">
                                @csrf
                                <input type="hidden" name="_method" value="PUT">

                                <div>
                                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('roles.select_new_role') }} <span class="text-[#FF3B30]">*</span></label>
                                    <select name="role_id" required x-model="memberSelectedRoleId"
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                                        @foreach ($roles as $roleOption)
                                            <option value="{{ $roleOption->id }}">
                                                {{ $roleOption->name }}{{ $roleOption->business_id ? __('roles.role_custom_tag') : __('roles.role_preset_tag') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div
                                    class="rounded-[10px] bg-[#007AFF]/8 border border-[#007AFF]/15 p-3 text-[12px] text-[#007AFF] leading-relaxed">
                                    {{ __('roles.role_change_notice') }}
                                </div>

                                <div class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2.5">
                                    <button type="button" @click="showMemberRoleModal = false"
                                        class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                                        {{ __('roles.btn_cancel') }}
                                    </button>
                                    <button type="submit"
                                        class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                                        {{ __('roles.btn_save_changes') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </template>
        @endif

        @if (\App\Support\Context::hasPermission('roles.delete') || \App\Support\Context::hasPermission('roles.manage'))
            <!-- ===================================================== -->
            <!-- APPLE ALERT DIALOG: HAPUS ROLE                        -->
            <!-- ===================================================== -->
            <template x-teleport="body">
                <div x-show="deleteRoleModalOpen" x-cloak
                    class="fixed inset-0 z-[220] overflow-y-auto"
                    @keydown.escape.window="closeDeleteRole()"
                    role="alertdialog"
                    aria-modal="true"
                    aria-labelledby="delete-role-title">

                    <!-- Backdrop -->
                    <div x-show="deleteRoleModalOpen"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="closeDeleteRole()"
                        class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

                    <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="closeDeleteRole()">
                        <div class="relative w-[300px] rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl overflow-hidden text-center shadow-2xl border border-black/10 dark:border-white/10 my-auto"
                            @click.stop
                            x-show="deleteRoleModalOpen"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95">

                            <div class="px-5 pt-6 pb-4">
                                <div class="w-12 h-12 rounded-full bg-[#FF3B30]/12 text-[#FF3B30] flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="trash-2" class="w-6 h-6"></i>
                                </div>
                                <p id="delete-role-title" class="text-[17px] font-semibold text-black dark:text-white">{{ __('roles.dialog_delete_role_title') }}</p>
                                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                                    {!! __('roles.dialog_delete_role_desc', ['name' => '<span x-text="deleteRoleTarget.name" class="font-medium text-black dark:text-white"></span>']) !!}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                                <button type="button" @click="closeDeleteRole()"
                                    class="min-h-[44px] py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                    {{ __('roles.btn_cancel') }}
                                </button>
                                <button type="button" @click="submitDeleteRole()"
                                    class="min-h-[44px] py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                    {{ __('roles.btn_delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        @endif

        @if (\App\Support\Context::hasPermission('users.delete') || \App\Support\Context::hasPermission('users.manage'))
            <!-- ===================================================== -->
            <!-- APPLE ALERT DIALOG: KELUARKAN ANGGOTA                 -->
            <!-- ===================================================== -->
            <template x-teleport="body">
                <div x-show="deleteMemberModalOpen" x-cloak
                    class="fixed inset-0 z-[220] overflow-y-auto"
                    @keydown.escape.window="closeDeleteMember()"
                    role="alertdialog"
                    aria-modal="true"
                    aria-labelledby="delete-member-title">

                    <!-- Backdrop -->
                    <div x-show="deleteMemberModalOpen"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="closeDeleteMember()"
                        class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

                    <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="closeDeleteMember()">
                        <div class="relative w-[300px] rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl overflow-hidden text-center shadow-2xl border border-black/10 dark:border-white/10 my-auto"
                            @click.stop
                            x-show="deleteMemberModalOpen"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95">

                            <div class="px-5 pt-6 pb-4">
                                <div class="w-12 h-12 rounded-full bg-[#FF3B30]/12 text-[#FF3B30] flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="user-x" class="w-6 h-6"></i>
                                </div>
                                <p id="delete-member-title" class="text-[17px] font-semibold text-black dark:text-white">{{ __('roles.dialog_delete_member_title') }}</p>
                                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                                    {!! __('roles.dialog_delete_member_desc', ['name' => '<span x-text="deleteMemberTarget.name" class="font-medium text-black dark:text-white"></span>']) !!}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                                <button type="button" @click="closeDeleteMember()"
                                    class="min-h-[44px] py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                    {{ __('roles.btn_cancel') }}
                                </button>
                                <button type="button" @click="submitDeleteMember()"
                                    class="min-h-[44px] py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                    {{ __('roles.btn_remove') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        @endif

    </div>
@endsection
