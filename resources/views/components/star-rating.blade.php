@props(['rating' => 0, 'count' => null])

@php
    $rating = max(0, min(5, (float) $rating));
    $rounded = (int) round($rating);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1']) }} aria-label="{{ number_format($rating, 1) }} out of 5 stars">
    <span class="text-yellow-500" aria-hidden="true">
        {{ str_repeat('★', $rounded) }}{{ str_repeat('☆', 5 - $rounded) }}
    </span>
    @if (! is_null($count))
        <span class="text-xs text-gray-500">{{ number_format($rating, 1) }} ({{ $count }})</span>
    @else
        <span class="text-xs text-gray-500">{{ number_format($rating, 1) }}</span>
    @endif
</span>
