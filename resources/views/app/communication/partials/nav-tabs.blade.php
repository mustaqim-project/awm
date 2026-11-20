<div class="rounded-[14px] bg-white/75 dark:bg-[#1C1C1E]/75 backdrop-blur-md border border-black/5 dark:border-white/10 p-1.5 flex flex-wrap sm:flex-nowrap items-center gap-1 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
    <a href="{{ route('communication.settings.index') }}"
       class="flex-1 min-h-[38px] px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all flex items-center justify-center gap-2 {{ request()->routeIs('communication.settings.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_6px_rgba(0,122,255,0.25)]' : 'text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 hover:text-black dark:hover:text-white' }}">
        <i data-lucide="server" class="w-4 h-4"></i>
        <span>{{ __('communication.tab_settings') }}</span>
    </a>

    <a href="{{ route('communication.templates.index') }}"
       class="flex-1 min-h-[38px] px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all flex items-center justify-center gap-2 {{ request()->routeIs('communication.templates.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_6px_rgba(0,122,255,0.25)]' : 'text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 hover:text-black dark:hover:text-white' }}">
        <i data-lucide="file-edit" class="w-4 h-4"></i>
        <span>{{ __('communication.tab_templates') }}</span>
    </a>

    <a href="{{ route('communication.reminders.index') }}"
       class="flex-1 min-h-[38px] px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all flex items-center justify-center gap-2 {{ request()->routeIs('communication.reminders.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_6px_rgba(0,122,255,0.25)]' : 'text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 hover:text-black dark:hover:text-white' }}">
        <i data-lucide="clock" class="w-4 h-4"></i>
        <span>{{ __('communication.tab_reminders') }}</span>
    </a>

    <a href="{{ route('communication.inbound.index') }}"
       class="flex-1 min-h-[38px] px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all flex items-center justify-center gap-2 {{ request()->routeIs('communication.inbound.*') ? 'bg-[#007AFF] text-white shadow-[0_2px_6px_rgba(0,122,255,0.25)]' : 'text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 hover:text-black dark:hover:text-white' }}">
        <i data-lucide="inbox" class="w-4 h-4"></i>
        <span>{{ __('communication.tab_inbound') }}</span>
    </a>
</div>
