@extends('layouts.app', [
    'title' => $project->name . ' - Cooca',
    'headerTitle' => $project->name,
    'headerSubtitle' => $project->code . ' • ' . __('operations.status_' . $project->status),
])

@section('content')
<div class="space-y-6 pb-24 lg:pb-16" x-data="{
    activeTab: 'overview', // 'overview', 'tasks', 'stages', 'calendar', 'timelogs'
    showEditModal: false,
    showNewTaskModal: false,

    // Task quick inline entry
    quickTaskTitle: '',
    async submitQuickTask() {
        if (!this.quickTaskTitle.trim()) return;
        try {
            const res = await fetch('{{ route('operations.tasks.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    title: this.quickTaskTitle,
                    project_id: '{{ $project->id }}',
                    status: 'pending',
                    priority: 'medium',
                }),
            });
            const data = await res.json();
            if (data.success) {
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
    }
}">

    {{-- Project Header Bar --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-5 border border-black/5 dark:border-white/10 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('operations.projects.index') }}" class="w-10 h-10 rounded-2xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] flex items-center justify-center shrink-0 text-[#8E8E93] hover:text-black dark:hover:text-white transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-[#8E8E93]">{{ $project->code }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase
                        {{ $project->status === 'completed' ? 'bg-[#34C759]/10 text-[#34C759]' : ($project->status === 'in_progress' ? 'bg-[#FF9500]/10 text-[#FF9500]' : 'bg-[#007AFF]/10 text-[#007AFF]') }}">
                        {{ __('operations.status_' . $project->status) }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase
                        {{ $project->priority === 'urgent' ? 'bg-[#FF3B30]/10 text-[#FF3B30]' : ($project->priority === 'high' ? 'bg-[#FF9500]/10 text-[#FF9500]' : 'bg-black/5 text-[#8E8E93]') }}">
                        {{ __('operations.priority_' . $project->priority) }}
                    </span>
                </div>
                <h2 class="text-xl lg:text-2xl font-bold text-black dark:text-white mt-1">{{ $project->name }}</h2>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 self-end md:self-auto">
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('projects.manage'))
            <button type="button" @click="showEditModal = true"
                class="px-3.5 py-2 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] rounded-xl text-xs font-semibold text-black dark:text-white flex items-center gap-1.5 transition-all">
                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                <span>{{ __('operations.edit_project') }}</span>
            </button>

            <form action="{{ route('operations.projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-2 text-[#FF3B30] hover:bg-[#FF3B30]/10 rounded-xl transition-all">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="flex items-center gap-2 overflow-x-auto border-b border-black/5 dark:border-white/10 pb-2">
        <button type="button" @click="activeTab = 'overview'"
            :class="activeTab === 'overview' ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-bold' : 'text-[#8E8E93] hover:text-black dark:hover:text-white hover:bg-black/[0.04]'"
            class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all">
            <i data-lucide="layout-grid" class="w-4 h-4"></i>
            <span>Ringkasan (Overview)</span>
        </button>

        <button type="button" @click="activeTab = 'tasks'"
            :class="activeTab === 'tasks' ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-bold' : 'text-[#8E8E93] hover:text-black dark:hover:text-white hover:bg-black/[0.04]'"
            class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all">
            <i data-lucide="check-square" class="w-4 h-4"></i>
            <span>Tugas & Todo ({{ $project->tasks->count() }})</span>
        </button>

        <button type="button" @click="activeTab = 'stages'"
            :class="activeTab === 'stages' ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-bold' : 'text-[#8E8E93] hover:text-black dark:hover:text-white hover:bg-black/[0.04]'"
            class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all">
            <i data-lucide="milestone" class="w-4 h-4"></i>
            <span>Tahapan ({{ $project->stages->count() }})</span>
        </button>

        <button type="button" @click="activeTab = 'calendar'"
            :class="activeTab === 'calendar' ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-bold' : 'text-[#8E8E93] hover:text-black dark:hover:text-white hover:bg-black/[0.04]'"
            class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all">
            <i data-lucide="calendar" class="w-4 h-4"></i>
            <span>Jadwal & Acara</span>
        </button>
    </div>

    {{-- TAB 1: OVERVIEW --}}
    <div x-show="activeTab === 'overview'" x-transition class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column (2 Cols): Project Info & Description --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Info Card --}}
                <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-6 border border-black/5 dark:border-white/10 shadow-sm space-y-4">
                    <h4 class="text-sm font-bold uppercase tracking-wider text-[#8E8E93]">Deskripsi & Ruang Lingkup</h4>
                    <p class="text-sm text-black dark:text-white leading-relaxed whitespace-pre-line">
                        {{ $project->description ?: 'Belum ada deskripsi yang ditambahkan untuk proyek ini.' }}
                    </p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-black/5 dark:border-white/10">
                        <div>
                            <span class="text-[11px] text-[#8E8E93] uppercase font-semibold block">{{ __('operations.start_date') }}</span>
                            <span class="text-sm font-bold text-black dark:text-white mt-0.5 block">
                                {{ $project->start_date ? $project->start_date->format('d M Y') : '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[11px] text-[#8E8E93] uppercase font-semibold block">{{ __('operations.due_date') }}</span>
                            <span class="text-sm font-bold text-black dark:text-white mt-0.5 block {{ $project->isOverdue() ? 'text-[#FF3B30]' : '' }}">
                                {{ $project->due_date ? $project->due_date->format('d M Y') : '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[11px] text-[#8E8E93] uppercase font-semibold block">{{ __('operations.budget') }}</span>
                            <span class="text-sm font-bold text-black dark:text-white mt-0.5 block">
                                Rp {{ number_format($project->budget, 0, ',', '.') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[11px] text-[#8E8E93] uppercase font-semibold block">{{ __('operations.spent') }}</span>
                            <span class="text-sm font-bold text-black dark:text-white mt-0.5 block">
                                Rp {{ number_format($project->spent, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Progress & Health --}}
                <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-6 border border-black/5 dark:border-white/10 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-[#8E8E93]">Kemajuan Pekerjaan</h4>
                        <span class="text-lg font-bold text-[#007AFF]">{{ $project->progress_percentage }}% Selesai</span>
                    </div>
                    <div class="w-full bg-black/5 dark:bg-white/10 h-3 rounded-full overflow-hidden">
                        <div class="bg-[#007AFF] h-full rounded-full transition-all duration-500" style="width: {{ $project->progress_percentage }}%"></div>
                    </div>
                    <p class="text-xs text-[#8E8E93] mt-3">
                        Telah menyelesaikan {{ $project->tasks->where('status', 'completed')->count() }} dari total {{ $project->tasks->count() }} tugas yang terdaftar.
                    </p>
                </div>
            </div>

            {{-- Right Column (1 Col): Client & Team Members --}}
            <div class="space-y-6">
                {{-- Client Card --}}
                <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-6 border border-black/5 dark:border-white/10 shadow-sm space-y-3">
                    <h4 class="text-sm font-bold uppercase tracking-wider text-[#8E8E93]">{{ __('operations.project_client') }}</h4>
                    @if($project->client)
                        <div class="flex items-center gap-3 pt-1">
                            <div class="w-10 h-10 rounded-full bg-[#007AFF]/10 text-[#007AFF] font-bold flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($project->client->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <h5 class="text-sm font-bold text-black dark:text-white truncate">{{ $project->client->name }}</h5>
                                <p class="text-xs text-[#8E8E93] truncate">{{ $project->client->phone ?: $project->client->email ?: 'Tidak ada kontak' }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-[#8E8E93]">Proyek internal tanpa relasi klien eksternal.</p>
                    @endif
                </div>

                {{-- Team Members Card --}}
                <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-6 border border-black/5 dark:border-white/10 shadow-sm space-y-3">
                    <h4 class="text-sm font-bold uppercase tracking-wider text-[#8E8E93]">{{ __('operations.team_members') }}</h4>
                    <div class="space-y-2 pt-1">
                        @forelse($project->members as $member)
                        <div class="flex items-center justify-between text-xs py-1.5 border-b border-black/5 dark:border-white/5 last:border-0">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center font-bold text-[10px]">
                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                </div>
                                <span class="font-semibold text-black dark:text-white">{{ $member->name }}</span>
                            </div>
                            <span class="text-[10px] uppercase font-bold text-[#8E8E93] bg-black/5 px-2 py-0.5 rounded-full">{{ $member->pivot->role ?? 'Anggota' }}</span>
                        </div>
                        @empty
                        <p class="text-xs text-[#8E8E93]">Belum ada anggota tim yang ditugaskan khusus.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: TASKS & KANBAN --}}
    <div x-show="activeTab === 'tasks'" x-transition class="space-y-4">
        {{-- Quick Task Input Bar --}}
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-3 border border-black/5 dark:border-white/10 shadow-sm">
            <div class="flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5 text-[#007AFF] shrink-0"></i>
                <input type="text" x-model="quickTaskTitle" @keydown.enter.prevent="submitQuickTask()"
                    placeholder="{{ __('operations.quick_task_placeholder') }}"
                    class="flex-1 text-xs bg-transparent border-0 focus:ring-0 outline-none text-black dark:text-white placeholder-[#8E8E93]">
                <button type="button" @click="submitQuickTask()"
                    class="px-4 py-2 bg-[#007AFF] text-white rounded-xl text-xs font-semibold hover:bg-[#007AFF]/90 active:scale-95 transition-all">
                    {{ __('operations.add_task') }}
                </button>
            </div>
        </div>

        {{-- Tasks List --}}
        <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-6 border border-black/5 dark:border-white/10 shadow-sm">
            <div class="space-y-2">
                @forelse($project->tasks as $task)
                <div class="flex items-center justify-between p-3 rounded-2xl border border-black/5 dark:border-white/5 hover:bg-black/[0.02] transition-colors group">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <button type="button" @click="toggleTask('{{ $task->id }}')"
                            class="w-5 h-5 rounded-md border flex items-center justify-center transition-colors {{ $task->status === 'completed' ? 'bg-[#34C759] border-[#34C759] text-white' : 'border-black/20 dark:border-white/20 hover:border-[#007AFF]' }}">
                            @if($task->status === 'completed')
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            @endif
                        </button>
                        <div class="min-w-0">
                            <span class="text-sm font-semibold text-black dark:text-white block truncate {{ $task->status === 'completed' ? 'line-through text-[#8E8E93] dark:text-[#8E8E93]' : '' }}">
                                {{ $task->title }}
                            </span>
                            @if($task->checklists->count() > 0)
                                <span class="text-[11px] text-[#8E8E93]">
                                    <i data-lucide="check-square" class="w-3 h-3 inline"></i>
                                    {{ $task->checklists->where('is_completed', true)->count() }}/{{ $task->checklists->count() }} subtask
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                            {{ $task->priority === 'urgent' ? 'bg-[#FF3B30]/10 text-[#FF3B30]' : ($task->priority === 'high' ? 'bg-[#FF9500]/10 text-[#FF9500]' : 'bg-black/5 text-[#8E8E93]') }}">
                            {{ __('operations.priority_' . $task->priority) }}
                        </span>
                        @if($task->due_date)
                            <span class="text-xs text-[#8E8E93] {{ $task->isOverdue() ? 'text-[#FF3B30] font-bold' : '' }}">
                                {{ $task->due_date->format('d M') }}
                            </span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-12 text-center text-xs text-[#8E8E93]">
                    {{ __('operations.no_tasks_found') }}
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- TAB 3: STAGES --}}
    <div x-show="activeTab === 'stages'" x-transition class="space-y-4">
        <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-6 border border-black/5 dark:border-white/10 shadow-sm">
            <h4 class="text-sm font-bold uppercase tracking-wider text-[#8E8E93] mb-4">Milestone & Tahapan Proyek</h4>
            <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-black/10 dark:before:bg-white/10">
                @forelse($project->stages as $stage)
                <div class="relative">
                    <div class="absolute -left-[27px] top-1.5 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-[#1C1C1E] {{ $stage->status === 'completed' ? 'bg-[#34C759]' : 'bg-[#007AFF]' }}"></div>
                    <div class="bg-[#F2F2F7]/50 dark:bg-[#2C2C2E]/50 rounded-2xl p-4 border border-black/5 dark:border-white/5">
                        <div class="flex items-center justify-between">
                            <h5 class="text-sm font-bold text-black dark:text-white">{{ $stage->name }}</h5>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-black/5 text-[#8E8E93]">
                                {{ $stage->tasks->count() }} Tugas Terkait
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-[#8E8E93]">Belum ada tahapan yang dikonfigurasi.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- TAB 4: CALENDAR --}}
    <div x-show="activeTab === 'calendar'" x-transition class="space-y-4">
        <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-6 border border-black/5 dark:border-white/10 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-bold uppercase tracking-wider text-[#8E8E93]">Agenda Kegiatan Proyek</h4>
                <a href="{{ route('operations.calendar.index') }}" class="text-xs text-[#007AFF] font-semibold hover:underline">
                    Buka Kalender Lengkap →
                </a>
            </div>
            <div class="space-y-3">
                @forelse($project->calendarEvents as $event)
                <div class="p-4 rounded-2xl border border-black/5 dark:border-white/5 flex items-center justify-between">
                    <div>
                        <h5 class="text-sm font-bold text-black dark:text-white">{{ $event->title }}</h5>
                        <p class="text-xs text-[#8E8E93] mt-0.5">
                            {{ $event->start_time->format('d M Y H:i') }} - {{ $event->end_time->format('H:i') }}
                        </p>
                    </div>
                    @if($event->meeting_link)
                        <a href="{{ $event->meeting_link }}" target="_blank" class="px-3 py-1.5 bg-[#007AFF]/10 text-[#007AFF] rounded-xl text-xs font-semibold hover:bg-[#007AFF]/20">
                            Gabung Meeting
                        </a>
                    @endif
                </div>
                @empty
                <div class="py-8 text-center text-xs text-[#8E8E93]">
                    Belum ada kegiatan atau meeting yang dijadwalkan khusus untuk proyek ini.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- EDIT PROJECT MODAL --}}
    <div x-show="showEditModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;">
        <div @click.outside="showEditModal = false" class="bg-white dark:bg-[#1C1C1E] rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-black/10 dark:border-white/10 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-black/5 dark:border-white/10">
                <h3 class="text-lg font-bold text-black dark:text-white">{{ __('operations.edit_project') }}</h3>
                <button type="button" @click="showEditModal = false" class="p-1 rounded-full hover:bg-black/5 text-[#8E8E93]">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('operations.projects.update', $project) }}" method="POST" class="space-y-4 pt-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.project_name') }}</label>
                    <input type="text" name="name" value="{{ $project->name }}" required
                        class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Status</label>
                        <select name="status" class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                            <option value="planning" {{ $project->status === 'planning' ? 'selected' : '' }}>{{ __('operations.status_planning') }}</option>
                            <option value="in_progress" {{ $project->status === 'in_progress' ? 'selected' : '' }}>{{ __('operations.status_in_progress') }}</option>
                            <option value="review" {{ $project->status === 'review' ? 'selected' : '' }}>{{ __('operations.status_review') }}</option>
                            <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>{{ __('operations.status_completed') }}</option>
                            <option value="on_hold" {{ $project->status === 'on_hold' ? 'selected' : '' }}>{{ __('operations.status_on_hold') }}</option>
                            <option value="cancelled" {{ $project->status === 'cancelled' ? 'selected' : '' }}>{{ __('operations.status_cancelled') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Prioritas</label>
                        <select name="priority" class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                            <option value="low" {{ $project->priority === 'low' ? 'selected' : '' }}>{{ __('operations.priority_low') }}</option>
                            <option value="medium" {{ $project->priority === 'medium' ? 'selected' : '' }}>{{ __('operations.priority_medium') }}</option>
                            <option value="high" {{ $project->priority === 'high' ? 'selected' : '' }}>{{ __('operations.priority_high') }}</option>
                            <option value="urgent" {{ $project->priority === 'urgent' ? 'selected' : '' }}>{{ __('operations.priority_urgent') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.budget') }}</label>
                        <input type="number" name="budget" value="{{ $project->budget }}"
                            class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.spent') }}</label>
                        <input type="number" name="spent" value="{{ $project->spent }}"
                            class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.project_description') }}</label>
                    <textarea name="description" rows="3"
                        class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">{{ $project->description }}</textarea>
                </div>

                <div class="pt-4 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#8E8E93] hover:bg-black/5">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#007AFF] text-white rounded-xl text-xs font-semibold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
