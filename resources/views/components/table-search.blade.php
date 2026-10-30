@props([
    'placeholder' => null,
    'name' => 'search',
    'value' => null,
    'resetUrl' => null,
    'live' => true,
])

@php
    $currentVal = $value ?? request($name, '');
    $resetTarget = $resetUrl ?? request()->url();
    $placeholderText = $placeholder ?? __('common.search');
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-1 max-w-sm']) }}>
    <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
    </svg>
    <input type="text"
           name="{{ $name }}"
           value="{{ $currentVal }}"
           @if($live)
               x-model="searchQuery"
               @input="filterTable()"
           @endif
           placeholder="{{ $placeholderText }}"
           class="w-full h-9 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] sm:rounded-[12px] pl-10 pr-9 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
    @if($currentVal)
        <a href="{{ $resetTarget }}" title="{{ __('common.reset') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full text-black/30 dark:text-white/30 hover:text-black dark:hover:text-white transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </a>
    @elseif($live)
        <button type="button" x-show="searchQuery" @click="searchQuery = ''; filterTable()" title="{{ __('common.clear') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full text-black/30 dark:text-white/30 hover:text-black dark:hover:text-white transition" style="display: none;">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    @endif
</div>
