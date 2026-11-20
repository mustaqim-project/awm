@extends('layouts.app', [
    'title' => __('operations.calendar_title') . ' - Cooca',
    'headerTitle' => __('operations.calendar_title'),
    'headerSubtitle' => __('operations.calendar_subtitle'),
])

@section('content')
<div class="space-y-6 pb-24 lg:pb-16" x-data="{
    currentView: 'month', // 'month', 'agenda'
    currentDate: new Date(),
    scope: 'all', // 'all', 'mine', 'google'
    events: [],
    loading: false,
    showNewModal: false,
    selectedDate: null,
    isSyncing: false,

    // Form
    form: {
        title: '',
        start_time: '',
        end_time: '',
        is_all_day: false,
        location: '',
        meeting_link: '',
        project_id: '',
        task_id: '',
        color_tag: '#007AFF',
        sync_google: true,
        reminders: [
            { channel: 'whatsapp', minutes_before: 15 },
            { channel: 'email', minutes_before: 60 }
        ]
    },

    get currentYear() {
        return this.currentDate.getFullYear();
    },
    get currentMonth() {
        return this.currentDate.getMonth();
    },
    get monthName() {
        return this.currentDate.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
    },

    init() {
        this.fetchEvents();
    },

    setScope(s) {
        this.scope = s;
        this.fetchEvents();
    },

    async fetchEvents() {
        this.loading = true;
        try {
            const start = new Date(this.currentYear, this.currentMonth - 1, 1).toISOString();
            const end = new Date(this.currentYear, this.currentMonth + 2, 0).toISOString();
            const res = await fetch(`/operations/calendar/events?start=${start}&end=${end}&scope=${this.scope}`, {
                headers: { 'Accept': 'application/json' }
            });
            this.events = await res.json();
        } catch (e) {
            console.error(e);
        } finally {
            this.loading = false;
        }
    },

    prevMonth() {
        this.currentDate = new Date(this.currentYear, this.currentMonth - 1, 1);
        this.fetchEvents();
    },
    nextMonth() {
        this.currentDate = new Date(this.currentYear, this.currentMonth + 1, 1);
        this.fetchEvents();
    },
    today() {
        this.currentDate = new Date();
        this.fetchEvents();
    },

    openAddEvent(dateStr = null) {
        if (dateStr) {
            const d = new Date(dateStr);
            d.setHours(9, 0, 0, 0);
            const dEnd = new Date(dateStr);
            dEnd.setHours(10, 0, 0, 0);
            this.form.start_time = this.formatInputDate(d);
            this.form.end_time = this.formatInputDate(dEnd);
        } else {
            const now = new Date();
            now.setMinutes(0);
            const later = new Date(now.getTime() + 60*60*1000);
            this.form.start_time = this.formatInputDate(now);
            this.form.end_time = this.formatInputDate(later);
        }
        this.showNewModal = true;
    },

    formatInputDate(d) {
        const pad = n => n < 10 ? '0' + n : n;
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    },

    async triggerGoogleSync() {
        this.isSyncing = true;
        try {
            const res = await fetch('{{ route('operations.calendar.google.sync-now') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            });
            const data = await res.json();
            if (data.success) {
                alert(data.message);
                this.fetchEvents();
            } else {
                alert(data.message || 'Gagal sinkronisasi');
            }
        } catch (e) {
            console.error(e);
        } finally {
            this.isSyncing = false;
        }
    },

    // Calendar grid generator
    get calendarDays() {
        const year = this.currentYear;
        const month = this.currentMonth;
        const firstDay = new Date(year, month, 1).getDay(); // 0 is Sunday
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        const days = [];

        // Adjust for Monday start (0: Mon, 6: Sun)
        const adjustedFirstDay = (firstDay === 0 ? 6 : firstDay - 1);

        // Prev month days
        for (let i = adjustedFirstDay - 1; i >= 0; i--) {
            const d = new Date(year, month - 1, daysInPrevMonth - i);
            days.push({
                date: d,
                dateStr: d.toISOString().split('T')[0],
                dayNumber: daysInPrevMonth - i,
                isCurrentMonth: false,
                isToday: false,
            });
        }

        // Current month days
        const todayStr = new Date().toISOString().split('T')[0];
        for (let i = 1; i <= daysInMonth; i++) {
            const d = new Date(year, month, i);
            const dStr = d.toISOString().split('T')[0];
            days.push({
                date: d,
                dateStr: dStr,
                dayNumber: i,
                isCurrentMonth: true,
                isToday: dStr === todayStr,
            });
        }

        // Next month days to fill 35 or 42 cells
        const total = days.length <= 35 ? 35 : 42;
        const remaining = total - days.length;
        for (let i = 1; i <= remaining; i++) {
            const d = new Date(year, month + 1, i);
            days.push({
                date: d,
                dateStr: d.toISOString().split('T')[0],
                dayNumber: i,
                isCurrentMonth: false,
                isToday: false,
            });
        }

        return days;
    },

    getEventsForDay(dateStr) {
        return this.events.filter(e => {
            const eStart = e.start.split('T')[0];
            return eStart === dateStr;
        });
    }
}">

    {{-- Top Controls & Google Sync Status Bar --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-5 border border-black/5 dark:border-white/10 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        {{-- Left: Month Title & Navigation --}}
        <div class="flex items-center gap-3">
            <h2 class="text-xl lg:text-2xl font-bold text-black dark:text-white capitalize min-w-[200px]" x-text="monthName"></h2>
            <div class="flex items-center gap-1 bg-black/[0.04] dark:bg-white/[0.06] p-1 rounded-xl">
                <button type="button" @click="prevMonth()" class="p-1.5 hover:bg-white dark:hover:bg-[#2C2C2E] rounded-lg transition-all text-[#8E8E93] hover:text-black dark:hover:text-white">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </button>
                <button type="button" @click="today()" class="px-2.5 py-1 text-xs font-semibold text-black dark:text-white hover:bg-white dark:hover:bg-[#2C2C2E] rounded-lg transition-all">
                    Hari Ini
                </button>
                <button type="button" @click="nextMonth()" class="p-1.5 hover:bg-white dark:hover:bg-[#2C2C2E] rounded-lg transition-all text-[#8E8E93] hover:text-black dark:hover:text-white">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        {{-- Center / Right: Google Calendar Status Badge & Actions --}}
        <div class="flex flex-wrap items-center gap-2.5 justify-end">
            {{-- Scope Filter (All / My Events / Google Calendar) --}}
            <div class="bg-black/[0.04] dark:bg-white/[0.06] p-1 rounded-xl flex items-center gap-1">
                <button type="button" @click="setScope('all')"
                    :class="scope === 'all' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-black dark:text-white' : 'text-[#8E8E93] hover:text-black dark:hover:text-white'"
                    class="px-2.5 py-1.5 rounded-lg text-xs transition-all">
                    {{ __('operations.calendar_scope_all') }}
                </button>
                <button type="button" @click="setScope('mine')"
                    :class="scope === 'mine' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-black dark:text-white' : 'text-[#8E8E93] hover:text-black dark:hover:text-white'"
                    class="px-2.5 py-1.5 rounded-lg text-xs transition-all">
                    {{ __('operations.calendar_scope_mine') }}
                </button>
                <button type="button" @click="setScope('google')"
                    :class="scope === 'google' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-black dark:text-white' : 'text-[#8E8E93] hover:text-black dark:hover:text-white'"
                    class="px-2.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1">
                    <i data-lucide="calendar" class="w-3 h-3 text-[#007AFF]"></i>
                    <span>{{ __('operations.calendar_scope_google') }}</span>
                </button>
            </div>

            {{-- Google Status Card / Pill --}}
            @if(!empty($myGoogleAccount))
                <div class="flex items-center gap-2 px-3 py-1.5 bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20 rounded-xl text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                    <span>{{ __('operations.my_google_calendar') }}: {{ $myGoogleAccount->email }}</span>
                    <button type="button" @click="triggerGoogleSync()" :disabled="isSyncing"
                        class="ml-1 p-1 hover:bg-[#34C759]/20 rounded-md transition-all" title="{{ __('operations.sync_now') }}">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="isSyncing ? 'animate-spin' : ''"></i>
                    </button>
                </div>
            @elseif(!empty($businessGoogleAccount))
                <div class="flex items-center gap-2 px-3 py-1.5 bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 rounded-xl text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-pulse"></span>
                    <span>{{ __('operations.business_google_calendar') }}: {{ $businessGoogleAccount->email }}</span>
                    <a href="{{ route('operations.calendar.settings') }}" class="ml-1 underline text-[11px] font-medium text-[#007AFF]">
                        + {{ __('operations.connect_my_google') }}
                    </a>
                </div>
            @else
                <a href="{{ route('operations.calendar.settings') }}"
                    class="flex items-center gap-2 px-3 py-1.5 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[#8E8E93] hover:text-black dark:hover:text-white rounded-xl text-xs font-medium transition-all">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span>{{ __('operations.connect_google') }}</span>
                </a>
            @endif

            {{-- View Toggle (Month / Agenda) --}}
            <div class="bg-black/[0.04] dark:bg-white/[0.06] p-1 rounded-xl flex items-center gap-1">
                <button type="button" @click="currentView = 'month'"
                    :class="currentView === 'month' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-black dark:text-white' : 'text-[#8E8E93] hover:text-black dark:hover:text-white'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all">
                    {{ __('operations.view_month') }}
                </button>
                <button type="button" @click="currentView = 'agenda'"
                    :class="currentView === 'agenda' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-black dark:text-white' : 'text-[#8E8E93] hover:text-black dark:hover:text-white'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all">
                    {{ __('operations.view_agenda') }}
                </button>
            </div>

            {{-- Add Event Button --}}
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('calendar.manage'))
            <button type="button" @click="openAddEvent()"
                class="px-4 py-2 bg-[#007AFF] hover:bg-[#007AFF]/90 text-white rounded-xl text-xs font-bold shadow-sm shadow-[#007AFF]/25 flex items-center gap-1.5 active:scale-95 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ __('operations.new_event') }}</span>
            </button>
            @endif
        </div>
    </div>

    {{-- MONTH GRID VIEW --}}
    <div x-show="currentView === 'month'" x-transition class="bg-white dark:bg-[#1C1C1E] rounded-3xl border border-black/5 dark:border-white/10 shadow-sm overflow-hidden">
        {{-- Weekday Headers --}}
        <div class="grid grid-cols-7 border-b border-black/5 dark:border-white/10 text-center py-3 text-xs font-bold text-[#8E8E93] uppercase tracking-wider bg-black/[0.01] dark:bg-white/[0.01]">
            <div>Sen</div>
            <div>Sel</div>
            <div>Rab</div>
            <div>Kam</div>
            <div>Jum</div>
            <div>Sab</div>
            <div>Min</div>
        </div>

        {{-- Month Days Grid --}}
        <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-black/5 dark:divide-white/5 border-b border-black/5 dark:border-white/5">
            <template x-for="day in calendarDays" :key="day.dateStr">
                <div class="min-h-[100px] lg:min-h-[120px] p-2 flex flex-col transition-colors hover:bg-black/[0.01] dark:hover:bg-white/[0.01] cursor-pointer"
                    :class="!day.isCurrentMonth ? 'bg-black/[0.015] dark:bg-white/[0.015] opacity-40' : ''"
                    @click="openAddEvent(day.dateStr)">
                    {{-- Day Number Header --}}
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center transition-all"
                            :class="day.isToday ? 'bg-[#007AFF] text-white shadow-sm' : 'text-black dark:text-white'"
                            x-text="day.dayNumber"></span>
                    </div>

                    {{-- Event Pills --}}
                    <div class="space-y-1 overflow-y-auto flex-1 max-h-[80px]">
                        <template x-for="ev in getEventsForDay(day.dateStr)" :key="ev.id">
                            <div class="px-2 py-1 rounded-md text-[11px] font-semibold truncate flex items-center gap-1 shadow-xs"
                                :style="'background-color: ' + ev.color + '20; color: ' + ev.color + '; border-left: 3px solid ' + ev.color"
                                :title="ev.title">
                                <span class="truncate" x-text="ev.title"></span>
                                <template x-if="ev.is_google_synced">
                                    <i data-lucide="refresh-cw" class="w-2.5 h-2.5 shrink-0 opacity-70"></i>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- AGENDA VIEW (Chronological list - great for mobile) --}}
    <div x-show="currentView === 'agenda'" x-transition class="space-y-4">
        <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-6 border border-black/5 dark:border-white/10 shadow-sm">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#8E8E93] mb-4">Agenda Kegiatan Mendatang</h3>
            <div class="space-y-3">
                <template x-for="ev in events" :key="ev.id">
                    <div class="p-4 rounded-2xl border border-black/5 dark:border-white/5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 hover:bg-black/[0.01] transition-all">
                        <div class="flex items-start gap-3">
                            <div class="w-3 h-12 rounded-full shrink-0 mt-1" :style="'background-color: ' + ev.color"></div>
                            <div>
                                <h4 class="text-sm font-bold text-black dark:text-white" x-text="ev.title"></h4>
                                <p class="text-xs text-[#8E8E93] mt-0.5" x-text="new Date(ev.start).toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' })"></p>
                                <template x-if="ev.location">
                                    <p class="text-xs text-[#8E8E93] mt-0.5 flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                                        <span x-text="ev.location"></span>
                                    </p>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-auto">
                            <template x-if="ev.meeting_link">
                                <a :href="ev.meeting_link" target="_blank" class="px-3 py-1.5 bg-[#007AFF]/10 text-[#007AFF] rounded-xl text-xs font-semibold hover:bg-[#007AFF]/20">
                                    Gabung Meeting
                                </a>
                            </template>
                            <form :action="'/operations/calendar/events/' + ev.id" method="POST" onsubmit="return confirm('Hapus kegiatan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-[#FF3B30] hover:bg-[#FF3B30]/10 rounded-lg">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
                <div x-show="events.length === 0" class="py-12 text-center text-xs text-[#8E8E93]">
                    Tidak ada agenda kegiatan yang terjadwal.
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH KEGIATAN BARU --}}
    <div x-show="showNewModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;">
        <div @click.outside="showNewModal = false" class="bg-white dark:bg-[#1C1C1E] rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-black/10 dark:border-white/10 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-black/5 dark:border-white/10">
                <h3 class="text-lg font-bold text-black dark:text-white">{{ __('operations.new_event') }}</h3>
                <button type="button" @click="showNewModal = false" class="p-1 rounded-full hover:bg-black/5 text-[#8E8E93]">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('operations.calendar.events.store') }}" method="POST" class="space-y-4 pt-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.event_title') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Misal: Meeting Evaluasi Proyek Klien A"
                        class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none font-medium">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.start_time') }} <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="start_time" x-model="form.start_time" required
                            class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.end_time') }} <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="end_time" x-model="form.end_time" required
                            class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.location') }}</label>
                        <input type="text" name="location" placeholder="Ruang Meeting / Outlet 1"
                            class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.meeting_link') }}</label>
                        <input type="url" name="meeting_link" placeholder="https://meet.google.com/..."
                            class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Tautkan ke Proyek</label>
                        <select name="project_id" class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                            <option value="">-- Tanpa Proyek --</option>
                            @foreach($projects as $proj)
                                <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">{{ __('operations.color_tag') }}</label>
                        <select name="color_tag" class="w-full px-3.5 py-2 text-xs bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#007AFF] outline-none">
                            <option value="#007AFF">Biru (Operasional)</option>
                            <option value="#34C759">Hijau (Meeting Klien)</option>
                            <option value="#FF9500">Oranye (Deadline/Review)</option>
                            <option value="#FF3B30">Merah (Mendesak/Penting)</option>
                            <option value="#AF52DE">Ungu (Pelatihan/Internal)</option>
                        </select>
                    </div>
                </div>

                {{-- Multi-Channel Reminders --}}
                <div class="p-3 bg-black/[0.02] dark:bg-white/[0.02] rounded-2xl border border-black/5 dark:border-white/5 space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-[#8E8E93] block">{{ __('operations.reminders') }}</label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="reminders[0][channel]" value="whatsapp" checked class="rounded text-[#007AFF]">
                            <input type="hidden" name="reminders[0][minutes_before]" value="15">
                            <span>WhatsApp (15 mnt sebelum)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="reminders[1][channel]" value="email" checked class="rounded text-[#007AFF]">
                            <input type="hidden" name="reminders[1][minutes_before]" value="60">
                            <span>Email (.ics invite)</span>
                        </label>
                    </div>
                </div>

                {{-- Google Sync Toggle --}}
                @if($googleAccount)
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="sync_google" value="1" id="sync_google" checked class="rounded text-[#007AFF]">
                    <label for="sync_google" class="text-xs text-black dark:text-white font-medium cursor-pointer">
                        {{ __('operations.sync_with_google') }} ({{ $googleAccount->email }})
                    </label>
                </div>
                @endif

                <div class="pt-4 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2">
                    <button type="button" @click="showNewModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#8E8E93] hover:bg-black/5">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#007AFF] hover:bg-[#007AFF]/90 text-white rounded-xl text-xs font-bold shadow-sm shadow-[#007AFF]/25">
                        Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
