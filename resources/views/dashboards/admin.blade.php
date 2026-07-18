@extends('layouts.app')

@section('title', 'Dashboard Admin - SMART SIAMI')
@section('page_title', 'Dashboard')
@section('body_class', 'crm-pilot')

@section('content')
    @php
        $metricMeta = [
            'Total Unit' => [
                'icon' => '<path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1"/>',
                'note' => 'Unit terdaftar',
                'indicator' => 'Basis audit aktif',
                'tone' => 'blue',
            ],
            'Total Auditor' => [
                'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
                'note' => 'Auditor tersedia',
                'indicator' => 'Siap ditugaskan',
                'tone' => 'violet',
            ],
            'Total Penugasan Aktif' => [
                'icon' => '<rect x="4" y="4" width="16" height="17" rx="2"/><path d="M9 4V2h6v2M8 10h8M8 14h6"/>',
                'note' => 'Dalam periode ini',
                'indicator' => 'Sedang berjalan',
                'tone' => 'teal',
            ],
            'Total Temuan Aktif' => [
                'icon' => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0ZM12 9v4M12 17h.01"/>',
                'note' => 'Memerlukan tindak lanjut',
                'indicator' => 'Perlu perhatian',
                'tone' => 'orange',
            ],
        ];
        $overdueCard = collect($cards)->firstWhere('label', 'Tindak Lanjut Terlambat');
        $totalProgress = max($selfEvaluationProgress['total'], 1);
        $progressItems = [
            ['label' => 'Finalisasi', 'value' => $selfEvaluationProgress['final'], 'tone' => 'success'],
            ['label' => 'Masih draft', 'value' => $selfEvaluationProgress['draft'], 'tone' => 'warning'],
            ['label' => 'Belum mulai', 'value' => $selfEvaluationProgress['not_started'], 'tone' => 'neutral'],
        ];
        $readinessValue = (float) $institutionReadiness;
        $latestActivityTime = $activities->first()['time'] ?? now();
        $readinessStatus = match (true) {
            $readinessValue >= 85 => ['label' => 'Sangat siap', 'tone' => 'success'],
            $readinessValue >= 70 => ['label' => 'Siap', 'tone' => 'success'],
            $readinessValue >= 50 => ['label' => 'Perlu perhatian', 'tone' => 'warning'],
            default => ['label' => 'Belum siap', 'tone' => 'danger'],
        };
    @endphp

    <section class="crm-page-heading" aria-labelledby="admin-dashboard-title">
        <div>
            <span class="crm-eyebrow">Insight institusi</span>
            <h1 id="admin-dashboard-title">Ringkasan Audit</h1>
            <p>Pantau kesiapan unit, progres evaluasi, dan tindak lanjut prioritas dalam satu tampilan.</p>
        </div>

        <div class="crm-heading-actions">
            <div class="crm-update-status">
                <span class="crm-update-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg>
                </span>
                <span>Diperbarui <strong>{{ $latestActivityTime->translatedFormat('d M Y') }}</strong></span>
                <i aria-label="Data tersedia"></i>
            </div>
            <form class="crm-period-filter" method="get">
                <label class="sr-only" for="audit_period_id">Periode audit</label>
                <div class="crm-filter-controls">
                    <select id="audit_period_id" name="audit_period_id">
                        @foreach ($periods as $period)
                            <option value="{{ $period->id }}" @selected($selectedPeriod?->id === $period->id)>{{ $period->nama }}</option>
                        @endforeach
                    </select>
                    <button type="submit">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16M7 12h10M10 19h4"></path></svg>
                        Terapkan
                    </button>
                </div>
            </form>
            <a class="crm-button-primary" href="{{ route('admin.reports') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5"></path><path d="M5 21h14"></path></svg>
                Laporan
            </a>
        </div>
    </section>

    <section class="crm-metric-grid" aria-label="Metrik utama audit">
        @foreach ($cards as $card)
            @continue($card['label'] === 'Tindak Lanjut Terlambat')
            @php($meta = $metricMeta[$card['label']] ?? ['icon' => '<circle cx="12" cy="12" r="8"/>', 'note' => 'Data saat ini', 'indicator' => 'Tersedia', 'tone' => 'blue'])
            <a class="crm-metric-card" href="{{ $card['url'] }}">
                <div class="crm-metric-topline">
                    <span class="crm-metric-icon tone-{{ $meta['tone'] }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">{!! $meta['icon'] !!}</svg>
                    </span>
                    <svg class="crm-card-arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                </div>
                <span class="crm-metric-label">{{ $card['label'] }}</span>
                <strong class="crm-metric-value tone-{{ $meta['tone'] }}">{{ number_format($card['value']) }}</strong>
                <span class="crm-metric-note tone-{{ $meta['tone'] }}">
                    <b><span aria-hidden="true">{{ $meta['tone'] === 'orange' ? '!' : '↗' }}</span> {{ $meta['indicator'] }}</b>
                    <em>{{ $meta['note'] }}</em>
                </span>
            </a>
        @endforeach
    </section>

    <div class="crm-dashboard-grid">
        <section class="crm-panel crm-progress-panel" aria-labelledby="self-evaluation-title">
            <div class="crm-panel-header">
                <div class="crm-panel-title-wrap">
                    <span class="crm-panel-icon tone-blue">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9M10 19V5M16 19v-7M22 19V3"></path></svg>
                    </span>
                    <div>
                    <span class="crm-panel-kicker">Kesiapan institusi</span>
                    <h2 id="self-evaluation-title">Progres Evaluasi Diri</h2>
                    <p>{{ $selectedPeriod?->nama ?? 'Belum ada periode audit aktif' }}</p>
                    </div>
                </div>
                <span class="crm-status-badge tone-{{ $readinessStatus['tone'] }}">{{ $readinessStatus['label'] }}</span>
            </div>

            <div class="crm-insight-visuals">
                <div class="crm-readiness-donut-wrap">
                    <div class="crm-readiness-donut" style="--readiness: {{ max(0, min(100, $readinessValue)) * 3.6 }}deg" role="img" aria-label="Skor kesiapan {{ number_format($readinessValue, 0) }} persen">
                        <div>
                            <strong>{{ number_format($readinessValue, 0) }}%</strong>
                            <span>Siap</span>
                        </div>
                    </div>
                    <div class="crm-donut-copy">
                        <strong>Skor institusi</strong>
                        <span>Akumulasi kesiapan seluruh unit pada periode terpilih.</span>
                        <a href="{{ route('admin.monitoring') }}">Buka detail <span aria-hidden="true">&rarr;</span></a>
                    </div>
                </div>
                <div class="crm-pipeline-chart" aria-label="Distribusi progres evaluasi diri">
                    <div class="crm-chart-heading">
                        <div>
                            <strong>Pipeline unit</strong>
                            <span>{{ $selfEvaluationProgress['total'] }} unit dipantau</span>
                        </div>
                        <span class="crm-live-chip"><i></i> Aktual</span>
                    </div>
                    <div class="crm-chart-area">
                        @foreach ($progressItems as $item)
                            @php($percent = (int) round(($item['value'] / $totalProgress) * 100))
                            <div class="crm-chart-column tone-{{ $item['tone'] }}">
                                <span class="crm-chart-value">{{ $percent }}%</span>
                                <div class="crm-chart-track"><i style="height: {{ max($percent, 5) }}%"></i></div>
                                <strong>{{ $item['label'] }}</strong>
                                <small>{{ $item['value'] }} unit</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="crm-segmented-progress" role="img" aria-label="Distribusi progres evaluasi diri">
                @foreach ($progressItems as $item)
                    @php($percent = (int) round(($item['value'] / $totalProgress) * 100))
                    @if ($percent > 0)<span class="tone-{{ $item['tone'] }}" style="width: {{ $percent }}%"></span>@endif
                @endforeach
            </div>
        </section>

        <section class="crm-panel crm-priority-panel" id="late-follow-ups" aria-labelledby="priority-title">
            <div class="crm-panel-header">
                <div class="crm-panel-title-wrap">
                    <span class="crm-panel-icon tone-red">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0ZM12 9v4M12 17h.01"></path></svg>
                    </span>
                    <div>
                    <span class="crm-panel-kicker">Prioritas hari ini</span>
                    <h2 id="priority-title">Perlu Tindakan</h2>
                    </div>
                </div>
                <span class="crm-alert-count">{{ $overdueCard['value'] ?? 0 }}</span>
            </div>

            <div class="crm-priority-list">
                @forelse ($lateFollowUps as $followUp)
                    <a class="crm-priority-item" href="{{ route('admin.assignments', ['unit_id' => $followUp->assignment->unit_id]) }}">
                        <span class="crm-priority-mark" aria-hidden="true"></span>
                        <div>
                            <strong>{{ $followUp->finding->nomor_temuan }}</strong>
                            <span>{{ $followUp->assignment->unit->kode }} · {{ $followUp->assignment->unit->nama }}</span>
                        </div>
                        <div class="crm-priority-status">
                            <span>Terlambat</span>
                            <time datetime="{{ $followUp->target_penyelesaian->format('Y-m-d') }}">{{ $followUp->target_penyelesaian->format('d M') }}</time>
                        </div>
                    </a>
                @empty
                    <div class="crm-empty-state">
                        <span aria-hidden="true">✓</span>
                        <strong>Semua terkendali</strong>
                        <p>Tidak ada tindak lanjut yang melewati target.</p>
                    </div>
                @endforelse
            </div>

            <a class="crm-panel-link" href="{{ route('admin.assignments', array_filter(['audit_period_id' => $selectedPeriod?->id])) }}">
                Buka seluruh penugasan <span aria-hidden="true">&rarr;</span>
            </a>
        </section>
    </div>

    <div class="crm-lower-grid">
        <section class="crm-panel crm-visit-panel" aria-labelledby="visit-title">
            <div class="crm-panel-header">
                <div class="crm-panel-title-wrap">
                    <span class="crm-panel-icon tone-orange">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg>
                    </span>
                    <div>
                    <span class="crm-panel-kicker">Agenda lapangan</span>
                    <h2 id="visit-title">Visitasi Terdekat</h2>
                    </div>
                </div>
                <a class="crm-text-link" href="{{ route('admin.assignments') }}">Lihat semua</a>
            </div>

            <div class="crm-data-list">
                @forelse ($upcomingVisits as $visit)
                    <a class="crm-data-row" href="{{ route('admin.assignments.show', $visit->assignment) }}">
                        <time class="crm-date-tile" datetime="{{ $visit->tanggal->format('Y-m-d') }}">
                            <strong>{{ $visit->tanggal->format('d') }}</strong>
                            <span>{{ strtoupper($visit->tanggal->translatedFormat('M')) }}</span>
                        </time>
                        <div>
                            <strong>{{ $visit->assignment->unit->kode }} · {{ $visit->assignment->unit->nama }}</strong>
                            <span>{{ $visit->assignment->auditPeriod->nama }} @if ($visit->waktu_mulai) · {{ $visit->waktu_mulai }} @endif</span>
                        </div>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                @empty
                    <div class="crm-empty-state compact">
                        <strong>Belum ada visitasi</strong>
                        <p>Agenda akan muncul setelah auditor menetapkan jadwal.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="crm-panel crm-activity-panel" aria-labelledby="activity-title">
            <div class="crm-panel-header">
                <div class="crm-panel-title-wrap">
                    <span class="crm-panel-icon tone-violet">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v4l3 2"></path><circle cx="12" cy="12" r="9"></circle></svg>
                    </span>
                    <div>
                    <span class="crm-panel-kicker">Jejak sistem</span>
                    <h2 id="activity-title">Aktivitas Terbaru</h2>
                    </div>
                </div>
            </div>

            <div class="crm-timeline">
                @forelse ($activities->take(5) as $activity)
                    <div class="crm-timeline-item">
                        <span class="crm-avatar-initial" aria-hidden="true">{{ str($activity['actor'])->substr(0, 1)->upper() }}</span>
                        <div>
                            <p><strong>{{ $activity['actor'] }}</strong> {{ $activity['action'] }}</p>
                            <time datetime="{{ $activity['time']->toIso8601String() }}">{{ $activity['time']->diffForHumans() }}</time>
                        </div>
                    </div>
                @empty
                    <div class="crm-empty-state compact">
                        <strong>Belum ada aktivitas</strong>
                        <p>Perubahan terbaru akan tercatat di sini.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
