@extends('layouts.app', ['title' => __('ai.lobby.page_title')])

@section('content')
<div class="space-y-6 w-full pb-28 sm:pb-32 lg:pb-12" x-data="aiOfficeBase()" x-init="runTeamworkWorkflow()">

    {{-- ========================================================== --}}
    {{-- 1. BENTO APPLE HIG HEADER WITH BREADCRUMB & ACTION COCKPIT --}}
    {{-- ========================================================== --}}
    <x-module-header
        title="Markas Tim AI Eksekutif (Digital HQ)"
        subtitle="Kolaborasi otonom agen cerdas (CEO, CFO, COO, CMO) untuk analisa data, penetapan target, dan rekomendasi pertumbuhan bisnis Anda"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Tim AI Bisnis', 'url' => route('cooca-ai.index')],
            ['label' => 'Markas Eksekutif', 'url' => null]
        ]">

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Workflow Quick Triggers --}}
            <button type="button" @click="activeWorkflowTab = 'teamwork'; runTeamworkWorkflow();"
                class="min-h-[40px] sm:min-h-0 h-10 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Rapat Tim (/teamwork)</span>
            </button>

            <button type="button" @click="activeWorkflowTab = 'goal'; runGoalWorkflow();"
                class="min-h-[40px] sm:min-h-0 h-10 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#2DBE50] active:scale-[0.97] transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)] cursor-pointer">
                <i data-lucide="target" class="w-4 h-4"></i>
                <span>Target Bisnis (/goal)</span>
            </button>

            <button type="button" @click="activeWorkflowTab = 'grill_me'; runGrillMeWorkflow();"
                class="min-h-[40px] sm:min-h-0 h-10 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF9500] hover:bg-[#E08500] active:scale-[0.97] transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(255,149,0,0.25)] cursor-pointer">
                <i data-lucide="flame" class="w-4 h-4"></i>
                <span>Diagnosa Usaha (/grill-me)</span>
            </button>

            <button type="button" @click="activeWorkflowTab = 'boost';"
                class="min-h-[40px] sm:min-h-0 h-10 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#AF52DE] hover:bg-[#9B42C6] active:scale-[0.97] transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(175,82,222,0.25)] cursor-pointer">
                <i data-lucide="zap" class="w-4 h-4"></i>
                <span>Analisa Kritis (/boost)</span>
            </button>

            <button type="button" @click="activeWorkflowTab = 'schedule'; runScheduleWorkflow();"
                class="min-h-[40px] sm:min-h-0 h-10 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="clock" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Rutinitas (/schedule)</span>
            </button>
        </div>
    </x-module-header>

    {{-- ========================================================== --}}
    {{-- 2. BENTO REAL-TIME BUSINESS KPI METRICS STRIP             --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Omzet Bulan Ini -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/90 dark:bg-[#1C1C1E]/90 border border-black/5 dark:border-white/10 p-4 transition-all shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Omzet Bulan Ini</span>
                <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white tabular-nums">
                Rp {{ number_format($cockpitMetrics['revenue_month'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="mt-1 flex items-center gap-1.5 text-[11px] text-[#34C759] font-medium">
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                <span>{{ ($cockpitMetrics['today_sales'] ?? 0) > 0 ? '+ Rp ' . number_format($cockpitMetrics['today_sales'], 0, ',', '.') . ' hari ini' : 'Bulan Berjalan' }}</span>
            </div>
        </div>

        <!-- Metric 2: Marjin Laba Kotor -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/90 dark:bg-[#1C1C1E]/90 border border-black/5 dark:border-white/10 p-4 transition-all shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Marjin Laba Kotor</span>
                <span class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="pie-chart" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white tabular-nums">
                {{ round($cockpitMetrics['gross_margin_percent'] ?? 0, 1) }}%
            </div>
            <div class="mt-1 flex items-center gap-1.5 text-[11px] {{ ($cockpitMetrics['gross_margin_percent'] ?? 0) >= 30 ? 'text-[#34C759]' : 'text-[#FF9500]' }} font-medium">
                <span class="w-1.5 h-1.5 rounded-full {{ ($cockpitMetrics['gross_margin_percent'] ?? 0) >= 30 ? 'bg-[#34C759]' : 'bg-[#FF9500]' }}"></span>
                <span>{{ ($cockpitMetrics['gross_margin_percent'] ?? 0) >= 30 ? 'Margin Sehat (Target >= 30%)' : 'Perlu Evaluasi Harga & HPP' }}</span>
            </div>
        </div>

        <!-- Metric 3: Stok Kritis Gudang -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/90 dark:bg-[#1C1C1E]/90 border border-black/5 dark:border-white/10 p-4 transition-all shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Peringatan Stok Kritis</span>
                <span class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center">
                    <i data-lucide="package-alert" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white tabular-nums">
                {{ $cockpitMetrics['critical_stock_count'] ?? 0 }} <span class="text-xs font-normal text-black/50 dark:text-white/50">SKU</span>
            </div>
            <div class="mt-1 flex items-center gap-1.5 text-[11px] {{ ($cockpitMetrics['critical_stock_count'] ?? 0) > 0 ? 'text-[#FF3B30]' : 'text-[#34C759]' }} font-medium">
                <i data-lucide="{{ ($cockpitMetrics['critical_stock_count'] ?? 0) > 0 ? 'alert-triangle' : 'check-circle' }}" class="w-3.5 h-3.5"></i>
                <span>{{ ($cockpitMetrics['critical_stock_count'] ?? 0) > 0 ? 'Mendesak: Perlu Purchase Order' : 'Semua Stok di Atas Batas Aman' }}</span>
            </div>
        </div>

        <!-- Metric 4: Piutang Usaha (AR) -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/90 dark:bg-[#1C1C1E]/90 border border-black/5 dark:border-white/10 p-4 transition-all shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Piutang Belum Tertagih</span>
                <span class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white tabular-nums">
                Rp {{ number_format($cockpitMetrics['unpaid_receivables'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="mt-1 flex items-center gap-1.5 text-[11px] text-black/60 dark:text-white/60 font-medium">
                <i data-lucide="shield" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Kawal Penagihan Pelanggan</span>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- 3. INTERACTIVE AUTONOMOUS TEAMWORK & STRATEGY COCKPIT      --}}
    {{-- ========================================================== --}}
    <div class="rounded-[20px] backdrop-blur-md bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-6">

        {{-- Workflow Selector Tabs (Apple Segmented Style) --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-black/5 dark:border-white/10">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#34C759] animate-pulse"></span>
                    <h2 class="text-base font-bold text-black dark:text-white tracking-tight">Pusat Kolaborasi Tim AI Otonom</h2>
                </div>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Pilih alur kerja strategis untuk menggerakkan tim eksekutif AI</p>
            </div>

            <!-- Segmented Control Bar -->
            <div class="flex items-center gap-1 p-1 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 overflow-x-auto">
                <button type="button" @click="activeWorkflowTab = 'teamwork'"
                    :class="activeWorkflowTab === 'teamwork' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="px-3 py-1.5 rounded-[9px] text-xs transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>/teamwork</span>
                </button>

                <button type="button" @click="activeWorkflowTab = 'goal'"
                    :class="activeWorkflowTab === 'goal' ? 'bg-white dark:bg-[#2C2C2E] text-[#34C759] shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="px-3 py-1.5 rounded-[9px] text-xs transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <i data-lucide="target" class="w-3.5 h-3.5"></i>
                    <span>/goal</span>
                </button>

                <button type="button" @click="activeWorkflowTab = 'grill_me'"
                    :class="activeWorkflowTab === 'grill_me' ? 'bg-white dark:bg-[#2C2C2E] text-[#FF9500] shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="px-3 py-1.5 rounded-[9px] text-xs transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <i data-lucide="flame" class="w-3.5 h-3.5"></i>
                    <span>/grill-me</span>
                </button>

                <button type="button" @click="activeWorkflowTab = 'boost'"
                    :class="activeWorkflowTab === 'boost' ? 'bg-white dark:bg-[#2C2C2E] text-[#AF52DE] shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="px-3 py-1.5 rounded-[9px] text-xs transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                    <span>/boost</span>
                </button>

                <button type="button" @click="activeWorkflowTab = 'schedule'"
                    :class="activeWorkflowTab === 'schedule' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                    class="px-3 py-1.5 rounded-[9px] text-xs transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    <span>/schedule</span>
                </button>
            </div>
        </div>

        {{-- Loading Spinner State --}}
        <div x-show="workflowLoading" x-transition.opacity class="py-12 text-center space-y-3">
            <div class="w-10 h-10 border-3 border-[#007AFF]/20 border-t-[#007AFF] rounded-full animate-spin mx-auto"></div>
            <div class="text-sm font-semibold text-black dark:text-white">Tim Eksekutif AI sedang berkoordinasi & menganalisa data bisnis...</div>
            <p class="text-xs text-black/50 dark:text-white/50">Mengkonsolidasikan data penjualan POS, stok gudang, dan pembukuan keuangan kas</p>
        </div>

        {{-- Error Alert Banner --}}
        <div x-show="workflowError && !workflowLoading" x-transition.opacity class="p-4 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 flex items-center gap-3 text-xs text-[#FF3B30]">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
            <span x-text="workflowError"></span>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 1: WORKFLOW /teamwork (Rapat Koordinasi Tim AI)       --}}
        {{-- ======================================================== --}}
        <div x-show="activeWorkflowTab === 'teamwork' && !workflowLoading" class="space-y-5">
            <!-- Topic Input Bar -->
            <form @submit.prevent="runTeamworkWorkflow()" class="flex flex-col sm:flex-row items-center gap-2.5">
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                        <i data-lucide="sparkles" class="w-4 h-4 text-[#007AFF]"></i>
                    </div>
                    <input type="text" x-model="teamworkTopic"
                        placeholder="Topik rapat tim (Contoh: Cara tingkatkan omzet kasir 20% & bersihkan stok lambat)"
                        class="w-full pl-10 pr-4 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-none focus:border-[#007AFF] focus:ring-1 focus:ring-[#007AFF]">
                </div>
                <button type="submit"
                    class="w-full sm:w-auto h-10 sm:h-auto px-5 py-2.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold shadow-sm transition-all flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                    <i data-lucide="play" class="w-3.5 h-3.5 fill-white"></i>
                    <span>Jalankan Rapat Tim</span>
                </button>
            </form>

            <!-- Live Results: Consensus & Perspectives -->
            <template x-if="workflowResults.teamwork">
                <div class="space-y-5 pt-2">
                    <!-- Executive Consensus Box -->
                    <div class="p-4 sm:p-5 rounded-[16px] bg-[#007AFF]/[0.06] dark:bg-[#007AFF]/15 border border-[#007AFF]/20 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                            <h3 class="text-xs sm:text-sm font-bold text-[#007AFF] uppercase tracking-wider">Hasil Konsensus Rapat Tim Eksekutif</h3>
                        </div>
                        <p class="text-xs sm:text-[13px] text-black/80 dark:text-white/90 leading-relaxed font-medium" x-text="workflowResults.teamwork.consensus"></p>
                    </div>

                    <!-- 4 Perspectives Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="(agent, key) in workflowResults.teamwork.discussions" :key="key">
                            <div class="rounded-[16px] p-4 bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-[10px] flex items-center justify-center text-xs font-bold border" :class="agent.avatar_color">
                                            <i data-lucide="bot" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-black dark:text-white" x-text="agent.role"></div>
                                            <div class="text-[10px] text-black/50 dark:text-white/50" x-text="agent.name"></div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-black/70 dark:text-white/70 leading-relaxed" x-text="agent.perspective"></p>
                                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-start gap-1.5 text-[11px] font-semibold text-[#007AFF]">
                                    <i data-lucide="arrow-right-circle" class="w-3.5 h-3.5 shrink-0 mt-0.5"></i>
                                    <span x-text="agent.directive"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Action Proposals (Maker-Checker Direct Execution) -->
                    <template x-if="workflowResults.teamwork.action_proposals && workflowResults.teamwork.action_proposals.length > 0">
                        <div class="space-y-3 pt-2">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">Proposal Tindakan Langsung (Maker-Checker)</h4>
                                <span class="text-[11px] text-[#FF9500] font-semibold">Menunggu Persetujuan Anda</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="proposal in workflowResults.teamwork.action_proposals" :key="proposal.id">
                                    <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 shadow-xs flex items-center justify-between gap-3">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded-[6px] text-[9px] font-bold uppercase tracking-wider bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70" x-text="proposal.agent"></span>
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase bg-[#FF9500]/15 text-[#FF9500]" x-text="proposal.risk"></span>
                                            </div>
                                            <div class="text-xs font-bold text-black dark:text-white" x-text="proposal.title"></div>
                                            <div class="text-[11px] text-black/50 dark:text-white/50 font-mono" x-text="'Biaya: ' + proposal.estimated_cost"></div>
                                        </div>
                                        <button type="button" @click="quickApproveProposal(proposal.id)"
                                            class="px-3 py-1.5 rounded-[8px] bg-[#34C759] hover:bg-[#2DBE50] text-white text-xs font-semibold shadow-xs transition-all shrink-0 cursor-pointer">
                                            Setujui
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 2: WORKFLOW /goal (Target & Milestone Decomposer)     --}}
        {{-- ======================================================== --}}
        <div x-show="activeWorkflowTab === 'goal' && !workflowLoading" class="space-y-5">
            <!-- Goal Configuration -->
            <form @submit.prevent="runGoalWorkflow()" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-black/60 dark:text-white/60 uppercase mb-1">Jenis Target</label>
                    <select x-model="goalType" class="w-full px-3 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white focus:outline-none focus:border-[#34C759]">
                        <option value="revenue">Omzet Penjualan Bulanan (Rp)</option>
                        <option value="margin">Marjin Laba Kotor (%)</option>
                        <option value="debt_recovery">Penagihan Piutang Macet (Rp)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-black/60 dark:text-white/60 uppercase mb-1">Target Nominal / Persen</label>
                    <input type="number" x-model="goalTargetValue" step="100000"
                        class="w-full px-3 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white font-mono focus:outline-none focus:border-[#34C759]">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full h-10 px-4 rounded-[12px] bg-[#34C759] hover:bg-[#2DBE50] text-white text-xs font-semibold shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                        <span>Hitung Rencana Kerja</span>
                    </button>
                </div>
            </form>

            <!-- Results: Milestone Breakdown & Progress -->
            <template x-if="workflowResults.goal">
                <div class="space-y-5 pt-2">
                    <!-- Progress Card -->
                    <div class="p-5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-bold text-black dark:text-white" x-text="workflowResults.goal.title"></h3>
                                <div class="text-xs text-black/50 dark:text-white/50" x-text="'Sisa waktu periode: ' + workflowResults.goal.remaining_days + ' hari'"></div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-black/50 dark:text-white/50">Capaian Saat Ini</div>
                                <div class="text-base font-bold text-[#34C759] font-mono" x-text="workflowResults.goal.formatted.current + ' / ' + workflowResults.goal.formatted.target"></div>
                            </div>
                        </div>

                        <!-- Progress Bar Apple HIG -->
                        <div class="space-y-1">
                            <div class="h-3 w-full bg-black/5 dark:bg-white/10 rounded-full overflow-hidden p-0.5">
                                <div class="h-full bg-[#34C759] rounded-full transition-all duration-500" :style="'width: ' + workflowResults.goal.progress_percent + '%'"></div>
                            </div>
                            <div class="flex justify-between text-[11px] text-black/50 dark:text-white/50 font-mono">
                                <span x-text="'Progress: ' + workflowResults.goal.progress_percent + '%'"></span>
                                <span x-text="'Kebutuhan Run-rate: ' + workflowResults.goal.formatted.daily_rate"></span>
                            </div>
                        </div>
                    </div>

                    <!-- 4 Weekly Milestones -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <template x-for="m in workflowResults.goal.milestones" :key="m.week">
                            <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-2 shadow-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-[#34C759] uppercase" x-text="m.week"></span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70" x-text="m.target_pct + '% Target'"></span>
                                </div>
                                <div class="text-xs font-bold text-black dark:text-white" x-text="m.phase"></div>
                                <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed" x-text="m.directive"></p>
                                <div class="text-[10px] font-semibold text-black/40 dark:text-white/40 pt-1 border-t border-black/5 dark:border-white/5" x-text="'Lead: ' + m.lead_agent"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 3: WORKFLOW /grill-me (Wawancara Diagnosa Kritis)     --}}
        {{-- ======================================================== --}}
        <div x-show="activeWorkflowTab === 'grill_me' && !workflowLoading" class="space-y-5">
            <div class="p-4 rounded-[14px] bg-[#FF9500]/10 border border-[#FF9500]/20 flex items-start gap-3">
                <i data-lucide="flame" class="w-5 h-5 text-[#FF9500] shrink-0 mt-0.5"></i>
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-black dark:text-white">Cockpit Wawancara Diagnostik Kritis</h3>
                    <p class="text-[11px] sm:text-xs text-black/60 dark:text-white/60 mt-0.5">
                        AI bertindak sebagai konsultan bisnis senior yang tajam dan tidak basa-basi. Jawab 4 pertanyaan berdasarkan kondisi riil usaha Anda untuk merumuskan resep perbaikan.
                    </p>
                </div>
            </div>

            <!-- Questions Form -->
            <template x-if="workflowResults.grill_me">
                <div class="space-y-4">
                    <template x-for="(q, idx) in workflowResults.grill_me.questions" :key="q.id">
                        <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-[#FF9500]/20 text-[#FF9500] text-xs font-bold flex items-center justify-center font-mono" x-text="idx + 1"></span>
                                <span class="text-xs font-bold text-black dark:text-white" x-text="q.pillar"></span>
                                <template x-if="q.severity === 'HIGH'">
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] uppercase">Titik Bocor</span>
                                </template>
                            </div>
                            <p class="text-xs sm:text-[13px] text-black/80 dark:text-white/90 font-medium leading-relaxed" x-text="q.question"></p>
                            
                            <!-- Options Radio -->
                            <div class="space-y-1.5 pt-1">
                                <template x-for="(opt, optIdx) in q.options" :key="optIdx">
                                    <label class="flex items-center gap-2.5 p-2.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 hover:border-[#FF9500]/40 transition-colors cursor-pointer text-xs text-black/80 dark:text-white/80">
                                        <input type="radio" :name="q.id" :value="opt" x-model="grillMeAnswers[q.id]" class="text-[#FF9500] focus:ring-[#FF9500]">
                                        <span x-text="opt"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="text-right pt-2">
                        <button type="button" @click="runGrillMeWorkflow()"
                            class="px-5 py-2.5 rounded-[12px] bg-[#FF9500] hover:bg-[#E08500] text-white text-xs font-semibold shadow-sm transition-all cursor-pointer">
                            Kirim Jawaban & Dapatkan Resep Perbaikan
                        </button>
                    </div>

                    <!-- Prescription Box when answers are synthesized -->
                    <template x-if="workflowResults.grill_me.prescription">
                        <div class="p-5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/25 space-y-3 mt-4">
                            <div class="flex items-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4 text-[#34C759]"></i>
                                <h4 class="text-xs font-bold text-[#34C759] uppercase tracking-wider">Resep Perbaikan Usaha (Turnaround Prescription)</h4>
                            </div>
                            <p class="text-xs text-black/80 dark:text-white/90 font-medium leading-relaxed" x-text="workflowResults.grill_me.prescription.diagnosis_summary"></p>
                            <div class="space-y-1.5 pt-1">
                                <div class="text-[11px] font-bold text-black/50 dark:text-white/50 uppercase">Tindakan Prioritas:</div>
                                <template x-for="act in workflowResults.grill_me.prescription.immediate_actions" :key="act">
                                    <div class="flex items-start gap-2 text-xs text-black/80 dark:text-white/80">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0 mt-0.5"></i>
                                        <span x-text="act"></span>
                                    </div>
                                </template>
                            </div>
                            <div class="text-xs font-bold text-[#34C759] pt-2" x-text="workflowResults.grill_me.prescription.projected_recovery"></div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 4: WORKFLOW /boost (Stress-Test Keputusan Kritis)     --}}
        {{-- ======================================================== --}}
        <div x-show="activeWorkflowTab === 'boost' && !workflowLoading" class="space-y-5">
            <form @submit.prevent="runBoostWorkflow()" class="space-y-3">
                <label class="block text-xs font-bold text-black/70 dark:text-white/70">
                    Masukkan Keputusan Strategis atau Masalah Bisnis yang Ingin Diuji:
                </label>
                <div class="flex flex-col sm:flex-row gap-2.5">
                    <input type="text" x-model="boostQuery"
                        placeholder="Contoh: Apakah aman menaikkan harga menu makanan 15% untuk menutup kenaikan bahan baku?"
                        class="flex-1 px-4 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs sm:text-sm text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-none focus:border-[#AF52DE]">
                    <button type="submit"
                        class="px-5 py-2.5 rounded-[12px] bg-[#AF52DE] hover:bg-[#9B42C6] text-white text-xs font-semibold shadow-sm transition-all flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                        <i data-lucide="zap" class="w-3.5 h-3.5 fill-white"></i>
                        <span>Uji Keputusan</span>
                    </button>
                </div>
            </form>

            <!-- Results: 4 Dimensions & Verdict Score -->
            <template x-if="workflowResults.boost">
                <div class="space-y-4 pt-2">
                    <!-- Verdict Banner -->
                    <div class="p-4 sm:p-5 rounded-[16px] bg-[#AF52DE]/10 border border-[#AF52DE]/25 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#AF52DE] text-white" x-text="workflowResults.boost.verdict.status_label"></span>
                                <span class="text-xs font-bold text-black/50 dark:text-white/50" x-text="'Skor Kelayakan: ' + workflowResults.boost.verdict.score + '/100'"></span>
                            </div>
                            <p class="text-xs text-black/80 dark:text-white/90 font-medium" x-text="workflowResults.boost.verdict.summary"></p>
                        </div>
                    </div>

                    <!-- 4 Dimensions Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="(ev, dim) in workflowResults.boost.evaluations" :key="dim">
                            <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-black dark:text-white" x-text="ev.executive + ': ' + ev.dimension"></span>
                                    <span class="font-bold font-mono text-[11px] text-[#AF52DE]" x-text="ev.rating"></span>
                                </div>
                                <p class="text-xs text-black/60 dark:text-white/60 leading-relaxed" x-text="ev.analysis"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 5: WORKFLOW /schedule (Automasi Rutinitas Operasional)--}}
        {{-- ======================================================== --}}
        <div x-show="activeWorkflowTab === 'schedule' && !workflowLoading" class="space-y-5">
            <!-- Routine Switcher -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                <button type="button" @click="scheduleRoutine = 'morning_briefing'; runScheduleWorkflow();"
                    :class="scheduleRoutine === 'morning_briefing' ? 'bg-[#007AFF] text-white font-bold' : 'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70'"
                    class="px-3 py-1.5 rounded-[10px] text-xs transition-all shrink-0 cursor-pointer">
                    Briefing Pagi (08:00)
                </button>
                <button type="button" @click="scheduleRoutine = 'closing_review'; runScheduleWorkflow();"
                    :class="scheduleRoutine === 'closing_review' ? 'bg-[#007AFF] text-white font-bold' : 'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70'"
                    class="px-3 py-1.5 rounded-[10px] text-xs transition-all shrink-0 cursor-pointer">
                    Evaluasi Tutup Toko (21:30)
                </button>
                <button type="button" @click="scheduleRoutine = 'weekly_strategy'; runScheduleWorkflow();"
                    :class="scheduleRoutine === 'weekly_strategy' ? 'bg-[#007AFF] text-white font-bold' : 'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70'"
                    class="px-3 py-1.5 rounded-[10px] text-xs transition-all shrink-0 cursor-pointer">
                    Audit Strategis Mingguan
                </button>
            </div>

            <!-- Results: Routine Checklist & Daily Briefing -->
            <template x-if="workflowResults.schedule">
                <div class="p-5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-black dark:text-white" x-text="workflowResults.schedule.title"></h3>
                            <div class="text-[11px] text-black/50 dark:text-white/50" x-text="workflowResults.schedule.timestamp"></div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759] uppercase">Otomatisasi Aktif</span>
                    </div>

                    <p class="text-xs sm:text-[13px] text-black/80 dark:text-white/90 leading-relaxed font-medium" x-text="workflowResults.schedule.summary"></p>

                    <!-- Checklist -->
                    <div class="space-y-2 pt-2 border-t border-black/5 dark:border-white/5">
                        <div class="text-[11px] font-bold text-black/40 dark:text-white/40 uppercase">Daftar Periksa SOP Harian:</div>
                        <template x-for="item in workflowResults.schedule.checklist" :key="item.label">
                            <div class="flex items-center gap-2 text-xs text-black/80 dark:text-white/80">
                                <i data-lucide="check-square" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                                <span x-text="item.label"></span>
                            </div>
                        </template>
                    </div>

                    <div class="p-3 rounded-[12px] bg-black/5 dark:bg-white/5 text-xs text-black/70 dark:text-white/70 italic" x-text="workflowResults.schedule.notes"></div>
                </div>
            </template>
        </div>

    </div>

    {{-- ========================================================== --}}
    {{-- 4. 4 AI EXECUTIVES STATUS CARDS & DIRECT CONSULTATION     --}}
    {{-- ========================================================== --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-black dark:text-white tracking-tight uppercase">Jajaran Eksekutif AI Bisnis Anda</h2>
            <span class="text-xs text-black/50 dark:text-white/50">4 Agen Aktif &bull; Siap Konsultasi</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- CEO CARD -->
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-xs flex flex-col justify-between hover:border-amber-500/40 transition-all">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-500/20">
                            <i data-lucide="crown" class="w-5 h-5"></i>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Siap</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-black dark:text-white">AI CEO</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Strategi Makro, Target Omzet, & Visi Usaha</p>
                    </div>
                    <div class="text-[11px] text-black/70 dark:text-white/70 bg-black/[0.02] dark:bg-white/[0.02] p-2 rounded-[8px]">
                        KPI: Omzet Bulanan & Cash Runway
                    </div>
                </div>
                <div class="pt-3 border-t border-black/5 dark:border-white/5 mt-3">
                    <button type="button" @click="activeChatAgent = 'ceo'; showConsultationModal = true;"
                        class="w-full py-1.5 px-3 rounded-[8px] bg-black/[0.05] hover:bg-black/[0.09] dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-xs font-semibold text-black dark:text-white transition cursor-pointer">
                        Konsultasi CEO
                    </button>
                </div>
            </div>

            <!-- CFO CARD -->
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-xs flex flex-col justify-between hover:border-emerald-500/40 transition-all">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                            <i data-lucide="wallet" class="w-5 h-5"></i>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Siap</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-black dark:text-white">AI CFO</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Pengendalian Biaya, Arus Kas, & Piutang</p>
                    </div>
                    <div class="text-[11px] text-black/70 dark:text-white/70 bg-black/[0.02] dark:bg-white/[0.02] p-2 rounded-[8px]">
                        KPI: Gross Profit Margin & Piutang AR
                    </div>
                </div>
                <div class="pt-3 border-t border-black/5 dark:border-white/5 mt-3">
                    <button type="button" @click="activeChatAgent = 'cfo'; showConsultationModal = true;"
                        class="w-full py-1.5 px-3 rounded-[8px] bg-black/[0.05] hover:bg-black/[0.09] dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-xs font-semibold text-black dark:text-white transition cursor-pointer">
                        Konsultasi CFO
                    </button>
                </div>
            </div>

            <!-- COO CARD -->
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-xs flex flex-col justify-between hover:border-blue-500/40 transition-all">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-500/20">
                            <i data-lucide="workflow" class="w-5 h-5"></i>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Siap</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-black dark:text-white">AI COO</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Rantai Pasok, Stok Gudang, & Restock</p>
                    </div>
                    <div class="text-[11px] text-black/70 dark:text-white/70 bg-black/[0.02] dark:bg-white/[0.02] p-2 rounded-[8px]">
                        KPI: In-Stock Rate & Dead Stock
                    </div>
                </div>
                <div class="pt-3 border-t border-black/5 dark:border-white/5 mt-3">
                    <button type="button" @click="activeChatAgent = 'coo'; showConsultationModal = true;"
                        class="w-full py-1.5 px-3 rounded-[8px] bg-black/[0.05] hover:bg-black/[0.09] dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-xs font-semibold text-black dark:text-white transition cursor-pointer">
                        Konsultasi COO
                    </button>
                </div>
            </div>

            <!-- CMO CARD -->
            <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-xs flex flex-col justify-between hover:border-purple-500/40 transition-all">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-500/20">
                            <i data-lucide="trending-up" class="w-5 h-5"></i>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Siap</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-black dark:text-white">AI CMO</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Akselerasi Penjualan, Promo, & Omnichannel</p>
                    </div>
                    <div class="text-[11px] text-black/70 dark:text-white/70 bg-black/[0.02] dark:bg-white/[0.02] p-2 rounded-[8px]">
                        KPI: Average Order Value & Retensi
                    </div>
                </div>
                <div class="pt-3 border-t border-black/5 dark:border-white/5 mt-3">
                    <button type="button" @click="activeChatAgent = 'cmo'; showConsultationModal = true;"
                        class="w-full py-1.5 px-3 rounded-[8px] bg-black/[0.05] hover:bg-black/[0.09] dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-xs font-semibold text-black dark:text-white transition cursor-pointer">
                        Konsultasi CMO
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- 5. MAKER-CHECKER ACTION PROPOSALS QUEUE                   --}}
    {{-- ========================================================== --}}
    @if($pendingProposals->count() > 0)
    <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-amber-500/30 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-[10px] bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-black dark:text-white">Antrean Keputusan Bisnis (Maker-Checker)</h3>
                    <p class="text-xs text-black/50 dark:text-white/50">{{ $pendingCount }} proposal tindakan otomatis menunggu persetujuan Anda</p>
                </div>
            </div>
            <a href="{{ route('cooca-ai.actions', ['status' => 'pending']) }}" class="text-xs font-bold text-[#007AFF] hover:underline flex items-center gap-1">
                <span>Lihat Semua</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($pendingProposals as $prop)
                <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex flex-col justify-between gap-3">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80 uppercase">
                                {{ $prop->agent }}
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase {{ $prop->risk_level === 'HIGH' ? 'bg-[#FF3B30]/15 text-[#FF3B30]' : 'bg-[#FF9500]/15 text-[#FF9500]' }}">
                                Resiko {{ $prop->risk_level }}
                            </span>
                        </div>
                        <div class="text-xs font-bold text-black dark:text-white">{{ $prop->title }}</div>
                        <p class="text-[11px] text-black/60 dark:text-white/60 line-clamp-2">{{ $prop->description }}</p>
                        @if($prop->estimated_cost > 0)
                            <div class="text-[11px] font-mono font-semibold text-black/80 dark:text-white/80">
                                Estimasi Biaya: Rp {{ number_format((float) $prop->estimated_cost, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                        <button type="button" @click="quickApproveProposal('{{ $prop->id }}')"
                            class="flex-1 py-1.5 px-3 rounded-[8px] bg-[#34C759] hover:bg-[#2DBE50] text-white text-xs font-semibold shadow-xs transition text-center cursor-pointer">
                            Setujui & Jalankan
                        </button>
                        <a href="{{ route('cooca-ai.actions') }}"
                            class="py-1.5 px-3 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] text-xs font-semibold text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white transition">
                            Tinjau
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ========================================================== --}}
    {{-- 6. OPTIONAL VIRTUAL OFFICE 3D CANVAS (EXPANDABLE)         --}}
    {{-- ========================================================== --}}
    <div x-data="{ showCanvas: false }" class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="box" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-black dark:text-white">Peta Kampus 3D Virtual Headquarter</h3>
                    <p class="text-xs text-black/50 dark:text-white/50">Simulasi visual ruang kerja 3D interaktif para agen AI Anda</p>
                </div>
            </div>
            <button type="button" @click="showCanvas = !showCanvas"
                class="px-3.5 py-1.5 rounded-[10px] text-xs font-semibold transition-all border border-black/10 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/5 flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="eye" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span x-text="showCanvas ? 'Tutup Peta 3D' : 'Buka Peta 3D Virtual'"></span>
            </button>
        </div>

        <div x-show="showCanvas" x-transition.opacity style="display: none;">
            @php
                $allAgentStatuses = array_merge(
                    $officesStats['executive']['agent_statuses'] ?? [],
                    $officesStats['operations']['agent_statuses'] ?? [],
                    $officesStats['growth']['agent_statuses'] ?? []
                );
            @endphp
            @include('app.ai.partials.virtual_office_canvas', [
                'mode' => 'lobby',
                'agentStatuses' => $allAgentStatuses,
                'recentTasks' => $recentTasks,
                'pendingProposals' => $pendingProposals,
                'recentHistories' => $recentHistories,
                'companyTotals' => $companyTotals,
            ])
        </div>
    </div>

    {{-- Shared Consultation Modal --}}
    @include('app.ai.partials.consultation_modal')

</div>

{{-- Shared Alpine Script --}}
@include('app.ai.partials.office_script')
@endsection
