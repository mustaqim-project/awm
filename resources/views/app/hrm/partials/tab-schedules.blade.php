<!-- ======================================================== -->
    <!-- TAB 4C: JADWAL & ROSTER KARYAWAN (EMPLOYEE SCHEDULES)    -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'schedules'" class="space-y-4" x-transition.opacity style="display: none;">
        <!-- Filter Controls Bar -->
        <form method="GET" action="{{ route('hrm.index') }}" class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.02)] space-y-3.5">
            <input type="hidden" name="tab" value="schedules">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-[11px] uppercase font-bold text-black/60 dark:text-white/60 mb-1">{{ __('hrm.th_employee') }}</label>
                    <select name="sch_user_id" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs font-semibold text-black dark:text-white focus:ring-1 focus:ring-[#5856D6]">
                        <option value="">{{ __('hrm.filter_all_employees') }}</option>
                        @foreach($memberships as $m)
                            @if($m->user)
                                <option value="{{ $m->user->id }}" {{ $schUserId === $m->user->id ? 'selected' : '' }}>{{ $m->user->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-bold text-black/60 dark:text-white/60 mb-1">{{ __('hrm.field_work_shift') }}</label>
                    <select name="sch_shift_id" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs font-semibold text-black dark:text-white focus:ring-1 focus:ring-[#5856D6]">
                        <option value="">{{ __('hrm.filter_all_shifts') }}</option>
                        @foreach($workShifts as $ws)
                            <option value="{{ $ws->id }}" {{ $schShiftId === $ws->id ? 'selected' : '' }}>{{ $ws->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] uppercase font-bold text-black/60 dark:text-white/60 mb-1">{{ __('hrm.field_schedule_type') }}</label>
                    <select name="sch_type" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs font-semibold text-black dark:text-white focus:ring-1 focus:ring-[#5856D6]">
                        <option value="">{{ __('hrm.filter_all_types') }}</option>
                        <option value="recurring" {{ $schType === 'recurring' ? 'selected' : '' }}>{{ __('hrm.opt_recurring_weekly') }}</option>
                        <option value="specific_date" {{ $schType === 'specific_date' ? 'selected' : '' }}>{{ __('hrm.opt_specific_date') }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 h-10 px-4 rounded-[10px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.12] text-black dark:text-white text-xs font-semibold transition cursor-pointer">
                        {{ __('hrm.apply_filter') }}
                    </button>
                    @if($schUserId || $schShiftId || $schType)
                        <a href="{{ route('hrm.index', ['tab' => 'schedules']) }}" class="h-10 px-3 rounded-[10px] bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60 text-xs font-semibold flex items-center justify-center hover:text-black dark:hover:text-white">
                            {{ __('hrm.reset_filter') }}
                        </a>
                    @endif
                </div>
            </div>
        </form>

        @php
            $dayLabels = [
                'monday' => __('hrm.day_monday'),
                'tuesday' => __('hrm.day_tuesday'),
                'wednesday' => __('hrm.day_wednesday'),
                'thursday' => __('hrm.day_thursday'),
                'friday' => __('hrm.day_friday'),
                'saturday' => __('hrm.day_saturday'),
                'sunday' => __('hrm.day_sunday'),
            ];
        @endphp

        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <!-- Desktop Table View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[950px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.th_employee') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_schedule_pattern') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_shift_assignment') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_valid_period') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_branch_location') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_notes') }}</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.th_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($employeeSchedules as $sch)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white">
                                    {{ $sch->user?->name ?? __('hrm.default_employee_name') }}
                                    <div class="text-[11px] text-black/50 dark:text-white/50 font-normal">{{ $sch->user?->email }}</div>
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($sch->schedule_type === 'recurring')
                                        <div class="font-bold text-[#5856D6] flex items-center gap-1.5">
                                            <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.every_day_prefix', ['day' => $dayLabels[$sch->day_of_week] ?? ucfirst($sch->day_of_week)]) }}</span>
                                        </div>
                                        <span class="text-[10.5px] text-black/50 dark:text-white/50">{{ __('hrm.recurring_roster_sub') }}</span>
                                    @else
                                        <div class="font-bold text-[#007AFF] tabular-nums">
                                            {{ $sch->specific_date ? \Carbon\Carbon::parse($sch->specific_date)->translatedFormat('d M Y') : '-' }}
                                        </div>
                                        <span class="text-[10.5px] text-black/50 dark:text-white/50">{{ __('hrm.opt_specific_date') }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($sch->is_off_day)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] border border-[#FF3B30]/30">
                                            {{ __('hrm.badge_off_day') }}
                                        </span>
                                    @elseif($sch->workShift)
                                        <div class="font-bold text-black dark:text-white flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $sch->workShift->color ?: '#5856D6' }}"></span>
                                            <span>{{ $sch->workShift->name }}</span>
                                        </div>
                                        <div class="text-[11px] text-black/60 dark:text-white/60 font-mono tabular-nums">
                                            {{ $sch->workShift->start_time }} - {{ $sch->workShift->end_time }}
                                            @if($sch->workShift->isOvernight()) (+1) @endif
                                        </div>
                                    @else
                                        <span class="text-black/40 dark:text-white/40">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-[11.5px] tabular-nums text-black/70 dark:text-white/70">
                                    @if($sch->effective_date || $sch->end_date)
                                        <div>{{ __('hrm.shift_starts_at') }} {{ $sch->effective_date ? \Carbon\Carbon::parse($sch->effective_date)->format('d/m/Y') : __('hrm.shift_always') }}</div>
                                        <div>{{ __('hrm.shift_ends_at') }} {{ $sch->end_date ? \Carbon\Carbon::parse($sch->end_date)->format('d/m/Y') : __('hrm.shift_indefinite') }}</div>
                                    @else
                                        <span class="text-black/40 dark:text-white/40">{{ __('hrm.shift_permanent') }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-[12.5px] text-black/70 dark:text-white/70">
                                    {{ $sch->location?->name ?? __('hrm.all_locations_global') }}
                                </td>
                                <td class="py-3.5 px-3 text-[11.5px] text-black/60 dark:text-white/60 max-w-xs truncate">
                                    {{ $sch->notes ?: '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        @if(\App\Support\Context::hasPermission('hrm.manage') || \App\Support\Context::hasPermission('users.manage'))
                                        <button type="button"
                                            @click="openEditSchedule({{ Js::from($sch) }}, '{{ route('hrm.schedules.update', $sch->id) }}')"
                                            class="w-9 h-9 min-h-[36px] min-w-[36px] sm:w-8 sm:h-8 rounded-[8px] flex items-center justify-center text-[#5856D6] hover:bg-[#5856D6]/10 dark:hover:bg-[#5856D6]/20 transition-all cursor-pointer shrink-0"
                                            title="{{ __('hrm.btn_edit') }}"
                                            aria-label="{{ __('hrm.btn_edit') }}">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        <form method="POST" action="{{ route('hrm.schedules.destroy', $sch->id) }}"
                                            onsubmit="return confirm('{{ __('hrm.confirm_delete_schedule') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-9 h-9 min-h-[36px] min-w-[36px] sm:w-8 sm:h-8 rounded-[8px] flex items-center justify-center text-[#FF3B30] hover:bg-[#FF3B30]/10 dark:hover:bg-[#FF3B30]/20 transition-all cursor-pointer shrink-0"
                                                title="{{ __('hrm.tooltip_delete_schedule') ?? 'Hapus Jadwal' }}"
                                                aria-label="{{ __('hrm.tooltip_delete_schedule') ?? 'Hapus Jadwal' }}">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    <i data-lucide="calendar" class="w-8 h-8 mx-auto mb-2 text-black/30 dark:text-white/30"></i>
                                    <p class="text-xs">{{ __('hrm.empty_roster_desc') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden divide-y divide-black/5 dark:divide-white/5">
                @forelse($employeeSchedules as $sch)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-black dark:text-white text-[14px]">{{ $sch->user?->name ?? __('hrm.default_employee_name') }}</span>
                            @if($sch->is_off_day)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">{{ __('hrm.badge_off_day') }}</span>
                            @elseif($sch->workShift)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#5856D6]/15 text-[#5856D6]">{{ $sch->workShift->name }}</span>
                            @endif
                        </div>
                        <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.schedule_pattern') }}</span>
                                <span class="font-bold">
                                    @if($sch->schedule_type === 'recurring')
                                        {{ __('hrm.every_day_prefix', ['day' => $dayLabels[$sch->day_of_week] ?? ucfirst($sch->day_of_week)]) }}
                                    @else
                                        {{ $sch->specific_date ? \Carbon\Carbon::parse($sch->specific_date)->translatedFormat('d M Y') : '-' }}
                                    @endif
                                </span>
                            </div>
                            @if($sch->workShift && !$sch->is_off_day)
                                <div class="flex items-center justify-between">
                                    <span class="text-black/55 dark:text-white/55">{{ __('hrm.shift_hours') }}</span>
                                    <span class="font-mono font-bold">{{ $sch->workShift->start_time }} - {{ $sch->workShift->end_time }}</span>
                                </div>
                            @endif
                            @if($sch->notes)
                                <div class="text-[11px] text-black/60 dark:text-white/60 italic pt-1">
                                    "{{ $sch->notes }}"
                                </div>
                            @endif
                        </div>
                        @if(\App\Support\Context::hasPermission('hrm.manage') || \App\Support\Context::hasPermission('users.manage'))
                        <div class="pt-1 flex items-center gap-2">
                            <button type="button"
                                @click="openEditSchedule({{ Js::from($sch) }}, '{{ route('hrm.schedules.update', $sch->id) }}')"
                                class="flex-1 h-11 px-3 rounded-[11px] text-[12.5px] font-bold text-[#5856D6] bg-[#5856D6]/10 hover:bg-[#5856D6] hover:text-white transition active:scale-[0.98] flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>{{ __('hrm.btn_edit_schedule') }}</span>
                            </button>
                            <form method="POST" action="{{ route('hrm.schedules.destroy', $sch->id) }}"
                                onsubmit="return confirm('{{ __('hrm.confirm_delete_schedule') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="h-11 w-11 rounded-[11px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition flex items-center justify-center cursor-pointer shrink-0">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="py-8 text-center text-black/50 dark:text-white/50 text-xs">
                        {{ __('hrm.empty_roster_desc') }}
                    </div>
                @endforelse
            </div>

            @if($employeeSchedules->total() > 0)
                <div class="p-3.5 border-t border-black/5 dark:border-white/10">
                    {{ $employeeSchedules->links() }}
                </div>
            @endif
        </div>
    </div>
