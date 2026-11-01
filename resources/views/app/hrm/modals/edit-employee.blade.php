<!-- ======================================================== -->
    <!-- MODAL 6: EDIT PROFIL HRM KARYAWAN (XXL CANVAS 2-KOLOM)   -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showEditEmployeeModal" 
            x-cloak
            @keydown.escape.window="showEditEmployeeModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showEditEmployeeModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showEditEmployeeModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showEditEmployeeModal = false">
                <div x-show="showEditEmployeeModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-5xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[90vh]">
                    <!-- Modal Header -->
                    <div class="px-6 py-4.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.01]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0 shadow-xs">
                                <i data-lucide="edit-3" class="w-5.5 h-5.5"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('hrm.modal_edit_emp_title') }}</h3>
                                    <span class="px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-[10.5px] font-bold uppercase tracking-wider">Canvas XXL</span>
                                </div>
                                <p class="text-[12.5px] text-black/60 dark:text-white/60">{{ __('hrm.modal_edit_emp_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showEditEmployeeModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <!-- Modal Form with 2-Column XXL Ergonomic Grid -->
            <form method="POST" :action="editFormAction" enctype="multipart/form-data"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-6 overflow-y-auto space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                    <!-- ==================== KOLOM KIRI ==================== -->
                    <div class="space-y-6">
                        <!-- Kluster 1: Identitas & Biodata Karyawan -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/8">
                                <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                                    <div class="w-6 h-6 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                        <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ __('hrm.cluster_1_title') }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('hrm.cluster_1_sub') }}</span>
                            </div>

                            <div class="space-y-3.5">
                                <div>
                                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_fullname') }} <span class="text-[#FF3B30]">*</span></label>
                                    <input type="text" name="name" required :value="selectedEmployee?.user?.name || ''"
                                        class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_wa_number') }}</label>
                                        <input type="text" name="whatsapp_number" :value="selectedEmployee?.whatsapp_number || ''" placeholder="{{ __('hrm.placeholder_wa') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_npwp') }}</label>
                                        <input type="text" name="npwp" :value="selectedEmployee?.npwp || ''" placeholder="{{ __('hrm.placeholder_npwp') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-mono font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_nik_ktp') }}</label>
                                    <input type="text" name="nik_ktp" :value="selectedEmployee?.nik_ktp || ''" placeholder="{{ __('hrm.placeholder_nik') }}" maxlength="16"
                                        class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-mono font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition">
                                </div>
                            </div>
                        </div>

                        <!-- Kluster 3: Skema Kompensasi & Rekening Penggajian -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/8">
                                <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                                    <div class="w-6 h-6 rounded-lg bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                        <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ __('hrm.cluster_3_title') }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('hrm.cluster_3_sub') }}</span>
                            </div>

                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_base_salary') }}</label>
                                        <input type="number" name="base_salary" step="1000" :value="selectedEmployee?.base_salary || 0"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#34C759]/40 focus:border-[#34C759] transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_daily_rate') }}</label>
                                        <input type="number" name="daily_rate" step="1000" :value="selectedEmployee?.daily_rate || 0"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#34C759]/40 focus:border-[#34C759] transition">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_bank_name') }}</label>
                                        <input type="text" name="bank_name" :value="selectedEmployee?.bank_name || ''" placeholder="{{ __('hrm.placeholder_bank_name') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#34C759]/40 focus:border-[#34C759] transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_bank_account_num') }}</label>
                                        <input type="text" name="bank_account_number" :value="selectedEmployee?.bank_account_number || ''" placeholder="{{ __('hrm.field_bank_account_ph') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#34C759]/40 focus:border-[#34C759] transition">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== KOLOM KANAN ==================== -->
                    <div class="space-y-6">
                        <!-- Kluster 2: Posisi, Struktur Organisasi & Penempatan -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/8">
                                <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                                    <div class="w-6 h-6 rounded-lg bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                                        <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ __('hrm.cluster_2_title') }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('hrm.cluster_2_sub') }}</span>
                            </div>

                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_access_role') }} <span class="text-[#FF3B30]">*</span></label>
                                        <select name="role_id" required class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                            @foreach($availableRoles as $r)
                                                <option value="{{ $r->id }}" :selected="selectedEmployee?.role_id === '{{ $r->id }}'">{{ $r->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_specific_job') }}</label>
                                        <input type="text" name="job_title" :value="selectedEmployee?.job_title || ''" placeholder="{{ __('hrm.field_specific_job_ph') }}"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_employment_type') }} <span class="text-[#FF3B30]">*</span></label>
                                        <select name="employment_type" required class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                            <option value="permanent" :selected="selectedEmployee?.employment_type === 'permanent'">{{ __('hrm.emp_type_permanent') }}</option>
                                            <option value="contract" :selected="selectedEmployee?.employment_type === 'contract'">{{ __('hrm.emp_type_contract') }}</option>
                                            <option value="daily_worker" :selected="selectedEmployee?.employment_type === 'daily_worker'">{{ __('hrm.emp_type_daily') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_join_date') }}</label>
                                        <input type="date" name="join_date" :value="selectedEmployee?.join_date ? selectedEmployee.join_date.substring(0, 10) : ''"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_attendance_mode') }} <span class="text-[#FF3B30]">*</span></label>
                                        <select name="attendance_mode" required class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                            <option value="geofenced" :selected="selectedEmployee?.attendance_mode === 'geofenced'">{{ __('hrm.att_mode_geofenced') }}</option>
                                            <option value="free" :selected="selectedEmployee?.attendance_mode === 'free'">{{ __('hrm.att_mode_free') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_office_assignment') }}</label>
                                        <select name="primary_location_id" class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                            <option value="">{{ __('hrm.opt_default_store_loc') }}</option>
                                            @foreach($locations as $loc)
                                                <option value="{{ $loc->id }}" :selected="selectedEmployee?.primary_location_id === '{{ $loc->id }}'">{{ $loc->name }} (Radius: {{ $loc->geofence_radius_meters ?? 50 }}m)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">
                                        Shift Kerja Utama (Default)
                                    </label>
                                    <select name="default_shift_id" class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#FF9500]/40 focus:border-[#FF9500] transition">
                                        <option value="">Bebas Jadwal / Ikuti Roster Mingguan</option>
                                        @foreach($workShifts as $ws)
                                            <option value="{{ $ws->id }}" :selected="selectedEmployee?.default_shift_id === '{{ $ws->id }}'">
                                                {{ $ws->name }} ({{ $ws->start_time }} - {{ $ws->end_time }}{{ $ws->isOvernight() ? ' +1' : '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block mt-1">
                                        Shift acuan jika karyawan tidak memiliki jadwal spesifik pada tanggal absensi.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Kluster 4: Kepatuhan BPJS, Pajak PPh 21 TER & Biometrik -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/8">
                                <div class="flex items-center gap-2 text-[13px] font-bold text-black dark:text-white">
                                    <div class="w-6 h-6 rounded-lg bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ __('hrm.cluster_4_title') }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('hrm.cluster_4_sub') }}</span>
                            </div>

                            <div class="space-y-3.5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_tax_ptkp') }}</label>
                                        <select name="tax_ptkp_status" class="w-full h-11 sm:h-10.5 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#AF52DE]/40 focus:border-[#AF52DE] transition">
                                            <option value="TK/0" :selected="selectedEmployee?.tax_ptkp_status === 'TK/0'">TK/0 (Lajang 0 Tanggungan)</option>
                                            <option value="TK/1" :selected="selectedEmployee?.tax_ptkp_status === 'TK/1'">TK/1 (Lajang 1 Tanggungan)</option>
                                            <option value="TK/2" :selected="selectedEmployee?.tax_ptkp_status === 'TK/2'">TK/2 (Lajang 2 Tanggungan)</option>
                                            <option value="TK/3" :selected="selectedEmployee?.tax_ptkp_status === 'TK/3'">TK/3 (Lajang 3 Tanggungan)</option>
                                            <option value="K/0" :selected="selectedEmployee?.tax_ptkp_status === 'K/0'">K/0 (Menikah 0 Tanggungan)</option>
                                            <option value="K/1" :selected="selectedEmployee?.tax_ptkp_status === 'K/1'">K/1 (Menikah 1 Tanggungan)</option>
                                            <option value="K/2" :selected="selectedEmployee?.tax_ptkp_status === 'K/2'">K/2 (Menikah 2 Tanggungan)</option>
                                            <option value="K/3" :selected="selectedEmployee?.tax_ptkp_status === 'K/3'">K/3 (Menikah 3 Tanggungan)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.field_dependents_count') }}</label>
                                        <input type="number" name="bpjs_dependents_count" min="0" max="10" :value="selectedEmployee?.bpjs_dependents_count || 0"
                                            class="w-full h-11 sm:h-10.5 px-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#AF52DE]/40 focus:border-[#AF52DE] transition">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    <div class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/8 dark:border-white/8 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" id="edit_bpjs_tk" name="bpjs_tk_enabled" value="1" :checked="selectedEmployee?.bpjs_tk_enabled" class="w-4 h-4 rounded text-[#007AFF]">
                                            <label for="edit_bpjs_tk" class="text-[12px] font-bold text-black dark:text-white cursor-pointer">{{ __('hrm.bpjs_tk_label') }}</label>
                                        </div>
                                        <input type="text" name="bpjs_tk_number" :value="selectedEmployee?.bpjs_tk_number || ''" placeholder="{{ __('hrm.bpjs_tk_ph') }}"
                                            class="w-full h-10 sm:h-8.5 px-3 sm:px-2.5 rounded-[8px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-[11.5px] font-mono text-black dark:text-white">
                                    </div>
                                    <div class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/8 dark:border-white/8 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" id="edit_bpjs_kes" name="bpjs_kes_enabled" value="1" :checked="selectedEmployee?.bpjs_kes_enabled" class="w-4 h-4 rounded text-[#34C759]">
                                            <label for="edit_bpjs_kes" class="text-[12px] font-bold text-black dark:text-white cursor-pointer">{{ __('hrm.bpjs_kes_label') }}</label>
                                        </div>
                                        <input type="text" name="bpjs_kes_number" :value="selectedEmployee?.bpjs_kes_number || ''" placeholder="{{ __('hrm.bpjs_kes_ph') }}"
                                            class="w-full h-10 sm:h-8.5 px-3 sm:px-2.5 rounded-[8px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-[11.5px] font-mono text-black dark:text-white">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1">{{ __('hrm.update_biometric_initial') }}</label>
                                    <input type="file" name="photo" accept="image/*"
                                        class="w-full text-[16px] sm:text-[12px] text-black/70 dark:text-white/70 file:mr-3 file:py-2.5 file:px-3.5 file:rounded-[10px] file:border-0 file:text-[13px] sm:file:text-[11.5px] file:font-bold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20 transition cursor-pointer">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer Actions -->
                <div class="pt-4 flex items-center justify-between border-t border-black/5 dark:border-white/10">
                    <span class="text-[12px] text-black/40 dark:text-white/40 font-medium">{{ __('hrm.modal_edit_emp_footer_note') }}</span>
                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="showEditEmployeeModal = false"
                            class="h-11 sm:h-10.5 px-5 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                            {{ __('hrm.btn_cancel') }}
                        </button>
                        <button type="submit" :disabled="isSubmittingForm"
                            :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                            class="h-11 sm:h-10.5 px-6 rounded-[12px] text-[13.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] transition active:scale-[0.98] shadow-[0_2px_12px_rgba(0,122,255,0.3)] cursor-pointer flex items-center justify-center gap-2">
                            <template x-if="!isSubmittingForm">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>{{ __('hrm.btn_update_employee') }}</span>
                                </div>
                            </template>
                            <template x-if="isSubmittingForm">
                                <div class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>{{ __('hrm.saving') }}</span>
                                </div>
                            </template>
                        </button>
                    </div>
            </form>
                </div>
            </div>
        </div>
    </template>
