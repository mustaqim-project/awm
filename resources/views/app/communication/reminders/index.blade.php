@extends('layouts.app', [
    'title' => __('communication.reminders_title'),
    'headerTitle' => __('communication.module_title'),
    'headerSubtitle' => __('communication.reminders_subtitle')
])

@section('content')
<div class="max-w-[1000px] mx-auto space-y-6 pb-20">
    <!-- 1. NAVIGATION TABS -->
    @include('app.communication.partials.nav-tabs')

    <form action="{{ route('communication.reminders.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 2. MASTER TOGGLE & PREFERRED HOUR (Bento HIG) -->
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-8 space-y-6 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-black/5 dark:border-white/10 pb-6">
                <div>
                    <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                        <i data-lucide="bell-ring" class="w-5 h-5 text-[#007AFF]"></i>
                        <span>{{ __('communication.reminder_general_card') }}</span>
                    </h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-1 max-w-xl">
                        {{ __('communication.auto_remind_invoices_help') }}
                    </p>
                </div>

                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" name="auto_remind_invoices" value="1" class="sr-only peer" {{ ($settings->auto_remind_invoices ?? true) ? 'checked' : '' }}>
                    <div class="w-12 h-6.5 bg-black/10 peer-focus:outline-none rounded-full peer dark:bg-white/10 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5.5 after:w-5.5 after:transition-all peer-checked:bg-[#34C759]"></div>
                </label>
            </div>

            <!-- Send Hour Preference -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                <div>
                    <label class="block text-[13px] font-semibold text-black dark:text-white mb-1">
                        {{ __('communication.preferred_send_hour') }}
                    </label>
                    <p class="text-[12px] text-black/50 dark:text-white/50 leading-relaxed">
                        {{ __('communication.preferred_send_hour_help') }}
                    </p>
                </div>
                <div>
                    <select name="preferred_send_hour" class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        @for($h = 0; $h <= 23; $h++)
                            @php $strH = sprintf('%02d:00', $h); @endphp
                            <option value="{{ $h }}" {{ old('preferred_send_hour', $settings->preferred_send_hour ?? 9) == $h ? 'selected' : '' }}>
                                {{ $strH }} WIB (Waktu Bisnis)
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. STAGES / SCHEDULE CHECKLIST CARDS -->
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-8 space-y-5 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
            <div class="border-b border-black/5 dark:border-white/10 pb-4">
                <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-[#34C759]"></i>
                    <span>{{ __('communication.schedule_stages') }}</span>
                </h3>
            </div>

            @php
                $stages = [
                    [
                        'name' => 'remind_h_minus_3',
                        'checked' => $settings->remind_h_minus_3 ?? true,
                        'title' => __('communication.remind_h_minus_3'),
                        'desc' => __('communication.remind_h_minus_3_desc'),
                        'tag' => 'H - 3',
                        'badge_color' => 'bg-[#007AFF]/10 text-[#007AFF]'
                    ],
                    [
                        'name' => 'remind_due_date',
                        'checked' => $settings->remind_due_date ?? true,
                        'title' => __('communication.remind_due_date'),
                        'desc' => __('communication.remind_due_date_desc'),
                        'tag' => 'Hari H',
                        'badge_color' => 'bg-[#FF9500]/10 text-[#FF9500]'
                    ],
                    [
                        'name' => 'remind_h_plus_3',
                        'checked' => $settings->remind_h_plus_3 ?? true,
                        'title' => __('communication.remind_h_plus_3'),
                        'desc' => __('communication.remind_h_plus_3_desc'),
                        'tag' => 'H + 3',
                        'badge_color' => 'bg-[#FF3B30]/10 text-[#FF3B30]'
                    ],
                    [
                        'name' => 'remind_h_plus_7',
                        'checked' => $settings->remind_h_plus_7 ?? true,
                        'title' => __('communication.remind_h_plus_7'),
                        'desc' => __('communication.remind_h_plus_7_desc'),
                        'tag' => 'H + 7',
                        'badge_color' => 'bg-[#FF3B30]/10 text-[#FF3B30]'
                    ],
                    [
                        'name' => 'remind_weekly_overdue',
                        'checked' => $settings->remind_weekly_overdue ?? true,
                        'title' => __('communication.remind_weekly_overdue'),
                        'desc' => __('communication.remind_weekly_overdue_desc'),
                        'tag' => 'Mingguan',
                        'badge_color' => 'bg-[#AF52DE]/10 text-[#AF52DE]'
                    ],
                ];
            @endphp

            <div class="space-y-3.5">
                @foreach($stages as $stage)
                    <label class="flex items-start gap-4 p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 hover:border-[#007AFF]/30 transition-all cursor-pointer">
                        <input type="checkbox" name="{{ $stage['name'] }}" value="1" class="mt-1 w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF] border-black/20 dark:border-white/20" {{ $stage['checked'] ? 'checked' : '' }}>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-[14px] font-semibold text-black dark:text-white">{{ $stage['title'] }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $stage['badge_color'] }}">{{ $stage['tag'] }}</span>
                            </div>
                            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5 leading-relaxed">
                                {{ $stage['desc'] }}
                            </p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- 4. SAVE BUTTON -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit" class="h-11 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.25)]">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>{{ __('communication.save_reminders') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection
