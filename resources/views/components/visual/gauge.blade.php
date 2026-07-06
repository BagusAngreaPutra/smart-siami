@props(['value' => 0, 'label' => 'Kesiapan', 'caption' => null])
@php
    $value = max(0, min(100, (int) $value));
    $tone = \App\Support\AuditVisuals::toneForScore($value);
@endphp

<div class="visual-gauge {{ $tone }}" role="img" aria-label="{{ $label }} {{ $value }} persen">
    <div class="gauge-ring" style="--value: {{ $value }};">
        <div class="gauge-inner">
            <strong>{{ $value }}%</strong>
            <span>{{ $label }}</span>
        </div>
    </div>
    @if ($caption)
        <p class="muted">{{ $caption }}</p>
    @endif
</div>
