@extends('layouts.app', ['title' => __('ai.offices.digital_company_title')])

@section('content')
<div class="space-y-6" x-data="aiOfficeApp()">

    <!-- Header & Executive Controls -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold tracking-wide bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80 border border-black/10 dark:border-white/10">
                    {{ __('ai.offices.digital_workforce') }}
                </span>
                <span class="text-xs text-black/50 dark:text-white/50 font-mono">{!! __('ai.offices.agents_and_depts', ['agents' => 12, 'depts' => 5]) !!}</span>
            </div>
            <h1 class="text-2xl font-bold text-black dark:text-white tracking-tight mt-1">{{ __('ai.offices.office_header_title') }}</h1>
            <p class="text-xs text-black/60 dark:text-white/60 mt-0.5">{{ __('ai.offices.office_header_subtitle') }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('ai.actions') }}" class="relative px-3.5 py-2 rounded-xl bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-black dark:text-white text-xs font-semibold transition border border-black/10 dark:border-white/10 flex items-center gap-2">
                <i data-lucide="check-square" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                <span>Action Center</span>
                @if($pendingCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-amber-500 text-white tabular-nums">{{ $pendingCount }}</span>
                @endif
            </a>

            <a href="{{ route('ai.history') }}" class="px-3.5 py-2 rounded-xl bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-black dark:text-white text-xs font-semibold transition border border-black/10 dark:border-white/10 flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-sky-600 dark:text-sky-400"></i>
                <span>Work History</span>
            </a>

            <a href="{{ route('ai.providers') }}" class="px-3.5 py-2 rounded-xl bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-black dark:text-white text-xs font-semibold transition border border-black/10 dark:border-white/10 flex items-center gap-2">
                <i data-lucide="sliders" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                <span>AI Providers</span>
            </a>

            <button @click="openConsultationModal()" class="px-4 py-2 rounded-xl bg-black hover:bg-black/90 dark:bg-white dark:hover:bg-white/90 text-white dark:text-black font-semibold text-xs shadow-sm transition flex items-center gap-2">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                <span>{{ __('ai.offices.btn_consult_ai') }}</span>
            </button>
        </div>
    </div>

    <!-- Active Human Approval Alert Banner -->
    @if($pendingCount > 0)
    <div class="rounded-2xl p-4 bg-amber-500/10 dark:bg-amber-500/15 border border-amber-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-black dark:text-white uppercase tracking-wider">{{ __('ai.offices.alert_pending_proposals_title', ['count' => $pendingCount]) }}</h4>
                <p class="text-xs text-black/70 dark:text-white/70 mt-0.5">{{ __('ai.offices.alert_pending_proposals_desc') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('ai.actions', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold transition">
                {{ __('ai.offices.btn_review_proposals') }}
            </a>
        </div>
    </div>
    @endif

    <!-- EXECUTIVE FLOOR: AI CEO & AI COO (BENTO TOP ROW) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        
        <!-- AI CEO Suite: Strategy & Business Health -->
        <div class="rounded-2xl p-5 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-4">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-500/20">
                        <i data-lucide="crown" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-black dark:text-white">AI CEO</h2>
                            <span class="px-2 py-0.2 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">{{ __('ai.offices.active_standby') }}</span>
                        </div>
                        <p class="text-xs text-black/50 dark:text-white/50">Strategy & Business Health</p>
                    </div>
                </div>

                <button @click="triggerDailyDiagnosis()" :disabled="isEvaluating" class="px-2.5 py-1.5 rounded-lg bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-xs font-semibold text-black/80 dark:text-white/80 transition flex items-center gap-1.5 border border-black/10 dark:border-white/10">
                    <i data-lucide="activity" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span x-text="isEvaluating ? '{{ __('ai.offices.eval_evaluating') }}' : '{{ __('ai.offices.eval_daily') }}'"></span>
                </button>
            </div>

            <div class="space-y-2 border-t border-black/5 dark:border-white/5 pt-3">
                <div class="text-xs font-semibold text-black/80 dark:text-white/80">{{ __('ai.offices.executive_priorities_title') }}</div>
                <ul class="space-y-1.5 text-xs text-black/70 dark:text-white/70">
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                        <span>{{ __('ai.offices.executive_priority_1') }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                        <span>{{ __('ai.offices.executive_priority_2') }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                        <span>{{ __('ai.offices.executive_priority_3') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- AI COO Suite: Operational Orchestrator -->
        <div class="rounded-2xl p-5 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-4">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-500/20">
                        <i data-lucide="cpu" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-black dark:text-white">AI COO</h2>
                            <span class="px-2 py-0.2 rounded text-[10px] font-semibold bg-blue-500/10 text-blue-600 dark:text-blue-400">{{ __('ai.offices.ops_orchestration_standby_short') }}</span>
                        </div>
                        <p class="text-xs text-black/50 dark:text-white/50">Operations & Department Coordination</p>
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-xs text-black/40 dark:text-white/40">{{ __('ai.offices.active_provider') }}</div>
                    <div class="text-xs font-mono font-bold text-black/80 dark:text-white/80 uppercase">{{ $resolvedProvider['provider']->getProviderName() }}</div>
                </div>
            </div>

            <!-- Operations Stats Bento Strip -->
            <div class="grid grid-cols-3 gap-2 border-t border-black/5 dark:border-white/5 pt-3">
                <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                    <div class="text-[11px] text-black/50 dark:text-white/50 font-medium">{{ __('ai.offices.teams_involved') }}</div>
                    <div class="text-base font-bold text-black dark:text-white tabular-nums mt-0.5">{{ __('ai.offices.unit_divisions', ['count' => 5]) }}</div>
                </div>
                <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                    <div class="text-[11px] text-black/50 dark:text-white/50 font-medium">{{ __('ai.offices.total_agents') }}</div>
                    <div class="text-base font-bold text-black dark:text-white tabular-nums mt-0.5">{{ __('ai.offices.unit_agents', ['count' => 12]) }}</div>
                </div>
                <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                    <div class="text-[11px] text-black/50 dark:text-white/50 font-medium">{{ __('ai.offices.needs_review') }}</div>
                    <div class="text-base font-bold text-amber-600 dark:text-amber-400 tabular-nums mt-0.5">{{ __('ai.offices.action_count', ['count' => $pendingCount]) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 DEPARTMENTS FLOOR & AGENT WORKSTATIONS -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-black/60 dark:text-white/60 uppercase tracking-wider">{{ __('ai.offices.dept_and_desks') }}</h3>
            <span class="text-xs text-black/40 dark:text-white/40">{{ __('ai.offices.status_real_history') }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            
            <!-- 1. SALES DEPARTMENT -->
            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/5">
                    <div class="flex items-center gap-2">
                        <i data-lucide="badge-dollar-sign" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                        <span class="text-xs font-bold text-black dark:text-white">{{ __('ai.offices.dept_sales') }}</span>
                    </div>
                    <span class="text-[10px] font-mono text-black/50 dark:text-white/50">{{ __('ai.offices.lead_sales_director') }}</span>
                </div>

                <!-- Agents in Sales -->
                <div class="space-y-2">
                    <!-- Sales Agent -->
                    @php $sStatus = $agentStatuses['sales'] ?? ['status' => 'IDLE', 'status_label' => __('ai.offices.available'), 'current_work' => __('ai.offices.no_tasks'), 'last_active' => '']; @endphp
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="trending-up" class="w-3.5 h-3.5 text-emerald-500"></i>
                                Sales Agent
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium {{ $sStatus['status'] === 'WORKING' ? 'bg-blue-500/10 text-blue-600' : 'bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60' }}">
                                {{ $sStatus['status_label'] }}
                            </span>
                        </div>
                        <div class="text-[11px] text-black/60 dark:text-white/60 truncate">{{ $sStatus['current_work'] }}</div>
                    </div>

                    <!-- Customer Agent -->
                    @php $cStatus = $agentStatuses['customer'] ?? ['status' => 'IDLE', 'status_label' => __('ai.offices.available'), 'current_work' => __('ai.offices.no_tasks'), 'last_active' => '']; @endphp
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                                Customer Agent
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium {{ $cStatus['status'] === 'WORKING' ? 'bg-blue-500/10 text-blue-600' : 'bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60' }}">
                                {{ $cStatus['status_label'] }}
                            </span>
                        </div>
                        <div class="text-[11px] text-black/60 dark:text-white/60 truncate">{{ $cStatus['current_work'] }}</div>
                    </div>
                </div>
            </div>

            <!-- 2. OPERATIONS DEPARTMENT -->
            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/5">
                    <div class="flex items-center gap-2">
                        <i data-lucide="boxes" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                        <span class="text-xs font-bold text-black dark:text-white">{{ __('ai.offices.dept_operations') }}</span>
                    </div>
                    <span class="text-[10px] font-mono text-black/50 dark:text-white/50">{{ __('ai.offices.lead_coo') }}</span>
                </div>

                <div class="space-y-2">
                    <!-- Inventory Agent -->
                    @php $iStatus = $agentStatuses['inventory'] ?? ['status' => 'IDLE', 'status_label' => __('ai.offices.available'), 'current_work' => __('ai.offices.no_tasks'), 'last_active' => '']; @endphp
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="boxes" class="w-3.5 h-3.5 text-blue-500"></i>
                                Inventory Agent
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">{{ $iStatus['status_label'] }}</span>
                        </div>
                        <div class="text-[11px] text-black/60 dark:text-white/60 truncate">{{ $iStatus['current_work'] }}</div>
                    </div>

                    <!-- Purchasing Agent -->
                    @php $pStatus = $agentStatuses['purchasing'] ?? ['status' => 'IDLE', 'status_label' => __('ai.offices.available'), 'current_work' => __('ai.offices.no_tasks'), 'last_active' => '']; @endphp
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="shopping-cart" class="w-3.5 h-3.5 text-blue-500"></i>
                                Purchasing Agent
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">{{ $pStatus['status_label'] }}</span>
                        </div>
                        <div class="text-[11px] text-black/60 dark:text-white/60 truncate">{{ $pStatus['current_work'] }}</div>
                    </div>

                    <!-- Marketplace Agent -->
                    @php $mStatus = $agentStatuses['marketplace'] ?? ['status' => 'IDLE', 'status_label' => __('ai.offices.available'), 'current_work' => __('ai.offices.no_tasks'), 'last_active' => '']; @endphp
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="store" class="w-3.5 h-3.5 text-blue-500"></i>
                                Marketplace Agent
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">{{ $mStatus['status_label'] }}</span>
                        </div>
                        <div class="text-[11px] text-black/60 dark:text-white/60 truncate">{{ $mStatus['current_work'] }}</div>
                    </div>
                </div>
            </div>

            <!-- 3. FINANCE DEPARTMENT -->
            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/5">
                    <div class="flex items-center gap-2">
                        <i data-lucide="wallet" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                        <span class="text-xs font-bold text-black dark:text-white">{{ __('ai.offices.dept_finance') }}</span>
                    </div>
                    <span class="text-[10px] font-mono text-black/50 dark:text-white/50">{{ __('ai.offices.lead_cfo') }}</span>
                </div>

                <div class="space-y-2">
                    <!-- Finance Agent -->
                    @php $fStatus = $agentStatuses['finance'] ?? ['status' => 'IDLE', 'status_label' => __('ai.offices.available'), 'current_work' => __('ai.offices.no_tasks'), 'last_active' => '']; @endphp
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="coins" class="w-3.5 h-3.5 text-amber-500"></i>
                                Finance Agent
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">{{ $fStatus['status_label'] }}</span>
                        </div>
                        <div class="text-[11px] text-black/60 dark:text-white/60 truncate">{{ $fStatus['current_work'] }}</div>
                    </div>

                    <!-- Reporting Agent -->
                    @php $rStatus = $agentStatuses['reporting'] ?? ['status' => 'IDLE', 'status_label' => __('ai.offices.available'), 'current_work' => __('ai.offices.no_tasks'), 'last_active' => '']; @endphp
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 text-amber-500"></i>
                                Reporting Agent
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">{{ $rStatus['status_label'] }}</span>
                        </div>
                        <div class="text-[11px] text-black/60 dark:text-white/60 truncate">{{ $rStatus['current_work'] }}</div>
                    </div>
                </div>
            </div>

            <!-- 4. MARKETING DEPARTMENT -->
            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/5">
                    <div class="flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                        <span class="text-xs font-bold text-black dark:text-white">{{ __('ai.offices.dept_marketing') }}</span>
                    </div>
                    <span class="text-[10px] font-mono text-black/50 dark:text-white/50">{{ __('ai.offices.lead_cmo') }}</span>
                </div>

                <div class="space-y-2">
                    <!-- Marketing Agent -->
                    @php $mktStatus = $agentStatuses['marketing'] ?? ['status' => 'IDLE', 'status_label' => __('ai.offices.available'), 'current_work' => __('ai.offices.no_tasks'), 'last_active' => '']; @endphp
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="target" class="w-3.5 h-3.5 text-purple-500"></i>
                                Marketing Agent
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">{{ $mktStatus['status_label'] }}</span>
                        </div>
                        <div class="text-[11px] text-black/60 dark:text-white/60 truncate">{{ $mktStatus['current_work'] }}</div>
                    </div>

                    <!-- Content Agent -->
                    @php $cntStatus = $agentStatuses['content'] ?? ['status' => 'IDLE', 'status_label' => __('ai.offices.available'), 'current_work' => __('ai.offices.no_tasks'), 'last_active' => '']; @endphp
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="pen-tool" class="w-3.5 h-3.5 text-purple-500"></i>
                                Content Agent
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">{{ $cntStatus['status_label'] }}</span>
                        </div>
                        <div class="text-[11px] text-black/60 dark:text-white/60 truncate">{{ $cntStatus['current_work'] }}</div>
                    </div>

                    <!-- Social Media Agent -->
                    @php $socStatus = $agentStatuses['social_media'] ?? ['status' => 'IDLE', 'status_label' => __('ai.offices.available'), 'current_work' => __('ai.offices.no_tasks'), 'last_active' => '']; @endphp
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="share-2" class="w-3.5 h-3.5 text-purple-500"></i>
                                Social Media Agent
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">{{ $socStatus['status_label'] }}</span>
                        </div>
                        <div class="text-[11px] text-black/60 dark:text-white/60 truncate">{{ $socStatus['current_work'] }}</div>
                    </div>
                </div>
            </div>

            <!-- 5. PEOPLE & HR DEPARTMENT -->
            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/5">
                    <div class="flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4 text-teal-600 dark:text-teal-400"></i>
                        <span class="text-xs font-bold text-black dark:text-white">{{ __('ai.offices.dept_hr') }}</span>
                    </div>
                    <span class="text-[10px] font-mono text-black/50 dark:text-white/50">{{ __('ai.offices.lead_hr_lead') }}</span>
                </div>

                <div class="space-y-2">
                    <!-- HR Agent -->
                    @php $hrStatus = $agentStatuses['hr'] ?? ['status' => 'IDLE', 'status_label' => __('ai.offices.available'), 'current_work' => __('ai.offices.no_tasks'), 'last_active' => '']; @endphp
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="users" class="w-3.5 h-3.5 text-teal-500"></i>
                                HR Agent
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">{{ $hrStatus['status_label'] }}</span>
                        </div>
                        <div class="text-[11px] text-black/60 dark:text-white/60 truncate">{{ $hrStatus['current_work'] }}</div>
                    </div>
                </div>
            </div>

            <!-- 6. EXECUTIVE ANALYST (BUSINESS AGENT) -->
            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/5">
                    <div class="flex items-center gap-2">
                        <i data-lucide="line-chart" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span class="text-xs font-bold text-black dark:text-white">{{ __('ai.offices.dept_analyst') }}</span>
                    </div>
                    <span class="text-[10px] font-mono text-black/50 dark:text-white/50">{{ __('ai.offices.lead_ceo') }}</span>
                </div>

                <div class="space-y-2">
                    <!-- Business Agent -->
                    @php $bizStatus = $agentStatuses['business'] ?? ['status' => 'IDLE', 'status_label' => __('ai.offices.available'), 'current_work' => __('ai.offices.no_tasks'), 'last_active' => '']; @endphp
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-500"></i>
                                Business Agent
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">{{ $bizStatus['status_label'] }}</span>
                        </div>
                        <div class="text-[11px] text-black/60 dark:text-white/60 truncate">{{ $bizStatus['current_work'] }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ACTIVITY FEED & RECENT HISTORIES -->
    <div class="rounded-2xl p-5 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="activity" class="w-4 h-4 text-black/70 dark:text-white/70"></i>
                <h3 class="text-sm font-bold text-black dark:text-white">AI Activity Feed</h3>
            </div>
            <a href="{{ route('ai.history') }}" class="text-xs font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white transition">{{ __('ai.offices.see_all_history') }} &rarr;</a>
        </div>

        @if($workHistories->isEmpty())
            <div class="py-8 text-center space-y-2">
                <div class="w-10 h-10 rounded-xl bg-black/5 dark:bg-white/5 mx-auto flex items-center justify-center text-black/30 dark:text-white/30">
                    <i data-lucide="inbox" class="w-5 h-5"></i>
                </div>
                <div class="text-xs font-semibold text-black/60 dark:text-white/60">{{ __('ai.offices.all_teams_updated') }}</div>
                <p class="text-xs text-black/40 dark:text-white/40">{{ __('ai.offices.no_sessions_today') }}</p>
            </div>
        @else
            <div class="divide-y divide-black/5 dark:divide-white/5">
                @foreach($workHistories as $history)
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-black dark:text-white">{{ $history->session_title }}</span>
                                <span class="text-[10px] text-black/40 dark:text-white/40 font-mono">{{ $history->recorded_at->translatedFormat('H:i, d M Y') }}</span>
                            </div>
                            <p class="text-xs text-black/60 dark:text-white/60 line-clamp-1">{{ $history->executive_summary }}</p>
                        </div>

                        <div class="flex items-center gap-3 shrink-0 text-xs text-black/60 dark:text-white/60 font-mono">
                            <span title="Temuan Terdeteksi">{{ __('ai.offices.findings_detected', ['count' => $history->insights_count]) }}</span>
                            &bull;
                            <span title="Aksi Diusulkan" class="font-bold text-black dark:text-white">{{ __('ai.offices.proposals_suggested', ['count' => $history->actions_count]) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- FULL-SIZE MODAL SHEET: KONSULTASI AI (APPLE HIG CANVAS XXL) -->
    <template x-teleport="body">
        <div x-show="showConsultationModal" 
             x-cloak
             class="fixed inset-0 z-[200] overflow-y-auto flex items-center justify-center p-3 sm:p-6" 
             @keydown.escape.window="closeConsultationModal()"
             role="dialog"
             aria-modal="true"
             aria-labelledby="ai-office-consultation-title">
            
            <!-- Backdrop -->
            <div x-show="showConsultationModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeConsultationModal()"
                 class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div x-show="showConsultationModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                 @click.outside="closeConsultationModal()"
                 class="relative w-full max-w-4xl bg-white dark:bg-zinc-900 rounded-[24px] sm:rounded-3xl border border-black/10 dark:border-white/10 shadow-2xl flex flex-col max-h-[90vh] overflow-hidden z-10">
                
                 <!-- Modal Header -->
                <div class="p-5 border-b border-black/10 dark:border-white/10 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-black dark:bg-white text-white dark:text-black flex items-center justify-center shrink-0">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 id="ai-office-consultation-title" class="text-sm font-bold text-black dark:text-white">{{ __('ai.offices.consultation_title') }}</h3>
                            <p class="text-xs text-black/50 dark:text-white/50">{{ __('ai.offices.consultation_subtitle') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="closeConsultationModal()" class="min-w-[44px] min-h-[44px] rounded-full hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center text-black/50 dark:text-white/50 transition active:scale-95" aria-label="{{ __('common.close') }}">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

            <!-- Modal Content (Scrollable) -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1">
                
                <!-- Input Box -->
                <form @submit.prevent="submitQuery()" class="space-y-3">
                    <div class="relative">
                        <textarea x-model="userQuery" 
                                  rows="3" 
                                  placeholder="{{ __('ai.offices.consultation_placeholder') }}"
                                  class="w-full bg-black/[0.02] dark:bg-white/[0.02] border border-black/15 dark:border-white/15 rounded-2xl p-4 text-xs sm:text-sm text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:border-black dark:focus:border-white transition"></textarea>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <!-- Quick Question Chips -->
                        <div class="flex flex-wrap items-center gap-1.5">
                            <button type="button" @click="quickAsk('{{ __('ai.offices.prompt_revenue_drop') }}')" class="px-2.5 py-1 rounded-lg bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-[11px] font-medium text-black/70 dark:text-white/70 transition">
                                {{ __('ai.offices.chip_revenue_drop') }}
                            </button>
                            <button type="button" @click="quickAsk('{{ __('ai.offices.prompt_critical_stock') }}')" class="px-2.5 py-1 rounded-lg bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-[11px] font-medium text-black/70 dark:text-white/70 transition">
                                {{ __('ai.offices.chip_critical_stock') }}
                            </button>
                            <button type="button" @click="quickAsk('{{ __('ai.offices.prompt_dormant_promo') }}')" class="px-2.5 py-1 rounded-lg bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-[11px] font-medium text-black/70 dark:text-white/70 transition">
                                {{ __('ai.offices.chip_dormant_promo') }}
                            </button>
                        </div>

                        <button type="submit" 
                                :disabled="isSubmitting || !userQuery.trim()" 
                                class="px-4 py-2 rounded-xl bg-black hover:bg-black/90 dark:bg-white dark:hover:bg-white/90 text-white dark:text-black font-semibold text-xs transition disabled:opacity-40 flex items-center gap-1.5 shrink-0">
                            <span x-text="isSubmitting ? '{{ __('ai.offices.analyzing_data') }}' : '{{ __('ai.offices.send_to_ai') }}'"></span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </form>

                <!-- Processing State Banner -->
                <div x-show="isSubmitting" class="p-6 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/10 dark:border-white/10 text-center space-y-2">
                    <div class="inline-block animate-spin text-black/60 dark:text-white/60">
                        <i data-lucide="loader" class="w-6 h-6"></i>
                    </div>
                    <div class="text-xs font-bold text-black dark:text-white">{{ __('ai.offices.coordinating_divisions') }}</div>
                    <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('ai.offices.coordinating_sub') }}</p>
                </div>

                <!-- Structured Response Container -->
                <div x-show="responseResult" class="space-y-4 pt-2">
                    <div class="rounded-2xl p-5 bg-black/[0.02] dark:bg-white/[0.02] border border-black/10 dark:border-white/10 space-y-4">
                        
                        <!-- Header & Participating Squad -->
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/5">
                            <div class="text-xs font-bold text-black dark:text-white">{{ __('ai.offices.team_analysis_result') }}</div>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <template x-for="agentName in (responseResult?.participating_agents || [])" :key="agentName">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-medium bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80" x-text="agentName"></span>
                                </template>
                            </div>
                        </div>

                        <!-- Executive Summary -->
                        <div>
                            <div class="text-xs font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider mb-1">Executive Summary</div>
                            <p class="text-xs sm:text-sm text-black dark:text-white leading-relaxed font-medium" x-text="responseResult?.summary"></p>
                        </div>

                        <!-- Findings -->
                        <template x-if="responseResult?.findings && responseResult.findings.length > 0">
                            <div>
                                <div class="text-xs font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider mb-2">{{ __('ai.offices.factual_findings') }}</div>
                                <ul class="space-y-1 text-xs text-black/70 dark:text-white/70">
                                    <template x-for="(finding, i) in responseResult.findings" :key="i">
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                                            <span x-text="finding"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        <!-- Recommendations -->
                        <template x-if="responseResult?.recommendations && responseResult.recommendations.length > 0">
                            <div>
                                <div class="text-xs font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider mb-2">{{ __('ai.offices.strategic_recommendations') }}</div>
                                <ul class="space-y-1 text-xs text-black/70 dark:text-white/70">
                                    <template x-for="(rec, i) in responseResult.recommendations" :key="i">
                                        <li class="flex items-start gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                                            <span x-text="rec"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        <!-- Proposed Actions -->
                        <template x-if="responseResult?.proposals && responseResult.proposals.length > 0">
                            <div class="pt-3 border-t border-black/5 dark:border-white/5 space-y-3">
                                <div class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">{{ __('ai.offices.proposals_pending_approval') }}</div>
                                
                                <div class="grid grid-cols-1 gap-2.5">
                                    <template x-for="prop in responseResult.proposals" :key="prop.id">
                                        <div class="p-3.5 rounded-xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-black dark:text-white" x-text="prop.title"></span>
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-500/20 text-amber-700 dark:text-amber-300" x-text="prop.risk_level"></span>
                                                </div>
                                                <div class="text-xs text-black/60 dark:text-white/60" x-text="prop.description"></div>
                                            </div>

                                            <div class="flex items-center gap-2 shrink-0">
                                                <a href="{{ route('ai.actions') }}" class="px-3 py-1.5 rounded-lg bg-black dark:bg-white text-white dark:text-black text-xs font-semibold">
                                                    {{ __('ai.offices.open_action_center') }} &rarr;
                                                </a>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-black/10 dark:border-white/10 flex items-center justify-between text-xs text-black/50 dark:text-white/50 bg-black/[0.01] dark:bg-white/[0.01]">
                <span>{{ __('ai.offices.modal_notice') }}</span>
                <button type="button" @click="closeConsultationModal()" class="min-h-[44px] px-4 py-2 rounded-xl bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 font-semibold text-black dark:text-white transition active:scale-95">{{ __('common.close') }}</button>
            </div>

        </div>
    </div>
</template>

</div>

<script>
function aiOfficeApp() {
    return {
        showConsultationModal: false,
        userQuery: '',
        isSubmitting: false,
        isEvaluating: false,
        responseResult: null,

        openConsultationModal() {
            this.showConsultationModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        closeConsultationModal() {
            this.showConsultationModal = false;
        },

        quickAsk(text) {
            this.userQuery = text;
            this.submitQuery();
        },

        async submitQuery() {
            if (!this.userQuery.trim() || this.isSubmitting) return;

            this.isSubmitting = true;
            this.responseResult = null;

            try {
                const res = await fetch("{{ route('ai.ask') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ query: this.userQuery })
                });

                const data = await res.json();
                if (data.success) {
                    this.responseResult = data.data;
                } else {
                    alert(data.message || '{{ __('ai.script.error_processing') }}');
                }
            } catch (err) {
                alert('{{ __('ai.script.network_error_prefix') }}' + err.message);
            } finally {
                this.isSubmitting = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        async triggerDailyDiagnosis() {
            if (this.isEvaluating) return;

            this.isEvaluating = true;
            try {
                const res = await fetch("{{ route('ai.daily-check') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (data.success) {
                    this.responseResult = data.data;
                    this.showConsultationModal = true;
                } else {
                    alert(data.message || '{{ __('ai.script.eval_failed') }}');
                }
            } catch (err) {
                alert('{{ __('ai.script.network_error_prefix') }}' + err.message);
            } finally {
                this.isEvaluating = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        }
    };
}
</script>
@endsection
