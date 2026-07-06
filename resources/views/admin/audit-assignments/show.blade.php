@extends('layouts.app')

@section('title', 'Detail Penugasan Audit - SMART SIAMI')
@section('page_title', 'Detail Penugasan Audit')

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
                <h3 class="panel-title">{{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}</h3>
                <p class="muted">{{ $assignment->auditPeriod->nama }} · Lead Auditor: {{ $assignment->leadAuditor->name }}</p>
            </div>

            <div class="actions">
                @if ($assignment->status === 'aktif')
                    <a class="button secondary" href="{{ route('admin.assignments.edit', $assignment) }}">Ubah Auditor</a>
                    <a class="button secondary" href="{{ route('admin.assignments.print-letter', $assignment) }}" target="_blank">Cetak Surat Tugas</a>
                    <form class="inline-form" method="post" action="{{ route('admin.assignments.notify', $assignment) }}">
                        @csrf
                        <button type="submit">Kirim Notifikasi</button>
                    </form>
                    <form class="inline-form" method="post" action="{{ route('admin.assignments.cancel', $assignment) }}" onsubmit="return confirm('Batalkan penugasan ini? Data audit yang sudah ada tidak akan dihapus.');">
                        @csrf
                        @method('patch')
                        <button type="submit">Batalkan Penugasan</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Status Penugasan</div>
                <div class="summary-value">{{ $assignment->status === 'aktif' ? 'Aktif' : 'Dibatalkan' }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Progres Evaluasi Diri</div>
                <div class="summary-value">{{ $summary['self_evaluation_progress'] }}%</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Desk Evaluation</div>
                <div class="summary-value">{{ $summary['desk_evaluation_status'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Visitasi</div>
                <div class="summary-value">{{ $summary['visitasi_status'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Temuan Aktif</div>
                <div class="summary-value">{{ $summary['active_findings'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Tindak Lanjut</div>
                <div class="summary-value">{{ $summary['follow_up_status'] }}</div>
            </div>
        </div>
    </div>

    <div class="panel">
        <h3 class="panel-title">Informasi Penugasan</h3>
        <div class="table-wrap">
            <table>
                <tbody>
                    <tr>
                        <th>Periode</th>
                        <td>{{ $assignment->auditPeriod->nama }}</td>
                    </tr>
                    <tr>
                        <th>Unit Auditee</th>
                        <td>{{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}</td>
                    </tr>
                    <tr>
                        <th>Lead Auditor</th>
                        <td>{{ $assignment->leadAuditor->name }}</td>
                    </tr>
                    <tr>
                        <th>Auditor Anggota</th>
                        <td>{{ $assignment->auditors->where('id', '!=', $assignment->lead_auditor_id)->pluck('name')->join(', ') ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Desk Evaluation</th>
                        <td>{{ $assignment->tanggal_desk_evaluation?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Jadwal Visitasi</th>
                        <td>{{ $assignment->jadwal_visitasi?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Catatan</th>
                        <td>{{ $assignment->catatan_penugasan ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <h3 class="panel-title">Daftar Temuan</h3>
        <p class="muted">Belum ada data temuan. Area ini akan terisi setelah modul temuan audit dibangun.</p>
    </div>
@endsection
