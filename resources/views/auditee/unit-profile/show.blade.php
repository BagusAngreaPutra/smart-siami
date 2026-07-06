@extends('layouts.app')

@section('title', 'Profil Unit - SMART SIAMI')
@section('page_title', 'Profil Unit')

@section('content')
    @if (! $unit)
        <div class="panel dashboard-panel-accent danger">
            <h3 class="panel-title">Akun belum terhubung ke unit</h3>
            <p class="muted">Hubungi Admin agar akun auditee Anda dikaitkan dengan unit yang benar.</p>
        </div>
    @else
        <section class="unit-profile-hero">
            <div>
                <span class="quick-guide-eyebrow">Identitas Unit</span>
                <h3>{{ $unit->nama }}</h3>
                <p>{{ $unit->kode }} · {{ $jenisUnitOptions[$unit->jenis_unit] ?? $unit->jenis_unit }}</p>
                <div class="hero-actions">
                    <a class="hero-action" href="{{ route('auditee.self-evaluations') }}">Evaluasi Diri</a>
                    <a class="hero-action" href="{{ route('auditee.documents') }}">Bukti Dokumen</a>
                    <a class="hero-action" href="{{ route('auditee.findings-followups') }}">Tindak Lanjut</a>
                </div>
            </div>
            <div class="unit-profile-stamp">
                <span>Status Unit</span>
                <strong>{{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}</strong>
                <small>{{ $assignment ? 'Ada penugasan audit aktif' : 'Belum ada penugasan audit aktif' }}</small>
            </div>
        </section>

        <div class="dashboard-kpi-grid">
            <a class="kpi-card neutral" href="{{ route('auditee.self-evaluations') }}">
                <span class="kpi-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"></path></svg>
                </span>
                <span class="kpi-body">
                    <span class="muted">Progres Evaluasi Diri</span>
                    <strong class="stat-value">{{ $selfAssessmentProgress }}%</strong>
                    <span class="kpi-hint">Buka pengisian</span>
                </span>
            </a>
            <a class="kpi-card danger" href="{{ route('auditee.findings-followups') }}">
                <span class="kpi-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"></path><path d="M12 9v4M12 17h.01"></path></svg>
                </span>
                <span class="kpi-body">
                    <span class="muted">Temuan Aktif</span>
                    <strong class="stat-value">{{ (int) $findingCounts->only(['aktif', 'dalam_tindak_lanjut', 'menunggu_verifikasi', 'terlambat'])->sum() }}</strong>
                    <span class="kpi-hint">Cek temuan</span>
                </span>
            </a>
            <a class="kpi-card warning" href="{{ route('auditee.findings-followups') }}">
                <span class="kpi-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 1-15.5 6.2L3 16"></path><path d="M3 21v-5h5"></path><path d="M3 12A9 9 0 0 1 18.5 5.8L21 8"></path><path d="M21 3v5h-5"></path></svg>
                </span>
                <span class="kpi-body">
                    <span class="muted">Tindak Lanjut Berjalan</span>
                    <strong class="stat-value">{{ $pendingFollowUps }}</strong>
                    <span class="kpi-hint">Pantau perbaikan</span>
                </span>
            </a>
        </div>

        <div class="dashboard-grid">
            <div class="panel dashboard-panel-accent">
                <h3 class="panel-title">Data Unit</h3>
                <div class="profile-info-list">
                    <div><span>Kode Unit</span><strong>{{ $unit->kode }}</strong></div>
                    <div><span>Nama Unit</span><strong>{{ $unit->nama }}</strong></div>
                    <div><span>Jenis Unit</span><strong>{{ $jenisUnitOptions[$unit->jenis_unit] ?? $unit->jenis_unit }}</strong></div>
                    <div><span>Fakultas Induk</span><strong>{{ $unit->fakultas_induk ?: '-' }}</strong></div>
                    <div><span>Nama Pimpinan</span><strong>{{ $unit->nama_pimpinan ?: '-' }}</strong></div>
                    <div><span>Email Unit</span><strong>{{ $unit->email ?: '-' }}</strong></div>
                    <div><span>Nomor Telepon</span><strong>{{ $unit->phone ?: '-' }}</strong></div>
                </div>
            </div>

            <div class="panel dashboard-panel-accent warning">
                <h3 class="panel-title">Penugasan Aktif</h3>
                @if ($assignment)
                    <div class="profile-info-list">
                        <div><span>Periode</span><strong>{{ $assignment->auditPeriod->nama }}</strong></div>
                        <div><span>Tahun Akademik</span><strong>{{ $assignment->auditPeriod->tahun_akademik }}</strong></div>
                        <div><span>Batas Evaluasi Diri</span><strong>{{ $assignment->auditPeriod->batas_evaluasi_diri->format('d/m/Y') }}</strong></div>
                        <div><span>Batas Tindak Lanjut</span><strong>{{ $assignment->auditPeriod->batas_tindak_lanjut->format('d/m/Y') }}</strong></div>
                        <div><span>Lead Auditor</span><strong>{{ $assignment->leadAuditor->name }}</strong></div>
                        <div><span>Jadwal Visitasi</span><strong>{{ $assignment->visit?->tanggal?->format('d/m/Y') ?? ($assignment->jadwal_visitasi?->format('d/m/Y') ?? 'Belum dijadwalkan') }}</strong></div>
                    </div>
                @else
                    <x-visual.empty-state title="Belum ada penugasan aktif" message="Data penugasan akan tampil setelah Admin menetapkan periode dan penugasan audit untuk unit Anda." />
                @endif
            </div>

            <div class="panel dashboard-panel-accent">
                <h3 class="panel-title">Tim Auditor</h3>
                @if ($assignment)
                    <div class="smart-list">
                        <div class="smart-list-item">
                            <div class="smart-list-row">
                                <strong>{{ $assignment->leadAuditor->name }}</strong>
                                <span class="badge success">Lead Auditor</span>
                            </div>
                            <span class="muted">{{ $assignment->leadAuditor->email }}</span>
                        </div>
                        @foreach ($assignment->auditors as $auditor)
                            <div class="smart-list-item">
                                <div class="smart-list-row">
                                    <strong>{{ $auditor->name }}</strong>
                                    <span class="badge neutral">{{ ucfirst($auditor->pivot->peran_dalam_tim ?? 'anggota') }}</span>
                                </div>
                                <span class="muted">{{ $auditor->email }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-visual.empty-state title="Tim auditor belum tersedia" message="Auditor akan tampil setelah penugasan dibuat." />
                @endif
            </div>

            <div class="panel dashboard-panel-accent danger">
                <h3 class="panel-title">Temuan Aktif Terbaru</h3>
                <div class="smart-list">
                    @forelse ($latestFindings as $finding)
                        <a class="smart-list-item" href="{{ route('auditee.findings-followups.show', $finding) }}">
                            <div class="smart-list-row">
                                <strong>{{ $finding->nomor_temuan }}</strong>
                                <span class="badge @if ($finding->status === 'terlambat') danger @else warning @endif">{{ $findingStatusOptions[$finding->status] }}</span>
                            </div>
                            <div>{{ $finding->rekomendasi_auditor }}</div>
                            <span class="muted">Status TL: {{ $finding->latestFollowUp ? $followUpStatusOptions[$finding->latestFollowUp->status] : 'Belum Dibuat' }}</span>
                        </a>
                    @empty
                        <x-visual.empty-state title="Belum ada temuan aktif" message="Temuan aktif untuk unit Anda akan tampil di sini." />
                    @endforelse
                </div>
            </div>
        </div>

        <div class="panel dashboard-panel-accent" style="margin-top:18px">
            <div class="toolbar">
                <div>
                    <h3 class="panel-title">Status Evaluasi Diri</h3>
                    <p class="muted">Ringkasan status instrumen pada penugasan aktif unit.</p>
                </div>
                <span class="badge @if ($selfAssessmentProgress === 100) success @else warning @endif">{{ $selfAssessmentProgress }}%</span>
            </div>
            <div class="progress"><div class="progress-bar" style="width: {{ $selfAssessmentProgress }}%"></div></div>
            <div class="status-pill-grid">
                @foreach ($assessmentStatusOptions as $status => $label)
                    <div class="status-pill">
                        <span>{{ $label }}</span>
                        <strong>{{ (int) ($assessmentCounts[$status] ?? 0) }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
