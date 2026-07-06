@props(['rows' => [], 'markers' => []])

<div class="audit-timeline" role="region" aria-label="Timeline visual periode audit">
    <div class="timeline-markers">
        @foreach ($markers as $marker)
            @if ($marker['date'])
                <span>{{ $marker['label'] }}<strong>{{ $marker['date']->format('d/m') }}</strong></span>
            @endif
        @endforeach
        <span class="today-marker">Hari ini<strong>{{ now()->format('d/m') }}</strong></span>
    </div>

    <div class="timeline-rows">
        @forelse ($rows as $row)
            <a class="timeline-row @if ($row['late']) is-late @endif" href="{{ $row['url'] }}">
                <strong title="{{ $row['title'] }}">{{ $row['label'] }}</strong>
                <div class="timeline-segments">
                    @foreach ($row['segments'] as $segment)
                        <span class="timeline-segment {{ $segment['tone'] }}" title="{{ $segment['label'] }}: {{ $segment['percent'] }}%{{ $segment['deadline'] ? ' | Deadline '.$segment['deadline'] : '' }}">
                            <i style="width: {{ $segment['percent'] }}%"></i>
                            <b>{{ $segment['label'] }}</b>
                        </span>
                    @endforeach
                </div>
            </a>
        @empty
            <div class="empty-compact">Belum ada unit pada periode ini.</div>
        @endforelse
    </div>
</div>
