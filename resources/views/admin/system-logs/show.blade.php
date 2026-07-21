@extends('layouts.app')

@section('title', 'Detail Jejak Sistem - SMART SIAMI')
@section('page_title', 'Detail Jejak Sistem')

@section('content')
    <section class="system-log-detail-hero">
        <div class="system-log-detail-heading">
            <span class="system-log-detail-icon tone-{{ $systemLog->eventTone() }}"><x-ui-icon name="history" /></span>
            <div>
                <span class="system-log-eyebrow">Aktivitas #{{ $systemLog->id }}</span>
                <h2>{{ $systemLog->action }}</h2>
                <p>{{ $systemLog->description ?: 'Aktivitas sistem tercatat tanpa keterangan tambahan.' }}</p>
            </div>
        </div>
        <div class="actions">
            <a class="button secondary with-icon" href="{{ route('admin.system-logs') }}"><x-ui-icon name="back" /> Kembali</a>
            <form class="inline-form" method="post" action="{{ route('admin.system-logs.destroy', $systemLog) }}">
                @csrf
                @method('delete')
                <button
                    class="button danger with-icon"
                    type="submit"
                    data-danger-confirm
                    data-danger-title="Hapus jejak sistem?"
                    data-danger-message="Riwayat aktivitas ini akan dihapus permanen."
                    data-danger-confirm-label="Ya, Hapus"
                ><x-ui-icon name="trash" /> Hapus</button>
            </form>
        </div>
    </section>

    <div class="system-log-detail-grid">
        <section class="panel system-log-detail-card">
            <div class="system-log-section-heading">
                <span class="system-log-section-icon"><x-ui-icon name="user" /></span>
                <div><span class="system-log-eyebrow">Identitas</span><h3 class="panel-title">Pelaku Aktivitas</h3></div>
            </div>
            <div class="system-log-identity">
                <span class="system-log-avatar large">{{ str($systemLog->actor_name ?? 'S')->substr(0, 1)->upper() }}</span>
                <div>
                    <strong>{{ $systemLog->actor_name ?? 'Sistem' }}</strong>
                    <span>{{ $systemLog->actor_email ?? 'Email tidak tersedia' }}</span>
                </div>
                <span class="system-log-role-pill">{{ $systemLog->actorRoleLabel() }}</span>
            </div>
            <dl class="system-log-data-list">
                <div><dt>ID Pengguna</dt><dd>{{ $systemLog->user_id ?? '-' }}</dd></div>
                <div><dt>Alamat IP</dt><dd>{{ $systemLog->ip_address ?? '-' }}</dd></div>
                <div><dt>Waktu</dt><dd>{{ $systemLog->created_at->translatedFormat('d F Y, H:i:s') }}</dd></div>
            </dl>
        </section>

        <section class="panel system-log-detail-card">
            <div class="system-log-section-heading">
                <span class="system-log-section-icon tone-blue"><x-ui-icon name="route" /></span>
                <div><span class="system-log-eyebrow">Sumber permintaan</span><h3 class="panel-title">Detail Teknis</h3></div>
            </div>
            <dl class="system-log-data-list">
                <div><dt>Jenis Aktivitas</dt><dd><span class="system-log-event tone-{{ $systemLog->eventTone() }}">{{ $systemLog->eventLabel() }}</span></dd></div>
                <div><dt>Metode HTTP</dt><dd><span class="system-log-method">{{ $systemLog->method ?? '-' }}</span></dd></div>
                <div><dt>Nama Route</dt><dd><code>{{ $systemLog->route_name ?? '-' }}</code></dd></div>
                <div><dt>Objek Terkait</dt><dd>{{ $systemLog->subject_type ? class_basename($systemLog->subject_type).' #'.$systemLog->subject_id : '-' }}</dd></div>
            </dl>
        </section>
    </div>

    <section class="panel system-log-request-card">
        <div class="system-log-section-heading">
            <span class="system-log-section-icon tone-orange"><x-ui-icon name="browser" /></span>
            <div><span class="system-log-eyebrow">Konteks akses</span><h3 class="panel-title">Permintaan dan Perangkat</h3></div>
        </div>
        <div class="system-log-request-row">
            <span>URL</span>
            <code>{{ $systemLog->url ?? '-' }}</code>
        </div>
        <div class="system-log-request-row">
            <span>Browser / Perangkat</span>
            <p>{{ $systemLog->user_agent ?: 'Informasi perangkat tidak tersedia.' }}</p>
        </div>
    </section>

    <section class="panel system-log-metadata-card">
        <div class="system-log-section-heading">
            <span class="system-log-section-icon tone-violet"><x-ui-icon name="code" /></span>
            <div><span class="system-log-eyebrow">Data pendukung</span><h3 class="panel-title">Metadata Aktivitas</h3></div>
        </div>
        @if (! empty($systemLog->metadata))
            <pre>{{ json_encode($systemLog->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        @else
            <div class="system-log-empty compact"><strong>Tidak ada metadata tambahan</strong></div>
        @endif
    </section>
@endsection
