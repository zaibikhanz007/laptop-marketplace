@props(['name', 'class' => 'h-5 w-5'])

<svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('dashboard')
            <rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="10" width="8" height="11" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/>
            @break
        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            @break
        @case('laptop')
            <rect x="4" y="4" width="16" height="12" rx="2"/><path d="M2 20h20l-2-4H4z"/>
            @break
        @case('orders')
            <path d="M6 7h15l-1.5 9h-12z"/><path d="M6 7 5 3H2"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>
            @break
        @case('complaints')
            <path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"/><path d="M12 8v4"/><path d="M12 16h.01"/>
            @break
        @case('reports')
            <path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>
            @break
        @case('store')
            <path d="M3 10v10h18V10"/><path d="m3 10 2-6h14l2 6"/><path d="M3 10a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/><path d="M9 20v-6h6v6"/>
            @break
        @case('wallet')
            <rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 9h18"/><path d="M16 14h2"/>
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
            @break
        @case('check')
            <circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/>
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>
            @break
        @case('search')
            <circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>
            @break
        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16"/>
            @break
        @case('close')
            <path d="m18 6-12 12M6 6l12 12"/>
            @break
        @case('download')
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5M12 15V3"/>
            @break
        @case('arrow')
            <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
            @break
        @default
            <circle cx="12" cy="12" r="9"/>
    @endswitch
</svg>
