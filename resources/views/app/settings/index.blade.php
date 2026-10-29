@extends('layouts.app', [
    'title' => __('settings.page_title'),
    'headerTitle' => __('settings.header_title'),
    'headerSubtitle' => __('settings.header_subtitle'),
])

@section('content')
    <!-- Leaflet.js Assets for Interactive Business & Branch Mapping -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .leaflet-container {
            font-family: inherit;
            z-index: 10 !important;
        }
        .custom-map-marker {
            background: transparent;
            border: none;
        }
    </style>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    @php
        $primaryBranchObj = $branches->firstWhere('is_primary', true) ?? $locations->firstWhere('is_primary', true);
        $defaultBranchLat = ($primaryBranchObj && !empty($primaryBranchObj->latitude)) ? (float) $primaryBranchObj->latitude : -6.2088;
        $defaultBranchLng = ($primaryBranchObj && !empty($primaryBranchObj->longitude)) ? (float) $primaryBranchObj->longitude : 106.8456;
    @endphp

    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="settingsPage()">

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR / PAGE HEADER (Apple HIG 3-Baris Standard) -->
        <!-- ===================================================== -->
        <header
            class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-6 space-y-4 shadow-sm">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div>
                    <!-- Overline Typographic (Anti-Pill) -->
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 mb-1">
                        {{ __('settings.breadcrumb_settings') }} &bull; {{ __('settings.breadcrumb_profile') }}
                    </p>
                    <h1 class="text-2xl sm:text-3xl font-bold text-black dark:text-white tracking-tight">
                        {{ __('settings.header_title') }}
                    </h1>
                    <p class="text-[13px] text-black/55 dark:text-white/55 mt-1 leading-relaxed max-w-3xl">
                        {{ __('settings.header_desc') }}
                    </p>
                </div>
            </div>

            <!-- Apple-Style Segmented Controls Switcher (Deep-Linking Active) -->
            <div class="pt-2 border-t border-black/5 dark:border-white/5">
                <div
                    class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-[13px] font-medium self-stretch sm:self-auto overflow-x-auto no-scrollbar max-w-full gap-1">
                    <button type="button" @click="activeTab = 'general'"
                        :class="activeTab === 'general' ?
                            'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' :
                            'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="min-h-[40px] px-4 py-2 rounded-[9px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.98]">
                        <i data-lucide="building-2" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>{{ __('settings.tab_general') }}</span>
                    </button>

                    <button type="button" @click="activeTab = 'branches'"
                        :class="activeTab === 'branches' ?
                            'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' :
                            'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="min-h-[40px] px-4 py-2 rounded-[9px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.98]">
                        <i data-lucide="store" class="w-4 h-4 text-[#34C759]"></i>
                        <span>{{ __('settings.tab_branches') }}</span>
                        <span
                            class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] tabular-nums">{{ $branches->count() }}</span>
                    </button>

                    <button type="button" @click="activeTab = 'operations'"
                        :class="activeTab === 'operations' ?
                            'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' :
                            'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="min-h-[40px] px-4 py-2 rounded-[9px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.98]">
                        <i data-lucide="clock" class="w-4 h-4 text-[#FF9500]"></i>
                        <span>{{ __('settings.tab_operations') }}</span>
                    </button>

                    <button type="button" @click="activeTab = 'whatsapp'"
                        :class="(activeTab === 'whatsapp' || activeTab === 'wa_receipt') ?
                            'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' :
                            'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="min-h-[40px] px-4 py-2 rounded-[9px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.98]">
                        <i data-lucide="message-square" class="w-4 h-4 text-[#34C759]"></i>
                        <span>{{ __('settings.tab_whatsapp') }}</span>
                        @if (isset($whatsappAccount) && $whatsappAccount && $whatsappAccount->isActive())
                            <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                        @endif
                    </button>

                    @if ($business->isModuleEnabled('channels_marketing'))
                        <button type="button" @click="activeTab = 'social_media'"
                            :class="(activeTab === 'social_media' || activeTab === 'social' || activeTab === 'channels') ?
                                'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' :
                                'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                            class="min-h-[40px] px-4 py-2 rounded-[9px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.98]">
                            <i data-lucide="share-2" class="w-4 h-4 text-[#FF2D55]"></i>
                            <span>{{ __('settings.tab_social_media') }}</span>
                            @if (isset($socialAccounts) && $socialAccounts->where('status', 'active')->count() > 0)
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] tabular-nums">
                                    {{ $socialAccounts->where('status', 'active')->count() }}
                                </span>
                            @endif
                        </button>
                    @endif

                    <button type="button" @click="activeTab = 'templates'"
                        :class="activeTab === 'templates' ?
                            'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' :
                            'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="min-h-[40px] px-4 py-2 rounded-[9px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.98]">
                        <i data-lucide="layers" class="w-4 h-4 text-[#AF52DE]"></i>
                        <span>{{ __('settings.tab_templates') }}</span>
                        <span
                            class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-black/10 dark:bg-white/10 tabular-nums">{{ $templates->count() }}</span>
                    </button>

                    <button type="button" @click="activeTab = 'modules'"
                        :class="activeTab === 'modules' ?
                            'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' :
                            'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="min-h-[40px] px-4 py-2 rounded-[9px] transition-all flex items-center justify-center gap-2 shrink-0 active:scale-[0.98]">
                        <i data-lucide="layout-grid" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>{{ __('settings.tab_modules') }}</span>
                        <span
                            class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#007AFF]/15 text-[#007AFF] tabular-nums"
                            x-text="activeModulesCount + '/' + totalModulesCount">
                            {{ count($allModules) - count($business->disabled_modules ?? []) }}/{{ count($allModules) }}
                        </span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Governance & Security Fast Links (Horizontal Snap Slider on Mobile, Grid on Tablet/Desktop) -->
        <div class="flex gap-3 overflow-x-auto snap-x snap-mandatory no-scrollbar -mx-4 px-4 pb-1 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:gap-3.5 sm:overflow-visible sm:mx-0 sm:px-0 sm:pb-0">
            <a href="{{ route('settings.audit-logs.index') }}"
                class="snap-start shrink-0 w-[260px] sm:w-auto p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 hover:border-[#FF3B30]/30 transition-all flex items-center justify-between group shadow-sm active:scale-[0.99]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center shrink-0">
                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-black dark:text-white group-hover:text-[#FF3B30] transition-colors">{{ __('settings.link_audit_logs_title') }}</p>
                        <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('settings.link_audit_logs_desc') }}</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 dark:text-white/30 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
            <a href="{{ route('approval-rules.index') }}"
                class="snap-start shrink-0 w-[260px] sm:w-auto p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 hover:border-[#007AFF]/30 transition-all flex items-center justify-between group shadow-sm active:scale-[0.99]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-black dark:text-white group-hover:text-[#007AFF] transition-colors">{{ __('settings.link_approval_rules_title') }}</p>
                        <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('settings.link_approval_rules_desc') }}</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 dark:text-white/30 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
            <a href="{{ route('roles.index') }}"
                class="snap-start shrink-0 w-[260px] sm:w-auto p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 hover:border-[#34C759]/30 transition-all flex items-center justify-between group shadow-sm active:scale-[0.99]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-black dark:text-white group-hover:text-[#34C759] transition-colors">{{ __('settings.link_roles_title') }}</p>
                        <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('settings.link_roles_desc') }}</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 dark:text-white/30 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
            <a href="{{ route('settings.integrations.mcp.index') }}"
                class="snap-start shrink-0 w-[260px] sm:w-auto p-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 hover:border-[#AF52DE]/30 transition-all flex items-center justify-between group shadow-sm active:scale-[0.99]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[10px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center shrink-0">
                        <i data-lucide="bot" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-black dark:text-white group-hover:text-[#AF52DE] transition-colors">{{ __('settings.mcp_nav_title') }}</p>
                        <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('settings.mcp_nav_sub') }}</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 dark:text-white/30 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
        </div>

        <!-- ===================================================== -->
        <!-- TAB 1: PROFIL BISNIS, LOGO, BANK & PEMBULATAN        -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'general'" class="space-y-6">
            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="_tab" value="general">

                <!-- Section 1: Logo & Identitas Kop Surat -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4 border-b border-black/5 dark:border-white/5 pb-4">
                        <div>
                            <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.company_identity') }}</h2>
                            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('settings.company_identity_desc') }}</p>
                        </div>
                        <span
                            class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                            {{ __('settings.official_letterhead') }}
                        </span>
                    </div>

                    <!-- Logo Section -->
                    <div
                        class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 flex flex-col sm:flex-row items-center gap-5">
                        <div
                            class="w-24 h-24 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 flex items-center justify-center overflow-hidden shrink-0 shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" alt="Logo Bisnis" class="w-full h-full object-contain p-2">
                            </template>
                            <template x-if="!logoPreview">
                                <div class="text-center p-2 text-black/30 dark:text-white/30">
                                    <svg class="w-8 h-8 mx-auto mb-1 stroke-current" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                    <span class="text-[10px] font-medium block">{{ __('settings.no_logo_yet') }}</span>
                                </div>
                            </template>
                        </div>

                        <div class="flex-1 space-y-2 text-left w-full">
                            <label for="biz_logo_input"
                                class="block text-[13px] font-semibold text-black dark:text-white">{{ __('settings.upload_logo') }}</label>
                            <input type="file" name="logo" id="biz_logo_input" accept="image/*"
                                @change="handleLogoChange($event)"
                                class="block w-full text-[13px] text-black/60 dark:text-white/60 file:mr-3 file:py-2 file:px-3 file:rounded-[8px] file:border-0 file:text-[12px] file:font-semibold file:bg-[#007AFF] file:text-white hover:file:bg-[#0071E3] active:file:scale-[0.97] file:transition file:cursor-pointer cursor-pointer">
                            <p class="text-[12px] text-black/40 dark:text-white/40">
                                {{ __('settings.upload_logo_hint') }}
                            </p>

                            @if ($business->logo_path)
                                <label
                                    class="inline-flex items-center gap-2 mt-1.5 cursor-pointer select-none text-[13px] text-[#FF3B30] font-medium">
                                    <input type="checkbox" name="remove_logo" value="1"
                                        class="rounded-[4px] border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]">
                                    <span>{{ __('settings.remove_current_logo') }}</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    <!-- Input Grid: Nama & NPWP -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="biz_name_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                {{ __('settings.business_name') }} <span class="text-[#FF3B30]">*</span>
                            </label>
                            <input type="text" id="biz_name_input" name="name"
                                value="{{ old('name', $business->name) }}" required
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <p class="mt-1 text-[11px] text-black/40 dark:text-white/40">{{ __('settings.business_name_hint') }}</p>
                        </div>
                        <div>
                            <label for="biz_tax_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                {{ __('settings.tax_id') }}
                            </label>
                            <input type="text" id="biz_tax_input" name="tax_identification_number"
                                value="{{ old('tax_identification_number', $business->tax_identification_number) }}"
                                placeholder="01.234.567.8-901.000"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <!-- Input Grid: Phone & Email -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="biz_phone_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                {{ __('settings.phone') }}
                            </label>
                            <input type="text" id="biz_phone_input" name="phone"
                                value="{{ old('phone', $business->phone) }}" placeholder="0812-3456-7890"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label for="biz_email_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                {{ __('settings.email') }}
                            </label>
                            <input type="email" id="biz_email_input" name="email"
                                value="{{ old('email', $business->email) }}" placeholder="finance@perusahaan.com"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="biz_address_input"
                            class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            {{ __('settings.address') }}
                        </label>
                        <textarea id="biz_address_input" name="address" rows="2"
                            placeholder="Jl. Sudirman No. 45, Gedung Cyber Lt. 5, Jakarta..."
                            class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[16px] sm:text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none">{{ old('address', $business->address) }}</textarea>
                    </div>
                </div>

                <!-- Section 2: Konfigurasi POS & Pajak (Auto-Hidden if POS Module Disabled) -->
                @if ($business->isModuleEnabled('pos_retail') || $business->isModuleEnabled('pos_dinein'))
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                        <div class="border-b border-black/5 dark:border-white/5 pb-3">
                            <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.pos_config') }}</h2>
                            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('settings.pos_config_desc') }}</p>
                        </div>

                        <div
                            class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 divide-y divide-black/5 dark:divide-white/5 overflow-hidden">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 gap-3">
                                <div>
                                    <p class="text-[15px] font-medium text-black dark:text-white">{{ __('settings.sales_tax') }}</p>
                                    <p class="text-[13px] text-black/50 dark:text-white/50">{{ __('settings.sales_tax_desc') }}</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <input type="hidden" name="pos_enable_tax" value="0">
                                    <!-- Apple iOS Toggle Switch -->
                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" name="pos_enable_tax" value="1"
                                            {{ $business->pos_enable_tax ? 'checked' : '' }} class="sr-only peer">
                                        <div
                                            class="w-11 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm peer-checked:bg-[#34C759]">
                                        </div>
                                    </label>
                                    <div class="flex items-center gap-1.5">
                                        <input type="number" name="pos_tax_percent"
                                            value="{{ old('pos_tax_percent', $business->pos_tax_percent ?? 0) }}"
                                            min="0" max="100" step="0.01"
                                            class="w-20 h-10 text-right bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-[15px] sm:text-[13px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                        <span class="text-[13px] font-semibold text-black/50 dark:text-white/50">%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4">
                                <div>
                                    <p class="text-[15px] font-medium text-black dark:text-white">{{ __('settings.pos_product_photos') }}</p>
                                    <p class="text-[13px] text-black/50 dark:text-white/50">{{ __('settings.pos_product_photos_desc') }}</p>
                                </div>
                                <input type="hidden" name="pos_show_product_images" value="0">
                                <!-- Apple iOS Toggle Switch -->
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" name="pos_show_product_images" value="1"
                                        {{ $business->pos_show_product_images ? 'checked' : '' }} class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm peer-checked:bg-[#007AFF]">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Section 3: Rekening Bank Resmi -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                    <div class="border-b border-black/5 dark:border-white/5 pb-3">
                        <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.bank_instructions') }}</h2>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('settings.bank_instructions_desc') }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="bank_name_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('settings.bank_name') }}</label>
                            <input type="text" id="bank_name_input" name="bank_name"
                                value="{{ old('bank_name', $business->bank_name) }}"
                                placeholder="BCA / Mandiri / BNI / BRI"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label for="bank_acc_num_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('settings.bank_account_number') }}</label>
                            <input type="text" id="bank_acc_num_input" name="bank_account_number"
                                value="{{ old('bank_account_number', $business->bank_account_number) }}"
                                placeholder="123-456-7890"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white tabular-nums placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label for="bank_holder_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('settings.bank_account_holder') }}</label>
                            <input type="text" id="bank_holder_input" name="bank_account_holder"
                                value="{{ old('bank_account_holder', $business->bank_account_holder) }}"
                                placeholder="PT Usaha Bersama"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>
                </div>

                <!-- Section 4: Mata Uang & Pembulatan HPP -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                    <div class="border-b border-black/5 dark:border-white/5 pb-3">
                        <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.currency_rounding') }}</h2>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('settings.currency_rounding_desc') }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="currency_code_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('settings.currency_code') }}</label>
                            <input type="text" id="currency_code_input" name="currency_code"
                                value="{{ old('currency_code', $business->currency_code) }}" required
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white tabular-nums uppercase focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <div>
                            <label for="currency_symbol_input"
                                class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('settings.currency_symbol') }}</label>
                            <input type="text" id="currency_symbol_input" name="currency_symbol"
                                value="{{ old('currency_symbol', $business->currency_symbol) }}" required
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <div class="space-y-2 pt-2">
                        <label for="rounding_strategy_select"
                            class="block text-[13px] font-medium text-black/70 dark:text-white/70">{{ __('settings.rounding_strategy') }}</label>
                        <select id="rounding_strategy_select" name="rounding_strategy" required
                            x-model="roundingStrategy"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                            <option value="ROUND">{{ __('settings.opt_round_nearest') }}</option>
                            <option value="CEIL">{{ __('settings.opt_ceil_up') }}</option>
                            <option value="FLOOR">{{ __('settings.opt_floor_down') }}</option>
                            <option value="ROUND_50">{{ __('settings.opt_round_50') }}</option>
                            <option value="ROUND_100">{{ __('settings.opt_round_100') }}</option>
                            <option value="ROUND_500">{{ __('settings.opt_round_500') }}</option>
                            <option value="ROUND_1000">{{ __('settings.opt_round_1000') }}</option>
                        </select>

                        <div
                            class="rounded-[10px] bg-[#007AFF]/8 border border-[#007AFF]/15 p-3 text-[13px] text-[#007AFF] font-medium flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            <span>{{ __('settings.rounding_simulation') }}: <strong class="tabular-nums font-semibold"
                                    x-text="getRoundingExample(roundingStrategy)"></strong></span>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Keamanan Kasir & PIN Otorisasi Supervisor (Auto-Hidden if POS Module Disabled) -->
                @if ($business->isModuleEnabled('pos_retail') || $business->isModuleEnabled('pos_dinein'))
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4 border-b border-black/5 dark:border-white/5 pb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.pos_security') }}</h2>
                                    @if (!empty($business->pos_supervisor_pin))
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                            {{ __('settings.pin_status_custom') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/15 text-[#C97700] dark:text-[#FF9F0A]">
                                            {{ __('settings.pin_status_default') }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('settings.pos_security_desc') }}</p>
                            </div>
                            <span
                                class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#AF52DE]/10 text-[#AF52DE]">
                                {{ __('settings.badge_pos_security') }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- PIN Input -->
                            <div>
                                <label for="pos_supervisor_pin_input"
                                    class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                    {{ __('settings.pos_supervisor_pin') }}
                                </label>
                                <input type="password" id="pos_supervisor_pin_input" name="pos_supervisor_pin"
                                    inputmode="numeric" pattern="[0-9]*" maxlength="8"
                                    placeholder="{{ !empty($business->pos_supervisor_pin) ? __('settings.pin_placeholder_masked') : __('settings.pin_placeholder_empty') }}"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[15px] font-mono tracking-widest text-black dark:text-white placeholder:font-sans placeholder:tracking-normal placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <p class="mt-1 text-[11px] text-black/40 dark:text-white/40">{{ __('settings.pos_supervisor_pin_hint') }}</p>
                            </div>

                            <!-- Max Cashier Discount -->
                            <div>
                                <label for="pos_max_discount_input"
                                    class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                    {{ __('settings.pos_max_cashier_discount') }}
                                </label>
                                <div class="relative">
                                    <input type="number" step="0.5" min="0" max="100"
                                        id="pos_max_discount_input" name="pos_max_cashier_discount_percent"
                                        value="{{ old('pos_max_cashier_discount_percent', $business->pos_max_cashier_discount_percent) }}"
                                        placeholder="10"
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-3.5 pr-8 text-[16px] sm:text-[15px] tabular-nums text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <span
                                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[13px] font-semibold text-black/40 dark:text-white/40">%</span>
                                </div>
                                <p class="mt-1 text-[11px] text-black/40 dark:text-white/40">{{ __('settings.pos_max_cashier_discount_hint') }}</p>
                            </div>
                        </div>

                        <!-- Toggles for Void, Refund & Auto-KDS Routing -->
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <label
                                class="flex items-start gap-3 p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 cursor-pointer hover:bg-black/[0.04] dark:hover:bg-white/[0.05] transition min-h-[48px]">
                                <input type="checkbox" name="pos_require_pin_for_void" value="1"
                                    {{ old('pos_require_pin_for_void', $business->pos_require_pin_for_void) ? 'checked' : '' }}
                                    class="mt-0.5 rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                <div class="text-[13px]">
                                    <span class="font-medium text-black dark:text-white block">{{ __('settings.pos_require_pin_void') }}</span>
                                    <span class="text-[11px] text-black/50 dark:text-white/50 block mt-0.5">{{ __('settings.pos_require_pin_void_desc') }}</span>
                                </div>
                            </label>

                            <label
                                class="flex items-start gap-3 p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 cursor-pointer hover:bg-black/[0.04] dark:hover:bg-white/[0.05] transition min-h-[48px]">
                                <input type="checkbox" name="pos_require_pin_for_refund" value="1"
                                    {{ old('pos_require_pin_for_refund', $business->pos_require_pin_for_refund) ? 'checked' : '' }}
                                    class="mt-0.5 rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                <div class="text-[13px]">
                                    <span class="font-medium text-black dark:text-white block">{{ __('settings.pos_require_pin_refund') }}</span>
                                    <span class="text-[11px] text-black/50 dark:text-white/50 block mt-0.5">{{ __('settings.pos_require_pin_refund_desc') }}</span>
                                </div>
                            </label>

                            @if ($business && ($business->hasDineInFeature() || $business->isModuleEnabled('pos_dinein')))
                            <label
                                class="flex items-start gap-3 p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 cursor-pointer hover:bg-black/[0.04] dark:hover:bg-white/[0.05] transition min-h-[48px]">
                                <input type="checkbox" name="pos_auto_send_kds" value="1"
                                    {{ old('pos_auto_send_kds', $business->pos_auto_send_kds) ? 'checked' : '' }}
                                    class="mt-0.5 rounded-[4px] border-black/20 text-[#34C759] focus:ring-[#34C759]">
                                <div class="text-[13px]">
                                    <span class="font-medium text-black dark:text-white flex items-center gap-1.5">
                                        <span>{{ __('settings.opt_auto_send_kds') }}</span>
                                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] uppercase">{{ __('settings.badge_paperless') }}</span>
                                    </span>
                                    <span class="text-[11px] text-black/50 dark:text-white/50 block mt-0.5">{{ __('settings.desc_auto_send_kds') }}</span>
                                </div>
                            </label>
                            @endif
                        </div>
                    </div>
                @endif

                @if (\App\Support\Context::hasPermission('settings.edit'))
                    <div class="flex items-center justify-end pt-2">
                        <button type="submit"
                            class="w-full sm:w-auto h-11 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)] min-h-[44px]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>{{ __('settings.save_business_profile') }}</span>
                        </button>
                    </div>
                @endif
            </form>
        </div>

        <!-- ===================================================== -->
        <!-- TAB 2: MANAJEMEN CABANG & TOKO (MULTI-OUTLET HUB)     -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'branches'" class="space-y-6" style="display: none;">
            {{-- Header Card --}}
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="w-11 h-11 rounded-[14px] bg-[#34C759]/12 text-[#34C759] flex items-center justify-center shrink-0 border border-[#34C759]/20">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-[17px] font-semibold text-black dark:text-white flex items-center gap-2">
                            <span>{{ __('settings.branch_management_title') }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] tabular-nums">
                                {{ $branches->count() }}
                            </span>
                        </h2>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5 leading-relaxed">
                            {{ __('settings.branch_management_desc') }}
                        </p>
                    </div>
                </div>

                @if (\App\Support\Context::hasPermission('settings.edit') || \App\Support\Context::isAdminOrOwner())
                    <button type="button" @click="openCreateBranch()"
                            class="w-full sm:w-auto h-11 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-[0_2px_4px_rgba(52,199,89,0.25)] shrink-0 cursor-pointer min-h-[44px]">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('settings.add_branch_button') }}</span>
                    </button>
                @endif
            </div>

            {{-- Info Callout: Relasi 1 Cabang Banyak Gudang --}}
            <div class="rounded-[16px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 p-4.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="info" class="w-4.5 h-4.5"></i>
                    </div>
                    <div class="text-[12px] leading-relaxed text-slate-700 dark:text-slate-300">
                        <strong class="font-semibold text-slate-900 dark:text-white">{{ __('settings.lbl_multi_warehouse_arch') }}</strong>
                        {{ __('settings.desc_multi_warehouse_arch') }} <a href="{{ route('warehouse.index') }}" class="text-[#007AFF] font-bold hover:underline">{{ __('settings.lbl_warehouse_and_logistics') }}</a>.
                    </div>
                </div>
                <a href="{{ route('warehouse.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[12px] font-semibold text-[#007AFF] hover:bg-black/[0.03] dark:hover:bg-white/[0.05] transition-all shrink-0">
                    <i data-lucide="warehouse" class="w-3.5 h-3.5"></i>
                    <span>{{ __('settings.btn_open_warehouse_hub') }}</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            {{-- Bento Grid Daftar Cabang --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($branches as $branch)
                    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between space-y-4 shadow-sm hover:shadow-md transition-all">
                        {{-- Top Card: Header & Badges --}}
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-11 h-11 rounded-[14px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0 border border-[#34C759]/20">
                                        <i data-lucide="store" class="w-5 h-5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-[15px] font-bold text-black dark:text-white truncate">
                                                {{ $branch->name }}
                                            </h3>
                                            @if ($branch->is_primary)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#FBBF24] border border-[#FF9500]/30">
                                                    <i data-lucide="building" class="w-3 h-3"></i>
                                                    <span>{{ __('settings.branch_primary_badge') }}</span>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5 text-[11px] text-black/50 dark:text-white/50">
                                            @if($branch->code)
                                                <span class="font-mono font-bold">{{ $branch->code }}</span>
                                                <span>&bull;</span>
                                            @endif
                                            <span>{{ __('settings.branch_type_outlet') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Status Pill --}}
                                <div>
                                    @if($branch->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                            <span>{{ __('settings.branch_active_badge') }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-black/30 dark:bg-white/30"></span>
                                            <span>{{ __('settings.branch_inactive_badge') }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Alamat & Kontak --}}
                            <div class="mt-3.5 space-y-1.5 text-[12px] text-black/70 dark:text-white/70">
                                <div class="flex items-start gap-2">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 shrink-0 mt-0.5"></i>
                                    <span class="line-clamp-2">{{ $branch->address ?: 'Alamat belum diisi' }}</span>
                                </div>
                                @if($branch->phone)
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="phone" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 shrink-0"></i>
                                        <span class="font-mono">{{ $branch->phone }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Section Metadata Bento --}}
                            <div class="mt-3.5 pt-3 border-t border-black/5 dark:border-white/5 grid grid-cols-2 gap-2 text-[11px]">
                                <div class="p-2 rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03]">
                                    <span class="text-black/40 dark:text-white/40 block">{{ __('settings.lbl_attendance_geofence') }}</span>
                                    <span class="font-semibold text-black dark:text-white tabular-nums">{{ $branch->geofence_radius_meters ?? 100 }} m</span>
                                </div>
                                <div class="p-2 rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03]">
                                    <span class="text-black/40 dark:text-white/40 block">{{ __('settings.lbl_active_timezone') }}</span>
                                    <span class="font-semibold text-black dark:text-white truncate block">{{ $branch->getTimezone() }}</span>
                                </div>
                            </div>

                            {{-- Connected Warehouses Bento Tile --}}
                            <div class="mt-3 p-2.5 rounded-[10px] bg-[#007AFF]/[0.04] dark:bg-[#007AFF]/[0.08] border border-[#007AFF]/15 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-[6px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                                        <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[12px] font-bold text-black dark:text-white block tabular-nums">
                                            {{ __('settings.lbl_connected_warehouses_count', ['count' => $branch->children->count()]) }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('warehouse.index') }}?parent_id={{ $branch->id }}"
                                   class="text-[11px] font-semibold text-[#007AFF] hover:underline inline-flex items-center gap-0.5 shrink-0">
                                    <span>{{ __('settings.view_branch_warehouses') }}</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>

                        {{-- Footer Actions --}}
                        <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-2">
                            @if (\App\Support\Context::hasPermission('settings.edit') || \App\Support\Context::isAdminOrOwner())
                                <button type="button" @click="openEditBranch('{{ $branch->id }}')"
                                        class="flex-1 min-h-[40px] px-3.5 rounded-[10px] text-[12.5px] font-semibold text-black/80 dark:text-white/80 bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('settings.edit_branch') }}</span>
                                </button>
                                @if(!$branch->is_primary)
                                    <button type="button" @click="openDeleteBranch('{{ $branch->id }}')"
                                            class="w-10 h-10 rounded-[10px] text-[#FF3B30] hover:bg-[#FF3B30]/10 active:scale-[0.98] transition-all flex items-center justify-center shrink-0 cursor-pointer"
                                            title="{{ __('settings.delete_branch') }}"
                                            aria-label="{{ __('settings.delete_branch') }}: {{ $branch->name }}">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 text-center space-y-3">
                        <div class="w-12 h-12 rounded-[16px] bg-black/5 dark:bg-white/5 text-black/30 dark:text-white/30 flex items-center justify-center mx-auto">
                            <i data-lucide="store" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-[15px] font-bold text-black dark:text-white">{{ __('settings.no_branches_found') }}</h4>
                        <p class="text-[12px] text-black/50 dark:text-white/50 max-w-sm mx-auto">{{ __('settings.no_branches_found_desc') }}</p>
                        @if (\App\Support\Context::hasPermission('settings.edit') || \App\Support\Context::isAdminOrOwner())
                            <button type="button" @click="openCreateBranch()"
                                    class="h-10 px-4 rounded-[10px] text-[12px] font-bold text-white bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] transition-all inline-flex items-center gap-1.5">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>{{ __('settings.add_branch_button') }}</span>
                            </button>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- TAB: OPERASIONAL, TIMEZONE & JAM KERJA                -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'operations'" class="space-y-6" style="display: none;">
            <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="_tab" value="operations">
                <input type="hidden" name="operating_hours_json" :value="JSON.stringify(operatingHours)">

                <!-- 1. Header Card & Timezone Configuration -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-6 shadow-sm">
                    <div class="border-b border-black/5 dark:border-white/5 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                                <i data-lucide="clock" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">{{ __('settings.timezone_title') }}</h3>
                                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('settings.timezone_desc') }}</p>
                            </div>
                        </div>

                        <!-- Live Digital Clock Badge -->
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-[12px] font-medium text-black/70 dark:text-white/70">
                            <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                            <span>{{ __('settings.business_time') }}:</span>
                            <span class="font-semibold text-black dark:text-white tabular-nums">{{ \App\Support\TimezoneHelper::formatLocal(now(), $currentTimezone, 'H:i:s') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <div>
                            <label for="business_timezone" class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                                {{ __('settings.base_timezone') }} <span class="text-[#FF3B30]">*</span>
                            </label>
                            <div class="relative">
                                <select id="business_timezone" name="timezone" x-model="selectedTimezone"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                                    @foreach ($timezones as $groupLabel => $zones)
                                        <optgroup label="{{ $groupLabel }}">
                                            @foreach ($zones as $zoneId => $zoneName)
                                                <option value="{{ $zoneId }}" {{ $currentTimezone === $zoneId ? 'selected' : '' }}>
                                                    {{ $zoneName }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1.5 text-[11px] text-black/45 dark:text-white/45 leading-relaxed">
                                {{ __('settings.base_timezone_hint') }}
                            </p>
                        </div>

                        <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                            <div class="flex items-center gap-2 text-[12px] font-semibold text-black dark:text-white">
                                <i data-lucide="info" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>{{ __('settings.single_source_truth') }}</span>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                                {{ __('settings.single_source_truth_desc') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 2. Weekly Operating Hours Bento Card -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-6 shadow-sm">
                    <div class="border-b border-black/5 dark:border-white/5 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">{{ __('settings.weekly_schedule') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('settings.weekly_schedule_desc') }}</p>
                        </div>
                        <div class="text-[11px] font-medium text-black/40 dark:text-white/40">
                            {{ __('settings.format_24h') }}
                        </div>
                    </div>

                    <div class="space-y-3.5">
                        <template x-for="(dayData, dayKey) in operatingHours" :key="dayKey">
                            <div class="p-4 rounded-[14px] border transition-all"
                                :class="dayData.is_open ? 'bg-white dark:bg-[#242426] border-black/10 dark:border-white/10 shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/5 dark:border-white/5 opacity-75'">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-3">
                                        <label class="relative inline-flex items-center cursor-pointer min-h-[44px]">
                                            <input type="checkbox" x-model="dayData.is_open" class="sr-only peer">
                                            <div class="w-10 h-6 bg-black/20 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                                        </label>
                                        <span class="text-[14px] font-semibold text-black dark:text-white w-24" x-text="dayData.day_name"></span>
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium"
                                            :class="dayData.is_open ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50'"
                                            x-text="dayData.is_open ? '{{ __('settings.open') }}' : '{{ __('settings.closed') }}'">
                                        </span>
                                    </div>

                                    <template x-if="dayData.is_open">
                                        <button type="button" @click="addPeriod(dayKey)"
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition active:scale-95 min-h-[36px]">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('settings.add_shift') }}</span>
                                        </button>
                                    </template>
                                </div>

                                <template x-if="!dayData.is_open">
                                    <p class="text-[12px] text-black/40 dark:text-white/40 italic pl-0 sm:pl-12">
                                        {{ __('settings.shop_closed_all_day') }}
                                    </p>
                                </template>

                                <template x-if="dayData.is_open">
                                    <div class="space-y-2.5 pl-0 sm:pl-12">
                                        <template x-for="(period, pIndex) in dayData.periods" :key="pIndex">
                                            <div class="flex flex-wrap items-center gap-2.5 p-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04]">
                                                <span class="text-[12px] font-medium text-black/50 dark:text-white/50 w-16" x-text="'Sesi ' + (pIndex + 1) + ':'"></span>
                                                
                                                <div class="flex items-center gap-2">
                                                    <input type="time" x-model="period.start" required
                                                        class="h-10 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-[15px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                                    <span class="text-[12px] text-black/40 dark:text-white/40">{{ __('settings.to') }}</span>
                                                    <input type="time" x-model="period.end" required
                                                        class="h-10 px-2.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-[15px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                                </div>

                                                <!-- Overnight Indicator Badge -->
                                                <template x-if="isOvernight(period)">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#AF52DE]/10 text-[#AF52DE]">
                                                        <i data-lucide="moon" class="w-3 h-3"></i>
                                                        <span>{{ __('settings.overnight') }}</span>
                                                    </span>
                                                </template>

                                                <template x-if="dayData.periods.length > 1">
                                                    <button type="button" @click="removePeriod(dayKey, pIndex)"
                                                        class="p-2 rounded-[8px] text-black/40 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 transition ml-auto min-w-[36px] min-h-[36px] flex items-center justify-center">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 3. Branch Outlets Timezone & Operating Hours Status -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 gap-2">
                        <div>
                            <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">{{ __('settings.branch_operations_status') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('settings.branch_operations_desc') }}</p>
                        </div>
                        @if (\App\Support\Context::hasPermission('warehouse.view') || \App\Support\Context::isAdminOrOwner())
                            <a href="{{ route('warehouse.index') }}" class="text-[12px] font-semibold text-[#007AFF] hover:underline inline-flex items-center gap-1 min-h-[36px]">
                                <span>{{ __('settings.branch_management') }}</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>

                    <!-- Desktop Table (md and up) -->
                    <div class="hidden md:block overflow-x-auto no-scrollbar">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider border-b border-black/5 dark:border-white/5">
                                    <th class="py-2.5 pr-4">{{ __('settings.branch_name') }}</th>
                                    <th class="py-2.5 px-4">{{ __('settings.branch_type') }}</th>
                                    <th class="py-2.5 px-4">{{ __('settings.branch_timezone') }}</th>
                                    <th class="py-2.5 px-4">{{ __('settings.branch_schedule_mode') }}</th>
                                    <th class="py-2.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('settings.action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/5 dark:divide-white/5">
                                @forelse ($locations as $loc)
                                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                        <td class="py-3 pr-4 font-semibold text-black dark:text-white">
                                            {{ $loc->name }}
                                            @if ($loc->is_primary)
                                                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">{{ __('settings.primary_outlet') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-black/60 dark:text-white/60 capitalize">
                                            {{ $loc->type }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-medium text-black dark:text-white tabular-nums">{{ $loc->getTimezone() }}</span>
                                                @if ($loc->timezone_mode === 'custom' && !empty($loc->timezone))
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#FF9500]/10 text-[#FF9500]">{{ __('settings.custom_mode') }}</span>
                                                @else
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">{{ __('settings.follow_center') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if ($loc->operating_hours_mode === 'custom' && !empty($loc->operating_hours))
                                                <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#FF9500]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                                    <span>{{ __('settings.custom_branch_schedule') }}</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#34C759]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                    <span>{{ __('settings.follow_center_schedule') }}</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                            <div class="flex items-center justify-end">
                                                @if (\App\Support\Context::hasPermission('warehouse.manage') || \App\Support\Context::isAdminOrOwner())
                                                    <a href="{{ route('warehouse.edit', $loc) }}"
                                                        class="w-8 h-8 rounded-[8px] flex items-center justify-center text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/10 transition-all cursor-pointer shrink-0"
                                                        title="{{ __('settings.change_schedule') }}"
                                                        aria-label="{{ __('settings.change_schedule') }}">
                                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                                    </a>
                                                @elseif (\App\Support\Context::hasPermission('warehouse.view'))
                                                    <a href="{{ route('warehouse.show', $loc) }}"
                                                        class="w-8 h-8 rounded-[8px] flex items-center justify-center text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/10 transition-all cursor-pointer shrink-0"
                                                        title="{{ __('settings.view_details') }}"
                                                        aria-label="{{ __('settings.view_details') }}">
                                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                                    </a>
                                                @else
                                                    <span class="text-[12px] text-black/40 dark:text-white/40">-</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-black/40 dark:text-white/40 italic">
                                            {{ __('settings.no_branches_yet') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Responsive Cards (md:hidden) -->
                    <div class="md:hidden space-y-3">
                        @forelse ($locations as $loc)
                            <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h4 class="text-[14px] font-semibold text-black dark:text-white">{{ $loc->name }}</h4>
                                        <span class="text-[11px] text-black/50 dark:text-white/50 capitalize">{{ $loc->type }}</span>
                                    </div>
                                    @if ($loc->is_primary)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">{{ __('settings.primary_outlet') }}</span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-[12px] pt-1 border-t border-black/5 dark:border-white/5">
                                    <div>
                                        <span class="text-[11px] text-black/40 dark:text-white/40 block">{{ __('settings.branch_timezone') }}</span>
                                        <span class="font-medium text-black dark:text-white tabular-nums">{{ $loc->getTimezone() }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[11px] text-black/40 dark:text-white/40 block">{{ __('settings.branch_schedule_mode') }}</span>
                                        <span class="font-medium {{ $loc->operating_hours_mode === 'custom' ? 'text-[#FF9500]' : 'text-[#34C759]' }}">
                                            {{ $loc->operating_hours_mode === 'custom' ? __('settings.custom_mode') : __('settings.follow_center') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-black/5 dark:border-white/5">
                                    @if (\App\Support\Context::hasPermission('warehouse.manage') || \App\Support\Context::isAdminOrOwner())
                                        <a href="{{ route('warehouse.edit', $loc) }}"
                                            class="w-full h-11 flex items-center justify-center rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 active:scale-[0.98] transition">
                                            {{ __('settings.change_schedule') }}
                                        </a>
                                    @elseif (\App\Support\Context::hasPermission('warehouse.view'))
                                        <a href="{{ route('warehouse.show', $loc) }}"
                                            class="w-full h-11 flex items-center justify-center rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 active:scale-[0.98] transition">
                                            {{ __('settings.view_details') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-[13px] text-black/40 dark:text-white/40 italic">
                                {{ __('settings.no_branches_yet') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                @if (\App\Support\Context::hasPermission('settings.edit'))
                    <div class="flex items-center justify-end pt-2">
                        <button type="submit"
                            class="w-full sm:w-auto h-11 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)] min-h-[44px]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>{{ __('settings.save_operations') }}</span>
                        </button>
                    </div>
                @endif
            </form>
        </div>

        <!-- ===================================================== -->
        <!-- TAB: WHATSAPP CLOUD API & STRUK SETTINGS              -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'whatsapp' || activeTab === 'wa_receipt'" class="space-y-6" style="display: none;">
            @include('app.settings.integrations.whatsapp')
        </div>

        @if ($business->isModuleEnabled('channels_marketing'))
            <!-- ===================================================== -->
            <!-- TAB: SOCIAL MEDIA INTEGRATIONS & OAUTH CHANNELS       -->
            <!-- ===================================================== -->
            <div x-show="activeTab === 'social_media' || activeTab === 'social' || activeTab === 'channels'" class="space-y-6" style="display: none;">
                @include('app.settings.integrations.social_media')
            </div>
        @endif

        <!-- ===================================================== -->
        <!-- TAB 4: 20 PRESET TEMPLATE INDUSTRI (§35)              -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'templates'" class="space-y-6" style="display: none;">
            <!-- Search & Category Filters -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-4 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-[17px] font-semibold text-black dark:text-white flex items-center gap-2">
                            <span>{{ __('settings.templates_title') }}</span>
                        </h2>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('settings.templates_desc') }}</p>
                    </div>

                    <!-- macOS Style Capsule Search -->
                    <div class="relative w-full md:w-72">
                        <i data-lucide="search" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input type="text" x-model="templateSearch" placeholder="{{ __('settings.search_template') }}"
                            class="w-full h-10 sm:h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[15px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <!-- Segmented Category Filters (Swipeable Pill Bar on Mobile, Flex on Desktop) -->
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1 -mx-2 px-2 sm:mx-0 sm:px-0 sm:flex-wrap border-t border-black/5 dark:border-white/5">
                    <button type="button" @click="selectedCategory = 'all'"
                        :class="selectedCategory === 'all' ?
                            'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' :
                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                        class="h-9 sm:h-8 px-3.5 rounded-[8px] text-[12px] font-medium active:scale-[0.97] transition-all min-h-[36px] shrink-0 whitespace-nowrap">
                        {{ __('settings.all_categories') }}
                    </button>
                    <button type="button" @click="selectedCategory = 'fnb'"
                        :class="selectedCategory === 'fnb' ?
                            'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' :
                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                        class="h-9 sm:h-8 px-3.5 rounded-[8px] text-[12px] font-medium active:scale-[0.97] transition-all min-h-[36px] shrink-0 whitespace-nowrap">
                        {{ __('settings.fnb') }}
                    </button>
                    <button type="button" @click="selectedCategory = 'retail'"
                        :class="selectedCategory === 'retail' ?
                            'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' :
                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                        class="h-9 sm:h-8 px-3.5 rounded-[8px] text-[12px] font-medium active:scale-[0.97] transition-all min-h-[36px] shrink-0 whitespace-nowrap">
                        {{ __('settings.retail') }}
                    </button>
                    <button type="button" @click="selectedCategory = 'manufacturing'"
                        :class="selectedCategory === 'manufacturing' ?
                            'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' :
                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                        class="h-9 sm:h-8 px-3.5 rounded-[8px] text-[12px] font-medium active:scale-[0.97] transition-all min-h-[36px] shrink-0 whitespace-nowrap">
                        {{ __('settings.manufacturing') }}
                    </button>
                    <button type="button" @click="selectedCategory = 'service'"
                        :class="selectedCategory === 'service' ?
                            'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' :
                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                        class="h-9 sm:h-8 px-3.5 rounded-[8px] text-[12px] font-medium active:scale-[0.97] transition-all min-h-[36px] shrink-0 whitespace-nowrap">
                        {{ __('settings.service') }}
                    </button>
                    <button type="button" @click="selectedCategory = 'agriculture'"
                        :class="selectedCategory === 'agriculture' ?
                            'bg-[#007AFF] text-white shadow-[0_1px_2px_rgba(0,122,255,0.25)]' :
                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                        class="h-9 sm:h-8 px-3.5 rounded-[8px] text-[12px] font-medium active:scale-[0.97] transition-all min-h-[36px] shrink-0 whitespace-nowrap">
                        {{ __('settings.agriculture') }}
                    </button>
                </div>
            </div>

            <!-- Templates Grid (Dense & Structured) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($templates as $tmpl)
                    <div x-show="(selectedCategory === 'all' || '{{ strtolower($tmpl->industry_category) }}'.includes(selectedCategory)) && (!templateSearch || '{{ strtolower($tmpl->name . ' ' . $tmpl->description . ' ' . $tmpl->industry_category) }}'.includes(templateSearch.toLowerCase()))"
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between space-y-4 hover:border-black/15 dark:hover:border-white/15 transition-all duration-200 shadow-sm">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                    {{ $tmpl->industry_category }}
                                </span>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 tabular-nums">
                                    {{ $tmpl->recommended_costing_method }}
                                </span>
                            </div>

                            <h3 class="text-[16px] font-semibold text-black dark:text-white">
                                {{ $tmpl->name }}
                            </h3>
                            <p class="text-[13px] text-black/55 dark:text-white/55 line-clamp-2 leading-relaxed">
                                {{ $tmpl->description }}
                            </p>
                        </div>

                        <div
                            class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-2">
                            <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">
                                {{ count($tmpl->default_cost_components ?? []) }} {{ __('settings.cost_components') }}
                            </span>

                            @if (\App\Support\Context::hasPermission('settings.edit'))
                                <button type="button"
                                    @click="openApplyTemplate('{{ $tmpl->code }}', '{{ addslashes($tmpl->name) }}', '{{ addslashes($tmpl->industry_category) }}')"
                                    class="h-9 px-3.5 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center gap-1.5 min-h-[36px]">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('settings.apply') }}</span>
                                </button>

                                <form id="form-apply-template-{{ $tmpl->code }}" method="POST"
                                    action="{{ route('settings.apply-template') }}" class="hidden">
                                    @csrf
                                    <input type="hidden" name="template_code" value="{{ $tmpl->code }}">
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- TAB 5: KELOLA MODUL & PENATAAN MENU BISNIS            -->
        <!-- ===================================================== -->
        <div x-show="activeTab === 'modules'" class="space-y-6" style="display: none;">
            <form method="POST" action="{{ route('settings.modules.update') }}" class="space-y-6" @submit.prevent>
                @csrf
                @method('PUT')

                <!-- Header Card -->
                <div
                    class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-black/5 dark:border-white/5 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.manage_modules_title') }}</h2>
                                @if ($business->template_code)
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                        {{ __('settings.lbl_preset') }}: {{ strtoupper($business->template_code) }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-[13px] text-black/50 dark:text-white/50 mt-1">
                                {{ __('settings.manage_modules_desc') }}
                            </p>
                        </div>

                        @if (\App\Support\Context::isOwner())
                            <div class="flex items-center gap-2 shrink-0">
                                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-[12px] font-medium transition-all"
                                    :class="{
                                        'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20': moduleSaveStatus === 'saved' || moduleSaveStatus === 'idle',
                                        'bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20': moduleSaveStatus === 'saving',
                                        'bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20': moduleSaveStatus === 'error'
                                    }">
                                    <template x-if="moduleSaveStatus === 'saving'">
                                        <svg class="animate-spin w-3.5 h-3.5 text-[#007AFF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="moduleSaveStatus === 'saved' || moduleSaveStatus === 'idle'">
                                        <svg class="w-3.5 h-3.5 text-[#34C759]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </template>
                                    <template x-if="moduleSaveStatus === 'error'">
                                        <svg class="w-3.5 h-3.5 text-[#FF3B30]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                                        </svg>
                                    </template>
                                    <span x-text="moduleSaveStatus === 'saving' ? '{{ __('settings.module_autosave_saving') }}' : (moduleSaveStatus === 'error' ? '{{ __('settings.module_autosave_error') }}' : '{{ __('settings.module_autosave_saved') }}')">
                                        {{ __('settings.module_autosave_saved') }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Callout Info -->
                    <div
                        class="rounded-[12px] bg-[#007AFF]/8 border border-[#007AFF]/15 p-3.5 flex items-start gap-3 text-[12px] text-black/70 dark:text-white/70">
                        <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                        <div class="space-y-0.5">
                            <p class="font-medium text-[#007AFF]">{{ __('settings.owner_control_title') }}</p>
                            <p>{{ __('settings.owner_control_desc') }}</p>
                        </div>
                    </div>
                </div>

                <!-- ============================================================= -->
                <!-- SUB-PANEL: 1-CLICK PROFIL SKALA BISNIS (PROGRESSIVE DISCLOSURE) -->
                <!-- ============================================================= -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-5 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF]">
                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                </span>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">Profil Skala Operasional (1-Click Preset)</h3>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                    One System for Life
                                </span>
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 mt-1">
                                Pilih profil yang mencerminkan fase bisnis Anda saat ini. Seluruh alur kerja dan parameter akan otomatis beradaptasi tanpa perlu berganti sistem dan tanpa menghapus riwayat transaksi.
                            </p>
                        </div>
                    </div>

                    <!-- 3 Bento Preset Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- TIER 1: STARTER -->
                        <div class="relative rounded-[14px] p-5 border transition-all flex flex-col justify-between"
                             :class="currentScaleTier === 'starter' 
                                ? 'bg-[#007AFF]/[0.03] dark:bg-[#007AFF]/[0.08] border-[#007AFF] ring-2 ring-[#007AFF]/20 shadow-sm' 
                                : 'bg-black/[0.01] dark:bg-white/[0.02] border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20'">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                        <i data-lucide="zap" class="w-3 h-3"></i> UMKM MIKRO
                                    </span>
                                    <template x-if="currentScaleTier === 'starter'">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#007AFF]">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Aktif Saat Ini
                                        </span>
                                    </template>
                                </div>
                                <div>
                                    <h4 class="text-[15px] font-bold text-black dark:text-white">Starter (Rintisan)</h4>
                                    <p class="text-[12px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
                                        Dirancang untuk pemilik usaha tunggal atau gerobak rintisan. Layar kasir bersih, transaksi instan, tanpa beban administrasi.
                                    </p>
                                </div>
                                <ul class="text-[11px] space-y-1.5 text-black/70 dark:text-white/70 pt-2 border-t border-black/5 dark:border-white/5">
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Kasir POS 1-Klik Super Cepat</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Bebas Jualan Saat Stok Habis/Minus</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Kirim Struk Otomatis via WhatsApp</span>
                                    </li>
                                    <li class="flex items-center gap-1.5 text-black/40 dark:text-white/40">
                                        <i data-lucide="minus" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span>Tanpa Menu Gudang & Jurnal Rumit</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="pt-4 mt-3">
                                <template x-if="currentScaleTier === 'starter'">
                                    <button type="button" disabled class="w-full py-2 px-3 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] text-[12px] font-bold text-center cursor-default">
                                        Profil Aktif
                                    </button>
                                </template>
                                <template x-if="currentScaleTier !== 'starter'">
                                    <button type="button" @click="requestApplyTier('starter', 'UMKM Mikro (Starter)')"
                                            class="w-full py-2 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] hover:text-[#007AFF] text-[12px] font-semibold text-black dark:text-white transition-all shadow-sm">
                                        Terapkan Starter
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- TIER 2: GROWTH -->
                        <div class="relative rounded-[14px] p-5 border transition-all flex flex-col justify-between"
                             :class="currentScaleTier === 'growth' 
                                ? 'bg-[#FF9500]/[0.03] dark:bg-[#FF9500]/[0.08] border-[#FF9500] ring-2 ring-[#FF9500]/20 shadow-sm' 
                                : 'bg-black/[0.01] dark:bg-white/[0.02] border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20'">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#FFA00A]">
                                        <i data-lucide="trending-up" class="w-3 h-3"></i> BERTUMBUH
                                    </span>
                                    <template x-if="currentScaleTier === 'growth'">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#FF9500]">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Aktif Saat Ini
                                        </span>
                                    </template>
                                </div>
                                <div>
                                    <h4 class="text-[15px] font-bold text-black dark:text-white">Growth (1-3 Cabang)</h4>
                                    <p class="text-[12px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
                                        Fokus pada pengawasan operasional, pencegahan kebocoran kas kasir (anti-fraud), dan resep HPP otomatis.
                                    </p>
                                </div>
                                <ul class="text-[11px] space-y-1.5 text-black/70 dark:text-white/70 pt-2 border-t border-black/5 dark:border-white/5">
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Buka / Tutup Shift Laci Kas Blind Close</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>PIN Otorisasi Supervisor untuk Void/Diskon</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Formula Resep (BOM) & HPP Otomatis</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Database Poin Loyalitas & WhatsApp CRM</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="pt-4 mt-3">
                                <template x-if="currentScaleTier === 'growth'">
                                    <button type="button" disabled class="w-full py-2 px-3 rounded-[10px] bg-[#FF9500]/15 text-[#FF9500] text-[12px] font-bold text-center cursor-default">
                                        Profil Aktif
                                    </button>
                                </template>
                                <template x-if="currentScaleTier !== 'growth'">
                                    <button type="button" @click="requestApplyTier('growth', 'UMKM Bertumbuh (Growth)')"
                                            class="w-full py-2 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#FF9500] hover:text-[#FF9500] text-[12px] font-semibold text-black dark:text-white transition-all shadow-sm">
                                        Terapkan Growth
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- TIER 3: CORPORATE -->
                        <div class="relative rounded-[14px] p-5 border transition-all flex flex-col justify-between"
                             :class="currentScaleTier === 'corporate' 
                                ? 'bg-[#AF52DE]/[0.03] dark:bg-[#AF52DE]/[0.08] border-[#AF52DE] ring-2 ring-[#AF52DE]/20 shadow-sm' 
                                : 'bg-black/[0.01] dark:bg-white/[0.02] border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20'">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#AF52DE]/15 text-[#8944AB] dark:text-[#BF5AF2]">
                                        <i data-lucide="building-2" class="w-3 h-3"></i> SCALE-UP
                                    </span>
                                    <template x-if="currentScaleTier === 'corporate'">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#AF52DE]">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Aktif Saat Ini
                                        </span>
                                    </template>
                                </div>
                                <div>
                                    <h4 class="text-[15px] font-bold text-black dark:text-white">Corporate (Scale-Up)</h4>
                                    <p class="text-[12px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
                                        Arsitektur lengkap untuk distributor regional, rantai pasok multi-gudang, dan kepatuhan akuntansi standar SAK EMKM.
                                    </p>
                                </div>
                                <ul class="text-[11px] space-y-1.5 text-black/70 dark:text-white/70 pt-2 border-t border-black/5 dark:border-white/5">
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Surat Jalan Resmi Supir (Delivery Order)</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Transfer Antar Multi-Gudang & Cabang</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Validasi Batas Plafon Piutang B2B</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Akuntansi Lengkap: COA, Jurnal & Neraca</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="pt-4 mt-3">
                                <template x-if="currentScaleTier === 'corporate'">
                                    <button type="button" disabled class="w-full py-2 px-3 rounded-[10px] bg-[#AF52DE]/15 text-[#AF52DE] text-[12px] font-bold text-center cursor-default">
                                        Profil Aktif
                                    </button>
                                </template>
                                <template x-if="currentScaleTier !== 'corporate'">
                                    <button type="button" @click="requestApplyTier('corporate', 'Perusahaan Berkembang (Corporate)')"
                                            class="w-full py-2 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#AF52DE] hover:text-[#AF52DE] text-[12px] font-semibold text-black dark:text-white transition-all shadow-sm">
                                        Terapkan Corporate
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================= -->
                <!-- SUB-PANEL: 24 PARAMETER GRANULAR KUSTOM MANDIRI               -->
                <!-- ============================================================= -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-5 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-[8px] bg-[#34C759]/10 text-[#34C759]">
                                    <i data-lucide="sliders" class="w-4 h-4"></i>
                                </span>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">24 Parameter Granular Mandiri</h3>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                    Kustomisasi Bebas
                                </span>
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 mt-1">
                                Atur setiap parameter operasional secara independen. Perubahan tersimpan otomatis secara real-time.
                            </p>
                        </div>

                        <!-- Search Parameter Input -->
                        <div class="relative w-full sm:w-64">
                            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                            <input type="text" x-model="featureSearch" placeholder="Cari nama parameter..."
                                   class="w-full pl-8 pr-3 py-1.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:border-[#007AFF] transition-all">
                        </div>
                    </div>

                    <!-- Cluster Grid of 24 Parameters -->
                    <div class="space-y-6">
                        @foreach($featureCategories as $catKey => $catMeta)
                            @php
                                $catFeatures = array_filter($featureDefinitions, fn($def) => $def['category'] === $catKey);
                            @endphp
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="p-1 rounded-[6px] bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                        <i data-lucide="{{ $catMeta['icon'] }}" class="w-3.5 h-3.5"></i>
                                    </span>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white">{{ $catMeta['name'] }}</h4>
                                    <span class="text-[11px] text-black/40 dark:text-white/40">({{ count($catFeatures) }} parameter)</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($catFeatures as $fKey => $fDef)
                                        <div x-show="!featureSearch || '{{ strtolower($fDef['name'] . ' ' . $fDef['description'] . ' ' . $fKey) }}'.includes(featureSearch.toLowerCase())"
                                             class="rounded-[12px] bg-black/[0.015] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-3.5 flex items-start justify-between gap-3 hover:bg-black/[0.03] dark:hover:bg-white/[0.04] transition-all">
                                            <div class="space-y-1 flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[13px] font-semibold text-black dark:text-white leading-tight">
                                                        {{ $fDef['name'] }}
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-black/50 dark:text-white/50 leading-relaxed line-clamp-2">
                                                    {{ $fDef['description'] }}
                                                </p>
                                                <div class="flex items-center gap-2 pt-1 text-[10px] text-black/40 dark:text-white/40 font-mono">
                                                    <span>{{ $fKey }}</span>
                                                </div>
                                            </div>

                                            <!-- iOS / Apple Toggle Switch -->
                                            <div class="shrink-0 pt-0.5">
                                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                                    <input type="checkbox"
                                                           :checked="featuresState['{{ $fKey }}']"
                                                           :disabled="featureSaving['{{ $fKey }}'] || !isOwner"
                                                           @change="toggleFeatureParam('{{ $fKey }}', $event.target.checked)"
                                                           class="sr-only peer">
                                                    <div class="w-10 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/15 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-[#34C759]"></div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- MODAL KONFIRMASI TERAPKAN TIER -->
                <div x-show="confirmTierModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;" x-cloak>
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="confirmTierModalOpen = false"></div>
                    <div class="relative w-full max-w-md rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2.5 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF]">
                                <i data-lucide="help-circle" class="w-6 h-6"></i>
                            </span>
                            <div>
                                <h3 class="text-[17px] font-bold text-black dark:text-white">Terapkan Profil Skala?</h3>
                                <p class="text-[12px] text-black/50 dark:text-white/50" x-text="targetTierName"></p>
                            </div>
                        </div>

                        <p class="text-[13px] text-black/70 dark:text-white/70 leading-relaxed">
                            Sistem akan menyesuaikan alur kerja kasir, otorisasi, dan modul fitur sesuai profil <strong x-text="targetTierName"></strong>. Seluruh riwayat transaksi, laporan, dan master data Anda tetap 100% aman dan utuh.
                        </p>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                            <button type="button" @click="confirmTierModalOpen = false"
                                    class="px-4 py-2 rounded-[10px] bg-black/5 dark:bg-white/10 text-[12px] font-semibold text-black dark:text-white hover:bg-black/10 transition-all">
                                Batal
                            </button>
                            <button type="button" @click="executeApplyTier()" :disabled="applyingTier"
                                    class="px-4 py-2 rounded-[10px] bg-[#007AFF] text-[12px] font-bold text-white hover:bg-[#007AFF]/90 transition-all inline-flex items-center gap-1.5 shadow-sm">
                                <template x-if="applyingTier">
                                    <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <span x-text="applyingTier ? 'Menerapkan...' : 'Ya, Terapkan Sekarang'"></span>
                            </button>
                        </div>
                    </div>
                </div>


                <!-- ============================================================= -->
                <!-- SUB-PANEL: 1-CLICK PROFIL SKALA BISNIS (PROGRESSIVE DISCLOSURE) -->
                <!-- ============================================================= -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-5 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF]">
                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                </span>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">Profil Skala Operasional (1-Click Preset)</h3>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                    One System for Life
                                </span>
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 mt-1">
                                Pilih profil yang mencerminkan fase bisnis Anda saat ini. Seluruh alur kerja dan parameter akan otomatis beradaptasi tanpa perlu berganti sistem dan tanpa menghapus riwayat transaksi.
                            </p>
                        </div>
                    </div>

                    <!-- 3 Bento Preset Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- TIER 1: STARTER -->
                        <div class="relative rounded-[14px] p-5 border transition-all flex flex-col justify-between"
                             :class="currentScaleTier === 'starter' 
                                ? 'bg-[#007AFF]/[0.03] dark:bg-[#007AFF]/[0.08] border-[#007AFF] ring-2 ring-[#007AFF]/20 shadow-sm' 
                                : 'bg-black/[0.01] dark:bg-white/[0.02] border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20'">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                        <i data-lucide="zap" class="w-3 h-3"></i> UMKM MIKRO
                                    </span>
                                    <template x-if="currentScaleTier === 'starter'">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#007AFF]">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Aktif Saat Ini
                                        </span>
                                    </template>
                                </div>
                                <div>
                                    <h4 class="text-[15px] font-bold text-black dark:text-white">Starter (Rintisan)</h4>
                                    <p class="text-[12px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
                                        Dirancang untuk pemilik usaha tunggal atau gerobak rintisan. Layar kasir bersih, transaksi instan, tanpa beban administrasi.
                                    </p>
                                </div>
                                <ul class="text-[11px] space-y-1.5 text-black/70 dark:text-white/70 pt-2 border-t border-black/5 dark:border-white/5">
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Kasir POS 1-Klik Super Cepat</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Bebas Jualan Saat Stok Habis/Minus</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Kirim Struk Otomatis via WhatsApp</span>
                                    </li>
                                    <li class="flex items-center gap-1.5 text-black/40 dark:text-white/40">
                                        <i data-lucide="minus" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span>Tanpa Menu Gudang & Jurnal Rumit</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="pt-4 mt-3">
                                <template x-if="currentScaleTier === 'starter'">
                                    <button type="button" disabled class="w-full py-2 px-3 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] text-[12px] font-bold text-center cursor-default">
                                        Profil Aktif
                                    </button>
                                </template>
                                <template x-if="currentScaleTier !== 'starter'">
                                    <button type="button" @click="requestApplyTier('starter', 'UMKM Mikro (Starter)')"
                                            class="w-full py-2 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] hover:text-[#007AFF] text-[12px] font-semibold text-black dark:text-white transition-all shadow-sm">
                                        Terapkan Starter
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- TIER 2: GROWTH -->
                        <div class="relative rounded-[14px] p-5 border transition-all flex flex-col justify-between"
                             :class="currentScaleTier === 'growth' 
                                ? 'bg-[#FF9500]/[0.03] dark:bg-[#FF9500]/[0.08] border-[#FF9500] ring-2 ring-[#FF9500]/20 shadow-sm' 
                                : 'bg-black/[0.01] dark:bg-white/[0.02] border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20'">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#FFA00A]">
                                        <i data-lucide="trending-up" class="w-3 h-3"></i> BERTUMBUH
                                    </span>
                                    <template x-if="currentScaleTier === 'growth'">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#FF9500]">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Aktif Saat Ini
                                        </span>
                                    </template>
                                </div>
                                <div>
                                    <h4 class="text-[15px] font-bold text-black dark:text-white">Growth (1-3 Cabang)</h4>
                                    <p class="text-[12px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
                                        Fokus pada pengawasan operasional, pencegahan kebocoran kas kasir (anti-fraud), dan resep HPP otomatis.
                                    </p>
                                </div>
                                <ul class="text-[11px] space-y-1.5 text-black/70 dark:text-white/70 pt-2 border-t border-black/5 dark:border-white/5">
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Buka / Tutup Shift Laci Kas Blind Close</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>PIN Otorisasi Supervisor untuk Void/Diskon</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Formula Resep (BOM) & HPP Otomatis</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Database Poin Loyalitas & WhatsApp CRM</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="pt-4 mt-3">
                                <template x-if="currentScaleTier === 'growth'">
                                    <button type="button" disabled class="w-full py-2 px-3 rounded-[10px] bg-[#FF9500]/15 text-[#FF9500] text-[12px] font-bold text-center cursor-default">
                                        Profil Aktif
                                    </button>
                                </template>
                                <template x-if="currentScaleTier !== 'growth'">
                                    <button type="button" @click="requestApplyTier('growth', 'UMKM Bertumbuh (Growth)')"
                                            class="w-full py-2 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#FF9500] hover:text-[#FF9500] text-[12px] font-semibold text-black dark:text-white transition-all shadow-sm">
                                        Terapkan Growth
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- TIER 3: CORPORATE -->
                        <div class="relative rounded-[14px] p-5 border transition-all flex flex-col justify-between"
                             :class="currentScaleTier === 'corporate' 
                                ? 'bg-[#AF52DE]/[0.03] dark:bg-[#AF52DE]/[0.08] border-[#AF52DE] ring-2 ring-[#AF52DE]/20 shadow-sm' 
                                : 'bg-black/[0.01] dark:bg-white/[0.02] border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20'">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#AF52DE]/15 text-[#8944AB] dark:text-[#BF5AF2]">
                                        <i data-lucide="building-2" class="w-3 h-3"></i> SCALE-UP
                                    </span>
                                    <template x-if="currentScaleTier === 'corporate'">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#AF52DE]">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Aktif Saat Ini
                                        </span>
                                    </template>
                                </div>
                                <div>
                                    <h4 class="text-[15px] font-bold text-black dark:text-white">Corporate (Scale-Up)</h4>
                                    <p class="text-[12px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
                                        Arsitektur lengkap untuk distributor regional, rantai pasok multi-gudang, dan kepatuhan akuntansi standar SAK EMKM.
                                    </p>
                                </div>
                                <ul class="text-[11px] space-y-1.5 text-black/70 dark:text-white/70 pt-2 border-t border-black/5 dark:border-white/5">
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Surat Jalan Resmi Supir (Delivery Order)</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Transfer Antar Multi-Gudang & Cabang</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Validasi Batas Plafon Piutang B2B</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Akuntansi Lengkap: COA, Jurnal & Neraca</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="pt-4 mt-3">
                                <template x-if="currentScaleTier === 'corporate'">
                                    <button type="button" disabled class="w-full py-2 px-3 rounded-[10px] bg-[#AF52DE]/15 text-[#AF52DE] text-[12px] font-bold text-center cursor-default">
                                        Profil Aktif
                                    </button>
                                </template>
                                <template x-if="currentScaleTier !== 'corporate'">
                                    <button type="button" @click="requestApplyTier('corporate', 'Perusahaan Berkembang (Corporate)')"
                                            class="w-full py-2 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#AF52DE] hover:text-[#AF52DE] text-[12px] font-semibold text-black dark:text-white transition-all shadow-sm">
                                        Terapkan Corporate
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================= -->
                <!-- SUB-PANEL: 24 PARAMETER GRANULAR KUSTOM MANDIRI               -->
                <!-- ============================================================= -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-5 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-[8px] bg-[#34C759]/10 text-[#34C759]">
                                    <i data-lucide="sliders" class="w-4 h-4"></i>
                                </span>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">24 Parameter Granular Mandiri</h3>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                    Kustomisasi Bebas
                                </span>
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 mt-1">
                                Atur setiap parameter operasional secara independen. Perubahan tersimpan otomatis secara real-time.
                            </p>
                        </div>

                        <!-- Search Parameter Input -->
                        <div class="relative w-full sm:w-64">
                            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                            <input type="text" x-model="featureSearch" placeholder="Cari nama parameter..."
                                   class="w-full pl-8 pr-3 py-1.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:border-[#007AFF] transition-all">
                        </div>
                    </div>

                    <!-- Cluster Grid of 24 Parameters -->
                    <div class="space-y-6">
                        @foreach($featureCategories as $catKey => $catMeta)
                            @php
                                $catFeatures = array_filter($featureDefinitions, fn($def) => $def['category'] === $catKey);
                            @endphp
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="p-1 rounded-[6px] bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                        <i data-lucide="{{ $catMeta['icon'] }}" class="w-3.5 h-3.5"></i>
                                    </span>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white">{{ $catMeta['name'] }}</h4>
                                    <span class="text-[11px] text-black/40 dark:text-white/40">({{ count($catFeatures) }} parameter)</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($catFeatures as $fKey => $fDef)
                                        <div x-show="!featureSearch || '{{ strtolower($fDef['name'] . ' ' . $fDef['description'] . ' ' . $fKey) }}'.includes(featureSearch.toLowerCase())"
                                             class="rounded-[12px] bg-black/[0.015] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-3.5 flex items-start justify-between gap-3 hover:bg-black/[0.03] dark:hover:bg-white/[0.04] transition-all">
                                            <div class="space-y-1 flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[13px] font-semibold text-black dark:text-white leading-tight">
                                                        {{ $fDef['name'] }}
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-black/50 dark:text-white/50 leading-relaxed line-clamp-2">
                                                    {{ $fDef['description'] }}
                                                </p>
                                                <div class="flex items-center gap-2 pt-1 text-[10px] text-black/40 dark:text-white/40 font-mono">
                                                    <span>{{ $fKey }}</span>
                                                </div>
                                            </div>

                                            <!-- iOS / Apple Toggle Switch -->
                                            <div class="shrink-0 pt-0.5">
                                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                                    <input type="checkbox"
                                                           :checked="featuresState['{{ $fKey }}']"
                                                           :disabled="featureSaving['{{ $fKey }}'] || !isOwner"
                                                           @change="toggleFeatureParam('{{ $fKey }}', $event.target.checked)"
                                                           class="sr-only peer">
                                                    <div class="w-10 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/15 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-[#34C759]"></div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- MODAL KONFIRMASI TERAPKAN TIER -->
                <div x-show="confirmTierModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;" x-cloak>
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="confirmTierModalOpen = false"></div>
                    <div class="relative w-full max-w-md rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2.5 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF]">
                                <i data-lucide="help-circle" class="w-6 h-6"></i>
                            </span>
                            <div>
                                <h3 class="text-[17px] font-bold text-black dark:text-white">Terapkan Profil Skala?</h3>
                                <p class="text-[12px] text-black/50 dark:text-white/50" x-text="targetTierName"></p>
                            </div>
                        </div>

                        <p class="text-[13px] text-black/70 dark:text-white/70 leading-relaxed">
                            Sistem akan menyesuaikan alur kerja kasir, otorisasi, dan modul fitur sesuai profil <strong x-text="targetTierName"></strong>. Seluruh riwayat transaksi, laporan, dan master data Anda tetap 100% aman dan utuh.
                        </p>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                            <button type="button" @click="confirmTierModalOpen = false"
                                    class="px-4 py-2 rounded-[10px] bg-black/5 dark:bg-white/10 text-[12px] font-semibold text-black dark:text-white hover:bg-black/10 transition-all">
                                Batal
                            </button>
                            <button type="button" @click="executeApplyTier()" :disabled="applyingTier"
                                    class="px-4 py-2 rounded-[10px] bg-[#007AFF] text-[12px] font-bold text-white hover:bg-[#007AFF]/90 transition-all inline-flex items-center gap-1.5 shadow-sm">
                                <template x-if="applyingTier">
                                    <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <span x-text="applyingTier ? 'Menerapkan...' : 'Ya, Terapkan Sekarang'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                @php
                    $clusterMap = [
                        'kasir' => [
                            'title' => __('settings.cluster_pos_title'),
                            'desc' => __('settings.cluster_pos_desc'),
                            'icon' => 'calculator',
                            'color' => 'text-[#007AFF]',
                            'bg' => 'bg-[#007AFF]/10',
                            'keys' => ['pos_retail', 'pos_dinein'],
                            'impacts' => [
                                'pos_retail' => ($business?->isFoodIndustry() ? 'Kasir & POS Resto: Buka Kasir POS, Transaksi Kasir & Shift, Laporan Kasir' : 'Kasir & POS: Buka Kasir POS, Transaksi Kasir & Shift, Laporan Kasir'),
                                'pos_dinein' => 'Restoran & Dine-In: KDS Layar Dapur, Pengaturan Meja QR',
                            ],
                        ],
                        'b2b' => [
                            'title' => __('settings.cluster_b2b_title'),
                            'desc' => __('settings.cluster_b2b_desc'),
                            'icon' => 'file-text',
                            'color' => 'text-[#34C759]',
                            'bg' => 'bg-[#34C759]/10',
                            'keys' => ['b2b_sales'],
                            'impacts' => [
                                'b2b_sales' => 'Penjualan B2B & Faktur: Pesanan Penjualan (SO), Surat Penawaran, Faktur Tagihan (Invoice), Retur Penjualan',
                            ],
                        ],
                        'produksi_gudang' => [
                            'title' => __('settings.cluster_mfg_title'),
                            'desc' => __('settings.cluster_mfg_desc'),
                            'icon' => 'boxes',
                            'color' => 'text-[#AF52DE]',
                            'bg' => 'bg-[#AF52DE]/10',
                            'keys' => ['recipe_bom', 'labor_machines', 'inventory_warehouse', 'procurement'],
                            'impacts' => [
                                'recipe_bom' => 'Produk & Logistik: Bahan Baku & Resep (BOM), Kategori Bahan, Satuan Ukur',
                                'labor_machines' => 'Produksi HPP: Upah Kerja & Mesin, Alokasi Biaya HPP',
                                'inventory_warehouse' => 'Produk & Logistik: Saldo Stok Real-Time, Cabang & Gudang, Transfer Stok, Stock Opname, Mutasi Stok',
                                'procurement' => 'Pembelian & Pengadaan: Pesanan Pembelian (PO), Tagihan Supplier (Bills), Supplier, Retur Pembelian',
                            ],
                        ],
                        'crm_marketing' => [
                            'title' => __('settings.cluster_crm_title'),
                            'desc' => __('settings.cluster_crm_desc'),
                            'icon' => 'award',
                            'color' => 'text-[#FF9500]',
                            'bg' => 'bg-[#FF9500]/10',
                            'keys' => ['crm_loyalty', 'channels_marketing'],
                            'impacts' => [
                                'crm_loyalty' => 'Pelanggan & Loyalitas: Data Pelanggan, Poin Loyalitas Member, Kupon Voucher Diskon',
                                'channels_marketing' => 'Marketing & Pesan: WhatsApp Gateway Struk & Promo, Landing Page CMS',
                            ],
                        ],
                        'toko_online' => [
                            'title' => __('settings.cluster_storefront_title'),
                            'desc' => __('settings.cluster_storefront_desc'),
                            'icon' => 'shopping-bag',
                            'color' => 'text-[#5856D6]',
                            'bg' => 'bg-[#5856D6]/10',
                            'keys' => ['storefront_checkout', 'order_request', 'scheduled_order', 'customer_po', 'reservation', 'merchant_shipping'],
                            'impacts' => [
                                'storefront_checkout' => 'Toko Online: Pengaturan Toko Online, Pesanan Masuk Toko',
                                'order_request' => 'Toko Online: Pengajuan Request Order Khusus',
                                'scheduled_order' => 'Toko Online: Slot Tanggal Pemesanan & Lead Time',
                                'customer_po' => 'B2B Portal: PO Batch Klien & Multi-Drop',
                                'reservation' => 'Kasir & POS: Reservasi Meja & Booking Jadwal',
                                'merchant_shipping' => 'Logistik: Pengaturan Ongkos Kirim & Kurir Toko',
                            ],
                        ],
                        'keuangan_akuntansi' => [
                            'title' => __('settings.cluster_accounting_title'),
                            'desc' => __('settings.cluster_accounting_desc'),
                            'icon' => 'book-open',
                            'color' => 'text-[#007AFF]',
                            'bg' => 'bg-[#007AFF]/10',
                            'keys' => ['accounting_corporate'],
                            'impacts' => [
                                'accounting_corporate' => 'Akuntansi Korporasi: Bagan Akun (COA), Buku Jurnal Keuangan, Buku Besar Akun, Neraca Keuangan, Neraca Saldo, Rekonsiliasi Bank',
                            ],
                        ],
                    ];
                @endphp

                <!-- 6 Thematic Bento Clusters -->
                @foreach ($clusterMap as $clusterId => $cluster)
                    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 shadow-sm">
                        <!-- Cluster Header -->
                        <div class="flex items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] {{ $cluster['bg'] }} {{ $cluster['color'] }} flex items-center justify-center shrink-0">
                                    <i data-lucide="{{ $cluster['icon'] }}" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-semibold text-black dark:text-white">{{ $cluster['title'] }}</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ $cluster['desc'] }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Cards in Cluster -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($cluster['keys'] as $moduleKey)
                                @if (isset($allModules[$moduleKey]))
                                    <!-- ============================================================= -->
                <!-- SUB-PANEL: 1-CLICK PROFIL SKALA BISNIS (PROGRESSIVE DISCLOSURE) -->
                <!-- ============================================================= -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-5 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF]">
                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                </span>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">Profil Skala Operasional (1-Click Preset)</h3>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                    One System for Life
                                </span>
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 mt-1">
                                Pilih profil yang mencerminkan fase bisnis Anda saat ini. Seluruh alur kerja dan parameter akan otomatis beradaptasi tanpa perlu berganti sistem dan tanpa menghapus riwayat transaksi.
                            </p>
                        </div>
                    </div>

                    <!-- 3 Bento Preset Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- TIER 1: STARTER -->
                        <div class="relative rounded-[14px] p-5 border transition-all flex flex-col justify-between"
                             :class="currentScaleTier === 'starter' 
                                ? 'bg-[#007AFF]/[0.03] dark:bg-[#007AFF]/[0.08] border-[#007AFF] ring-2 ring-[#007AFF]/20 shadow-sm' 
                                : 'bg-black/[0.01] dark:bg-white/[0.02] border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20'">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                        <i data-lucide="zap" class="w-3 h-3"></i> UMKM MIKRO
                                    </span>
                                    <template x-if="currentScaleTier === 'starter'">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#007AFF]">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Aktif Saat Ini
                                        </span>
                                    </template>
                                </div>
                                <div>
                                    <h4 class="text-[15px] font-bold text-black dark:text-white">Starter (Rintisan)</h4>
                                    <p class="text-[12px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
                                        Dirancang untuk pemilik usaha tunggal atau gerobak rintisan. Layar kasir bersih, transaksi instan, tanpa beban administrasi.
                                    </p>
                                </div>
                                <ul class="text-[11px] space-y-1.5 text-black/70 dark:text-white/70 pt-2 border-t border-black/5 dark:border-white/5">
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Kasir POS 1-Klik Super Cepat</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Bebas Jualan Saat Stok Habis/Minus</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Kirim Struk Otomatis via WhatsApp</span>
                                    </li>
                                    <li class="flex items-center gap-1.5 text-black/40 dark:text-white/40">
                                        <i data-lucide="minus" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span>Tanpa Menu Gudang & Jurnal Rumit</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="pt-4 mt-3">
                                <template x-if="currentScaleTier === 'starter'">
                                    <button type="button" disabled class="w-full py-2 px-3 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] text-[12px] font-bold text-center cursor-default">
                                        Profil Aktif
                                    </button>
                                </template>
                                <template x-if="currentScaleTier !== 'starter'">
                                    <button type="button" @click="requestApplyTier('starter', 'UMKM Mikro (Starter)')"
                                            class="w-full py-2 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] hover:text-[#007AFF] text-[12px] font-semibold text-black dark:text-white transition-all shadow-sm">
                                        Terapkan Starter
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- TIER 2: GROWTH -->
                        <div class="relative rounded-[14px] p-5 border transition-all flex flex-col justify-between"
                             :class="currentScaleTier === 'growth' 
                                ? 'bg-[#FF9500]/[0.03] dark:bg-[#FF9500]/[0.08] border-[#FF9500] ring-2 ring-[#FF9500]/20 shadow-sm' 
                                : 'bg-black/[0.01] dark:bg-white/[0.02] border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20'">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#FFA00A]">
                                        <i data-lucide="trending-up" class="w-3 h-3"></i> BERTUMBUH
                                    </span>
                                    <template x-if="currentScaleTier === 'growth'">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#FF9500]">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Aktif Saat Ini
                                        </span>
                                    </template>
                                </div>
                                <div>
                                    <h4 class="text-[15px] font-bold text-black dark:text-white">Growth (1-3 Cabang)</h4>
                                    <p class="text-[12px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
                                        Fokus pada pengawasan operasional, pencegahan kebocoran kas kasir (anti-fraud), dan resep HPP otomatis.
                                    </p>
                                </div>
                                <ul class="text-[11px] space-y-1.5 text-black/70 dark:text-white/70 pt-2 border-t border-black/5 dark:border-white/5">
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Buka / Tutup Shift Laci Kas Blind Close</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>PIN Otorisasi Supervisor untuk Void/Diskon</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Formula Resep (BOM) & HPP Otomatis</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Database Poin Loyalitas & WhatsApp CRM</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="pt-4 mt-3">
                                <template x-if="currentScaleTier === 'growth'">
                                    <button type="button" disabled class="w-full py-2 px-3 rounded-[10px] bg-[#FF9500]/15 text-[#FF9500] text-[12px] font-bold text-center cursor-default">
                                        Profil Aktif
                                    </button>
                                </template>
                                <template x-if="currentScaleTier !== 'growth'">
                                    <button type="button" @click="requestApplyTier('growth', 'UMKM Bertumbuh (Growth)')"
                                            class="w-full py-2 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#FF9500] hover:text-[#FF9500] text-[12px] font-semibold text-black dark:text-white transition-all shadow-sm">
                                        Terapkan Growth
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- TIER 3: CORPORATE -->
                        <div class="relative rounded-[14px] p-5 border transition-all flex flex-col justify-between"
                             :class="currentScaleTier === 'corporate' 
                                ? 'bg-[#AF52DE]/[0.03] dark:bg-[#AF52DE]/[0.08] border-[#AF52DE] ring-2 ring-[#AF52DE]/20 shadow-sm' 
                                : 'bg-black/[0.01] dark:bg-white/[0.02] border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20'">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#AF52DE]/15 text-[#8944AB] dark:text-[#BF5AF2]">
                                        <i data-lucide="building-2" class="w-3 h-3"></i> SCALE-UP
                                    </span>
                                    <template x-if="currentScaleTier === 'corporate'">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#AF52DE]">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Aktif Saat Ini
                                        </span>
                                    </template>
                                </div>
                                <div>
                                    <h4 class="text-[15px] font-bold text-black dark:text-white">Corporate (Scale-Up)</h4>
                                    <p class="text-[12px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
                                        Arsitektur lengkap untuk distributor regional, rantai pasok multi-gudang, dan kepatuhan akuntansi standar SAK EMKM.
                                    </p>
                                </div>
                                <ul class="text-[11px] space-y-1.5 text-black/70 dark:text-white/70 pt-2 border-t border-black/5 dark:border-white/5">
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Surat Jalan Resmi Supir (Delivery Order)</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Transfer Antar Multi-Gudang & Cabang</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Validasi Batas Plafon Piutang B2B</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                        <span>Akuntansi Lengkap: COA, Jurnal & Neraca</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="pt-4 mt-3">
                                <template x-if="currentScaleTier === 'corporate'">
                                    <button type="button" disabled class="w-full py-2 px-3 rounded-[10px] bg-[#AF52DE]/15 text-[#AF52DE] text-[12px] font-bold text-center cursor-default">
                                        Profil Aktif
                                    </button>
                                </template>
                                <template x-if="currentScaleTier !== 'corporate'">
                                    <button type="button" @click="requestApplyTier('corporate', 'Perusahaan Berkembang (Corporate)')"
                                            class="w-full py-2 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#AF52DE] hover:text-[#AF52DE] text-[12px] font-semibold text-black dark:text-white transition-all shadow-sm">
                                        Terapkan Corporate
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================= -->
                <!-- SUB-PANEL: 24 PARAMETER GRANULAR KUSTOM MANDIRI               -->
                <!-- ============================================================= -->
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-5 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-[8px] bg-[#34C759]/10 text-[#34C759]">
                                    <i data-lucide="sliders" class="w-4 h-4"></i>
                                </span>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">24 Parameter Granular Mandiri</h3>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                    Kustomisasi Bebas
                                </span>
                            </div>
                            <p class="text-[12px] text-black/55 dark:text-white/55 mt-1">
                                Atur setiap parameter operasional secara independen. Perubahan tersimpan otomatis secara real-time.
                            </p>
                        </div>

                        <!-- Search Parameter Input -->
                        <div class="relative w-full sm:w-64">
                            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                            <input type="text" x-model="featureSearch" placeholder="Cari nama parameter..."
                                   class="w-full pl-8 pr-3 py-1.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:border-[#007AFF] transition-all">
                        </div>
                    </div>

                    <!-- Cluster Grid of 24 Parameters -->
                    <div class="space-y-6">
                        @foreach($featureCategories as $catKey => $catMeta)
                            @php
                                $catFeatures = array_filter($featureDefinitions, fn($def) => $def['category'] === $catKey);
                            @endphp
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="p-1 rounded-[6px] bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                        <i data-lucide="{{ $catMeta['icon'] }}" class="w-3.5 h-3.5"></i>
                                    </span>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white">{{ $catMeta['name'] }}</h4>
                                    <span class="text-[11px] text-black/40 dark:text-white/40">({{ count($catFeatures) }} parameter)</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($catFeatures as $fKey => $fDef)
                                        <div x-show="!featureSearch || '{{ strtolower($fDef['name'] . ' ' . $fDef['description'] . ' ' . $fKey) }}'.includes(featureSearch.toLowerCase())"
                                             class="rounded-[12px] bg-black/[0.015] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-3.5 flex items-start justify-between gap-3 hover:bg-black/[0.03] dark:hover:bg-white/[0.04] transition-all">
                                            <div class="space-y-1 flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[13px] font-semibold text-black dark:text-white leading-tight">
                                                        {{ $fDef['name'] }}
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-black/50 dark:text-white/50 leading-relaxed line-clamp-2">
                                                    {{ $fDef['description'] }}
                                                </p>
                                                <div class="flex items-center gap-2 pt-1 text-[10px] text-black/40 dark:text-white/40 font-mono">
                                                    <span>{{ $fKey }}</span>
                                                </div>
                                            </div>

                                            <!-- iOS / Apple Toggle Switch -->
                                            <div class="shrink-0 pt-0.5">
                                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                                    <input type="checkbox"
                                                           :checked="featuresState['{{ $fKey }}']"
                                                           :disabled="featureSaving['{{ $fKey }}'] || !isOwner"
                                                           @change="toggleFeatureParam('{{ $fKey }}', $event.target.checked)"
                                                           class="sr-only peer">
                                                    <div class="w-10 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/15 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-[#34C759]"></div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- MODAL KONFIRMASI TERAPKAN TIER -->
                <div x-show="confirmTierModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;" x-cloak>
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="confirmTierModalOpen = false"></div>
                    <div class="relative w-full max-w-md rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2.5 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF]">
                                <i data-lucide="help-circle" class="w-6 h-6"></i>
                            </span>
                            <div>
                                <h3 class="text-[17px] font-bold text-black dark:text-white">Terapkan Profil Skala?</h3>
                                <p class="text-[12px] text-black/50 dark:text-white/50" x-text="targetTierName"></p>
                            </div>
                        </div>

                        <p class="text-[13px] text-black/70 dark:text-white/70 leading-relaxed">
                            Sistem akan menyesuaikan alur kerja kasir, otorisasi, dan modul fitur sesuai profil <strong x-text="targetTierName"></strong>. Seluruh riwayat transaksi, laporan, dan master data Anda tetap 100% aman dan utuh.
                        </p>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                            <button type="button" @click="confirmTierModalOpen = false"
                                    class="px-4 py-2 rounded-[10px] bg-black/5 dark:bg-white/10 text-[12px] font-semibold text-black dark:text-white hover:bg-black/10 transition-all">
                                Batal
                            </button>
                            <button type="button" @click="executeApplyTier()" :disabled="applyingTier"
                                    class="px-4 py-2 rounded-[10px] bg-[#007AFF] text-[12px] font-bold text-white hover:bg-[#007AFF]/90 transition-all inline-flex items-center gap-1.5 shadow-sm">
                                <template x-if="applyingTier">
                                    <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <span x-text="applyingTier ? 'Menerapkan...' : 'Ya, Terapkan Sekarang'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                @php
                                        $mod = $allModules[$moduleKey];
                                        $isEnabled = $business->isModuleEnabled($moduleKey);
                                        $impactText = $cluster['impacts'][$moduleKey] ?? null;
                                    @endphp
                                    <div
                                        class="rounded-[14px] p-4 sm:p-4.5 flex flex-col justify-between gap-3.5 transition-all hover:border-black/15 dark:hover:border-white/15"
                                        :class="modulesState['{{ $moduleKey }}'] ?
                                            'border border-[#34C759]/30 dark:border-[#34C759]/30 bg-[#34C759]/[0.02]' :
                                            'border border-black/5 dark:border-white/5 opacity-75 bg-black/[0.02] dark:bg-white/[0.02]'">
                                        <div class="space-y-2.5">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex items-center gap-2.5">
                                                    <div
                                                        class="w-9 h-9 rounded-[9px] flex items-center justify-center shrink-0 transition-colors"
                                                        :class="modulesState['{{ $moduleKey }}'] ?
                                                            'bg-[#007AFF]/10 text-[#007AFF]' :
                                                            'bg-black/5 dark:bg-white/5 text-black/40 dark:text-white/40'">
                                                        <i data-lucide="{{ $mod['icon'] }}" class="w-4.5 h-4.5"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-[14px] font-semibold text-black dark:text-white leading-snug">
                                                            {{ $mod['name'] }}
                                                        </h4>
                                                        <span class="text-[10.5px] text-black/40 dark:text-white/40 uppercase font-semibold">{{ $mod['category'] }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed min-h-[34px]">
                                                {{ $mod['description'] }}
                                            </p>

                                            <!-- Live Impact Badge -->
                                            @if ($impactText)
                                                <div class="rounded-[8px] bg-black/[0.03] dark:bg-white/[0.04] px-2.5 py-1.5 text-[11px] text-black/65 dark:text-white/65 flex items-start gap-1.5 border border-black/5 dark:border-white/5">
                                                    <i data-lucide="layout-panel-left" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 shrink-0 mt-0.5"></i>
                                                    <div class="line-clamp-2 leading-tight">
                                                        <strong class="font-medium text-black dark:text-white">{{ __('settings.lbl_sidebar_impact') }}</strong> {{ $impactText }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Apple Switch Toggle -->
                                        <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                            <span class="text-[11.5px] font-medium"
                                                :class="modulesState['{{ $moduleKey }}'] ? 'text-[#248A3D] dark:text-[#30D158]' : 'text-black/40 dark:text-white/40'"
                                                x-text="modulesState['{{ $moduleKey }}'] ? '{{ __('settings.module_active') }}' : '{{ __('settings.module_inactive') }}'">
                                                {{ $isEnabled ? __('settings.module_active') : __('settings.module_inactive') }}
                                            </span>

                                            <label class="relative inline-flex items-center cursor-pointer select-none min-h-[44px]">
                                                <input type="checkbox" name="enabled_modules[]" value="{{ $moduleKey }}"
                                                    x-model="modulesState['{{ $moduleKey }}']"
                                                    @change="toggleModule('{{ $moduleKey }}', $event.target.checked)"
                                                    :disabled="!isOwner || moduleSaving['{{ $moduleKey }}']"
                                                    {{ $isEnabled ? 'checked' : '' }}
                                                    {{ !\App\Support\Context::isOwner() ? 'disabled' : '' }} class="sr-only peer">
                                                <div
                                                    class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759] transition-colors">
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <!-- Bottom Action Footer -->
                @if (\App\Support\Context::isOwner())
                    <div
                        class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm">
                        <div class="flex items-center gap-2 text-[12px] text-black/50 dark:text-white/50">
                            <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span>{{ __('settings.module_autosave_notice') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-[12px] font-medium transition-all"
                                :class="{
                                    'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20': moduleSaveStatus === 'saved' || moduleSaveStatus === 'idle',
                                    'bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20': moduleSaveStatus === 'saving',
                                    'bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20': moduleSaveStatus === 'error'
                                }">
                                <template x-if="moduleSaveStatus === 'saving'">
                                    <svg class="animate-spin w-3.5 h-3.5 text-[#007AFF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <template x-if="moduleSaveStatus === 'saved' || moduleSaveStatus === 'idle'">
                                    <svg class="w-3.5 h-3.5 text-[#34C759]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </template>
                                <template x-if="moduleSaveStatus === 'error'">
                                    <svg class="w-3.5 h-3.5 text-[#FF3B30]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                                    </svg>
                                </template>
                                <span x-text="moduleSaveStatus === 'saving' ? '{{ __('settings.module_autosave_saving') }}' : (moduleSaveStatus === 'error' ? '{{ __('settings.module_autosave_error') }}' : '{{ __('settings.module_autosave_saved') }}')">
                                    {{ __('settings.module_autosave_saved') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>

        <!-- ===================================================== -->
        <!-- BENTO MODAL SHEET XXL (Terapkan Template Industri)    -->
        <!-- ===================================================== -->
        @if (\App\Support\Context::hasPermission('settings.edit'))
            <template x-teleport="body">
                <div x-show="templateModalOpen" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-sm overflow-y-auto"
                    role="dialog"
                    aria-modal="true"
                    @click.self="closeApplyTemplate()"
                    @keydown.escape.window="closeApplyTemplate()"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                    <div class="relative z-10 w-full max-w-lg rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_24px_64px_rgba(0,0,0,0.3)] p-6 space-y-5 text-left my-auto"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95">

                        <div class="flex items-start gap-3.5">
                            <div class="w-11 h-11 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="layers" class="w-5 h-5"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-[17px] font-semibold text-black dark:text-white">{{ __('settings.apply_template_title') }}</h3>
                                <p class="text-[13px] text-black/55 dark:text-white/55 mt-0.5 leading-snug">
                                    {{ __('settings.apply_template_desc') }}
                                </p>
                            </div>
                        </div>

                        <!-- Template Details Preview Card -->
                        <div class="p-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2">
                            <div class="flex items-center justify-between text-[12px]">
                                <span class="text-black/50 dark:text-white/50">{{ __('settings.lbl_selected_template') }}</span>
                                <span class="font-semibold text-black dark:text-white" x-text="selectedTemplate.name"></span>
                            </div>
                            <div class="flex items-center justify-between text-[12px]">
                                <span class="text-black/50 dark:text-white/50">{{ __('settings.lbl_industry_category') }}</span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#34C759]/10 text-[#34C759]" x-text="selectedTemplate.category"></span>
                            </div>
                        </div>

                        <!-- No-Panic Microcopy Notice -->
                        <div class="flex items-start gap-2.5 text-[12px] text-black/60 dark:text-white/60">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0 mt-0.5"></i>
                            <p>{{ __('settings.template_safety_notice') }}</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-black/5 dark:border-white/5">
                            <button type="button" @click="closeApplyTemplate()"
                                class="h-11 px-5 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 active:scale-[0.97] transition min-h-[44px]">
                                {{ __('settings.cancel') }}
                            </button>
                            <button type="button" @click="submitApplyTemplate()"
                                class="h-11 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition flex items-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)] min-h-[44px]">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>{{ __('settings.confirm_apply') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        @endif

        {{-- ===================================================== --}}
        {{-- MODAL 1: TAMBAH CABANG BARU (APPLE BENTO MAP SHEET)   --}}
        {{-- ===================================================== --}}
        <template x-teleport="body">
            <div x-show="showCreateBranchModal"
                 x-cloak
                 class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-sm overflow-y-auto"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="create-branch-modal-title"
                 @click.self="showCreateBranchModal = false"
                 @keydown.escape.window="showCreateBranchModal = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">

                <div class="relative z-10 w-full max-w-[95vw] lg:max-w-4xl xl:max-w-5xl max-h-[92vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden my-auto">

                    {{-- Header --}}
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-[14px] bg-[#34C759]/12 text-[#34C759] flex items-center justify-center shrink-0 border border-[#34C759]/20 shadow-sm">
                                <i data-lucide="store" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                            </div>
                            <div>
                                <p class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 mb-0.5">
                                    {{ __('settings.tab_branches') }} &bull; {{ __('settings.page_title') }}
                                </p>
                                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">{{ __('settings.create_branch_title') }}</h3>
                                <p class="text-xs text-black/55 dark:text-white/55 mt-0.5">{{ __('settings.modal_branch_create_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showCreateBranchModal = false"
                                class="min-h-[44px] min-w-[44px] rounded-[12px] text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center justify-center cursor-pointer"
                                aria-label="{{ __('settings.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    {{-- Scrollable Form --}}
                    <form action="{{ route('settings.branches.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden" @submit="branchSubmitting = true">
                        @csrf
                        <input type="hidden" name="type" value="outlet">

                        <div class="p-5 sm:p-6 overflow-y-auto space-y-6 flex-1">
                            @if ($errors->any())
                                <div class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
                                    <div class="font-bold mb-1.5 flex items-center gap-2">
                                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                                        <span>{{ __('settings.alert_fix_errors') }}</span>
                                    </div>
                                    <ul class="list-disc list-inside space-y-1 opacity-90 pl-1">
                                        @foreach ($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- ========================================================== --}}
                            {{-- BAGIAN 1: IDENTITAS CABANG & KONTAK                        --}}
                            {{-- ========================================================== --}}
                            <div class="space-y-4">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5 dark:border-white/5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center">1</span>
                                        <h3 class="text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">{{ __('settings.sec_branch_identity_contact') }}</h3>
                                    </div>
                                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">{{ __('settings.sec_branch_operational_data') }}</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="sm:col-span-1">
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                            {{ __('settings.branch_name') }} <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                                <i data-lucide="store" class="w-4 h-4"></i>
                                            </div>
                                            <input type="text" name="name" x-model="branchForm.name" required placeholder="{{ __('settings.ph_branch_name_sample') }}"
                                                   class="w-full pl-10 pr-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] sm:rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                            {{ __('settings.branch_code') }}
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                                <i data-lucide="hash" class="w-4 h-4"></i>
                                            </div>
                                            <input type="text" name="code" x-model="branchForm.code" placeholder="{{ __('settings.ph_branch_code_sample') }}"
                                                   class="w-full pl-10 pr-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] sm:rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] font-mono uppercase outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                            {{ __('settings.branch_phone') }}
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                                <i data-lucide="smartphone" class="w-4 h-4"></i>
                                            </div>
                                            <input type="tel" name="phone" x-model="branchForm.phone" placeholder="081234567890"
                                                   class="w-full pl-10 pr-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] sm:rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] font-mono outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ========================================================== --}}
                            {{-- BAGIAN 2: TITIK LOKASI & WILAYAH INDONESIA (LEAFLET.JS)   --}}
                            {{-- ========================================================== --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5 dark:border-white/5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center">2</span>
                                        <h3 class="text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">{{ __('settings.sec_branch_location_title') }}</h3>
                                    </div>
                                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">{{ __('settings.sec_village_subdistrict') }}</span>
                                </div>

                                <!-- Autocomplete Pencarian Cepat Wilayah Indonesia -->
                                <div class="relative">
                                    <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                        {{ __('settings.lbl_quick_search_area') }}
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                            <i data-lucide="search" class="w-4 h-4"></i>
                                        </div>
                                        <input type="text"
                                               x-model="branchSearchQuery"
                                               @input.debounce.350ms="searchBranchAreas('create')"
                                               @focus="if(branchSearchResults.length > 0) branchShowDropdown = true"
                                               class="w-full pl-10 pr-10 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] sm:rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] outline-none"
                                               placeholder="{{ __('settings.ph_postal_or_subdistrict') }}">
                                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center">
                                            <template x-if="branchIsSearching">
                                                <i data-lucide="loader-2" class="w-4 h-4 text-[#007AFF] animate-spin"></i>
                                            </template>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">
                                        {{ __('settings.desc_autocomplete_hint') }}
                                    </p>

                                    <!-- Dropdown Hasil Pencarian Wilayah -->
                                    <div x-show="branchShowDropdown && branchSearchResults.length > 0" x-cloak
                                         @click.outside="branchShowDropdown = false"
                                         class="absolute z-30 left-0 right-0 mt-1.5 max-h-60 overflow-y-auto bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[16px] shadow-2xl divide-y divide-black/5 dark:divide-white/5">
                                        <template x-for="item in branchSearchResults" :key="item.id || item.label">
                                            <button type="button"
                                                    @click="selectBranchArea('create', item)"
                                                    class="w-full px-4 py-3 text-left hover:bg-[#007AFF]/10 dark:hover:bg-[#0A84FF]/15 transition-colors flex items-start gap-3 cursor-pointer">
                                                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0 mt-0.5">
                                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[13px] font-semibold text-black dark:text-white truncate" x-text="item.village ? item.village + ', ' + item.district : item.label"></p>
                                                    <p class="text-[11.5px] text-black/55 dark:text-white/55" x-text="item.city + ', ' + item.province"></p>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-[6px] text-[11px] font-mono font-bold bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 shrink-0" x-text="item.postal_code || '-'"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Bento Grid Form Wilayah Administratif (Consistent Apple HIG Inputs) -->
                                <div class="p-4 sm:p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-3.5">
                                    <div class="flex items-center justify-between pb-1 border-b border-black/5 dark:border-white/5">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                            <i data-lucide="map-pinned" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                            {{ __('settings.sec_administrative_area') }}
                                        </span>
                                        <span class="text-[10.5px] font-medium text-black/45 dark:text-white/45">{{ __('settings.lbl_can_edit_manually') }}</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                {{ __('settings.lbl_province') }} <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="province" x-model="branchForm.province" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="{{ __('settings.ph_province_sample') }}">
                                        </div>

                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                {{ __('settings.lbl_city') }} <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="city" x-model="branchForm.city" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="{{ __('settings.ph_city_sample') }}">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                {{ __('settings.lbl_district') }} <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="district" x-model="branchForm.district" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="{{ __('settings.ph_district_sample') }}">
                                        </div>

                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                {{ __('settings.lbl_village') }} <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="village" x-model="branchForm.village" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="{{ __('settings.ph_subdistrict_sample') }}">
                                        </div>

                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                {{ __('settings.lbl_postal_code') }} <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="postal_code" x-model="branchForm.postal_code" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none font-mono tabular-nums"
                                                   placeholder="{{ __('settings.ph_postal_code_sample') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Detail Alamat Jalan / Nomor Bangunan -->
                                <div>
                                    <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                        {{ __('settings.lbl_street_address_detail') }} <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <textarea name="address" rows="2" required x-model="branchForm.address"
                                              class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] sm:rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] outline-none resize-none"
                                              placeholder="{{ __('settings.ph_address_sample') }}"></textarea>
                                </div>

                                <!-- Peta Interaktif Leaflet.js -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs">
                                            {{ __('settings.lbl_gps_map_pin') }} <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <button type="button"
                                                @click="useCurrentBranchGps('create')"
                                                :disabled="branchGpsLoading"
                                                class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                                            <i data-lucide="crosshair" class="w-3.5 h-3.5" :class="{'animate-spin': branchGpsLoading}"></i>
                                            <span x-text="branchGpsLoading ? '{{ addslashes(__('settings.lbl_searching_gps')) }}' : '{{ addslashes(__('settings.lbl_use_my_gps')) }}'"></span>
                                        </button>
                                    </div>

                                    <!-- Map Canvas Container (Apple HIG Squircle) -->
                                    <div class="relative rounded-[16px] sm:rounded-[20px] overflow-hidden border border-black/10 dark:border-white/10 shadow-sm">
                                        <div id="branch-create-map" class="w-full h-[260px] sm:h-[300px] bg-black/5 dark:bg-white/5 z-0"></div>
                                        <div class="absolute bottom-2.5 left-2.5 z-10 px-3 py-1.5 rounded-[10px] sm:rounded-[12px] bg-white/95 dark:bg-black/85 backdrop-blur-md border border-black/10 dark:border-white/10 text-[11px] font-mono text-black/70 dark:text-white/70 shadow-md flex items-center gap-2">
                                            <span class="font-bold text-[#007AFF] dark:text-[#0A84FF]">GPS:</span>
                                            <span x-text="branchForm.latitude ? Number(branchForm.latitude).toFixed(5) : '-'"></span>,
                                            <span x-text="branchForm.longitude ? Number(branchForm.longitude).toFixed(5) : '-'"></span>
                                            <span class="text-black/30 dark:text-white/30">&bull;</span>
                                            <span class="text-[#34C759] font-bold">{{ __('settings.lbl_radius_label') }} <span x-text="branchForm.geofence_radius_meters"></span>m</span>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">
                                        {{ __('settings.desc_gps_map_hint') }}
                                    </p>
                                </div>

                                <!-- Slider Geofence Radius Presensi -->
                                <div class="p-3.5 rounded-[14px] sm:rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-semibold text-black/80 dark:text-white/80">{{ __('settings.branch_geofence_radius') }}</span>
                                        <span class="font-bold text-[#007AFF] font-mono"><span x-text="branchForm.geofence_radius_meters"></span> {{ __('settings.unit_meter') }}</span>
                                    </div>
                                    <input type="range" name="geofence_radius_meters" min="20" max="1000" step="10"
                                           x-model="branchForm.geofence_radius_meters"
                                           @input="updateGeofenceCircle('create')"
                                           class="w-full accent-[#007AFF] cursor-pointer">
                                    <p class="text-[10.5px] text-black/45 dark:text-white/45">{{ __('settings.geofence_attendance_tolerance_desc') }}</p>
                                </div>

                                <!-- Hidden Form Inputs for Geolocation Metadata -->
                                <input type="hidden" name="latitude" :value="branchForm.latitude">
                                <input type="hidden" name="longitude" :value="branchForm.longitude">
                                <input type="hidden" name="biteship_area_id" :value="branchForm.biteship_area_id">
                            </div>

                            {{-- ========================================================== --}}
                            {{-- BAGIAN 3: PENGATURAN OPERASIONAL & TOKO ONLINE             --}}
                            {{-- ========================================================== --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5 dark:border-white/5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center">3</span>
                                        <h3 class="text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">{{ __('settings.sec_branch_operational_online') }}</h3>
                                    </div>
                                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">{{ __('settings.sec_fulfillment_attendance') }}</span>
                                </div>

                                <div class="space-y-3">
                                    <label class="flex items-start gap-3 p-3.5 min-h-[44px] rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] cursor-pointer hover:border-[#FF9500]/40 transition">
                                        <input type="checkbox" name="is_primary" value="1" x-model="branchForm.is_primary" class="mt-0.5 w-4 h-4 rounded text-[#FF9500] focus:ring-[#FF9500]">
                                        <div class="text-xs">
                                            <span class="font-bold text-black dark:text-white block">{{ __('settings.lbl_set_primary_branch') }}</span>
                                            <span class="text-[11px] text-black/55 dark:text-white/55 block mt-0.5">{{ __('settings.desc_primary_branch') }}</span>
                                        </div>
                                    </label>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <label class="flex items-start gap-2.5 p-3 min-h-[44px] rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] cursor-pointer">
                                            <input type="checkbox" name="is_online_fulfillment" value="1" x-model="branchForm.is_online_fulfillment" class="mt-0.5 w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                            <div class="text-xs">
                                                <span class="font-semibold text-black/85 dark:text-white/85 block">{{ __('settings.lbl_online_shipping_origin') }}</span>
                                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">{{ __('settings.desc_online_shipping_origin') }}</span>
                                            </div>
                                        </label>
                                        <label class="flex items-start gap-2.5 p-3 min-h-[44px] rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] cursor-pointer">
                                            <input type="checkbox" name="allow_storefront_pickup" value="1" x-model="branchForm.allow_storefront_pickup" class="mt-0.5 w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                            <div class="text-xs">
                                                <span class="font-semibold text-black/85 dark:text-white/85 block">{{ __('settings.lbl_allow_self_pickup') }}</span>
                                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">{{ __('settings.desc_allow_self_pickup') }}</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                            <button type="button" @click="showCreateBranchModal = false"
                                    class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs sm:text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 active:scale-[0.98] transition cursor-pointer">
                                {{ __('settings.cancel') }}
                            </button>
                            <button type="submit" :disabled="branchSubmitting"
                                    class="min-h-[44px] h-11 px-6 rounded-[12px] text-xs sm:text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-[0_1px_3px_rgba(0,122,255,0.3)] flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                <span x-text="branchSubmitting ? '{{ addslashes(__('settings.btn_saving')) }}' : '{{ addslashes(__('settings.btn_save_branch')) }}'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- ===================================================== --}}
        {{-- MODAL 2: EDIT DATA CABANG (APPLE BENTO MAP SHEET)     --}}
        {{-- ===================================================== --}}
        <template x-teleport="body">
            <div x-show="showEditBranchModal"
                 x-cloak
                 class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-sm overflow-y-auto"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="edit-branch-modal-title"
                 @click.self="showEditBranchModal = false"
                 @keydown.escape.window="showEditBranchModal = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">

                <div class="relative z-10 w-full max-w-[95vw] lg:max-w-4xl xl:max-w-5xl max-h-[92vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden my-auto">

                    {{-- Header --}}
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/20 shadow-sm">
                                <i data-lucide="pencil" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                            </div>
                            <div>
                                <p class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 mb-0.5">
                                    {{ __('settings.tab_branches') }} &bull; {{ __('settings.page_title') }}
                                </p>
                                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">{{ __('settings.edit_branch_title') }}</h3>
                                <p class="text-xs text-black/55 dark:text-white/55 mt-0.5">{{ __('settings.modal_branch_edit_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showEditBranchModal = false"
                                class="min-h-[44px] min-w-[44px] rounded-[12px] text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center justify-center cursor-pointer"
                                aria-label="{{ __('settings.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    {{-- Scrollable Form --}}
                    <form :action="branchEditUrl" method="POST" class="flex flex-col flex-1 overflow-hidden" @submit="branchSubmitting = true">
                        @csrf
                        @method('PUT')

                        <div class="p-5 sm:p-6 overflow-y-auto space-y-6 flex-1">
                            @if ($errors->any())
                                <div class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
                                    <div class="font-bold mb-1.5 flex items-center gap-2">
                                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                                        <span>{{ __('settings.alert_fix_errors') }}</span>
                                    </div>
                                    <ul class="list-disc list-inside space-y-1 opacity-90 pl-1">
                                        @foreach ($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- ========================================================== --}}
                            {{-- BAGIAN 1: IDENTITAS CABANG & KONTAK                        --}}
                            {{-- ========================================================== --}}
                            <div class="space-y-4">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5 dark:border-white/5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center">1</span>
                                        <h3 class="text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">{{ __('settings.sec_branch_identity_contact') }}</h3>
                                    </div>
                                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">{{ __('settings.sec_branch_operational_data') }}</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="sm:col-span-1">
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                            {{ __('settings.branch_name') }} <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                                <i data-lucide="store" class="w-4 h-4"></i>
                                            </div>
                                            <input type="text" name="name" x-model="branchEditData.name" required
                                                   class="w-full pl-10 pr-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                            {{ __('settings.branch_code') }}
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                                <i data-lucide="hash" class="w-4 h-4"></i>
                                            </div>
                                            <input type="text" name="code" x-model="branchEditData.code" placeholder="{{ __('settings.ph_branch_code_sample') }}"
                                                   class="w-full pl-10 pr-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] font-mono uppercase outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                            {{ __('settings.branch_phone') }}
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                                <i data-lucide="smartphone" class="w-4 h-4"></i>
                                            </div>
                                            <input type="tel" name="phone" x-model="branchEditData.phone" placeholder="081234567890"
                                                   class="w-full pl-10 pr-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] font-mono outline-none">
                                        </div>
                                    </div>
                                </div>

                                <!-- Sakelar Status Cabang Aktif -->
                                <div class="pt-1">
                                    <label class="flex items-center gap-3 p-3 min-h-[44px] rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" x-model="branchEditData.is_active" class="w-4 h-4 rounded text-[#34C759] focus:ring-[#34C759]">
                                        <span class="text-xs font-semibold text-black/85 dark:text-white/85">{{ __('settings.lbl_branch_active_toggle') }}</span>
                                    </label>
                                </div>
                            </div>

                            {{-- ========================================================== --}}
                            {{-- BAGIAN 2: TITIK LOKASI & WILAYAH INDONESIA (LEAFLET.JS)   --}}
                            {{-- ========================================================== --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5 dark:border-white/5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center">2</span>
                                        <h3 class="text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">{{ __('settings.sec_branch_location_title') }}</h3>
                                    </div>
                                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">{{ __('settings.sec_village_subdistrict') }}</span>
                                </div>

                                <!-- Autocomplete Pencarian Cepat Wilayah Indonesia -->
                                <div class="relative">
                                    <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                        {{ __('settings.lbl_quick_search_area') }}
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                            <i data-lucide="search" class="w-4 h-4"></i>
                                        </div>
                                        <input type="text"
                                               x-model="branchSearchQuery"
                                               @input.debounce.350ms="searchBranchAreas('edit')"
                                               @focus="if(branchSearchResults.length > 0) branchShowDropdown = true"
                                               class="w-full pl-10 pr-10 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] outline-none"
                                               placeholder="{{ __('settings.ph_postal_or_subdistrict') }}">
                                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center">
                                            <template x-if="branchIsSearching">
                                                <i data-lucide="loader-2" class="w-4 h-4 text-[#007AFF] animate-spin"></i>
                                            </template>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">
                                        {{ __('settings.desc_autocomplete_hint') }}
                                    </p>

                                    <!-- Dropdown Hasil Pencarian Wilayah -->
                                    <div x-show="branchShowDropdown && branchSearchResults.length > 0" x-cloak
                                         @click.outside="branchShowDropdown = false"
                                         class="absolute z-30 left-0 right-0 mt-1.5 max-h-60 overflow-y-auto bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[18px] shadow-2xl divide-y divide-black/5 dark:divide-white/5">
                                        <template x-for="item in branchSearchResults" :key="item.id || item.label">
                                            <button type="button"
                                                    @click="selectBranchArea('edit', item)"
                                                    class="w-full px-4 py-3 text-left hover:bg-[#007AFF]/10 dark:hover:bg-[#0A84FF]/15 transition-colors flex items-start gap-3 cursor-pointer">
                                                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0 mt-0.5">
                                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[13px] font-semibold text-black dark:text-white truncate" x-text="item.village ? item.village + ', ' + item.district : item.label"></p>
                                                    <p class="text-[11.5px] text-black/55 dark:text-white/55" x-text="item.city + ', ' + item.province"></p>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-[6px] text-[11px] font-mono font-bold bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 shrink-0" x-text="item.postal_code || '-'"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Bento Grid Form Wilayah Administratif (Visible & Editable) -->
                                <div class="p-4 sm:p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-3.5">
                                    <div class="flex items-center justify-between pb-1 border-b border-black/5 dark:border-white/5">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                            <i data-lucide="map-pinned" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                            {{ __('settings.sec_administrative_area') }}
                                        </span>
                                        <span class="text-[10.5px] font-medium text-black/45 dark:text-white/45">{{ __('settings.lbl_can_edit_manually') }}</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                {{ __('settings.lbl_province') }} <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="province" x-model="branchEditData.province" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="{{ __('settings.ph_province_sample') }}">
                                        </div>

                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                {{ __('settings.lbl_city') }} <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="city" x-model="branchEditData.city" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="{{ __('settings.ph_city_sample') }}">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                {{ __('settings.lbl_district') }} <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="district" x-model="branchEditData.district" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="{{ __('settings.ph_district_sample') }}">
                                        </div>

                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                {{ __('settings.lbl_village') }} <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="village" x-model="branchEditData.village" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none"
                                                   placeholder="{{ __('settings.ph_subdistrict_sample') }}">
                                        </div>

                                        <div>
                                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                                {{ __('settings.lbl_postal_code') }} <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <input type="text" name="postal_code" x-model="branchEditData.postal_code" required
                                                   class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs outline-none font-mono"
                                                   placeholder="{{ __('settings.ph_postal_code_sample') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Detail Alamat Jalan / Nomor Bangunan -->
                                <div>
                                    <label class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1.5">
                                        {{ __('settings.lbl_street_address_detail') }} <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <textarea name="address" rows="2" required x-model="branchEditData.address"
                                              class="w-full px-3.5 py-2.5 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-xs sm:text-[13px] outline-none resize-none"
                                              placeholder="{{ __('settings.ph_address_sample') }}"></textarea>
                                </div>

                                <!-- Peta Interaktif Leaflet.js -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block font-semibold text-black/80 dark:text-white/85 text-xs">
                                            {{ __('settings.lbl_gps_map_pin') }} <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <button type="button"
                                                @click="useCurrentBranchGps('edit')"
                                                :disabled="branchGpsLoading"
                                                class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                                            <i data-lucide="crosshair" class="w-3.5 h-3.5" :class="{'animate-spin': branchGpsLoading}"></i>
                                            <span x-text="branchGpsLoading ? '{{ addslashes(__('settings.lbl_searching_gps')) }}' : '{{ addslashes(__('settings.lbl_use_my_gps')) }}'"></span>
                                        </button>
                                    </div>

                                    <!-- Map Canvas Container (Apple HIG Squircle) -->
                                    <div class="relative rounded-[20px] overflow-hidden border border-black/10 dark:border-white/10 shadow-sm">
                                        <div id="branch-edit-map" class="w-full h-[260px] sm:h-[300px] bg-black/5 dark:bg-white/5 z-0"></div>
                                        <div class="absolute bottom-2.5 left-2.5 z-10 px-3 py-1.5 rounded-[12px] bg-white/95 dark:bg-black/85 backdrop-blur-md border border-black/10 dark:border-white/10 text-[11px] font-mono text-black/70 dark:text-white/70 shadow-md flex items-center gap-2">
                                            <span class="font-bold text-[#007AFF] dark:text-[#0A84FF]">GPS:</span>
                                            <span x-text="branchEditData.latitude ? Number(branchEditData.latitude).toFixed(5) : '-'"></span>,
                                            <span x-text="branchEditData.longitude ? Number(branchEditData.longitude).toFixed(5) : '-'"></span>
                                            <span class="text-black/30 dark:text-white/30">&bull;</span>
                                            <span class="text-[#34C759] font-bold">{{ __('settings.lbl_radius_label') }} <span x-text="branchEditData.geofence_radius_meters"></span>m</span>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">
                                        {{ __('settings.desc_gps_map_hint') }}
                                    </p>
                                </div>

                                <!-- Slider Geofence Radius Presensi -->
                                <div class="p-3.5 rounded-[14px] sm:rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-semibold text-black/80 dark:text-white/80">{{ __('settings.branch_geofence_radius') }}</span>
                                        <span class="font-bold text-[#007AFF] font-mono"><span x-text="branchEditData.geofence_radius_meters"></span> {{ __('settings.unit_meter') }}</span>
                                    </div>
                                    <input type="range" name="geofence_radius_meters" min="20" max="1000" step="10"
                                           x-model="branchEditData.geofence_radius_meters"
                                           @input="updateGeofenceCircle('edit')"
                                           class="w-full accent-[#007AFF] cursor-pointer">
                                    <p class="text-[10.5px] text-black/45 dark:text-white/45">{{ __('settings.geofence_attendance_tolerance_desc') }}</p>
                                </div>

                                <!-- Hidden Form Inputs for Geolocation Metadata -->
                                <input type="hidden" name="latitude" :value="branchEditData.latitude">
                                <input type="hidden" name="longitude" :value="branchEditData.longitude">
                                <input type="hidden" name="biteship_area_id" :value="branchEditData.biteship_area_id">
                            </div>

                            {{-- ========================================================== --}}
                            {{-- BAGIAN 3: PENGATURAN OPERASIONAL & TOKO ONLINE             --}}
                            {{-- ========================================================== --}}
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5 dark:border-white/5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center">3</span>
                                        <h3 class="text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">{{ __('settings.sec_branch_operational_online') }}</h3>
                                    </div>
                                    <span class="text-[11px] font-medium text-black/45 dark:text-white/45">{{ __('settings.sec_fulfillment_attendance') }}</span>
                                </div>

                                <div class="space-y-3">
                                    <label class="flex items-start gap-3 p-3.5 min-h-[44px] rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] cursor-pointer hover:border-[#FF9500]/40 transition">
                                        <input type="checkbox" name="is_primary" value="1" x-model="branchEditData.is_primary" class="mt-0.5 w-4 h-4 rounded text-[#FF9500] focus:ring-[#FF9500]">
                                        <div class="text-xs">
                                            <span class="font-bold text-black dark:text-white block">{{ __('settings.lbl_set_primary_branch') }}</span>
                                            <span class="text-[11px] text-black/55 dark:text-white/55 block mt-0.5">{{ __('settings.desc_primary_branch') }}</span>
                                        </div>
                                    </label>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <label class="flex items-start gap-2.5 p-3 min-h-[44px] rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] cursor-pointer">
                                            <input type="checkbox" name="is_online_fulfillment" value="1" x-model="branchEditData.is_online_fulfillment" class="mt-0.5 w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                            <div class="text-xs">
                                                <span class="font-semibold text-black/85 dark:text-white/85 block">{{ __('settings.lbl_online_shipping_origin') }}</span>
                                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">{{ __('settings.desc_online_shipping_origin') }}</span>
                                            </div>
                                        </label>
                                        <label class="flex items-start gap-2.5 p-3 min-h-[44px] rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.08] cursor-pointer">
                                            <input type="checkbox" name="allow_storefront_pickup" value="1" x-model="branchEditData.allow_storefront_pickup" class="mt-0.5 w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                            <div class="text-xs">
                                                <span class="font-semibold text-black/85 dark:text-white/85 block">{{ __('settings.lbl_allow_self_pickup') }}</span>
                                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">{{ __('settings.desc_allow_self_pickup') }}</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0 bg-slate-50/50 dark:bg-white/[0.02]">
                            <button type="button" @click="showEditBranchModal = false"
                                    class="min-h-[44px] h-11 px-5 rounded-[12px] text-xs sm:text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 active:scale-[0.98] transition cursor-pointer">
                                {{ __('settings.cancel') }}
                            </button>
                            <button type="submit" :disabled="branchSubmitting"
                                    class="min-h-[44px] h-11 px-6 rounded-[12px] text-xs sm:text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-[0_1px_3px_rgba(0,122,255,0.3)] flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span x-text="branchSubmitting ? '{{ addslashes(__('settings.btn_saving')) }}' : '{{ addslashes(__('settings.btn_save_changes')) }}'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- ===================================================== --}}
        {{-- MODAL 3: KONFIRMASI HAPUS CABANG (APPLE ALERT)        --}}
        {{-- ===================================================== --}}
        <template x-teleport="body">
            <div x-show="deleteBranchModalOpen"
                 x-cloak
                 class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-sm overflow-y-auto"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="delete-branch-modal-title"
                 @click.self="closeDeleteBranch()"
                 @keydown.escape.window="closeDeleteBranch()"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                <div class="relative z-10 w-full max-w-sm rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] shadow-2xl overflow-hidden text-center my-auto"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    <div class="p-6">
                        <div class="w-12 h-12 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3.5">
                            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                        </div>
                        <p class="text-base font-bold text-black dark:text-white">{{ __('settings.delete_branch_confirm_title') }}</p>
                        <p class="text-xs text-black/60 dark:text-white/60 mt-1.5 leading-relaxed">
                            {{ __('settings.delete_branch_warning_prefix') }} <span x-text="branchDeleteTarget.name" class="font-bold text-black dark:text-white"></span> {{ __('settings.delete_branch_warning_suffix') }}
                        </p>
                    </div>

                    <form x-ref="deleteBranchForm" :action="branchDeleteUrl" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>

                    <div class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.12] text-xs font-semibold">
                        <button type="button" @click="closeDeleteBranch()"
                                class="min-h-[44px] py-3.5 text-black/70 dark:text-white/70 border-r border-black/[0.08] dark:border-white/[0.12] active:bg-black/5 dark:active:bg-white/5 transition cursor-pointer flex items-center justify-center">
                            {{ __('settings.cancel') }}
                        </button>
                        <button type="button" @click="submitDeleteBranch()"
                                class="min-h-[44px] py-3.5 text-[#FF3B30] dark:text-[#FF453A] font-bold active:bg-black/5 dark:active:bg-white/5 transition cursor-pointer flex items-center justify-center">
                            {{ __('settings.delete_branch') }}
                        </button>
                    </div>
                </div>
            </div>
        </template>

        {{-- MODAL SUPERVISOR PIN DISCONNECT WHATSAPP WABA --}}
        <template x-teleport="body">
            <div x-show="showDisconnectModal" x-cloak
                role="dialog" aria-modal="true" aria-labelledby="disconnect-modal-title"
                @keydown.escape.window="showDisconnectModal = false"
                class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/70 dark:bg-black/85"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                <div @click.away="showDisconnectModal = false"
                    class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-4"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">
                    
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#FF3B30]/12 text-[#FF3B30] flex items-center justify-center shrink-0">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 id="disconnect-modal-title" class="text-[16px] font-bold text-black dark:text-white">{{ __('whatsapp.disconnect_supervisor_title') }}</h3>
                            <p class="text-[12px] text-black/55 dark:text-white/55">{{ __('whatsapp.disconnect_supervisor_modal_sub') }}</p>
                        </div>
                    </div>

                    <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                        {{ __('whatsapp.disconnect_supervisor_desc') }}
                    </p>

                    <div class="space-y-1.5">
                        <label class="block text-[11.5px] font-bold text-black/60 dark:text-white/60 uppercase">{{ __('whatsapp.supervisor_pin_label') }}</label>
                        <input type="password" maxlength="6" inputmode="numeric" x-model="disconnectPin"
                            @keydown.enter.prevent="submitDisconnectWithPin()"
                            placeholder="••••••"
                            class="w-full h-12 text-center tracking-[0.5em] text-xl font-mono rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/15 focus:outline-none focus:ring-2 focus:ring-[#FF3B30] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30">
                    </div>

                    <template x-if="disconnectError">
                        <div class="p-3 rounded-[10px] bg-[#FF3B30]/10 text-[#C41E17] dark:text-[#FF453A] text-xs font-semibold flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span x-text="disconnectError"></span>
                        </div>
                    </template>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="showDisconnectModal = false"
                            class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition">
                            {{ __('whatsapp.cancel_btn') }}
                        </button>
                        <button type="button" @click="submitDisconnectWithPin()" :disabled="disconnectLoading || !disconnectPin.trim()"
                            class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.98] transition shadow-md shadow-[#FF3B30]/25 disabled:opacity-50 flex items-center gap-2">
                            <i data-lucide="unlink" class="w-4 h-4"></i>
                            <span x-text="disconnectLoading ? '{{ __('whatsapp.verifying') }}' : '{{ __('whatsapp.disconnect_action_btn') }}'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        function settingsPage() {
            return {
                activeTab: @json(request('tab', session('active_tab', 'general'))),
                templateSearch: '',
                selectedCategory: 'all',

                // WhatsApp WABA & Receipt Gateway state
                metaAccount: @json($whatsappAccount),
                waSession: @json($waSession ?? null),
                embeddedLoading: false,
                embeddedSuccess: null,
                embeddedError: null,
                metaEmbeddedPhoneId: null,
                metaEmbeddedWabaId: null,
                showDisconnectModal: false,
                disconnectPin: '',
                disconnectError: '',
                disconnectLoading: false,
                testPhone: '',
                testMessage: '',
                testLoading: false,
                testResult: '',
                testOk: false,

                // Social Media Omnichannel state
                socialLoading: false,
                socialErrorMessage: '',
                socialSuccessMessage: '',
                disconnectedSocialIds: [],
                logoPreview: @json($business->logo_url),
                roundingStrategy: @json($business->rounding_strategy ?? 'ROUND_100'),
                templateModalOpen: false,
                selectedTemplate: {
                    code: '',
                    name: '',
                    category: ''
                },

                // Operations & Timezone state
                operatingHours: @json($operatingHours),
                selectedTimezone: @json($currentTimezone),

                // Modules Management Reactive & Auto-Save State
                isOwner: @json(\App\Support\Context::isOwner()),
                totalModulesCount: {{ count($allModules) }},
                activeModulesCount: {{ count($allModules) - count($business->disabled_modules ?? []) }},
                modulesState: {
                    @foreach($allModules as $mKey => $mDef)
                        '{{ $mKey }}': {{ $business->isModuleEnabled($mKey) ? 'true' : 'false' }},
                    @endforeach
                },
                moduleSaving: {},
                moduleSaveStatus: 'idle', // 'idle' | 'saving' | 'saved' | 'error'
                saveStatusTimer: null,
                // Progressive Disclosure & Scaling Presets
                currentScaleTier: @json($currentScalingTier ?? $business->getScalingTier()),
                applyingTier: false,
                confirmTierModalOpen: false,
                targetTierToApply: '',
                targetTierName: '',

                // 24 Granular Parameters State
                featureSearch: '',
                featureSaving: {},
                featuresState: {
                    @foreach(\App\Domain\Template\FeatureRegistry::allKeys() as $fKey)
                        '{{ $fKey }}': {{ $business->isFeatureEnabled($fKey) ? 'true' : 'false' }},
                    @endforeach
                },

                async toggleModule(moduleKey, isChecked) {
                    if (!this.isOwner) return;

                    this.moduleSaving[moduleKey] = true;
                    this.moduleSaveStatus = 'saving';
                    if (this.saveStatusTimer) clearTimeout(this.saveStatusTimer);

                    // Optimistic update of counter
                    this.activeModulesCount = Object.values(this.modulesState).filter(Boolean).length;

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                            || document.querySelector('input[name="_token"]')?.value;

                        const response = await fetch('{{ route('settings.modules.update') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                _method: 'PUT',
                                module_key: moduleKey,
                                enabled: isChecked,
                            }),
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.moduleSaveStatus = 'saved';
                            if (typeof data.enabled_count === 'number') {
                                this.activeModulesCount = data.enabled_count;
                            }
                            if (window.AppAlert && data.message) {
                                window.AppAlert.toast('success', data.message, { duration: 3000 });
                            }
                            this.saveStatusTimer = setTimeout(() => {
                                if (this.moduleSaveStatus === 'saved') {
                                    this.moduleSaveStatus = 'idle';
                                }
                            }, 2500);
                        } else {
                            throw new Error(data.message || '{{ __('settings.module_save_failed_msg') }}');
                        }
                    } catch (err) {
                        console.error('Module auto-save error:', err);
                        // Revert checkbox & counter state
                        this.modulesState[moduleKey] = !isChecked;
                        this.activeModulesCount = Object.values(this.modulesState).filter(Boolean).length;
                        this.moduleSaveStatus = 'error';

                        if (window.AppAlert) {
                            window.AppAlert.toast('error', err.message || '{{ __('settings.module_save_failed_msg') }}');
                        }
                        this.saveStatusTimer = setTimeout(() => {
                            if (this.moduleSaveStatus === 'error') {
                                this.moduleSaveStatus = 'idle';
                            }
                        }, 4000);
                    } finally {
                        this.moduleSaving[moduleKey] = false;
                    }
                },

                requestApplyTier(tierKey, tierName) {
                    this.targetTierToApply = tierKey;
                    this.targetTierName = tierName;
                    this.confirmTierModalOpen = true;
                },

                async executeApplyTier() {
                    if (!this.isOwner || this.applyingTier) return;
                    this.applyingTier = true;

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            || document.querySelector('input[name="_token"]')?.value;

                        const response = await fetch('{{ route('settings.features.apply-tier') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                tier: this.targetTierToApply,
                            }),
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.currentScaleTier = data.tier;
                            if (data.features) {
                                this.featuresState = { ...this.featuresState, ...data.features };
                            }
                            if (window.AppAlert && data.message) {
                                window.AppAlert.toast('success', data.message, { duration: 4000 });
                            }
                            this.confirmTierModalOpen = false;
                            setTimeout(() => {
                                window.location.reload();
                            }, 800);
                        } else {
                            throw new Error(data.message || 'Gagal menerapkan profil skala.');
                        }
                    } catch (err) {
                        console.error('Apply tier error:', err);
                        if (window.AppAlert) {
                            window.AppAlert.toast('error', err.message || 'Terjadi kesalahan sistem.');
                        } else {
                            alert(err.message || 'Terjadi kesalahan sistem.');
                        }
                    } finally {
                        this.applyingTier = false;
                    }
                },

                async toggleFeatureParam(featureKey, isChecked) {
                    if (!this.isOwner) return;

                    this.featureSaving[featureKey] = true;
                    this.featuresState[featureKey] = isChecked;

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            || document.querySelector('input[name="_token"]')?.value;

                        const response = await fetch('{{ route('settings.features.toggle') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                feature_key: featureKey,
                                enabled: isChecked,
                            }),
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            if (window.AppAlert && data.message) {
                                window.AppAlert.toast('success', data.message, { duration: 3000 });
                            }
                        } else {
                            throw new Error(data.message || 'Gagal menyimpan parameter fitur.');
                        }
                    } catch (err) {
                        console.error('Feature toggle error:', err);
                        this.featuresState[featureKey] = !isChecked;
                        if (window.AppAlert) {
                            window.AppAlert.toast('error', err.message || 'Gagal mengubah parameter.');
                        } else {
                            alert(err.message || 'Gagal mengubah parameter.');
                        }
                    } finally {
                        this.featureSaving[featureKey] = false;
                    }
                },


                init() {
                    // Deep-linking URL synchronization on tab change without page reload
                    this.$watch('activeTab', (val) => {
                        const url = new URL(window.location);
                        url.searchParams.set('tab', val);
                        window.history.replaceState({}, '', url);
                    });

                    // Check for OAuth or Embedded code in URL params
                    const urlParams = new URLSearchParams(window.location.search);
                    const authCode = urlParams.get('code');
                    if (authCode) {
                        if (this.activeTab === 'social_media' || this.activeTab === 'social' || this.activeTab === 'channels') {
                            this.handleOAuthCode(authCode);
                        } else if (this.activeTab === 'whatsapp' || this.activeTab === 'wa_receipt') {
                            this.exchangeEmbeddedCode(authCode);
                        }
                    }

                    // Listen for Meta Embedded Signup PostMessage
                    window.addEventListener('message', (event) => {
                        if (event.origin !== "https://www.facebook.com" && event.origin !== "https://web.facebook.com") return;
                        try {
                            const data = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;
                            if (data.type === 'WA_EMBEDDED_SIGNUP') {
                                if (data.event === 'FINISH' && data.data) {
                                    this.metaEmbeddedPhoneId = data.data.phone_number_id || null;
                                    this.metaEmbeddedWabaId = data.data.waba_id || null;
                                }
                            }
                        } catch (e) {}
                    });

                    if (this.showCreateBranchModal) {
                        this.$nextTick(() => {
                            setTimeout(() => {
                                this.initBranchCreateMap();
                            }, 350);
                        });
                    }
                },

                addPeriod(dayKey) {
                    if (!this.operatingHours[dayKey]) {
                        this.operatingHours[dayKey] = { day_name: dayKey, is_open: true, periods: [] };
                    }
                    if (!Array.isArray(this.operatingHours[dayKey].periods)) {
                        this.operatingHours[dayKey].periods = [];
                    }
                    this.operatingHours[dayKey].periods.push({ start: '17:00', end: '22:00' });
                },

                removePeriod(dayKey, index) {
                    if (this.operatingHours[dayKey] && Array.isArray(this.operatingHours[dayKey].periods)) {
                        this.operatingHours[dayKey].periods.splice(index, 1);
                    }
                },

                isOvernight(period) {
                    if (!period || !period.start || !period.end) return false;
                    return period.end < period.start;
                },

                // WhatsApp Receipt CMS & WABA state
                previewMode: 'waba',
                receiptFooterNote: @json($business->pos_receipt_footer_note ?? ''),
                waTemplateText: @json($business->pos_receipt_wa_template ?: __('settings.default_wa_template')),

                insertTag(tag) {
                    const el = this.$refs.waTextarea;
                    if (!el) {
                        this.waTemplateText += tag;
                        return;
                    }
                    const start = el.selectionStart || 0;
                    const end = el.selectionEnd || 0;
                    const current = this.waTemplateText || '';
                    this.waTemplateText = current.substring(0, start) + tag + current.substring(end);
                    this.$nextTick(() => {
                        el.focus();
                        el.setSelectionRange(start + tag.length, start + tag.length);
                    });
                },

                resetToDefaultTemplate() {
                    this.waTemplateText = @json(__('settings.default_wa_template'));
                },

                get waRenderedPreview() {
                    let text = this.waTemplateText || '';
                    const replacements = {
                        '{business_name}': @json($business->name),
                        '{customer_name}': 'Agung Mustaqim',
                        '{order_number}': 'POS-20260910-0042',
                        '{date}': @json(now()->format('d/m/Y H:i')),
                        '{cashier_name}': @json(auth()->user()?->name ?? __('settings.default_cashier_name')),
                        '{receipt_link}': 'https://cooca.id/receipt/sample',
                        '{footer_note}': this.receiptFooterNote || @json($business->pos_receipt_footer_note ?? __('settings.default_footer_note'))
                    };
                    for (const [key, val] of Object.entries(replacements)) {
                        text = text.replaceAll(key, val || '');
                    }
                    return text;
                },

                formatWaMarkdown(str) {
                    if (!str) return '';
                    let escaped = str
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                    escaped = escaped.replace(/\*([^\*]+)\*/g,
                        '<strong class="font-bold text-black dark:text-white">$1</strong>');
                    escaped = escaped.replace(/_([^_]+)_/g, '<em class="italic">$1</em>');
                    escaped = escaped.replace(/~([^~]+)~/g, '<del class="line-through opacity-60">$1</del>');
                    escaped = escaped.replace(/```([^`]+)```/g,
                        '<code class="bg-black/10 dark:bg-white/10 px-1 py-0.5 rounded font-mono text-[11px]">$1</code>'
                    );
                    return escaped.replace(/\n/g, '<br>');
                },

                openApplyTemplate(code, name, category) {
                    this.selectedTemplate = {
                        code,
                        name,
                        category
                    };
                    this.templateModalOpen = true;
                },

                closeApplyTemplate() {
                    this.templateModalOpen = false;
                    this.selectedTemplate = {
                        code: '',
                        name: '',
                        category: ''
                    };
                },

                submitApplyTemplate() {
                    if (this.selectedTemplate.code) {
                        document.getElementById('form-apply-template-' + this.selectedTemplate.code).submit();
                    }
                },

                handleLogoChange(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            this.logoPreview = ev.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                getRoundingExample(strategy) {
                    const samples = {
                        'ROUND': 'Rp 14.234 → Rp 14.234 (Presisi desimal normal)',
                        'CEIL': 'Rp 14.234 → Rp 15.000 (Plafon ke atas)',
                        'FLOOR': 'Rp 14.234 → Rp 14.000 (Pangkas ke bawah)',
                        'ROUND_50': 'Rp 14.234 → Rp 14.250 (Kelipatan 50 terdekat)',
                        'ROUND_100': 'Rp 14.234 → Rp 14.200 (Kelipatan 100 terdekat)',
                        'ROUND_500': 'Rp 14.234 → Rp 14.500 (Kelipatan 500 terdekat)',
                        'ROUND_1000': 'Rp 14.234 → Rp 14.000 (Kelipatan 1.000 terdekat)'
                    };
                    return samples[strategy] || 'Standar pembulatan sistem';
                },

                // =====================================================
                // Branch Management Modals, GPS & Leaflet Map Integration
                // =====================================================
                branchesList: @json($branches ?? []),
                showCreateBranchModal: @json(($errors->any() && old('name')) || (request('tab') === 'branches' && (request('add') === '1' || request('add') === 'branch' || request('add') === 'outlet'))),
                showEditBranchModal: false,
                deleteBranchModalOpen: false,
                branchDeleteTarget: { id: null, name: '' },
                branchDeleteUrl: '',
                branchEditUrl: '',
                branchSubmitting: false,

                branchForm: {
                    name: @json(old('name', '')),
                    code: @json(old('code', '')),
                    phone: @json(old('phone', '')),
                    address: @json(old('address', '')),
                    province: @json(old('province', '')),
                    city: @json(old('city', '')),
                    district: @json(old('district', '')),
                    village: @json(old('village', '')),
                    postal_code: @json(old('postal_code', '')),
                    biteship_area_id: @json(old('biteship_area_id', '')),
                    latitude: @json(old('latitude', $defaultBranchLat)),
                    longitude: @json(old('longitude', $defaultBranchLng)),
                    geofence_radius_meters: @json((int) old('geofence_radius_meters', 100)),
                    is_primary: @json((bool) old('is_primary', false)),
                    is_online_fulfillment: @json((bool) old('is_online_fulfillment', true)),
                    allow_storefront_pickup: @json((bool) old('allow_storefront_pickup', true))
                },

                branchEditData: {
                    id: null,
                    name: '',
                    code: '',
                    phone: '',
                    address: '',
                    province: '',
                    city: '',
                    district: '',
                    village: '',
                    postal_code: '',
                    biteship_area_id: '',
                    latitude: {{ $defaultBranchLat }},
                    longitude: {{ $defaultBranchLng }},
                    geofence_radius_meters: 100,
                    is_primary: false,
                    is_online_fulfillment: true,
                    allow_storefront_pickup: true,
                    is_active: true
                },

                branchSearchQuery: '',
                branchSearchResults: [],
                branchIsSearching: false,
                branchShowDropdown: false,
                branchGpsLoading: false,

                branchCreateMap: null,
                branchCreateMarker: null,
                branchCreateCircle: null,

                branchEditMap: null,
                branchEditMarker: null,
                branchEditCircle: null,

                getMapCustomMarkerIcon() {
                    return L.divIcon({
                        className: 'custom-map-marker',
                        html: `<div style="background-color: #007AFF; width: 32px; height: 32px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,122,255,0.45); border: 2.5px solid white;">
                                 <div style="width: 10px; height: 10px; background-color: white; border-radius: 50%; transform: rotate(45deg);"></div>
                               </div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 32]
                    });
                },

                openCreateBranch() {
                    this.branchForm = {
                        name: '',
                        code: '',
                        phone: '',
                        address: '',
                        province: '',
                        city: '',
                        district: '',
                        village: '',
                        postal_code: '',
                        biteship_area_id: '',
                        latitude: {{ $defaultBranchLat }},
                        longitude: {{ $defaultBranchLng }},
                        geofence_radius_meters: 100,
                        is_primary: false,
                        is_online_fulfillment: true,
                        allow_storefront_pickup: true
                    };
                    this.branchSearchQuery = '';
                    this.branchSearchResults = [];
                    this.branchShowDropdown = false;
                    this.branchGpsLoading = false;
                    this.branchSubmitting = false;
                    this.showCreateBranchModal = true;

                    this.$nextTick(() => {
                        setTimeout(() => {
                            this.initBranchCreateMap();
                            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                                window.lucide.createIcons();
                            }
                        }, 250);
                    });
                },

                openEditBranch(branchOrId) {
                    let branch = branchOrId;
                    if (typeof branchOrId === 'string' || typeof branchOrId === 'number') {
                        branch = (this.branchesList || []).find(b => String(b.id) === String(branchOrId));
                    }
                    if (!branch) return;

                    this.branchEditUrl = '{{ url('/settings/branches') }}/' + branch.id;
                    const lat = (branch.latitude !== null && branch.latitude !== undefined && branch.latitude !== '')
                        ? parseFloat(branch.latitude)
                        : {{ $defaultBranchLat }};
                    const lng = (branch.longitude !== null && branch.longitude !== undefined && branch.longitude !== '')
                        ? parseFloat(branch.longitude)
                        : {{ $defaultBranchLng }};
                    const radius = (branch.geofence_radius_meters !== null && branch.geofence_radius_meters !== undefined)
                        ? parseInt(branch.geofence_radius_meters)
                        : 100;

                    this.branchEditData = {
                        id: branch.id,
                        name: branch.name || '',
                        code: branch.code || '',
                        phone: branch.phone || '',
                        address: branch.address || '',
                        province: branch.province || '',
                        city: branch.city || '',
                        district: branch.district || '',
                        village: branch.village || '',
                        postal_code: branch.postal_code || '',
                        biteship_area_id: branch.biteship_area_id || '',
                        latitude: lat,
                        longitude: lng,
                        geofence_radius_meters: radius,
                        is_primary: Boolean(branch.is_primary),
                        is_online_fulfillment: Boolean(branch.is_online_fulfillment !== false && branch.is_online_fulfillment !== 0),
                        allow_storefront_pickup: Boolean(branch.allow_storefront_pickup !== false && branch.allow_storefront_pickup !== 0),
                        is_active: Boolean(branch.is_active !== false && branch.is_active !== 0)
                    };
                    this.branchSearchQuery = '';
                    this.branchSearchResults = [];
                    this.branchShowDropdown = false;
                    this.branchGpsLoading = false;
                    this.branchSubmitting = false;
                    this.showEditBranchModal = true;

                    this.$nextTick(() => {
                        setTimeout(() => {
                            this.initBranchEditMap();
                            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                                window.lucide.createIcons();
                            }
                        }, 250);
                    });
                },

                initBranchCreateMap() {
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initBranchCreateMap(), 200);
                        return;
                    }
                    const container = document.getElementById('branch-create-map');
                    if (!container) return;

                    const lat = parseFloat(this.branchForm.latitude) || {{ $defaultBranchLat }};
                    const lng = parseFloat(this.branchForm.longitude) || {{ $defaultBranchLng }};
                    const radius = parseInt(this.branchForm.geofence_radius_meters) || 100;

                    if (this.branchCreateMap) {
                        try { this.branchCreateMap.remove(); } catch (e) {}
                        this.branchCreateMap = null;
                        this.branchCreateMarker = null;
                        this.branchCreateCircle = null;
                    }

                    this.branchCreateMap = L.map('branch-create-map', {
                        zoomControl: true,
                        scrollWheelZoom: false
                    }).setView([lat, lng], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.branchCreateMap);

                    this.branchCreateMarker = L.marker([lat, lng], {
                        draggable: true,
                        icon: this.getMapCustomMarkerIcon()
                    }).addTo(this.branchCreateMap);

                    this.branchCreateCircle = L.circle([lat, lng], {
                        radius: radius,
                        color: '#007AFF',
                        fillColor: '#007AFF',
                        fillOpacity: 0.15,
                        weight: 2
                    }).addTo(this.branchCreateMap);

                    this.branchCreateMarker.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.branchForm.latitude = parseFloat(pos.lat.toFixed(7));
                        this.branchForm.longitude = parseFloat(pos.lng.toFixed(7));
                        if (this.branchCreateCircle) {
                            this.branchCreateCircle.setLatLng(pos);
                        }
                        this.reverseGeocodeBranch('create', pos.lat, pos.lng, true);
                    });

                    this.branchCreateMap.on('click', (e) => {
                        this.branchCreateMarker.setLatLng(e.latlng);
                        if (this.branchCreateCircle) {
                            this.branchCreateCircle.setLatLng(e.latlng);
                        }
                        this.branchForm.latitude = parseFloat(e.latlng.lat.toFixed(7));
                        this.branchForm.longitude = parseFloat(e.latlng.lng.toFixed(7));
                        this.reverseGeocodeBranch('create', e.latlng.lat, e.latlng.lng, true);
                    });

                    setTimeout(() => {
                        if (this.branchCreateMap) this.branchCreateMap.invalidateSize();
                    }, 350);
                },

                initBranchEditMap() {
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initBranchEditMap(), 200);
                        return;
                    }
                    const container = document.getElementById('branch-edit-map');
                    if (!container) return;

                    const lat = parseFloat(this.branchEditData.latitude) || {{ $defaultBranchLat }};
                    const lng = parseFloat(this.branchEditData.longitude) || {{ $defaultBranchLng }};
                    const radius = parseInt(this.branchEditData.geofence_radius_meters) || 100;

                    if (this.branchEditMap) {
                        try { this.branchEditMap.remove(); } catch (e) {}
                        this.branchEditMap = null;
                        this.branchEditMarker = null;
                        this.branchEditCircle = null;
                    }

                    this.branchEditMap = L.map('branch-edit-map', {
                        zoomControl: true,
                        scrollWheelZoom: false
                    }).setView([lat, lng], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.branchEditMap);

                    this.branchEditMarker = L.marker([lat, lng], {
                        draggable: true,
                        icon: this.getMapCustomMarkerIcon()
                    }).addTo(this.branchEditMap);

                    this.branchEditCircle = L.circle([lat, lng], {
                        radius: radius,
                        color: '#007AFF',
                        fillColor: '#007AFF',
                        fillOpacity: 0.15,
                        weight: 2
                    }).addTo(this.branchEditMap);

                    this.branchEditMarker.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.branchEditData.latitude = parseFloat(pos.lat.toFixed(7));
                        this.branchEditData.longitude = parseFloat(pos.lng.toFixed(7));
                        if (this.branchEditCircle) {
                            this.branchEditCircle.setLatLng(pos);
                        }
                        this.reverseGeocodeBranch('edit', pos.lat, pos.lng, true);
                    });

                    this.branchEditMap.on('click', (e) => {
                        this.branchEditMarker.setLatLng(e.latlng);
                        if (this.branchEditCircle) {
                            this.branchEditCircle.setLatLng(e.latlng);
                        }
                        this.branchEditData.latitude = parseFloat(e.latlng.lat.toFixed(7));
                        this.branchEditData.longitude = parseFloat(e.latlng.lng.toFixed(7));
                        this.reverseGeocodeBranch('edit', e.latlng.lat, e.latlng.lng, true);
                    });

                    setTimeout(() => {
                        if (this.branchEditMap) this.branchEditMap.invalidateSize();
                    }, 350);
                },

                updateGeofenceCircle(target) {
                    if (target === 'create') {
                        const radius = parseInt(this.branchForm.geofence_radius_meters) || 100;
                        if (this.branchCreateCircle) {
                            this.branchCreateCircle.setRadius(radius);
                        }
                    } else {
                        const radius = parseInt(this.branchEditData.geofence_radius_meters) || 100;
                        if (this.branchEditCircle) {
                            this.branchEditCircle.setRadius(radius);
                        }
                    }
                },

                async searchBranchAreas(target) {
                    const q = (this.branchSearchQuery || '').trim();
                    if (q.length < 2) {
                        this.branchSearchResults = [];
                        this.branchShowDropdown = false;
                        return;
                    }

                    this.branchIsSearching = true;
                    try {
                        const res = await fetch(`/geo/search-areas?query=${encodeURIComponent(q)}`);
                        const data = await res.json();
                        if (data && data.success && Array.isArray(data.areas)) {
                            this.branchSearchResults = data.areas;
                            this.branchShowDropdown = this.branchSearchResults.length > 0;
                        } else {
                            this.branchSearchResults = [];
                            this.branchShowDropdown = false;
                        }
                    } catch (err) {
                        console.error('Error searching areas:', err);
                        this.branchSearchResults = [];
                    } finally {
                        this.branchIsSearching = false;
                    }
                },

                selectBranchArea(target, area) {
                    let formObj = target === 'create' ? this.branchForm : this.branchEditData;
                    let mapObj = target === 'create' ? this.branchCreateMap : this.branchEditMap;
                    let markerObj = target === 'create' ? this.branchCreateMarker : this.branchEditMarker;
                    let circleObj = target === 'create' ? this.branchCreateCircle : this.branchEditCircle;

                    if (formObj) {
                        formObj.province = area.province || '';
                        formObj.city = area.city || '';
                        formObj.district = area.district || '';
                        formObj.village = area.village || '';
                        formObj.postal_code = area.postal_code || '';
                        formObj.biteship_area_id = area.id || '';
                    }

                    this.branchSearchQuery = '';
                    this.branchShowDropdown = false;

                    if (area.latitude && area.longitude && mapObj && markerObj) {
                        const lat = parseFloat(area.latitude);
                        const lng = parseFloat(area.longitude);
                        formObj.latitude = parseFloat(lat.toFixed(7));
                        formObj.longitude = parseFloat(lng.toFixed(7));
                        markerObj.setLatLng([lat, lng]);
                        if (circleObj) circleObj.setLatLng([lat, lng]);
                        mapObj.flyTo([lat, lng], 15, { duration: 1.2 });
                    } else if (mapObj && markerObj) {
                        const labelQuery = area.label || `${area.district || ''}, ${area.city || ''}, Indonesia`;
                        fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&limit=1&q=${encodeURIComponent(labelQuery)}`)
                            .then(r => r.json())
                            .then(geo => {
                                if (geo && geo[0]) {
                                    const lat = parseFloat(geo[0].lat);
                                    const lng = parseFloat(geo[0].lon);
                                    formObj.latitude = parseFloat(lat.toFixed(7));
                                    formObj.longitude = parseFloat(lng.toFixed(7));
                                    markerObj.setLatLng([lat, lng]);
                                    if (circleObj) circleObj.setLatLng([lat, lng]);
                                    mapObj.flyTo([lat, lng], 15, { duration: 1.2 });
                                }
                            })
                            .catch(() => {});
                    }
                },

                async reverseGeocodeBranch(target, lat, lng, forceOverwrite = false) {
                    let formObj = target === 'create' ? this.branchForm : this.branchEditData;
                    if (!formObj) return;

                    try {
                        const res = await fetch(`/geo/reverse-geocode?lat=${lat}&lng=${lng}`);
                        const data = await res.json();
                        if (data && data.success) {
                            if (forceOverwrite || !formObj.province) {
                                if (data.province) formObj.province = data.province;
                            }
                            if (forceOverwrite || !formObj.city) {
                                if (data.city) formObj.city = data.city;
                            }
                            if (forceOverwrite || !formObj.district) {
                                if (data.district) formObj.district = data.district;
                            }
                            if (forceOverwrite || !formObj.village) {
                                if (data.village) formObj.village = data.village;
                            }
                            if (forceOverwrite || !formObj.postal_code) {
                                if (data.postal_code) formObj.postal_code = data.postal_code;
                            }
                            if (data.biteship_area_id) {
                                formObj.biteship_area_id = data.biteship_area_id;
                            }
                            if (data.road && (forceOverwrite || !formObj.address)) {
                                formObj.address = data.road;
                            }
                        }
                    } catch (err) {
                        console.warn('Reverse geocode error:', err);
                    }
                },

                useCurrentBranchGps(target) {
                    if (!navigator.geolocation) {
                        alert('{{ addslashes(__('settings.err_gps_not_supported')) }}');
                        return;
                    }

                    this.branchGpsLoading = true;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            const lat = parseFloat(pos.coords.latitude.toFixed(7));
                            const lng = parseFloat(pos.coords.longitude.toFixed(7));
                            let formObj = target === 'create' ? this.branchForm : this.branchEditData;
                            let mapObj = target === 'create' ? this.branchCreateMap : this.branchEditMap;
                            let markerObj = target === 'create' ? this.branchCreateMarker : this.branchEditMarker;
                            let circleObj = target === 'create' ? this.branchCreateCircle : this.branchEditCircle;

                            if (formObj) {
                                formObj.latitude = lat;
                                formObj.longitude = lng;
                            }
                            if (mapObj && markerObj) {
                                markerObj.setLatLng([lat, lng]);
                                if (circleObj) circleObj.setLatLng([lat, lng]);
                                mapObj.flyTo([lat, lng], 16, { duration: 1.2 });
                            }
                            this.reverseGeocodeBranch(target, lat, lng, true);
                            this.branchGpsLoading = false;
                        },
                        (err) => {
                            this.branchGpsLoading = false;
                            alert('{{ addslashes(__('settings.err_gps_failed_prefix')) }}' + (err.message || '{{ addslashes(__('settings.err_gps_permission_denied')) }}'));
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                },

                openDeleteBranch(id, name = null) {
                    if (!name && this.branchesList) {
                        const branch = this.branchesList.find(b => String(b.id) === String(id));
                        name = branch ? branch.name : '';
                    }
                    this.branchDeleteTarget = { id, name: name || '' };
                    this.branchDeleteUrl = '{{ url('/settings/branches') }}/' + id;
                    this.deleteBranchModalOpen = true;
                },

                closeDeleteBranch() {
                    this.deleteBranchModalOpen = false;
                    this.branchDeleteTarget = { id: null, name: '' };
                },

                submitDeleteBranch() {
                    if (this.$refs.deleteBranchForm) {
                        this.$refs.deleteBranchForm.submit();
                    }
                },

                // =====================================================
                // WhatsApp WABA & Gateway Methods
                // =====================================================
                async refreshMetaStatus() {
                    try {
                        const response = await fetch('{{ route('whatsapp.meta.status') }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (!response.ok || !data.success || !data.account) return;

                        const account = data.account;
                        this.metaAccount = {
                            ...this.metaAccount,
                            ...account,
                            is_active: Boolean(data.connected && account.is_connected !== false)
                        };
                    } catch (e) {}
                },

                async launchEmbeddedSignup() {
                    this.embeddedLoading = true;
                    this.embeddedError = null;
                    this.embeddedSuccess = null;

                    try {
                        const configRes = await fetch('{{ route('whatsapp.meta.config') }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const configData = await configRes.json();
                        if (!configRes.ok || !configData.success) {
                            this.embeddedError = configData.error || {{ Js::from(__('whatsapp.error_meta_app_id_missing')) }};
                            this.embeddedLoading = false;
                            return;
                        }

                        if (!configData.config_id || String(configData.config_id).trim() === '') {
                            this.embeddedLoading = false;
                            this.embeddedError = {{ Js::from(__('whatsapp.error_meta_config_id_missing')) }};
                            return;
                        }

                        await this.ensureFbSdkLoaded(configData.app_id, configData.version || 'v26.0');

                        const loginOptions = {
                            config_id: String(configData.config_id).trim(),
                            response_type: 'code',
                            override_default_response_type: true,
                            extras: {
                                setup: {
                                    business: {
                                        name: {{ Js::from($business->name) }}
                                    }
                                }
                            }
                        };

                        FB.login((response) => {
                            if (response.authResponse && response.authResponse.code) {
                                this.exchangeEmbeddedCode(
                                    response.authResponse.code,
                                    this.metaEmbeddedWabaId,
                                    this.metaEmbeddedPhoneId
                                );
                            } else {
                                this.embeddedLoading = false;
                                this.embeddedError = {{ Js::from(__('whatsapp.error_meta_auth_cancelled')) }};
                            }
                        }, loginOptions);
                    } catch (err) {
                        this.embeddedLoading = false;
                        this.embeddedError = {{ Js::from(__('whatsapp.error_launch_signup_failed')) }} + ': ' + (err.message || err);
                    }
                },

                ensureFbSdkLoaded(appId, version) {
                    return new Promise((resolve) => {
                        if (window.FB) {
                            resolve();
                            return;
                        }
                        window.fbAsyncInit = function() {
                            FB.init({
                                appId: appId,
                                autoLogAppEvents: true,
                                xfbml: true,
                                version: version
                            });
                            resolve();
                        };
                        const js = document.createElement('script');
                        js.id = 'facebook-jssdk';
                        js.src = 'https://connect.facebook.net/en_US/sdk.js';
                        js.async = true;
                        js.defer = true;
                        document.body.appendChild(js);
                    });
                },

                async exchangeEmbeddedCode(code, wabaId = null, phoneId = null) {
                    this.embeddedLoading = true;
                    try {
                        const res = await fetch('{{ route('whatsapp.meta.exchange-code') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({
                                code: code,
                                waba_id: wabaId,
                                phone_number_id: phoneId
                            })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.embeddedSuccess = {{ Js::from(__('whatsapp.meta_connected_success')) }};
                            this.metaAccount = data.account;
                            if (window.AppAlert) {
                                AppAlert.success(this.embeddedSuccess);
                            }
                        } else {
                            this.embeddedError = data.error || {{ Js::from(__('whatsapp.error_meta_exchange_failed')) }};
                        }
                    } catch (e) {
                        this.embeddedError = {{ Js::from(__('whatsapp.error_meta_credentials_save')) }};
                    } finally {
                        this.embeddedLoading = false;
                    }
                },

                async toggleServiceStatus(e) {
                    const isChecked = e.target.checked;
                    try {
                        const formData = new FormData();
                        formData.append('is_active', isChecked ? '1' : '0');
                        formData.append('provider', 'meta_cloud');
                        formData.append('_token', document.querySelector('meta[name=csrf-token]')?.content || '');
                        await fetch('{{ route('whatsapp.settings') }}', {
                            method: 'POST',
                            body: formData,
                            headers: { 'Accept': 'application/json' }
                        });
                        if (this.metaAccount) {
                            this.metaAccount.is_active = isChecked;
                        }
                        if (window.AppAlert) {
                            AppAlert.toast('success', isChecked ? 'Layanan WhatsApp diaktifkan' : 'Layanan WhatsApp dinonaktifkan');
                        }
                    } catch (err) {
                        console.error('Failed to toggle service status', err);
                    }
                },

                disconnectMeta() {
                    const hasPin = {{ $business->hasSupervisorPin() ? 'true' : 'false' }};
                    if (hasPin) {
                        this.disconnectPin = '';
                        this.disconnectError = '';
                        this.showDisconnectModal = true;
                    } else {
                        this.confirmDisconnectDirect();
                    }
                },

                async confirmDisconnectDirect() {
                    let confirmed = false;
                    if (window.AppAlert) {
                        confirmed = await AppAlert.confirm({
                            title: {{ Js::from(__('whatsapp.disconnect_confirm_title')) }},
                            message: {{ Js::from(__('whatsapp.disconnect_confirm_desc')) }},
                            type: 'danger',
                            confirmText: {{ Js::from(__('whatsapp.disconnect_btn')) }},
                            cancelText: {{ Js::from(__('whatsapp.cancel_btn')) }}
                        });
                    } else {
                        confirmed = confirm({{ Js::from(__('whatsapp.disconnect_confirm_desc')) }});
                    }
                    if (!confirmed) return;
                    this.executeDisconnect('');
                },

                async submitDisconnectWithPin() {
                    if (this.disconnectLoading) return;
                    if (!this.disconnectPin.trim()) {
                        this.disconnectError = {{ Js::from(__('whatsapp.supervisor_pin_required')) }};
                        return;
                    }
                    this.disconnectLoading = true;
                    this.disconnectError = '';
                    await this.executeDisconnect(this.disconnectPin);
                    this.disconnectLoading = false;
                },

                async executeDisconnect(pin) {
                    try {
                        const res = await fetch('{{ route('whatsapp.meta.disconnect') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({ pin: pin })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.showDisconnectModal = false;
                            this.metaAccount = null;
                            if (window.AppAlert) {
                                AppAlert.success('Akun WhatsApp resmi berhasil diputuskan.');
                            }
                        } else {
                            const errMsg = data.message || {{ Js::from(__('whatsapp.supervisor_pin_invalid')) }};
                            if (this.showDisconnectModal) {
                                this.disconnectError = errMsg;
                            } else {
                                alert(errMsg);
                            }
                        }
                    } catch (e) {
                        console.error('Failed to disconnect Meta account', e);
                        if (this.showDisconnectModal) {
                            this.disconnectError = {{ Js::from(__('whatsapp.error_network_disconnect')) }};
                        }
                    }
                },

                async sendTest() {
                    if (this.testLoading) return;
                    if (!this.testPhone.trim() || !this.testMessage.trim()) {
                        this.testResult = {{ Js::from(__('whatsapp.test_validation_error')) }};
                        this.testOk = false;
                        return;
                    }
                    this.testLoading = true;
                    this.testResult = '';
                    try {
                        const res = await fetch('{{ route('whatsapp.test') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({
                                phone: this.testPhone,
                                message: this.testMessage
                            })
                        });
                        const data = await res.json();
                        this.testOk = data.success ?? false;
                        this.testResult = this.testOk 
                            ? {{ Js::from(__('whatsapp.test_success')) }} 
                            : ({{ Js::from(__('whatsapp.test_failed_prefix')) }} + ': ' + (data.error || {{ Js::from(__('whatsapp.server_error')) }}));
                    } catch (e) {
                        this.testResult = {{ Js::from(__('whatsapp.test_network_error')) }};
                        this.testOk = false;
                    } finally {
                        this.testLoading = false;
                    }
                },

                // =====================================================
                // Social Media Omnichannel Methods
                // =====================================================
                async launchMetaLogin() {
                    this.socialLoading = true;
                    this.socialErrorMessage = '';
                    try {
                        const res = await fetch('{{ route('social-media.config') }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();

                        if (!data.success || !data.login_url) {
                            this.socialErrorMessage = data.message || @js(__('social_media.meta_not_configured'));
                            this.socialLoading = false;
                            return;
                        }

                        window.location.href = data.login_url;
                    } catch (e) {
                        this.socialErrorMessage = @js(__('common.error')) + ': ' + (e.message || e);
                        this.socialLoading = false;
                    }
                },

                async handleOAuthCode(code) {
                    this.socialLoading = true;
                    try {
                        const res = await fetch('{{ route('social-media.exchange-token') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({ code: code })
                        });

                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.socialSuccessMessage = data.message || @js(__('social_media.meta_connected_success'));
                            if (window.AppAlert) {
                                AppAlert.success(this.socialSuccessMessage);
                            }
                        } else {
                            this.socialErrorMessage = data.error || @js(__('social_media.meta_token_invalid'));
                            if (window.AppAlert) {
                                AppAlert.error(this.socialErrorMessage);
                            }
                        }
                    } catch (e) {
                        this.socialErrorMessage = @js(__('common.error'));
                    } finally {
                        this.socialLoading = false;
                    }
                },

                async disconnectSocial(accountId) {
                    let confirmed = false;
                    if (window.AppAlert) {
                        confirmed = await AppAlert.confirm({
                            title: @js(__('social_media.disconnect_confirm_title')),
                            message: @js(__('social_media.disconnect_confirm_msg')),
                            type: 'danger',
                            confirmText: @js(__('social_media.disconnect_confirm_btn')),
                            cancelText: @js(__('social_media.cancel'))
                        });
                    } else {
                        confirmed = confirm(@js(__('social_media.disconnect_confirm_msg')));
                    }
                    if (!confirmed) return;

                    let pin = null;
                    @if(!empty($business->pos_supervisor_pin))
                    if (window.Swal) {
                        const { value: inputPin } = await Swal.fire({
                            title: @js(__('social_media.supervisor_pin_required')),
                            input: 'password',
                            inputAttributes: {
                                inputmode: 'numeric',
                                pattern: '[0-9]*',
                                maxlength: 6
                            },
                            inputPlaceholder: @js(__('social_media.supervisor_pin_placeholder')),
                            showCancelButton: true,
                            confirmButtonText: @js(__('common.confirm')),
                            cancelButtonText: @js(__('common.cancel'))
                        });
                        if (!inputPin) return;
                        pin = inputPin;
                    }
                    @endif

                    try {
                        const res = await fetch('{{ route('social-media.disconnect') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({ account_id: accountId, pin: pin })
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.disconnectedSocialIds.push(accountId);
                            if (window.AppAlert) {
                                AppAlert.success(data.message || @js(__('social_media.account_disconnected_success')));
                            }
                        } else {
                            if (window.AppAlert) {
                                AppAlert.error(data.error || data.message || @js(__('social_media.account_not_found')));
                            }
                        }
                    } catch (e) {
                        if (window.AppAlert) {
                            AppAlert.error(@js(__('common.error')));
                        }
                    }
                }
            };
        }
    </script>
@endsection
