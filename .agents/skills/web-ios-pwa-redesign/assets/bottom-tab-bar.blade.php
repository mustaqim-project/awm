{{--
  resources/views/partials/bottom-tab-bar.blade.php — mobile-only bottom tab bar (hidden at >= 768px by tokens.css).
  Include once in the layout, right before </body>:   @include('partials.bottom-tab-bar')
  Add class "has-tabbar" to <body>.  Requires Alpine.js (already in the COOCA stack) and tokens.css.

  RULES
  - Use ONLY route names that already exist. Route::has() below silently drops unknown names.
  - Max 5 slots. If the site has more public routes, put the rest in $more (opens a sheet) so that
    EVERY existing top-level public route stays reachable on mobile. Never orphan a page.
  - Order by user value: Beranda first, primary conversion route (contact/demo/pricing) last or second to last.
  - Labels <= 10 characters. Icons: one consistent outline set.
--}}
@php
    // REPLACE_ROUTE_* with real route names (php artisan route:list --name).
    $tabs = [
        ['route' => 'REPLACE_ROUTE_HOME',    'label' => 'Beranda', 'icon' => 'home', 'match' => ['REPLACE_ROUTE_HOME']],
        ['route' => 'REPLACE_ROUTE_FEATURES','label' => 'Fitur',   'icon' => 'grid', 'match' => ['REPLACE_ROUTE_FEATURES*']],
        ['route' => 'REPLACE_ROUTE_BLOG',    'label' => 'Blog',    'icon' => 'doc',  'match' => ['REPLACE_ROUTE_BLOG*']],
        ['route' => 'REPLACE_ROUTE_CONTACT', 'label' => 'Kontak',  'icon' => 'chat', 'match' => ['REPLACE_ROUTE_CONTACT*']],
    ];
    $more = [
        // ['route' => 'REPLACE_ROUTE_ABOUT', 'label' => 'Tentang kami'],
        // ['route' => 'REPLACE_ROUTE_PRIVACY', 'label' => 'Kebijakan privasi'],
    ];
    $tabs = collect($tabs)->filter(fn ($t) => Route::has($t['route']))->values();
    $more = collect($more)->filter(fn ($t) => Route::has($t['route']))->values();
    $icons = [
        'home' => 'M3 11.5 12 4l9 7.5M5.5 10v9.5h4.5V14h4v5.5h4.5V10',
        'grid' => 'M4 4h6.5v6.5H4zM13.5 4H20v6.5h-6.5zM4 13.5h6.5V20H4zM13.5 13.5H20V20h-6.5z',
        'doc'  => 'M7 3.5h7.5L19 8v12.5H7zM14.5 3.5V8H19M9.5 12.5h7M9.5 16h7',
        'chat' => 'M20.5 12a8 8 0 0 1-11.7 7.1L4 20.3l1.3-4.4A8 8 0 1 1 20.5 12z',
        'tag'  => 'M3.5 12.5V4.5h8l9 9-8 8zM8 8.5h.01',
        'more' => 'M5 12h.01M12 12h.01M19 12h.01',
    ];
@endphp

{{-- Wrapper holds the Alpine state. The sheet must be a SIBLING of <nav>, not a child: .tabbar uses backdrop-filter,
     which would trap position:fixed descendants inside the bar. --}}
<div x-data="{ more: false, kb: false }"
     @focusin.window="kb = $event.target.matches('input:not([type=checkbox]):not([type=radio]), textarea, select')"
     @focusout.window="kb = false"
     @keydown.escape.window="more = false">

    <nav class="tabbar" aria-label="Navigasi utama" :class="{ 'is-hidden': kb }">
        @foreach ($tabs as $t)
            <a class="tab" href="{{ route($t['route']) }}"
               @if (request()->routeIs(...$t['match'])) aria-current="page" @endif>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icons[$t['icon']] ?? $icons['grid'] }}"/></svg>
                <span>{{ $t['label'] }}</span>
            </a>
        @endforeach

        @if ($more->isNotEmpty())
            <button type="button" class="tab" :class="{ 'is-open': more }" @click="more = !more"
                    :aria-expanded="more.toString()" aria-haspopup="dialog">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true"><path d="{{ $icons['more'] }}"/></svg>
                <span>Lainnya</span>
            </button>
        @endif
    </nav>

    @if ($more->isNotEmpty())
        <div x-cloak x-show="more" x-transition.opacity class="sheet-backdrop" @click="more = false" aria-hidden="true"></div>
        <div x-cloak x-show="more" x-transition class="sheet" role="dialog" aria-modal="true" aria-label="Menu lainnya">
            <ul class="group" style="margin-top:8px">
                @foreach ($more as $m)
                    <li><a href="{{ route($m['route']) }}">{{ $m['label'] }}<span aria-hidden="true" style="color:var(--text-2)">›</span></a></li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
