{{-- PANDUAN RINGKAS METODE 1-KLIK META WHATSAPP CLOUD API (APPLE HIG BENTO DESIGN) --}}
@php
    $guideMode = $mode ?? (auth('admin')->check() ? 'admin' : 'owner');
    $isAdminGuide = $guideMode === 'admin';
@endphp

<div class="rounded-[18px] bg-[#1877F2]/6 border border-[#1877F2]/15 p-4 sm:p-5 space-y-3">
    <div class="flex items-center gap-2.5 text-[#1877F2] font-bold text-[13.5px]">
        <div class="w-7 h-7 rounded-[10px] bg-[#1877F2] text-white flex items-center justify-center shrink-0 shadow-sm shadow-[#1877F2]/20">
            <i data-lucide="sparkles" class="w-4 h-4"></i>
        </div>
        <span>{{ __('whatsapp.guide_official_method') }}</span>
    </div>
    <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
        {{ __('whatsapp.guide_official_desc') }}
    </p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1 text-[11.5px]">
        <div class="p-2.5 rounded-[12px] bg-white/70 dark:bg-black/20 border border-black/5 dark:border-white/5">
            <span class="font-bold text-[#1877F2] block">{{ __('whatsapp.guide_step_1_title') }}</span>
            <span class="text-black/60 dark:text-white/60">{{ __('whatsapp.guide_step_1_desc') }}</span>
        </div>
        <div class="p-2.5 rounded-[12px] bg-white/70 dark:bg-black/20 border border-black/5 dark:border-white/5">
            <span class="font-bold text-[#1877F2] block">{{ __('whatsapp.guide_step_2_title') }}</span>
            <span class="text-black/60 dark:text-white/60">{{ __('whatsapp.guide_step_2_desc') }}</span>
        </div>
        <div class="p-2.5 rounded-[12px] bg-white/70 dark:bg-black/20 border border-black/5 dark:border-white/5">
            <span class="font-bold text-[#34C759] block">{{ __('whatsapp.guide_step_3_title') }}</span>
            <span class="text-black/60 dark:text-white/60">{{ __('whatsapp.guide_step_3_desc') }}</span>
        </div>
    </div>
</div>
