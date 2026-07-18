@props([
    'name' => 'circle',
])

@php
    $icons = [
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>',
        'filter' => '<path d="M22 3H2l8 9.5V20l4 2v-9.5Z"/>',
        'plus' => '<path d="M12 5v14"/><path d="M5 12h14"/>',
        'reset' => '<path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/>',
        'template' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h6"/>',
        'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/>',
        'eye' => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'pdf' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h5"/>',
        'excel' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 8l4 8M12 8l-4 8M15 8h3M15 12h3M15 16h3"/>',
    ];

    $svg = $icons[$name] ?? '<circle cx="12" cy="12" r="8"/>';
@endphp

<svg class="button-icon" viewBox="0 0 24 24" aria-hidden="true">{!! $svg !!}</svg>
