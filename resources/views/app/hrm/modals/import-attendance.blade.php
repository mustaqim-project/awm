<!-- ======================================================== -->
    <!-- MODAL IMPORT PRESENSI MESIN (BENTO XXL PRE-COMMIT)        -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showImportAttendanceModal" 
            x-cloak
            @keydown.escape.window="if (!importAttendanceExecuting) showImportAttendanceModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showImportAttendanceModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="if (!importAttendanceExecuting) showImportAttendanceModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-4" @click.self="showImportAttendanceModal = false">
                <div x-show="showImportAttendanceModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-5xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-white dark:bg-[#1C1C1E]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                                <i data-lucide="fingerprint" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white">{{ __('hrm.modal_import_att_title') }}</h3>
                                <p class="text-xs text-black/55 dark:text-white/55">{{ __('hrm.modal_import_att_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="if (!importAttendanceExecuting) showImportAttendanceModal = false"
                            class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1">

                <!-- State 1: Upload Dropzone -->
                <div x-show="!importAttendancePreview" class="space-y-4">
                    <!-- Langkah 1: Unduh Template -->
                    <div class="p-4 rounded-[16px] bg-[#34C759]/[0.08] dark:bg-[#34C759]/[0.12] border border-[#34C759]/25 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-[#248A3D] dark:text-[#30D158] font-bold text-[13px]">
                                <span class="w-5 h-5 rounded-full bg-[#34C759]/15 flex items-center justify-center text-[11px] font-bold">1</span>
                                <span>{{ __('hrm.step_1_download_att_template') }}</span>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.step_1_att_template_desc') }}</p>
                        </div>
                        <a href="{{ route('hrm.attendance.template') }}"
                           class="h-9 px-4 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] text-white text-[12px] font-semibold transition flex items-center justify-center gap-1.5 shadow-sm shrink-0 active:scale-[0.98]">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>{{ __('hrm.btn_download_template_xlsx') }}</span>
                        </a>
                    </div>

                    <!-- Langkah 2: Upload Dropzone -->
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-2 text-black/80 dark:text-white/80 font-bold text-[13px]">
                            <span class="w-5 h-5 rounded-full bg-black/10 dark:bg-white/10 flex items-center justify-center text-[11px] font-bold">2</span>
                            <span>{{ __('hrm.step_2_upload_att_file') }}</span>
                        </div>
                        <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('hrm.max_file_size_10mb') }}</span>
                    </div>

                    <label class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#34C759] dark:hover:border-[#34C759] rounded-[20px] p-8 sm:p-12 flex flex-col items-center justify-center text-center cursor-pointer transition bg-black/[0.01] hover:bg-[#34C759]/[0.02] block">
                        <input type="file" accept=".xlsx,.xls,.csv" @change="handleImportAttendanceFileSelect($event)" class="hidden">
                        
                        <div class="w-16 h-16 rounded-full bg-[#34C759]/10 text-[#34C759] flex items-center justify-center mb-4 mx-auto">
                            <i data-lucide="file-spreadsheet" class="w-8 h-8" x-show="!importAttendanceLoading"></i>
                            <i data-lucide="loader" class="w-8 h-8 animate-spin" x-show="importAttendanceLoading"></i>
                        </div>

                        <div class="space-y-1">
                            <span class="text-base font-bold text-black dark:text-white block" x-text="importAttendanceLoading ? '{{ addslashes(__('hrm.upload_att_file_analyzing')) }}' : '{{ addslashes(__('hrm.upload_att_file_idle')) }}'"></span>
                            <span class="text-xs text-black/50 dark:text-white/50 block">{{ __('hrm.upload_att_format_hint') }}</span>
                        </div>
                    </label>
                </div>

                <!-- State 2: Pre-Commit Preview -->
                <div x-show="importAttendancePreview" class="space-y-5">
                    <!-- KPI Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
                            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('hrm.metric_total_rows') }}</span>
                            <div class="text-2xl font-bold font-mono text-black dark:text-white" x-text="importAttendancePreview ? importAttendancePreview.total_rows : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-emerald-500/[0.08] border border-emerald-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">{{ __('hrm.metric_ready_import') }}</span>
                            <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400" x-text="importAttendancePreview ? importAttendancePreview.valid_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-amber-500/[0.08] border border-amber-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">{{ __('hrm.metric_already_exists') }}</span>
                            <div class="text-2xl font-bold font-mono text-amber-600 dark:text-amber-400" x-text="importAttendancePreview ? importAttendancePreview.duplicate_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-rose-500/[0.08] border border-rose-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wider">{{ __('hrm.metric_format_error') }}</span>
                            <div class="text-2xl font-bold font-mono text-rose-600 dark:text-rose-400" x-text="importAttendancePreview ? importAttendancePreview.error_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-[#34C759]/[0.08] border border-[#34C759]/20 space-y-1 col-span-2 sm:col-span-1">
                            <span class="text-[11px] font-semibold text-[#34C759] uppercase tracking-wider">{{ __('hrm.metric_total_work_hours') }}</span>
                            <div class="text-xl font-bold font-mono text-[#34C759]" x-text="importAttendancePreview ? (importAttendancePreview.total_work_hours + ' {{ addslashes(__('hrm.unit_hours')) }}') : '0 {{ addslashes(__('hrm.unit_hours')) }}'"></div>
                        </div>
                    </div>

                    <!-- Filter & Duplicate Strategy Bar -->
                    <div class="p-4 rounded-[18px] bg-[#F5F5F7] dark:bg-[#252528] border border-black/[0.05] dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-1 bg-black/[0.05] dark:bg-white/[0.08] p-1 rounded-[12px] text-[12px] font-medium">
                            <button type="button" @click="importAttendanceFilter = 'all'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importAttendanceFilter === 'all' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_all') }} (<span x-text="importAttendancePreview ? importAttendancePreview.total_rows : 0"></span>)
                            </button>
                            <button type="button" @click="importAttendanceFilter = 'valid'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importAttendanceFilter === 'valid' ? 'bg-white dark:bg-[#1C1C1E] text-emerald-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_ready') }} (<span x-text="importAttendancePreview ? importAttendancePreview.valid_count : 0"></span>)
                            </button>
                            <button type="button" @click="importAttendanceFilter = 'duplicate'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importAttendanceFilter === 'duplicate' ? 'bg-white dark:bg-[#1C1C1E] text-amber-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_duplicate') }} (<span x-text="importAttendancePreview ? importAttendancePreview.duplicate_count : 0"></span>)
                            </button>
                            <button type="button" @click="importAttendanceFilter = 'error'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importAttendanceFilter === 'error' ? 'bg-white dark:bg-[#1C1C1E] text-rose-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_error') }} (<span x-text="importAttendancePreview ? importAttendancePreview.error_count : 0"></span>)
                            </button>
                        </div>

                        <div class="flex items-center gap-3 text-[12px]">
                            <span class="text-black/50 dark:text-white/50 font-medium">{{ __('hrm.dup_strategy_att_label') }}</span>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="skip" x-model="importAttendanceDuplicateStrategy" class="text-[#34C759] focus:ring-0">
                                <span>{{ __('hrm.dup_strategy_skip') }}</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="update" x-model="importAttendanceDuplicateStrategy" class="text-[#34C759] focus:ring-0">
                                <span>{{ __('hrm.dup_strategy_update_hours') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Interactive Table -->
                    <div class="border border-black/[0.08] dark:border-white/[0.1] rounded-[18px] overflow-hidden bg-white dark:bg-[#1C1C1E]">
                        <div class="max-h-[380px] overflow-y-auto">
                            <table class="w-full text-left text-[12px] border-collapse min-w-[750px]">
                                <thead class="sticky top-0 bg-[#F5F5F7] dark:bg-[#252528] border-b border-black/[0.08] dark:border-white/[0.1] text-black/60 dark:text-white/60 font-semibold z-10">
                                    <tr>
                                        <th class="py-2.5 px-3 w-16 text-center">{{ __('hrm.th_status') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_employee') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_date') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_clock_in') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_clock_out') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_tardiness') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_work_duration') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_attendance_status') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_diagnostic') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                                    <template x-for="(row, idx) in (importAttendancePreview ? importAttendancePreview.rows.filter(r => importAttendanceFilter === 'all' || r.status === importAttendanceFilter) : [])" :key="idx">
                                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                                            <td class="py-2.5 px-3 text-center">
                                                <span x-show="row.status === 'valid'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">{{ __('hrm.filter_ready') }}</span>
                                                <span x-show="row.status === 'duplicate'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20">{{ __('hrm.filter_duplicate') }}</span>
                                                <span x-show="row.status === 'error'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20">{{ __('hrm.filter_error') }}</span>
                                            </td>
                                            <td class="py-2.5 px-3 font-semibold text-black dark:text-white">
                                                <div x-text="row.user_name || row.identifier"></div>
                                                <div class="text-[11px] text-black/40 dark:text-white/40 font-mono" x-text="row.identifier"></div>
                                            </td>
                                            <td class="py-2.5 px-3 tabular-nums text-black/80 dark:text-white/80" x-text="row.date || '-'"></td>
                                            <td class="py-2.5 px-3 font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="row.clock_in ? row.clock_in.substring(11, 16) : '-'"></td>
                                            <td class="py-2.5 px-3 font-mono font-bold text-black/70 dark:text-white/70" x-text="row.clock_out ? row.clock_out.substring(11, 16) : '-'"></td>
                                            <td class="py-2.5 px-3 tabular-nums" :class="row.late_minutes > 0 ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-black/50 dark:text-white/50'" x-text="row.late_minutes > 0 ? (row.late_minutes + ' {{ addslashes(__('hrm.unit_minutes_short')) }}') : '{{ addslashes(__('hrm.status_on_time')) }}'"></td>
                                            <td class="py-2.5 px-3 tabular-nums font-medium text-black dark:text-white" x-text="Math.floor((row.work_duration_minutes || 0) / 60) + 'j ' + ((row.work_duration_minutes || 0) % 60) + 'm'"></td>
                                            <td class="py-2.5 px-3">
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                                    :class="row.attendance_status === 'late' ? 'bg-amber-500/10 text-amber-600' : 'bg-emerald-500/10 text-emerald-600'"
                                                    x-text="row.attendance_status === 'late' ? '{{ addslashes(__('hrm.status_late')) }}' : '{{ addslashes(__('hrm.status_present')) }}'"></span>
                                            </td>
                                            <td class="py-2.5 px-3">
                                                <div class="space-y-0.5">
                                                    <template x-for="(msg, mi) in row.messages" :key="mi">
                                                        <div class="text-[11px]" :class="row.status === 'error' ? 'text-rose-600 dark:text-rose-400' : (row.status === 'duplicate' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400')" x-text="msg"></div>
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F9F9FB] dark:bg-[#252528]">
                <div>
                    <button type="button" x-show="importAttendancePreview" @click="resetAttendanceImport()"
                        class="h-10 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer flex items-center gap-1.5"
                        :disabled="importAttendanceExecuting">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>{{ __('hrm.btn_change_excel_file') }}</span>
                    </button>
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" @click="if (!importAttendanceExecuting) showImportAttendanceModal = false"
                        class="h-10 px-5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer"
                        :disabled="importAttendanceExecuting">
                        {{ __('hrm.btn_cancel') }}
                    </button>

                    <button type="button" x-show="importAttendancePreview" @click="executeAttendanceImport()"
                        class="h-10 px-6 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13px] font-semibold shadow-sm shadow-[#34C759]/25 active:scale-[0.98] transition cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="importAttendanceExecuting || !importAttendancePreview || (importAttendanceDuplicateStrategy === 'skip' ? importAttendancePreview.valid_count : (importAttendancePreview.valid_count + importAttendancePreview.duplicate_count)) === 0">
                        <i data-lucide="loader" class="w-4 h-4 animate-spin" x-show="importAttendanceExecuting"></i>
                        <i data-lucide="check" class="w-4 h-4" x-show="!importAttendanceExecuting"></i>
                        <span x-text="importAttendanceExecuting ? '{{ addslashes(__('hrm.processing_import')) }}' : ('{{ addslashes(__('hrm.commit_import_prefix')) }}' + (importAttendanceDuplicateStrategy === 'skip' ? importAttendancePreview.valid_count : (importAttendancePreview.valid_count + importAttendancePreview.duplicate_count)) + '{{ addslashes(__('hrm.commit_import_suffix_attendance')) }}')"></span>
                    </button>
                </div>
                </div>
            </div>
        </div>
    </template>
