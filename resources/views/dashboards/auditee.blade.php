@extends('layouts.app')

@section('title', 'Dashboard Auditee - SMART SIAMI')
@section('page_title', 'Dashboard Auditee')

@section('content')
    @php
        $auditeeIcons = [
            'Total Instrumen' => 'file',
            'Belum Diisi' => 'clock',
            'Perlu Klarifikasi' => 'message',
            'Sudah Final' => 'check',
        ];
        $auditeeGuideSteps = [
            [
                'title' => 'Isi evaluasi diri',
                'description' => 'Jawab instrumen per standar dan simpan sebagai draft bila belum selesai.',
                'url' => route('auditee.self-evaluations'),
            ],
            [
                'title' => 'Lengkapi bukti dokumen',
                'description' => 'Unggah file atau tautan pendukung untuk instrumen terkait.',
                'url' => route('auditee.documents'),
            ],
            [
                'title' => 'Jawab klarifikasi auditor',
                'description' => 'Perbaiki jawaban atau tambahkan bukti saat auditor meminta penjelasan.',
                'url' => route('auditee.clarifications'),
            ],
            [
                'title' => 'Selesaikan tindak lanjut',
                'description' => 'Tanggapi temuan, ajukan bukti perbaikan, dan pantau verifikasinya.',
                'url' => route('auditee.findings-followups'),
            ],
        ];
        $auditeeGuideActions = [
            ['label' => 'Buka Panduan Lengkap', 'url' => route('auditee.guide')],
            ['label' => 'Mulai Evaluasi Diri', 'url' => route('auditee.self-evaluations')],
            ['label' => 'Cek Temuan', 'url' => route('auditee.findings-followups')],
        ];
    @endphp

    <x-dashboard.quick-guide
        eyebrow="Panduan Auditee"
        title="Masih bingung harus mengerjakan apa?"
        description="Mulai dari evaluasi diri, lengkapi bukti, jawab klarifikasi, lalu tindak lanjuti temuan sampai diverifikasi auditor."
        :steps="$auditeeGuideSteps"
        :actions="$auditeeGuideActions"
    />

    <section class="dashboard-hero">
        <div class="dashboard-hero-content">
            <div>
                <span class="dashboard-eyebrow">Dashboard Unit</span>
                <h3>Lengkapi evaluasi diri dengan lebih terarah</h3>
                <p>Fokus pada instrumen yang belum selesai, klarifikasi yang perlu dijawab, jadwal visitasi, dan tindak lanjut yang mendekati target.</p>
                <div class="hero-actions">
                    <a class="hero-action" href="{{ route('auditee.self-evaluations') }}">Isi Evaluasi Diri</a>
                    <a class="hero-action" href="{{ route('auditee.documents') }}">Bukti Dokumen</a>
                    <a class="hero-action" href="{{ route('auditee.findings-followups') }}">Tindak Lanjut</a>
                </div>
            </div>
            <div class="hero-filter">
                <span class="dashboard-eyebrow">Kesiapan Unit</span>
                <strong style="font-size:34px;line-height:1">{{ $readinessProgress }}%</strong>
                <span>{{ $assignment?->unit?->kode ?? 'Belum ada unit aktif' }}</span>
            </div>
        </div>
    </section>

    <div class="dashboard-kpi-grid">
        @foreach ($cards as $card)
            <x-dashboard.kpi-card :card="$card" :icon="$auditeeIcons[$card['label']] ?? 'dashboard'" />
        @endforeach
    </div>

    <div class="panel dashboard-panel-accent">

        <div class="section-block">
            <div class="toolbar">
                <div>
                    <h3 class="panel-title">Kesiapan Evaluasi Diri</h3>
                    <p class="muted">{{ $assignment?->auditPeriod->nama ?? 'Belum ada periode aktif untuk unit Anda.' }}</p>
                </div>
                <span class="badge @if ($readinessProgress === 100) success @else warning @endif">{{ $readinessProgress }}%</span>
            </div>
            <div class="progress"><div class="progress-bar" style="width: {{ $readinessProgress }}%"></div></div>
        </div>

        <div class="visual-grid" style="margin-top:18px">
            <div class="section-block visual-gauge-panel">
                <x-visual.gauge :value="$readinessGauge" label="Kesiapan Unit" :caption="$assignment?->unit?->kode" />
            </div>
            <div class="section-block">
                <h3 class="panel-title">Radar Kesiapan per Standar</h3>
                <x-visual.radar :items="$standardScores" title="Radar kesiapan unit per standar" />
            </div>
        </div>

        @if ($remainingDays !== null)
            <div class="@if ($remainingDays < 0) warning @else status @endif" style="margin-top: 16px">
                Batas evaluasi diri: {{ $assignment->auditPeriod->batas_evaluasi_diri->format('d/m/Y') }}
                @if ($remainingDays >= 0)
                    (tersisa {{ (int) $remainingDays }} hari)
                @else
                    (terlewat {{ abs((int) $remainingDays) }} hari)
                @endif
            </div>
        @endif
    </div>

    <div class="dashboard-grid">
        <div class="panel dashboard-panel-accent warning">
            <h3 class="panel-title">Jadwal Visitasi</h3>
            @if ($nextVisit)
                <a class="smart-list-item" href="{{ route('auditee.visit-schedules.show', $nextVisit) }}">
                    <div class="smart-list-row">
                        <strong>{{ $nextVisit->tanggal->format('d/m/Y') }} {{ $nextVisit->waktu_mulai ?? '' }}</strong>
                        <span class="badge warning">{{ $nextVisit->tipe === 'daring' ? 'Daring' : 'Lapangan' }}</span>
                    </div>
                    <div>{{ $nextVisit->tipe === 'daring' ? 'Daring' : 'Lapangan' }}</div>
                    <span class="muted">{{ $nextVisit->lokasi_atau_tautan ?? '-' }}</span>
                </a>
            @else
                <x-visual.empty-state title="Belum dijadwalkan" message="Auditor belum menetapkan jadwal visitasi untuk unit Anda." />
            @endif
        </div>

        <div class="panel dashboard-panel-accent danger">
            <h3 class="panel-title">Temuan Aktif</h3>
            <div class="smart-list">
                @forelse ($activeFindings as $finding)
                    <a class="smart-list-item" href="{{ route('auditee.findings-followups.show', $finding) }}">
                        <div class="smart-list-row">
                            <strong>{{ $finding->nomor_temuan }}</strong>
                            <span class="badge @if ($finding->status === 'terlambat') danger @else warning @endif">{{ \App\Models\Finding::statusOptions()[$finding->status] }}</span>
                        </div>
                        <div>Status TL: {{ $finding->latestFollowUp ? \App\Models\FollowUp::statusOptions()[$finding->latestFollowUp->status] : 'Belum Dibuat' }}</div>
                    </a>
                @empty
                    <x-visual.empty-state title="Belum ada temuan aktif" message="Bagus, belum ada temuan yang perlu ditindaklanjuti saat ini." />
                @endforelse
            </div>
        </div>

        <div class="panel dashboard-panel-accent warning">
            <h3 class="panel-title">Tindak Lanjut Mendesak</h3>
            <div class="smart-list">
                @forelse ($urgentFollowUps as $followUp)
                    @php($late = $followUp->target_penyelesaian->toDateString() < now()->toDateString())
                    <a class="smart-list-item" href="{{ route('auditee.findings-followups.show', $followUp->finding) }}">
                        <div class="smart-list-row">
                            <strong>{{ $followUp->finding->nomor_temuan }}</strong>
                            <span class="badge @if ($late) danger @else warning @endif">Target {{ $followUp->target_penyelesaian->format('d/m/Y') }}</span>
                        </div>
                        <div>{{ $followUp->rencana_tindakan }}</div>
                    </a>
                @empty
                    <x-visual.empty-state title="Tidak ada tindak lanjut mendesak" message="Tidak ada target tindak lanjut yang mendekati tenggat." />
                @endforelse
            </div>
        </div>
    </div>
@endsection
