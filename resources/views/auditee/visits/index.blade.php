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

    @php
        $visibleAssignments = $assignments->getCollection();
        $visibleVisits = $visibleAssignments->pluck('visit')->filter();
    @endphp
    <section class="auditee-page-stats" aria-label="Ringkasan visitasi">
        <x-auditee.metric-card label="Penugasan" :value="$assignments->total()" caption="Ruang lingkup unit" tone="teal" icon="building" />
        <x-auditee.metric-card label="Terjadwal" :value="$visibleVisits->where('status', 'terjadwal')->count()" caption="Pada halaman ini" tone="blue" icon="calendar" />
        <x-auditee.metric-card label="Perlu Konfirmasi" :value="$visibleVisits->where('konfirmasi_auditee', false)->count()" caption="Tindakan auditee" tone="orange" icon="clock" />
        <x-auditee.metric-card label="Selesai" :value="$visibleVisits->whereIn('status', ['selesai', 'berita_acara_disetujui'])->count()" caption="Visitasi tuntas" tone="teal" icon="check" />
    </section>

    <div class="panel auditee-list-surface">
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
