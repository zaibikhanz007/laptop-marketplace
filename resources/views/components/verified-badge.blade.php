@props(['verified' => false])

@if ($verified)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 text-xs font-semibold text-blue-700']) }} title="Verified seller">
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 1.5 12.2 3l2.7-.1.8 2.6 2.1 1.7-.9 2.5.4 2.7-2.4 1.3-1.2 2.4-2.7-.4L8.5 17l-2.1-1.7-2.7-.1-.8-2.6L.8 11l.9-2.5-.4-2.7 2.4-1.3L4.9 2.1l2.7.4L10 1.5Zm3.2 6.7a.75.75 0 0 0-1.1-1L9 10.7 7.9 9.6a.75.75 0 1 0-1.1 1.1l1.7 1.7a.75.75 0 0 0 1.1 0l3.6-4.2Z" clip-rule="evenodd" />
        </svg>
        <span>Verified</span>
    </span>
@endif
