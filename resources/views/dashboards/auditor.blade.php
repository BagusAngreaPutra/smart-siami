@extends('layouts.app')

@section('title', 'Dashboard Auditor - SMART SIAMI')
@section('page_title', 'Dashboard Auditor')

@section('content')
    @php
        $auditorIcons = [
            'Tugas Aktif' => 'clipboard',
            'Instrumen Belum Diperiksa' => 'file',
            'Klarifikasi Menunggu Tanggapan Auditee' => 'message',
            'Tindak Lanjut Menunggu Verifikasi' => 'refresh',
        ];
        $auditorGuideSteps = [
            [
                'title' => 'Buka unit tugas',
                'description' => 'Pilih unit yang ditugaskan, lalu lihat progres pemeriksaan.',
                'url' => route('auditor.desk-evaluation'),
            ],
            [
                'title' => 'Periksa jawaban dan bukti',
                'description' => 'Beri status bukti, skor bila perlu, dan catatan auditor.',
                'url' => route('auditor.desk-evaluation'),
            ],
            [
                'title' => 'Minta klarifikasi',
                'description' => 'Gunakan klarifikasi jika jawaban atau dokumen belum jelas.',
                'url' => route('auditor.clarifications'),
            ],
            [
                'title' => 'Finalisasi hasil',
                'description' => 'Lanjutkan ke visitasi, temuan, dan verifikasi tindak lanjut.',
                'url' => route('auditor.findings'),
            ],
        ];
        $auditorGuideActions = [
            ['label' => 'Buka Panduan Lengkap', 'url' => route('auditor.guide')],
            ['label' => 'Mulai Desk Evaluation', 'url' => route('auditor.desk-evaluation')],
            ['label' => 'Kelola Temuan', 'url' => route('auditor.findings')],
        ];
    @endphp

    <x-dashboard.quick-guide
        eyebrow="Panduan Auditor"
        title="Masih bingung alur auditnya?"
        description="Ikuti alur sederhana ini: cek tugas, periksa instrumen, minta klarifikasi bila perlu, lalu finalisasi hasil audit."
        :steps="$auditorGuideSteps"
        :actions="$auditorGuideActions"
    />

    <section class="dashboard-hero">
        <div class="dashboard-hero-content">
            <div>
                <span class="dashboard-eyebrow">Workspace Auditor</span>
                <h3>Prioritaskan pemeriksaan yang paling mendesak</h3>
                <p>Lihat progres desk evaluation, klarifikasi yang masih terbuka, jadwal visitasi, dan draft temuan dalam tampilan yang cepat dipindai.</p>
                <div class="hero-actions">
                    <a class="hero-action" href="{{ route('auditor.desk-evaluation') }}">Desk Evaluation</a>
                    <a class="hero-action" href="{{ route('auditor.clarifications') }}">Klarifikasi</a>
                    <a class="hero-action" href="{{ route('auditor.findings') }}">Temuan</a>
                </div>
            </div>
            <div class="hero-filter">
                <span class="dashboard-eyebrow">Tugas aktif</span>
                <strong style="font-size:34px;line-height:1">{{ $assignments->count() }}</strong>
                <span>unit penugasan dalam periode aktif</span>
            </div>
        </div>
    </section>

    @foreach ($deadlineWarnings as $assignment)
        <div class="dashboard-alert">
            <span class="dashboard-alert-icon">!</span>
            <div>
                <strong>Deadline mendekat</strong>
                <div>Batas desk evaluation {{ $assignment->unit->nama }} pada {{ $assignment->auditPeriod->batas_desk_evaluation->format('d/m/Y') }} sudah mendekat.</div>
            </div>
        </div>
    @endforeach

    <div class="dashboard-kpi-grid">
        @foreach ($cards as $card)
            <x-dashboard.kpi-card :card="$card" :icon="$auditorIcons[$card['label']] ?? 'dashboard'" />
        @endforeach
    </div>

    <div class="dashboard-grid">
        <div class="panel dashboard-panel-accent">
            <h3 class="panel-title">Unit Tugas dan Progres Desk Evaluation</h3>
            <div class="smart-list">
                @forelse ($assignments as $assignment)
                    <a class="smart-list-item" href="{{ route('auditor.desk-evaluation.show', $assignment) }}">
                        <div class="smart-list-row">
                            <strong>{{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}</strong>
                            <span class="badge @if ($assignment->desk_progress === 100) success @else warning @endif">{{ $assignment->desk_progress }}%</span>
                        </div>
                        <div class="progress"><div class="progress-bar" style="width: {{ $assignment->desk_progress }}%"></div></div>
                        <div class="muted">{{ $assignment->desk_checked }}/{{ $assignment->desk_total }} instrumen diperiksa</div>
                    </a>
                @empty
                    <x-visual.empty-state title="Belum ada tugas aktif" message="Tugas audit akan muncul setelah admin membuat penugasan." />
                @endforelse
            </div>
        </div>

        <div class="panel dashboard-panel-accent warning">
            <h3 class="panel-title">Jadwal Visitasi Mendatang</h3>
            <div class="smart-list">
                @forelse ($upcomingVisits as $visit)
                    <a class="smart-list-item" href="{{ route('auditor.visitations.show', $visit->assignment) }}">
                        <div class="smart-list-row">
                            <strong>{{ $visit->assignment->unit->kode }} - {{ $visit->assignment->unit->nama }}</strong>
                            <span class="badge warning">{{ $visit->tanggal->format('d/m/Y') }} {{ $visit->waktu_mulai ?? '' }}</span>
                        </div>
                        <div>{{ $visit->assignment->auditPeriod->nama }}</div>
                    </a>
                @empty
                    <x-visual.empty-state title="Belum ada visitasi mendatang" message="Jadwal yang sudah ditetapkan akan tampil di sini." />
                @endforelse
            </div>
        </div>

        <div class="panel dashboard-panel-accent">
            <h3 class="panel-title">Temuan Draft</h3>
            <div class="smart-list">
                @forelse ($draftFindings as $finding)
                    <a class="smart-list-item" href="{{ route('auditor.findings.show', $finding) }}">
                        <div class="smart-list-row">
                            <strong>Draft #{{ $finding->id }}</strong>
                            <span class="badge warning">{{ $finding->target_penyelesaian->format('d/m/Y') }}</span>
                        </div>
                        <div>{{ $finding->assignment->unit->kode }} - {{ $finding->standard->kode }}</div>
                    </a>
                @empty
                    <x-visual.empty-state title="Tidak ada temuan draft" message="Temuan yang belum difinalisasi akan tampil di sini." />
                @endforelse
            </div>
        </div>
    </div>
@endsection
