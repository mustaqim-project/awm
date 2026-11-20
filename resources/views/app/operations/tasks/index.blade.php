@extends('layouts.app', [
    'title' => __('operations.tasks_title') . ' - Cooca',
    'headerTitle' => __('operations.tasks_title'),
    'headerSubtitle' => __('operations.tasks_subtitle'),
])

@section('content')
<div class="space-y-6 pb-24 lg:pb-16" x-data="{
    currentTab: '{{ $filters['tab'] ?? 'all' }}',
    quickTitle: '',
    quickDueDate: '',
    quickSyncCalendar: true,
    selectedProjectId: '{{ $filters['project_id'] ?? 'all' }}',
    selectedPriority: 'medium',
    activeTask: null,
    drawerOpen: false,
    newSubtaskTitle: '',
    logMinutes: 30,
    logNotes: '',
    isLoggingTime: false,

    async submitQuickTask() {
        if (!this.quickTitle.trim()) return;
        try {
            const res = await fetch('{{ route('operations.tasks.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    title: this.quickTitle,
                    project_id: this.selectedProjectId !== 'all' ? this.selectedProjectId : null,
                    priority: this.selectedPriority,
                    due_date: this.quickDueDate ? this.quickDueDate : null,
                    sync_to_calendar: this.quickSyncCalendar,
                    status: 'pending',
                }),
            });
            const data = await res.json();
            if (data.success) {
                this.quickTitle = '';
                this.quickDueDate = '';
                window.location.reload();
            }
        } catch (e) {
            console.error(e);
        }
    },

    async toggleTask(taskId) {
        try {
            const res = await fetch(`/operations/tasks/${taskId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            }
        } catch (e) {
            console.error(e);
        }
    },

    async openTaskDrawer(task) {
        try {
            const res = await fetch(`/operations/tasks/${task.id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            });
            const data = await res.json();
            if (data.success) {
                this.activeTask = data.task;
                this.drawerOpen = true;
            }
        } catch (e) {
            console.error(e);
        }
    },

    async addSubtask() {
        if (!this.newSubtaskTitle.trim() || !this.activeTask) return;
        try {
            const res = await fetch(`/operations/tasks/${this.activeTask.id}/checklists`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ title: this.newSubtaskTitle }),
            });
            const data = await res.json();
            if (data.success) {
                this.activeTask.checklists.push(data.checklist);
                this.newSubtaskTitle = '';
            }
        } catch (e) {
            console.error(e);
        }
    },

    async toggleSubtask(checklistId) {
        try {
            const res = await fetch(`/operations/tasks/checklists/${checklistId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (data.success && this.activeTask) {
                const item = this.activeTask.checklists.find(c => c.id === checklistId);
                if (item) {
                    item.is_completed = data.checklist.is_completed;
                }
            }
        } catch (e) {
            console.error(e);
        }
    },

    async submitTimeLog() {
        if (!this.activeTask || !this.logMinutes) return;
        try {
            const res = await fetch(`/operations/tasks/${this.activeTask.id}/time-logs`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    duration_minutes: this.logMinutes,
                    notes: this.logNotes,
                    is_billable: false,
                }),
            });
            const data = await res.json();
            if (data.success) {
                if (!this.activeTask.time_logs) this.activeTask.time_logs = [];
                this.activeTask.time_logs.unshift(data.time_log);
                this.activeTask.actual_hours = data.actual_hours;
                this.logNotes = '';
                this.isLoggingTime = false;
            }
        } catch (e) {
            console.error(e);
        }
    }
}">

    {{-- Top Metrics Bento Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Belum Selesai --}}
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 lg:p-5 border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[12px] font-medium text-[#8E8E93] uppercase tracking-wider">{{ __('operations.filter_pending') }}</p>
                <h3 class="text-2xl lg:text-3xl font-bold text-black dark:text-white mt-1">{{ number_format($metrics['total_pending']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                <i data-lucide="check-square" class="w-6 h-6"></i>
            </div>
        </div>

        {{-- Hari Ini --}}
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 lg:p-5 border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[12px] font-medium text-[#8E8E93] uppercase tracking-wider">{{ __('operations.filter_today') }}</p>
                <h3 class="text-2xl lg:text-3xl font-bold text-[#FF9500] mt-1">{{ number_format($metrics['due_today']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                <i data-lucide="calendar" class="w-6 h-6"></i>
            </div>
        </div>

        {{-- Terlambat --}}
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 lg:p-5 border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[12px] font-medium text-[#8E8E93] uppercase tracking-wider">{{ __('operations.overdue_projects') }}</p>
                <h3 class="text-2xl lg:text-3xl font-bold {{ $metrics['overdue'] > 0 ? 'text-[#FF3B30]' : 'text-black dark:text-white' }} mt-1">
                    {{ number_format($metrics['overdue']) }}
                </h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center shrink-0">
                <i data-lucide="alert-circle" class="w-6 h-6"></i>
            </div>
        </div>

        {{-- Selesai --}}
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 lg:p-5 border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[12px] font-medium text-[#8E8E93] uppercase tracking-wider">{{ __('operations.filter_completed') }}</p>
                <h3 class="text-2xl lg:text-3xl font-bold text-[#34C759] mt-1">{{ number_format($metrics['total_completed']) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                <i data-lucide="check-check" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    {{-- Quick Inline Task Input Bar --}}
    @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('tasks.manage'))
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border border-black/5 dark:border-white/10 shadow-sm">
        <form @submit.prevent="submitQuickTask()" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1">
                <i data-lucide="plus-circle" class="w-5 h-5 text-[#007AFF] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" x-model="quickTitle" placeholder="{{ __('operations.quick_task_placeholder') }}"
                    class="w-full pl-11 pr-4 py-3 text-sm bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none text-black dark:text-white font-medium">
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select x-model="selectedProjectId"
                    class="py-3 px-3 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                    <option value="all">Tanpa Proyek</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>

                <select x-model="selectedPriority"
                    class="py-3 px-3 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                    <option value="low">{{ __('operations.priority_low') }}</option>
                    <option value="medium" selected>{{ __('operations.priority_medium') }}</option>
                    <option value="high">{{ __('operations.priority_high') }}</option>
                    <option value="urgent">{{ __('operations.priority_urgent') }}</option>
                </select>

                <input type="datetime-local" x-model="quickDueDate" title="Tenggat Waktu"
                    class="py-2.5 px-3 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none text-[#8E8E93]">

                <button type="button" @click="quickSyncCalendar = !quickSyncCalendar"
                    :class="quickSyncCalendar ? 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/25 font-bold' : 'bg-black/[0.03] text-[#8E8E93] border-black/5'"
                    class="px-2.5 py-3 text-xs border rounded-xl flex items-center gap-1 transition-all" title="Sinkronkan ke Google Calendar">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Google Sync</span>
                </button>

                <button type="submit"
                    class="px-5 py-3 bg-[#007AFF] hover:bg-[#007AFF]/90 text-white rounded-xl text-xs font-bold shadow-sm shadow-[#007AFF]/25 shrink-0 flex items-center gap-1.5 active:scale-95 transition-all">
                    <span>{{ __('operations.add_task') }}</span>
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Filter Chips & Search --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        {{-- Filter Chips --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
            @php
                $tabs = [
                    'all' => __('operations.filter_all'),
                    'today' => __('operations.filter_today'),
                    'upcoming' => __('operations.filter_upcoming'),
                    'high_priority' => __('operations.filter_high_priority'),
                    'my_tasks' => __('operations.filter_my_tasks'),
                    'completed' => __('operations.filter_completed'),
                ];
            @endphp
            @foreach($tabs as $tabKey => $tabLabel)
            <a href="{{ route('operations.tasks.index', array_merge($filters, ['tab' => $tabKey])) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all {{ ($filters['tab'] ?? 'all') === $tabKey ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25' : 'bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 text-[#8E8E93] hover:text-black dark:hover:text-white' }}">
                {{ $tabLabel }}
            </a>
            @endforeach
        </div>

        {{-- Search Input --}}
        <form method="GET" action="{{ route('operations.tasks.index') }}" class="relative w-full sm:w-64">
            <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'all' }}">
            <i data-lucide="search" class="w-4 h-4 text-[#8E8E93] absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                placeholder="Cari tugas..."
                class="w-full pl-9 pr-3 py-1.5 text-xs bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
        </form>
    </div>

    {{-- Tasks List Cards --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-5 border border-black/5 dark:border-white/10 shadow-sm">
        <div class="space-y-2">
            @forelse($tasks as $task)
            <div class="flex items-center justify-between p-3.5 rounded-2xl border border-black/5 dark:border-white/5 hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors group">
                <div class="flex items-center gap-3.5 flex-1 min-w-0">
                    {{-- Toggle Checkbox --}}
                    <button type="button" @click="toggleTask('{{ $task->id }}')"
                        class="w-5 h-5 rounded-lg border flex items-center justify-center shrink-0 transition-all {{ $task->status === 'completed' ? 'bg-[#34C759] border-[#34C759] text-white shadow-sm' : 'border-black/20 dark:border-white/20 hover:border-[#007AFF] hover:bg-[#007AFF]/5' }}">
                        @if($task->status === 'completed')
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        @endif
                    </button>

                    <div class="min-w-0 flex-1 cursor-pointer" @click="openTaskDrawer(@js($task))">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-black dark:text-white truncate {{ $task->status === 'completed' ? 'line-through text-[#8E8E93] dark:text-[#8E8E93]' : '' }}">
                                {{ $task->title }}
                            </span>
                            @if($task->project)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] shrink-0">
                                    {{ $task->project->name }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 mt-1 text-[11px] text-[#8E8E93]">
                            @if($task->checklists_count > 0)
                                <span class="flex items-center gap-1">
                                    <i data-lucide="check-square" class="w-3 h-3"></i>
                                    {{ $task->completed_checklists_count }}/{{ $task->checklists_count }}
                                </span>
                            @endif

                            @if($task->actual_hours)
                                <span class="flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    {{ $task->actual_hours }} jam
                                </span>
                            @endif

                            @if($task->assignee)
                                <span class="flex items-center gap-1">
                                    <i data-lucide="user" class="w-3 h-3"></i>
                                    {{ $task->assignee->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                        {{ $task->priority === 'urgent' ? 'bg-[#FF3B30]/10 text-[#FF3B30]' : ($task->priority === 'high' ? 'bg-[#FF9500]/10 text-[#FF9500]' : 'bg-black/5 text-[#8E8E93]') }}">
                        {{ __('operations.priority_' . $task->priority) }}
                    </span>

                    @if($task->due_date)
                        <span class="text-xs text-[#8E8E93] {{ $task->isOverdue() ? 'text-[#FF3B30] font-bold' : '' }}">
                            {{ $task->due_date->format('d M') }}
                        </span>
                    @endif

                    <button type="button" @click="openTaskDrawer(@js($task))" class="p-1 hover:bg-black/5 dark:hover:bg-white/10 rounded-lg text-[#8E8E93]">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            @empty
            <div class="py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-black/[0.03] dark:bg-white/[0.05] mx-auto flex items-center justify-center text-[#8E8E93] mb-3">
                    <i data-lucide="check-circle" class="w-8 h-8"></i>
                </div>
                <h4 class="text-sm font-bold text-black dark:text-white">{{ __('operations.no_tasks_found') }}</h4>
                <p class="text-xs text-[#8E8E93] mt-1">{{ __('operations.empty_tasks_hint') }}</p>
            </div>
            @endforelse
        </div>

        <div class="pt-4 mt-4 border-t border-black/5 dark:border-white/5">
            {{ $tasks->links() }}
        </div>
    </div>

    {{-- SLIDE-OVER TASK DETAIL DRAWER --}}
    <div x-show="drawerOpen" x-transition.opacity class="fixed inset-0 z-50 overflow-hidden bg-black/40 backdrop-blur-sm" style="display: none;">
        <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white dark:bg-[#1C1C1E] shadow-2xl border-l border-black/10 dark:border-white/10 flex flex-col"
            @click.outside="drawerOpen = false"
            x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

            {{-- Drawer Header --}}
            <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button type="button" @click="activeTask && toggleTask(activeTask.id)"
                        class="w-6 h-6 rounded-lg border flex items-center justify-center transition-all"
                        :class="activeTask && activeTask.status === 'completed' ? 'bg-[#34C759] border-[#34C759] text-white' : 'border-black/20 dark:border-white/20'">
                        <i data-lucide="check" class="w-4 h-4" x-show="activeTask && activeTask.status === 'completed'"></i>
                    </button>
                    <span class="text-xs font-bold text-[#8E8E93]" x-text="activeTask && activeTask.status === 'completed' ? 'Selesai' : 'Sedang Berjalan'"></span>
                </div>
                <button type="button" @click="drawerOpen = false" class="p-1 rounded-full hover:bg-black/5 text-[#8E8E93]">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Drawer Body --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-6" x-show="activeTask">
                <div>
                    <h3 class="text-lg font-bold text-black dark:text-white" x-text="activeTask ? activeTask.title : ''"></h3>
                    <template x-if="activeTask && activeTask.project">
                        <span class="inline-block mt-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#007AFF]/10 text-[#007AFF]" x-text="activeTask.project.name"></span>
                    </template>
                </div>

                {{-- Subtask Checklist --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#8E8E93]">{{ __('operations.checklists') }}</h4>
                    <div class="space-y-2">
                        <template x-for="item in (activeTask ? activeTask.checklists : [])" :key="item.id">
                            <div class="flex items-center gap-2 p-2 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <input type="checkbox" :checked="item.is_completed" @change="toggleSubtask(item.id)" class="rounded text-[#007AFF]">
                                <span class="text-xs flex-1 text-black dark:text-white" :class="item.is_completed ? 'line-through text-[#8E8E93]' : ''" x-text="item.title"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Add Subtask Input --}}
                    <div class="flex items-center gap-2 pt-1">
                        <input type="text" x-model="newSubtaskTitle" @keydown.enter.prevent="addSubtask()" placeholder="{{ __('operations.add_subtask') }}"
                            class="flex-1 px-3 py-1.5 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                        <button type="button" @click="addSubtask()" class="px-3 py-1.5 bg-[#007AFF] text-white rounded-xl text-xs font-semibold">Tambah</button>
                    </div>
                </div>

                {{-- Time Tracking --}}
                <div class="space-y-3 pt-4 border-t border-black/5 dark:border-white/10">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-[#8E8E93]">{{ __('operations.log_time') }}</h4>
                        <span class="text-xs font-bold text-black dark:text-white" x-text="'Total: ' + (activeTask ? (activeTask.actual_hours || 0) : 0) + ' jam'"></span>
                    </div>

                    <div class="p-3 bg-black/[0.02] dark:bg-white/[0.02] rounded-2xl border border-black/5 dark:border-white/5 space-y-2">
                        <div class="flex items-center gap-2">
                            <input type="number" x-model="logMinutes" min="5" step="5" placeholder="Menit" class="w-24 px-3 py-1.5 text-xs bg-white dark:bg-[#2C2C2E] border border-black/10 rounded-xl">
                            <input type="text" x-model="logNotes" placeholder="Catatan pekerjaan..." class="flex-1 px-3 py-1.5 text-xs bg-white dark:bg-[#2C2C2E] border border-black/10 rounded-xl">
                            <button type="button" @click="submitTimeLog()" class="px-3 py-1.5 bg-[#007AFF] text-white rounded-xl text-xs font-semibold">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Drawer Footer --}}
            <div class="p-4 border-t border-black/5 dark:border-white/10 flex items-center justify-between" x-show="activeTask">
                <form :action="'/operations/tasks/' + activeTask.id" method="POST" onsubmit="return confirm('Hapus tugas ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-xs text-[#FF3B30] hover:bg-[#FF3B30]/10 rounded-xl font-semibold">
                        {{ __('operations.task_deleted_success') }}
                    </button>
                </form>
                <button type="button" @click="drawerOpen = false" class="px-4 py-2 bg-black/[0.05] rounded-xl text-xs font-semibold">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection
