@extends('layouts.app')

@section('title', 'Desk Evaluation - SMART SIAMI')
@section('page_title', 'Desk Evaluation')

@section('content')
    @php
        $newAssignmentIds = unreadNotificationObjectIds('audit_assignment');
    @endphp

    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    <div class="panel">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Unit</th>
                        <th>Periode</th>
                        <th>Jadwal Desk Evaluation</th>
                        <th>Progres Desk Evaluation</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        @php
                            $total = $assignment->evaluations->count();
                            $checked = $assignment->evaluations->where('status_pemeriksaan', '!=', 'belum_dimulai')->count();
                            $progress = $total > 0 ? (int) round(($checked / $total) * 100) : 0;
                            $final = $total > 0 && $assignment->evaluations->where('status_pemeriksaan', '!=', 'final')->isEmpty();
                            $hasNewInfo = in_array((int) $assignment->id, $newAssignmentIds, true);
                        @endphp
                        <tr class="@if ($hasNewInfo) unread-row @endif">
                            <td>
                                {{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}
                                @if ($hasNewInfo)
                                    <span class="new-info-dot" title="Evaluasi diri baru dikirim"></span>
                                @endif
                            </td>
                            <td>{{ $assignment->auditPeriod->nama }}</td>
                            <td>{{ $assignment->tanggal_desk_evaluation?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <div class="progress"><div class="progress-bar" style="width: {{ $progress }}%"></div></div>
                                <span class="muted">{{ $checked }}/{{ $total }} instrumen</span>
                            </td>
                            <td><span class="badge @if (! $final) off @endif">{{ $final ? 'Final' : 'Berlangsung' }}</span></td>
                            <td>
                                <div class="table-actions">
                                    <x-action-icon :href="route('auditor.desk-evaluation.show', $assignment)" icon="edit" label="Periksa desk evaluation" tone="edit" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Belum ada tugas desk evaluation untuk Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $assignments->links() }}</div>
    </div>
@endsection
