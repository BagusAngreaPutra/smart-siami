@extends('layouts.app')

@section('title', 'Desk Evaluation Unit - SMART SIAMI')
@section('page_title', 'Desk Evaluation Unit')

@section('content')
    @once
        <style>
            .desk-hero {
                display: grid;
                gap: 18px;
            }

            .desk-hero-main {
                align-items: flex-start;
                display: flex;
                gap: 16px;
                justify-content: space-between;
            }

            .desk-unit-code {
                color: var(--primary, #0E6656);
                font-size: 13px;
                font-weight: 800;
                letter-spacing: .04em;
                text-transform: uppercase;
            }

            .desk-progress-line {
                align-items: center;
                display: grid;
                gap: 10px;
                grid-template-columns: minmax(0, 1fr) auto;
            }

            .desk-standard {
                display: grid;
                gap: 12px;
            }

            .desk-standard-head {
                align-items: center;
                border-bottom: 1px solid var(--border, #E5E7E0);
                display: flex;
                gap: 14px;
                justify-content: space-between;
                padding-bottom: 12px;
            }

            .desk-standard-meta {
                color: var(--muted, #6B7B76);
                display: flex;
                flex-wrap: wrap;
                font-size: 13px;
                gap: 8px;
            }

            .desk-pill {
                background: #E4F2EE;
                border-radius: 999px;
                color: #0E6656;
                font-size: 12px;
                font-weight: 800;
                padding: 6px 10px;
            }

            .desk-instrument {
                border: 1px solid var(--border, #E5E7E0);
                border-radius: 14px;
                overflow: hidden;
                transition: border-color .18s ease, box-shadow .18s ease;
            }

            .desk-instrument[open] {
                border-color: rgba(14, 102, 86, .28);
                box-shadow: 0 10px 28px rgba(14, 102, 86, .08);
            }

            .desk-instrument-summary {
                align-items: center;
                background: #fff;
                cursor: pointer;
                display: grid;
                gap: 12px;
                grid-template-columns: minmax(0, 1fr) auto;
                list-style: none;
                padding: 16px;
            }

            .desk-instrument-summary::-webkit-details-marker {
                display: none;
            }

            .desk-instrument-title {
                align-items: flex-start;
                display: flex;
                gap: 12px;
                min-width: 0;
            }

            .desk-number {
                background: #F7FBFA;
                border: 1px solid #DCEBE7;
                border-radius: 10px;
                color: #0E6656;
                flex: 0 0 auto;
                font-size: 12px;
                font-weight: 900;
                padding: 7px 9px;
            }

            .desk-question {
                color: var(--heading, #1F2C29);
                font-weight: 800;
                line-height: 1.35;
                margin: 0;
            }

            .desk-subline {
                color: var(--muted, #6B7B76);
                font-size: 13px;
                margin-top: 4px;
            }

            .desk-instrument-status {
                align-items: center;
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-end;
            }

            .desk-instrument-body {
                background: linear-gradient(180deg, #FFFFFF, #FBFDFC);
                border-top: 1px solid var(--border, #E5E7E0);
                display: grid;
                gap: 18px;
                grid-template-columns: minmax(0, 1.15fr) minmax(280px, .85fr);
                padding: 18px;
            }

            .desk-info-list {
                display: grid;
                gap: 10px;
            }

            .desk-info-item {
                background: #fff;
                border: 1px solid #E5E7E0;
                border-radius: 12px;
                padding: 12px;
            }

            .desk-info-item strong {
                color: #0E6656;
                display: block;
                font-size: 12px;
                margin-bottom: 5px;
                text-transform: uppercase;
            }

            .desk-info-item span {
                color: #1F2C29;
                line-height: 1.55;
            }

            .desk-review-card {
                background: #fff;
                border: 1px solid #DCEBE7;
                border-radius: 14px;
                padding: 16px;
            }

            .desk-review-card .actions {
                justify-content: stretch;
            }

            .desk-review-card .actions button,
            .desk-review-card .button {
                justify-content: center;
                width: 100%;
            }

            .desk-evidence-head {
                align-items: center;
                display: flex;
                justify-content: space-between;
                margin: 16px 0 10px;
            }

            .desk-evidence-head h4 {
                margin: 0;
            }

            @media (max-width: 920px) {
                .desk-hero-main,
                .desk-standard-head {
                    align-items: stretch;
                    flex-direction: column;
                }

                .desk-instrument-summary,
                .desk-instrument-body {
                    grid-template-columns: 1fr;
                }

                .desk-instrument-status {
                    justify-content: flex-start;
                }
            }
        </style>
    @endonce

    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    @php
        $progress = $summary['total'] > 0 ? (int) round(($summary['checked'] / $summary['total']) * 100) : 0;
        $openedFirst = false;
    @endphp

    <div class="panel desk-hero">
        <div class="desk-hero-main">
            <div>
                <div class="desk-unit-code">{{ $assignment->unit->kode }}</div>
                <h3 class="panel-title">{{ $assignment->unit->nama }}</h3>
                <p class="muted">{{ $assignment->auditPeriod->nama }} &middot; {{ $assignment->leadAuditor->name }}</p>
            </div>

            @if ($canFinalize && ! $isFinalized)
                <form method="post" action="{{ route('auditor.desk-evaluation.finalize', $assignment) }}" onsubmit="return confirm('Finalisasi desk evaluation? Catatan auditor akan dikunci.');">
                    @csrf
                    <button type="submit">Finalisasi Desk Evaluation</button>
                </form>
            @else
                <span class="badge @if (! $isFinalized) off @endif">{{ $isFinalized ? 'Final' : 'Belum Siap Final' }}</span>
            @endif
        </div>

        @if ($isFinalized)
            <div class="status">Desk evaluation sudah final. Catatan auditor tidak dapat diubah dari halaman ini.</div>
        @endif

        <div class="desk-progress-line">
            <div>
                <div class="progress"><div class="progress-bar" style="width: {{ $progress }}%"></div></div>
                <p class="muted">{{ $summary['checked'] }}/{{ $summary['total'] }} instrumen sudah mulai diperiksa.</p>
            </div>
            <span class="desk-pill">{{ $progress }}%</span>
        </div>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Instrumen</div>
                <div class="summary-value">{{ $summary['total'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Sudah Diperiksa</div>
                <div class="summary-value">{{ $summary['checked'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Bukti Valid</div>
                <div class="summary-value">{{ $summary['valid_evidences'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Perlu Klarifikasi</div>
                <div class="summary-value">{{ $summary['clarification_evidences'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Usulan Temuan</div>
                <div class="summary-value">{{ $summary['proposed_findings'] }}</div>
            </div>
        </div>
    </div>

    @foreach ($standards as $group)
        @php
            $standardTotal = $group['evaluations']->count();
            $standardChecked = $group['evaluations']->where('status_pemeriksaan', '!=', 'belum_dimulai')->count();
            $standardClarifications = $group['evaluations']->where('status_bukti', 'perlu_klarifikasi')->count();
            $standardFindings = $group['evaluations']->where('usulan_temuan', true)->count();
        @endphp

        <div class="panel desk-standard">
            <div class="desk-standard-head">
                <div>
                    <h3 class="panel-title">{{ $group['standard']->kode }} - {{ $group['standard']->nama }}</h3>
                    <div class="desk-standard-meta">
                        <span>{{ $standardChecked }}/{{ $standardTotal }} diperiksa</span>
                        <span>{{ $standardClarifications }} klarifikasi</span>
                        <span>{{ $standardFindings }} usulan temuan</span>
                    </div>
                </div>
                <span class="desk-pill">{{ $standardTotal > 0 ? (int) round(($standardChecked / $standardTotal) * 100) : 0 }}%</span>
            </div>

            @foreach ($group['evaluations'] as $evaluation)
                @php
                    $assessment = $evaluation->selfAssessment;
                    $instrument = $evaluation->instrument;
                    $readOnly = $isFinalized;
                    $evidenceCount = $assessment->evidences->count();
                    $shouldOpen = ! $openedFirst && in_array($evaluation->status_pemeriksaan, ['belum_dimulai', 'menunggu_klarifikasi'], true);
                    if ($shouldOpen) {
                        $openedFirst = true;
                    }
                    $statusTone = match ($evaluation->status_pemeriksaan) {
                        'final' => 'success',
                        'menunggu_klarifikasi' => 'warning',
                        'berlangsung' => 'neutral',
                        default => 'off',
                    };
                    $shortQuestion = str($instrument->pertanyaan)->limit(145);
                @endphp

                <details class="desk-instrument" @if ($shouldOpen) open @endif>
                    <summary class="desk-instrument-summary">
                        <div class="desk-instrument-title">
                            <span class="desk-number">{{ $instrument->kode }}</span>
                            <div>
                                <p class="desk-question">{{ $instrument->nama_indikator ?: $shortQuestion }}</p>
                                <div class="desk-subline">{{ $instrument->nama_indikator ? $shortQuestion : $instrument->jenis_jawaban }}</div>
                            </div>
                        </div>
                        <div class="desk-instrument-status">
                            <span class="badge {{ $statusTone }}">{{ $statusPemeriksaanOptions[$evaluation->status_pemeriksaan] }}</span>
                            <span class="badge neutral">{{ $evidenceCount }} bukti</span>
                            @if ($evaluation->usulan_temuan)
                                <span class="badge warning">Usulan Temuan</span>
                            @endif
                        </div>
                    </summary>

                    <div class="desk-instrument-body">
                        <div>
                            <div class="desk-info-list">
                                <div class="desk-info-item">
                                    <strong>Pertanyaan</strong>
                                    <span>{{ $instrument->pertanyaan }}</span>
                                </div>
                                <div class="desk-info-item">
                                    <strong>Jawaban Auditee</strong>
                                    <span>{{ $assessment->jawaban_naratif ?? '-' }}</span>
                                </div>
                                <div class="desk-info-item">
                                    <strong>Realisasi</strong>
                                    <span>{{ $assessment->realisasi ?? '-' }}</span>
                                </div>
                                <div class="desk-info-item">
                                    <strong>Kendala</strong>
                                    <span>{{ $assessment->kendala ?? '-' }}</span>
                                </div>
                                <div class="desk-info-item">
                                    <strong>Analisis Gap</strong>
                                    <span>{{ $assessment->analisis_gap ?? '-' }}</span>
                                </div>
                                <div class="desk-info-item">
                                    <strong>Rencana Perbaikan Awal</strong>
                                    <span>{{ $assessment->rencana_perbaikan_awal ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="desk-evidence-head">
                                <h4>Bukti Auditee</h4>
                                <span class="badge neutral">{{ $evidenceCount }} dokumen</span>
                            </div>

                            <div class="evidence-grid">
                                @forelse ($assessment->evidences as $evidence)
                                    <x-visual.evidence-card
                                        :evidence="$evidence"
                                        :preview-url="$evidence->tipe_sumber === 'file' ? route('auditor.desk-evaluation.evidences.preview', $evidence) : $evidence->url_tautan"
                                        :download-url="$evidence->tipe_sumber === 'file' ? route('auditor.desk-evaluation.evidences.download', $evidence) : null"
                                    />
                                @empty
                                    <x-visual.empty-state title="Belum ada bukti" message="Auditee belum mengunggah bukti untuk instrumen ini." icon="document" />
                                @endforelse
                            </div>
                        </div>

                        <aside class="desk-review-card">
                            <h4 class="panel-title">Penilaian Auditor</h4>
                            <form class="form-grid" method="post" action="{{ route('auditor.desk-evaluation.update', [$assignment, $evaluation]) }}">
                                @csrf
                                @method('patch')

                                @if ($instrument->jenis_jawaban === 'skor')
                                    <div class="form-field full">
                                        <label for="skor_{{ $evaluation->id }}">Skor</label>
                                        <input id="skor_{{ $evaluation->id }}" name="skor" type="number" step="0.01" min="{{ $instrument->skor_min }}" max="{{ $instrument->skor_max }}" value="{{ old('skor', $evaluation->skor) }}" @disabled($readOnly)>
                                    </div>
                                @endif

                                <div class="form-field full">
                                    <label for="status_bukti_{{ $evaluation->id }}">Status Bukti</label>
                                    <select id="status_bukti_{{ $evaluation->id }}" name="status_bukti" @disabled($readOnly)>
                                        @foreach ($statusBuktiOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('status_bukti', $evaluation->status_bukti) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-field full">
                                    <label for="catatan_auditor_{{ $evaluation->id }}">Catatan Auditor</label>
                                    <textarea id="catatan_auditor_{{ $evaluation->id }}" name="catatan_auditor" @disabled($readOnly)>{{ old('catatan_auditor', $evaluation->catatan_auditor) }}</textarea>
                                </div>

                                <label class="remember">
                                    <input type="checkbox" name="usulan_temuan" value="1" @checked(old('usulan_temuan', $evaluation->usulan_temuan)) @disabled($readOnly)>
                                    Tandai sebagai Usulan Temuan
                                </label>

                                <div class="form-field full">
                                    <label for="rekomendasi_awal_{{ $evaluation->id }}">Rekomendasi Awal</label>
                                    <textarea id="rekomendasi_awal_{{ $evaluation->id }}" name="rekomendasi_awal" @disabled($readOnly)>{{ old('rekomendasi_awal', $evaluation->rekomendasi_awal) }}</textarea>
                                </div>

                                @unless ($readOnly)
                                    <div class="form-field full actions">
                                        <button type="submit">Simpan Penilaian</button>
                                    </div>
                                @endunless
                            </form>

                            @unless ($readOnly)
                                <form method="post" action="{{ route('auditor.desk-evaluation.clarification', [$assignment, $evaluation]) }}" style="margin-top: 12px">
                                    @csrf
                                    <button class="button secondary" type="submit">Kirim Klarifikasi</button>
                                </form>
                            @endunless
                        </aside>
                    </div>
                </details>
            @endforeach
        </div>
    @endforeach
@endsection
