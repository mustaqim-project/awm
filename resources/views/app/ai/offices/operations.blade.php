@extends('layouts.app', ['title' => __('ai.offices.operations_title')])

@section('content')
<div class="space-y-6 w-full" x-data="aiOfficeBase()">

    <!-- Unified Apple HIG Navigation Hub -->
    @include('app.ai.partials.office_navigation', ['activeOffice' => 'operations', 'pendingCount' => $officeStats['pending_approvals_count'] ?? 0])

    <!-- INTERACTIVE VIRTUAL OFFICE FLOOR (Watch AI Agents Work Live) -->
    @include('app.ai.partials.virtual_office_canvas', [
        'mode' => 'operations',
        'agentStatuses' => $officeStats['agent_statuses'] ?? [],
        'recentTasks' => $recentTasks,
        'pendingProposals' => $pendingProposals,
        'recentHistories' => $recentHistories,
    ])

    <!-- AI COO COMMAND SUITE (TOP BENTO) -->
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-500/20 shrink-0">
                    <i data-lucide="cpu" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-black dark:text-white">AI COO</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400">{{ __('ai.offices.ops_orchestration_standby') }}</span>
                    </div>
                    <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">{{ __('ai.offices.coo_tagline') }}</p>
                </div>
            </div>

            <div class="text-right">
                <div class="text-[11px] text-black/40 dark:text-white/40">{{ __('ai.offices.status_operation') }}</div>
                <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center justify-end gap-1.5 mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>{{ __('ai.offices.controlled_coordinated') }}</span>
                </div>
            </div>
        </div>

        <!-- Operational Responsibilities Strip -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 border-t border-black/5 dark:border-white/5 pt-4">
            <div class="p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                <div class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.offices.logistics_warehouse') }}</span>
                </div>
                <p class="text-xs text-black/70 dark:text-white/70">{{ __('ai.offices.logistics_desc') }}</p>
            </div>

            <div class="p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.offices.procurement_supplier') }}</span>
                </div>
                <p class="text-xs text-black/70 dark:text-white/70">{{ __('ai.offices.procurement_desc') }}</p>
            </div>

            <div class="p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                <div class="text-[11px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="store" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.offices.omnichannel_market') }}</span>
                </div>
                <p class="text-xs text-black/70 dark:text-white/70">{{ __('ai.offices.omnichannel_desc') }}</p>
            </div>
        </div>
    </div>

    <!-- OPERATIONAL HEALTH & METRICS STRIP -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('ai.offices.active_catalog') }}</div>
            <div class="text-lg sm:text-xl font-bold text-black dark:text-white tabular-nums tracking-tight mt-1">
                {{ __('ai.offices.unit_items', ['count' => number_format($operationalMetrics['total_products'] ?? 0)]) }}
            </div>
            <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">{{ __('ai.offices.active_catalog_sub') }}</div>
        </div>

        <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('ai.offices.critical_stock_rop') }}</div>
            <div class="text-lg sm:text-xl font-bold text-amber-600 dark:text-amber-400 tabular-nums tracking-tight mt-1">
                {{ __('ai.offices.unit_items', ['count' => count($stockLevels['critical_items'] ?? [])]) }}
            </div>
            <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">{{ __('ai.offices.critical_stock_sub') }}</div>
        </div>

        <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('ai.offices.suppliers_procurement') }}</div>
            <div class="text-lg sm:text-xl font-bold text-black dark:text-white tabular-nums tracking-tight mt-1">
                {{ __('ai.offices.unit_suppliers', ['count' => $operationalMetrics['total_suppliers'] ?? 0]) }}
            </div>
            <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">{{ __('ai.offices.suppliers_sub', ['count' => $operationalMetrics['pending_pos'] ?? 0]) }}</div>
        </div>

        <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('ai.offices.connected_marketplace') }}</div>
            <div class="text-lg sm:text-xl font-bold text-blue-600 dark:text-blue-400 tabular-nums tracking-tight mt-1">
                {{ __('ai.offices.unit_channels', ['count' => $operationalMetrics['connected_marketplaces'] ?? 0]) }}
            </div>
            <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">Shopee, Tokopedia, TikTok</div>
        </div>
    </div>

    <!-- SQUAD WORKSTATIONS (INVENTORY, PURCHASING, MARKETPLACE AGENTS) -->
    <div class="space-y-4">
        <div>
            <h3 class="text-sm font-bold text-black dark:text-white">{{ __('ai.offices.workstations_ops_daily_title') }}</h3>
            <p class="text-xs text-black/50 dark:text-white/50">{{ __('ai.offices.workstations_ops_daily_sub') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Workstation 1: Inventory Agent -->
            @php $invAgent = $officeStats['agent_statuses']['inventory'] ?? null; @endphp
            <div class="rounded-2xl p-5 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between space-y-3">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-black/5 dark:bg-white/10 text-black dark:text-white flex items-center justify-center font-bold">
                                <i data-lucide="boxes" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-black dark:text-white">Inventory Agent</h4>
                                <div class="text-[10px] text-black/40 dark:text-white/40">{{ __('ai.offices.warehouse_and_stock') }}</div>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $invAgent['status'] === 'WORKING' ? 'bg-blue-500/10 text-blue-600' : ($invAgent['status'] === 'WAITING_APPROVAL' ? 'bg-amber-500/10 text-amber-600' : 'bg-emerald-500/10 text-emerald-600') }}">
                            {{ $invAgent['status_label'] ?? __('ai.offices.available') }}
                        </span>
                    </div>

                    <p class="text-xs text-black/60 dark:text-white/60">{{ __('ai.offices.agent_inv_desc') }}</p>
                    
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 text-[11px] text-black/70 dark:text-white/70">
                        <span class="text-black/40 dark:text-white/40 font-medium">{{ __('ai.offices.current_task') }}</span>
                        <div class="font-semibold text-black dark:text-white truncate mt-0.5">{{ $invAgent['current_work'] ?? __('ai.offices.no_active_task') }}</div>
                    </div>
                </div>

                <div class="text-[10px] text-black/40 dark:text-white/40 pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                    <span>{{ __('ai.offices.last_activity') }}</span>
                    <span class="font-medium">{{ $invAgent['last_active'] ?? '-' }}</span>
                </div>
            </div>

            <!-- Workstation 2: Purchasing Agent -->
            @php $purAgent = $officeStats['agent_statuses']['purchasing'] ?? null; @endphp
            <div class="rounded-2xl p-5 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between space-y-3">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-black/5 dark:bg-white/10 text-black dark:text-white flex items-center justify-center font-bold">
                                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-black dark:text-white">Purchasing Agent</h4>
                                <div class="text-[10px] text-black/40 dark:text-white/40">{{ __('ai.offices.procurement_and_supplier') }}</div>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $purAgent['status'] === 'WORKING' ? 'bg-blue-500/10 text-blue-600' : ($purAgent['status'] === 'WAITING_APPROVAL' ? 'bg-amber-500/10 text-amber-600' : 'bg-emerald-500/10 text-emerald-600') }}">
                            {{ $purAgent['status_label'] ?? __('ai.offices.available') }}
                        </span>
                    </div>

                    <p class="text-xs text-black/60 dark:text-white/60">{{ __('ai.offices.agent_pur_desc') }}</p>
                    
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 text-[11px] text-black/70 dark:text-white/70">
                        <span class="text-black/40 dark:text-white/40 font-medium">{{ __('ai.offices.current_task') }}</span>
                        <div class="font-semibold text-black dark:text-white truncate mt-0.5">{{ $purAgent['current_work'] ?? __('ai.offices.no_active_task') }}</div>
                    </div>
                </div>

                <div class="text-[10px] text-black/40 dark:text-white/40 pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                    <span>{{ __('ai.offices.last_activity') }}</span>
                    <span class="font-medium">{{ $purAgent['last_active'] ?? '-' }}</span>
                </div>
            </div>

            <!-- Workstation 3: Marketplace Agent -->
            @php $mktAgent = $officeStats['agent_statuses']['marketplace'] ?? null; @endphp
            <div class="rounded-2xl p-5 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between space-y-3">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-black/5 dark:bg-white/10 text-black dark:text-white flex items-center justify-center font-bold">
                                <i data-lucide="store" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-black dark:text-white">Marketplace Agent</h4>
                                <div class="text-[10px] text-black/40 dark:text-white/40">{{ __('ai.offices.omnichannel_sync') }}</div>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $mktAgent['status'] === 'WORKING' ? 'bg-blue-500/10 text-blue-600' : ($mktAgent['status'] === 'WAITING_APPROVAL' ? 'bg-amber-500/10 text-amber-600' : 'bg-emerald-500/10 text-emerald-600') }}">
                            {{ $mktAgent['status_label'] ?? __('ai.offices.available') }}
                        </span>
                    </div>

                    <p class="text-xs text-black/60 dark:text-white/60">{{ __('ai.offices.agent_mkt_desc') }}</p>
                    
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 text-[11px] text-black/70 dark:text-white/70">
                        <span class="text-black/40 dark:text-white/40 font-medium">{{ __('ai.offices.current_task') }}</span>
                        <div class="font-semibold text-black dark:text-white truncate mt-0.5">{{ $mktAgent['current_work'] ?? __('ai.offices.no_active_task') }}</div>
                    </div>
                </div>

                <div class="text-[10px] text-black/40 dark:text-white/40 pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                    <span>{{ __('ai.offices.last_activity') }}</span>
                    <span class="font-medium">{{ $mktAgent['last_active'] ?? '-' }}</span>
                </div>
            </div>

        </div>
    </div>

    <!-- STOCK HEALTH WARNINGS (IF CRITICAL ITEMS DETECTED) -->
    @if(!empty($stockLevels['critical_items']))
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-amber-500/30 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-black dark:text-white">{{ __('ai.offices.stock_warning_title') }}</h3>
                    <p class="text-xs text-black/50 dark:text-white/50">{{ __('ai.offices.stock_warning_sub') }}</p>
                </div>
            </div>
            <button type="button" @click="quickAsk(@js(__('ai.offices.prompt_auto_draft_po')))" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold transition">
                {{ __('ai.offices.btn_auto_draft_po') }}
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach(array_slice($stockLevels['critical_items'], 0, 6) as $item)
                <div class="p-3 rounded-2xl bg-amber-500/5 border border-amber-500/20 flex items-center justify-between gap-3 text-xs">
                    <div>
                        <div class="font-bold text-black dark:text-white truncate">{{ $item['name'] ?? 'Item' }}</div>
                        <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('ai.offices.remaining_stock_units', ['stock' => $item['stock'] ?? 0]) }}</div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-700 dark:text-amber-300">CRITICAL</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- CURRENT OPERATIONAL WORK IN PROGRESS -->
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white">{{ __('ai.offices.running_ops_tasks_title') }}</h3>
                <p class="text-xs text-black/50 dark:text-white/50">{{ __('ai.offices.running_ops_tasks_sub') }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-md text-[11px] font-mono font-semibold bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">
                {{ __('ai.offices.tasks_recorded', ['count' => $recentTasks->count()]) }}
            </span>
        </div>

        <div class="space-y-2">
            @forelse($recentTasks as $task)
                <div class="p-3.5 rounded-2xl bg-black/[0.01] dark:bg-white/[0.01] border border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="package" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80 uppercase">
                                    {{ $task->agent }}
                                </span>
                                <span class="text-xs font-semibold text-black dark:text-white">{{ $task->input }}</span>
                            </div>
                            <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">
                                {{ $task->created_at->diffForHumans() }} &bull; {{ __('ai.offices.duration', ['ms' => $task->duration_ms ?? 0]) }}
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0">
                        @if($task->isCompleted())
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">COMPLETED</span>
                        @elseif($task->isRunning())
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400">WORKING</span>
                        @elseif($task->isWaitingApproval())
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400">WAITING APPROVAL</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-zinc-500/10 text-zinc-600 dark:text-zinc-400">{{ $task->status }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 rounded-2xl bg-black/[0.01] dark:bg-white/[0.01] border border-black/5 dark:border-white/5 text-center text-xs text-black/50 dark:text-white/50">
                    {{ __('ai.offices.no_active_ops_tasks') }}
                </div>
            @endforelse
        </div>
    </div>

    <!-- OPERATIONAL PROPOSALS REQUIRING OWNER DECISION -->
    @if($pendingProposals->count() > 0)
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-amber-500/20 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                    <span>{{ __('ai.offices.pending_ops_proposals_title') }}</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">{{ __('ai.offices.pending_ops_proposals_sub') }}</p>
            </div>
            <a href="{{ route('cooca-ai.actions', ['status' => 'pending']) }}" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">
                {{ __('ai.offices.open_action_center') }} &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($pendingProposals as $proposal)
                <div class="p-4 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/10 dark:border-white/10 space-y-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80 uppercase">
                                {{ $proposal->agent }}
                            </span>
                            <span class="px-2 py-0.2 rounded text-[10px] font-bold uppercase {{ $proposal->risk_level === 'high' || $proposal->risk_level === 'critical' ? 'bg-red-500/15 text-red-600 dark:text-red-400' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400' }}">
                                {{ $proposal->risk_level }}
                            </span>
                        </div>
                        <h4 class="text-xs font-bold text-black dark:text-white">{{ $proposal->title }}</h4>
                        <p class="text-xs text-black/60 dark:text-white/60 line-clamp-2">{{ $proposal->description }}</p>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-black/5 dark:border-white/5 text-xs">
                        <span class="text-black/40 dark:text-white/40 font-mono text-[11px]">{{ $proposal->created_at->diffForHumans() }}</span>
                        <div class="flex items-center gap-2">
                            <form action="{{ route('cooca-ai.actions.reject', $proposal) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 font-semibold text-black/80 dark:text-white/80 transition">{{ __('ai.offices.reject') }}</button>
                            </form>
                            <form action="{{ route('cooca-ai.actions.approve', $proposal) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-black hover:bg-black/90 dark:bg-white dark:hover:bg-white/90 text-white dark:text-black font-semibold transition">{{ __('ai.offices.approve') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- RECENT OPERATIONS WORK HISTORY -->
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white">{{ __('ai.offices.audit_history_ops_title') }}</h3>
                <p class="text-xs text-black/50 dark:text-white/50">{{ __('ai.offices.audit_ops_history_sub') }}</p>
            </div>
            <a href="{{ route('cooca-ai.history') }}" class="text-xs font-semibold text-black/70 dark:text-white/70 hover:underline">
                {{ __('ai.offices.see_all_history') }} &rarr;
            </a>
        </div>

        <div class="space-y-2.5">
            @forelse($recentHistories as $hist)
                <div class="p-3.5 rounded-2xl bg-black/[0.01] dark:bg-white/[0.01] border border-black/5 dark:border-white/5 flex items-start justify-between gap-3 text-xs">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            @if(is_array($hist->participating_agents) && count($hist->participating_agents) > 0)
                                 @foreach(array_slice($hist->participating_agents, 0, 2) as $ag)
                                     <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80 uppercase">{{ $ag }}</span>
                                 @endforeach
                            @else
                                 <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80 uppercase">{{ $hist->agent }}</span>
                            @endif
                            <span class="font-bold text-black dark:text-white">{{ $hist->session_title ?? $hist->action }}</span>
                        </div>
                        <p class="text-black/60 dark:text-white/60 line-clamp-1">{{ $hist->executive_summary ?? $hist->summary }}</p>
                    </div>
                    <span class="text-[11px] text-black/40 dark:text-white/40 font-mono shrink-0">{{ $hist->recorded_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="p-6 rounded-2xl bg-black/[0.01] dark:bg-white/[0.01] border border-black/5 dark:border-white/5 text-center text-xs text-black/50 dark:text-white/50">
                    {{ __('ai.offices.no_history_ops') }}
                </div>
            @endforelse
        </div>
    </div>

    <!-- Shared Consultation Modal -->
    @include('app.ai.partials.consultation_modal')

</div>

<!-- Shared Alpine Script -->
@include('app.ai.partials.office_script')
@endsection
