@props(['card', 'icon' => 'dashboard'])

<a class="kpi-card {{ $card['tone'] ?? 'neutral' }}" href="{{ $card['url'] ?? '#' }}">
    <span class="kpi-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24">
            @switch($icon)
                @case('building')
                    <path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path><path d="M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1"></path>
                    @break
                @case('users')
                    <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path><circle cx="9.5" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
                    @break
                @case('clipboard')
                    <rect x="8" y="2" width="8" height="4" rx="1"></rect><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><path d="M8 12h8M8 16h6"></path>
                    @break
                @case('alert')
                    <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"></path><path d="M12 9v4M12 17h.01"></path>
                    @break
                @case('check')
                    <path d="M9 12l2 2 4-5"></path><rect x="3" y="3" width="18" height="18" rx="4"></rect>
                    @break
                @case('clock')
                    <circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>
                    @break
                @case('message')
                    <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
                    @break
                @case('refresh')
                    <path d="M21 12a9 9 0 0 1-15.5 6.2L3 16"></path><path d="M3 21v-5h5"></path><path d="M3 12A9 9 0 0 1 18.5 5.8L21 8"></path><path d="M21 3v5h-5"></path>
                    @break
                @case('file')
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6M8 13h8M8 17h6"></path>
                    @break
                @default
                    <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect>
            @endswitch
        </svg>
    </span>
    <span class="kpi-body">
        <span class="summary-label">{{ $card['label'] }}</span>
        <strong class="stat-value">{{ $card['value'] }}</strong>
        <span class="kpi-hint">Buka detail</span>
    </span>
</a>
