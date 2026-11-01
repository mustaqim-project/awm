<!-- ======================================================== -->
    <!-- TAB 4B: SHIFT KERJA KARYAWAN (WORK SHIFTS)               -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'shifts'" class="space-y-4" x-transition.opacity style="display: none;">
        <!-- Penjelasan Konsep Jam Operasional vs Shift Kerja (Bento Alert) -->
        <div class="p-4.5 rounded-[18px] bg-gradient-to-r from-[#30B0C7]/10 via-[#007AFF]/5 to-transparent border border-[#30B0C7]/20 flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-[11px] bg-[#30B0C7]/15 text-[#30B0C7] flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="info" class="w-5 h-5"></i>
            </div>
            <div class="text-xs sm:text-[13px] leading-relaxed text-black/80 dark:text-white/80">
                <strong class="font-bold text-black dark:text-white block mb-0.5">Pemisahan Jam Operasional Outlet & Shift Kerja:</strong>
                Operating hours outlet menentukan jam buka toko, sedangkan <strong>Shift Kerja</strong> menentukan jadwal wajib karyawan (Pagi, Siang, Malam/Overnight). Sistem menghitung keterlambatan dan kepulangan lebih awal secara otomatis berdasarkan shift aktif dan toleransi (grace period) yang dikonfigurasi.
            </div>
        </div>

        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <!-- Desktop Table View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[950px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.th_shift_name_code') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_shift_hours') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_break_net_hours') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_grace_period') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_branch_coverage') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_status') }}</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.th_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($workShifts as $ws)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $ws->color ?: '#30B0C7' }}"></span>
                                        <div>
                                            <span class="block text-[13.5px] font-bold">{{ $ws->name }}</span>
                                            @if($ws->code)
                                                <span class="text-[11px] font-mono text-black/50 dark:text-white/50 uppercase tracking-wider font-semibold">{{ $ws->code }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($ws->description)
                                        <div class="text-[11px] text-black/50 dark:text-white/50 font-normal mt-0.5 max-w-xs truncate">{{ $ws->description }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 tabular-nums">
                                    <div class="font-bold text-black dark:text-white text-[13.5px]">
                                        {{ $ws->start_time }} - {{ $ws->end_time }}
                                    </div>
                                    @if($ws->isOvernight())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-[#AF52DE]/15 text-[#AF52DE] border border-[#AF52DE]/30 mt-0.5">
                                            <i data-lucide="moon" class="w-3 h-3"></i>
                                            <span>{{ __('hrm.badge_overnight_shift') }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 tabular-nums text-black/80 dark:text-white/80 font-medium">
                                    <div>{{ round($ws->net_work_minutes / 60, 1) }}{{ __('hrm.unit_work_hours') }}</div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50">{{ __('hrm.break_prefix') }}{{ $ws->break_duration_minutes }} {{ __('hrm.unit_minutes') }}</div>
                                </td>
                                <td class="py-3.5 px-3 tabular-nums">
                                    @if($ws->grace_period_minutes > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                            {{ $ws->grace_period_minutes }}{{ __('hrm.unit_minutes_cap') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-500/10 text-slate-500">
                                            {{ __('hrm.opt_grace_0_strict') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-[12.5px] text-black/70 dark:text-white/70">
                                    {{ $ws->location?->name ?? __('hrm.all_locations_global') }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($ws->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            {{ __('hrm.status_active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50">
                                            {{ __('hrm.status_inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                            @click="openEditShift({{ Js::from($ws) }}, '{{ route('hrm.shifts.update', $ws->id) }}')"
                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all cursor-pointer shrink-0"
                                            title="{{ __('hrm.btn_edit') }}"
                                            aria-label="{{ __('hrm.btn_edit') }}">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        <form method="POST" action="{{ route('hrm.shifts.destroy', $ws->id) }}"
                                            onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_shift')) }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#FF3B30] hover:bg-[#FF3B30]/10 dark:hover:bg-[#FF3B30]/20 transition-all cursor-pointer shrink-0"
                                                title="{{ __('hrm.tooltip_delete_shift') ?? 'Hapus Shift' }}"
                                                aria-label="{{ __('hrm.tooltip_delete_shift') ?? 'Hapus Shift' }}">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    <i data-lucide="clock" class="w-8 h-8 mx-auto mb-2 text-black/30 dark:text-white/30"></i>
                                    <p class="text-xs">{{ __('hrm.empty_shift_desc') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden divide-y divide-black/5 dark:divide-white/5">
                @forelse($workShifts as $ws)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $ws->color ?: '#30B0C7' }}"></span>
                                <span class="font-bold text-black dark:text-white text-[14px] truncate">{{ $ws->name }}</span>
                                @if($ws->code)
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-black/5 dark:bg-white/10 font-bold uppercase">{{ $ws->code }}</span>
                                @endif
                            </div>
                            @if($ws->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759] shrink-0">{{ __('hrm.status_active') }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50 shrink-0">{{ __('hrm.status_inactive') }}</span>
                            @endif
                        </div>

                        <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.label_work_hours') }}</span>
                                <span class="font-bold font-mono text-[13px] text-black dark:text-white">{{ $ws->start_time }} - {{ $ws->end_time }}</span>
                            </div>
                            @if($ws->isOvernight())
                                <div class="text-[10.5px] font-bold text-[#AF52DE] flex items-center gap-1">
                                    <i data-lucide="moon" class="w-3 h-3"></i>
                                    <span>{{ __('hrm.badge_overnight_shift_long') }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between pt-1 border-t border-black/5 dark:border-white/5">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.label_grace_period') }}</span>
                                <span class="font-semibold">{{ $ws->grace_period_minutes > 0 ? $ws->grace_period_minutes . ' ' . __('hrm.unit_minutes') : __('hrm.opt_grace_0_strict_lower') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-black/55 dark:text-white/55">{{ __('hrm.label_location') }}</span>
                                <span class="font-semibold">{{ $ws->location?->name ?? __('hrm.label_all_locations') }}</span>
                            </div>
                        </div>

                        <div class="pt-1 flex items-center gap-2">
                            <button type="button"
                                @click="openEditShift({{ Js::from($ws) }}, '{{ route('hrm.shifts.update', $ws->id) }}')"
                                class="flex-1 h-10 px-3 rounded-[11px] text-[12.5px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#0071E3] hover:text-white transition active:scale-[0.98] flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>{{ __('hrm.btn_edit_shift') }}</span>
                            </button>
                            <form method="POST" action="{{ route('hrm.shifts.destroy', $ws->id) }}"
                                onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_shift')) }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="h-10 w-10 rounded-[11px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition flex items-center justify-center cursor-pointer shrink-0">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-black/50 dark:text-white/50 text-xs">
                        {{ __('hrm.empty_shift_short') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
