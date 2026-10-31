@php
    $activeModuleKey = $activeModuleKey ?? null;
    $activeModuleDef = $activeModuleDef ?? (!empty($activeModuleKey) ? \App\Support\Navigation\NavigationRegistry::getModule($activeModuleKey) : null);
    $secondaryNavTabs = $secondaryNavTabs ?? (!empty($activeModuleKey) ? \App\Support\Navigation\NavigationRegistry::getTabsForModule($activeModuleKey) : []);
@endphp

@if (!empty($secondaryNavTabs))
    {{-- Secondary Sidebar (Desktop Dual Sidebar Layout for Active Module) --}}
    <aside id="secondary-sidebar"
        data-secondary-sidebar="{{ $activeModuleKey }}"
        aria-label="{{ __('common.sub_navigation') }}"
        :class="{
            'lg:left-[76px]': sidebarCollapsed,
            'lg:left-[272px]': !sidebarCollapsed,
            'lg:-translate-x-[270px] lg:opacity-0 lg:pointer-events-none': secondarySidebarCollapsed,
            'lg:translate-x-0 lg:opacity-100': !secondarySidebarCollapsed
        }"
        class="hidden lg:flex fixed inset-y-0 z-30 w-[260px] flex-col justify-between bg-white dark:bg-[#1C1C1E] border-r border-black/5 dark:border-white/10 transition-all duration-250 ease-out select-none shadow-[2px_0_12px_rgba(0,0,0,0.02)]">

        {{-- Top Header: Module Label, Item Count & Collapse Button --}}
        <div class="h-14 flex items-center justify-between px-4 border-b border-black/5 dark:border-white/10 shrink-0">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-[11px] font-bold tracking-wider text-black/50 dark:text-white/50 uppercase truncate">
                    {{ strtoupper(__('common.section')) }} {{ strtoupper($activeModuleDef['label'] ?? $activeModuleKey) }}
                </span>
                <span class="text-[11px] font-semibold text-[#007AFF] px-2 py-0.5 rounded-full bg-[#007AFF]/10 shrink-0">
                    {{ count($secondaryNavTabs) }} {{ __('common.parts') }}
                </span>
            </div>
            <button @click="toggleSecondarySidebarCollapse()" type="button"
                class="w-7 h-7 rounded-[7px] text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center transition-all cursor-pointer shrink-0"
                title="{{ __('navigation.collapse_secondary_sidebar') }}"
                aria-label="{{ __('navigation.collapse_secondary_sidebar') }}">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Sub-Navigation Item List --}}
        <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-1.5 overscroll-contain">
            @foreach ($secondaryNavTabs as $index => $tab)
                <a href="{{ $tab['url'] }}"
                    data-subnav-key="{{ $tab['key'] }}"
                    data-active-secondary-nav="{{ $tab['is_active'] ? 'true' : 'false' }}"
                    class="group w-full flex items-center justify-between px-3.5 py-2.5 rounded-[12px] text-[13px] transition-all {{ $tab['is_active'] ? 'bg-[#007AFF] text-white font-semibold shadow-[0_2px_8px_rgba(0,122,255,0.3)]' : 'text-black/75 dark:text-white/75 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] hover:text-black dark:hover:text-white font-medium' }}">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-5 h-5 rounded-[6px] flex items-center justify-center text-[10.5px] font-mono shrink-0 {{ $tab['is_active'] ? 'bg-white/25 text-white font-bold' : 'bg-black/[0.06] dark:bg-white/10 text-black/50 dark:text-white/50 font-medium group-hover:bg-black/10 dark:group-hover:bg-white/15' }}">
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
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/90 shrink-0 group-hover:translate-x-0.5 transition-transform"></i>
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

    {{-- Floating Un-collapse Pill Button when Secondary Sidebar is Hidden on Desktop --}}
    <div x-show="secondarySidebarCollapsed"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-x-2"
        x-transition:enter-end="opacity-100 translate-x-0"
        :class="sidebarCollapsed ? 'lg:left-[76px]' : 'lg:left-[272px]'"
        class="hidden lg:flex fixed top-16 z-30 transition-[left] duration-250 ease-out"
        style="display: none;">
        <button @click="toggleSecondarySidebarCollapse()" type="button"
            class="h-8 pl-2.5 pr-3 rounded-r-[10px] bg-white dark:bg-[#1C1C1E] border border-l-0 border-black/10 dark:border-white/15 shadow-[2px_2px_8px_rgba(0,0,0,0.06)] text-[11.5px] font-semibold text-[#007AFF] hover:bg-[#007AFF]/5 flex items-center gap-1.5 transition-all cursor-pointer"
            title="{{ __('navigation.expand_secondary_sidebar') }}"
            aria-label="{{ __('navigation.expand_secondary_sidebar') }}">
            <i data-lucide="panel-left-open" class="w-3.5 h-3.5"></i>
            <span class="max-w-[130px] truncate">{{ $activeModuleDef['label'] ?? $activeModuleKey }}</span>
        </button>
    </div>
@endif
