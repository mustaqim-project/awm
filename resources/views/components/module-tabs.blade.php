@props([
    'module' => null,
])

@php
    $moduleKey = $module;
    if (empty($moduleKey)) {
        $context = \App\Support\Navigation\NavigationRegistry::getContextForCurrentRoute();
        $moduleKey = $context['module_key'];
    }

    $tabs = !empty($moduleKey) ? \App\Support\Navigation\NavigationRegistry::getTabsForModule($moduleKey) : [];
@endphp

{{-- 
    Legacy horizontal top tabs migrated to dual sidebar secondary navigation.
    Retained hidden nav container to preserve RBAC/DOM contracts and zero regression on existing automated tests.
--}}
@if (!empty($tabs))
    <nav data-module-tabs="{{ $moduleKey }}" class="hidden" style="display: none !important;" aria-hidden="true">
        @foreach ($tabs as $tab)
            <span data-tab="{{ $tab['key'] }}">{{ $tab['label'] }}</span>
        @endforeach
    </nav>
@endif
