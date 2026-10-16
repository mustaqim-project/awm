@props([
    'col',
    'type' => 'string',
    'title' => null,
    'align' => 'left',
])

@php
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

<th @click="sortBy({{ (int) $col }}, '{{ $type }}', $event)"
    {{ $attributes->merge(['class' => "py-3 px-4 text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 cursor-pointer select-none group hover:text-black dark:hover:text-white transition-colors {$thAlign}"]) }}>
    <div class="inline-flex items-center gap-1.5 {{ $alignClass }}">
        <span>{{ $title ?? $slot }}</span>
        <span class="inline-flex items-center text-black/30 dark:text-white/30 shrink-0"
              :class="sortCol === {{ (int) $col }} ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'group-hover:text-black/60 dark:group-hover:text-white/60'">
            <svg x-show="sortCol === {{ (int) $col }} && sortAsc" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
            <svg x-show="sortCol === {{ (int) $col }} && !sortAsc" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
            <svg x-show="sortCol !== {{ (int) $col }}" class="w-3 h-3 opacity-40 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
        </span>
    </div>
</th>
