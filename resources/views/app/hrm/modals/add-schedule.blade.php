<!-- ======================================================== -->
    <!-- MODAL ROSTER 1: TAMBAH JADWAL / ROSTER (APPLE HIG)       -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showAddScheduleModal" 
            x-cloak
            @keydown.escape.window="showAddScheduleModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showAddScheduleModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showAddScheduleModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showAddScheduleModal = false">
                <div x-show="showAddScheduleModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#5856D6]/15 text-[#5856D6] flex items-center justify-center shrink-0">
                                <i data-lucide="calendar" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_add_roster_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.modal_add_roster_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddScheduleModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" action="{{ route('hrm.schedules.store') }}"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-6 overflow-y-auto space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_employee_required') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="user_id" required class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                            @foreach($memberships as $m)
                                @if($m->user)
                                    <option value="{{ $m->user->id }}">{{ $m->user->name }} ({{ $m->roleModel?->name ?? $m->role }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_schedule_type') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="schedule_type" x-model="scheduleType" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                            <option value="recurring">{{ __('hrm.opt_weekly_pattern') }}</option>
                            <option value="specific_date">{{ __('hrm.opt_specific_date_event') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Conditional Day of Week / Specific Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <template x-if="scheduleType === 'recurring'">
                        <div>
                            <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_work_day') }} <span class="text-[#FF3B30]">*</span></label>
                            <select name="day_of_week" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                                <option value="monday">{{ __('hrm.day_monday') }}</option>
                                <option value="tuesday">{{ __('hrm.day_tuesday') }}</option>
                                <option value="wednesday">{{ __('hrm.day_wednesday') }}</option>
                                <option value="thursday">{{ __('hrm.day_thursday') }}</option>
                                <option value="friday">{{ __('hrm.day_friday') }}</option>
                                <option value="saturday">{{ __('hrm.day_saturday') }}</option>
                                <option value="sunday">{{ __('hrm.day_sunday') }}</option>
                            </select>
                        </div>
                    </template>
                    <template x-if="scheduleType === 'specific_date'">
                        <div>
                            <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_specific_date_req') }} <span class="text-[#FF3B30]">*</span></label>
                            <input type="date" name="specific_date" value="{{ date('Y-m-d') }}"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                        </div>
                    </template>

                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.th_shift_assignment') }}</label>
                        <select name="work_shift_id" :disabled="isOffDay" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40 disabled:opacity-40">
                            @foreach($workShifts as $ws)
                                <option value="{{ $ws->id }}">{{ $ws->name }} ({{ $ws->start_time }} - {{ $ws->end_time }}{{ $ws->isOvernight() ? ' +1' : '' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- OFF DAY Toggle -->
                <div class="p-3.5 rounded-[14px] bg-[#FF3B30]/[0.06] border border-[#FF3B30]/20 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center shrink-0">
                            <i data-lucide="coffee" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-black dark:text-white block">{{ __('hrm.field_off_day') }}</span>
                            <span class="text-[11px] text-black/60 dark:text-white/60 block">{{ __('hrm.field_off_day_hint') }}</span>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_off_day" value="1" x-model="isOffDay" class="sr-only peer">
                        <div class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#FF3B30]"></div>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_effective_from') }}</label>
                        <input type="date" name="effective_date" placeholder="{{ __('hrm.effective_date_ph') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                        <span class="text-[10px] text-black/50 dark:text-white/50 block mt-1">{{ __('hrm.field_shift_change_hint') }}</span>
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_effective_until') }}</label>
                        <input type="date" name="end_date" placeholder="{{ __('hrm.end_date_ph') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                    </div>
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_override_location') }}</label>
                    <select name="location_id" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#5856D6]/40">
                        <option value="">{{ __('hrm.opt_follow_placement') }}</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_roster_notes') }}</label>
                    <input type="text" name="notes" placeholder="{{ __('hrm.roster_notes_placeholder') }}"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showAddScheduleModal = false"
                        class="h-11 px-4.5 rounded-[12px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmittingForm"
                        class="h-11 px-5 rounded-[12px] bg-[#5856D6] hover:bg-[#4745B8] text-white text-xs font-bold transition active:scale-[0.98] shadow-sm cursor-pointer">
                        {{ __('hrm.btn_save_roster') }}
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>
