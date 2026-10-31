@extends('layouts.admin')
@section('title', __('admin.wa_page_title'))

@section('content')
    @php
        $validTabs = ['parent_setup', 'reminders', 'blast', 'templates', 'merchants'];
        $initialTab = in_array($tab, $validTabs, true) ? $tab : 'parent_setup';
        $initialPhone = $waStatus['phone'] ?? '';
        $totalDueCount = $dueData['stats']['total_due'] ?? 0;
        $pendingRemindersCount = $dueData['stats']['total_pending'] ?? 0;
        $sentRemindersCount = $dueData['stats']['total_sent'] ?? 0;
        $totalOwnersCount = $targetCounts['all_owners'] ?? 0;
        $totalBlastSent = $blasts->sum('total_sent');
        $totalBlastRecipients = $blasts->sum('total_recipients');
        $overallSuccessRate =
            $totalBlastRecipients + $totalDueCount > 0
                ? round(
                    (($totalBlastSent + $sentRemindersCount) / max(1, $totalBlastRecipients + $totalDueCount)) * 100,
                )
                : 100;
        $activeMerchantsCount = $merchantSummary['active'] ?? 0;
        $totalMerchantsCount = $merchantSummary['total'] ?? 0;
    @endphp

    <div class="space-y-5 sm:space-y-6 max-w-[1250px] w-full min-w-0 mx-auto pb-28 lg:pb-10" x-data="adminWaCenter()"
        x-init="init()">

        {{-- 1. BENTO HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-4 min-w-0">
            <div class="flex items-center gap-3 sm:gap-3.5 min-w-0 flex-1">
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-[14px] sm:rounded-[18px] bg-gradient-to-br from-[#1877F2] to-[#007AFF] flex items-center justify-center shadow-md shadow-[#1877F2]/20 shrink-0 text-white">
                    <i data-lucide="shield-check" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h1 class="text-[18px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight truncate">
                        WhatsApp Platform Admin Center</h1>
                    <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5 truncate">Pusat konfigurasi
                        {{ __('admin.wa_header_desc') }}</p>
                </div>
            </div>

            {{-- Live Status Indicator & Quick Setup Button --}}
            <div class="grid grid-cols-1 xs:grid-cols-2 sm:flex items-center gap-2 sm:gap-3 w-full sm:w-auto min-w-0">
                <template x-if="status === 'connected'">
                    <div
                        class="flex items-center justify-center gap-2 px-3 py-2 sm:px-3.5 sm:py-2.5 rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] text-[12px] font-semibold min-h-[40px] sm:min-h-[44px] min-w-0">
                        <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
                        <span class="truncate">{{ __('admin.wa_status_meta_official_active') }}</span>
                        <span class="text-black/30 dark:text-white/30">|</span>
                        <span class="font-mono truncate"
                            x-text="phone ? (phone.startsWith('+') ? phone : '+' + phone) : 'Online'"></span>
                    </div>
                </template>
                <template x-if="status !== 'connected' && codeVerificationStatus === 'NOT_VERIFIED' && phone">
                    <div
                        class="flex items-center justify-center gap-2 px-3 py-2 sm:px-3.5 sm:py-2.5 rounded-[12px] bg-[#FF9500]/12 border border-[#FF9500]/25 text-[#B25E00] dark:text-[#FF9F0A] text-[12px] font-semibold min-h-[40px] sm:min-h-[44px] min-w-0">
                        <span class="w-2 h-2 rounded-full bg-[#FF9500] shrink-0 animate-pulse"></span>
                        <span class="truncate">{{ __('admin.wa_status_need_number_verification') }}</span>
                    </div>
                </template>
                <template x-if="status !== 'connected' && (codeVerificationStatus !== 'NOT_VERIFIED' || !phone)">
                    <div
                        class="flex items-center justify-center gap-2 px-3 py-2 sm:px-3.5 sm:py-2.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/10 dark:border-white/10 text-black/55 dark:text-white/55 text-[12px] font-semibold min-h-[40px] sm:min-h-[44px] min-w-0">
                        <span class="w-2 h-2 rounded-full bg-[#FF9500] shrink-0"></span>
                        <span class="truncate">{{ __('admin.wa_status_meta_unconfigured') }}</span>
                    </div>
                </template>

                <button type="button" @click="setTab('blast')"
                    class="min-h-[40px] sm:min-h-[44px] px-3.5 rounded-[12px] text-[12.5px] sm:text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#2DB34F] active:scale-[0.98] shadow-sm transition-all inline-flex items-center justify-center gap-2 min-w-0">
                    <i data-lucide="megaphone" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">{{ __('whatsapp.send_broadcast_blast') }}</span>
                </button>

                <a href="{{ route('admin.settings.index', ['tab' => 'whatsapp']) }}"
                    class="min-h-[40px] sm:min-h-[44px] px-4 rounded-[12px] text-[12.5px] sm:text-[13px] font-semibold bg-[#1877F2] text-white hover:bg-[#166FE5] active:scale-[0.98] shadow-sm transition-all inline-flex items-center justify-center gap-2 min-w-0">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">{{ __('whatsapp.manage_meta_settings') }}</span>
                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 shrink-0 opacity-70"></i>
                </a>
            </div>
        </div>

        {{-- NOTIFICATIONS / ALERTS --}}
        @if ($errors->any())
            <div
                class="rounded-[18px] p-4 bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#C41E17] dark:text-[#FF453A] text-[13px] font-medium shadow-sm">
                <div class="flex items-center gap-2 mb-1.5 font-bold">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span>{{ __('admin.check_form_input_again') }}</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-[12px] opacity-90 pl-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 2. BENTO HERO KPI TILES (ADAPTIVE 2-COLUMN MOBILE, 4-COLUMN DESKTOP) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 w-full min-w-0">
            {{-- Tile 1: Status Gateway Platform --}}
            <div
                class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">Bot Platform</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] flex items-center justify-center shrink-0"
                        :class="status === 'connected' ? 'bg-[#34C759]/15 text-[#34C759]' :
                            'bg-black/5 dark:bg-white/10 text-black/40 dark:text-white/40'">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                    </div>
                </div>
                <div class="mt-2.5 min-w-0">
                    <div
                        class="text-[18px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight flex items-center gap-1.5 truncate">
                        <span x-text="status === 'connected' ? 'Meta Resmi' : 'Offline'"></span>
                    </div>
                    <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 font-mono truncate"
                        x-text="phone ? '+' + phone : @json(__('admin.wa_credentials_incomplete'))"></p>
                </div>
            </div>

            {{-- Tile 2: Merchant Terhubung --}}
            <div
                class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">{{ __('admin.wa_active_merchants') }}</span>
                    <div
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] bg-[#1877F2]/15 text-[#1877F2] flex items-center justify-center shrink-0">
                        <i data-lucide="store" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                    </div>
                </div>
                <div class="mt-2.5 min-w-0">
                    <div
                        class="text-[18px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight tabular-nums truncate">
                        {{ $activeMerchantsCount }} <span
                            class="text-[11px] sm:text-[12px] font-medium text-black/45 dark:text-white/45">{{ __('admin.active_short') }}</span>
                    </div>
                    <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 truncate tabular-nums">
                        {{ __('admin.wa_total_registered_stores', ['count' => $totalMerchantsCount]) }}</p>
                </div>
            </div>

            {{-- Tile 3: Pengingat Hari Ini --}}
            <div
                class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">{{ __('admin.wa_due_reminders') }}</span>
                    <div
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center shrink-0">
                        <i data-lucide="bell" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                    </div>
                </div>
                <div class="mt-2.5 min-w-0">
                    <div
                        class="text-[18px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight tabular-nums truncate">
                        {{ $pendingRemindersCount }} <span
                            class="text-[11px] sm:text-[12px] font-medium text-black/45 dark:text-white/45">{{ __('admin.wa_pending_count', ['count' => '']) }}</span>
                    </div>
                    <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 truncate tabular-nums">
                        {{ __('admin.wa_total_due', ['count' => $totalDueCount]) }}</p>
                </div>
            </div>

            {{-- Tile 4: Keberhasilan Kirim --}}
            <div
                class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">{{ __('admin.wa_delivery_rate') }}</span>
                    <div
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="check-check" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                    </div>
                </div>
                <div class="mt-2.5 min-w-0">
                    <div
                        class="text-[18px] sm:text-[24px] font-bold text-[#248A3D] dark:text-[#30D158] tracking-tight tabular-nums truncate">
                        {{ $overallSuccessRate }}%
                    </div>
                    <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 truncate tabular-nums">
                        {{ __('admin.wa_messages_sent', ['count' => number_format($sentRemindersCount + $totalBlastSent)]) }}</p>
                </div>
            </div>
        </div>

        {{-- 3. PERSISTENT SEGMENTED CONTROL (APPLE HIG STYLE) --}}
        <div class="w-full max-w-full min-w-0 overflow-hidden">
            <div
                class="p-1.5 bg-black/[0.05] dark:bg-white/[0.07] rounded-[16px] flex items-center gap-1.5 overflow-x-auto shadow-inner no-scrollbar">
                <button type="button" @click="setTab('parent_setup')"
                    :class="activeTab === 'parent_setup' ?
                        'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' :
                        'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="min-h-[42px] sm:min-h-[44px] px-3.5 sm:px-5 rounded-[12px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 sm:gap-2.5 shrink-0 whitespace-nowrap active:scale-[0.98]">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#1877F2]"></i>
                    <span>{{ __('whatsapp.meta_platform_settings') }}</span>
                    <template x-if="status === 'connected'">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                    </template>
                </button>

                <button type="button" @click="setTab('merchants')"
                    :class="activeTab === 'merchants' ?
                        'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' :
                        'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="min-h-[42px] sm:min-h-[44px] px-3.5 sm:px-5 rounded-[12px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 sm:gap-2.5 shrink-0 whitespace-nowrap active:scale-[0.98]">
                    <i data-lucide="store" class="w-4 h-4 text-[#34C759]"></i>
                    <span>{{ __('admin.wa_tab_merchants') }}</span>
                    @if ($activeMerchantsCount > 0)
                        <span
                            class="px-2 py-0.5 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] text-[11px] font-bold tabular-nums">
                            {{ $activeMerchantsCount }}
                        </span>
                    @endif
                </button>

                <button type="button" @click="setTab('reminders')"
                    :class="activeTab === 'reminders' ?
                        'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' :
                        'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="min-h-[42px] sm:min-h-[44px] px-3.5 sm:px-5 rounded-[12px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 sm:gap-2.5 shrink-0 whitespace-nowrap active:scale-[0.98]">
                    <i data-lucide="bell" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ __('admin.wa_tab_reminders') }}</span>
                    @if ($pendingRemindersCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-[#FF3B30] text-white text-[11px] font-bold tabular-nums">
                            {{ $pendingRemindersCount }}
                        </span>
                    @endif
                </button>

                <button type="button" @click="setTab('blast')"
                    :class="activeTab === 'blast' ?
                        'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' :
                        'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="min-h-[42px] sm:min-h-[44px] px-3.5 sm:px-5 rounded-[12px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 sm:gap-2.5 shrink-0 whitespace-nowrap active:scale-[0.98]">
                    <i data-lucide="send" class="w-4 h-4 text-[#FF9500]"></i>
                    <span>{{ __('admin.wa_tab_blast') }}</span>
                </button>

                <button type="button" @click="setTab('templates')"
                    :class="activeTab === 'templates' ?
                        'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' :
                        'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="min-h-[42px] sm:min-h-[44px] px-3.5 sm:px-5 rounded-[12px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 sm:gap-2.5 shrink-0 whitespace-nowrap active:scale-[0.98]">
                    <i data-lucide="file-text" class="w-4 h-4 text-[#AF52DE]"></i>
                    <span>{{ __('admin.wa_tab_templates') }}</span>
                </button>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 1: PENGATURAN PLATFORM META (PARENT SETUP)                            --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'parent_setup'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6">

            {{-- 1. HERO CARD: LIVE STATUS Bot Platform META --}}
            <div
                class="rounded-[22px] sm:rounded-[24px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-6 w-full min-w-0">
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-5 pb-5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-start gap-4 min-w-0 flex-1">
                        <div
                            class="w-12 h-12 sm:w-14 sm:h-14 rounded-[18px] bg-gradient-to-br from-[#1877F2] to-[#007AFF] text-white flex items-center justify-center shadow-lg shadow-[#1877F2]/25 shrink-0">
                            <i data-lucide="shield-check" class="w-6 h-6 sm:w-7 sm:h-7"></i>
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h2 class="text-[17px] sm:text-[19px] font-bold text-black dark:text-white tracking-tight">
                                    Bot WhatsApp Platform Cooca
                                </h2>
                                <template x-if="status === 'connected'">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                        <span>{{ __('admin.wa_status_active_connected') }}</span>
                                    </span>
                                </template>
                                <template
                                    x-if="status !== 'connected' && codeVerificationStatus === 'NOT_VERIFIED' && phone">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-bold bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A] border border-[#FF3B30]/25">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                        <span>{{ __('admin.wa_status_need_number_verification') }}</span>
                                    </span>
                                </template>
                                <template
                                    x-if="status !== 'connected' && (codeVerificationStatus !== 'NOT_VERIFIED' || !phone)">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-bold bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/25">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                        <span>{{ __('admin.wa_status_unconfigured') }}</span>
                                    </span>
                                </template>
                            </div>
                            <p class="text-[12.5px] text-black/60 dark:text-white/60 leading-relaxed max-w-2xl">
                                {{ __('admin.wa_meta_parent_desc') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0 self-stretch sm:self-auto">
                        <button type="button" @click="checkStatus()" :disabled="isLoading"
                            class="min-h-[44px] px-4 rounded-[12px] text-[12.5px] font-bold bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white active:scale-[0.98] transition-all inline-flex items-center justify-center gap-2 w-full sm:w-auto">
                            <i data-lucide="refresh-cw" class="w-4 h-4" :class="isLoading ? 'animate-spin' : ''"></i>
                            <span>{{ __('whatsapp.check_meta_status') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Status Pills Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                    <div
                        class="p-3.5 sm:p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span
                            class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider block">{{ __('admin.wa_label_business_account_name') }}</span>
                        <div class="text-[13.5px] sm:text-[14px] font-bold text-black dark:text-white truncate"
                            x-text="metaVerifyResult?.verified_name || '{{ $waStatus['verified_name'] ?? 'Cooca Platform' }}'">
                            {{ $waStatus['verified_name'] ?? 'Cooca Platform' }}
                        </div>
                    </div>

                    <div
                        class="p-3.5 sm:p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span
                            class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider block">{{ __('admin.wa_label_bot_phone_number') }}</span>
                        <div class="text-[13.5px] sm:text-[14px] font-bold font-mono text-black dark:text-white truncate"
                            x-text="phone ? (phone.startsWith('+') ? phone : '+' + phone) : '{{ $waStatus['display_phone'] ?? '-' }}'">
                            {{ $waStatus['display_phone'] ?? '-' }}
                        </div>
                    </div>

                    <div
                        class="p-3.5 sm:p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span
                            class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider block">{{ __('admin.wa_label_number_quality') }}</span>
                        <div
                            class="text-[13.5px] sm:text-[14px] font-bold text-[#248A3D] dark:text-[#30D158] flex items-center gap-1.5 truncate">
                            <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                            <span
                                x-text="metaVerifyResult?.quality_rating || '{{ $waStatus['quality_rating'] ?? 'GREEN' }}'">
                                {{ $waStatus['quality_rating'] ?? 'GREEN' }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="p-3.5 sm:p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span
                            class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider block">{{ __('admin.wa_label_daily_limit') }}</span>
                        <div class="text-[13.5px] sm:text-[14px] font-bold text-black dark:text-white truncate"
                            x-text="metaVerifyResult?.messaging_tier || '{{ $waStatus['messaging_tier'] ?? 'TIER_1K' }}'">
                            {{ $waStatus['messaging_tier'] ?? 'TIER_1K' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- BANNER TINDAKAN: VERIFIKASI NOMOR META WHATSAPP MANAGER --}}
            <template x-if="status !== 'connected' && codeVerificationStatus === 'NOT_VERIFIED' && phone">
                <div
                    class="rounded-[22px] sm:rounded-[24px] bg-[#FF9500]/10 border border-[#FF9500]/25 p-5 sm:p-6 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5 min-w-0">
                            <div
                                class="w-11 h-11 rounded-[14px] bg-[#FF9500]/20 text-[#D97706] dark:text-[#FFA114] flex items-center justify-center shrink-0">
                                <i data-lucide="shield-alert" class="w-6 h-6"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-[15px] sm:text-[16px] font-bold text-black dark:text-white tracking-tight">
                                    {{ __('whatsapp.action_required_verify_number_meta') }}
                                </h3>
                                <p
                                    class="text-[12px] sm:text-[12.5px] text-black/65 dark:text-white/65 mt-1 leading-relaxed max-w-3xl">
                                    {{ __('admin.wa_number_verification_notice_p1') }} <strong class="font-mono text-black dark:text-white"
                                        x-text="phone ? (phone.startsWith('+') ? phone : '+' + phone) : '+62 852-8786-4176'"></strong>
                                    {{ __('admin.wa_number_verification_notice_p2') }}
                                    <span class="font-mono font-bold text-[#D97706] dark:text-[#FFA114]"
                                        x-text="metaStatus + ' / ' + codeVerificationStatus">DISCONNECTED /
                                        NOT_VERIFIED</span>. {{ __('admin.wa_number_verification_notice_p3') }} (<code
                                        class="text-[11px] bg-black/5 dark:bg-white/10 px-1 py-0.5 rounded font-mono">#133010
                                        Account not registered</code>) {{ __('admin.wa_number_verification_notice_p4') }}
                                </p>
                            </div>
                        </div>
                        <a :href="managerUrl" target="_blank" rel="noopener noreferrer"
                            class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-bold bg-[#FF9500] hover:bg-[#E08500] text-white active:scale-[0.98] transition-all inline-flex items-center justify-center gap-2 shadow-sm shrink-0">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            <span>{{ __('whatsapp.open_meta_wa_manager') }}</span>
                        </a>
                    </div>

                    <div
                        class="pt-3 border-t border-[#FF9500]/20 text-[12px] text-black/70 dark:text-white/70 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-5 h-5 rounded-full bg-[#FF9500]/20 text-[#D97706] dark:text-[#FFA114] font-bold text-[11px] flex items-center justify-center shrink-0">1</span>
                            <span>{{ __('admin.wa_step1_open_link') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="w-5 h-5 rounded-full bg-[#FF9500]/20 text-[#D97706] dark:text-[#FFA114] font-bold text-[11px] flex items-center justify-center shrink-0">2</span>
                            <span>{{ __('admin.wa_step2_search_and_verify') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="w-5 h-5 rounded-full bg-[#FF9500]/20 text-[#D97706] dark:text-[#FFA114] font-bold text-[11px] flex items-center justify-center shrink-0">3</span>
                            <span>{{ __('admin.wa_step3_input_otp') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="w-5 h-5 rounded-full bg-[#FF9500]/20 text-[#D97706] dark:text-[#FFA114] font-bold text-[11px] flex items-center justify-center shrink-0">4</span>
                            <span>{{ __('admin.wa_step4_status_active') }}</span>
                        </div>
                    </div>
                </div>
            </template>

            {{-- 2. BENTO INTEGRATION HUB: PUSAT PENGATURAN TERPADU --}}
            <div
                class="rounded-[22px] sm:rounded-[24px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-6 w-full min-w-0">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-start gap-3.5">
                        <div
                            class="w-11 h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="sliders-horizontal" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">
                                    Kredensial Bot Induk Platform Meta
                                </h3>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                    Terintegrasi
                                </span>
                            </div>
                            <p class="text-[12px] sm:text-[12.5px] text-black/55 dark:text-white/55 mt-0.5 max-w-2xl">
                                {{ __('admin.wa_central_settings_desc') }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('admin.settings.index', ['tab' => 'whatsapp']) }}"
                        class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-bold bg-[#007AFF] hover:bg-[#0071E3] text-white active:scale-[0.98] transition-all inline-flex items-center justify-center gap-2 shadow-sm shrink-0">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                        <span>{{ __('whatsapp.open_wa_cloud_api_settings') }}</span>
                        <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 opacity-70"></i>
                    </a>
                </div>

                {{-- Current Settings Snapshot Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    {{-- 1. Meta App ID --}}
                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Meta App ID (META_WA_APP_ID)</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ $platformApp['app_id'] ?: __('admin.unconfigured') }}
                        </div>
                    </div>

                    {{-- 2. Meta App Secret --}}
                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Meta App Secret (META_WA_APP_SECRET)</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ !empty($platformApp['app_secret']) ? '••••••••••••••••' : __('admin.unconfigured') }}
                        </div>
                    </div>

                    {{-- 3. Embedded Signup Config ID --}}
                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Embedded Signup Config ID (META_WA_CONFIG_ID)</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ $platformApp['config_id'] ?: __('admin.optional_default') }}
                        </div>
                    </div>

                    {{-- 4. Webhook Verify Token --}}
                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Webhook Verify Token (META_WA_WEBHOOK_VERIFY_TOKEN)</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ $platformApp['webhook_verify_token'] ?: __('admin.unconfigured') }}
                        </div>
                    </div>

                    {{-- 5. Graph API Version --}}
                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Graph API Version (META_WA_GRAPH_VERSION)</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ $platformApp['graph_version'] ?: 'v26.0' }}
                        </div>
                    </div>

                    {{-- 6. Graph API Base URL --}}
                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Graph API Base URL (META_WA_GRAPH_URL)</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ $platformApp['graph_url'] ?: 'https://graph.facebook.com' }}
                        </div>
                    </div>

                    {{-- 7. Phone Number ID --}}
                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span
                            class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Phone
                            Number ID</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ $metaCreds['phone_number_id'] ?: __('admin.unconfigured') }}
                        </div>
                    </div>

                    {{-- 8. WhatsApp Business ID (WABA) --}}
                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span
                            class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">WhatsApp
                            Business ID (WABA)</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ $metaCreds['waba_id'] ?: __('admin.unconfigured') }}
                        </div>
                    </div>

                    {{-- 9. Meta System User Permanent Access Token --}}
                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Meta System User Permanent Access Token (META_WA_TOKEN)</span>
                        <div class="text-[13px] font-bold text-[#248A3D] dark:text-[#30D158] flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span>{{ !empty($metaCreds['token']) ? __('whatsapp.saved_encrypted_aes') : __('whatsapp.no_token_yet') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Webhook Callback URL (Meta Webhook Endpoint) --}}
                <div
                    class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">
                            Webhook Callback URL (Meta Webhook Endpoint)
                        </span>
                        <span
                            class="text-[10.5px] font-bold px-2 py-0.5 rounded-full bg-[#34C759]/15 text-[#34C759]">api/v1/wa/meta/webhook</span>
                    </div>
                    <div
                        class="flex items-center justify-between gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10">
                        <code
                            class="text-[12px] font-mono text-[#007AFF] break-all">{{ $platformApp['webhook_url'] }}</code>
                        <button type="button"
                            @click="copyToClipboard('{{ $platformApp['webhook_url'] }}', 'Webhook URL')"
                            class="text-[11.5px] font-bold text-[#007AFF] hover:underline shrink-0">
                            Salin URL
                        </button>
                    </div>
                </div>
            </div>
            {{-- 4. CARD UJI KIRIM PESAN (LIVE DIAGNOSTIC) --}}
            {{-- 4. CARD UJI KIRIM PESAN & WA OTP SIMULATOR (LIVE DIAGNOSTIC) --}}
            <div
                class="rounded-[22px] sm:rounded-[24px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-5 w-full min-w-0">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div
                            class="w-10 h-10 sm:w-11 sm:h-11 rounded-[14px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center shrink-0">
                            <i data-lucide="send" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">
                                {{ __('admin.wa_test_message_and_otp_simulator') }}
                            </h3>
                            <p class="text-[12px] sm:text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">
                                {{ __('admin.wa_test_message_desc') }}
                            </p>
                        </div>
                    </div>

                    {{-- Mode Switcher --}}
                    <div
                        class="flex items-center gap-1.5 p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[13px] self-start sm:self-auto shrink-0 shadow-inner">
                        <button type="button" @click="diagnosticMode = 'message'"
                            :class="diagnosticMode === 'message' ?
                                'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' :
                                'text-black/55 dark:text-white/55 font-medium'"
                            class="min-h-[34px] px-3 rounded-[10px] text-[12px] transition-all flex items-center gap-1.5">
                            <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                            <span>{{ __('admin.text_message') }}</span>
                        </button>
                        <button type="button" @click="diagnosticMode = 'otp'; if(!otpCode) generateRandomOtp();"
                            :class="diagnosticMode === 'otp' ?
                                'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' :
                                'text-black/55 dark:text-white/55 font-medium'"
                            class="min-h-[34px] px-3 rounded-[10px] text-[12px] transition-all flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>{{ __('whatsapp.send_wa_otp') }}</span>
                        </button>
                    </div>
                </div>

                {{-- MODE 1: PESAN TEKS BIASA --}}
                <div x-show="diagnosticMode === 'message'" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                        <div>
                            <label
                                class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">{{ __('admin.recipient_phone_number') }}</label>
                            <input x-model="testPhone" type="tel"
                                placeholder="{{ __('whatsapp.phone_example_placeholder') }}"
                                class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition-all">
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">{{ __('admin.test_message_content') }}</label>
                            <textarea x-model="testMessage" rows="2" placeholder="{{ __('admin.test_message_placeholder') }}"
                                class="w-full bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] p-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition-all resize-none"></textarea>
                        </div>

                        <div class="md:col-span-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                            <div class="flex-1 min-w-0">
                                <div x-show="testResult" class="p-3.5 rounded-[12px] text-[12px] font-medium"
                                    :class="testOk ?
                                        'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20' :
                                        'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] border border-[#FF3B30]/20'"
                                    x-text="testResult"></div>
                            </div>
                            <button type="button" @click="sendTest()"
                                :disabled="testLoading || !testPhone.trim() || !testMessage.trim()"
                                class="min-h-[44px] px-6 rounded-[12px] text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#2DB34F] disabled:opacity-50 disabled:pointer-events-none active:scale-[0.98] transition-all inline-flex items-center justify-center gap-2 shadow-sm shrink-0">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="testLoading"></i>
                                <i data-lucide="send" class="w-4 h-4" x-show="!testLoading"></i>
                                <span x-text="testLoading ? @json(__('admin.sending_test_message')) : @json(__('admin.send_test_message'))"></span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- MODE 2: SIMULATOR KIRIM WA OTP RESMI --}}
                <div x-show="diagnosticMode === 'otp'" class="space-y-4" style="display: none;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                        <div class="space-y-3.5">
                            <div>
                                <label
                                    class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">{{ __('admin.otp_target_phone_number') }}</label>
                                <input x-model="otpPhone" type="tel"
                                    placeholder="{{ __('whatsapp.phone_example_placeholder') }}"
                                    class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label
                                        class="text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">{{ __('admin.otp_6digit_code') }}</label>
                                    <button type="button" @click="generateRandomOtp()"
                                        class="text-[11px] font-bold text-[#007AFF] hover:underline flex items-center gap-1">
                                        <i data-lucide="refresh-cw" class="w-3 h-3"></i>
                                        <span>{{ __('whatsapp.randomize_new_code') }}</span>
                                    </button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input x-model="otpCode" type="text" maxlength="8" placeholder="{{ __('admin.otp_code_placeholder') }}"
                                        class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[14px] font-mono font-bold tracking-widest text-[#007AFF] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                                </div>
                            </div>
                        </div>

                        {{-- Bubble Preview Template OTP --}}
                        <div
                            class="p-4 rounded-[16px] bg-[#007AFF]/5 border border-[#007AFF]/15 flex flex-col justify-between space-y-3">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-[10.5px] font-bold uppercase tracking-wider text-[#007AFF] flex items-center gap-1">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('admin.preview_meta_otp_template') }} ({{ $metaCreds['otp_template'] ?: 'cooca_otp' }})</span>
                                    </span>
                                    <span class="text-[10.5px] font-mono text-black/40 dark:text-white/40">Official
                                        Authentication</span>
                                </div>
                                <div
                                    class="p-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] shadow-sm border border-black/5 dark:border-white/10 text-[12.5px] space-y-1.5">
                                    <div class="font-bold text-black dark:text-white flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                                        <span>COOCA Authentication Service</span>
                                    </div>
                                    <p class="text-black/75 dark:text-white/75">
                                        {{ __('admin.cooca_otp_verification_message') }} <strong
                                            class="font-mono text-[14px] text-[#007AFF]"
                                            x-text="otpCode || '------'"></strong>
                                    </p>
                                    <p class="text-[11px] text-black/45 dark:text-white/45">
                                        {{ __('admin.otp_validity_warning') }}
                                    </p>
                                </div>
                            </div>

                            <p class="text-[11px] text-black/50 dark:text-white/50 italic">
                                {{ __('admin.otp_template_official_disclaimer') }}
                            </p>
                        </div>

                        <div
                            class="md:col-span-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1 border-t border-black/[0.04] dark:border-white/[0.06]">
                            <div class="flex-1 min-w-0">
                                <div x-show="otpResult" class="p-3.5 rounded-[12px] text-[12px] font-medium"
                                    :class="otpOk ?
                                        'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20' :
                                        'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] border border-[#FF3B30]/20'"
                                    x-text="otpResult"></div>
                            </div>
                            <button type="button" @click="sendOtpLive()" :disabled="otpLoading || !otpPhone.trim()"
                                class="min-h-[44px] px-6 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-50 disabled:pointer-events-none active:scale-[0.98] transition-all inline-flex items-center justify-center gap-2 shadow-sm shrink-0">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="otpLoading"></i>
                                <i data-lucide="shield-check" class="w-4 h-4" x-show="!otpLoading"></i>
                                <span x-text="otpLoading ? 'Mengirimkan WA OTP...' : 'Kirim WhatsApp OTP Sekarang'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 2: PENGINGAT LANGGANAN (H-7, H-3, H-1, HARI H)                        --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'reminders'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6 w-full min-w-0">

            {{-- Action Bar: Kirim Semua Pengingat --}}
            <div
                class="p-4 sm:p-6 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <h2 class="text-[17px] font-bold text-black dark:text-white">{{ __('whatsapp.subscription_active_period_reminder') }}</h2>
                    <p class="text-[12px] sm:text-[13px] text-black/55 dark:text-white/55 mt-0.5">{{ __('admin.wa_reminders_system_desc') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if(Auth::guard('admin')->user()?->hasPermission('admin.whatsapp.blast'))

                    <form method="POST" action="{{ route('admin.whatsapp.reminders.send-all') }}"
                        onsubmit="return confirm({{ Js::from(__('whatsapp.confirm_send_all_reminders')) }});">
                        @csrf
                        <button type="submit" :disabled="status !== 'connected'"
                            class="min-h-[48px] px-6 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-50 disabled:pointer-events-none text-white text-[13px] font-bold inline-flex items-center gap-2 shadow-sm transition-all active:scale-[0.98]">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>{{ __('whatsapp.send_all') }} ({{ $pendingRemindersCount }})</span>
                        </button>
                    </form>

                    @endif
                </div>
            </div>

            {{-- 4 Periode Bento Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                @php
                    $periodConfigs = [
                        'h-7' => [
                            'label' => __('admin.wa_period_h7'),
                            'badge' => 'bg-[#007AFF]/15 text-[#007AFF]',
                        ],
                        'h-3' => [
                            'label' => __('admin.wa_period_h3'),
                            'badge' => 'bg-[#FF9500]/15 text-[#FF9500]',
                        ],
                        'h-1' => [
                            'label' => __('admin.wa_period_h1'),
                            'badge' => 'bg-[#AF52DE]/15 text-[#AF52DE]',
                        ],
                        'hari_h' => [
                            'label' => __('admin.wa_period_hari_h'),
                            'badge' => 'bg-[#FF3B30]/15 text-[#FF3B30]',
                        ],
                    ];
                @endphp

                @foreach ($periodConfigs as $periodKey => $cfg)
                    <div
                        class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden flex flex-col justify-between">
                        <div>
                            <div
                                class="px-5 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span
                                        class="font-mono text-[11px] font-bold tracking-wider text-black/50 dark:text-white/50 uppercase">
                                        {{ strtoupper($periodKey) }}
                                    </span>
                                    <h3 class="text-[14px] font-bold text-black dark:text-white">{{ $cfg['label'] }}</h3>
                                </div>
                                <span class="text-[12px] font-semibold text-black/45 dark:text-white/45 tabular-nums">
                                    {{ __('admin.businesses_count', ['count' => count($dueData[$periodKey] ?? [])]) }}
                                </span>
                            </div>

                            @if (!empty($dueData[$periodKey]) && count($dueData[$periodKey]) > 0)
                                <div
                                    class="divide-y divide-black/[0.04] dark:divide-white/[0.06] max-h-72 overflow-y-auto">
                                    @foreach ($dueData[$periodKey] as $item)
                                        <div
                                            class="p-4 flex items-center justify-between gap-3 hover:bg-black/[0.01] dark:hover:bg-white/[0.02] transition-colors">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-bold text-[13px] text-black dark:text-white truncate">
                                                    {{ $item['business']->name ?? 'Bisnis' }}
                                                </div>
                                                <div
                                                    class="text-[11px] text-black/55 dark:text-white/55 flex items-center gap-1.5 mt-0.5 flex-wrap">
                                                    <span>{{ $item['owner']?->name ?? 'Owner' }}</span>
                                                    <span>&bull;</span>
                                                    <span class="font-mono">{{ $item['phone'] ?: 'No Phone' }}</span>
                                                    <span>&bull;</span>
                                                    <span
                                                        class="text-[#007AFF] font-medium">{{ $item['plan_name'] }}</span>
                                                </div>
                                            </div>

                                            <div class="shrink-0">
                                                @if ($item['already_sent'])
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                        <i data-lucide="check" class="w-3 h-3"></i>
                                                        <span>{{ __('admin.sent') }}</span>
                                                    </span>
                                                @elseif ($item['phone'])
                                                    <button type="button"
                                                        @click="sendSingleReminder({{ $item['subscription']->id }}, '{{ $periodKey }}')"
                                                        class="min-h-[40px] px-3.5 rounded-[10px] text-[12px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shadow-sm">
                                                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                                        <span>{{ __('admin.send') }}</span>
                                                    </button>
                                                @else
                                                    <span
                                                        class="text-[11.5px] font-medium text-[#FF3B30] dark:text-[#FF453A] inline-flex items-center gap-1">
                                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                                        <span>{{ __('admin.no_wa_number') }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-8 text-center text-[12px] text-black/40 dark:text-white/40">
                                    {{ __('admin.no_due_subscriptions_in_period') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Log Riwayat Pengingat Terkini (Table-to-Card Transformation View) --}}
            <div
                class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden">
                <div
                    class="px-5 sm:px-6 py-4.5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('admin.audit_log_reminders_sent') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('admin.audit_log_reminders_desc') }}</p>
                    </div>
                    <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">{{ __('admin.page_x_of_y', ['current' => $recentReminders->currentPage(), 'last' => $recentReminders->lastPage()]) }}</span>
                </div>

                {{-- Mobile Card List View (< md) --}}
                <div class="block md:hidden divide-y divide-black/[0.06] dark:divide-white/[0.06]">
                    @forelse ($recentReminders as $log)
                        <div class="p-4 space-y-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="font-bold text-[13.5px] text-black dark:text-white truncate">
                                        {{ $log->business_name }}</div>
                                    <div class="text-[11.5px] text-black/50 dark:text-white/50">{{ $log->owner_name }}
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    @if ($log->status === 'sent')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            Terkirim
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                            Gagal
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div
                                class="flex items-center justify-between text-[11.5px] text-black/60 dark:text-white/60 pt-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="font-mono text-[11px] font-bold uppercase text-black/60 dark:text-white/60 tracking-wider">
                                        {{ strtoupper($log->reminder_type) }}
                                    </span>
                                    <span
                                        class="font-mono text-black/70 dark:text-white/70">{{ $log->recipient_phone }}</span>
                                </div>
                                <span
                                    class="tabular-nums text-[11px] text-black/45 dark:text-white/45">{{ optional($log->sent_at)->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-[13px] text-black/40 dark:text-white/40">
                            {{ __('whatsapp.no_reminder_logs') }}
                        </div>
                    @endforelse
                </div>

                {{-- Desktop Table View (>= md) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-[13px]">
                        <thead>
                            <tr
                                class="border-b border-black/[0.06] dark:border-white/[0.08] text-black/45 dark:text-white/45 bg-black/[0.01] dark:bg-white/[0.02]">
                                <th class="text-left px-5 sm:px-6 py-3 font-semibold text-[11px] uppercase tracking-wider">
                                    {{ __('admin.business_name_and_owner') }}</th>
                                <th class="text-left px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">{{ __('admin.whatsapp_number') }}</th>
                                <th class="text-left px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">{{ __('admin.period') }}</th>
                                <th class="text-center px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">{{ __('admin.status') }}</th>
                                <th class="text-left px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">{{ __('admin.execution_time') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse ($recentReminders as $log)
                                <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.025] transition-colors">
                                    <td class="px-5 sm:px-6 py-3.5 font-medium text-black dark:text-white">
                                        <div>{{ $log->business_name }}</div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50 font-normal">
                                            {{ $log->owner_name }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 font-mono text-black/70 dark:text-white/70 tabular-nums">
                                        {{ $log->recipient_phone }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span
                                            class="font-mono text-[12px] font-semibold uppercase text-black/70 dark:text-white/70">
                                            {{ strtoupper($log->reminder_type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if ($log->status === 'sent')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                Terkirim
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                                Gagal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-black/50 dark:text-white/50 tabular-nums">
                                        {{ optional($log->sent_at)->format('d M Y, H:i') }} WIB
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="p-8 text-center text-[13px] text-black/40 dark:text-white/40">
                                        {{ __('whatsapp.no_reminder_logs') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($recentReminders->hasPages())
                    <div class="px-5 sm:px-6 py-3.5 border-t border-black/[0.06] dark:border-white/[0.08]">
                        {{ $recentReminders->appends(['tab' => 'reminders'])->links() }}
                    </div>
                @endif
            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 3: BROADCAST BISNIS OWNER                                             --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'blast'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6 w-full min-w-0">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 w-full min-w-0">

                {{-- Kolom Kiri: Form Buat Broadcast Baru (5 Kolom) --}}
                <div
                    class="lg:col-span-5 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-4 sm:p-6 space-y-5 w-full min-w-0">
                    <div class="min-w-0">
                        <h2 class="text-[17px] font-bold text-black dark:text-white">{{ __('admin.create_new_broadcast_message') }}</h2>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">{{ __('admin.create_broadcast_desc') }}</p>
                    </div>

                    {{-- Driver Active Indicator & Warning --}}
                    @if ($isBlastActive && ($liveStatus === 'connected' || !empty($metaCreds['token'])))
                        <div
                            class="p-3.5 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/25 text-[12px] text-[#248A3D] dark:text-[#30D158] space-y-1">
                            <div class="flex items-center gap-1.5 font-bold">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                <span>{{ __('admin.official_meta_wa_cloud_gateway') }}</span>
                            </div>
                            <p class="text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                                {{ __('admin.broadcast_max_performance_desc') }}
                            </p>
                        </div>
                    @else
                        <div
                            class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12px] text-[#C41E17] dark:text-[#FF453A]">
                            <strong>{{ __('admin.broadcast_channel_not_ready') }}:</strong> {{ __('admin.broadcast_channel_not_ready_desc') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.whatsapp.blasts.store') }}" class="space-y-4"
                        onsubmit="return confirm({{ Js::from(__('admin.confirm_send_broadcast_to_selected_owners')) }});">
                        @csrf

                        <div>
                            <label
                                class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">{{ __('admin.broadcast_title') }}</label>
                            <input name="title" required maxlength="255"
                                placeholder="{{ __('admin.broadcast_title_placeholder') }}"
                                class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">{{ __('admin.target_segment_owners') }}</label>
                            <select name="target_filter"
                                class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                                <option value="all_owners">{{ __('admin.all_registered_business_owners') }} ({{ number_format($targetCounts['all_owners']) }} {{ __('admin.contacts') }})</option>
                                <option value="active_subscribers">{{ __('admin.active_plan_subscribers') }} ({{ number_format($targetCounts['active_subscribers']) }} {{ __('admin.contacts') }})</option>
                                <option value="expiring_soon">{{ __('admin.expiring_in_7_days') }} ({{ number_format($targetCounts['expiring_soon']) }} {{ __('admin.contacts') }})</option>
                                <option value="free_tier">{{ __('admin.free_or_expired_plan') }} ({{ number_format($targetCounts['free_tier']) }} {{ __('admin.contacts') }})</option>
                            </select>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label
                                    class="text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">{{ __('admin.wa_message_content') }}</label>
                                <span class="text-[11px] text-black/45 dark:text-white/45 font-mono">{{ __('admin.max_3000_chars') }}</span>
                            </div>

                            {{-- Chip Variabel Interaktif --}}
                            <div class="flex flex-wrap gap-1.5 mb-2">
                                <span class="text-[11px] text-black/45 dark:text-white/45 self-center mr-1">{{ __('admin.click_to_insert') }}</span>
                                @foreach (['{owner}', '{bisnis}', '{paket}', '{tanggal_habis}'] as $v)
                                    <button type="button" @click="insertVariableToBlast('{{ $v }}')"
                                        class="px-2 py-0.5 rounded-[8px] text-[11px] font-mono bg-black/[0.05] dark:bg-white/[0.08] text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors">
                                        {{ $v }}
                                    </button>
                                @endforeach
                            </div>

                            <textarea id="blastMessageTextarea" name="message" required maxlength="3000" rows="8" x-model="blastMessage"
                                placeholder="{{ __('admin.blast_message_placeholder') }}"
                                class="w-full bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] p-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all resize-y"></textarea>
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 mb-1.5">{{ __('admin.banner_image_url_optional') }}</label>
                            <input name="media_url" type="url" placeholder="https://domain.com/banner-promo.jpg"
                                class="w-full min-h-[46px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                        </div>

                        {{-- Calming Reassurance Microcopy --}}
                        <div
                            class="p-3.5 rounded-[14px] bg-[#007AFF]/8 border border-[#007AFF]/20 text-[12px] text-[#007AFF] dark:text-[#0A84FF] flex items-start gap-2.5">
                            <i data-lucide="shield-check" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <span><strong>{{ __('admin.safe_delivery_label') }}:</strong> {{ __('admin.safe_delivery_desc') }}</span>
                        </div>

                        <button type="submit" :disabled="status !== 'connected'"
                            class="w-full min-h-[48px] sm:min-h-[50px] rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-50 disabled:pointer-events-none text-white text-[14px] font-bold inline-flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>{{ __('common.send') }}</span>
                        </button>
                    </form>
                </div>

                {{-- Kolom Kanan: Riwayat Broadcast Blast (7 Kolom) (Table-to-Card Transformation View) --}}
                <div
                    class="lg:col-span-7 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden flex flex-col justify-between w-full min-w-0">
                    <div>
                        <div
                            class="px-5 sm:px-6 py-4.5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                            <div>
                                <h2 class="text-[16px] font-bold text-black dark:text-white">{{ __('whatsapp.wa_broadcast_history') }}</h2>
                                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('admin.broadcast_history_desc') }}</p>
                            </div>
                            <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">{{ __('admin.total_messages_count', ['count' => $blasts->total()]) }}</span>
                        </div>

                        {{-- Mobile Card List View (< md) --}}
                        <div class="block md:hidden divide-y divide-black/[0.06] dark:divide-white/[0.06]">
                            @forelse ($blasts as $b)
                                <div class="p-4 space-y-2.5">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.whatsapp.blasts.show', $b) }}"
                                                class="font-bold text-[13.5px] text-black dark:text-white hover:text-[#007AFF] truncate block">
                                                {{ $b->title }}
                                            </a>
                                            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                                {{ optional($b->created_at)->format('d M Y, H:i') }} WIB
                                            </div>
                                        </div>
                                        <div class="shrink-0">
                                            @if ($b->status === 'completed')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                    Selesai
                                                </span>
                                            @elseif ($b->status === 'processing')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                                    Memproses
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                                    Gagal
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between text-[11.5px] text-black/60 dark:text-white/60 pt-1">
                                        <div class="flex items-center gap-2">
                                            <span>{{ str_replace('_', ' ', ucfirst($b->target_filter)) }}</span>
                                            <span>&bull;</span>
                                            <span
                                                class="tabular-nums font-semibold text-black dark:text-white">{{ $b->total_sent }}/{{ $b->total_recipients }}</span>
                                        </div>
                                        <a href="{{ route('admin.whatsapp.blasts.show', $b) }}"
                                            class="min-h-[32px] px-3 rounded-[8px] text-[11.5px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-all inline-flex items-center gap-1">
                                            <span>{{ __('common.detail') }}</span>
                                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-10 text-center text-[13px] text-black/40 dark:text-white/40">
                                    {{ __('whatsapp.no_blasts_sent') }}
                                </div>
                            @endforelse
                        </div>

                        {{-- Desktop Table View (>= md) --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-[13px]">
                                <thead>
                                    <tr
                                        class="border-b border-black/[0.06] dark:border-white/[0.08] text-black/45 dark:text-white/45 bg-black/[0.01] dark:bg-white/[0.02]">
                                        <th
                                            class="text-left px-5 sm:px-6 py-3 font-semibold text-[11px] uppercase tracking-wider">
                                            {{ __('admin.broadcast_title') }}</th>
                                        <th class="text-left px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">
                                            {{ __('admin.target') }}</th>
                                        <th
                                            class="text-center px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">
                                            {{ __('admin.deliverability') }}</th>
                                        <th
                                            class="text-center px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="text-right px-5 sm:px-6 py-3 font-semibold text-[11px] uppercase tracking-wider">
                                            {{ __('admin.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                    @forelse ($blasts as $b)
                                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.025] transition-colors">
                                            <td class="px-5 sm:px-6 py-4">
                                                <a href="{{ route('admin.whatsapp.blasts.show', $b) }}"
                                                    class="font-bold text-[#007AFF] hover:underline">
                                                    {{ $b->title }}
                                                </a>
                                                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                                    {{ optional($b->created_at)->format('d M Y, H:i') }} WIB
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-black/70 dark:text-white/70 text-[12px]">
                                                {{ str_replace('_', ' ', ucfirst($b->target_filter)) }}
                                            </td>
                                            <td class="px-4 py-4 text-center tabular-nums">
                                                <span
                                                    class="font-bold text-[#248A3D] dark:text-[#30D158]">{{ $b->total_sent }}</span>
                                                <span class="text-black/40 dark:text-white/40">/
                                                    {{ $b->total_recipients }}</span>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                @if ($b->status === 'completed')
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                        Selesai
                                                    </span>
                                                @elseif ($b->status === 'processing')
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                                        Memproses
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                                        Gagal
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-5 sm:px-6 py-4 text-right">
                                                <a href="{{ route('admin.whatsapp.blasts.show', $b) }}"
                                                    class="min-h-[36px] px-3 rounded-[10px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-all inline-flex items-center gap-1.5">
                                                    <span>{{ __('common.detail') }}</span>
                                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5"
                                                class="p-10 text-center text-[13px] text-black/40 dark:text-white/40">
                                                {{ __('whatsapp.no_blasts_sent') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($blasts->hasPages())
                        <div class="px-5 sm:px-6 py-3.5 border-t border-black/[0.06] dark:border-white/[0.08]">
                            {{ $blasts->appends(['tab' => 'blast'])->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 4: TEMPLATE NOTIFIKASI PESAN                                          --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'templates'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6 w-full min-w-0">

            {{-- ----------------------------------------------------------------- --}}
            {{-- SUBSEKSI 1: TEMPLATE PESAN RESMI META CLOUD API (v26.0)           --}}
            {{-- ----------------------------------------------------------------- --}}
            <div
                class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-4 sm:p-7 space-y-6 w-full min-w-0">

                {{-- Header Subseksi --}}
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-black/[0.06] dark:border-white/[0.08] min-w-0">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] animate-pulse"></span>
                                Meta Graph API v26.0
                            </span>
                            <span class="text-[12px] font-medium text-black/40 dark:text-white/40">WABA:
                                {{ $metaCreds['waba_id'] ?: '2292844188142290' }}</span>
                        </div>
                        <h2 class="text-[17px] font-bold text-black dark:text-white">{{ __('admin.waba_template_catalog') }}</h2>
                        <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('admin.waba_template_catalog_desc') }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <button type="button" @click="deployStandardTemplates()" :disabled="standardsDeploying"
                            class="min-h-[42px] px-4 rounded-[12px] bg-[#34C759] hover:bg-[#2DB34F] text-white text-[13px] font-bold inline-flex items-center gap-2 shadow-sm active:scale-[0.98] transition-all disabled:opacity-50">
                            <i data-lucide="cloud-upload" class="w-4 h-4" :class="{ 'animate-bounce': standardsDeploying }"></i>
                            <span :text="standardsDeploying ? @json(__('whatsapp.submitting_to_meta')) : @json(__('whatsapp.deploy_standard_templates_meta'))">{{ __('whatsapp.deploy_standard_templates_meta') }}</span>
                        </button>
                        <button type="button" @click="seedStandardTemplates()" :disabled="standardsSeeding"
                            class="min-h-[42px] px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/10 dark:hover:bg-white/12 text-black dark:text-white text-[13px] font-semibold inline-flex items-center gap-1.5 transition-all disabled:opacity-50">
                            <i data-lucide="database" class="w-4 h-4"></i>
                            <span>{{ __('whatsapp.install_locally') }}</span>
                        </button>
                        <button type="button" @click="syncMetaTemplates()" :disabled="templateSyncing"
                            class="min-h-[42px] px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/10 dark:hover:bg-white/12 text-black dark:text-white text-[13px] font-semibold inline-flex items-center gap-2 transition-all disabled:opacity-50">
                            <i data-lucide="refresh-cw" class="w-4 h-4" :class="{ 'animate-spin': templateSyncing }"></i>
                            <span :text="templateSyncing ? @json(__('whatsapp.syncing_with_meta')) : @json(__('whatsapp.sync_from_meta'))">{{ __('whatsapp.sync_from_meta') }}</span>
                        </button>
                        @if(Auth::guard('admin')->user()?->hasPermission('admin.whatsapp.templates'))

                        <button type="button" @click="purgeNonIdEnTemplates()" :disabled="purgingNonIdEn"
                            class="min-h-[42px] px-3.5 rounded-[12px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] dark:text-[#FF453A] border border-[#FF3B30]/20 text-[13px] font-semibold inline-flex items-center gap-1.5 transition-all disabled:opacity-50"
                            title="{{ __('whatsapp.purge_non_id_en_tooltip') }}">

                            <i data-lucide="trash-2" class="w-4 h-4" :class="{ 'animate-pulse': purgingNonIdEn }"></i>
                            <span :text="purgingNonIdEn ? @json(__('whatsapp.cleaning_in_progress')) : @json(__('whatsapp.purge_non_id_en'))">{{ __('whatsapp.purge_non_id_en') }}</span>
                        </button>

                        <button type="button" @click="openCreateTemplateModal()"
                            class="min-h-[42px] px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold inline-flex items-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>{{ __('admin.create_custom_template') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Bento Stats Grid --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    {{-- Total --}}
                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                        <div>
                            <div class="text-[11px] font-semibold text-black/45 dark:text-white/45">{{ __('admin.total_templates') }}</div>
                            <div class="text-[22px] font-bold text-black dark:text-white font-mono mt-0.5"
                                x-text="(metaTemplates || []).length">
                                {{ count($metaTemplates) }}
                            </div>
                        </div>
                        <div
                            class="w-9 h-9 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] flex items-center justify-center text-black/60 dark:text-white/60">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </div>
                    </div>

                    {{-- Approved --}}
                    <div
                        class="p-4 rounded-[16px] bg-[#34C759]/[0.05] border border-[#34C759]/20 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] font-semibold text-[#34C759]">{{ __('admin.status_approved_meta') }}</div>
                            <div class="text-[22px] font-bold text-[#34C759] font-mono mt-0.5"
                                x-text="(metaTemplates || []).filter(t => (t.status || '').toUpperCase() === 'APPROVED').length">
                                {{ $metaTemplates->filter(fn($t) => strtoupper($t->status) === 'APPROVED')->count() }}
                            </div>
                        </div>
                        <div
                            class="w-9 h-9 rounded-[10px] bg-[#34C759]/15 flex items-center justify-center text-[#34C759]">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        </div>
                    </div>

                    {{-- Pending --}}
                    <div
                        class="p-4 rounded-[16px] bg-[#FF9500]/[0.05] border border-[#FF9500]/20 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] font-semibold text-[#FF9500]">{{ __('admin.status_pending_meta') }}</div>
                            <div class="text-[22px] font-bold text-[#FF9500] font-mono mt-0.5"
                                x-text="(metaTemplates || []).filter(t => (t.status || '').toUpperCase() === 'PENDING').length">
                                {{ $metaTemplates->filter(fn($t) => strtoupper($t->status) === 'PENDING')->count() }}
                            </div>
                        </div>
                        <div
                            class="w-9 h-9 rounded-[10px] bg-[#FF9500]/15 flex items-center justify-center text-[#FF9500]">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                    </div>

                    {{-- Rejected / Other --}}
                    <div
                        class="p-4 rounded-[16px] bg-[#FF3B30]/[0.05] border border-[#FF3B30]/20 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] font-semibold text-[#FF3B30]">{{ __('admin.status_rejected_meta') }}</div>
                            <div class="text-[22px] font-bold text-[#FF3B30] font-mono mt-0.5"
                                x-text="(metaTemplates || []).filter(t => ['REJECTED', 'PAUSED', 'DISABLED'].includes((t.status || '').toUpperCase())).length">
                                {{ $metaTemplates->filter(fn($t) => in_array(strtoupper($t->status), ['REJECTED', 'PAUSED', 'DISABLED']))->count() }}
                            </div>
                        </div>
                        <div
                            class="w-9 h-9 rounded-[10px] bg-[#FF3B30]/15 flex items-center justify-center text-[#FF3B30]">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>

                {{-- ----------------------------------------------------------------- --}}
                {{-- BENTO SHOWCASE: 7 TEMPLATE STANDAR RESMI META (ANTI-BLOKIR SYSTEM)--}}
                {{-- ----------------------------------------------------------------- --}}
                <div class="rounded-[20px] bg-gradient-to-br from-[#1877F2]/[0.06] via-white dark:via-[#1C1C1E] to-[#34C759]/[0.06] border border-[#1877F2]/20 dark:border-white/10 p-5 sm:p-6 space-y-5 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-start gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.meta_standard_templates_title') }}</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                        Graph API v26.0 Compliant
                                    </span>
                                </div>
                                <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5 leading-relaxed">
                                    {{ __('admin.meta_standard_templates_desc') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" @click="deployStandardTemplates()" :disabled="standardsDeploying"
                                class="min-h-[40px] px-3.5 rounded-[11px] bg-[#34C759] hover:bg-[#2DB34F] text-white text-[12.5px] font-bold inline-flex items-center gap-1.5 shadow-sm active:scale-[0.98] transition-all disabled:opacity-50">
                                <i data-lucide="cloud-upload" class="w-3.5 h-3.5" :class="{ 'animate-bounce': standardsDeploying }"></i>
                                <span>{{ __('whatsapp.deploy_11_templates_meta') }}</span>
                            </button>
                            <button type="button" @click="seedStandardTemplates()" :disabled="standardsSeeding"
                                class="min-h-[40px] px-3 rounded-[11px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/10 text-black dark:text-white text-[12.5px] font-semibold inline-flex items-center gap-1.5 transition-all disabled:opacity-50">
                                <i data-lucide="database" class="w-3.5 h-3.5"></i>
                                <span>{{ __('whatsapp.install_in_database') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- 11 Standard Templates Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5 sm:gap-4">
                        @php
                            $standards = [
                                [
                                    'name' => 'cooca_pos_receipt',
                                    'category' => 'UTILITY',
                                    'title' => 'Struk Kasir POS Digital',
                                    'desc' => 'Digunakan otomatis kasir saat checkout POS untuk struk digital tanpa kertas.',
                                    'header' => 'Struk Pembelian Kasir',
                                    'body' => "Halo {{1}}! Terima kasih telah berbelanja di {{2}}.\n\nRincian transaksi Anda:\n• No. Struk: {{3}}\n• Waktu: {{4}}\n• Total Bayar: {{5}}.",
                                    'footer' => 'Layanan Kasir Resmi Cooca POS',
                                    'btn' => 'Buka Struk Digital',
                                ],
                                [
                                    'name' => 'cooca_reservation_reminder',
                                    'category' => 'UTILITY',
                                    'title' => 'Pengingat Reservasi Meja & Booking',
                                    'desc' => 'Pengingat otomatis jadwal reservasi meja restoran F&B, booking servis bengkel, klinik, salon.',
                                    'header' => 'Pengingat Reservasi Anda',
                                    'body' => "Halo {{1}}! Mengingatkan kembali jadwal reservasi Anda di {{2}}:\n• Kode Booking: {{3}}\n• Jadwal: {{4}}\n• Detail: {{5}}.",
                                    'footer' => 'Layanan Reservasi Resmi Cooca',
                                    'btn' => 'Lihat Reservasi',
                                ],
                                [
                                    'name' => 'cooca_marketplace_receipt',
                                    'category' => 'UTILITY',
                                    'title' => 'Bukti Bayar Toko Online & Marketplace',
                                    'desc' => 'Notifikasi instan konfirmasi pembayaran pesanan marketplace / web storefront yang berhasil diverifikasi.',
                                    'header' => 'Bukti Pembayaran Berhasil',
                                    'body' => "Halo {{1}}! Pembayaran Anda untuk pesanan {{2}} di {{3}} sebesar {{4}} via {{5}} telah berhasil diverifikasi.",
                                    'footer' => 'Konfirmasi Otomatis Cooca Storefront',
                                    'btn' => 'Pantau Pesanan',
                                ],
                                [
                                    'name' => 'cooca_shipping_tracking',
                                    'category' => 'UTILITY',
                                    'title' => 'Resi Pengiriman Kurir Ekspedisi',
                                    'desc' => 'Notifikasi pesanan telah diserahkan ke kurir (J&T, SiCepat, JNE) beserta nomor resi pelacakan online.',
                                    'header' => 'Pesanan Telah Dikirim',
                                    'body' => "Halo {{1}}! Paket pesanan nomor {{2}} dari {{3}} telah diserahkan ke ekspedisi {{4}} dengan nomor resi: {{5}}.",
                                    'footer' => 'Integrasi Logistik Resmi Cooca',
                                    'btn' => 'Lacak Resi Pengiriman',
                                ],
                                [
                                    'name' => 'cooca_cart_reminder',
                                    'category' => 'MARKETING',
                                    'title' => 'Pengingat Keranjang Belanja (Abandoned Cart)',
                                    'desc' => 'Follow-up otomatis ke pembeli yang belum checkout keranjang belanja toko online dilengkapi insentif kupon.',
                                    'header' => 'Ada Item Menunggumu di Keranjang!',
                                    'body' => "Halo {{1}}! Anda masih memiliki produk di keranjang belanja {{2}}:\n• Item: {{3}}\n• Promo: {{4}}\n• Batas Waktu: {{5}}.",
                                    'footer' => 'Balas STOP untuk berhenti berlangganan info promo.',
                                    'btn' => 'Lanjutkan Pembayaran',
                                ],
                                [
                                    'name' => 'cooca_sales_invoice',
                                    'category' => 'UTILITY',
                                    'title' => 'Faktur & Tagihan Penjualan',
                                    'desc' => 'Dikirim saat menerbitkan invoice atau tagihan penjualan kepada pelanggan/klien.',
                                    'header' => 'Faktur Tagihan Resmi',
                                    'body' => "Yth. {{1}},\n\nFaktur tagihan dari {{2}}:\n• No. Invoice: {{3}}\n• Jatuh Tempo: {{4}}\n• Total Tagihan: {{5}}.",
                                    'footer' => 'Sistem Akuntansi Terpadu Cooca',
                                    'btn' => 'Lihat / Bayar Invoice',
                                ],
                                [
                                    'name' => 'cooca_order_status_update',
                                    'category' => 'UTILITY',
                                    'title' => 'Pembaruan Status Pesanan',
                                    'desc' => 'Notifikasi real-time update progres pesanan (F&B, Bengkel, Laundry, Manufaktur).',
                                    'header' => 'Status Pesanan: {{1}}',
                                    'body' => "Halo {{1}}!\n\nPesanan Anda nomor {{2}} di {{3}} telah diperbarui statusnya menjadi: {{4}}.\nCatatan: {{5}}.",
                                    'footer' => 'Terima kasih telah mempercayai layanan kami.',
                                    'btn' => 'Cek Status Pesanan',
                                ],
                                [
                                    'name' => 'cooca_promo_broadcast',
                                    'category' => 'MARKETING',
                                    'title' => 'Blast Promosi & Diskon',
                                    'desc' => 'Template resmi kirim broadcast penawaran massal ke pelanggan setia tanpa risiko blokir.',
                                    'header' => 'MEDIA: Banner / Gambar Promo',
                                    'body' => "Halo {{1}}! Ada kabar gembira dari {{2}}.\n\nNikmati penawaran spesial: {{3}}.\nGunakan kode voucher: {{4}} saat bertransaksi.\nBerlaku hingga: {{5}}.",
                                    'footer' => 'Balas STOP untuk berhenti berlangganan info promo.',
                                    'btn' => 'Klaim Penawaran',
                                ],
                                [
                                    'name' => 'cooca_customer_welcome',
                                    'category' => 'MARKETING',
                                    'title' => 'Sambutan Pelanggan Baru',
                                    'desc' => 'Ucapan selamat datang saat customer mendaftar member atau transaksi pertama.',
                                    'header' => 'Selamat Datang di {{1}}!',
                                    'body' => "Halo {{1}},\n\nTerima kasih telah menjadi bagian dari keluarga besar {{2}}! Anda terdaftar sebagai {{3}} dengan {{4}} poin awal.",
                                    'footer' => 'Balas STOP untuk berhenti berlangganan info promo.',
                                    'btn' => 'Jelajahi Produk Kami',
                                ],
                                [
                                    'name' => 'cooca_payment_reminder',
                                    'category' => 'UTILITY',
                                    'title' => 'Pengingat Jatuh Tempo Tagihan',
                                    'desc' => 'Pengingat sopan H-7, H-3, H-1 jatuh tempo tagihan langganan atau piutang toko.',
                                    'header' => 'Pengingat Tagihan: {{1}}',
                                    'body' => "Yth. {{1}},\n\nMengingatkan kembali bahwa tagihan {{2}} untuk {{3}} sebesar {{4}} akan jatuh tempo pada {{5}}.",
                                    'footer' => 'Abaikan pesan ini jika Anda telah menyelesaikan pembayaran.',
                                    'btn' => 'Bayar Sekarang',
                                ],
                                [
                                    'name' => 'cooca_otp',
                                    'category' => 'AUTHENTICATION',
                                    'title' => 'Kode Keamanan & Verifikasi OTP',
                                    'desc' => 'Autentikasi login, reset PIN kasir, dan verifikasi verifikasi transaksi sensitif.',
                                    'header' => null,
                                    'body' => "Kode verifikasi COOCA Anda adalah: {{1}}.\n\nDemi keamanan akun Anda, jangan pernah membagikan kode rahasia ini kepada siapa pun termasuk staf kasir atau admin.",
                                    'footer' => 'Kode ini hanya berlaku selama 10 menit.',
                                    'btn' => 'Salin Kode',
                                ],
                            ];
                        @endphp

                        @foreach ($standards as $std)
                            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 flex flex-col justify-between space-y-3 shadow-xs hover:border-[#1877F2]/40 transition-all">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between gap-1.5">
                                        <span class="px-2 py-0.5 rounded-[5px] text-[10px] font-bold uppercase tracking-wide
                                            {{ $std['category'] === 'UTILITY' ? 'bg-[#007AFF]/15 text-[#007AFF]' : ($std['category'] === 'MARKETING' ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#AF52DE]/15 text-[#AF52DE]') }}">
                                            {{ $std['category'] }}
                                        </span>

                                        <span class="inline-flex items-center gap-1 text-[10.5px] font-semibold"
                                            :class="(metaTemplates || []).some(t => t.name === '{{ $std['name'] }}') ? 'text-[#34C759]' : 'text-black/40 dark:text-white/40'">
                                            <i data-lucide="check-circle" class="w-3 h-3" x-show="(metaTemplates || []).some(t => t.name === '{{ $std['name'] }}')"></i>
                                            <span x-text="(metaTemplates || []).some(t => t.name === '{{ $std['name'] }}') ? 'Terpasang' : 'Belum Ada'"></span>
                                        </span>
                                    </div>

                                    <div>
                                        <h4 class="text-[13.5px] font-bold text-black dark:text-white leading-snug">{{ $std['title'] }}</h4>
                                        <code class="text-[11px] text-[#007AFF] font-mono mt-0.5 block">{{ $std['name'] }}</code>
                                        <p class="text-[11.5px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">{{ $std['desc'] }}</p>
                                    </div>

                                    {{-- Mini Chat Bubble --}}
                                    <div class="rounded-[12px] bg-[#EFEAE2] dark:bg-[#0B141A] p-2.5 text-[11px] space-y-1">
                                        @if ($std['header'])
                                            <div class="font-bold text-black/70 dark:text-white/70 text-[10.5px] border-b border-black/5 dark:border-white/5 pb-0.5">
                                                {{ $std['header'] }}
                                            </div>
                                        @endif
                                        <div class="text-black/85 dark:text-white/90 leading-relaxed font-sans whitespace-pre-line text-[11px]">
                                            {!! preg_replace('/(\{\{\d+\}\})/', '<span class="px-1 py-0.2 rounded bg-[#007AFF]/15 text-[#007AFF] font-mono font-semibold">$1</span>', e($std['body'])) !!}
                                        </div>
                                        <div class="text-[9.5px] text-black/45 dark:text-white/45 pt-0.5 border-t border-black/5 dark:border-white/5">
                                            {{ $std['footer'] }}
                                        </div>
                                        <div class="w-full py-1 px-2 rounded-[6px] bg-white dark:bg-[#1F2C34] text-center text-[10px] font-semibold text-[#007AFF] shadow-xs mt-1">
                                            {{ $std['btn'] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                                    <span>{{ __('admin.language') }}: <strong class="font-mono text-black dark:text-white">ID (Indonesia)</strong></span>
                                    <span class="text-[10px] font-medium text-[#34C759]">Opt-out Guarded</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Language Filter Segmented Pill & Guard Notice (Hanya ID & EN) --}}
                <div x-show="(metaTemplates || []).length > 0" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                    <div class="flex items-center gap-1 bg-black/[0.04] dark:bg-white/[0.06] p-1 rounded-[12px] border border-black/5 dark:border-white/5 w-fit">
                        <button type="button" @click="selectedLangFilter = 'all'"
                            :class="selectedLangFilter === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                            class="px-3 py-1.5 rounded-[8px] text-[12px] transition-all flex items-center gap-1.5 cursor-pointer">
                            <span>{{ __('whatsapp.all_templates') }}</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10.5px] font-mono bg-black/10 dark:bg-white/10"
                                x-text="(filteredTemplates || []).length"></span>
                        </button>
                        <button type="button" @click="selectedLangFilter = 'id'"
                            :class="selectedLangFilter === 'id' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                            class="px-3 py-1.5 rounded-[8px] text-[12px] transition-all flex items-center gap-1.5 cursor-pointer">
                            <span>🇮🇩 {{ __('whatsapp.indonesian_id') }}</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10.5px] font-mono bg-black/10 dark:bg-white/10"
                                x-text="(metaTemplates || []).filter(t => (t.language || '').toLowerCase().startsWith('id')).length"></span>
                        </button>
                        <button type="button" @click="selectedLangFilter = 'en'"
                            :class="selectedLangFilter === 'en' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                            class="px-3 py-1.5 rounded-[8px] text-[12px] transition-all flex items-center gap-1.5 cursor-pointer">
                            <span>🇬🇧 English (en)</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10.5px] font-mono bg-black/10 dark:bg-white/10"
                                x-text="(metaTemplates || []).filter(t => (t.language || '').toLowerCase().startsWith('en')).length"></span>
                        </button>
                    </div>

                    <div class="text-[12px] text-black/50 dark:text-white/50 flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#34C759]"></i>
                        <span>{{ __('admin.language_standard_notice') }}</span>
                    </div>
                </div>

                {{-- Template Card Grid --}}
                <div x-show="(filteredTemplates || []).length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                    <template x-for="tpl in filteredTemplates" :key="tpl.id">
                        <div
                            class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between space-y-4 hover:border-black/15 dark:hover:border-white/20 transition-all">

                            {{-- Top Header info --}}
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    {{-- Category Badge --}}
                                    <span
                                        :class="{
                                            'bg-[#007AFF]/15 text-[#007AFF]': (tpl.category || '')
                                                .toUpperCase() === 'UTILITY',
                                            'bg-[#34C759]/15 text-[#34C759]': (tpl.category || '')
                                                .toUpperCase() === 'MARKETING',
                                            'bg-[#AF52DE]/15 text-[#AF52DE]': (tpl.category || '')
                                                .toUpperCase() === 'AUTHENTICATION',
                                            'bg-black/10 dark:bg-white/10 text-black/70 dark:text-white/70': ![
                                                'UTILITY', 'MARKETING', 'AUTHENTICATION'
                                            ].includes((tpl.category || '').toUpperCase())
                                        }"
                                        class="px-2.5 py-0.5 rounded-[6px] text-[10px] font-bold tracking-wide uppercase"
                                        x-text="tpl.category || 'UTILITY'">
                                    </span>

                                    {{-- Status Badge --}}
                                    <span
                                        :class="{
                                            'bg-[#34C759]/15 text-[#34C759] border-[#34C759]/30': (tpl.status || '')
                                                .toUpperCase() === 'APPROVED',
                                            'bg-[#FF9500]/15 text-[#FF9500] border-[#FF9500]/30': (tpl.status || '')
                                                .toUpperCase() === 'PENDING',
                                            'bg-[#FF3B30]/15 text-[#FF3B30] border-[#FF3B30]/30': ['REJECTED', 'PAUSED',
                                                'DISABLED'
                                            ].includes((tpl.status || '').toUpperCase()),
                                            'bg-black/10 text-black/60 border-black/20': !['APPROVED', 'PENDING',
                                                'REJECTED', 'PAUSED', 'DISABLED'
                                            ].includes((tpl.status || '').toUpperCase())
                                        }"
                                        class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold border">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                            :class="{
                                                'bg-[#34C759]': (tpl.status || '').toUpperCase() === 'APPROVED',
                                                'bg-[#FF9500]': (tpl.status || '').toUpperCase() === 'PENDING',
                                                'bg-[#FF3B30]': ['REJECTED', 'PAUSED', 'DISABLED'].includes((tpl
                                                    .status || '').toUpperCase()),
                                                'bg-black/40': !['APPROVED', 'PENDING', 'REJECTED', 'PAUSED',
                                                    'DISABLED'
                                                ].includes((tpl.status || '').toUpperCase())
                                            }"></span>
                                        <span x-text="tpl.status"></span>
                                    </span>
                                </div>

                                <div>
                                    <h3 class="text-[14px] font-bold text-black dark:text-white font-mono truncate"
                                        :title="tpl.name" x-text="tpl.name"></h3>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[11px] text-black/45 dark:text-white/45 font-mono">{{ __('admin.language') }}: <span
                                                class="uppercase font-semibold text-black/70 dark:text-white/70"
                                                x-text="tpl.language"></span></span>
                                        <span x-show="tpl.quality_score"
                                            class="text-[11px] text-black/45 dark:text-white/45 font-mono">• {{ __('admin.quality') }}:
                                            <span class="font-semibold text-[#34C759]"
                                                x-text="tpl.quality_score"></span></span>
                                    </div>
                                </div>
                            </div>

                            {{-- WhatsApp Chat Bubble Preview (iOS WhatsApp Style) --}}
                            <div
                                class="rounded-[16px] bg-[#EFEAE2] dark:bg-[#0B141A] p-3 sm:p-3.5 border border-black/[0.04] dark:border-white/[0.04]">
                                <div
                                    class="rounded-[14px] rounded-tl-none bg-white dark:bg-[#1F2C34] p-3 space-y-1.5 shadow-sm border border-black/[0.04] dark:border-white/[0.06] text-[12px]">
                                    {{-- Header Preview --}}
                                    <div x-show="getTemplateHeader(tpl)"
                                        class="font-bold text-[12px] text-black dark:text-white border-b border-black/[0.06] dark:border-white/[0.06] pb-1"
                                        x-text="getTemplateHeader(tpl)">
                                    </div>

                                    {{-- Body Preview --}}
                                    <div class="text-black/85 dark:text-white/90 leading-relaxed font-sans whitespace-pre-line text-[12px]"
                                        x-html="formatBodyPreview(getTemplateBody(tpl))">
                                    </div>

                                    {{-- Footer Preview --}}
                                    <div x-show="getTemplateFooter(tpl)"
                                        class="text-[10px] text-black/45 dark:text-white/45 pt-1 border-t border-black/[0.04] dark:border-white/[0.04]"
                                        x-text="getTemplateFooter(tpl)">
                                    </div>
                                </div>

                                {{-- Buttons Preview --}}
                                <div x-show="getTemplateButtons(tpl).length > 0" class="mt-1.5 space-y-1">
                                    <template x-for="btn in getTemplateButtons(tpl)" :key="btn.text">
                                        <div
                                            class="w-full py-1.5 px-3 rounded-[10px] bg-white dark:bg-[#1F2C34] text-center text-[11px] font-semibold text-[#007AFF] dark:text-[#3897F0] shadow-sm flex items-center justify-center gap-1.5">
                                            <i data-lucide="corner-up-right" class="w-3 h-3"></i>
                                            <span x-text="btn.text"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Rejection alert if any --}}
                            <div x-show="tpl.rejected_reason"
                                class="p-2.5 rounded-[10px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[11px] text-[#FF3B30] flex items-start gap-2">
                                <i data-lucide="alert-triangle" class="w-3.5 h-3.5 shrink-0 mt-0.5"></i>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold">{{ __('admin.meta_rejection_reason') }}</div>
                                    <div class="text-black/70 dark:text-white/70" x-text="tpl.rejected_reason"></div>
                                </div>
                            </div>

                            {{-- Bottom actions --}}
                            <div
                                class="flex items-center justify-between pt-2 border-t border-black/[0.06] dark:border-white/[0.08] text-[11px]">
                                <span class="font-mono text-black/40 dark:text-white/40 truncate max-w-[140px]"
                                    :title="'ID Meta: ' + (tpl.meta_template_id || tpl.id)"
                                    x-text="'ID: ' + (tpl.meta_template_id ? tpl.meta_template_id.substring(0, 10) + '...' : '-')"></span>
                                <button type="button" @click="deleteMetaTemplate(tpl)"
                                    class="inline-flex items-center gap-1 text-[#FF3B30] hover:text-[#D70015] font-semibold p-1 rounded hover:bg-[#FF3B30]/10 transition-colors">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('common.delete') }}</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Empty State --}}
                <div x-show="(metaTemplates || []).length === 0"
                    class="p-8 sm:p-12 text-center rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-dashed border-black/10 dark:border-white/10 space-y-4">
                    <div
                        class="w-12 h-12 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                        <i data-lucide="file-text" class="w-6 h-6"></i>
                    </div>
                    <div class="max-w-md mx-auto space-y-1">
                        <h4 class="text-[15px] font-bold text-black dark:text-white">{{ __('whatsapp.no_templates_synced') }}</h4>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('admin.no_templates_synced_desc') }}</p>
                    </div>
                    <div class="flex items-center justify-center gap-3 pt-2">
                        <button type="button" @click="syncMetaTemplates()" :disabled="templateSyncing"
                            class="min-h-[40px] px-5 rounded-[12px] bg-black/[0.06] dark:bg-white/[0.1] hover:bg-black/10 text-black dark:text-white text-[13px] font-bold inline-flex items-center gap-2 transition-all">
                            <i data-lucide="refresh-cw" class="w-4 h-4" :class="{ 'animate-spin': templateSyncing }"></i>
                            <span>{{ __('admin.sync_now') }}</span>
                        </button>
                        <button type="button" @click="openCreateTemplateModal()"
                            class="min-h-[40px] px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold inline-flex items-center gap-2 transition-all">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>{{ __('admin.create_new_template') }}</span>
                        </button>

                        @endif
                    </div>
                </div>

            </div>

            {{-- ----------------------------------------------------------------- --}}
            {{-- MODAL SHEET: BUAT TEMPLATE BARU (BENTO APPLE HIG)                 --}}
            {{-- ----------------------------------------------------------------- --}}
            <template x-teleport="body">
                <div x-show="showCreateTemplateModal" x-cloak
                    class="fixed inset-0 z-[200] overflow-y-auto"
                    role="dialog" aria-modal="true"
                    @keydown.escape.window="closeCreateTemplateModal()">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                        @click="closeCreateTemplateModal()"
                        aria-hidden="true"></div>

                    <!-- Centering Wrapper -->
                    <div class="relative z-10 min-h-full flex items-center justify-center p-4 sm:p-6 pointer-events-none">
                        <div class="w-full max-w-4xl max-h-[90vh] flex flex-col rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden pointer-events-auto"
                            @click.stop>

                    {{-- Modal Header --}}
                    <div
                        class="px-6 py-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="text-[17px] font-bold text-black dark:text-white">{{ __('admin.create_meta_message_template') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">{{ __('admin.create_meta_template_desc') }}</p>
                        </div>
                        <button type="button" @click="closeCreateTemplateModal()"
                            class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/10 dark:hover:bg-white/15 flex items-center justify-center text-black/60 dark:text-white/60 transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    {{-- Modal Body (2 Columns) --}}
                    <div class="p-6 overflow-y-auto flex-1 grid grid-cols-1 lg:grid-cols-12 gap-6">
                        {{-- Form Inputs (Col 7) --}}
                        <div class="lg:col-span-7 space-y-4">
                            {{-- Nama Template --}}
                            <div class="space-y-1.5">
                                <label
                                    class="text-[12px] font-bold text-black dark:text-white flex items-center justify-between">
                                    <span>{{ __('admin.template_name') }} <span class="text-[#FF3B30]">*</span></span>
                                    <span class="text-[11px] font-mono text-black/40 dark:text-white/40">{{ __('admin.lowercase_underscore_only') }}</span>
                                </label>
                                <input type="text" x-model="newTemplate.name" required
                                    placeholder="{{ __('whatsapp.template_name_example_placeholder') }}"
                                    class="w-full bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 py-2.5 text-[13px] font-mono text-black dark:text-white placeholder-black/30 dark:placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                <p class="text-[11px] text-black/45 dark:text-white/45">{{ __('admin.template_name_hint') }}</p>
                            </div>

                            {{-- Kategori & Bahasa --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-[12px] font-bold text-black dark:text-white">{{ __('admin.category') }} <span
                                            class="text-[#FF3B30]">*</span></label>
                                    <select x-model="newTemplate.category"
                                        class="w-full bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 py-2.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                        <option value="UTILITY">{{ __('admin.category_utility_desc') }}</option>
                                        <option value="MARKETING">{{ __('admin.category_marketing_desc') }}</option>
                                        <option value="AUTHENTICATION">{{ __('admin.category_auth_desc') }}</option>
                                    </select>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[12px] font-bold text-black dark:text-white">{{ __('admin.language') }} <span
                                            class="text-[#FF3B30]">*</span></label>
                                    <select x-model="newTemplate.language"
                                        class="w-full bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 py-2.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                        <option value="id">Bahasa Indonesia (id)</option>
                                        <option value="en_US">English - US (en_US)</option>
                                        <option value="en_GB">English - UK (en_GB)</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Header Teks --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-[12px] font-bold text-black dark:text-white">{{ __('admin.header_text_optional') }}</label>
                                    <span class="text-[11px] font-mono text-black/40 dark:text-white/40"
                                        x-text="(newTemplate.header_text || '').length + '/60'"></span>
                                </div>
                                <input type="text" x-model="newTemplate.header_text" maxlength="60"
                                    placeholder="{{ __('whatsapp.header_text_example_placeholder') }}"
                                    class="w-full bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 py-2.5 text-[13px] text-black dark:text-white placeholder-black/30 dark:placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                            </div>

                            {{-- Body Teks --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-[12px] font-bold text-black dark:text-white">{{ __('admin.message_body_text') }} <span class="text-[#FF3B30]">*</span></label>
                                    <span class="text-[11px] font-mono text-black/40 dark:text-white/40"
                                        x-text="(newTemplate.body_text || '').length + '/1024'"></span>
                                </div>
                                <textarea x-model="newTemplate.body_text" rows="5" maxlength="1024" required
                                    placeholder="{{ __('whatsapp.body_text_example_placeholder') }}"
                                    class="w-full bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 rounded-[12px] p-3 text-[13px] text-black dark:text-white placeholder-black/30 dark:placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 leading-relaxed resize-y"></textarea>

                                {{-- Quick Insert Placeholder Variables --}}
                                <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                    <span class="text-[11px] font-medium text-black/40 dark:text-white/40">{{ __('admin.insert_variable') }}</span>
                                    <button type="button" @click="insertVariableToNewTemplate('@{{ 1 }}')"
                                        class="px-2 py-0.5 rounded-[6px] text-[11px] font-mono font-bold bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition-colors">
                                        + @{{ 1 }}
                                    </button>
                                    <button type="button" @click="insertVariableToNewTemplate('@{{ 2 }}')"
                                        class="px-2 py-0.5 rounded-[6px] text-[11px] font-mono font-bold bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition-colors">
                                        + @{{ 2 }}
                                    </button>
                                    <button type="button" @click="insertVariableToNewTemplate('@{{ 3 }}')"
                                        class="px-2 py-0.5 rounded-[6px] text-[11px] font-mono font-bold bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition-colors">
                                        + @{{ 3 }}
                                    </button>
                                    <button type="button" @click="insertVariableToNewTemplate('@{{ 4 }}')"
                                        class="px-2 py-0.5 rounded-[6px] text-[11px] font-mono font-bold bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition-colors">
                                        + @{{ 4 }}
                                    </button>
                                </div>
                            </div>

                            {{-- Footer Teks --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-[12px] font-bold text-black dark:text-white">{{ __('admin.footer_text_optional') }}</label>
                                    <span class="text-[11px] font-mono text-black/40 dark:text-white/40"
                                        x-text="(newTemplate.footer_text || '').length + '/60'"></span>
                                </div>
                                <input type="text" x-model="newTemplate.footer_text" maxlength="60"
                                    placeholder="{{ __('whatsapp.footer_text_example_placeholder') }}"
                                    class="w-full bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 py-2.5 text-[13px] text-black dark:text-white placeholder-black/30 dark:placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                            </div>
                        </div>

                        {{-- Live Preview (Col 5) --}}
                        <div class="lg:col-span-5 flex flex-col">
                            <label class="text-[12px] font-bold text-black dark:text-white mb-2 flex items-center gap-1.5">
                                <i data-lucide="smartphone" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Live Chat Preview</span>
                            </label>

                            <div
                                class="rounded-[20px] bg-[#EFEAE2] dark:bg-[#0B141A] p-4 border border-black/[0.08] dark:border-white/[0.08] flex-1 flex flex-col justify-between space-y-4">
                                {{-- Phone Topbar Sim --}}
                                <div
                                    class="flex items-center gap-2.5 pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
                                    <div
                                        class="w-8 h-8 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-[12px]">
                                        C
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="text-[12px] font-bold text-black dark:text-white flex items-center gap-1">
                                            <span>Cooca Official</span>
                                            <i data-lucide="check-circle-2" class="w-3 h-3 text-[#34C759]"></i>
                                        </div>
                                        <div class="text-[10px] text-black/50 dark:text-white/50">{{ __('admin.official_business_account') }}</div>
                                    </div>
                                </div>

                                {{-- WhatsApp Bubble Mock --}}
                                <div
                                    class="rounded-[14px] rounded-tl-none bg-white dark:bg-[#1F2C34] p-3.5 space-y-2 shadow-sm border border-black/[0.04] dark:border-white/[0.06] text-[12px]">
                                    {{-- Live Header --}}
                                    <div x-show="newTemplate.header_text"
                                        class="font-bold text-[12px] text-black dark:text-white border-b border-black/[0.06] dark:border-white/[0.06] pb-1"
                                        x-text="newTemplate.header_text"></div>

                                    {{-- Live Body --}}
                                    <div class="text-black/85 dark:text-white/90 leading-relaxed font-sans whitespace-pre-line text-[12px]"
                                        x-html="formatBodyPreview(newTemplate.body_text)"></div>

                                    {{-- Live Footer --}}
                                    <div x-show="newTemplate.footer_text"
                                        class="text-[10px] text-black/45 dark:text-white/45 pt-1 border-t border-black/[0.04] dark:border-white/[0.04]"
                                        x-text="newTemplate.footer_text"></div>
                                </div>

                                <div class="text-[11px] text-black/40 dark:text-white/40 text-center italic">
                                    {{ __('admin.live_preview_bubble_simulation_desc') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div
                        class="px-6 py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0">
                        <button type="button" @click="closeCreateTemplateModal()"
                            class="min-h-[42px] px-5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/10 text-black dark:text-white text-[13px] font-semibold transition-colors">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="button" @click="createMetaTemplate()" :disabled="templateCreating"
                            class="min-h-[42px] px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold inline-flex items-center gap-2 shadow-sm disabled:opacity-50 transition-all">
                            <i data-lucide="send" class="w-4 h-4" :class="{ 'animate-pulse': templateCreating }"></i>
                            <span :text="templateCreating ? @json(__('whatsapp.sending_to_meta_progress')) : @json(__('whatsapp.send_to_meta'))">{{ __('whatsapp.send_to_meta') }}</span>
                        </button>
                    </div>

                        </div>
                    </div>
                </div>
            </template>

            {{-- ----------------------------------------------------------------- --}}
            {{-- SUBSEKSI 2: TEMPLATE PESAN PENGINGAT OTOMATIS                     --}}
            {{-- ----------------------------------------------------------------- --}}
            <div
                class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-4 sm:p-7 space-y-6 w-full min-w-0">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-black/[0.06] dark:border-white/[0.08] min-w-0">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-[17px] font-bold text-black dark:text-white">{{ __('whatsapp.automated_reminder_message_templates') }}</h2>
                        <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ __('admin.automated_reminder_desc') }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[11px] font-semibold text-black/45 dark:text-white/45">{{ __('admin.supported_variables') }}</span>
                        @foreach (['{owner}', '{bisnis}', '{paket}', '{tanggal_habis}', '{link_bayar}'] as $tag)
                            <span
                                class="px-2 py-0.5 rounded-[6px] text-[11px] font-mono bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.whatsapp.reminders.templates') }}" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                        @php
                            $templateConfigs = [
                                'h-7' => [
                                    'name' => 'template_h7',
                                    'label' => __('admin.reminder_h7_label'),
                                    'badge' => 'bg-[#007AFF]/15 text-[#007AFF]',
                                ],
                                'h-3' => [
                                    'name' => 'template_h3',
                                    'label' => __('admin.reminder_h3_label'),
                                    'badge' => 'bg-[#FF9500]/15 text-[#FF9500]',
                                ],
                                'h-1' => [
                                    'name' => 'template_h1',
                                    'label' => __('admin.reminder_h1_label'),
                                    'badge' => 'bg-[#AF52DE]/15 text-[#AF52DE]',
                                ],
                                'hari_h' => [
                                    'name' => 'template_h0',
                                    'label' => __('admin.reminder_hari_h_label'),
                                    'badge' => 'bg-[#FF3B30]/15 text-[#FF3B30]',
                                ],
                            ];
                        @endphp

                        @foreach ($templateConfigs as $type => $field)
                            <div
                                class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-[13px] font-bold text-black dark:text-white">
                                        {{ $field['label'] }}
                                    </label>
                                    <span
                                        class="font-mono text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                        {{ strtoupper($type) }}
                                    </span>
                                </div>
                                <textarea name="{{ $field['name'] }}" required maxlength="2000" rows="8"
                                    class="w-full bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[12px] p-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all font-mono leading-relaxed resize-y">{{ $templates[$type] ?? '' }}</textarea>
                            </div>
                        @endforeach
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="submit"
                            class="min-h-[48px] px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold inline-flex items-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>{{ __('common.save') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 5: MONITORING Merchant WABA (PLATFORM PARENT OVERSIGHT)               --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'merchants'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6 w-full min-w-0">

            {{-- Header Card --}}
            <div
                class="p-5 sm:p-6 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 w-full min-w-0">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-11 h-11 sm:w-12 sm:h-12 rounded-[16px] bg-gradient-to-br from-[#1877F2] to-[#007AFF] text-white flex items-center justify-center shadow-md shadow-[#1877F2]/20 shrink-0">
                        <i data-lucide="store" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('whatsapp.monitoring_merchant_wa_accounts') }}</h2>
                        <p class="text-[12px] sm:text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">
                            {{ __('admin.merchant_wa_oversight_desc') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span
                        class="px-3.5 py-1.5 rounded-[12px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] text-[12px] font-bold border border-[#34C759]/20 tabular-nums">
                        {{ __('admin.active_accounts_count', ['count' => $activeMerchantsCount]) }}
                    </span>
                </div>
            </div>

            {{-- 4 Metric Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                <div
                    class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-1">
                    <span
                        class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider block">{{ __('admin.total_merchants') }}</span>
                    <div class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tabular-nums">
                        {{ $merchantSummary['total'] ?? 0 }}
                    </div>
                </div>
                <div
                    class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-1">
                    <span
                        class="text-[11px] font-semibold text-[#248A3D] dark:text-[#30D158] uppercase tracking-wider block">{{ __('admin.actively_connected') }}</span>
                    <div class="text-[20px] sm:text-[24px] font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums">
                        {{ $merchantSummary['connected'] ?? 0 }}
                    </div>
                </div>
                <div
                    class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-1">
                    <span class="text-[11px] font-semibold text-[#007AFF] uppercase tracking-wider block">{{ __('admin.live_mode_accounts') }}</span>
                    <div class="text-[20px] sm:text-[24px] font-bold text-[#007AFF] tabular-nums">
                        {{ $merchantSummary['live_count'] ?? 0 }}
                    </div>
                </div>
                <div
                    class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-1">
                    <span class="text-[11px] font-semibold text-[#FF9500] uppercase tracking-wider block">{{ __('admin.sandbox_draft') }}</span>
                    <div class="text-[20px] sm:text-[24px] font-bold text-[#FF9500] tabular-nums">
                        {{ $merchantSummary['sandbox_count'] ?? 0 }}
                    </div>
                </div>
            </div>

            {{-- Bento Table: Merchant Accounts --}}
            <div
                class="rounded-[22px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden">
                <div
                    class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                    <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('whatsapp.merchant_wa_accounts_list') }}</h3>
                    <span class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ __('admin.showing_accounts_count', ['count' => count($merchantSummary['accounts'] ?? [])]) }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr
                                class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08] text-black/50 dark:text-white/50 text-[11px] font-bold uppercase tracking-wider">
                                <th class="py-3 px-4 sm:px-6">{{ __('admin.business_name_and_owner') }}</th>
                                <th class="py-3 px-4">{{ __('admin.whatsapp_number') }}</th>
                                <th class="py-3 px-4">WABA ID / Phone ID</th>
                                <th class="py-3 px-4 text-center">{{ __('admin.status') }}</th>
                                <th class="py-3 px-4 text-center">{{ __('admin.mode') }}</th>
                                <th class="py-3 px-4 sm:px-6 text-right">{{ __('admin.connected_since') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.06] dark:divide-white/[0.08]">
                            @forelse ($merchantSummary['accounts'] ?? [] as $acc)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3.5 px-4 sm:px-6">
                                        <div class="font-bold text-black dark:text-white">
                                            {{ $acc->business->name ?? 'Bisnis #' . $acc->business_id }}</div>
                                        <div class="text-[11.5px] text-black/50 dark:text-white/50">
                                            {{ $acc->business->owner->name ?? '-' }}
                                            ({{ $acc->business->owner->email ?? '-' }})
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-mono font-medium text-black dark:text-white">
                                            {{ $acc->display_phone_number ?: ($acc->phone_number ? '+' . $acc->phone_number : '-') }}
                                        </div>
                                        <div class="text-[11px] text-black/40 dark:text-white/40">
                                            {{ $acc->verified_name ?: __('admin.unverified_name') }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-[11.5px] text-black/70 dark:text-white/70">
                                        <div>WABA: {{ $acc->waba_id ?: '-' }}</div>
                                        <div>PID: {{ $acc->phone_number_id ?: '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($acc->status === 'connected')
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                <span>Aktif</span>
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">
                                                <span>{{ ucfirst($acc->status) }}</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($acc->environment === 'live')
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/12 text-[#007AFF]">Live</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">Sandbox</span>
                                        @endif
                                    </td>
                                    <td
                                        class="py-3.5 px-4 sm:px-6 text-right text-black/50 dark:text-white/50 text-[12px] tabular-nums">
                                        {{ $acc->created_at ? $acc->created_at->isoFormat('D MMM Y, HH:mm') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-black/45 dark:text-white/45">
                                        <div
                                            class="w-12 h-12 rounded-[16px] bg-black/5 dark:bg-white/10 text-black/40 dark:text-white/40 flex items-center justify-center mx-auto mb-3">
                                            <i data-lucide="store" class="w-6 h-6"></i>
                                        </div>
                                        <div class="text-[14px] font-bold text-black dark:text-white">{{ __('whatsapp.no_merchants_yet') }}</div>
                                        <p class="text-[12px] text-black/50 dark:text-white/50 max-w-sm mx-auto mt-1">
                                            {{ __('admin.merchant_self_connect_hint') }}
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Toast Notifikasi Salin Kredensial --}}
        <div x-show="copyToast" x-cloak x-transition
            class="fixed bottom-6 right-6 z-50 p-4 rounded-[16px] bg-black/90 dark:bg-white/95 text-white dark:text-black shadow-2xl flex items-center gap-2.5 text-[13px] font-bold">
            <i data-lucide="check-circle" class="w-4 h-4 text-[#34C759]"></i>
            <span x-text="copyToastMessage"></span>
        </div>

    </div>

    @push('scripts')
        <script>
            function adminWaCenter() {
                return {
                    activeTab: @json($initialTab),
                    status: @json($liveStatus ?? 'disconnected'),
                    phone: @json($initialPhone),
                    codeVerificationStatus: @json($waStatus['code_verification_status'] ?? 'NOT_VERIFIED'),
                    metaStatus: @json($waStatus['meta_status'] ?? 'DISCONNECTED'),
                    platformType: @json($waStatus['platform_type'] ?? 'ON_PREMISE'),
                    statusDetail: @json($waStatus['status_detail'] ?? ''),
                    managerUrl: @json($waStatus['manager_url'] ?? 'https://business.facebook.com/wa/manage/phone-numbers/?waba_id=2292844188142290'),
                    isLoading: false,
                    blastMessage: '',

                    testPhone: '',
                    testMessage: @json(__('admin.wa_test_default_message')),
                    testLoading: false,
                    testResult: '',
                    testOk: false,

                    diagnosticMode: 'message',
                    otpPhone: '',
                    otpCode: '',
                    otpLoading: false,
                    otpResult: '',
                    otpOk: false,

                    generateRandomOtp() {
                        this.otpCode = Math.floor(100000 + Math.random() * 900000).toString();
                    },

                    async sendOtpLive() {
                        if (!this.otpPhone.trim()) {
                            this.otpResult = @json(__('admin.wa_otp_phone_required'));
                            this.otpOk = false;
                            return;
                        }
                        if (!this.otpCode.trim()) {
                            this.generateRandomOtp();
                        }
                        this.otpLoading = true;
                        this.otpResult = '';
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.send-otp') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                },
                                body: JSON.stringify({
                                    phone: this.otpPhone.trim(),
                                    otp_code: this.otpCode.trim()
                                })
                            });
                            const data = await response.json();
                            this.otpOk = response.ok && data.success === true;
                            this.otpResult = data.message || (this.otpOk ? @json(__('admin.wa_otp_sent_success')) : (data.error ||
                                @json(__('admin.wa_otp_send_failed'))));
                        } catch (error) {
                            this.otpOk = false;
                            this.otpResult = @json(__('whatsapp.network_error_sending_otp'));
                        } finally {
                            this.otpLoading = false;
                            this.refreshIcons();
                        }
                    },

                    hasMetaToken: {{ !empty($metaCreds['token']) ? 'true' : 'false' }},
                    metaToken: '',
                    metaPhoneId: @json($metaCreds['phone_number_id'] ?? ''),
                    metaWabaId: @json($metaCreds['waba_id'] ?? ''),
                    metaAppId: @json($platformApp['app_id'] ?? ''),
                    hasMetaAppSecret: {{ !empty($platformApp['app_secret']) ? 'true' : 'false' }},
                    metaAppSecret: '',
                    metaConfigId: @json($platformApp['config_id'] ?? ''),
                    metaWebhookVerifyToken: '',
                    metaGraphVersion: @json($platformApp['graph_version'] ?? 'v26.0'),
                    metaGraphUrl: @json($platformApp['graph_url'] ?? 'https://graph.facebook.com'),
                    showMetaToken: false,
                    showMetaAppSecret: false,
                    metaVerifyLoading: false,
                    metaVerifyResult: null,
                    metaVerifyError: null,

                    copyToast: false,
                    copyToastMessage: '',

                    refreshIcons() {
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                                lucide.createIcons();
                            }
                        });
                    },

                    copyToClipboard(text, label) {
                        if (!text) return;
                        navigator.clipboard.writeText(text).then(() => {
                            this.copyToastMessage = label + ' ' + @json(__('admin.copied_to_clipboard_suffix'));
                            this.copyToast = true;
                            setTimeout(() => {
                                this.copyToast = false;
                            }, 3000);
                        }).catch(() => {
                            prompt('Salin manual:', text);
                        });
                    },

                    async verifyMetaCredentials() {
                        if (!this.hasMetaToken && !this.metaToken.trim()) {
                            this.metaVerifyError = @json(__('admin.wa_fill_meta_token_first'));
                            this.metaVerifyResult = null;
                            return;
                        }
                        if (!this.metaPhoneId.trim()) {
                            this.metaVerifyError = @json(__('admin.wa_fill_phone_id_first'));
                            this.metaVerifyResult = null;
                            return;
                        }
                        this.metaVerifyLoading = true;
                        this.metaVerifyResult = null;
                        this.metaVerifyError = null;
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.verify-meta') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                },
                                body: JSON.stringify({
                                    token: this.metaToken.trim() || undefined,
                                    phone_number_id: this.metaPhoneId.trim()
                                })
                            });
                            const data = await response.json();
                            if (response.ok && data.success) {
                                this.metaVerifyResult = data.data;
                                this.status = 'connected';
                                if (data.data.display_phone_number) {
                                    this.phone = data.data.display_phone_number;
                                }
                            } else {
                                this.metaVerifyError = data.error || @json(__('admin.wa_verify_meta_failed'));
                            }
                        } catch (e) {
                            this.metaVerifyError = @json(__('admin.wa_network_error_verifying'));
                        } finally {
                            this.metaVerifyLoading = false;
                            this.refreshIcons();
                        }
                    },

                    init() {
                        this.checkStatus();
                        this.refreshIcons();
                    },

                    setTab(tab) {
                        this.activeTab = tab;
                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', tab);
                        window.history.replaceState({}, '', url);
                        if (tab === 'parent_setup') {
                            this.checkStatus();
                        }
                        this.refreshIcons();
                    },

                    insertVariableToBlast(variable) {
                        const textarea = document.getElementById('blastMessageTextarea');
                        if (!textarea) return;
                        const start = textarea.selectionStart;
                        const end = textarea.selectionEnd;
                        const text = textarea.value;
                        textarea.value = text.substring(0, start) + variable + text.substring(end);
                        this.blastMessage = textarea.value;
                        textarea.focus();
                        textarea.selectionStart = textarea.selectionEnd = start + variable.length;
                    },

                    async checkStatus() {
                        this.isLoading = true;
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.status') }}', {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            const data = await response.json();
                            this.applyStatus(data);
                        } catch (error) {
                            this.status = 'disconnected';
                        } finally {
                            this.isLoading = false;
                            this.refreshIcons();
                        }
                    },

                    applyStatus(data) {
                        const rawStatus = String(data.status || '').toLowerCase();
                        this.status = rawStatus === 'connected' ? 'connected' : 'disconnected';
                        this.phone = data.phone || this.phone;
                        this.codeVerificationStatus = data.code_verification_status || this.codeVerificationStatus;
                        this.metaStatus = data.meta_status || this.metaStatus;
                        this.platformType = data.platform_type || this.platformType;
                        this.statusDetail = data.status_detail || this.statusDetail;
                        this.managerUrl = data.manager_url || this.managerUrl;
                        this.refreshIcons();
                    },

                    async sendTest() {
                        if (!this.testPhone.trim() || !this.testMessage.trim()) {
                            this.testResult = @json(__('admin.wa_test_phone_and_message_required'));
                            this.testOk = false;
                            return;
                        }
                        this.testLoading = true;
                        this.testResult = '';
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.test') }}', {
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
                            const data = await response.json();
                            this.testOk = response.ok && data.success === true;
                            this.testResult = this.testOk ? @json(__('admin.wa_test_message_sent_success')) : (
                                @json(__('admin.failed')) + ': ' + (data.error || @json(__('admin.wa_meta_gateway_no_response'))));
                        } catch (error) {
                            this.testOk = false;
                            this.testResult = @json(__('whatsapp.network_error_sending_message'));
                        } finally {
                            this.testLoading = false;
                            this.refreshIcons();
                        }
                    },

                    async sendSingleReminder(subscriptionId, type) {
                        if (!confirm(@json(__('admin.confirm_send_single_reminder')) + ' (' + type + ')?')) return;
                        try {
                            const response = await fetch(`/admin/whatsapp/reminders/${subscriptionId}/send`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                },
                                body: JSON.stringify({
                                    type: type
                                })
                            });
                            const data = await response.json();
                            alert(data.message || @json(__('admin.process_completed')));
                            window.location.reload();
                        } catch (error) {
                            alert(@json(__('admin.send_reminder_network_error')));
                        }
                    },

                    metaTemplates: @json($metaTemplates ?? []),
                    selectedLangFilter: 'all',
                    purgingNonIdEn: false,
                    get filteredTemplates() {
                        const list = (this.metaTemplates || []).filter(t => {
                            const lang = (t.language || '').toLowerCase();
                            return lang.startsWith('id') || lang.startsWith('en');
                        });
                        if (this.selectedLangFilter === 'id') {
                            return list.filter(t => (t.language || '').toLowerCase().startsWith('id'));
                        }
                        if (this.selectedLangFilter === 'en') {
                            return list.filter(t => (t.language || '').toLowerCase().startsWith('en'));
                        }
                        return list;
                    },
                    standardTemplatesCatalog: @json($standardTemplatesCatalog ?? []),
                    standardsDeploying: false,
                    standardsSeeding: false,
                    templateSyncing: false,
                    templateCreating: false,
                    showCreateTemplateModal: false,
                    newTemplate: {
                        name: '',
                        category: 'UTILITY',
                        language: 'id',
                        header_text: '',
                        body_text: '',
                        footer_text: ''
                    },

                    getTemplateBody(tpl) {
                        if (!tpl) return '';
                        let comps = tpl.components;
                        if (typeof comps === 'string') {
                            try {
                                comps = JSON.parse(comps);
                            } catch (e) {
                                comps = [];
                            }
                        }
                        if (!Array.isArray(comps)) comps = [];
                        const b = comps.find(c => String(c.type || '').toUpperCase() === 'BODY');
                        return b ? (b.text || '') : (tpl.body_text || '');
                    },

                    getTemplateHeader(tpl) {
                        if (!tpl) return null;
                        let comps = tpl.components;
                        if (typeof comps === 'string') {
                            try {
                                comps = JSON.parse(comps);
                            } catch (e) {
                                comps = [];
                            }
                        }
                        if (!Array.isArray(comps)) comps = [];
                        const h = comps.find(c => String(c.type || '').toUpperCase() === 'HEADER');
                        return h ? (h.text || null) : (tpl.header_text || null);
                    },

                    getTemplateFooter(tpl) {
                        if (!tpl) return null;
                        let comps = tpl.components;
                        if (typeof comps === 'string') {
                            try {
                                comps = JSON.parse(comps);
                            } catch (e) {
                                comps = [];
                            }
                        }
                        if (!Array.isArray(comps)) comps = [];
                        const f = comps.find(c => String(c.type || '').toUpperCase() === 'FOOTER');
                        return f ? (f.text || null) : (tpl.footer_text || null);
                    },

                    getTemplateButtons(tpl) {
                        if (!tpl) return [];
                        let comps = tpl.components;
                        if (typeof comps === 'string') {
                            try {
                                comps = JSON.parse(comps);
                            } catch (e) {
                                comps = [];
                            }
                        }
                        if (!Array.isArray(comps)) comps = [];
                        const btnComp = comps.find(c => String(c.type || '').toUpperCase() === 'BUTTONS');
                        return btnComp && Array.isArray(btnComp.buttons) ? btnComp.buttons : [];
                    },

                    formatBodyPreview(text) {
                        if (!text)
                            return '<span class="text-black/35 dark:text-white/35 italic">' + @json(__('admin.type_text_preview')) + '</span>';
                        const escaped = String(text)
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&#039;');
                        return escaped.replace(/(\{\{\d+\}\})/g,
                            '<span class="px-1.5 py-0.5 rounded-[4px] bg-[#007AFF]/15 text-[#007AFF] font-mono text-[11px] font-semibold">$1</span>'
                        );
                    },

                    insertVariableToNewTemplate(v) {
                        this.newTemplate.body_text = (this.newTemplate.body_text || '') + (this.newTemplate.body_text ? ' ' :
                            '') + v;
                    },

                    openCreateTemplateModal() {
                        this.newTemplate = {
                            name: '',
                            category: 'UTILITY',
                            language: 'id',
                            header_text: '',
                            body_text: '',
                            footer_text: ''
                        };
                        this.showCreateTemplateModal = true;
                        this.refreshIcons();
                    },

                    closeCreateTemplateModal() {
                        this.showCreateTemplateModal = false;
                    },

                    async deployStandardTemplates() {
                        if (!confirm(@json(__('admin.confirm_deploy_standard_templates')))) {
                            return;
                        }
                        this.standardsDeploying = true;
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.meta-templates.deploy-standards') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                }
                            });
                            const data = await response.json();
                            if (response.ok && data.success) {
                                if (data.templates) {
                                    this.metaTemplates = data.templates;
                                }
                                if (data.standards) {
                                    this.standardTemplatesCatalog = data.standards;
                                }
                                this.copyToastMessage = data.message || @json(__('admin.standard_templates_deployed_success'));
                                this.copyToast = true;
                                setTimeout(() => {
                                    this.copyToast = false;
                                }, 4000);
                            } else {
                                alert(data.message || @json(__('admin.standard_templates_deploy_failed')));
                            }
                        } catch (err) {
                            alert(@json(__('whatsapp.network_error_deploying_standards')));
                        } finally {
                            this.standardsDeploying = false;
                            this.refreshIcons();
                        }
                    },

                    async seedStandardTemplates() {
                        this.standardsSeeding = true;
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.meta-templates.seed-standards') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                }
                            });
                            const data = await response.json();
                            if (response.ok && data.success) {
                                if (data.templates) {
                                    this.metaTemplates = data.templates;
                                }
                                if (data.standards) {
                                    this.standardTemplatesCatalog = data.standards;
                                }
                                this.copyToastMessage = data.message || @json(__('admin.standard_templates_seeded_success'));
                                this.copyToast = true;
                                setTimeout(() => {
                                    this.copyToast = false;
                                }, 3500);
                            } else {
                                alert(data.message || @json(__('admin.standard_templates_seed_failed')));
                            }
                        } catch (err) {
                            alert(@json(__('whatsapp.network_error_registering_template')));
                        } finally {
                            this.standardsSeeding = false;
                            this.refreshIcons();
                        }
                    },

                    async syncMetaTemplates() {
                        this.templateSyncing = true;
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.meta-templates.sync') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                }
                            });
                            const data = await response.json();
                            if (response.ok && data.success) {
                                if (data.templates) {
                                    this.metaTemplates = data.templates;
                                }
                                this.copyToastMessage = data.message || @json(__('admin.templates_synced_success'));
                                this.copyToast = true;
                                setTimeout(() => {
                                    this.copyToast = false;
                                }, 3500);
                            } else {
                                alert(data.message || @json(__('admin.templates_sync_failed')));
                            }
                        } catch (err) {
                            alert(@json(__('whatsapp.network_error_syncing_templates')));
                        } finally {
                            this.templateSyncing = false;
                            this.refreshIcons();
                        }
                    },

                    async createMetaTemplate() {
                        if (!this.newTemplate.name.trim() || !this.newTemplate.body_text.trim()) {
                            alert(@json(__('admin.template_name_and_body_required')));
                            return;
                        }
                        this.newTemplate.name = this.newTemplate.name.trim().toLowerCase().replace(/[^a-z0-9_]/g, '_');
                        this.templateCreating = true;
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.meta-templates.create') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                },
                                body: JSON.stringify(this.newTemplate)
                            });
                            const data = await response.json();
                            if (response.ok && data.success) {
                                if (data.templates) {
                                    this.metaTemplates = data.templates;
                                }
                                this.showCreateTemplateModal = false;
                                this.copyToastMessage = data.message || @json(__('admin.template_created_success'));
                                this.copyToast = true;
                                setTimeout(() => {
                                    this.copyToast = false;
                                }, 3500);
                            } else {
                                const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') :
                                    @json(__('admin.template_create_failed')));
                                alert(errMsg);
                            }
                        } catch (err) {
                            alert(@json(__('whatsapp.network_error_submitting_template')));
                        } finally {
                            this.templateCreating = false;
                            this.refreshIcons();
                        }
                    },

                    async purgeNonIdEnTemplates() {
                        if (!confirm({{ Js::from(__('whatsapp.confirm_purge_non_id_en')) }})) {
                            return;
                        }
                        this.purgingNonIdEn = true;
                        try {
                            const response = await fetch('{{ route('admin.whatsapp.meta-templates.purge-non-id-en') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                }
                            });
                            const data = await response.json();
                            if (response.ok && data.success) {
                                if (data.templates) {
                                    this.metaTemplates = data.templates;
                                }
                                this.copyToastMessage = data.message || @json(__('admin.non_id_en_purged_success'));
                                this.copyToast = true;
                                setTimeout(() => {
                                    this.copyToast = false;
                                }, 3500);
                            } else {
                                alert(data.message || @json(__('admin.non_id_en_purge_failed')));
                            }
                        } catch (err) {
                            alert(@json(__('whatsapp.network_error_purging_templates')));
                        } finally {
                            this.purgingNonIdEn = false;
                            this.refreshIcons();
                        }
                    },

                    async deleteMetaTemplate(tpl) {
                        if (!confirm(@json(__('admin.confirm_delete_meta_template_prefix')) + ' \'' + tpl.name + '\' ' + @json(__('admin.confirm_delete_meta_template_suffix')))) {
                            return;
                        }
                        try {
                            const response = await fetch(`/admin/whatsapp/meta-templates/${tpl.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                                }
                            });
                            const data = await response.json();
                            if (response.ok && data.success) {
                                if (data.templates) {
                                    this.metaTemplates = data.templates;
                                } else {
                                    this.metaTemplates = this.metaTemplates.filter(t => t.id !== tpl.id);
                                }
                                this.copyToastMessage = data.message || @json(__('admin.template_deleted_success'));
                                this.copyToast = true;
                                setTimeout(() => {
                                    this.copyToast = false;
                                }, 3000);
                            } else {
                                alert(data.message || @json(__('admin.template_delete_failed')));
                            }
                        } catch (err) {
                            alert(@json(__('whatsapp.network_error_deleting_template')));
                        } finally {
                            this.refreshIcons();
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection
