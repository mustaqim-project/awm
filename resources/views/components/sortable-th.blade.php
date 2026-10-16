@props([
    'column',
    'title' => null,
    'align' => 'left',
    'defaultDir' => 'asc',
])

@php
    $currentSort = (string) request('sort', '');
    $currentDir = strtolower((string) request('dir', ''));
    $isActive = ($currentSort === (string) $column);

    // Toggle direction: if active and currently asc, toggle to desc, else asc.
    // If not active, start with defaultDir.
    if ($isActive) {
        $nextDir = ($currentDir === 'asc') ? 'desc' : 'asc';
    } else {
        $nextDir = in_array(strtolower($defaultDir), ['asc', 'desc'], true) ? strtolower($defaultDir) : 'asc';
    }

    // Preserve all existing query parameters, set new sort & dir, and reset pagination
    $queryParams = array_merge(request()->query(), [
        'sort' => $column,
        'dir' => $nextDir,
    ]);
    unset($queryParams['page']);

    $sortUrl = request()->url() . '?' . http_build_query($queryParams);

    $alignClass = match ($align) {
        'right' => 'justify-end text-right ml-auto',
        'center' => 'justify-center text-center mx-auto',
        default => 'justify-start text-left',
    };

    $thAlign = match ($align) {
        'right' => 'text-right',
        'center' => 'text-center',
        default => 'text-left',
    };
@endphp

<th {{ $attributes->merge(['class' => "py-3 px-4 text-[11px] font-bold uppercase tracking-wider select-none {$thAlign}"]) }}>
    <a href="{{ $sortUrl }}"
       class="group inline-flex items-center gap-1.5 transition-colors {{ $alignClass }} {{ $isActive ? 'text-black dark:text-white font-bold' : 'text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white' }}">
        <span>{{ $title ?? $slot }}</span>
        <span class="inline-flex items-center shrink-0">
            @if ($isActive && $currentDir === 'asc')
                {{-- Active ASC --}}
                <svg class="w-3.5 h-3.5 text-[#007AFF] dark:text-[#0A84FF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m18 15-6-6-6 6"/>
                </svg>
            @elseif ($isActive && $currentDir === 'desc')
                {{-- Active DESC --}}
                <svg class="w-3.5 h-3.5 text-[#007AFF] dark:text-[#0A84FF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            @else
                {{-- Inactive Neutral --}}
                <svg class="w-3.5 h-3.5 text-black/25 dark:text-white/25 group-hover:text-black/50 dark:group-hover:text-white/50 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m7 15 5 5 5-5"/>
                    <path d="m7 9 5-5 5 5"/>
                </svg>
            @endif
        </span>
    </a>
</th>
