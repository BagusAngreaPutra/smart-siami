@props(['items' => [], 'title' => 'Radar Capaian'])
@php
    $items = collect($items)->values();
    $count = $items->count();
    $radarCount = max($count, 3);
    $center = 110;
    $radius = 74;
    $valuePoints = [];
    $targetPoints = [];

    foreach ($items as $index => $item) {
        $angle = (-90 + ($index * 360 / $radarCount)) * pi() / 180;
        $targetPoints[] = ($center + cos($angle) * $radius).','.($center + sin($angle) * $radius);
        $valueRadius = $radius * (($item['value'] ?? 0) / 100);
        $valuePoints[] = ($center + cos($angle) * $valueRadius).','.($center + sin($angle) * $valueRadius);
    }

    $gradientId = 'radar'.substr(md5($title), 0, 8);
@endphp

<div class="visual-chart" role="img" aria-label="{{ $title }}">
    @if ($count >= 3)
        <svg class="radar-chart" viewBox="0 0 220 220" aria-hidden="true">
            <defs>
                <linearGradient id="{{ $gradientId }}Fill" x1="0" x2="1" y1="0" y2="1">
                    <stop offset="0%" stop-color="#0E6656" stop-opacity=".42"></stop>
                    <stop offset="100%" stop-color="#E8B36A" stop-opacity=".28"></stop>
                </linearGradient>
                <linearGradient id="{{ $gradientId }}Stroke" x1="0" x2="1">
                    <stop offset="0%" stop-color="#0E6656"></stop>
                    <stop offset="100%" stop-color="#3D9C87"></stop>
                </linearGradient>
            </defs>
            <circle cx="110" cy="110" r="74"></circle>
            <circle cx="110" cy="110" r="52"></circle>
            <circle cx="110" cy="110" r="30"></circle>
            <circle cx="110" cy="110" r="8"></circle>
            <polygon class="radar-target" points="{{ implode(' ', $targetPoints) }}"></polygon>
            <polygon class="radar-value" points="{{ implode(' ', $valuePoints) }}" style="fill:url(#{{ $gradientId }}Fill);stroke:url(#{{ $gradientId }}Stroke);"></polygon>
            @foreach ($items as $index => $item)
                @php
                    $angle = (-90 + ($index * 360 / $radarCount)) * pi() / 180;
                    $x = $center + cos($angle) * ($radius + 20);
                    $y = $center + sin($angle) * ($radius + 20);
                    $dotRadius = $radius * (($item['value'] ?? 0) / 100);
                    $dotX = $center + cos($angle) * $dotRadius;
                    $dotY = $center + sin($angle) * $dotRadius;
                @endphp
                <line x1="110" y1="110" x2="{{ $center + cos($angle) * $radius }}" y2="{{ $center + sin($angle) * $radius }}"></line>
                <circle class="radar-dot" cx="{{ $dotX }}" cy="{{ $dotY }}" r="4">
                    <title>{{ $item['label'] }}: {{ $item['value'] }}%</title>
                </circle>
                <text x="{{ $x }}" y="{{ $y }}" text-anchor="middle">{{ $item['label'] }}</text>
            @endforeach
        </svg>
    @else
        <div class="score-orbit" aria-hidden="true">
            @forelse ($items as $item)
                @php($value = max(0, min(100, (int) ($item['value'] ?? 0))))
                <div class="score-orbit-item {{ $item['tone'] ?? 'neutral' }}">
                    <div class="score-orbit-head">
                        <strong>{{ $item['label'] }}</strong>
                        <span>{{ $value }}%</span>
                    </div>
                    <div class="score-track"><i style="width: {{ $value }}%"></i></div>
                    <small>{{ $item['title'] ?? 'Target 100%' }}</small>
                </div>
            @empty
                <div class="heatmap-empty">Belum ada standar aktif untuk divisualkan.</div>
            @endforelse
        </div>
    @endif

    @if ($items->isNotEmpty())
        <div class="chart-legend" aria-hidden="true">
            @foreach ($items as $item)
                <span><i class="legend-dot {{ $item['tone'] ?? 'neutral' }}"></i>{{ $item['label'] }} {{ $item['value'] }}%</span>
            @endforeach
        </div>
    @endif

    <table class="sr-table">
        <caption>{{ $title }}</caption>
        <thead><tr><th>Standar</th><th>Capaian</th><th>Target</th></tr></thead>
        <tbody>
            @foreach ($items as $item)
                <tr><td>{{ $item['title'] ?? $item['label'] }}</td><td>{{ $item['value'] }}%</td><td>{{ $item['target'] ?? 100 }}%</td></tr>
            @endforeach
        </tbody>
    </table>
</div>
