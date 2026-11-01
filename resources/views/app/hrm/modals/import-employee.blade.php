<!-- ======================================================== -->
    <!-- MODAL IMPORT KARYAWAN MASSAL (BENTO XXL PRE-COMMIT)       -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showImportEmployeeModal" 
            x-cloak
            @keydown.escape.window="if (!importEmployeeExecuting) showImportEmployeeModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showImportEmployeeModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="if (!importEmployeeExecuting) showImportEmployeeModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-4" @click.self="showImportEmployeeModal = false">
                <div x-show="showImportEmployeeModal"
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
                            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white">{{ __('hrm.modal_import_emp_title') }}</h3>
                                <p class="text-xs text-black/55 dark:text-white/55">{{ __('hrm.modal_import_emp_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="if (!importEmployeeExecuting) showImportEmployeeModal = false"
                            class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1">

                <!-- State 1: Upload Dropzone -->
                <div x-show="!importEmployeePreview" class="space-y-4">
                    <!-- Langkah 1: Unduh Template -->
                    <div class="p-4 rounded-[16px] bg-[#007AFF]/[0.05] dark:bg-[#007AFF]/[0.1] border border-[#007AFF]/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-[#007AFF] font-bold text-[13px]">
                                <span class="w-5 h-5 rounded-full bg-[#007AFF]/15 flex items-center justify-center text-[11px] font-bold">1</span>
                                <span>{{ __('hrm.step_1_download_emp_template') }}</span>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.step_1_emp_template_desc') }}</p>
                        </div>
                        <a href="{{ route('hrm.employees.template') }}"
                           class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0066D6] text-white text-[12px] font-semibold transition flex items-center justify-center gap-1.5 shadow-sm shrink-0 active:scale-[0.98]">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>{{ __('hrm.btn_download_template_xlsx') }}</span>
                        </a>
                    </div>

                    <!-- Langkah 2: Upload Dropzone -->
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-2 text-black/80 dark:text-white/80 font-bold text-[13px]">
                            <span class="w-5 h-5 rounded-full bg-black/10 dark:bg-white/10 flex items-center justify-center text-[11px] font-bold">2</span>
                            <span>{{ __('hrm.step_2_upload_emp_file') }}</span>
                        </div>
                        <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('hrm.max_file_size_10mb') }}</span>
                    </div>

                    <label class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#007AFF] dark:hover:border-[#007AFF] rounded-[20px] p-8 sm:p-12 flex flex-col items-center justify-center text-center cursor-pointer transition bg-black/[0.01] hover:bg-[#007AFF]/[0.02] block">
                        <input type="file" accept=".xlsx,.xls,.csv" @change="handleImportEmployeeFileSelect($event)" class="hidden">
                        
                        <div class="w-16 h-16 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mb-4 mx-auto">
                            <i data-lucide="file-spreadsheet" class="w-8 h-8" x-show="!importEmployeeLoading"></i>
                            <i data-lucide="loader" class="w-8 h-8 animate-spin" x-show="importEmployeeLoading"></i>
                        </div>

                        <div class="space-y-1">
                            <span class="text-base font-bold text-black dark:text-white block" x-text="importEmployeeLoading ? '{{ addslashes(__('hrm.upload_emp_file_analyzing')) }}' : '{{ addslashes(__('hrm.upload_emp_file_idle')) }}'"></span>
                            <span class="text-xs text-black/50 dark:text-white/50 block">{{ __('hrm.upload_emp_format_hint') }}</span>
                        </div>
                    </label>
                </div>

                <!-- State 2: Pre-Commit Preview -->
                <div x-show="importEmployeePreview" class="space-y-5">
                    <!-- KPI Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
                            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('hrm.metric_total_staff') }}</span>
                            <div class="text-2xl font-bold font-mono text-black dark:text-white" x-text="importEmployeePreview ? importEmployeePreview.total_rows : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-emerald-500/[0.08] border border-emerald-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">{{ __('hrm.metric_ready_import') }}</span>
                            <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400" x-text="importEmployeePreview ? importEmployeePreview.valid_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-amber-500/[0.08] border border-amber-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">{{ __('hrm.metric_duplicate') }}</span>
                            <div class="text-2xl font-bold font-mono text-amber-600 dark:text-amber-400" x-text="importEmployeePreview ? importEmployeePreview.duplicate_count : 0"></div>
                        </div>
                        <div class="p-3.5 rounded-[16px] bg-rose-500/[0.08] border border-rose-500/20 space-y-1">
                            <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wider">{{ __('hrm.metric_format_error') }}</span>
                            <div class="text-2xl font-bold font-mono text-rose-600 dark:text-rose-400" x-text="importEmployeePreview ? importEmployeePreview.error_count : 0"></div>
                        </div>
                    </div>

                    <!-- Filter & Duplicate Strategy Bar -->
                    <div class="p-4 rounded-[18px] bg-[#F5F5F7] dark:bg-[#252528] border border-black/[0.05] dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-1 bg-black/[0.05] dark:bg-white/[0.08] p-1 rounded-[12px] text-[12px] font-medium">
                            <button type="button" @click="importEmployeeFilter = 'all'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importEmployeeFilter === 'all' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_all') }} (<span x-text="importEmployeePreview ? importEmployeePreview.total_rows : 0"></span>)
                            </button>
                            <button type="button" @click="importEmployeeFilter = 'valid'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importEmployeeFilter === 'valid' ? 'bg-white dark:bg-[#1C1C1E] text-emerald-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_ready') }} (<span x-text="importEmployeePreview ? importEmployeePreview.valid_count : 0"></span>)
                            </button>
                            <button type="button" @click="importEmployeeFilter = 'duplicate'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importEmployeeFilter === 'duplicate' ? 'bg-white dark:bg-[#1C1C1E] text-amber-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_duplicate') }} (<span x-text="importEmployeePreview ? importEmployeePreview.duplicate_count : 0"></span>)
                            </button>
                            <button type="button" @click="importEmployeeFilter = 'error'"
                                class="px-3 py-1.5 rounded-[8px] transition"
                                :class="importEmployeeFilter === 'error' ? 'bg-white dark:bg-[#1C1C1E] text-rose-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                {{ __('hrm.filter_error') }} (<span x-text="importEmployeePreview ? importEmployeePreview.error_count : 0"></span>)
                            </button>
                        </div>

                        <div class="flex items-center gap-3 text-[12px]">
                            <span class="text-black/50 dark:text-white/50 font-medium">{{ __('hrm.dup_strategy_emp_label') }}</span>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="skip" x-model="importEmployeeDuplicateStrategy" class="text-[#007AFF] focus:ring-0">
                                <span>{{ __('hrm.dup_strategy_skip') }}</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                <input type="radio" value="update" x-model="importEmployeeDuplicateStrategy" class="text-[#007AFF] focus:ring-0">
                                <span>{{ __('hrm.dup_strategy_update') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Interactive Table -->
                    <div class="border border-black/[0.08] dark:border-white/[0.1] rounded-[18px] overflow-hidden bg-white dark:bg-[#1C1C1E]">
                        <div class="max-h-[380px] overflow-y-auto">
                            <table class="w-full text-left text-[12px] border-collapse min-w-[800px]">
                                <thead class="sticky top-0 bg-[#F5F5F7] dark:bg-[#252528] border-b border-black/[0.08] dark:border-white/[0.1] text-black/60 dark:text-white/60 font-semibold z-10">
                                    <tr>
                                        <th class="py-2.5 px-3 w-16 text-center">{{ __('hrm.th_status') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_employee_name') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_email_phone') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_role_position') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_work_type') }}</th>
                                        <th class="py-2.5 px-3 text-right">{{ __('hrm.th_basic_salary') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_branch_location') }}</th>
                                        <th class="py-2.5 px-3">{{ __('hrm.th_notes_message') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                                    <template x-for="(row, idx) in (importEmployeePreview ? importEmployeePreview.rows.filter(r => importEmployeeFilter === 'all' || r.status === importEmployeeFilter) : [])" :key="idx">
                                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                                            <td class="py-2.5 px-3 text-center">
                                                <span x-show="row.status === 'valid'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">{{ __('hrm.filter_ready') }}</span>
                                                <span x-show="row.status === 'duplicate'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20">{{ __('hrm.filter_duplicate') }}</span>
                                                <span x-show="row.status === 'error'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20">{{ __('hrm.filter_error') }}</span>
                                            </td>
                                            <td class="py-2.5 px-3 font-semibold text-black dark:text-white" x-text="row.name || '-'"></td>
                                            <td class="py-2.5 px-3 text-black/70 dark:text-white/70">
                                                <div x-text="row.email || '-'"></div>
                                                <div class="text-[11px] text-black/40 dark:text-white/40" x-text="row.phone || ''"></div>
                                            </td>
                                            <td class="py-2.5 px-3 text-black/70 dark:text-white/70">
                                                <div class="font-medium text-black dark:text-white" x-text="row.job_title || '-'"></div>
                                                <div class="text-[11px] text-black/50 dark:text-white/50 capitalize" x-text="'Role: ' + (row.role || 'staff')"></div>
                                            </td>
                                            <td class="py-2.5 px-3 text-black/70 dark:text-white/70 capitalize" x-text="row.employment_type || 'full_time'"></td>
                                            <td class="py-2.5 px-3 text-right font-mono font-semibold text-black dark:text-white" x-text="'Rp ' + formatCurrency(row.base_salary)"></td>
                                            <td class="py-2.5 px-3 text-black/70 dark:text-white/70" x-text="row.location_name || '{{ addslashes(__('hrm.default_hq_location')) }}'"></td>
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
                    <button type="button" x-show="importEmployeePreview" @click="resetEmployeeImport()"
                        class="h-10 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer flex items-center gap-1.5"
                        :disabled="importEmployeeExecuting">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>{{ __('hrm.btn_change_excel_file') }}</span>
                    </button>
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" @click="if (!importEmployeeExecuting) showImportEmployeeModal = false"
                        class="h-10 px-5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer"
                        :disabled="importEmployeeExecuting">
                        {{ __('hrm.btn_cancel') }}
                    </button>

                    <button type="button" x-show="importEmployeePreview" @click="executeEmployeeImport()"
                        class="h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-sm shadow-[#007AFF]/25 active:scale-[0.98] transition cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="importEmployeeExecuting || !importEmployeePreview || (importEmployeeDuplicateStrategy === 'skip' ? importEmployeePreview.valid_count : (importEmployeePreview.valid_count + importEmployeePreview.duplicate_count)) === 0">
                        <i data-lucide="loader" class="w-4 h-4 animate-spin" x-show="importEmployeeExecuting"></i>
                        <i data-lucide="check" class="w-4 h-4" x-show="!importEmployeeExecuting"></i>
                        <span x-text="importEmployeeExecuting ? '{{ addslashes(__('hrm.processing_import')) }}' : ('{{ addslashes(__('hrm.commit_import_prefix')) }}' + (importEmployeeDuplicateStrategy === 'skip' ? importEmployeePreview.valid_count : (importEmployeePreview.valid_count + importEmployeePreview.duplicate_count)) + '{{ addslashes(__('hrm.commit_import_suffix_employees')) }}')"></span>
                    </button>
                </div>
                </div>
            </div>
        </div>
    </template>
