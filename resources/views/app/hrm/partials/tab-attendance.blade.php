<!-- ======================================================== -->
    <!-- TAB 2: PRESENSI WAJAH & REKAP HARIAN                     -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'attendance'" class="space-y-6" x-transition.opacity style="display: none;">
        <!-- 1. BENTO HERO: WIDGET PRESENSI MANDIRI & BIOMETRIK -->
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-6 shadow-[0_4px_20px_rgba(0,0,0,0.03)] space-y-5">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-[14px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="scan-face" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white">{{ __('hrm.anti_spoofing_face_attendance') }}</h3>
                        <p class="text-[12.5px] text-black/60 dark:text-white/60 font-medium">
                            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }} • {{ __('hrm.wib_timezone') }}
                        </p>
                    </div>
                </div>

                <!-- Digital Clock Display -->
                <div class="flex items-baseline gap-1 px-4 py-2.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10">
                    <span class="text-[28px] sm:text-[34px] font-extrabold tracking-tight tabular-nums text-black dark:text-white" x-text="liveClock">00:00</span>
                    <span class="text-[16px] sm:text-[18px] font-bold text-black/50 dark:text-white/50 tabular-nums" x-text="':' + liveSeconds">:00</span>
                    <span class="ml-1.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase">WIB</span>
                </div>
            </div>

            <!-- Geolocation Radar Status & Policy Indicator -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                <!-- Location Status Card -->
                <div class="p-4.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 md:col-span-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-[#007AFF]"></i>
                            <span class="text-[12.5px] font-bold text-black/80 dark:text-white/80">{{ __('hrm.location_geofence_status') }}</span>
                        </div>
                        <template x-if="gpsStatus === 'ready'">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                :class="isFreeLocation || (distanceMeters !== null && distanceMeters <= officeRadius) ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#FF3B30]/15 text-[#FF3B30]'">
                                <span class="w-2 h-2 rounded-full" :class="isFreeLocation || (distanceMeters !== null && distanceMeters <= officeRadius) ? 'bg-[#34C759]' : 'bg-[#FF3B30]'"></span>
                                <span x-text="isFreeLocation ? '{{ addslashes(__('hrm.geo_free_location')) }}' : (distanceMeters !== null && distanceMeters <= officeRadius ? '{{ addslashes(__('hrm.geo_in_range')) }}' : '{{ addslashes(__('hrm.geo_out_of_range')) }}')"></span>
                            </span>
                        </template>
                        <template x-if="gpsStatus === 'locating'">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF]">
                                <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                                <span>{{ __('hrm.searching_gps') }}</span>
                            </span>
                        </template>
                        <template x-if="gpsStatus === 'error' || gpsStatus === 'denied'">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ __('hrm.gps_disabled') }}</span>
                            </span>
                        </template>
                    </div>

                    <p class="text-[13px] font-bold text-black dark:text-white" x-text="gpsMessage">
                        {{ __('hrm.geo_connecting') }}
                    </p>

                    <div class="text-[11.5px] text-black/60 dark:text-white/60 flex flex-wrap items-center gap-x-3 gap-y-1 font-medium">
                        @if($currentUserMembership && $currentUserMembership->isFreeLocation())
                            <span>{{ __('hrm.policy_label') }} <strong class="text-[#007AFF]">{{ __('hrm.free_location_mode') }}</strong> {{ __('hrm.field_staff_note') }}</span>
                        @else
                            <span>{{ __('hrm.office_label') }} <strong>{{ $primaryLocation?->name ?? __('hrm.default_main_outlet') }}</strong></span>
                            <span>{{ __('hrm.radius_limit_label') }} <strong>{{ $primaryLocation?->geofence_radius_meters ?? 50 }} {{ __('hrm.meters_unit') }}</strong></span>
                        @endif
                        <template x-if="currentAccuracy">
                            <span>{{ __('hrm.sensor_accuracy_label') }} <strong class="tabular-nums" x-text="currentAccuracy + 'm'"></strong></span>
                        </template>
                    </div>
                </div>

                <!-- Anti-Spoofing & Privacy Notice Card -->
                <div class="p-4.5 rounded-[16px] bg-[#007AFF]/[0.04] border border-[#007AFF]/15 flex flex-col justify-between gap-2">
                    <div class="flex items-center gap-2 text-[#007AFF]">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span class="text-[11px] font-bold uppercase tracking-wider">{{ __('hrm.zero_photo_storage') }}</span>
                    </div>
                    <p class="text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed font-medium">
                        {{ __('hrm.zero_photo_desc') }}
                    </p>
                </div>
            </div>

            <!-- Action Controls Form -->
            <div class="pt-2">
                @if(!$currentUserAttendance || !$currentUserAttendance->clock_in_at)
                    <!-- ACTION CLOCK-IN MASUK -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <input type="text" x-model="attendanceNotes" placeholder="{{ __('hrm.attendance_notes_placeholder') }}"
                            class="flex-1 h-12 px-4 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:ring-2 focus:ring-[#34C759]/50 placeholder:text-black/40 dark:placeholder:text-white/40">

                        <button type="button" @click="startFaceAttendance('in')"
                            class="h-12 px-7 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[14px] font-bold flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(52,199,89,0.35)] transition active:scale-[0.98] cursor-pointer shrink-0">
                            <i data-lucide="scan-face" class="w-5 h-5"></i>
                            <span>{{ __('hrm.clock_in_face_recognition') }}</span>
                        </button>
                    </div>
                @elseif(!$currentUserAttendance->clock_out_at)
                    <!-- ACTION CLOCK-OUT PULANG -->
                    <div class="p-4.5 rounded-[16px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/20 text-[#34C759]">
                                    {{ __('hrm.currently_working') }}
                                </span>
                                <span class="text-[13.5px] font-bold text-black dark:text-white">
                                    Clock-In: {{ \App\Support\TimezoneHelper::formatTime($currentUserAttendance->clock_in_at, 'H:i', null, true) }}
                                </span>
                            </div>
                            <p class="text-[12px] text-black/70 dark:text-white/70 font-medium">
                                {{ __('hrm.entry_status_label') }} <strong class="{{ $currentUserAttendance->isLate() ? 'text-[#FF9500]' : 'text-[#34C759]' }}">{{ $currentUserAttendance->isLate() ? __('hrm.late_minutes_badge', ['min' => $currentUserAttendance->late_minutes]) : __('hrm.status_on_time') }}</strong>
                                @if($currentUserAttendance->clock_in_distance_meters)
                                    • {{ __('hrm.distance_from_office_note', ['dist' => $currentUserAttendance->clock_in_distance_meters]) }}
                                @endif
                            </p>
                        </div>

                        <button type="button" @click="startFaceAttendance('out')"
                            class="w-full sm:w-auto h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13.5px] font-bold flex items-center justify-center gap-2 shadow-[0_4px_14px_rgba(0,122,255,0.3)] transition active:scale-[0.98] cursor-pointer">
                            <i data-lucide="scan-face" class="w-4.5 h-4.5"></i>
                            <span>{{ __('hrm.clock_out_face_recognition') }}</span>
                        </button>
                    </div>
                @else
                    <!-- SELESAI HARI INI -->
                    <div class="p-4.5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/25 flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-full bg-[#34C759]/20 text-[#34C759] flex items-center justify-center shrink-0">
                                <i data-lucide="check-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="text-[14px] font-bold text-black dark:text-white">{{ __('hrm.workday_completed') }}</span>
                                <p class="text-[12px] text-black/70 dark:text-white/70 font-medium">
                                    {{ __('hrm.work_in_label') }} <strong>{{ $currentUserAttendance->clock_in_at?->format('H:i') }}</strong> •
                                    {{ __('hrm.work_out_label') }} <strong>{{ $currentUserAttendance->clock_out_at?->format('H:i') }}</strong> •
                                    {{ __('hrm.work_duration_label') }} <strong>{{ $currentUserAttendance->formatted_work_duration }}</strong>
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[11.5px] font-bold bg-[#34C759] text-white">
                            {{ __('hrm.completed') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. REKAP KPI ATTENDANCE HARI INI -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
            <div class="p-4.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 space-y-1 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <span class="text-[11.5px] font-bold text-black/60 dark:text-white/60 uppercase">{{ __('hrm.metric_today_present') }}</span>
                <div class="text-[22px] font-extrabold text-black dark:text-white tabular-nums">{{ $todayPresentCount }}</div>
                <p class="text-[11.5px] text-[#34C759] font-bold">{{ __('hrm.metric_present_sub') }}</p>
            </div>
            <div class="p-4.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 space-y-1 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <span class="text-[11.5px] font-bold text-black/60 dark:text-white/60 uppercase">{{ __('hrm.late_today') ?? 'Terlambat' }}</span>
                <div class="text-[22px] font-extrabold text-[#FF9500] tabular-nums">{{ $todayLateCount }}</div>
                <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">{{ __('hrm.metric_late_sub') }}</p>
            </div>
            <div class="p-4.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 space-y-1 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <span class="text-[11.5px] font-bold text-black/60 dark:text-white/60 uppercase">{{ __('hrm.metric_free_location') }}</span>
                <div class="text-[22px] font-extrabold text-[#007AFF] tabular-nums">{{ $todayFreeCount }}</div>
                <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">{{ __('hrm.metric_field_staff') }}</p>
            </div>
            <div class="p-4.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 space-y-1 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                <span class="text-[11.5px] font-bold text-black/60 dark:text-white/60 uppercase">{{ __('hrm.metric_pending_corrections') }}</span>
                <div class="text-[22px] font-extrabold {{ $pendingCorrectionsCount > 0 ? 'text-[#FF3B30]' : 'text-black dark:text-white' }} tabular-nums">{{ $pendingCorrectionsCount }}</div>
                <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">{{ __('hrm.metric_pending_sub') }}</p>
            </div>
        </div>

        <!-- 3. TABEL LOG REKAP PRESENSI HARIAN -->
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)] space-y-3">
            <form method="GET" action="{{ route('hrm.index') }}" class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex flex-wrap items-center justify-between gap-3">
                <input type="hidden" name="tab" value="attendance">

                <div class="flex flex-wrap items-center gap-3">
                    <div>
                        <label class="block text-[11px] uppercase font-bold text-black/60 dark:text-white/60 mb-1">{{ __('hrm.col_target_date') }}</label>
                        <input type="date" name="att_date" value="{{ $attDate }}"
                            class="h-11 sm:h-9 px-3 rounded-[10px] sm:rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-[11px] uppercase font-bold text-black/60 dark:text-white/60 mb-1">{{ __('hrm.col_requester_staff') }}</label>
                        <select name="att_user_id" class="h-11 sm:h-9 px-3 rounded-[10px] sm:rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                            <option value="">{{ __('hrm.filter_all_staff') }}</option>
                            @foreach($memberships as $m)
                                @if($m->user)
                                    <option value="{{ $m->user->id }}" {{ $attUserId === $m->user->id ? 'selected' : '' }}>{{ $m->user->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] uppercase font-bold text-black/60 dark:text-white/60 mb-1">{{ __('hrm.attendance_status_label') }}</label>
                        <select name="att_status" class="h-11 sm:h-9 px-3 rounded-[10px] sm:rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-semibold text-black dark:text-white focus:ring-1 focus:ring-[#007AFF]">
                            <option value="">{{ __('hrm.filter_all_status') }}</option>
                            <option value="present" {{ $attStatus === 'present' ? 'selected' : '' }}>{{ __('hrm.status_on_time') }}</option>
                            <option value="late" {{ $attStatus === 'late' ? 'selected' : '' }}>{{ __('hrm.late_today') ?? 'Terlambat' }}</option>
                            <option value="half_day" {{ $attStatus === 'half_day' ? 'selected' : '' }}>{{ __('hrm.status_half_day') }}</option>
                            <option value="absent" {{ $attStatus === 'absent' ? 'selected' : '' }}>{{ __('hrm.status_absent') }}</option>
                            <option value="leave" {{ $attStatus === 'leave' ? 'selected' : '' }}>{{ __('hrm.status_official_leave') }}</option>
                            <option value="sick" {{ $attStatus === 'sick' ? 'selected' : '' }}>{{ __('hrm.status_sick') }}</option>
                        </select>
                    </div>

                    <div class="flex items-end pt-5">
                        <button type="submit"
                            class="h-11 sm:h-9 px-4.5 rounded-[10px] sm:rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black dark:text-white text-[13.5px] sm:text-[12.5px] font-bold transition cursor-pointer">
                            {{ __('common.filter') }}
                        </button>
                    </div>
                </div>

                <div class="text-[12.5px] text-black/60 dark:text-white/60 font-medium">
                    {{ __('hrm.filter_records_count', ['count' => $attendances->total()]) }}
                </div>
            </form>

            <!-- Desktop Table View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[950px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.col_requester_staff') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_target_date') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_clock_in') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_clock_out') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_duration') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.th_location_distance') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_status') }}</th>
                            <th class="py-3.5 px-4 text-right">{{ __('hrm.th_biometric_verification') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($attendances as $att)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white">
                                    {{ $att->user?->name ?? 'Staf' }}
                                    <div class="text-[11.5px] text-black/50 dark:text-white/50 font-normal">{{ $att->user?->email }}</div>
                                </td>
                                <td class="py-3.5 px-3 text-black/70 dark:text-white/70 tabular-nums font-medium">
                                    {{ $att->date->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3.5 px-3 tabular-nums">
                                    @if($att->clock_in_at)
                                        <div class="font-bold text-black dark:text-white">
                                            {{ $att->clock_in_at->setTimezone($att->timezone ?? 'Asia/Jakarta')->format('H:i') }}
                                            <span class="text-[11px] font-semibold text-[#007AFF]">
                                                {{ \App\Support\TimezoneHelper::abbreviation($att->timezone ?? 'Asia/Jakarta') }}
                                            </span>
                                        </div>
                                        @if($att->clock_in_status === 'late' || $att->late_minutes > 0)
                                            <span class="text-[10.5px] text-[#FF3B30] font-bold">Terlambat {{ $att->late_minutes }}m</span>
                                        @elseif($att->early_in_minutes > 0)
                                            <span class="text-[10.5px] text-[#007AFF] font-bold">{{ $att->early_in_minutes }}m lebih awal</span>
                                        @elseif($att->clock_in_status === 'free_location')
                                            <span class="text-[10.5px] text-[#007AFF] font-bold">{{ __('hrm.geo_free_location') }}</span>
                                        @else
                                            <span class="text-[10.5px] text-[#34C759] font-bold">Tepat Waktu</span>
                                        @endif
                                        @if($att->shift_name)
                                            <div class="text-[10.5px] text-black/50 dark:text-white/50 mt-0.5">
                                                <span>{{ $att->shift_name }}</span>
                                                @if($att->scheduled_start_at)
                                                    <span>({{ $att->scheduled_start_at->setTimezone($att->timezone ?? 'Asia/Jakarta')->format('H:i') }})</span>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-black/40 dark:text-white/40">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 tabular-nums">
                                    @if($att->clock_out_at)
                                        <div class="font-bold text-black dark:text-white">
                                            {{ $att->clock_out_at->setTimezone($att->timezone ?? 'Asia/Jakarta')->format('H:i') }}
                                            <span class="text-[11px] font-semibold text-[#007AFF]">
                                                {{ \App\Support\TimezoneHelper::abbreviation($att->timezone ?? 'Asia/Jakarta') }}
                                            </span>
                                        </div>
                                        @if($att->early_out_minutes > 0)
                                            <span class="text-[10.5px] text-[#FF9500] font-bold">Pulang awal {{ $att->early_out_minutes }}m</span>
                                        @endif
                                    @else
                                        <span class="text-black/40 dark:text-white/40">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-semibold text-black/80 dark:text-white/80">
                                    {{ $att->formatted_work_duration }}
                                </td>
                                <td class="py-3.5 px-3 text-[12.5px] text-black/70 dark:text-white/70">
                                    @if($att->clock_in_distance_meters !== null)
                                        <span class="tabular-nums font-bold">{{ $att->clock_in_distance_meters }}m</span>
                                        <div class="text-[11px] text-black/50 dark:text-white/50">{{ $att->location?->name ?? __('hrm.branch_default') }}</div>
                                    @elseif($att->clock_in_status === 'free_location')
                                        <span class="text-[#007AFF] font-bold">{{ __('hrm.geo_free_location') }}</span>
                                    @else
                                        <span class="text-black/40 dark:text-white/40">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($att->status === 'present')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            {{ __('hrm.status_on_time') }}
                                        </span>
                                    @elseif($att->status === 'late')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                            {{ __('hrm.late_today') ?? 'Terlambat' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]">
                                            {{ ucfirst($att->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($att->face_verified)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30" title="{{ __('hrm.tooltip_verified_biometric') }}">
                                            <i data-lucide="scan-face" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.face_matched_percent', ['score' => round(($att->face_similarity_score ?? 0.9) * 100)]) }}</span>
                                        </span>
                                    @elseif($att->is_corrected)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.ticket_approved_badge') }}</span>
                                        </span>
                                    @else
                                        <span class="text-[11.5px] text-black/50 dark:text-white/50 font-medium">{{ __('hrm.sensor_gps_badge') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    {{ __('hrm.empty_attendance_filter') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden space-y-3 p-3.5 sm:p-4">
                @forelse($attendances as $att)
                    @php
                        $isPresent = ($att->status === 'present');
                        $isLate = ($att->status === 'late' || $att->clock_in_status === 'late');
                    @endphp
                    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/8 dark:border-white/10 space-y-3 shadow-xs">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-bold text-black dark:text-white text-[13.5px] truncate">
                                    {{ $att->user?->name ?? 'Staf' }}
                                </div>
                                <div class="text-[11.5px] text-black/50 dark:text-white/50 flex items-center gap-1.5 mt-0.5">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    <span>{{ $att->date->translatedFormat('d M Y') }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold {{ $isPresent ? 'bg-[#34C759]/15 text-[#34C759]' : ($isLate ? 'bg-[#FF9500]/15 text-[#FF9500]' : 'bg-[#AF52DE]/15 text-[#AF52DE]') }}">
                                    {{ ucfirst($att->status) }}
                                </span>
                                @if($att->face_verified)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#34C759]">
                                        <i data-lucide="scan-face" class="w-3 h-3"></i>
                                        <span>{{ __('hrm.face_verified_badge') }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-black/5 dark:border-white/5">
                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 space-y-0.5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('hrm.th_clock_in') }}</span>
                                <div class="font-bold text-black dark:text-white tabular-nums text-[12.5px]">
                                    {{ \App\Support\TimezoneHelper::formatTime($att->clock_in_at, 'H:i', null, true) }}
                                </div>
                                @if($att->clock_in_status === 'late')
                                    <span class="text-[10.5px] text-[#FF9500] font-bold block">{{ __('hrm.late_with_min', ['min' => $att->late_minutes]) }}</span>
                                @endif
                            </div>

                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 space-y-0.5">
                                <span class="text-[10px] uppercase font-bold text-black/50 dark:text-white/50 block">{{ __('hrm.th_clock_out') }}</span>
                                <div class="font-bold text-black dark:text-white tabular-nums text-[12.5px]">
                                    {{ \App\Support\TimezoneHelper::formatTime($att->clock_out_at, 'H:i', null, true) }}
                                </div>
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">{{ __('hrm.th_duration') }}: {{ $att->formatted_work_duration }}</span>
                            </div>
                        </div>

                        @if($att->clock_in_distance_meters !== null || $att->clock_in_status === 'free_location')
                            <div class="p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 flex items-center justify-between text-xs">
                                <span class="text-[11px] text-black/50 dark:text-white/50">{{ __('hrm.geofence_radius_label') }}</span>
                                @if($att->clock_in_status === 'free_location')
                                    <span class="text-[11px] font-bold text-[#007AFF]">{{ __('hrm.geo_free_location') }}</span>
                                @else
                                    <span class="font-mono font-bold text-black dark:text-white">{{ $att->clock_in_distance_meters }}m ({{ $att->location?->name ?? __('hrm.branch_default') }})</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-8 text-center text-black/50 dark:text-white/50 font-medium">
                        <i data-lucide="calendar-x" class="w-8 h-8 mx-auto mb-1.5 text-black/30 dark:text-white/30"></i>
                        <p class="text-xs">{{ __('hrm.empty_attendance_filter') }}</p>
                    </div>
                @endforelse
            </div>

            @if($attendances->total() > 0)
                <div class="p-3.5 border-t border-black/5 dark:border-white/10">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
