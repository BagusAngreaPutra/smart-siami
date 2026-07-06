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

            .desk-command {
                background:
                    radial-gradient(circle at top left, rgba(14, 102, 86, .08), transparent 32%),
                    linear-gradient(135deg, #FFFFFF, #F7FBFA);
                display: grid;
                gap: 14px;
            }

            .desk-command-head {
                align-items: center;
                display: flex;
                gap: 14px;
                justify-content: space-between;
            }

            .desk-command-title {
                margin: 0;
            }

            .desk-command-grid {
                display: grid;
                gap: 12px;
                grid-template-columns: minmax(220px, 1fr) auto;
            }

            .desk-search {
                background: #fff;
                border: 1px solid #DCEBE7;
                border-radius: 14px;
                color: #1F2C29;
                font: inherit;
                padding: 12px 14px;
                width: 100%;
            }

            .desk-filter-bar,
            .desk-action-bar {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .desk-filter,
            .desk-mini-action {
                align-items: center;
                background: #fff;
                border: 1px solid #DCEBE7;
                border-radius: 999px;
                color: #0E6656;
                cursor: pointer;
                display: inline-flex;
                font-size: 12px;
                font-weight: 800;
                gap: 7px;
                padding: 9px 12px;
                transition: background .16s ease, border-color .16s ease, color .16s ease, transform .16s ease;
            }

            .desk-filter:hover,
            .desk-mini-action:hover {
                border-color: rgba(14, 102, 86, .32);
                transform: translateY(-1px);
            }

            .desk-filter.is-active {
                background: #0E6656;
                border-color: #0E6656;
                color: #fff;
            }

            .desk-count {
                background: #E4F2EE;
                border-radius: 999px;
                color: #0E6656;
                font-size: 12px;
                font-weight: 900;
                padding: 6px 10px;
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
                position: relative;
                transition: border-color .18s ease, box-shadow .18s ease;
            }

            .desk-instrument::before {
                background: #A3B0AC;
                bottom: 0;
                content: "";
                left: 0;
                position: absolute;
                top: 0;
                width: 4px;
                z-index: 1;
            }

            .desk-instrument.priority-high::before {
                background: #D9A441;
            }

            .desk-instrument.priority-danger::before {
                background: #C7645A;
            }

            .desk-instrument.priority-success::before {
                background: #3B9E7C;
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
                padding-left: 20px;
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

            .desk-smart-note {
                background: #FFF8EA;
                border: 1px solid #F1D9A8;
                border-radius: 12px;
                color: #72521B;
                font-size: 13px;
                line-height: 1.45;
                margin-top: 10px;
                padding: 10px 12px;
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

            [data-theme="dark"] .desk-command {
                background:
                    radial-gradient(circle at top left, rgba(45, 212, 191, .08), transparent 34%),
                    linear-gradient(135deg, #122E28, #0F2823);
            }

            [data-theme="dark"] .desk-search,
            [data-theme="dark"] .desk-filter,
            [data-theme="dark"] .desk-mini-action,
            [data-theme="dark"] .desk-instrument-summary,
            [data-theme="dark"] .desk-info-item,
            [data-theme="dark"] .desk-review-card {
                background: #122E28;
                border-color: #1E4B41;
                color: #E2F5F0;
            }

            [data-theme="dark"] .desk-instrument-body {
                background: linear-gradient(180deg, #122E28, #0F2823);
            }

            [data-theme="dark"] .desk-question,
            [data-theme="dark"] .desk-info-item span {
                color: #E2F5F0;
            }

            [data-theme="dark"] .desk-subline {
                color: #A8C9C0;
            }

            [data-theme="dark"] .desk-smart-note {
                background: rgba(217, 164, 65, .12);
                border-color: rgba(217, 164, 65, .26);
                color: #F7D99A;
            }

            @media (max-width: 920px) {
                .desk-hero-main,
                .desk-standard-head,
                .desk-command-head {
                    align-items: stretch;
                    flex-direction: column;
                }

                .desk-command-grid {
                    grid-template-columns: 1fr;
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

    <div class="panel desk-command" data-desk-controls>
        <div class="desk-command-head">
            <div>
                <h3 class="panel-title desk-command-title">Pusat Pemeriksaan</h3>
                <p class="muted">Cari instrumen, fokuskan item penting, lalu buka hanya bagian yang sedang diperiksa.</p>
            </div>
            <span class="desk-count"><span data-desk-visible-count>{{ $summary['total'] }}</span> instrumen tampil</span>
        </div>

        <div class="desk-command-grid">
            <input class="desk-search" type="search" placeholder="Cari kode instrumen, pertanyaan, jawaban, atau catatan auditor..." data-desk-search>
            <div class="desk-action-bar">
                <button class="desk-mini-action" type="button" data-desk-action="open-visible">Buka tampil</button>
                <button class="desk-mini-action" type="button" data-desk-action="close-all">Tutup semua</button>
            </div>
        </div>

        <div class="desk-filter-bar" aria-label="Filter instrumen desk evaluation">
            <button class="desk-filter is-active" type="button" data-desk-filter="all">Semua</button>
            <button class="desk-filter" type="button" data-desk-filter="attention">Butuh perhatian</button>
            <button class="desk-filter" type="button" data-desk-filter="unstarted">Belum diperiksa</button>
            <button class="desk-filter" type="button" data-desk-filter="clarification">Klarifikasi</button>
            <button class="desk-filter" type="button" data-desk-filter="finding">Usulan temuan</button>
        </div>
    </div>

    @foreach ($standards as $group)
        @php
            $standardTotal = $group['evaluations']->count();
            $standardChecked = $group['evaluations']->where('status_pemeriksaan', '!=', 'belum_dimulai')->count();
            $standardClarifications = $group['evaluations']->where('status_bukti', 'perlu_klarifikasi')->count();
            $standardFindings = $group['evaluations']->where('usulan_temuan', true)->count();
        @endphp

        <div class="panel desk-standard" data-desk-standard>
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
                    $needsAttention = $evaluation->status_pemeriksaan === 'belum_dimulai'
                        || $evaluation->status_bukti === 'perlu_klarifikasi'
                        || $evaluation->usulan_temuan
                        || $evidenceCount === 0;
                    $priorityClass = $evaluation->status_pemeriksaan === 'final'
                        ? 'priority-success'
                        : ($evaluation->status_bukti === 'perlu_klarifikasi' || $evaluation->usulan_temuan ? 'priority-danger' : ($needsAttention ? 'priority-high' : ''));
                    $searchText = str($instrument->kode.' '.$instrument->nama_indikator.' '.$instrument->pertanyaan.' '.$assessment->jawaban_naratif.' '.$assessment->realisasi.' '.$evaluation->catatan_auditor)->lower()->toString();
                @endphp

                <details
                    class="desk-instrument {{ $priorityClass }}"
                    data-desk-item
                    data-status="{{ $evaluation->status_pemeriksaan }}"
                    data-evidence-status="{{ $evaluation->status_bukti }}"
                    data-has-finding="{{ $evaluation->usulan_temuan ? '1' : '0' }}"
                    data-has-attention="{{ $needsAttention ? '1' : '0' }}"
                    data-search="{{ e($searchText) }}"
                    @if ($shouldOpen) open @endif
                >
                    <summary class="desk-instrument-summary">
                        <div class="desk-instrument-title">
                            <span class="desk-number">{{ $instrument->kode }}</span>
                            <div>
                                <p class="desk-question">{{ $instrument->nama_indikator ?: $shortQuestion }}</p>
                                <div class="desk-subline">{{ $instrument->nama_indikator ? $shortQuestion : $instrument->jenis_jawaban }}</div>
                                @if ($needsAttention)
                                    <div class="desk-smart-note">
                                        @if ($evaluation->status_bukti === 'perlu_klarifikasi')
                                            Prioritas: butir ini sedang menunggu klarifikasi auditee.
                                        @elseif ($evaluation->usulan_temuan)
                                            Prioritas: butir ini sudah ditandai sebagai usulan temuan.
                                        @elseif ($evaluation->status_pemeriksaan === 'belum_dimulai')
                                            Prioritas: penilaian auditor belum dimulai.
                                        @elseif ($evidenceCount === 0)
                                            Prioritas: belum ada bukti pendukung dari auditee.
                                        @endif
                                    </div>
                                @endif
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

@push('scripts')
    <script>
        (() => {
            const controls = document.querySelector('[data-desk-controls]');
            if (!controls) return;

            const items = Array.from(document.querySelectorAll('[data-desk-item]'));
            const standards = Array.from(document.querySelectorAll('[data-desk-standard]'));
            const search = controls.querySelector('[data-desk-search]');
            const count = controls.querySelector('[data-desk-visible-count]');
            const filters = Array.from(controls.querySelectorAll('[data-desk-filter]'));
            let activeFilter = 'all';

            const matchesFilter = (item) => {
                if (activeFilter === 'all') return true;
                if (activeFilter === 'attention') return item.dataset.hasAttention === '1';
                if (activeFilter === 'unstarted') return item.dataset.status === 'belum_dimulai';
                if (activeFilter === 'clarification') return item.dataset.evidenceStatus === 'perlu_klarifikasi';
                if (activeFilter === 'finding') return item.dataset.hasFinding === '1';
                return true;
            };

            const applyFilters = () => {
                const term = (search?.value || '').trim().toLowerCase();
                let visible = 0;

                items.forEach((item) => {
                    const matchSearch = !term || (item.dataset.search || '').includes(term);
                    const show = matchSearch && matchesFilter(item);
                    item.hidden = !show;
                    if (show) visible++;
                });

                standards.forEach((standard) => {
                    const hasVisible = Array.from(standard.querySelectorAll('[data-desk-item]')).some((item) => !item.hidden);
                    standard.hidden = !hasVisible;
                });

                if (count) count.textContent = visible;
            };

            filters.forEach((button) => {
                button.addEventListener('click', () => {
                    activeFilter = button.dataset.deskFilter || 'all';
                    filters.forEach((item) => item.classList.toggle('is-active', item === button));
                    applyFilters();

                    if (activeFilter !== 'all') {
                        items.filter((item) => !item.hidden && item.dataset.hasAttention === '1').slice(0, 3).forEach((item) => item.open = true);
                    }
                });
            });

            search?.addEventListener('input', applyFilters);

            controls.querySelector('[data-desk-action="open-visible"]')?.addEventListener('click', () => {
                items.filter((item) => !item.hidden).forEach((item) => item.open = true);
            });

            controls.querySelector('[data-desk-action="close-all"]')?.addEventListener('click', () => {
                items.forEach((item) => item.open = false);
            });

            applyFilters();
        })();
    </script>
@endpush
