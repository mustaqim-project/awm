{{-- ========================================================================= --}}
{{-- REVIEW STARS: Bakery & Cake (Artisan Bakery & Patisserie)                 --}}
{{-- ========================================================================= --}}
@props([
    'rating' => 4.9,
    'count' => 128,
    'showCount' => true,
    'size' => 'sm', // 'xs', 'sm', 'md'
])

@php
    $starSizeClass = match($size) {
        'xs' => 'w-3 h-3',
        'md' => 'w-4.5 h-4.5',
        default => 'w-3.5 h-3.5',
    };
    $numRating = (float) ($rating ?: 5.0);
    $fullStars = (int) floor($numRating);
    $hasHalfStar = ($numRating - $fullStars) >= 0.4;
    $gradientId = 'star_grad_' . $size . '_' . str_replace('.', '_', (string) $numRating);
@endphp

<div class="inline-flex items-center gap-1.5 font-sans">
    <div class="flex items-center text-amber-500 dark:text-amber-400">
        @for ($i = 1; $i <= 5; $i++)
            @if ($i <= $fullStars)
                <svg class="{{ $starSizeClass }} fill-current" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            @elseif ($i == $fullStars + 1 && $hasHalfStar)
                <svg class="{{ $starSizeClass }}" viewBox="0 0 20 20" aria-hidden="true">
                    <defs>
                        <linearGradient id="{{ $gradientId }}">
                            <stop offset="50%" stop-color="#F59E0B" />
                            <stop offset="50%" stop-color="#D1D5DB" class="dark:stop-color-neutral-700" />
                        </linearGradient>
                    </defs>
                    <path fill="url(#{{ $gradientId }})" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            @else
                <svg class="{{ $starSizeClass }} text-neutral-300 dark:text-neutral-700 fill-current" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            @endif
        @endfor
    </div>

    <span class="text-xs font-bold text-neutral-800 dark:text-neutral-200 tabular-nums">
        {{ number_format($numRating, 1) }}
    </span>

    @if ($showCount && !empty($count))
        <span class="text-[11px] text-neutral-500 dark:text-neutral-400 font-normal">
            ({{ number_format((int) $count) }} ulasan)
        </span>
    @endif
</div>