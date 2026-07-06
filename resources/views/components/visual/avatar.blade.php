@props(['name' => 'User', 'size' => 'sm', 'photoUrl' => null, 'focusX' => 50, 'focusY' => 50])
@php
    $focusX = (int) $focusX;
    $focusY = (int) $focusY;
    $initials = collect(explode(' ', trim($name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->join('');
    $hue = crc32($name) % 360;
@endphp

<span
    class="avatar {{ $size }} @if ($photoUrl) has-photo @endif"
    style="--avatar-hue: {{ $hue }}; --photo-x: {{ $focusX }}%; --photo-y: {{ $focusY }}%; @if ($photoUrl) --photo-url: url('{{ $photoUrl }}'); @endif"
    title="{{ $name }}"
    @if ($photoUrl) role="img" aria-label="{{ $name }}" @endif
>
    @unless ($photoUrl)
        {{ strtoupper($initials ?: 'U') }}
    @endunless
</span>
