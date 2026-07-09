@props([
    'href' => null,
    'label' => 'Excel',
    'mode' => 'export',
])

@php
    $class = 'excel-action excel-action-'.$mode;
    $arrow = $mode === 'import'
        ? '<path d="M12 8v8"/><path d="m8 12 4 4 4-4"/>'
        : '<path d="M12 16V8"/><path d="m8 12 4-4 4 4"/>';
@endphp

@if ($href)
    <a {{ $attributes->merge(['class' => $class])->except('type') }} href="{{ $href }}">
        <span class="excel-action-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                <path d="M14 2v6h6"/>
                <path d="M8 13h8"/>
                <path d="M8 17h8"/>
                {!! $arrow !!}
            </svg>
        </span>
        <span>{{ $label }}</span>
    </a>
@else
    <button {{ $attributes->merge(['class' => $class, 'type' => 'button']) }}>
        <span class="excel-action-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                <path d="M14 2v6h6"/>
                <path d="M8 13h8"/>
                <path d="M8 17h8"/>
                {!! $arrow !!}
            </svg>
        </span>
        <span>{{ $label }}</span>
    </button>
@endif
