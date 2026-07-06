@extends('layouts.app')

@section('title', $period->nama.' - SMART SIAMI')
@section('page_title', 'Detail Periode Audit')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    <div class="panel">
        <div class="toolbar">
            <div>
                <h3 class="panel-title">{{ $period->nama }}</h3>
                <p class="muted">{{ $jenisAuditOptions[$period->jenis_audit] ?? $period->jenis_audit }} · {{ $period->tahun_akademik }}</p>
            </div>

            <div class="actions">
                @if ($period->canBeEdited())
                    <a class="button secondary" href="{{ route('admin.periods.edit', $period) }}">Edit</a>
                @endif

                <form class="inline-form" method="post" action="{{ route('admin.periods.duplicate', $period) }}">
                    @csrf
                    <button class="button secondary" type="submit">Duplikasi Periode</button>
                </form>

                @if ($period->canActivate())
                    <form class="inline-form" method="post" action="{{ route('admin.periods.activate', $period) }}">
                        @csrf
                        @method('patch')
                        <button type="submit">Aktifkan</button>
                    </form>
                @endif

                @if ($period->canClose())
                    <form class="inline-form" method="post" action="{{ route('admin.periods.close', $period) }}" onsubmit="return confirm('Tutup periode audit ini?');">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="force_close" value="1">
                        <button type="submit">Tutup Periode</button>
                    </form>
                @endif

                @if ($period->canArchive())
                    <form class="inline-form" method="post" action="{{ route('admin.periods.archive', $period) }}" onsubmit="return confirm('Arsipkan periode audit ini?');">
                        @csrf
                        @method('patch')
                        <button type="submit">Arsipkan</button>
                    </form>
                @endif

                @if ($period->status === 'aktif')
                    <form class="inline-form" method="post" action="{{ route('admin.periods.notify-opening', $period) }}">
                        @csrf
                        <button class="button secondary" type="submit">Kirim Notifikasi Pembukaan</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Status</div>
                <div class="summary-value">{{ $statusOptions[$period->status] ?? $period->status }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Penugasan</div>
                <div class="summary-value">{{ $summary['assignments'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Progres Evaluasi Diri</div>
                <div class="summary-value">{{ $summary['self_evaluation_progress'] }}%</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Temuan Aktif</div>
                <div class="summary-value">{{ $summary['active_findings'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Dibuat Oleh</div>
                <div class="summary-value">{{ $period->creator?->name ?? '-' }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Mulai</div>
                <div class="summary-value">{{ $period->tanggal_mulai->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <div class="panel">
        <h3 class="panel-title">Jadwal Periode</h3>
        <div class="table-wrap">
            <table>
                <tbody>
                    <tr>
                        <th>Tanggal Mulai</th>
                        <td>{{ $period->tanggal_mulai->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Batas Evaluasi Diri</th>
                        <td>{{ $period->batas_evaluasi_diri->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Batas Desk Evaluation</th>
                        <td>{{ $period->batas_desk_evaluation->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Visitasi</th>
                        <td>{{ $period->visitasi_mulai?->format('d/m/Y') ?? '-' }} sampai {{ $period->visitasi_selesai?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Batas Tindak Lanjut</th>
                        <td>{{ $period->batas_tindak_lanjut->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Catatan</th>
                        <td>{{ $period->catatan ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
