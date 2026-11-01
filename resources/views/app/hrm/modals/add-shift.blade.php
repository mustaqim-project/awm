<!-- ======================================================== -->
    <!-- MODAL SHIFT 1: TAMBAH SHIFT KERJA (APPLE HIG)            -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showAddShiftModal" 
            x-cloak
            @keydown.escape.window="showAddShiftModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showAddShiftModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showAddShiftModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showAddShiftModal = false">
                <div x-show="showAddShiftModal"
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
                            <div class="w-10 h-10 rounded-[12px] bg-[#30B0C7]/15 text-[#30B0C7] flex items-center justify-center shrink-0">
                                <i data-lucide="clock" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_add_shift_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.modal_add_shift_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddShiftModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" action="{{ route('hrm.shifts.store') }}"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-6 overflow-y-auto space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div class="sm:col-span-2">
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_shift_name') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="name" required placeholder="{{ __('hrm.shift_name_placeholder') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#30B0C7]/40">
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_shift_code') }}</label>
                        <input type="text" name="code" placeholder="{{ __('hrm.shift_code_placeholder') }}" maxlength="20"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[14px] font-mono font-semibold text-black dark:text-white uppercase focus:ring-2 focus:ring-[#30B0C7]/40">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_start_time') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="time" name="start_time" required value="08:00"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[15px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#30B0C7]/40">
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_end_time') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="time" name="end_time" required value="17:00"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[15px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#30B0C7]/40">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_late_grace') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="grace_period_minutes" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#30B0C7]/40">
                            <option value="0">{{ __('hrm.opt_grace_0') }}</option>
                            <option value="5">{{ __('hrm.opt_grace_5') }}</option>
                            <option value="10" selected>{{ __('hrm.opt_grace_10') }}</option>
                            <option value="15">{{ __('hrm.opt_grace_15') }}</option>
                            <option value="30">{{ __('hrm.opt_grace_30') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_break_duration') }}</label>
                        <div class="relative">
                            <input type="number" name="break_duration_minutes" value="60" min="0" max="240"
                                class="w-full h-11 px-3.5 pr-12 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13.5px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#30B0C7]/40">
                            <span class="absolute right-3.5 top-3 text-xs text-black/40 dark:text-white/40 font-medium">{{ __('hrm.unit_minutes') }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_branch_location') }}</label>
                        <select name="location_id" class="w-full h-11 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#30B0C7]/40">
                            <option value="">{{ __('hrm.all_locations_global') }}</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Overnight Shift Toggle -->
                <div class="p-3.5 rounded-[14px] bg-[#AF52DE]/[0.06] border border-[#AF52DE]/20 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#AF52DE]/15 text-[#AF52DE] flex items-center justify-center shrink-0">
                            <i data-lucide="moon" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-black dark:text-white block">{{ __('hrm.field_overnight_shift') }}</span>
                            <span class="text-[11px] text-black/60 dark:text-white/60 block">{{ __('hrm.field_overnight_hint') }}</span>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_overnight" value="1" class="sr-only peer">
                        <div class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#AF52DE]"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_shift_description') }}</label>
                    <textarea name="description" rows="2" placeholder="{{ __('hrm.shift_notes_placeholder') }}"
                        class="w-full p-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white"></textarea>
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showAddShiftModal = false"
                        class="h-11 px-4.5 rounded-[12px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmittingForm"
                        class="h-11 px-5 rounded-[12px] bg-[#30B0C7] hover:bg-[#2591a3] text-white text-xs font-bold transition active:scale-[0.98] shadow-sm cursor-pointer">
                        {{ __('hrm.btn_save_shift') }}
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>
