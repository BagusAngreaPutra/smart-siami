@props([
    'eyebrow' => 'Panduan Cepat',
    'title' => 'Masih bingung mulai dari mana?',
    'description' => null,
    'steps' => [],
    'actions' => [],
])

<section class="quick-guide-panel" aria-label="{{ $title }}">
    <div class="quick-guide-intro">
        <span class="quick-guide-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9"></circle>
                <path d="m15.5 8.5-2.1 4.9-4.9 2.1 2.1-4.9z"></path>
            </svg>
        </span>
        <div>
            <span class="quick-guide-eyebrow">{{ $eyebrow }}</span>
            <h3>{{ $title }}</h3>
            @if ($description)
                <p>{{ $description }}</p>
            @endif
        </div>
    </div>

    <div class="quick-guide-steps">
        @foreach ($steps as $index => $step)
            <a class="quick-guide-step" href="{{ $step['url'] ?? '#' }}">
                <span class="quick-guide-number">{{ $index + 1 }}</span>
                <span>
                    <strong>{{ $step['title'] }}</strong>
                    <small>{{ $step['description'] }}</small>
                </span>
            </a>
        @endforeach
    </div>

    @if ($actions)
        <div class="quick-guide-actions" aria-label="Aksi panduan">
            @foreach ($actions as $action)
                <a class="quick-guide-action {{ $loop->first ? 'primary' : '' }}" href="{{ $action['url'] }}">
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    @endif
</section>
