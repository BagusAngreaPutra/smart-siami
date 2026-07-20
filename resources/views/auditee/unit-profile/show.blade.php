@extends('layouts.app')

@section('title', 'Profil Unit - SMART SIAMI')
@section('page_title', 'Profil Unit')

@section('content')
    @if (! $unit)
        <section class="unit-profile-empty">
            <span class="unit-profile-empty-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path><path d="M9 8h1M14 8h1M9 12h1M14 12h1"></path></svg>
            </span>
            <div>
                <span class="unit-profile-eyebrow">Identitas belum tersedia</span>
                <h2>Akun belum terhubung ke unit</h2>
                <p>Hubungi Administrator agar akun auditee Anda dikaitkan dengan unit yang benar sebelum memulai proses audit.</p>
            </div>
        </section>
    @else
        @php
            $unitInitials = strtoupper(collect(preg_split('/\s+/', trim($unit->nama)))->filter()->take(2)->map(fn ($word) => mb_substr($word, 0, 1))->join(''));
            $profilePhotoUrl = $profileUser->profile_photo_path
                ? route('profile.photo.show', $profileUser).'?v='.substr(md5($profileUser->profile_photo_path), 0, 12)
                : null;
            $activeFindingCount = (int) $findingCounts->only(['aktif', 'dalam_tindak_lanjut', 'menunggu_verifikasi', 'terlambat'])->sum();
            $period = $assignment?->auditPeriod;
            $visitDate = $assignment?->visit?->tanggal?->format('d M Y')
                ?? $assignment?->jadwal_visitasi?->format('d M Y')
                ?? 'Belum dijadwalkan';
        @endphp

        <section class="auditee-unit-sheet" aria-labelledby="unit-sheet-name">
            <div class="unit-sheet-cover" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>

            <div class="unit-sheet-content">
                <aside class="unit-sheet-photo-column">
                    <div
                        class="unit-profile-photo-card @if ($profilePhotoUrl) has-photo @endif"
                        @if ($profilePhotoUrl)
                            style="--unit-profile-photo: url('{{ $profilePhotoUrl }}'); --photo-x: {{ $profileUser->profile_photo_focus_x ?? 50 }}%; --photo-y: {{ $profileUser->profile_photo_focus_y ?? 50 }}%;"
                        @endif
                    >
                        @unless ($profilePhotoUrl)
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path><path d="M9 8h1M14 8h1M9 12h1M14 12h1M9 16h1M14 16h1"></path></svg>
                            <strong>{{ $unitInitials ?: 'UN' }}</strong>
                        @endunless
                        <div class="unit-photo-caption">
                            <span>Pengelola profil unit</span>
                            <strong>{{ $profileUser->name }}</strong>
                        </div>
                    </div>
                    <a class="unit-photo-profile-link" href="{{ route('profile.edit') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"></path></svg>
                        Kelola foto akun
                    </a>
                </aside>

                <div class="unit-sheet-identity">
                    <div class="unit-sheet-badges">
                        <span class="unit-profile-status {{ $unit->is_active ? 'is-active' : 'is-inactive' }}">
                            <i></i>{{ $unit->is_active ? 'Unit Aktif' : 'Unit Nonaktif' }}
                        </span>
                        <span class="unit-profile-type">{{ $jenisUnitOptions[$unit->jenis_unit] ?? $unit->jenis_unit }}</span>
                    </div>

                    <span class="unit-profile-eyebrow">Lembar identitas unit</span>
                    <h2 id="unit-sheet-name">{{ $unit->nama }}</h2>
                    <p class="unit-sheet-code">{{ $unit->kode }} <span>&bull;</span> {{ $unit->fakultas_induk ?: 'Unit independen' }}</p>

                    <div class="unit-identity-grid">
                        <div class="unit-identity-item">
                            <span class="unit-identity-icon tone-violet"><svg viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg></span>
                            <div><span>Pimpinan Unit</span><strong>{{ $unit->nama_pimpinan ?: 'Belum dilengkapi' }}</strong></div>
                        </div>
                        <div class="unit-identity-item">
                            <span class="unit-identity-icon tone-blue"><svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"></path><path d="m4 6 8 6 8-6"></path></svg></span>
                            <div><span>Email Unit</span><strong>{{ $unit->email ?: 'Belum dilengkapi' }}</strong></div>
                        </div>
                        <div class="unit-identity-item">
                            <span class="unit-identity-icon tone-teal"><svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.4 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"></path></svg></span>
                            <div><span>Nomor Telepon</span><strong>{{ $unit->phone ?: 'Belum dilengkapi' }}</strong></div>
                        </div>
                        <div class="unit-identity-item">
                            <span class="unit-identity-icon tone-orange"><svg viewBox="0 0 24 24"><path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path><path d="M9 8h1M14 8h1M9 12h1M14 12h1"></path></svg></span>
                            <div><span>Fakultas Induk</span><strong>{{ $unit->fakultas_induk ?: 'Tidak ada' }}</strong></div>
                        </div>
                    </div>

                    <nav class="unit-profile-actions" aria-label="Akses cepat unit">
                        <a class="is-primary" href="{{ route('auditee.self-evaluations') }}">
                            <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"></path></svg>
                            Evaluasi Diri
                        </a>
                        <a href="{{ route('auditee.documents') }}">
                            <svg viewBox="0 0 24 24"><path d="m21.4 11.6-8.5 8.5a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.2 9.2a2 2 0 1 1-2.8-2.8l8.5-8.5"></path></svg>
                            Bukti Dokumen
                        </a>
                        <a href="{{ route('auditee.findings-followups') }}">
                            <svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 1-15.5 6.2L3 16"></path><path d="M3 21v-5h5M3 12A9 9 0 0 1 18.5 5.8L21 8M21 3v5h-5"></path></svg>
                            Tindak Lanjut
                        </a>
                    </nav>
                </div>

                <aside class="unit-audit-snapshot">
                    <div class="unit-snapshot-head">
                        <div>
                            <span>Audit aktif</span>
                            <strong>{{ $period?->nama ?? 'Belum tersedia' }}</strong>
                        </div>
                        <span class="unit-progress-ring" style="--progress: {{ $selfAssessmentProgress }}">
                            <strong>{{ $selfAssessmentProgress }}%</strong>
                        </span>
                    </div>
                    <div class="unit-snapshot-list">
                        <div><span>Tahun Akademik</span><strong>{{ $period?->tahun_akademik ?? '-' }}</strong></div>
                        <div><span>Lead Auditor</span><strong>{{ $assignment?->leadAuditor?->name ?? 'Belum ditetapkan' }}</strong></div>
                        <div><span>Jadwal Visitasi</span><strong>{{ $visitDate }}</strong></div>
                    </div>
                    <div class="unit-snapshot-deadline">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg>
                        <div><span>Batas evaluasi diri</span><strong>{{ $period?->batas_evaluasi_diri?->format('d M Y') ?? 'Belum ditentukan' }}</strong></div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="unit-profile-metrics" aria-label="Ringkasan profil unit">
            <a class="unit-profile-metric tone-blue" href="{{ route('auditee.self-evaluations') }}">
                <span class="unit-metric-icon"><svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"></path></svg></span>
                <div><span>Progres Evaluasi Diri</span><strong>{{ $selfAssessmentProgress }}%</strong><small>{{ $selfAssessmentProgress === 100 ? 'Seluruh instrumen siap' : 'Lanjutkan pengisian instrumen' }}</small></div>
                <svg class="unit-metric-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
            </a>
            <a class="unit-profile-metric tone-red" href="{{ route('auditee.findings-followups') }}">
                <span class="unit-metric-icon"><svg viewBox="0 0 24 24"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"></path><path d="M12 9v4M12 17h.01"></path></svg></span>
                <div><span>Temuan Aktif</span><strong>{{ $activeFindingCount }}</strong><small>{{ $activeFindingCount > 0 ? 'Memerlukan perhatian unit' : 'Tidak ada temuan terbuka' }}</small></div>
                <svg class="unit-metric-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
            </a>
            <a class="unit-profile-metric tone-orange" href="{{ route('auditee.findings-followups') }}">
                <span class="unit-metric-icon"><svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 1-15.5 6.2L3 16"></path><path d="M3 21v-5h5M3 12A9 9 0 0 1 18.5 5.8L21 8M21 3v5h-5"></path></svg></span>
                <div><span>Tindak Lanjut Berjalan</span><strong>{{ $pendingFollowUps }}</strong><small>Pantau progres perbaikan</small></div>
                <svg class="unit-metric-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
            </a>
        </section>

        <div class="unit-profile-detail-grid">
            <section class="unit-profile-panel unit-assignment-panel">
                <header class="unit-panel-header">
                    <span class="unit-panel-icon tone-blue"><svg viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"></rect><path d="M8 8h8M8 12h8M8 16h5"></path></svg></span>
                    <div><span>Periode berjalan</span><h3>Penugasan Aktif</h3></div>
                    @if ($assignment)<span class="unit-panel-status">Aktif</span>@endif
                </header>
                @if ($assignment)
                    <div class="unit-assignment-grid">
                        <div><span>Periode Audit</span><strong>{{ $period->nama }}</strong></div>
                        <div><span>Tahun Akademik</span><strong>{{ $period->tahun_akademik }}</strong></div>
                        <div><span>Batas Evaluasi Diri</span><strong>{{ $period->batas_evaluasi_diri?->format('d M Y') ?? '-' }}</strong></div>
                        <div><span>Batas Tindak Lanjut</span><strong>{{ $period->batas_tindak_lanjut?->format('d M Y') ?? '-' }}</strong></div>
                        <div><span>Jadwal Visitasi</span><strong>{{ $visitDate }}</strong></div>
                        <div><span>Status Penugasan</span><strong>{{ ucfirst($assignment->status) }}</strong></div>
                    </div>
                @else
                    <x-visual.empty-state title="Belum ada penugasan aktif" message="Data penugasan akan tampil setelah Admin menetapkan periode audit untuk unit Anda." />
                @endif
            </section>

            <section class="unit-profile-panel unit-auditor-panel">
                <header class="unit-panel-header">
                    <span class="unit-panel-icon tone-violet"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path><circle cx="9.5" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path></svg></span>
                    <div><span>Kontak audit</span><h3>Tim Auditor</h3></div>
                </header>
                @if ($assignment)
                    <div class="unit-auditor-list">
                        <div class="unit-auditor-item">
                            <x-visual.avatar :name="$assignment->leadAuditor->name" size="md" />
                            <div><strong>{{ $assignment->leadAuditor->name }}</strong><span>{{ $assignment->leadAuditor->email }}</span></div>
                            <b>Lead</b>
                        </div>
                        @foreach ($assignment->auditors as $auditor)
                            <div class="unit-auditor-item">
                                <x-visual.avatar :name="$auditor->name" size="md" />
                                <div><strong>{{ $auditor->name }}</strong><span>{{ $auditor->email }}</span></div>
                                <b class="is-member">{{ ucfirst($auditor->pivot->peran_dalam_tim ?? 'Anggota') }}</b>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-visual.empty-state title="Tim auditor belum tersedia" message="Auditor akan tampil setelah penugasan dibuat." />
                @endif
            </section>

            <section class="unit-profile-panel unit-findings-panel">
                <header class="unit-panel-header">
                    <span class="unit-panel-icon tone-red"><svg viewBox="0 0 24 24"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"></path><path d="M12 9v4M12 17h.01"></path></svg></span>
                    <div><span>Perlu perhatian</span><h3>Temuan Aktif Terbaru</h3></div>
                    <a href="{{ route('auditee.findings-followups') }}">Lihat semua</a>
                </header>
                <div class="unit-finding-list">
                    @forelse ($latestFindings as $finding)
                        <a class="unit-finding-item" href="{{ route('auditee.findings-followups.show', $finding) }}">
                            <span class="unit-finding-number">{{ $finding->nomor_temuan }}</span>
                            <div><strong>{{ $finding->rekomendasi_auditor }}</strong><span>Status TL: {{ $finding->latestFollowUp ? $followUpStatusOptions[$finding->latestFollowUp->status] : 'Belum Dibuat' }}</span></div>
                            <span class="unit-finding-status {{ $finding->status === 'terlambat' ? 'is-overdue' : '' }}">{{ $findingStatusOptions[$finding->status] }}</span>
                        </a>
                    @empty
                        <x-visual.empty-state title="Belum ada temuan aktif" message="Temuan aktif untuk unit Anda akan tampil di sini." />
                    @endforelse
                </div>
            </section>

            <section class="unit-profile-panel unit-assessment-panel">
                <header class="unit-panel-header">
                    <span class="unit-panel-icon tone-teal"><svg viewBox="0 0 24 24"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2"></path></svg></span>
                    <div><span>Kesiapan instrumen</span><h3>Status Evaluasi Diri</h3></div>
                    <strong class="unit-assessment-value">{{ $selfAssessmentProgress }}%</strong>
                </header>
                <div class="unit-assessment-content">
                    <div class="unit-assessment-progress"><i style="width: {{ $selfAssessmentProgress }}%"></i></div>
                    <div class="unit-assessment-statuses">
                        @foreach ($assessmentStatusOptions as $status => $label)
                            <div><span>{{ $label }}</span><strong>{{ (int) ($assessmentCounts[$status] ?? 0) }}</strong></div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
    @endif
@endsection
