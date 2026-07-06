@props(['evidence', 'downloadUrl' => null, 'previewUrl' => null])
@php
    $extension = strtolower(pathinfo((string) $evidence->path_file, PATHINFO_EXTENSION));
    $isImage = in_array($extension, ['jpg', 'jpeg', 'png'], true);
    $isPdf = $extension === 'pdf';
    $statusClass = match ($evidence->status_verifikasi) {
        'valid' => 'success',
        'perlu_klarifikasi' => 'danger',
        default => 'neutral',
    };
@endphp

<article class="evidence-card">
    <div class="evidence-thumb">
        @if ($previewUrl && $isImage)
            <img src="{{ $previewUrl }}" alt="{{ $evidence->nama_dokumen }}">
        @elseif ($previewUrl && $isPdf)
            <iframe src="{{ $previewUrl }}#toolbar=0&navpanes=0" title="Preview {{ $evidence->nama_dokumen }}"></iframe>
        @else
            <div class="file-glyph">{{ $evidence->tipe_sumber === 'tautan' ? 'URL' : strtoupper($extension ?: 'FILE') }}</div>
        @endif
        <span class="badge {{ $statusClass }}">{{ \App\Models\Evidence::statusVerifikasiOptions()[$evidence->status_verifikasi] ?? $evidence->status_verifikasi }}</span>
    </div>
    <div class="evidence-body">
        <strong>{{ $evidence->nama_dokumen }}</strong>
        <span class="muted">{{ $evidence->jenis_dokumen ?? $evidence->instrumen_terkait ?? 'Dokumen bukti' }}</span>
        <div class="actions">
            @if ($previewUrl)
                <a class="link-button" target="_blank" href="{{ $previewUrl }}">Lihat</a>
            @endif
            @if ($downloadUrl)
                <a class="link-button" href="{{ $downloadUrl }}">Unduh</a>
            @elseif ($evidence->url_tautan)
                <a class="link-button" target="_blank" href="{{ $evidence->url_tautan }}">Buka</a>
            @endif
        </div>
    </div>
</article>
