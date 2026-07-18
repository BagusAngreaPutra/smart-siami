@props([
    'tone' => 'sidebar',
    'width' => 190,
])

@php
    $palette = match ($tone) {
        'teal' => [
            'primary' => '#252D3D',
            'accent' => '#3979ED',
            'accent_end' => '#5A8FF0',
        ],
        default => [
            'primary' => '#FFFFFF',
            'accent' => '#3979ED',
            'accent_end' => '#77A3F5',
        ],
    };
    $uid = 'smart-siami-wordmark-'.substr(md5(uniqid('', true)), 0, 10);
@endphp

<svg
    {{ $attributes->merge(['class' => 'brand-wordmark']) }}
    width="{{ $width }}"
    viewBox="0 0 205 42"
    role="img"
    aria-label="SMART SIAMI"
    xmlns="http://www.w3.org/2000/svg"
>
    <defs>
        <linearGradient id="{{ $uid }}-badge" x1="104" y1="6" x2="199" y2="36" gradientUnits="userSpaceOnUse">
            <stop stop-color="{{ $palette['accent'] }}"/>
            <stop offset="1" stop-color="{{ $palette['accent_end'] }}"/>
        </linearGradient>
        <filter id="{{ $uid }}-shadow" x="-15%" y="-30%" width="140%" height="170%">
            <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="{{ $palette['accent'] }}" flood-opacity=".22"/>
        </filter>
    </defs>
    <text
        class="brand-wordmark-primary"
        x="1"
        y="30"
        fill="{{ $palette['primary'] }}"
        font-family="'SIAMI Manrope', 'SIAMI Jakarta', Manrope, Arial, sans-serif"
        font-size="26"
        font-weight="800"
        letter-spacing="-1"
    >SMART</text>
    <g filter="url(#{{ $uid }}-shadow)">
        <rect class="brand-wordmark-badge" x="105" y="6" width="94" height="30" rx="9" fill="url(#{{ $uid }}-badge)"/>
        <path d="M113 14.5h8" stroke="#BFD3FF" stroke-width="1.5" stroke-linecap="round" opacity=".9"/>
        <circle cx="116.5" cy="14.5" r="2.4" fill="#FFFFFF"/>
    </g>
    <text
        class="brand-wordmark-accent"
        x="126"
        y="28"
        fill="#FFFFFF"
        font-family="'SIAMI Manrope', 'SIAMI Jakarta', Manrope, Arial, sans-serif"
        font-size="17"
        font-weight="800"
        letter-spacing=".4"
    >SIAMI</text>
</svg>
