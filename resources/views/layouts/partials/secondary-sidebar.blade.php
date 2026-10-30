@php
    $activeModuleKey = $activeModuleKey ?? null;
    $activeModuleDef = $activeModuleDef ?? (!empty($activeModuleKey) ? \App\Support\Navigation\NavigationRegistry::getModule($activeModuleKey) : null);
    $secondaryNavTabs = $secondaryNavTabs ?? (!empty($activeModuleKey) ? \App\Support\Navigation\NavigationRegistry::getTabsForModule($activeModuleKey) : []);
@endphp

@if (!empty($secondaryNavTabs))
    <aside id="secondary-sidebar"
        data-secondary-sidebar="{{ $activeModuleKey }}"
        aria-label="{{ __('common.sub_navigation') }}"
        :class="sidebarCollapsed ? 'lg:left-[76px]' : 'lg:left-[272px]'"
        class="hidden lg:flex fixed inset-y-0 z-30 w-[260px] flex-col justify-between bg-white dark:bg-[#1C1C1E] border-r border-black/5 dark:border-white/10 transition-[left] duration-250 ease-out select-none">

        {{-- Top Header: Module Label & Item Count --}}
        <div class="h-14 flex items-center justify-between px-5 border-b border-black/5 dark:border-white/10 shrink-0">
            <span class="text-[11px] font-bold tracking-wider text-black/50 dark:text-white/50 uppercase truncate">
                {{ strtoupper(__('common.section')) }} {{ strtoupper($activeModuleDef['label'] ?? $activeModuleKey) }}
            </span>
            <span class="text-[11.5px] font-semibold text-[#007AFF] shrink-0 ml-2">
                {{ count($secondaryNavTabs) }} {{ __('common.parts') }}
            </span>
        </div>

        {{-- Sub-Navigation Item List --}}
        <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-1.5 overscroll-contain">
            @foreach ($secondaryNavTabs as $index => $tab)
                <a href="{{ $tab['url'] }}"
                    data-subnav-key="{{ $tab['key'] }}"
                    data-active-secondary-nav="{{ $tab['is_active'] ? 'true' : 'false' }}"
                    class="group w-full flex items-center justify-between px-3.5 py-2.5 rounded-[12px] text-[13px] transition-all {{ $tab['is_active'] ? 'bg-[#007AFF] text-white font-semibold shadow-[0_2px_8px_rgba(0,122,255,0.3)]' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white font-medium' }}">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] shrink-0 {{ $tab['is_active'] ? 'bg-white/25 text-white font-bold' : 'bg-black/[0.06] dark:bg-white/10 text-black/50 dark:text-white/50 font-medium group-hover:bg-black/10 dark:group-hover:bg-white/15' }}">
                            {{ $index + 1 }}
                        </span>
                        <span class="truncate">{{ $tab['label'] }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0 ml-2">
                        @if (!empty($tab['badge']))
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $tab['is_active'] ? 'bg-white/20 text-white' : 'bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50' }}">
                                {{ $tab['badge'] }}
                            </span>
                        @endif
                        @if ($tab['is_active'])
                            <i data-lucide="chevron-right" class="w-4 h-4 text-white shrink-0 group-hover:translate-x-0.5 transition-transform"></i>
                        @endif
                    </div>
                </a>
            @endforeach
        </nav>

        {{-- Bottom Status Note / Footer --}}
        <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/10 shrink-0">
            <div class="flex items-center justify-center gap-1.5 text-[11.5px] text-black/40 dark:text-white/40 font-medium">
                <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759]"></i>
                <span>{{ __('common.changes_saved_auto') }}</span>
            </div>
        </div>
    </aside>
@endif
