@extends('layouts.app', [
    'title' => __('operations.projects_title') . ' - Cooca',
    'headerTitle' => __('operations.projects_title'),
    'headerSubtitle' => __('operations.projects_subtitle'),
])

@section('content')
<div class="space-y-6 pb-24 lg:pb-16" x-data="{
    viewMode: 'kanban', // 'kanban' or 'table'
    showNewModal: false,
    selectedStatus: '{{ $filters['status'] ?? 'all' }}',
    filterPriority: '{{ $filters['priority'] ?? 'all' }}',
    filterClient: '{{ $filters['client_id'] ?? '' }}',
    searchQuery: '{{ $filters['search'] ?? '' }}',

    // New Project Form Data
    newForm: {
        name: '',
        code: '',
        description: '',
        client_id: '',
        priority: 'medium',
        status: 'planning',
        budget: '',
        start_date: '',
        due_date: '',
        color: '#007AFF',
        stages: ['Perencanaan', 'Pengembangan', 'Review & Testing', 'Peluncuran'],
        member_ids: [],
    },

    addStage() {
        this.newForm.stages.push('');
    },
    removeStage(idx) {
        this.newForm.stages.splice(idx, 1);
    },

    async updateProjectStatus(projectId, newStatus) {
        try {
            const res = await fetch(`/operations/projects/${projectId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status: newStatus }),
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            }
        } catch (e) {
            console.error(e);
        }
    }
}">

    {{-- Top Bento Metrics Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Proyek Aktif --}}
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 lg:p-5 border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[12px] font-medium text-[#8E8E93] uppercase tracking-wider">{{ __('operations.active_projects') }}</p>
                <h3 class="text-2xl lg:text-3xl font-bold text-black dark:text-white mt-1">{{ number_format($metrics['total_active']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                <i data-lucide="folder-kanban" class="w-6 h-6"></i>
            </div>
        </div>

        {{-- Selesai --}}
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 lg:p-5 border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[12px] font-medium text-[#8E8E93] uppercase tracking-wider">{{ __('operations.completed_projects') }}</p>
                <h3 class="text-2xl lg:text-3xl font-bold text-[#34C759] mt-1">{{ number_format($metrics['total_completed']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
        </div>

        {{-- Lewat Tenggat --}}
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 lg:p-5 border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[12px] font-medium text-[#8E8E93] uppercase tracking-wider">{{ __('operations.overdue_projects') }}</p>
                <h3 class="text-2xl lg:text-3xl font-bold {{ $metrics['overdue'] > 0 ? 'text-[#FF3B30]' : 'text-black dark:text-white' }} mt-1">
                    {{ number_format($metrics['overdue']) }}
                </h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
        </div>

        {{-- Total Anggaran --}}
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 lg:p-5 border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[12px] font-medium text-[#8E8E93] uppercase tracking-wider">{{ __('operations.total_budget') }}</p>
                <h3 class="text-xl lg:text-2xl font-bold text-black dark:text-white mt-1">
                    Rp {{ number_format($metrics['total_budget'], 0, ',', '.') }}
                </h3>
                <p class="text-[11px] text-[#8E8E93] mt-0.5">Realisasi: Rp {{ number_format($metrics['total_spent'], 0, ',', '.') }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center shrink-0">
                <i data-lucide="pie-chart" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    {{-- Filter, Search & View Controls --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border border-black/5 dark:border-white/10 shadow-sm space-y-3">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            {{-- Search & Filters --}}
            <form method="GET" action="{{ route('operations.projects.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
                <div class="relative flex-1 min-w-[200px]">
                    <i data-lucide="search" class="w-4 h-4 text-[#8E8E93] absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="{{ __('operations.project_name') }} / kode..."
                        class="w-full pl-9 pr-3 py-2 text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <select name="priority" onchange="this.form.submit()"
                    class="py-2 px-3 text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="all">{{ __('operations.filter_all') }} Prioritas</option>
                    <option value="low" {{ ($filters['priority'] ?? '') === 'low' ? 'selected' : '' }}>{{ __('operations.priority_low') }}</option>
                    <option value="medium" {{ ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' }}>{{ __('operations.priority_medium') }}</option>
                    <option value="high" {{ ($filters['priority'] ?? '') === 'high' ? 'selected' : '' }}>{{ __('operations.priority_high') }}</option>
                    <option value="urgent" {{ ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' }}>{{ __('operations.priority_urgent') }}</option>
                </select>

                <select name="client_id" onchange="this.form.submit()"
                    class="py-2 px-3 text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">{{ __('operations.filter_all') }} Klien</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ ($filters['client_id'] ?? '') === $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                    @endforeach
                </select>

                <noscript><button type="submit" class="px-3 py-2 bg-black/5 rounded-xl text-xs">Cari</button></noscript>
            </form>

            {{-- View Toggle & New Project Button --}}
            <div class="flex items-center gap-2 justify-end">
                <div class="bg-black/[0.04] dark:bg-white/[0.06] p-1 rounded-xl flex items-center gap-1">
                    <button type="button" @click="viewMode = 'kanban'"
                        :class="viewMode === 'kanban' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-black dark:text-white' : 'text-[#8E8E93] hover:text-black dark:hover:text-white'"
                        class="px-2.5 py-1.5 rounded-lg text-xs flex items-center gap-1.5 transition-all">
                        <i data-lucide="kanban" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">{{ __('operations.view_kanban') }}</span>
                    </button>
                    <button type="button" @click="viewMode = 'table'"
                        :class="viewMode === 'table' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-black dark:text-white' : 'text-[#8E8E93] hover:text-black dark:hover:text-white'"
                        class="px-2.5 py-1.5 rounded-lg text-xs flex items-center gap-1.5 transition-all">
                        <i data-lucide="list" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">{{ __('operations.view_table') }}</span>
                    </button>
                </div>

                @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('projects.manage'))
                <button type="button" @click="showNewModal = true"
                    class="px-3.5 py-2 bg-[#007AFF] hover:bg-[#007AFF]/90 text-white rounded-xl text-xs font-semibold shadow-sm shadow-[#007AFF]/25 flex items-center gap-1.5 active:scale-[0.98] transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('operations.new_project') }}</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- KANBAN BOARD VIEW --}}
    <div x-show="viewMode === 'kanban'" x-transition class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @php
                $columns = [
                    'planning' => ['title' => __('operations.status_planning'), 'color' => 'border-t-[#007AFF]', 'bg' => 'bg-[#007AFF]/10 text-[#007AFF]'],
                    'in_progress' => ['title' => __('operations.status_in_progress'), 'color' => 'border-t-[#FF9500]', 'bg' => 'bg-[#FF9500]/10 text-[#FF9500]'],
                    'review' => ['title' => __('operations.status_review'), 'color' => 'border-t-[#AF52DE]', 'bg' => 'bg-[#AF52DE]/10 text-[#AF52DE]'],
                    'completed' => ['title' => __('operations.status_completed'), 'color' => 'border-t-[#34C759]', 'bg' => 'bg-[#34C759]/10 text-[#34C759]'],
                ];
            @endphp

            @foreach($columns as $statusKey => $col)
            @php
                $columnProjects = $projects->filter(fn($p) => $p->status === $statusKey);
            @endphp
            <div class="bg-[#F2F2F7]/70 dark:bg-[#1C1C1E]/60 rounded-2xl p-3 border-t-4 {{ $col['color'] }} border border-black/5 dark:border-white/10 flex flex-col min-h-[500px]">
                <div class="flex items-center justify-between pb-3 px-1">
                    <div class="flex items-center gap-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-black dark:text-white">{{ $col['title'] }}</h4>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $col['bg'] }}">
                            {{ $columnProjects->count() }}
                        </span>
                    </div>
                </div>

                {{-- Card list in column --}}
                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($columnProjects as $project)
                    <div class="bg-white dark:bg-[#2C2C2E] rounded-xl p-4 border border-black/5 dark:border-white/10 shadow-sm hover:shadow-md transition-all group">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <span class="text-[11px] font-bold text-[#8E8E93] tracking-wider">{{ $project->code }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $project->priority === 'urgent' ? 'bg-[#FF3B30]/10 text-[#FF3B30]' : ($project->priority === 'high' ? 'bg-[#FF9500]/10 text-[#FF9500]' : 'bg-black/5 text-[#8E8E93]') }}">
                                {{ __('operations.priority_' . $project->priority) }}
                            </span>
                        </div>

                        <a href="{{ route('operations.projects.show', $project) }}" class="block font-bold text-[14px] text-black dark:text-white group-hover:text-[#007AFF] transition-colors line-clamp-2">
                            {{ $project->name }}
                        </a>

                        @if($project->description)
                            <p class="text-xs text-[#8E8E93] mt-1 line-clamp-2">{{ $project->description }}</p>
                        @endif

                        {{-- Progress Bar --}}
                        <div class="mt-3">
                            <div class="flex items-center justify-between text-[11px] mb-1">
                                <span class="text-[#8E8E93]">Tugas: {{ $project->completed_tasks_count }}/{{ $project->tasks_count }}</span>
                                <span class="font-bold text-black dark:text-white">{{ $project->progress_percentage }}%</span>
                            </div>
                            <div class="w-full bg-black/5 dark:bg-white/10 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-[#007AFF] h-full rounded-full transition-all duration-300" style="width: {{ $project->progress_percentage }}%"></div>
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="mt-3 pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-[#8E8E93]">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                <span class="{{ $project->isOverdue() ? 'text-[#FF3B30] font-semibold' : '' }}">
                                    {{ $project->due_date ? $project->due_date->format('d M Y') : '-' }}
                                </span>
                            </div>

                            {{-- Quick Status Move Dropdown --}}
                            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('projects.manage'))
                            <div class="relative" x-data="{ openMenu: false }">
                                <button type="button" @click="openMenu = !openMenu" class="p-1 hover:bg-black/5 dark:hover:bg-white/10 rounded-md">
                                    <i data-lucide="more-horizontal" class="w-3.5 h-3.5"></i>
                                </button>
                                <div x-show="openMenu" @click.outside="openMenu = false" x-transition
                                    class="absolute right-0 bottom-full mb-1 w-36 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-xl shadow-lg py-1 z-20 text-xs">
                                    <div class="px-2 py-1 text-[10px] uppercase font-bold text-[#8E8E93]">Pindah Status:</div>
                                    @foreach(['planning', 'in_progress', 'review', 'completed'] as $st)
                                        @if($st !== $project->status)
                                        <button type="button" @click="updateProjectStatus('{{ $project->id }}', '{{ $st }}')"
                                            class="w-full text-left px-3 py-1.5 hover:bg-black/5 dark:hover:bg-white/10">
                                            {{ __('operations.status_' . $st) }}
                                        </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-xs text-[#8E8E93]">
                        Tidak ada proyek di tahapan ini.
                    </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- TABLE VIEW --}}
    <div x-show="viewMode === 'table'" x-transition class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/5 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] text-[#8E8E93] uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 font-semibold">{{ __('operations.project_code') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('operations.project_name') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('operations.project_client') }}</th>
                        <th class="py-3.5 px-4 font-semibold">Status</th>
                        <th class="py-3.5 px-4 font-semibold">Prioritas</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('operations.progress') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('operations.due_date') }}</th>
                        <th class="py-3.5 px-4 font-semibold text-right">{{ __('operations.budget') }}</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse($projects as $project)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                        <td class="py-3.5 px-4 font-bold text-[#8E8E93]">{{ $project->code }}</td>
                        <td class="py-3.5 px-4 font-semibold text-black dark:text-white">
                            <a href="{{ route('operations.projects.show', $project) }}" class="hover:text-[#007AFF] transition-colors">
                                {{ $project->name }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4 text-[#8E8E93]">{{ $project->client?->name ?? '-' }}</td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $project->status === 'completed' ? 'bg-[#34C759]/10 text-[#34C759]' : ($project->status === 'in_progress' ? 'bg-[#FF9500]/10 text-[#FF9500]' : 'bg-[#007AFF]/10 text-[#007AFF]') }}">
                                {{ __('operations.status_' . $project->status) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $project->priority === 'urgent' ? 'bg-[#FF3B30]/10 text-[#FF3B30]' : ($project->priority === 'high' ? 'bg-[#FF9500]/10 text-[#FF9500]' : 'bg-black/5 text-[#8E8E93]') }}">
                                {{ __('operations.priority_' . $project->priority) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-16 bg-black/5 dark:bg-white/10 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-[#007AFF] h-full rounded-full" style="width: {{ $project->progress_percentage }}%"></div>
                                </div>
                                <span class="font-bold text-[11px]">{{ $project->progress_percentage }}%</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 {{ $project->isOverdue() ? 'text-[#FF3B30] font-bold' : 'text-[#8E8E93]' }}">
                            {{ $project->due_date ? $project->due_date->format('d/m/Y') : '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-medium text-black dark:text-white">
                            Rp {{ number_format($project->budget, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('operations.projects.show', $project) }}" class="p-1.5 hover:bg-black/5 dark:hover:bg-white/10 rounded-lg inline-flex items-center text-[#007AFF]">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-xs text-[#8E8E93]">
                            Belum ada proyek yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-black/5 dark:border-white/5">
            {{ $projects->links() }}
        </div>
    </div>

    {{-- MODAL TAMBAH PROYEK BARU --}}
    <div x-show="showNewModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;">
        <div @click.outside="showNewModal = false" class="bg-white dark:bg-[#1C1C1E] rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-black/10 dark:border-white/10 overflow-hidden flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between pb-4 border-b border-black/5 dark:border-white/10">
                <h3 class="text-lg font-bold text-black dark:text-white">{{ __('operations.new_project') }}</h3>
                <button type="button" @click="showNewModal = false" class="p-1 rounded-full hover:bg-black/5 text-[#8E8E93]">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('operations.projects.store') }}" method="POST" class="space-y-4 pt-4 overflow-y-auto flex-1 pr-1">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.project_name') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Misal: Renovasi Ritel Cabang 2"
                        class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.project_client') }}</label>
                        <select name="client_id" class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                            <option value="">{{ __('operations.select_client') }}</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Prioritas</label>
                        <select name="priority" class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                            <option value="low">{{ __('operations.priority_low') }}</option>
                            <option value="medium" selected>{{ __('operations.priority_medium') }}</option>
                            <option value="high">{{ __('operations.priority_high') }}</option>
                            <option value="urgent">{{ __('operations.priority_urgent') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.budget') }}</label>
                        <input type="number" name="budget" min="0" placeholder="0"
                            class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.start_date') }}</label>
                        <input type="date" name="start_date"
                            class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.due_date') }}</label>
                        <input type="date" name="due_date"
                            class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.project_description') }}</label>
                    <textarea name="description" rows="2" placeholder="Tujuan, target output, dan catatan proyek..."
                        class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none"></textarea>
                </div>

                {{-- Milestone Stages --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold text-black dark:text-white">{{ __('operations.stages') }}</label>
                        <button type="button" @click="addStage()" class="text-xs text-[#007AFF] font-semibold hover:underline">+ Tambah Tahapan</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(stage, idx) in newForm.stages" :key="idx">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-bold text-[#8E8E93] w-5" x-text="(idx + 1) + '.'"></span>
                                <input type="text" :name="'stages[' + idx + ']'" x-model="newForm.stages[idx]" placeholder="Nama tahapan..."
                                    class="flex-1 px-3 py-1.5 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                                <button type="button" @click="removeStage(idx)" class="p-1 hover:text-red-500 text-[#8E8E93]">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-4 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2">
                    <button type="button" @click="showNewModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#8E8E93] hover:bg-black/5">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#007AFF] hover:bg-[#007AFF]/90 text-white rounded-xl text-xs font-semibold shadow-sm shadow-[#007AFF]/25">
                        Simpan Proyek
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
