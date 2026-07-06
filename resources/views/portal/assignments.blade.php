@extends('layouts.app')

@section('title', $title.' - SMART SIAMI')
@section('page_title', $title)

@section('content')
    @php
        $newAssignmentIds = unreadNotificationObjectIds('audit_assignment');
        $totalAssignments = $assignments->total();
        $currentItems = $assignments->getCollection();
        $pendingDesk = $currentItems->filter(fn ($assignment) => ($assignment->desk_total ?? 0) === 0 || ($assignment->desk_checked ?? 0) < ($assignment->desk_total ?? 0))->count();
        $upcomingVisits = $currentItems->filter(fn ($assignment) => $assignment->visit?->tanggal && $assignment->visit->tanggal->toDateString() >= now()->toDateString())->count();
        $totalFindings = $currentItems->sum('active_findings_count');
    @endphp

    <section class="task-hero">
        <div>
            <span class="quick-guide-eyebrow">Workspace Auditor</span>
            <h3>Tugas Audit yang Perlu Dipantau</h3>
            <p>Pilih unit, lanjutkan pemeriksaan, cek jadwal visitasi, atau buka temuan dari satu tempat.</p>
        </div>
        <div class="task-hero-actions">
            <a class="hero-action" href="{{ route('auditor.desk-evaluation') }}">Desk Evaluation</a>
            <a class="hero-action" href="{{ route('auditor.visitations') }}">Visitasi</a>
            <a class="hero-action" href="{{ route('auditor.findings') }}">Temuan</a>
        </div>
    </section>

    <div class="dashboard-kpi-grid">
        <div class="kpi-card neutral">
            <span class="kpi-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13"></path><path d="M3 6h.01M3 12h.01M3 18h.01"></path></svg>
            </span>
            <span class="kpi-body">
                <span class="muted">Total Tugas</span>
                <strong class="stat-value">{{ $totalAssignments }}</strong>
                <span class="kpi-hint">Penugasan aktif</span>
            </span>
        </div>
        <div class="kpi-card warning">
            <span class="kpi-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.3-4.3"></path></svg>
            </span>
            <span class="kpi-body">
                <span class="muted">Desk Belum Final</span>
                <strong class="stat-value">{{ $pendingDesk }}</strong>
                <span class="kpi-hint">Pada halaman ini</span>
            </span>
        </div>
        <div class="kpi-card success">
            <span class="kpi-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11z"></path><circle cx="12" cy="10" r="2.5"></circle></svg>
            </span>
            <span class="kpi-body">
                <span class="muted">Visitasi Mendatang</span>
                <strong class="stat-value">{{ $upcomingVisits }}</strong>
                <span class="kpi-hint">Pada halaman ini</span>
            </span>
        </div>
        <div class="kpi-card danger">
            <span class="kpi-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"></path><path d="M12 9v4M12 17h.01"></path></svg>
            </span>
            <span class="kpi-body">
                <span class="muted">Temuan Aktif</span>
                <strong class="stat-value">{{ $totalFindings }}</strong>
                <span class="kpi-hint">Pada halaman ini</span>
            </span>
        </div>
    </div>

    <div class="task-board">
        @forelse ($assignments as $assignment)
            @php
                $selfTotal = max((int) ($assignment->self_total ?? 0), 1);
                $selfProgress = (int) round(((int) ($assignment->self_submitted ?? 0) / $selfTotal) * 100);
                $deskTotal = max((int) ($assignment->desk_total ?? 0), 1);
                $deskProgress = (int) round(((int) ($assignment->desk_checked ?? 0) / $deskTotal) * 100);
                $visit = $assignment->visit;
                $isLead = $assignment->lead_auditor_id === auth()->id();
                $hasNewInfo = in_array((int) $assignment->id, $newAssignmentIds, true);
            @endphp

            <article class="task-card @if ($hasNewInfo) unread-row @endif">
                <div class="task-card-main">
                    <div class="task-unit-mark">
                        <strong>{{ str($assignment->unit->kode)->limit(4, '') }}</strong>
                    </div>
                    <div>
                        <div class="task-card-title">
                            <div>
                                <h3>
                                    {{ $assignment->unit->nama }}
                                    @if ($hasNewInfo)
                                        <span class="new-info-dot" title="Informasi penugasan baru"></span>
                                    @endif
                                </h3>
                                <p>{{ $assignment->auditPeriod->nama }} · {{ $assignment->auditPeriod->tahun_akademik }}</p>
                            </div>
                            <span class="badge {{ $isLead ? 'success' : 'neutral' }}">{{ $isLead ? 'Lead Auditor' : 'Anggota Tim' }}</span>
                        </div>

                        <div class="task-meta-grid">
                            <div>
                                <span>Lead Auditor</span>
                                <strong>{{ $assignment->leadAuditor->name }}</strong>
                            </div>
                            <div>
                                <span>Anggota</span>
                                <strong>{{ $assignment->auditors->where('id', '!=', $assignment->lead_auditor_id)->pluck('name')->join(', ') ?: '-' }}</strong>
                            </div>
                            <div>
                                <span>Desk Evaluation</span>
                                <strong>{{ $assignment->tanggal_desk_evaluation?->format('d/m/Y') ?? 'Belum dijadwalkan' }}</strong>
                            </div>
                            <div>
                                <span>Visitasi</span>
                                <strong>{{ $visit?->tanggal?->format('d/m/Y') ?? ($assignment->jadwal_visitasi?->format('d/m/Y') ?? 'Belum dijadwalkan') }}</strong>
                            </div>
                        </div>

                        <div class="task-progress-grid">
                            <div>
                                <div class="smart-list-row">
                                    <strong>Evaluasi Diri</strong>
                                    <span class="badge {{ $selfProgress === 100 ? 'success' : 'warning' }}">{{ $selfProgress }}%</span>
                                </div>
                                <div class="progress"><div class="progress-bar" style="width: {{ $selfProgress }}%"></div></div>
                            </div>
                            <div>
                                <div class="smart-list-row">
                                    <strong>Desk Evaluation</strong>
                                    <span class="badge {{ $deskProgress === 100 ? 'success' : 'warning' }}">{{ $deskProgress }}%</span>
                                </div>
                                <div class="progress"><div class="progress-bar" style="width: {{ $deskProgress }}%"></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="task-card-side">
                    <div class="task-status-box">
                        <span>Temuan Aktif</span>
                        <strong>{{ $assignment->active_findings_count }}</strong>
                    </div>
                    <div class="task-actions">
                        <a class="button" href="{{ route('auditor.desk-evaluation.show', $assignment) }}">Periksa</a>
                        <a class="button secondary" href="{{ route('auditor.visitations.show', $assignment) }}">Visitasi</a>
                        <a class="button secondary" href="{{ route('auditor.findings', ['unit_id' => $assignment->unit_id]) }}">Temuan</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="panel">
                <x-visual.empty-state title="Belum ada tugas aktif" :message="$emptyMessage" />
            </div>
        @endforelse
    </div>

    <div class="pagination">{{ $assignments->links() }}</div>
@endsection
