@props([
    'tone' => 'sidebar',
    'width' => 190,
])

@php
    $uid = 'smart-siami-wordmark-'.substr(md5(uniqid('', true)), 0, 10);
    $palette = match ($tone) {
        'teal' => [
            'from' => '#0A4A3F',
            'mid' => '#0E6656',
            'to' => '#3D9C87',
            'accent' => '#E8B36A',
            'muted' => 'rgba(14, 102, 86, .28)',
        ],
        default => [
            'from' => '#FFFFFF',
            'mid' => '#DDF7EF',
            'to' => '#A9E5D5',
            'accent' => '#E8B36A',
            'muted' => 'rgba(226, 245, 240, .34)',
        ],
    };
@endphp

<svg
    {{ $attributes->merge(['class' => 'brand-wordmark']) }}
    width="{{ $width }}"
    viewBox="0 0 286 62"
    role="img"
    aria-label="SMART SIAMI"
    xmlns="http://www.w3.org/2000/svg"
>
    <defs>
        <linearGradient id="{{ $uid }}-text" x1="0" y1="0" x2="286" y2="0" gradientUnits="userSpaceOnUse">
            <stop stop-color="{{ $palette['from'] }}"/>
            <stop offset=".52" stop-color="{{ $palette['mid'] }}"/>
            <stop offset="1" stop-color="{{ $palette['to'] }}"/>
        </linearGradient>
        <linearGradient id="{{ $uid }}-accent" x1="10" y1="54" x2="276" y2="54" gradientUnits="userSpaceOnUse">
            <stop stop-color="{{ $palette['accent'] }}" stop-opacity=".95"/>
            <stop offset=".52" stop-color="{{ $palette['to'] }}" stop-opacity=".78"/>
            <stop offset="1" stop-color="{{ $palette['accent'] }}" stop-opacity=".12"/>
        </linearGradient>
        <filter id="{{ $uid }}-soft" x="-8%" y="-22%" width="116%" height="150%">
            <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#001A15" flood-opacity=".16"/>
        </filter>
    </defs>

    <path d="M10 53H70L82 46H118" stroke="{{ $palette['muted'] }}" stroke-width="2" stroke-linecap="round" fill="none"/>
    <path d="M164 53H220L230 46H276" stroke="{{ $palette['muted'] }}" stroke-width="2" stroke-linecap="round" fill="none"/>
    <path d="M10 54H276" stroke="url(#{{ $uid }}-accent)" stroke-width="3" stroke-linecap="round"/>
    <circle cx="82" cy="46" r="3.5" fill="{{ $palette['accent'] }}"/>
    <circle cx="230" cy="46" r="3.5" fill="{{ $palette['accent'] }}"/>

    <g filter="url(#{{ $uid }}-soft)">
        <text
            x="10"
            y="39"
            fill="url(#{{ $uid }}-text)"
            font-family="Rajdhani, Orbitron, 'Segoe UI', Arial, sans-serif"
            font-size="35"
            font-weight="900"
            letter-spacing="1.6"
        >SMART</text>
        <text
            x="147"
            y="39"
            fill="url(#{{ $uid }}-text)"
            font-family="Rajdhani, Orbitron, 'Segoe UI', Arial, sans-serif"
            font-size="35"
            font-weight="900"
            letter-spacing="1.6"
        >SIAMI</text>
    </g>

    <path d="M132 14L138 8H148" stroke="{{ $palette['accent'] }}" stroke-width="3" stroke-linecap="round" fill="none"/>
    <path d="M132 42L138 48H148" stroke="{{ $palette['accent'] }}" stroke-width="3" stroke-linecap="round" fill="none"/>
</svg>
