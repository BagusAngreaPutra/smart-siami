@extends('layouts.app')

@section('title', 'Jadwal Visitasi - SMART SIAMI')
@section('page_title', 'Jadwal Visitasi')

@section('content')
    @php
        $newVisitIds = unreadNotificationObjectIds('visit');
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
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        @php
                            $visit = $assignment->visit;
                            $hasNewInfo = $visit && in_array((int) $visit->id, $newVisitIds, true);
                        @endphp
                        <tr class="@if ($hasNewInfo) unread-row @endif">
                            <td>
                                {{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}
                                @if ($hasNewInfo)
                                    <span class="new-info-dot" title="Informasi visitasi baru"></span>
                                @endif
                            </td>
                            <td>{{ $assignment->auditPeriod->nama }}</td>
                            <td>{{ $visit?->tanggal?->format('d/m/Y') ?? 'Jadwal visitasi belum ditetapkan auditor.' }}</td>
                            <td>{{ $visit ? (($visit->waktu_mulai ?? '-') . ' - ' . ($visit->waktu_selesai ?? '-')) : '-' }}</td>
                            <td>{{ $visit ? $tipeOptions[$visit->tipe] : '-' }}</td>
                            <td><span class="badge @if (! $visit || $visit->status === 'belum_dijadwalkan') off @endif">{{ $visit ? $statusOptions[$visit->status] : 'Belum Dijadwalkan' }}</span></td>
                            <td>
                                @if ($visit)
                                    <div class="table-actions">
                                        <x-action-icon :href="route('auditee.visit-schedules.show', $visit)" icon="eye" label="Buka jadwal" tone="view" />
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Belum ada penugasan audit aktif untuk unit Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $assignments->links() }}</div>
    </div>
@endsection
