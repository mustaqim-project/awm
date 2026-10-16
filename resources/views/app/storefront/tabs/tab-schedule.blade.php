{{-- TAB 4: JAM OPERASIONAL & SLOT WAKTU --}}
@php
    $dayNames = [
        'monday'    => __('storefront.backoffice.tab_schedule.day_monday'),
        'tuesday'   => __('storefront.backoffice.tab_schedule.day_tuesday'),
        'wednesday' => __('storefront.backoffice.tab_schedule.day_wednesday'),
        'thursday'  => __('storefront.backoffice.tab_schedule.day_thursday'),
        'friday'    => __('storefront.backoffice.tab_schedule.day_friday'),
        'saturday'  => __('storefront.backoffice.tab_schedule.day_saturday'),
        'sunday'    => __('storefront.backoffice.tab_schedule.day_sunday'),
    ];
    $activeDays = is_array($setting->operating_days) ? $setting->operating_days : array_keys($dayNames);

    $slotList = '';
    if (! empty($setting->available_slots) && is_array($setting->available_slots)) {
        $slotList = implode("\n", $setting->available_slots);
    }

    $customBatchText = '';
    if (! empty($setting->custom_batch_dates) && is_array($setting->custom_batch_dates)) {
        $lines = [];
        foreach ($setting->custom_batch_dates as $cbd) {
            $dateStr = $cbd['date'] ?? '';
            if ($dateStr !== '') {
                $quotaPart = isset($cbd['quota']) && $cbd['quota'] !== null ? " : {$cbd['quota']}" : '';
                $notePart = ! empty($cbd['note']) ? " : {$cbd['note']}" : '';
                $lines[] = "{$dateStr}{$quotaPart}{$notePart}";
            }
        }
        $customBatchText = implode("\n", $lines);
    }
@endphp

<div class="space-y-6" x-data="{ batchMode: '{{ $setting->batch_dates_mode ?? 'operating_days' }}' }">
    {{-- HARI OPERASIONAL & SLOT --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2.5 pb-4 border-b border-black/5 dark:border-white/10">
            <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                <i data-lucide="clock" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">{{ __('storefront.backoffice.tab_schedule.hours_slots_title') }}</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('storefront.backoffice.tab_schedule.hours_slots_desc') }}</p>
            </div>
        </div>

        <div>
            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-2.5">
                {{ __('storefront.backoffice.tab_schedule.select_operating_days') }}
            </label>
            <div class="flex flex-wrap gap-2">
                @foreach ($dayNames as $dKey => $dLabel)
                    <label class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 text-[12.5px] font-medium text-black dark:text-white cursor-pointer hover:border-[#007AFF] transition select-none">
                        <input type="checkbox" name="operating_days[]" value="{{ $dKey }}"
                            class="rounded border-gray-300 text-[#007AFF] focus:ring-[#007AFF]"
                            {{ in_array($dKey, $activeDays, true) ? 'checked' : '' }}>
                        <span>{{ $dLabel }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                {{ __('storefront.backoffice.tab_schedule.slots_label') }}
            </label>
            <textarea name="available_slots" rows="3"
                class="w-full p-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"
                placeholder="09:00 - 11:00&#10;11:00 - 13:00&#10;14:00 - 16:00&#10;16:00 - 18:00">{{ $slotList }}</textarea>
            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">{{ __('storefront.backoffice.tab_schedule.slots_hint') }}</p>
        </div>
    </div>

    {{-- ATURAN PRE-ORDER & LEAD TIME --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2.5 pb-4 border-b border-black/5 dark:border-white/10">
            <div class="w-9 h-9 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold">
                <i data-lucide="timer" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">{{ __('storefront.backoffice.tab_schedule.lead_time_title') }}</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('storefront.backoffice.tab_schedule.lead_time_desc') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    {{ __('storefront.backoffice.tab_schedule.lead_time_hours_label') }}
                </label>
                <input type="number" name="lead_time_hours" value="{{ $setting->lead_time_hours ?? 0 }}" min="0" max="720"
                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] tabular-nums">
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">{{ __('storefront.backoffice.tab_schedule.lead_time_hours_hint') }}</p>
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    {{ __('storefront.backoffice.tab_schedule.cutoff_time_label') }}
                </label>
                <input type="time" name="cut_off_time" value="{{ $setting->cut_off_time }}"
                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">{{ __('storefront.backoffice.tab_schedule.cutoff_time_hint') }}</p>
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    {{ __('storefront.backoffice.tab_schedule.daily_quota_label') }}
                </label>
                <input type="number" name="daily_order_quota" value="{{ $setting->daily_order_quota ?? 0 }}" min="0" max="10000"
                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] tabular-nums">
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">{{ __('storefront.backoffice.tab_schedule.daily_quota_hint') }}</p>
            </div>
        </div>

        {{-- MODE TANGGAL BATCH (KATERING & PO) --}}
        <div class="pt-2 border-t border-black/5 dark:border-white/10 space-y-3">
            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                {{ __('storefront.backoffice.tab_schedule.date_selection_mode_label') }}
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="flex items-start gap-3 p-3.5 rounded-[14px] border cursor-pointer transition"
                    :class="batchMode === 'operating_days' ? 'bg-[#007AFF]/5 border-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/5 dark:border-white/10'">
                    <input type="radio" name="batch_dates_mode" value="operating_days" x-model="batchMode"
                        class="mt-1 text-[#007AFF] focus:ring-[#007AFF]">
                    <div>
                        <span class="text-[13px] font-bold text-black dark:text-white block">{{ __('storefront.backoffice.tab_schedule.mode_regular_days_title') }}</span>
                        <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">{{ __('storefront.backoffice.tab_schedule.mode_regular_days_desc') }}</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3.5 rounded-[14px] border cursor-pointer transition"
                    :class="batchMode === 'custom_dates' ? 'bg-[#007AFF]/5 border-[#007AFF]' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/5 dark:border-white/10'">
                    <input type="radio" name="batch_dates_mode" value="custom_dates" x-model="batchMode"
                        class="mt-1 text-[#007AFF] focus:ring-[#007AFF]">
                    <div>
                        <span class="text-[13px] font-bold text-black dark:text-white block">{{ __('storefront.backoffice.tab_schedule.mode_custom_batches_title') }}</span>
                        <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">{{ __('storefront.backoffice.tab_schedule.mode_custom_batches_desc') }}</p>
                    </div>
                </label>
            </div>

            <div x-show="batchMode === 'custom_dates'" x-cloak class="pt-2">
                <label class="block text-[11.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    {{ __('storefront.backoffice.tab_schedule.custom_batch_dates_label') }}
                </label>
                <textarea name="custom_batch_dates" rows="3"
                    class="w-full p-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"
                    placeholder="2026-10-01 : 150 : Batch 1 Jumat&#10;2026-10-08 : 150 : Batch 2 Jumat">{{ $customBatchText }}</textarea>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">{{ __('storefront.backoffice.tab_schedule.custom_batch_dates_hint') }}</p>
            </div>
        </div>
    </div>
</div>
