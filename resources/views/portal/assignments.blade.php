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
        $auditor = auth()->user();
        $profilePhotoUrl = $auditor->profile_photo_path
            ? route('profile.photo.show', $auditor).'?v='.substr(md5($auditor->profile_photo_path), 0, 12)
            : null;
        $nameParts = preg_split('/\s+/', trim($auditor->name)) ?: [];
        $initials = collect($nameParts)->filter()->take(2)->map(fn ($word) => mb_substr($word, 0, 1))->implode('');
        $leadAssignments = $currentItems->where('lead_auditor_id', $auditor->id)->count();
        $memberAssignments = max($currentItems->count() - $leadAssignments, 0);
        $deskProgressAverage = $currentItems->isNotEmpty()
            ? (int) round($currentItems->average(function ($assignment) {
                $total = max((int) ($assignment->desk_total ?? 0), 1);

                return ((int) ($assignment->desk_checked ?? 0) / $total) * 100;
            }))
            : 0;
        $nextVisit = $currentItems
            ->filter(fn ($assignment) => $assignment->visit?->tanggal && $assignment->visit->tanggal->gte(today()))
            ->sortBy(fn ($assignment) => $assignment->visit->tanggal)
            ->first();
    @endphp

    <section class="auditor-task-sheet" aria-labelledby="auditor-sheet-name">
        <div class="auditor-sheet-cover" aria-hidden="true"><span></span><span></span><span></span></div>

        <div class="auditor-sheet-content">
            <aside class="auditor-sheet-photo-column">
                <div
                    class="auditor-profile-photo-card @if ($profilePhotoUrl) has-photo @endif"
                    @if ($profilePhotoUrl)
                        style="--auditor-profile-photo: url('{{ $profilePhotoUrl }}'); --photo-x: {{ $auditor->profile_photo_focus_x ?? 50 }}%; --photo-y: {{ $auditor->profile_photo_focus_y ?? 50 }}%;"
                    @endif
                >
                    @unless ($profilePhotoUrl)
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <strong>{{ $initials ?: 'AU' }}</strong>
                    @endunless
                    <div class="auditor-photo-caption">
                        <span>Auditor SMART SIAMI</span>
                        <strong>{{ $auditor->name }}</strong>
                    </div>
                </div>
                <a class="auditor-profile-link" href="{{ route('profile.edit') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"></path></svg>
                    Kelola Profil Auditor
                </a>
            </aside>

            <div class="auditor-sheet-identity">
                <div class="auditor-sheet-badges">
                    <span class="auditor-profile-status"><i></i> Auditor Aktif</span>
                    <span class="auditor-profile-type">Tim Audit Internal</span>
                </div>

                <span class="auditor-sheet-eyebrow">Workspace Auditor &bull; Lembar kerja personal</span>
                <h2 id="auditor-sheet-name">{{ $auditor->name }}</h2>
                <p class="auditor-sheet-code">{{ $auditor->nip_nidn ?: 'NIP/NIDN belum dilengkapi' }} <span>&bull;</span> {{ $totalAssignments }} penugasan aktif</p>

                <div class="auditor-identity-grid">
                    <div class="auditor-identity-item">
                        <span class="auditor-identity-icon tone-blue"><svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"></path><path d="m4 6 8 6 8-6"></path></svg></span>
                        <div><span>Email Auditor</span><strong>{{ $auditor->email }}</strong></div>
                    </div>
                    <div class="auditor-identity-item">
                        <span class="auditor-identity-icon tone-teal"><svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.4 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"></path></svg></span>
                        <div><span>Nomor Telepon</span><strong>{{ $auditor->phone ?: 'Belum dilengkapi' }}</strong></div>
                    </div>
                    <div class="auditor-identity-item">
                        <span class="auditor-identity-icon tone-violet"><svg viewBox="0 0 24 24"><rect x="8" y="2" width="8" height="4" rx="1"></rect><path d="M16 4h2a2 2 0 0 1 2 2v14H4V6a2 2 0 0 1 2-2h2M8 12h8M8 16h6"></path></svg></span>
                        <div><span>Peran Utama</span><strong>{{ $leadAssignments }} Lead Auditor</strong></div>
                    </div>
                    <div class="auditor-identity-item">
                        <span class="auditor-identity-icon tone-orange"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path><circle cx="9.5" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path></svg></span>
                        <div><span>Kolaborasi Tim</span><strong>{{ $memberAssignments }} sebagai Anggota</strong></div>
                    </div>
                </div>

                <nav class="auditor-profile-actions" aria-label="Akses cepat auditor">
                    <a class="is-primary" href="{{ route('auditor.desk-evaluation') }}"><x-ui-icon name="eye" /> Desk Evaluation</a>
                    <a href="{{ route('auditor.visitations') }}"><x-ui-icon name="calendar" /> Visitasi</a>
                    <a href="{{ route('auditor.findings') }}"><x-ui-icon name="alert" /> Temuan</a>
                </nav>
            </div>

            <aside class="auditor-audit-snapshot">
                <div class="auditor-snapshot-head">
                    <div>
                        <span>Kesiapan Pemeriksaan</span>
                        <strong>Progres Desk Evaluation</strong>
                    </div>
                    <div class="auditor-progress-ring" style="--progress: {{ $deskProgressAverage }}">
                        <strong>{{ $deskProgressAverage }}%</strong><span>selesai</span>
                    </div>
                </div>
                <div class="auditor-snapshot-list">
                    <div><span>Total Penugasan</span><strong>{{ $totalAssignments }}</strong></div>
                    <div><span>Desk Belum Final</span><strong>{{ $pendingDesk }}</strong></div>
                    <div><span>Temuan Aktif</span><strong>{{ $totalFindings }}</strong></div>
                </div>
                <div class="auditor-snapshot-deadline">
                    <span>Visitasi Berikutnya</span>
                    <strong>{{ $nextVisit?->visit?->tanggal?->translatedFormat('d M Y') ?? 'Belum dijadwalkan' }}</strong>
                    <small>{{ $nextVisit?->unit?->nama ?? 'Pantau agenda visitasi secara berkala' }}</small>
                </div>
            </aside>
        </div>
    </section>

    <section class="auditor-task-metrics" aria-label="Ringkasan tugas auditor">
        <a class="auditor-task-metric tone-blue" href="{{ route('auditor.desk-evaluation') }}">
            <span class="auditor-metric-icon"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.3-4.3M8 11l2 2 4-4"></path></svg></span>
            <div><span>Desk Belum Final</span><strong>{{ $pendingDesk }}</strong><small>Lanjutkan pemeriksaan instrumen</small></div>
            <svg class="auditor-metric-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
        </a>
        <a class="auditor-task-metric tone-violet" href="{{ route('auditor.visitations') }}">
            <span class="auditor-metric-icon"><svg viewBox="0 0 24 24"><path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11z"></path><circle cx="12" cy="10" r="2.5"></circle></svg></span>
            <div><span>Visitasi Mendatang</span><strong>{{ $upcomingVisits }}</strong><small>Agenda pada halaman ini</small></div>
            <svg class="auditor-metric-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
        </a>
        <a class="auditor-task-metric tone-red" href="{{ route('auditor.findings') }}">
            <span class="auditor-metric-icon"><svg viewBox="0 0 24 24"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"></path></svg></span>
            <div><span>Temuan Aktif</span><strong>{{ $totalFindings }}</strong><small>Memerlukan pemantauan auditor</small></div>
            <svg class="auditor-metric-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
        </a>
    </section>

    <div class="auditor-task-list-heading">
        <div><span>Portofolio penugasan</span><h2>Daftar Unit yang Diaudit</h2><p>Pilih unit untuk melanjutkan pemeriksaan, visitasi, atau pemantauan temuan.</p></div>
        <b>{{ $totalAssignments }} tugas aktif</b>
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
                        <a class="button with-icon" href="{{ route('auditor.desk-evaluation.show', $assignment) }}"><x-ui-icon name="eye" /> Periksa</a>
                        <a class="button secondary with-icon" href="{{ route('auditor.visitations.show', $assignment) }}"><x-ui-icon name="calendar" /> Visitasi</a>
                        <a class="button secondary with-icon" href="{{ route('auditor.findings', ['unit_id' => $assignment->unit_id]) }}"><x-ui-icon name="alert" /> Temuan</a>
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
