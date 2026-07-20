@props([
    'label',
    'value',
    'caption' => null,
    'tone' => 'teal',
    'icon' => 'file',
    'href' => null,
])

@php($tag = $href ? 'a' : 'div')

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class(['auditee-stat-card', 'tone-'.$tone]) }}>
    <span class="auditee-stat-main">
        <span class="auditee-stat-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
                @switch($icon)
                    @case('edit')
                        <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"></path>
                        @break
                    @case('message')
                        <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4zM8 9h8M8 13h5"></path>
                        @break
                    @case('calendar')
                        <rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path>
                        @break
                    @case('check')
                        <rect x="3" y="3" width="18" height="18" rx="4"></rect><path d="m8 12 2.5 2.5L16 9"></path>
                        @break
                    @case('alert')
                        <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0ZM12 9v4M12 17h.01"></path>
                        @break
                    @case('refresh')
                        <path d="M21 12a9 9 0 0 1-15.5 6.2L3 16M3 21v-5h5M3 12A9 9 0 0 1 18.5 5.8L21 8M21 3v5h-5"></path>
                        @break
                    @case('clock')
                        <circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>
                        @break
                    @case('building')
                        <path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M9 7h1M14 7h1M9 11h1M14 11h1"></path>
                        @break
                    @default
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h6"></path>
                @endswitch
            </svg>
        </span>

        <span class="auditee-stat-copy">
            <span class="auditee-stat-label">{{ $label }}</span>
            <strong>{{ is_numeric($value) ? number_format((float) $value, floor((float) $value) == (float) $value ? 0 : 1) : $value }}</strong>
        </span>

        @if ($href)
            <svg class="auditee-stat-arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
        @endif
    </span>

    <span class="auditee-stat-footer">
        <b><span aria-hidden="true">{{ in_array($tone, ['orange', 'red'], true) ? '!' : '↗' }}</span> {{ $caption ?: 'Data terkini' }}</b>
        @if ($href)<em>Buka detail</em>@endif
    </span>
</{{ $tag }}>
