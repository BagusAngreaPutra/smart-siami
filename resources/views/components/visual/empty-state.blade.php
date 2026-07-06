@props(['title' => 'Belum ada data', 'message' => 'Data akan muncul setelah proses berjalan.', 'icon' => 'check'])

<div class="empty-state">
    <div class="empty-illustration" aria-hidden="true">
        <svg viewBox="0 0 64 64">
            <circle cx="32" cy="32" r="28"></circle>
            @if ($icon === 'document')
                <path d="M23 16h14l8 8v24H23z"></path><path d="M37 16v9h8M28 34h12M28 41h9"></path>
            @else
                <path d="M21 33l8 8 15-18"></path>
            @endif
        </svg>
    </div>
    <strong>{{ $title }}</strong>
    <p class="muted">{{ $message }}</p>
</div>
