@props([
    'id',
    'title',
    'description' => 'Pilih file Excel yang akan diimport ke sistem.',
    'action',
    'inputId',
    'accept' => '.xlsx,.xls,.xml,.csv,.txt',
    'submitLabel' => 'Import Excel',
])

<div class="import-modal" data-import-modal="{{ $id }}" hidden>
    <div class="import-modal-backdrop" data-import-modal-close></div>
    <section class="import-modal-card" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}Title">
        <div class="import-modal-header">
            <div>
                <span class="instrument-eyebrow">Import Excel</span>
                <h3 id="{{ $id }}Title">{{ $title }}</h3>
                <p class="muted">{{ $description }}</p>
            </div>
            <button class="template-modal-close" type="button" aria-label="Tutup modal import" data-import-modal-close>&times;</button>
        </div>

        <form class="import-modal-form" method="post" action="{{ $action }}" enctype="multipart/form-data">
            @csrf
            <label class="import-file-drop" for="{{ $inputId }}" data-import-drop>
                <span class="excel-action-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                        <path d="M14 2v6h6"/>
                        <path d="M12 8v8"/>
                        <path d="m8 12 4 4 4-4"/>
                    </svg>
                </span>
                <strong>Tarik file ke sini atau klik untuk memilih</strong>
                <small>Format yang diterima: {{ str_replace(',', ', ', $accept) }}</small>
                <span class="import-file-name" data-import-file-name>Belum ada file dipilih</span>
            </label>
            <input id="{{ $inputId }}" type="file" name="file" accept="{{ $accept }}" required data-import-file-input>
            @error('file')
                <div class="error">{{ $message }}</div>
            @enderror
            <div class="import-modal-actions">
                <button class="button secondary" type="button" data-import-modal-close>Batal</button>
                <button type="submit">{{ $submitLabel }}</button>
            </div>
        </form>
    </section>
</div>
