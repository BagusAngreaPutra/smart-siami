@props(['standards' => [], 'rows' => []])

<div class="heatmap" role="region" aria-label="Heatmap capaian unit per standar">
    <div class="heatmap-grid" style="--columns: {{ max(count($standards), 1) + 1 }}">
        <div class="heatmap-head">Unit</div>
        @foreach ($standards as $standard)
            <div class="heatmap-head" title="{{ $standard->nama }}">{{ $standard->kode }}</div>
        @endforeach

        @forelse ($rows as $row)
            <a class="heatmap-unit" href="{{ $row['url'] }}" title="{{ $row['title'] }}">{{ $row['label'] }}</a>
            @foreach ($row['values'] as $cell)
                <a class="heatmap-cell {{ $cell['tone'] }}" style="--value: {{ max(0, min(100, (int) $cell['value'])) }}" href="{{ $row['url'] }}" title="{{ $row['title'] }} - {{ $cell['title'] }}: {{ $cell['value'] }}%">
                    {{ $cell['value'] }}%
                </a>
            @endforeach
        @empty
            <div class="heatmap-empty" style="grid-column: 1 / -1">Belum ada data penugasan untuk divisualkan.</div>
        @endforelse
    </div>
    <div class="chart-legend">
        <span><i class="legend-dot success"></i>Baik >= 80%</span>
        <span><i class="legend-dot warning"></i>Cukup 50-79%</span>
        <span><i class="legend-dot danger"></i>Kurang < 50%</span>
    </div>
</div>
