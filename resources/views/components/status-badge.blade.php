@props([
    'tone' => 'neutral',
])

@php
    $safeTone = in_array($tone, ['success', 'warning', 'danger', 'neutral'], true) ? $tone : 'neutral';
    $icons = [
        'success' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/>',
        'warning' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'danger' => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/>',
        'neutral' => '<circle cx="12" cy="12" r="9"/><path d="M8 12h8"/>',
    ];
@endphp

<span {{ $attributes->class(['badge', 'status-badge', $safeTone]) }}>
    <svg class="status-badge-icon" viewBox="0 0 24 24" aria-hidden="true">{!! $icons[$safeTone] !!}</svg>
    <span>{{ $slot }}</span>
</span>
