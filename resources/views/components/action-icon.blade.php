@props([
    'href' => null,
    'action' => null,
    'method' => 'post',
    'icon' => 'eye',
    'label' => 'Aksi',
    'tone' => 'neutral',
    'target' => null,
    'confirm' => false,
    'confirmTitle' => null,
    'confirmMessage' => null,
    'confirmLabel' => 'Ya, Lanjutkan',
])

@php
    $icons = [
        'archive' => '<path d="M21 8v13H3V8"/><path d="M1 3h22v5H1z"/><path d="M10 12h4"/>',
        'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'copy' => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>',
        'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
        'external' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14 21 3"/>',
        'eye' => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'key' => '<circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3L22 7l-3-3"/>',
        'power' => '<path d="M12 2v10"/><path d="M18.4 6.6a9 9 0 1 1-12.8 0"/>',
        'printer' => '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>',
        'send' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
        'trash' => '<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>',
        'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
    ];

    $svg = $icons[$icon] ?? $icons['eye'];
    $classes = 'action-icon tone-'.$tone;
    $normalizedMethod = strtolower($method);
    $needsMethodSpoof = ! in_array($normalizedMethod, ['get', 'post'], true);
    $usesConfirm = filter_var($confirm, FILTER_VALIDATE_BOOLEAN);
@endphp

@if ($href)
    <a class="{{ $classes }}" href="{{ $href }}" title="{{ $label }}" aria-label="{{ $label }}" @if ($target) target="{{ $target }}" rel="noopener" @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true">{!! $svg !!}</svg>
        <span class="action-tooltip">{{ $label }}</span>
    </a>
@else
    <form class="inline-form" method="post" action="{{ $action }}">
        @csrf
        @if ($needsMethodSpoof)
            @method($method)
        @endif
        {{ $slot }}
        <button
            class="{{ $classes }}"
            type="submit"
            title="{{ $label }}"
            aria-label="{{ $label }}"
            @if ($usesConfirm)
                data-danger-confirm
                data-danger-title="{{ $confirmTitle ?? $label }}"
                data-danger-message="{{ $confirmMessage ?? 'Tindakan ini perlu dikonfirmasi sebelum dilanjutkan.' }}"
                data-danger-confirm-label="{{ $confirmLabel }}"
            @endif
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">{!! $svg !!}</svg>
            <span class="action-tooltip">{{ $label }}</span>
        </button>
    </form>
@endif
