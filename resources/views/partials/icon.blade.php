@php
    $icons = [
        'home' => '<path d="M4 11.5 12 4l8 7.5"/><path d="M6 10v9h12v-9"/>',
        'wave' => '<path d="M3 9c2-3 4 3 6 0s4-3 6 0 3 3 6 0"/><path d="M3 16c2-3 4 3 6 0s4-3 6 0 3 3 6 0"/>',
        'fish' => '<path d="M3 12c3-5 9-6 14-2l4-2v8l-4-2c-5 4-11 3-14-2Z"/><circle cx="9" cy="11" r="0.6" fill="currentColor"/>',
        'report' => '<path d="M7 3h8l4 4v14H7z"/><path d="M15 3v4h4"/><path d="M10 12h6M10 16h6"/>',
        'people' => '<circle cx="9" cy="9" r="3"/><path d="M3 20c0-3 2.5-5 6-5s6 2 6 5"/><circle cx="17.5" cy="10.5" r="2.2"/><path d="M15.5 20c.2-2.2 1.8-3.7 4-3.9"/>',
        'gear' => '<circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1.2l2-1.5-2-3.4-2.3.9a7 7 0 0 0-2-1.2L14.2 3H9.8l-.4 2.6a7 7 0 0 0-2 1.2l-2.3-.9-2 3.4 2 1.5a7 7 0 0 0 0 2.4l-2 1.5 2 3.4 2.3-.9a7 7 0 0 0 2 1.2l.4 2.6h4.4l.4-2.6a7 7 0 0 0 2-1.2l2.3.9 2-3.4-2-1.5c.07-.4.1-.8.1-1.2Z"/>',
        'shield' => '<path d="M12 3l7 3v5c0 5-3 8-7 10-4-2-7-5-7-10V6z"/><path d="M9 12l2 2 4-4"/>',
        'bell' => '<path d="M6 16V11a6 6 0 0 1 12 0v5l1.5 2h-15z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
    ];
@endphp
<svg class="{{ $class ?? 'h-5 w-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$name] ?? '' !!}</svg>
