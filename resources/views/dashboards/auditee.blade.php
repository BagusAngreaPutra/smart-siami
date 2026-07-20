@extends('layouts.app')

@section('title', 'Dashboard Auditee - SMART SIAMI')
@section('page_title', 'Dashboard Auditee')
@section('content')
    @php
        $metricMeta = [
            'Total Instrumen' => [
                'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h6"/>',
                'note' => 'Ruang lingkup evaluasi',
                'indicator' => 'Instrumen aktif',
                'tone' => 'blue',
            ],
            'Belum Diisi' => [
                'icon' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
                'note' => 'Mulai dari butir ini',
                'indicator' => 'Perlu dikerjakan',
                'tone' => 'orange',
            ],
            'Perlu Klarifikasi' => [
                'icon' => '<path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8M8 13h5"/>',
                'note' => 'Menunggu respons unit',
                'indicator' => 'Perlu jawaban',
                'tone' => 'red',
            ],
            'Sudah Final' => [
                'icon' => '<rect x="3" y="3" width="18" height="18" rx="4"/><path d="m8 12 2.5 2.5L16 9"/>',
                'note' => 'Jawaban telah dikunci',
                'indicator' => 'Tuntas diperiksa',
                'tone' => 'teal',
            ],
        ];
        $cardsByLabel = collect($cards)->keyBy('label');
        $readinessValue = (int) max(0, min(100, $readinessGauge));
        $remainingDaysValue = $remainingDays === null ? null : (int) $remainingDays;
        $readinessStatus = match (true) {
            ! $assignment => ['label' => 'Belum ada penugasan', 'tone' => 'neutral'],
            $readinessProgress >= 100 => ['label' => 'Evaluasi selesai', 'tone' => 'success'],
            $readinessValue >= 70 => ['label' => 'Hampir siap', 'tone' => 'success'],
            $readinessValue >= 40 => ['label' => 'Sedang dikerjakan', 'tone' => 'warning'],
            default => ['label' => 'Perlu perhatian', 'tone' => 'danger'],
        };
        $deadlineMeta = match (true) {
            $remainingDaysValue === null => ['label' => 'Belum ada tenggat aktif', 'tone' => 'neutral'],
            $remainingDaysValue < 0 => ['label' => 'Terlambat '.abs($remainingDaysValue).' hari', 'tone' => 'danger'],
            $remainingDaysValue === 0 => ['label' => 'Berakhir hari ini', 'tone' => 'danger'],
            $remainingDaysValue <= 3 => ['label' => 'Tersisa '.$remainingDaysValue.' hari', 'tone' => 'warning'],
            default => ['label' => 'Tersisa '.$remainingDaysValue.' hari', 'tone' => 'neutral'],
        };
        $clarificationCount = (int) ($cardsByLabel->get('Perlu Klarifikasi')['value'] ?? 0);
        $priorityCount = $clarificationCount + $urgentFollowUps->count();
    @endphp

    <section class="crm-page-heading crm-auditee-heading" aria-labelledby="auditee-dashboard-title">
        <div>
            <span class="crm-eyebrow">Workspace unit</span>
            <h1 id="auditee-dashboard-title">Kesiapan Audit Unit</h1>
            <p>Pantau progres evaluasi diri, tenggat, klarifikasi, visitasi, dan perbaikan yang perlu diselesaikan.</p>
        </div>

        <div class="crm-heading-actions">
            @if ($assignment)
                <div class="crm-auditee-context" title="{{ $assignment->unit->nama }} · {{ $assignment->auditPeriod->nama }}">
                    <span class="crm-auditee-context-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1"></path></svg>
                    </span>
                    <span><strong>{{ $assignment->unit->kode }}</strong>{{ $assignment->auditPeriod->nama }}</span>
                </div>
            @endif
            <a class="crm-button-secondary" href="{{ route('auditee.documents') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21.4 11.6-8.5 8.5a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.2 9.2a2 2 0 1 1-2.8-2.8l8.5-8.5"></path></svg>
                Bukti
            </a>
            <a class="crm-button-primary" href="{{ route('auditee.self-evaluations') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"></path></svg>
                Isi Evaluasi
            </a>
        </div>
    </section>

    <section class="crm-metric-grid" aria-label="Ringkasan pekerjaan auditee">
        @foreach ($cards as $card)
            @php
                $meta = $metricMeta[$card['label']]
                    ?? ['icon' => '<circle cx="12" cy="12" r="8"/>', 'note' => 'Data saat ini', 'indicator' => 'Tersedia', 'tone' => 'blue'];
            @endphp
            <a class="crm-metric-card" href="{{ $card['url'] }}">
                <div class="crm-metric-topline">
                    <span class="crm-metric-icon tone-{{ $meta['tone'] }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">{!! $meta['icon'] !!}</svg>
                    </span>
                    <svg class="crm-card-arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
                </div>
                <span class="crm-metric-label">{{ $card['label'] }}</span>
                <strong class="crm-metric-value tone-{{ $meta['tone'] }}">{{ number_format($card['value']) }}</strong>
                <span class="crm-metric-note tone-{{ $meta['tone'] }}">
                    <b><span aria-hidden="true">{{ in_array($meta['tone'], ['orange', 'red'], true) ? '!' : '↗' }}</span> {{ $meta['indicator'] }}</b>
                    <em>{{ $meta['note'] }}</em>
                </span>
            </a>
        @endforeach
    </section>

    <div class="crm-dashboard-grid crm-auditee-dashboard-grid">
        <section class="crm-panel crm-auditee-readiness-panel" aria-labelledby="unit-readiness-title">
            <div class="crm-panel-header">
                <div class="crm-panel-title-wrap">
                    <span class="crm-panel-icon tone-blue">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9M10 19V5M16 19v-7M22 19V3"></path></svg>
                    </span>
                    <div>
                        <span class="crm-panel-kicker">Evaluasi diri</span>
                        <h2 id="unit-readiness-title">Kesiapan Evaluasi Diri</h2>
                        <p>{{ $assignment?->auditPeriod->nama ?? 'Belum ada periode audit aktif untuk unit Anda.' }}</p>
                    </div>
                </div>
                <span class="crm-status-badge tone-{{ $readinessStatus['tone'] }}">{{ $readinessStatus['label'] }}</span>
            </div>

            @if ($assignment)
                <div class="crm-auditee-readiness-layout">
                    <div class="crm-readiness-donut-wrap crm-auditee-donut-wrap">
                        <div class="crm-readiness-donut" style="--readiness: {{ $readinessValue * 3.6 }}deg" role="img" aria-label="Skor kesiapan unit {{ $readinessValue }} persen">
                            <div>
                                <strong>{{ $readinessValue }}%</strong>
                                <span>Siap</span>
                            </div>
                        </div>
                        <div class="crm-donut-copy">
                            <strong>{{ $assignment->unit->nama }}</strong>
                            <span>{{ $readinessProgress }}% instrumen telah dikirim atau final. Skor kesiapan turut memperhitungkan jawaban draft dan klarifikasi.</span>
                            <a href="{{ route('auditee.self-evaluations', ['assignment_id' => $assignment->id]) }}">Lanjutkan evaluasi <span aria-hidden="true">→</span></a>
                        </div>
                    </div>

                    <div class="crm-auditee-standard-progress" aria-label="Kesiapan per standar">
                        <div class="crm-auditee-section-heading">
                            <div>
                                <strong>Kesiapan per standar</strong>
                                <span>Prioritaskan standar dengan progres terendah.</span>
                            </div>
                            <span>{{ count($standardScores) }} standar</span>
                        </div>
                        <div class="crm-auditee-standard-list">
                            @forelse ($standardScores as $standard)
                                <a href="{{ route('auditee.self-evaluations', ['assignment_id' => $assignment->id]) }}" title="{{ $standard['title'] }}">
                                    <span class="crm-auditee-standard-code">{{ $standard['label'] }}</span>
                                    <span class="crm-auditee-standard-copy">
                                        <strong>{{ $standard['title'] }}</strong>
                                        <span class="crm-auditee-progress-track"><i class="tone-{{ $standard['tone'] }}" style="width: {{ $standard['value'] }}%"></i></span>
                                    </span>
                                    <b>{{ $standard['value'] }}%</b>
                                </a>
                            @empty
                                <div class="crm-empty-state compact">
                                    <strong>Belum ada standar aktif</strong>
                                    <p>Progres akan muncul setelah instrumen tersedia.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="crm-auditee-deadline tone-{{ $deadlineMeta['tone'] }}">
                    <span class="crm-auditee-deadline-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg>
                    </span>
                    <span>
                        <strong>Batas evaluasi diri</strong>
                        {{ $assignment->auditPeriod->batas_evaluasi_diri->translatedFormat('d F Y') }}
                    </span>
                    <b>{{ $deadlineMeta['label'] }}</b>
                </div>
            @else
                <div class="crm-empty-state crm-auditee-empty-assignment">
                    <span aria-hidden="true">i</span>
                    <strong>Belum ada penugasan aktif</strong>
                    <p>Dashboard akan menampilkan progres setelah admin mengaktifkan penugasan audit untuk unit Anda.</p>
                </div>
            @endif
        </section>

        <section class="crm-panel crm-priority-panel" aria-labelledby="auditee-priority-title">
            <div class="crm-panel-header">
                <div class="crm-panel-title-wrap">
                    <span class="crm-panel-icon tone-red">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0ZM12 9v4M12 17h.01"></path></svg>
                    </span>
                    <div>
                        <span class="crm-panel-kicker">Prioritas hari ini</span>
                        <h2 id="auditee-priority-title">Perlu Tindakan</h2>
                        <p>Pekerjaan yang perlu segera ditangani unit.</p>
                    </div>
                </div>
                <span class="crm-alert-count">{{ $priorityCount }}</span>
            </div>

            <div class="crm-priority-list">
                @if ($clarificationCount > 0)
                    <a class="crm-priority-item" href="{{ route('auditee.clarifications') }}">
                        <span class="crm-priority-mark" aria-hidden="true"></span>
                        <div>
                            <strong>{{ $clarificationCount }} klarifikasi menunggu jawaban</strong>
                            <span>Baca catatan auditor dan lengkapi bukti yang diminta.</span>
                        </div>
                        <div class="crm-priority-status"><span>Jawab</span></div>
                    </a>
                @endif

                @foreach ($urgentFollowUps as $followUp)
                    @php
                        $late = $followUp->target_penyelesaian->toDateString() < now()->toDateString();
                    @endphp
                    <a class="crm-priority-item" href="{{ route('auditee.findings-followups.show', $followUp->finding) }}">
                        <span class="crm-priority-mark {{ $late ? '' : 'tone-orange' }}" aria-hidden="true"></span>
                        <div>
                            <strong>{{ $followUp->finding->nomor_temuan }}</strong>
                            <span>{{ str($followUp->rencana_tindakan)->limit(58) }}</span>
                        </div>
                        <div class="crm-priority-status">
                            <span>{{ $late ? 'Terlambat' : 'Mendekati' }}</span>
                            <time datetime="{{ $followUp->target_penyelesaian->format('Y-m-d') }}">{{ $followUp->target_penyelesaian->translatedFormat('d M') }}</time>
                        </div>
                    </a>
                @endforeach

                @if ($priorityCount === 0)
                    <div class="crm-empty-state">
                        <span aria-hidden="true">✓</span>
                        <strong>Semua terkendali</strong>
                        <p>Tidak ada klarifikasi atau tindak lanjut mendesak saat ini.</p>
                    </div>
                @endif
            </div>

            <a class="crm-panel-link" href="{{ route('auditee.findings-followups') }}">Buka seluruh tindak lanjut <span aria-hidden="true">→</span></a>
        </section>
    </div>

    <div class="crm-lower-grid crm-auditee-lower-grid">
        <section class="crm-panel" aria-labelledby="auditee-visit-title">
            <div class="crm-panel-header">
                <div class="crm-panel-title-wrap">
                    <span class="crm-panel-icon tone-orange">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg>
                    </span>
                    <div>
                        <span class="crm-panel-kicker">Agenda audit</span>
                        <h2 id="auditee-visit-title">Jadwal Visitasi</h2>
                        <p>Jadwal lapangan atau pertemuan daring unit.</p>
                    </div>
                </div>
                <a class="crm-text-link" href="{{ route('auditee.visit-schedules') }}">Lihat semua</a>
            </div>

            @if ($nextVisit)
                <a class="crm-auditee-visit-card" href="{{ route('auditee.visit-schedules.show', $nextVisit) }}">
                    <time datetime="{{ $nextVisit->tanggal->format('Y-m-d') }}">
                        <strong>{{ $nextVisit->tanggal->format('d') }}</strong>
                        <span>{{ strtoupper($nextVisit->tanggal->translatedFormat('M')) }}</span>
                        <small>{{ $nextVisit->tanggal->format('Y') }}</small>
                    </time>
                    <span class="crm-auditee-visit-copy">
                        <span class="crm-status-badge tone-warning">{{ $nextVisit->tipe === 'daring' ? 'Daring' : 'Lapangan' }}</span>
                        <strong>{{ $nextVisit->agenda ?: 'Agenda visitasi audit' }}</strong>
                        <span>{{ $nextVisit->waktu_mulai ?: 'Waktu menyusul' }} @if ($nextVisit->waktu_selesai)–{{ $nextVisit->waktu_selesai }}@endif · {{ $nextVisit->lokasi_atau_tautan ?: 'Lokasi menyusul' }}</span>
                    </span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
                </a>
            @else
                <div class="crm-empty-state compact">
                    <strong>Belum ada jadwal visitasi</strong>
                    <p>Jadwal akan muncul setelah auditor menetapkannya.</p>
                </div>
            @endif
        </section>

        <section class="crm-panel" aria-labelledby="auditee-findings-title">
            <div class="crm-panel-header">
                <div class="crm-panel-title-wrap">
                    <span class="crm-panel-icon tone-red">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4zM8 9h8M8 13h5"></path></svg>
                    </span>
                    <div>
                        <span class="crm-panel-kicker">Perbaikan mutu</span>
                        <h2 id="auditee-findings-title">Temuan Aktif</h2>
                        <p>Pantau status tindak lanjut setiap temuan.</p>
                    </div>
                </div>
                <a class="crm-text-link" href="{{ route('auditee.findings-followups') }}">Lihat semua</a>
            </div>

            <div class="crm-auditee-finding-list">
                @forelse ($activeFindings as $finding)
                    @php
                        $findingTone = $finding->status === 'terlambat' ? 'danger' : ($finding->status === 'menunggu_verifikasi' ? 'success' : 'warning');
                        $followUpLabel = $finding->latestFollowUp
                            ? \App\Models\FollowUp::statusOptions()[$finding->latestFollowUp->status]
                            : 'Belum dibuat';
                    @endphp
                    <a href="{{ route('auditee.findings-followups.show', $finding) }}">
                        <span class="crm-auditee-finding-icon tone-{{ $findingTone }}" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0ZM12 9v4M12 17h.01"></path></svg>
                        </span>
                        <span>
                            <strong>{{ $finding->nomor_temuan }}</strong>
                            <small>Tindak lanjut: {{ $followUpLabel }}</small>
                        </span>
                        <span class="crm-status-badge tone-{{ $findingTone }}">{{ \App\Models\Finding::statusOptions()[$finding->status] }}</span>
                    </a>
                @empty
                    <div class="crm-empty-state compact">
                        <strong>Belum ada temuan aktif</strong>
                        <p>Tidak ada temuan yang perlu ditindaklanjuti saat ini.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="crm-panel crm-auditee-guide" aria-labelledby="auditee-guide-title">
        <div class="crm-auditee-guide-intro">
            <span class="crm-panel-icon tone-violet">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="m15.5 8.5-2.1 4.9-4.9 2.1 2.1-4.9z"></path></svg>
            </span>
            <div>
                <span class="crm-panel-kicker">Alur kerja auditee</span>
                <h2 id="auditee-guide-title">Lanjutkan dari tahap yang membutuhkan perhatian</h2>
                <p>Setiap tahap terhubung langsung ke workspace yang relevan.</p>
            </div>
            <a class="crm-text-link" href="{{ route('auditee.guide') }}">Buka panduan lengkap →</a>
        </div>
        <div class="crm-auditee-guide-steps">
            <a href="{{ route('auditee.self-evaluations') }}"><b>01</b><span><strong>Evaluasi Diri</strong><small>Jawab dan kirim instrumen</small></span></a>
            <a href="{{ route('auditee.documents') }}"><b>02</b><span><strong>Bukti Dokumen</strong><small>Lengkapi file pendukung</small></span></a>
            <a href="{{ route('auditee.clarifications') }}"><b>03</b><span><strong>Klarifikasi</strong><small>Respons catatan auditor</small></span></a>
            <a href="{{ route('auditee.findings-followups') }}"><b>04</b><span><strong>Tindak Lanjut</strong><small>Selesaikan perbaikan mutu</small></span></a>
        </div>
    </section>
@endsection
