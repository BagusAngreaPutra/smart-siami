@extends('layouts.app')

@section('title', 'Evaluasi Diri - SMART SIAMI')
@section('page_title', 'Evaluasi Diri')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    @if (! $assignment)
        <div class="panel">
            <h3 class="panel-title">Belum Ada Penugasan Aktif</h3>
            <p class="muted">Evaluasi diri akan tersedia setelah Admin membuat penugasan audit untuk unit Anda pada periode aktif.</p>
        </div>
    @else
        <div class="panel">
            <div class="toolbar">
                <div>
                    <h3 class="panel-title">{{ $assignment->unit->nama }}</h3>
                    <p class="muted">{{ $assignment->auditPeriod->nama }} · Batas evaluasi diri {{ $assignment->auditPeriod->batas_evaluasi_diri->format('d/m/Y') }}</p>
                </div>

                <form class="filters" method="get" action="{{ route('auditee.self-evaluations') }}">
                    <div class="form-field">
                        <label for="assignment_id">Penugasan</label>
                        <select id="assignment_id" name="assignment_id">
                            @foreach ($assignments as $option)
                                <option value="{{ $option->id }}" @selected($option->id === $assignment->id)>{{ $option->auditPeriod->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit">Tampilkan</button>
                </form>
            </div>

            @if (! $isEditablePeriod)
                <div class="warning">Evaluasi diri tidak dapat diubah karena periode sudah ditutup atau melewati batas evaluasi diri.</div>
            @endif

            @if ($canFinalize)
                <form method="post" action="{{ route('auditee.self-assessments.finalize', $assignment) }}" onsubmit="return confirm('Finalisasi evaluasi diri? Semua jawaban yang dikirim akan dikunci.');">
                    @csrf
                    <button type="submit">Finalisasi Evaluasi Diri</button>
                </form>
            @endif
        </div>

        @foreach ($standards as $group)
            <div class="panel">
                <div class="toolbar">
                    <div>
                        <h3 class="panel-title">{{ $group['standard']->kode }} - {{ $group['standard']->nama }}</h3>
                        <p class="muted">Progress {{ $group['progress'] }}%</p>
                    </div>
                    <div style="min-width: 220px">
                        <div class="progress"><div class="progress-bar" style="width: {{ $group['progress'] }}%"></div></div>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Instrumen</th>
                                <th>Status</th>
                                <th>Bukti</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group['assessments'] as $assessment)
                                <tr>
                                    <td>{{ $assessment->instrument->kode }}</td>
                                    <td>{{ $assessment->instrument->pertanyaan }}</td>
                                    <td><span class="badge @if (! in_array($assessment->status, ['dikirim', 'final'], true)) off @endif">{{ $statusOptions[$assessment->status] }}</span></td>
                                    <td>{{ $assessment->evidences->isNotEmpty() ? 'Ada' : 'Belum Ada' }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <x-action-icon :href="route('auditee.self-assessments.edit', $assessment)" icon="edit" label="Isi atau lihat" tone="edit" />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
@endsection
