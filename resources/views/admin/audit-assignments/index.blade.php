@extends('layouts.app')

@section('title', 'Penugasan Audit - SMART SIAMI')
@section('page_title', 'Penugasan Audit')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    <div class="panel">
        <div class="toolbar">
            <form class="filters" method="get" action="{{ route('admin.assignments') }}">
                <div class="form-field">
                    <label for="audit_period_id">Periode</label>
                    <select id="audit_period_id" name="audit_period_id">
                        <option value="">Semua</option>
                        @foreach ($periodOptions as $period)
                            <option value="{{ $period->id }}" @selected((string) $selectedPeriodId === (string) $period->id)>{{ $period->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="auditor_id">Auditor</label>
                    <select id="auditor_id" name="auditor_id">
                        <option value="">Semua</option>
                        @foreach ($auditorOptions as $auditor)
                            <option value="{{ $auditor->id }}" @selected((string) request('auditor_id') === (string) $auditor->id)>{{ $auditor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="unit_id">Unit</label>
                    <select id="unit_id" name="unit_id">
                        <option value="">Semua</option>
                        @foreach ($unitOptions as $unit)
                            <option value="{{ $unit->id }}" @selected((string) request('unit_id') === (string) $unit->id)>{{ $unit->kode }} - {{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit">Filter</button>
                <a class="button secondary" href="{{ route('admin.assignments') }}">Reset</a>
            </form>

            <a class="button" href="{{ route('admin.assignments.create', ['audit_period_id' => $activePeriod?->id]) }}">Tambah Penugasan</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Unit</th>
                        <th>Lead Auditor</th>
                        <th>Periode</th>
                        <th>Tanggal Visitasi</th>
                        <th>Progres Evaluasi Diri</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        <tr>
                            <td>{{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}</td>
                            <td>{{ $assignment->leadAuditor->name }}</td>
                            <td>{{ $assignment->auditPeriod->nama }}</td>
                            <td>{{ $assignment->jadwal_visitasi?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $assignment->progressEvaluasiDiri() }}%</td>
                            <td><span class="badge @if ($assignment->status !== 'aktif') off @endif">{{ $statusOptions[$assignment->status] ?? $assignment->status }}</span></td>
                            <td>
                                <div class="actions">
                                    <a class="link-button" href="{{ route('admin.assignments.show', $assignment) }}">Detail</a>
                                    @if ($assignment->status === 'aktif')
                                        <a class="link-button" href="{{ route('admin.assignments.edit', $assignment) }}">Ubah Auditor</a>
                                        <a class="link-button" href="{{ route('admin.assignments.print-letter', $assignment) }}" target="_blank">Cetak Surat Tugas</a>
                                        <form class="inline-form" method="post" action="{{ route('admin.assignments.notify', $assignment) }}">
                                            @csrf
                                            <button class="link-button" type="submit">Kirim Notifikasi</button>
                                        </form>
                                        <form class="inline-form" method="post" action="{{ route('admin.assignments.cancel', $assignment) }}" onsubmit="return confirm('Batalkan penugasan ini? Data audit yang sudah ada tidak akan dihapus.');">
                                            @csrf
                                            @method('patch')
                                            <button class="link-button danger-link" type="submit">Batalkan</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Belum ada data penugasan audit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $assignments->links() }}</div>
    </div>
@endsection
