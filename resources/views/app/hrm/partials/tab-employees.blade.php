<!-- ======================================================== -->
    <!-- TAB 1: KARYAWAN & PROFIL GAJI                            -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'employees'" class="space-y-4" x-transition.opacity>
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <!-- Desktop Table View (>= 768px) -->
            <div x-data="coocaTableSort()" class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[1050px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <x-table-sort-th :col="0" type="string" :title="__('hrm.th_employee_identity')" class="sm:px-5" />
                            <x-table-sort-th :col="1" type="string" :title="__('hrm.th_job_role')" />
                            <x-table-sort-th :col="2" type="string" :title="__('hrm.th_employment_type')" />
                            <x-table-sort-th :col="3" type="number" :title="__('hrm.th_wage_structure')" />
                            <x-table-sort-th :col="4" type="string" :title="__('hrm.th_bpjs_social')" />
                            <x-table-sort-th :col="5" type="string" :title="__('hrm.th_biometric_face')" />
                            <x-table-sort-th :col="6" type="string" :title="__('hrm.th_bank_account')" />
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($memberships as $m)
                            @php
                                $u = $m->user;
                                $isOwner = ($m->role === 'owner');
                                $hasFace = $m->hasFaceRegistered();
                                $salaryVal = (float)($m->employment_type === 'daily_worker' ? $m->daily_rate : $m->base_salary);
                            @endphp
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <!-- Karyawan -->
                                <td data-sort-value="{{ strtolower($u->name ?? 'User') }}" class="py-3.5 px-4 sm:px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full {{ $hasFace ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#007AFF]/12 text-[#007AFF]' }} font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ substr($u->name ?? 'K', 0, 2) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-black dark:text-white truncate text-[13.5px]">
                                                {{ $u->name ?? 'User' }}
                                                @if($isOwner)
                                                    <span class="ml-1 text-[10px] px-2 py-0.5 rounded-full bg-[#FF9500]/15 text-[#FF9500] font-bold">{{ __('hrm.status_owner') }}</span>
                                                @endif
                                            </div>
                                            <div class="text-[11.5px] text-black/55 dark:text-white/55 truncate">
                                                {{ $u->email ?? '-' }}
                                            </div>
                                            <div class="text-[11px] text-black/45 dark:text-white/45 flex flex-wrap items-center gap-2 mt-0.5">
                                                @if($m->nik_ktp)
                                                    <span>{{ __('hrm.nik_prefix') }} <strong class="font-mono text-black/70 dark:text-white/70">{{ $m->nik_ktp }}</strong></span>
                                                @endif
                                                @if($m->whatsapp_number)
                                                    <span class="text-[#25D366] font-medium flex items-center gap-0.5">
                                                        <i data-lucide="phone" class="w-3 h-3"></i>
                                                        {{ $m->whatsapp_number }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Jabatan & Peran -->
                                <td data-sort-value="{{ strtolower($m->job_title ?: ucfirst($m->role)) }}" class="py-3.5 px-3">
                                    <div class="font-semibold text-black dark:text-white">{{ $m->job_title ?: ucfirst($m->role) }}</div>
                                    <div class="text-[11.5px] text-black/50 dark:text-white/50">{{ $m->customRole?->name ?? ucfirst($m->role) }}</div>
                                </td>

                                <!-- Tipe Kerja -->
                                <td data-sort-value="{{ $m->employment_type }}" class="py-3.5 px-3">
                                    @if($m->employment_type === 'daily_worker')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                            {{ __('hrm.daily_worker_badge') }}
                                        </span>
                                    @elseif($m->employment_type === 'contract')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]">
                                            {{ __('portal.employment_type_contract') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            {{ __('portal.employment_type_permanent') }}
                                        </span>
                                    @endif
                                    @if($m->join_date)
                                        <div class="text-[11px] text-black/50 dark:text-white/50 mt-1 font-medium">
                                            Masuk: {{ \Carbon\Carbon::parse($m->join_date)->translatedFormat('d M Y') }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Struktur Upah -->
                                <td data-sort-value="{{ $salaryVal }}" class="py-3.5 px-3">
                                    @if($m->employment_type === 'daily_worker')
                                        <div class="font-bold text-black dark:text-white tabular-nums">
                                            Rp {{ number_format((float)$m->daily_rate, 0, ',', '.') }} <span class="text-[10.5px] font-normal text-black/50 dark:text-white/50">{{ __('hrm.per_day_suffix') }}</span>
                                        </div>
                                    @else
                                        <div class="font-bold text-black dark:text-white tabular-nums">
                                            Rp {{ number_format((float)$m->base_salary, 0, ',', '.') }}
                                        </div>
                                        @if($m->fixed_allowances > 0 || $m->variable_allowances > 0)
                                            <div class="text-[11.5px] text-black/60 dark:text-white/60 tabular-nums font-medium">
                                                + Tunj: Rp {{ number_format((float)($m->fixed_allowances + $m->variable_allowances), 0, ',', '.') }}
                                            </div>
                                        @endif
                                    @endif
                                </td>

                                <!-- BPJS & Tanggungan -->
                                <td data-sort-value="{{ $m->tax_ptkp_status ?: 'TK/0' }}" class="py-3.5 px-3">
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap gap-1 items-center">
                                            <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80">
                                                {{ $m->tax_ptkp_status ?: 'TK/0' }}
                                            </span>
                                            @if($m->bpjs_tk_enabled)
                                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-[#007AFF]/12 text-[#007AFF]" title="{{ $m->bpjs_tk_number ? 'No: '.$m->bpjs_tk_number : __('portal.active') }}">
                                                    BPJS TK
                                                </span>
                                            @endif
                                            @if($m->bpjs_kes_enabled)
                                                <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-[#34C759]/12 text-[#34C759]" title="{{ $m->bpjs_kes_number ? 'No: '.$m->bpjs_kes_number : __('portal.active') }}">
                                                    BPJS Kes
                                                </span>
                                            @endif
                                        </div>
                                        @if($m->bpjs_dependents_count > 0)
                                            <div class="text-[11px] text-black/60 dark:text-white/60 font-medium">
                                                {{ __('portal.dependents_label') }} <strong class="text-[#007AFF]">{{ $m->bpjs_dependents_count }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Status Biometrik Wajah -->
                                <td data-sort-value="{{ $hasFace ? '1_enrolled' : '0_unenrolled' }}" class="py-3.5 px-3">
                                    @if($hasFace)
                                        <button type="button"
                                            @click="openFaceRegister({{ Js::from($m) }}, '{{ route('hrm.employees.face-register', $m->id) }}')"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30 hover:bg-[#34C759]/25 transition cursor-pointer"
                                            title="{{ __('hrm.tooltip_manage_face') }}">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.enrolled') }}</span>
                                        </button>
                                    @else
                                        <button type="button"
                                            @click="openFaceRegister({{ Js::from($m) }}, '{{ route('hrm.employees.face-register', $m->id) }}')"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500] hover:bg-[#FF9500]/25 transition cursor-pointer"
                                            title="{{ __('hrm.tooltip_manage_face') }}">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.enroll_face') }}</span>
                                        </button>
                                    @endif
                                </td>

                                <!-- Rekening Bank -->
                                <td data-sort-value="{{ strtolower($m->bank_name ?: ($m->bank_account_number ? 'bank' : 'tunai')) }}" class="py-3.5 px-3">
                                    @if($m->bank_account_number)
                                        <div class="font-semibold text-black dark:text-white">{{ $m->bank_name ?: 'Bank' }}</div>
                                        <div class="text-[11.5px] text-black/60 dark:text-white/60 tabular-nums font-mono">{{ $m->bank_account_number }}</div>
                                    @else
                                        <span class="text-[11.5px] text-black/40 dark:text-white/40 font-medium">{{ __('hrm.cash_method') }}</span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                            @click="openEditEmployee({{ Js::from($m) }}, '{{ route('hrm.employees.update', $m->id) }}')"
                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all cursor-pointer shrink-0"
                                            title="{{ __('hrm.btn_edit') }}"
                                            aria-label="{{ __('hrm.btn_edit') }}">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>

                                        @if(! $isOwner)
                                            <form method="POST" action="{{ route('hrm.employees.destroy', $m->id) }}"
                                                onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_employee')) }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#FF3B30] hover:bg-[#FF3B30]/10 dark:hover:bg-[#FF3B30]/20 transition-all cursor-pointer shrink-0"
                                                    title="{{ __('hrm.tooltip_delete_employee') }}"
                                                    aria-label="{{ __('hrm.tooltip_delete_employee') }}">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    {{ __('hrm.empty_staff_list') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden space-y-3.5 p-3.5 sm:p-4">
                @forelse($memberships as $m)
                    @php
                        $u = $m->user;
                        $isOwner = ($m->role === 'owner');
                        $hasFace = $m->hasFaceRegistered();
                    @endphp
                    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-3.5 shadow-xs">
                        <!-- Top: Profile Header & Badge -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-full {{ $hasFace ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#007AFF]/12 text-[#007AFF]' }} font-bold text-sm flex items-center justify-center shrink-0">
                                    {{ substr($u->name ?? 'K', 0, 2) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-black dark:text-white truncate text-[14px]">
                                        {{ $u->name ?? 'User' }}
                                    </div>
                                    <div class="text-[12px] text-black/60 dark:text-white/60 truncate">
                                        {{ $m->job_title ?: ucfirst($m->role) }} • {{ $m->customRole?->name ?? ucfirst($m->role) }}
                                    </div>
                                    <div class="text-[11.5px] text-black/45 dark:text-white/45 truncate">
                                        {{ $u->email ?? '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                @if($isOwner)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#FF9500]/15 text-[#FF9500] font-bold">Owner</span>
                                @endif
                                @if($m->employment_type === 'daily_worker')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500]">{{ __('hrm.badges.daily') }}</span>
                                @elseif($m->employment_type === 'contract')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]">{{ __('hrm.badges.contract') }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759]">{{ __('hrm.badges.permanent') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Mid: 2-Col Data Chips -->
                        <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-black/5 dark:border-white/5">
                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 space-y-0.5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('hrm.table.salary_compensation') }}</span>
                                <div class="font-bold text-black dark:text-white tabular-nums text-[12.5px]">
                                    @if($m->employment_type === 'daily_worker')
                                        Rp {{ number_format((float)$m->daily_rate, 0, ',', '.') }}<span class="text-[10px] font-normal text-black/50">/{{ __('hrm.table.day_short') }}</span>
                                    @else
                                        Rp {{ number_format((float)$m->base_salary, 0, ',', '.') }}
                                    @endif
                                </div>
                                @if($m->fixed_allowances > 0)
                                    <span class="text-[10.5px] text-[#34C759] font-medium tabular-nums block">+{{ __('hrm.table.allowance_short') }} Rp {{ number_format((float)$m->fixed_allowances, 0, ',', '.') }}</span>
                                @endif
                            </div>

                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 space-y-0.5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('hrm.table.bpjs_ptkp') }}</span>
                                <div class="flex items-center gap-1 flex-wrap">
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">{{ $m->tax_ptkp_status ?: 'TK/0' }}</span>
                                    @if($m->bpjs_tk_enabled)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#007AFF]/15 text-[#007AFF]">TK</span>
                                    @endif
                                    @if($m->bpjs_kes_enabled)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#34C759]/15 text-[#34C759]">Kes</span>
                                    @endif
                                </div>
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">{{ __('hrm.table.dependents') }}: {{ $m->bpjs_dependents_count ?: 0 }} {{ __('hrm.table.people') }}</span>
                            </div>

                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 space-y-0.5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('hrm.table.face_biometric') }}</span>
                                @if($hasFace)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#34C759]">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                        <span>{{ __('hrm.badges.registered') }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#FF9500]">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                        <span>{{ __('hrm.badges.not_registered') }}</span>
                                    </span>
                                @endif
                            </div>

                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 space-y-0.5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('hrm.table.payroll_account') }}</span>
                                <span class="text-[11px] font-semibold text-black dark:text-white truncate block">
                                    {{ $m->bank_account_number ? ($m->bank_name ?: 'Bank') . ' ' . $m->bank_account_number : __('hrm.table.cash') }}
                                </span>
                            </div>
                        </div>

                        <!-- Bottom: Touch-Friendly Action Buttons (>= 44px) -->
                        <div class="flex items-center gap-2 pt-1 border-t border-black/5 dark:border-white/5">
                            <button type="button"
                                @click="openFaceRegister({{ Js::from($m) }}, '{{ route('hrm.employees.face-register', $m->id) }}')"
                                class="flex-1 h-11 px-3 rounded-[12px] text-[12.5px] font-bold {{ $hasFace ? 'text-[#34C759] bg-[#34C759]/10 hover:bg-[#34C759]/20' : 'text-[#FF9500] bg-[#FF9500]/10 hover:bg-[#FF9500]/20' }} active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="scan-face" class="w-4 h-4"></i>
                                <span>{{ $hasFace ? __('hrm.actions.change_face') : __('hrm.actions.register_face') }}</span>
                            </button>

                            <button type="button"
                                @click="openEditEmployee({{ Js::from($m) }}, '{{ route('hrm.employees.update', $m->id) }}')"
                                class="flex-1 h-11 px-3 rounded-[12px] text-[12.5px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                <span>{{ __('hrm.actions.edit_profile') }}</span>
                            </button>

                            @if(! $isOwner)
                                <form method="POST" action="{{ route('hrm.employees.destroy', $m->id) }}"
                                    onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_employee')) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="h-11 w-11 rounded-[12px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all flex items-center justify-center cursor-pointer shrink-0"
                                        title="{{ __('hrm.actions.delete_staff') }}"
                                        aria-label="{{ __('hrm.actions.delete_staff') }}">
                                        <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-black/50 dark:text-white/50 font-medium">
                        <i data-lucide="users" class="w-8 h-8 mx-auto mb-1.5 text-black/30 dark:text-white/30"></i>
                        <p class="text-xs">{{ __('hrm.empty.no_staff') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
