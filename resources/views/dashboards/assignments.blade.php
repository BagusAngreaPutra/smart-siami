@extends('layouts.app')

@section('title', $title.' - SMART SIAMI')
@section('page_title', $title)

@section('content')
    @php
        $newAssignmentIds = unreadNotificationObjectIds('audit_assignment');
    @endphp

    <div class="panel">
        <h3 class="panel-title">Penugasan Audit Aktif</h3>
        <p class="muted">Daftar ini hanya menampilkan penugasan yang melibatkan akun atau unit Anda pada periode aktif.</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Unit</th>
                        <th>Periode</th>
                        <th>Lead Auditor</th>
                        <th>Desk Evaluation</th>
                        <th>Visitasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        @php
                            $hasNewInfo = in_array((int) $assignment->id, $newAssignmentIds, true);
                        @endphp
                        <tr class="@if ($hasNewInfo) unread-row @endif">
                            <td>
                                {{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}
                                @if ($hasNewInfo)
                                    <span class="new-info-dot" title="Informasi penugasan baru"></span>
                                @endif
                            </td>
                            <td>{{ $assignment->auditPeriod->nama }}</td>
                            <td>{{ $assignment->leadAuditor->name }}</td>
                            <td>{{ $assignment->tanggal_desk_evaluation?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $assignment->jadwal_visitasi?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">{{ $emptyMessage }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
