@extends('layouts.app')

@section('title', 'Dashboard Admin - SMART SIAMI')
@section('page_title', 'Dashboard Admin')

@section('content')
    @php
        $adminIcons = [
            'Total Unit' => 'building',
            'Total Auditor' => 'users',
            'Total Penugasan Aktif' => 'clipboard',
            'Total Temuan Aktif' => 'alert',
            'Tindak Lanjut Terlambat' => 'clock',
        ];
    @endphp

    <section class="dashboard-hero">
        <div class="dashboard-hero-content">
            <div>
                <span class="dashboard-eyebrow">Command Center Admin</span>
                <h3>Kontrol siklus audit dalam satu layar</h3>
                <p>Pantau kesiapan institusi, progres unit, risiko keterlambatan, dan aktivitas audit terbaru tanpa membuka banyak menu.</p>
                <div class="hero-actions">
                    <a class="hero-action" href="{{ route('admin.monitoring') }}">Buka Monitoring</a>
                    <a class="hero-action" href="{{ route('admin.assignments') }}">Kelola Penugasan</a>
                    <a class="hero-action" href="{{ route('admin.reports') }}">Lihat Laporan</a>
                </div>
            </div>

            <form class="hero-filter" method="get">
                <div class="form-field">
                    <label for="audit_period_id">Periode Audit</label>
                    <select id="audit_period_id" name="audit_period_id">
                        @foreach ($periods as $period)
                            <option value="{{ $period->id }}" @selected($selectedPeriod?->id === $period->id)>{{ $period->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit">Terapkan</button>
                <span class="badge @if ($selectedPeriod?->status === 'aktif') success @else neutral @endif">
                    {{ $selectedPeriod?->nama ?? 'Belum ada periode' }}
                </span>
            </form>
        </div>
    </section>

    <div class="dashboard-kpi-grid admin-kpi-grid">
        @foreach ($cards as $card)
            <x-dashboard.kpi-card :card="$card" :icon="$adminIcons[$card['label']] ?? 'dashboard'" />
        @endforeach
    </div>

    <div class="dashboard-grid admin-dashboard-grid">

        {{-- ================= SKOR KESIAPAN INSTITUSI ================= --}}
        @php
            // Tentukan kategori & warna status berdasarkan ambang batas skor.
            // Sesuaikan ambang batas ini dengan standar mutu institusi jika berbeda.
            $readinessValue = (float) $institutionReadiness;
            $readinessStatus = match (true) {
                $readinessValue >= 85 => ['label' => 'Sangat Siap', 'tone' => 'success'],
                $readinessValue >= 70 => ['label' => 'Siap', 'tone' => 'success'],
                $readinessValue >= 50 => ['label' => 'Perlu Perhatian', 'tone' => 'warning'],
                default              => ['label' => 'Belum Siap', 'tone' => 'danger'],
            };
        @endphp

        <div class="panel gauge-panel dashboard-panel-accent admin-visual-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Skor Kesiapan Institusi</h3>
                    <p class="muted">
                        Rata-rata kesiapan evaluasi diri seluruh unit
                        @if ($selectedPeriod)
                            pada periode <strong>{{ $selectedPeriod->nama }}</strong>.
                        @else
                            &mdash; belum ada periode aktif.
                        @endif
                    </p>
                </div>
                <span class="badge {{ $readinessStatus['tone'] }}">{{ $readinessStatus['label'] }}</span>
            </div>

            <div class="gauge-chart-wrap">
                <x-visual.gauge :value="$readinessValue" label="Institusi" />

                <div class="gauge-footnote">
                    <span class="gauge-score">{{ number_format($readinessValue, 1) }}%</span>
                    <span class="muted">dari target 100%</span>
                </div>
            </div>
        </div>

        {{-- ================= RADAR CAPAIAN STANDAR ================= --}}
        <div class="panel dashboard-panel-accent admin-visual-panel radar-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Radar Capaian per Standar</h3>
                    <p class="muted">Capaian rata-rata unit dibanding target 100%.</p>
                </div>
            </div>
            <x-visual.radar :items="$radarScores" title="Radar capaian rata-rata institusi" />
        </div>

        {{-- ================= HEATMAP UNIT x STANDAR ================= --}}
        <div class="panel dashboard-panel-accent" style="grid-column: 1 / -1">
            <h3 class="panel-title">Heatmap Unit x Standar</h3>
            <p class="muted">Warna membantu melihat pola standar yang membutuhkan perhatian lintas unit.</p>
            <x-visual.heatmap :standards="$heatmapStandards" :rows="$heatmapRows" />
        </div>

        {{-- ================= PROGRES EVALUASI DIRI ================= --}}
        <div class="panel dashboard-panel-accent">
            <h3 class="panel-title">Progres Evaluasi Diri</h3>
            <p class="muted">Periode: {{ $selectedPeriod?->nama ?? '-' }}</p>

            @php($total = max($selfEvaluationProgress['total'], 1))
            @foreach ([
                ['label' => 'Finalisasi', 'value' => $selfEvaluationProgress['final'], 'class' => 'success'],
                ['label' => 'Masih Draft', 'value' => $selfEvaluationProgress['draft'], 'class' => 'warning'],
                ['label' => 'Belum Mulai', 'value' => $selfEvaluationProgress['not_started'], 'class' => 'neutral'],
            ] as $item)
                @php($percent = (int) round(($item['value'] / $total) * 100))
                <div class="section-block">
                    <div class="toolbar">
                        <strong>{{ $item['label'] }}</strong>
                        <span class="badge {{ $item['class'] }}">{{ $item['value'] }} unit</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $percent }}%"></div>
                    </div>
                    <div class="muted">{{ $percent }}%</div>
                </div>
            @endforeach
        </div>

        {{-- ================= TINDAK LANJUT TERLAMBAT ================= --}}
        <div class="panel dashboard-panel-accent danger" id="late-follow-ups">
            <h3 class="panel-title">Tindak Lanjut Terlambat</h3>
            <div class="smart-list">
                @forelse ($lateFollowUps as $followUp)
                    <a class="smart-list-item" href="{{ route('admin.assignments', ['unit_id' => $followUp->assignment->unit_id]) }}">
                        <div class="smart-list-row">
                            <strong>{{ $followUp->finding->nomor_temuan }}</strong>
                            <span class="badge danger">Target {{ $followUp->target_penyelesaian->format('d/m/Y') }}</span>
                        </div>
                        <div>{{ $followUp->assignment->unit->kode }} - {{ $followUp->assignment->unit->nama }}</div>
                    </a>
                @empty
                    <x-visual.empty-state
                        title="Tidak ada tindak lanjut terlambat"
                        message="Semua tindak lanjut masih berada dalam batas waktu." />
                @endforelse
            </div>
        </div>

        {{-- ================= VISITASI TERDEKAT ================= --}}
        <div class="panel dashboard-panel-accent warning">
            <h3 class="panel-title">Visitasi Terdekat</h3>
            <div class="smart-list">
                @forelse ($upcomingVisits as $visit)
                    <a class="smart-list-item" href="{{ route('admin.assignments.show', $visit->assignment) }}">
                        <div class="smart-list-row">
                            <strong>{{ $visit->assignment->unit->kode }} - {{ $visit->assignment->unit->nama }}</strong>
                            <span class="badge warning">{{ $visit->tanggal->format('d/m/Y') }} {{ $visit->waktu_mulai ?? '' }}</span>
                        </div>
                        <div>{{ $visit->assignment->auditPeriod->nama }}</div>
                    </a>
                @empty
                    <x-visual.empty-state
                        title="Belum ada visitasi terjadwal"
                        message="Jadwal visitasi akan muncul saat auditor menetapkannya." />
                @endforelse
            </div>
        </div>

        {{-- ================= AKTIVITAS TERBARU ================= --}}
        <div class="panel dashboard-panel-accent">
            <h3 class="panel-title">Aktivitas Terbaru</h3>
            <div class="smart-list">
                @forelse ($activities as $activity)
                    <div class="smart-list-item">
                        <div class="smart-list-row">
                            <strong>{{ $activity['actor'] }}</strong>
                            <span class="muted">{{ $activity['time']->format('d/m/Y H:i') }}</span>
                        </div>
                        <div>{{ $activity['action'] }}</div>
                    </div>
                @empty
                    <p class="muted">Belum ada aktivitas sistem.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
