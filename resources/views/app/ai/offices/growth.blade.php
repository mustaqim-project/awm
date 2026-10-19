@extends('layouts.app', ['title' => __('ai.offices.growth_title')])

@section('content')
<div class="space-y-6 w-full" x-data="aiOfficeBase()">

    <!-- Unified Apple HIG Navigation Hub -->
    @include('app.ai.partials.office_navigation', ['activeOffice' => 'growth', 'pendingCount' => $officeStats['pending_approvals_count'] ?? 0])

    <!-- INTERACTIVE VIRTUAL OFFICE FLOOR (Watch AI Agents Work Live) -->
    @include('app.ai.partials.virtual_office_canvas', [
        'mode' => 'growth',
        'agentStatuses' => $officeStats['agent_statuses'] ?? [],
        'recentTasks' => $recentTasks,
        'pendingProposals' => $pendingProposals,
        'recentHistories' => $recentHistories,
    ])

    <!-- AI CMO & SALES DIRECTOR LEADERSHIP SUITE (TOP BENTO) -->
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-500/20 shrink-0">
                    <i data-lucide="sparkles" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-black dark:text-white">AI CMO & Sales Director</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400">{{ __('ai.offices.growth_active') }}</span>
                    </div>
                    <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Marketing, Sales, Content, Social Media & Customer Retention</p>
                </div>
            </div>

            <div class="text-right">
                <div class="text-[11px] text-black/40 dark:text-white/40">{{ __('ai.offices.focus_commercial') }}</div>
                <div class="text-xs font-semibold text-purple-600 dark:text-purple-400 flex items-center justify-end gap-1.5 mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    <span>{{ __('ai.offices.acquisition_reactivation') }}</span>
                </div>
            </div>
        </div>

        <!-- Growth Leadership Directives -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 border-t border-black/5 dark:border-white/5 pt-4">
            <div class="p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                <div class="text-[11px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="target" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.offices.campaign_strategy') }}</span>
                </div>
                <p class="text-xs text-black/70 dark:text-white/70">{{ __('ai.offices.campaign_strategy_desc') }}</p>
            </div>

            <div class="p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                <div class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.offices.sales_performance') }}</span>
                </div>
                <p class="text-xs text-black/70 dark:text-white/70">{{ __('ai.offices.sales_perf_desc') }}</p>
            </div>

            <div class="p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.offices.customer_retention') }}</span>
                </div>
                <p class="text-xs text-black/70 dark:text-white/70">{{ __('ai.offices.customer_retention_desc') }}</p>
            </div>
        </div>
    </div>

    <!-- GROWTH & CUSTOMER METRICS STRIP -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('ai.offices.total_registered_customers') }}</div>
            <div class="text-lg sm:text-xl font-bold text-black dark:text-white tabular-nums tracking-tight mt-1">
                {{ __('ai.offices.unit_contacts', ['count' => number_format($customerSummary['total_customers'] ?? $growthMetrics['total_customers'] ?? 0)]) }}
            </div>
            <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">{{ __('ai.offices.total_reg_sub') }}</div>
        </div>

        <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('ai.offices.dormant_customers') }}</div>
            <div class="text-lg sm:text-xl font-bold text-amber-600 dark:text-amber-400 tabular-nums tracking-tight mt-1">
                {{ __('ai.offices.unit_contacts', ['count' => number_format($customerSummary['dormant_customers'] ?? 0)]) }}
            </div>
            <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">{{ __('ai.offices.dormant_sub') }}</div>
        </div>

        <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('ai.offices.vip_customers') }}</div>
            <div class="text-lg sm:text-xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums tracking-tight mt-1">
                {{ __('ai.offices.unit_contacts', ['count' => number_format($customerSummary['vip_customers'] ?? 0)]) }}
            </div>
            <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">{{ __('ai.offices.vip_sub') }}</div>
        </div>

        <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('ai.offices.social_content_queue') }}</div>
            <div class="text-lg sm:text-xl font-bold text-purple-600 dark:text-purple-400 tabular-nums tracking-tight mt-1">
                {{ __('ai.offices.unit_scripts', ['count' => $growthMetrics['scheduled_posts'] ?? 0]) }}
            </div>
            <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">{{ __('ai.offices.social_content_sub') }}</div>
        </div>
    </div>

    <!-- SQUAD WORKSTATIONS (5 AGENTS IN GROWTH OFFICE) -->
    <div class="space-y-4">
        <div>
            <h3 class="text-sm font-bold text-black dark:text-white">{{ __('ai.offices.workstations_growth_commercial_title') }}</h3>
            <p class="text-xs text-black/50 dark:text-white/50">{{ __('ai.offices.workstations_growth_commercial_sub') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3">
            
            <!-- Workstation 1: Marketing Agent -->
            @php $mktgAgent = $officeStats['agent_statuses']['marketing'] ?? null; @endphp
            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between space-y-3">
                <div class="space-y-2">
                    <div class="flex items-start justify-between">
                        <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold shrink-0">
                            <i data-lucide="target" class="w-4 h-4"></i>
                        </div>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase {{ $mktgAgent['status'] === 'WORKING' ? 'bg-blue-500/10 text-blue-600' : 'bg-emerald-500/10 text-emerald-600' }}">
                            {{ $mktgAgent['status_label'] ?? __('ai.offices.available') }}
                        </span>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-black dark:text-white">Marketing Agent</h4>
                        <p class="text-[11px] text-black/60 dark:text-white/60 mt-1 line-clamp-3">{{ __('ai.offices.agent_mktg_desc') }}</p>
                    </div>

                    <div class="p-2 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 text-[10px] text-black/70 dark:text-white/70">
                        <div class="font-semibold text-black dark:text-white truncate">{{ $mktgAgent['current_work'] ?? __('ai.offices.standby') }}</div>
                    </div>
                </div>

                <div class="text-[10px] text-black/40 dark:text-white/40 pt-2 border-t border-black/5 dark:border-white/5">
                    {{ $mktgAgent['last_active'] ?? '-' }}
                </div>
            </div>

            <!-- Workstation 2: Content Agent -->
            @php $cntAgent = $officeStats['agent_statuses']['content'] ?? null; @endphp
            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between space-y-3">
                <div class="space-y-2">
                    <div class="flex items-start justify-between">
                        <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold shrink-0">
                            <i data-lucide="pen-tool" class="w-4 h-4"></i>
                        </div>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase {{ $cntAgent['status'] === 'WORKING' ? 'bg-blue-500/10 text-blue-600' : 'bg-emerald-500/10 text-emerald-600' }}">
                            {{ $cntAgent['status_label'] ?? __('ai.offices.available') }}
                        </span>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-black dark:text-white">Content Agent</h4>
                        <p class="text-[11px] text-black/60 dark:text-white/60 mt-1 line-clamp-3">{{ __('ai.offices.agent_cnt_desc') }}</p>
                    </div>

                    <div class="p-2 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 text-[10px] text-black/70 dark:text-white/70">
                        <div class="font-semibold text-black dark:text-white truncate">{{ $cntAgent['current_work'] ?? __('ai.offices.standby') }}</div>
                    </div>
                </div>

                <div class="text-[10px] text-black/40 dark:text-white/40 pt-2 border-t border-black/5 dark:border-white/5">
                    {{ $cntAgent['last_active'] ?? '-' }}
                </div>
            </div>

            <!-- Workstation 3: Social Media Agent -->
            @php $socAgent = $officeStats['agent_statuses']['social_media'] ?? null; @endphp
            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between space-y-3">
                <div class="space-y-2">
                    <div class="flex items-start justify-between">
                        <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold shrink-0">
                            <i data-lucide="share-2" class="w-4 h-4"></i>
                        </div>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase {{ $socAgent['status'] === 'WORKING' ? 'bg-blue-500/10 text-blue-600' : 'bg-emerald-500/10 text-emerald-600' }}">
                            {{ $socAgent['status_label'] ?? __('ai.offices.available') }}
                        </span>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-black dark:text-white">Social Media Agent</h4>
                        <p class="text-[11px] text-black/60 dark:text-white/60 mt-1 line-clamp-3">{{ __('ai.offices.agent_soc_desc') }}</p>
                    </div>

                    <div class="p-2 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 text-[10px] text-black/70 dark:text-white/70">
                        <div class="font-semibold text-black dark:text-white truncate">{{ $socAgent['current_work'] ?? __('ai.offices.standby') }}</div>
                    </div>
                </div>

                <div class="text-[10px] text-black/40 dark:text-white/40 pt-2 border-t border-black/5 dark:border-white/5">
                    {{ $socAgent['last_active'] ?? '-' }}
                </div>
            </div>

            <!-- Workstation 4: Sales Agent -->
            @php $slsAgent = $officeStats['agent_statuses']['sales'] ?? null; @endphp
            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between space-y-3">
                <div class="space-y-2">
                    <div class="flex items-start justify-between">
                        <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold shrink-0">
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                        </div>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase {{ $slsAgent['status'] === 'WORKING' ? 'bg-blue-500/10 text-blue-600' : 'bg-emerald-500/10 text-emerald-600' }}">
                            {{ $slsAgent['status_label'] ?? __('ai.offices.available') }}
                        </span>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-black dark:text-white">Sales Agent</h4>
                        <p class="text-[11px] text-black/60 dark:text-white/60 mt-1 line-clamp-3">{{ __('ai.offices.agent_sls_desc') }}</p>
                    </div>

                    <div class="p-2 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 text-[10px] text-black/70 dark:text-white/70">
                        <div class="font-semibold text-black dark:text-white truncate">{{ $slsAgent['current_work'] ?? __('ai.offices.standby') }}</div>
                    </div>
                </div>

                <div class="text-[10px] text-black/40 dark:text-white/40 pt-2 border-t border-black/5 dark:border-white/5">
                    {{ $slsAgent['last_active'] ?? '-' }}
                </div>
            </div>

            <!-- Workstation 5: Customer Agent -->
            @php $cstAgent = $officeStats['agent_statuses']['customer'] ?? null; @endphp
            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between space-y-3">
                <div class="space-y-2">
                    <div class="flex items-start justify-between">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shrink-0">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                        </div>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase {{ $cstAgent['status'] === 'WORKING' ? 'bg-blue-500/10 text-blue-600' : 'bg-emerald-500/10 text-emerald-600' }}">
                            {{ $cstAgent['status_label'] ?? __('ai.offices.available') }}
                        </span>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-black dark:text-white">Customer Agent</h4>
                        <p class="text-[11px] text-black/60 dark:text-white/60 mt-1 line-clamp-3">{{ __('ai.offices.agent_cst_desc') }}</p>
                    </div>

                    <div class="p-2 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 text-[10px] text-black/70 dark:text-white/70">
                        <div class="font-semibold text-black dark:text-white truncate">{{ $cstAgent['current_work'] ?? __('ai.offices.standby') }}</div>
                    </div>
                </div>

                <div class="text-[10px] text-black/40 dark:text-white/40 pt-2 border-t border-black/5 dark:border-white/5">
                    {{ $cstAgent['last_active'] ?? '-' }}
                </div>
            </div>

        </div>
    </div>

    <!-- TOP PERFORMING PRODUCTS BENTO -->
    @if(!empty($topProducts['top_selling']))
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white">{{ __('ai.offices.top_selling_title') }}</h3>
                <p class="text-xs text-black/50 dark:text-white/50">{{ __('ai.offices.top_selling_sub') }}</p>
            </div>
            <button type="button" @click="quickAsk('{{ __('ai.offices.prompt_promo_content') }}')" class="px-3 py-1.5 rounded-xl bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-xs font-semibold text-black dark:text-white transition">
                {{ __('ai.offices.btn_create_promo_content') }} &rarr;
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            @foreach(array_slice($topProducts['top_selling'], 0, 4) as $prod)
                <div class="p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                    <div class="text-xs font-bold text-black dark:text-white truncate">{{ $prod['name'] ?? __('ai.offices.product_fallback') }}</div>
                    <div class="text-[11px] text-black/50 dark:text-white/50">{!! __('ai.offices.sold_units', ['quantity' => '<span class="font-bold text-black dark:text-white tabular-nums">' . ($prod['quantity_sold'] ?? 0) . '</span>']) !!}</div>
                    <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 tabular-nums">
                        Rp {{ number_format($prod['revenue'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- CURRENT GROWTH WORK IN PROGRESS -->
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white">{{ __('ai.offices.running_growth_tasks_title') }}</h3>
                <p class="text-xs text-black/50 dark:text-white/50">{{ __('ai.offices.running_growth_tasks_sub') }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-md text-[11px] font-mono font-semibold bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">
                {{ __('ai.offices.tasks_recorded', ['count' => $recentTasks->count()]) }}
            </span>
        </div>

        <div class="space-y-2">
            @forelse($recentTasks as $task)
                <div class="p-3.5 rounded-2xl bg-black/[0.01] dark:bg-white/[0.01] border border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
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
                    {{ __('ai.offices.no_active_growth_tasks') }}
                </div>
            @endforelse
        </div>
    </div>

    <!-- GROWTH PROPOSALS REQUIRING OWNER DECISION (PROMOTIONS & POSTS) -->
    @if($pendingProposals->count() > 0)
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-amber-500/20 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                    <span>{{ __('ai.offices.pending_growth_proposals_title') }}</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">{{ __('ai.offices.pending_growth_proposals_sub') }}</p>
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

    <!-- RECENT GROWTH WORK HISTORY -->
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white">{{ __('ai.offices.audit_growth_history_title') }}</h3>
                <p class="text-xs text-black/50 dark:text-white/50">{{ __('ai.offices.audit_growth_history_sub') }}</p>
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
                    {{ __('ai.offices.no_history_growth') }}
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
