@extends('layouts.app')

@section('title', 'Desk Evaluation Unit - SMART SIAMI')
@section('page_title', 'Desk Evaluation Unit')

@section('content')
    @once
        <style>
            .audit-arena {
                display: grid;
                gap: 18px;
            }

            .arena-hero {
                background:
                    radial-gradient(circle at top left, rgba(14, 102, 86, .14), transparent 34%),
                    linear-gradient(135deg, #ffffff, #f7fbfa 54%, #fff8ea);
                border: 1px solid rgba(14, 102, 86, .12);
                overflow: hidden;
                position: relative;
            }

            .arena-hero::before {
                background-image:
                    linear-gradient(rgba(14, 102, 86, .055) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(14, 102, 86, .055) 1px, transparent 1px);
                background-size: 26px 26px;
                bottom: 0;
                content: "";
                left: 0;
                opacity: .7;
                position: absolute;
                right: 0;
                top: 0;
            }

            .arena-hero > * {
                position: relative;
                z-index: 1;
            }

            .arena-hero-top {
                align-items: center;
                display: grid;
                gap: 18px;
                grid-template-columns: minmax(0, 1fr) auto;
            }

            .arena-kicker {
                color: #0E6656;
                font-size: 12px;
                font-weight: 900;
                letter-spacing: .12em;
                text-transform: uppercase;
            }

            .arena-title {
                color: #1F2C29;
                font-size: clamp(24px, 3vw, 38px);
                line-height: 1.04;
                margin: 6px 0 8px;
            }

            .arena-subtitle {
                color: #6B7B76;
                margin: 0;
                max-width: 760px;
            }

            .arena-score {
                align-items: center;
                background: #fff;
                border: 1px solid #dcebe7;
                border-radius: 22px;
                box-shadow: 0 18px 44px rgba(14, 102, 86, .10);
                display: grid;
                height: 132px;
                justify-items: center;
                min-width: 148px;
                padding: 18px;
            }

            .arena-score strong {
                color: #0E6656;
                font-size: 44px;
                line-height: 1;
            }

            .arena-score span {
                color: #6B7B76;
                font-size: 12px;
                font-weight: 800;
                text-transform: uppercase;
            }

            .arena-track {
                background: rgba(255, 255, 255, .78);
                border: 1px solid #dcebe7;
                border-radius: 999px;
                margin-top: 18px;
                overflow: hidden;
                padding: 5px;
            }

            .arena-track span {
                background: linear-gradient(90deg, #0E6656, #3D9C87, #E8B36A);
                border-radius: inherit;
                display: block;
                height: 12px;
                width: 0;
            }

            .arena-stats {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(5, minmax(0, 1fr));
                margin-top: 18px;
            }

            .arena-stat {
                background: rgba(255, 255, 255, .82);
                border: 1px solid #e5e7e0;
                border-radius: 16px;
                padding: 14px;
            }

            .arena-stat span {
                color: #6B7B76;
                display: block;
                font-size: 12px;
                font-weight: 800;
                text-transform: uppercase;
            }

            .arena-stat strong {
                color: #1F2C29;
                display: block;
                font-size: 28px;
                line-height: 1;
                margin-top: 6px;
            }

            .mission-console {
                align-items: end;
                display: grid;
                gap: 12px;
                grid-template-columns: minmax(240px, 1fr) auto;
            }

            .mission-search {
                background: #fff;
                border: 1px solid #dcebe7;
                border-radius: 14px;
                color: #1F2C29;
                font: inherit;
                padding: 13px 14px;
                width: 100%;
            }

            .mission-actions,
            .mission-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .mission-filter,
            .mission-action {
                background: #fff;
                border: 1px solid #dcebe7;
                border-radius: 999px;
                color: #0E6656;
                cursor: pointer;
                font-size: 12px;
                font-weight: 900;
                padding: 9px 12px;
                transition: background .16s ease, color .16s ease, transform .16s ease;
            }

            .mission-filter:hover,
            .mission-action:hover {
                transform: translateY(-1px);
            }

            .mission-filter.is-active {
                background: #0E6656;
                color: #fff;
            }

            .standard-world {
                display: grid;
                gap: 14px;
            }

            .world-header {
                align-items: center;
                display: grid;
                gap: 14px;
                grid-template-columns: minmax(0, 1fr) auto;
            }

            .world-title {
                margin: 0;
            }

            .world-meta {
                color: #6B7B76;
                display: flex;
                flex-wrap: wrap;
                font-size: 13px;
                gap: 9px;
                margin-top: 5px;
            }

            .world-badge {
                background: #E4F2EE;
                border-radius: 999px;
                color: #0E6656;
                font-size: 12px;
                font-weight: 900;
                padding: 7px 11px;
            }

            .mission-grid {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            }

            .mission-card {
                background: #fff;
                border: 1px solid #e5e7e0;
                border-radius: 18px;
                box-shadow: 0 10px 28px rgba(14, 102, 86, .055);
                display: grid;
                min-height: 214px;
                overflow: hidden;
                position: relative;
                transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
            }

            .mission-card::before {
                background: #a3b0ac;
                content: "";
                height: 5px;
                left: 0;
                position: absolute;
                right: 0;
                top: 0;
            }

            .mission-card.priority-warning::before {
                background: #d9a441;
            }

            .mission-card.priority-danger::before {
                background: #c7645a;
            }

            .mission-card.priority-success::before {
                background: #3b9e7c;
            }

            .mission-card[open] {
                border-color: rgba(14, 102, 86, .35);
                box-shadow: 0 18px 48px rgba(14, 102, 86, .12);
                grid-column: 1 / -1;
                transform: translateY(-1px);
            }

            .mission-summary {
                cursor: pointer;
                display: grid;
                gap: 12px;
                grid-template-rows: auto 1fr auto;
                list-style: none;
                min-height: 214px;
                padding: 18px;
                padding-top: 22px;
            }

            .mission-summary::-webkit-details-marker {
                display: none;
            }

            .mission-topline {
                align-items: center;
                display: flex;
                gap: 8px;
                justify-content: space-between;
            }

            .mission-code {
                background: #f7fbfa;
                border: 1px solid #dcebe7;
                border-radius: 11px;
                color: #0E6656;
                font-size: 12px;
                font-weight: 900;
                padding: 7px 9px;
            }

            .mission-level {
                color: #6B7B76;
                font-size: 11px;
                font-weight: 900;
                letter-spacing: .08em;
                text-transform: uppercase;
            }

            .mission-title {
                color: #1F2C29;
                font-size: 17px;
                line-height: 1.28;
                margin: 0;
            }

            .mission-desc {
                color: #6B7B76;
                font-size: 13px;
                line-height: 1.45;
                margin: 8px 0 0;
            }

            .mission-tags {
                display: flex;
                flex-wrap: wrap;
                gap: 7px;
            }

            .mission-chip {
                background: #f8faf9;
                border: 1px solid #e5e7e0;
                border-radius: 999px;
                color: #6B7B76;
                font-size: 11px;
                font-weight: 800;
                padding: 6px 8px;
            }

            .mission-chip.attention {
                background: #fff8ea;
                border-color: #f1d9a8;
                color: #72521b;
            }

            .mission-playfield {
                background: linear-gradient(180deg, #ffffff, #fbfdfc);
                border-top: 1px solid #e5e7e0;
                display: grid;
                gap: 18px;
                grid-template-columns: minmax(0, 1.08fr) minmax(300px, .92fr);
                padding: 18px;
            }

            .mission-lane {
                display: grid;
                gap: 12px;
            }

            .info-tile {
                background: #fff;
                border: 1px solid #e5e7e0;
                border-radius: 14px;
                padding: 13px;
            }

            .info-tile strong {
                color: #0E6656;
                display: block;
                font-size: 12px;
                font-weight: 900;
                margin-bottom: 5px;
                text-transform: uppercase;
            }

            .info-tile span {
                color: #1F2C29;
                line-height: 1.55;
            }

            .review-console {
                background:
                    radial-gradient(circle at top right, rgba(232, 179, 106, .14), transparent 30%),
                    #fff;
                border: 1px solid #dcebe7;
                border-radius: 18px;
                padding: 16px;
            }

            .review-console .panel-title {
                margin-bottom: 12px;
            }

            .review-console .actions button,
            .review-console .button {
                justify-content: center;
                width: 100%;
            }

            .evidence-heading {
                align-items: center;
                display: flex;
                justify-content: space-between;
                margin-top: 6px;
            }

            .evidence-heading h4 {
                margin: 0;
            }

            [data-theme="dark"] .arena-hero,
            [data-theme="dark"] .mission-card,
            [data-theme="dark"] .mission-summary,
            [data-theme="dark"] .mission-playfield,
            [data-theme="dark"] .info-tile,
            [data-theme="dark"] .review-console,
            [data-theme="dark"] .mission-search,
            [data-theme="dark"] .mission-filter,
            [data-theme="dark"] .mission-action {
                background: #122E28;
                border-color: #1E4B41;
                color: #E2F5F0;
            }

            [data-theme="dark"] .arena-title,
            [data-theme="dark"] .mission-title,
            [data-theme="dark"] .info-tile span {
                color: #E2F5F0;
            }

            [data-theme="dark"] .arena-subtitle,
            [data-theme="dark"] .mission-desc,
            [data-theme="dark"] .mission-level,
            [data-theme="dark"] .world-meta {
                color: #A8C9C0;
            }

            @media (max-width: 980px) {
                .arena-hero-top,
                .mission-console,
                .world-header,
                .mission-playfield {
                    grid-template-columns: 1fr;
                }

                .arena-score {
                    align-items: center;
                    grid-template-columns: auto 1fr;
                    height: auto;
                    justify-items: start;
                    min-width: 0;
                }

                .arena-stats {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 640px) {
                .arena-stats {
                    grid-template-columns: 1fr;
                }

                .mission-card[open] {
                    grid-column: auto;
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

    <div class="audit-arena">
        <section class="panel arena-hero">
            <div class="arena-hero-top">
                <div>
                    <div class="arena-kicker">Audit Mission Board</div>
                    <h2 class="arena-title">{{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}</h2>
                    <p class="arena-subtitle">{{ $assignment->auditPeriod->nama }}. Lead auditor: {{ $assignment->leadAuditor->name }}.</p>
                </div>

                <div class="arena-score">
                    <strong>{{ $progress }}%</strong>
                    <span>Progress</span>
                </div>
            </div>

            <div class="arena-track" aria-hidden="true">
                <span style="width: {{ $progress }}%"></span>
            </div>

            @if ($isFinalized)
                <div class="status" style="margin-top: 16px">Desk evaluation sudah final. Catatan auditor tidak dapat diubah dari halaman ini.</div>
            @endif

            <div class="arena-stats">
                <div class="arena-stat">
                    <span>Total Misi</span>
                    <strong>{{ $summary['total'] }}</strong>
                </div>
                <div class="arena-stat">
                    <span>Selesai Dicek</span>
                    <strong>{{ $summary['checked'] }}</strong>
                </div>
                <div class="arena-stat">
                    <span>Bukti Valid</span>
                    <strong>{{ $summary['valid_evidences'] }}</strong>
                </div>
                <div class="arena-stat">
                    <span>Klarifikasi</span>
                    <strong>{{ $summary['clarification_evidences'] }}</strong>
                </div>
                <div class="arena-stat">
                    <span>Usulan Temuan</span>
                    <strong>{{ $summary['proposed_findings'] }}</strong>
                </div>
            </div>

            <div class="mission-console" data-mission-console style="margin-top: 18px">
                <input class="mission-search" type="search" placeholder="Cari misi audit, kode instrumen, jawaban, bukti, atau catatan..." data-mission-search>
                <div class="mission-actions">
                    @if ($canFinalize && ! $isFinalized)
                        <form method="post" action="{{ route('auditor.desk-evaluation.finalize', $assignment) }}" onsubmit="return confirm('Finalisasi desk evaluation? Catatan auditor akan dikunci.');">
                            @csrf
                            <button class="with-icon" type="submit"><x-ui-icon name="check" /> Finalisasi</button>
                        </form>
                    @else
                        <span class="badge @if (! $isFinalized) off @endif">{{ $isFinalized ? 'Final' : 'Belum Siap Final' }}</span>
                    @endif
                    <button class="mission-action" type="button" data-mission-action="focus">Fokus Prioritas</button>
                    <button class="mission-action" type="button" data-mission-action="close">Tutup Semua</button>
                </div>
            </div>

            <div class="mission-filters" aria-label="Filter mission board" style="margin-top: 12px">
                <button class="mission-filter is-active" type="button" data-mission-filter="all">Semua</button>
                <button class="mission-filter" type="button" data-mission-filter="attention">Prioritas</button>
                <button class="mission-filter" type="button" data-mission-filter="unstarted">Belum Dicek</button>
                <button class="mission-filter" type="button" data-mission-filter="clarification">Klarifikasi</button>
                <button class="mission-filter" type="button" data-mission-filter="finding">Temuan</button>
                <button class="mission-filter" type="button" data-mission-filter="no-evidence">Tanpa Bukti</button>
            </div>
        </section>

        @foreach ($standards as $group)
            @php
                $standardTotal = $group['evaluations']->count();
                $standardChecked = $group['evaluations']->where('status_pemeriksaan', '!=', 'belum_dimulai')->count();
                $standardClarifications = $group['evaluations']->where('status_bukti', 'perlu_klarifikasi')->count();
                $standardFindings = $group['evaluations']->where('usulan_temuan', true)->count();
                $standardProgress = $standardTotal > 0 ? (int) round(($standardChecked / $standardTotal) * 100) : 0;
            @endphp

            <section class="panel standard-world" data-standard-world>
                <div class="world-header">
                    <div>
                        <h3 class="panel-title world-title">{{ $group['standard']->kode }} - {{ $group['standard']->nama }}</h3>
                        <div class="world-meta">
                            <span>{{ $standardChecked }}/{{ $standardTotal }} misi dicek</span>
                            <span>{{ $standardClarifications }} klarifikasi</span>
                            <span>{{ $standardFindings }} usulan temuan</span>
                        </div>
                    </div>
                    <span class="world-badge">{{ $standardProgress }}%</span>
                </div>

                <div class="mission-grid">
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
                            $needsAttention = $evaluation->status_pemeriksaan === 'belum_dimulai'
                                || $evaluation->status_bukti === 'perlu_klarifikasi'
                                || $evaluation->usulan_temuan
                                || $evidenceCount === 0;
                            $priorityClass = $evaluation->status_pemeriksaan === 'final'
                                ? 'priority-success'
                                : ($evaluation->status_bukti === 'perlu_klarifikasi' || $evaluation->usulan_temuan ? 'priority-danger' : ($needsAttention ? 'priority-warning' : ''));
                            $shortQuestion = str($instrument->pertanyaan)->limit(135);
                            $missionTitle = $instrument->nama_indikator ?: $shortQuestion;
                            $searchText = str($instrument->kode.' '.$instrument->nama_indikator.' '.$instrument->pertanyaan.' '.$assessment->jawaban_naratif.' '.$assessment->realisasi.' '.$assessment->kendala.' '.$assessment->analisis_gap.' '.$evaluation->catatan_auditor)->lower()->toString();
                        @endphp

                        <details
                            class="mission-card {{ $priorityClass }}"
                            data-mission-card
                            data-status="{{ $evaluation->status_pemeriksaan }}"
                            data-evidence-status="{{ $evaluation->status_bukti }}"
                            data-has-finding="{{ $evaluation->usulan_temuan ? '1' : '0' }}"
                            data-has-attention="{{ $needsAttention ? '1' : '0' }}"
                            data-has-evidence="{{ $evidenceCount > 0 ? '1' : '0' }}"
                            data-search="{{ e($searchText) }}"
                            @if ($shouldOpen) open @endif
                        >
                            <summary class="mission-summary">
                                <div class="mission-topline">
                                    <span class="mission-code">{{ $instrument->kode }}</span>
                                    <span class="mission-level">{{ $statusPemeriksaanOptions[$evaluation->status_pemeriksaan] }}</span>
                                </div>

                                <div>
                                    <h4 class="mission-title">{{ $missionTitle }}</h4>
                                    <p class="mission-desc">{{ $instrument->nama_indikator ? $shortQuestion : ucfirst($instrument->jenis_jawaban) }}</p>
                                </div>

                                <div class="mission-tags">
                                    <span class="badge {{ $statusTone }}">{{ $statusPemeriksaanOptions[$evaluation->status_pemeriksaan] }}</span>
                                    <span class="mission-chip">{{ $evidenceCount }} bukti</span>
                                    @if ($evaluation->status_bukti === 'perlu_klarifikasi')
                                        <span class="mission-chip attention">Klarifikasi</span>
                                    @endif
                                    @if ($evaluation->usulan_temuan)
                                        <span class="mission-chip attention">Temuan</span>
                                    @endif
                                    @if ($evidenceCount === 0)
                                        <span class="mission-chip attention">Tanpa Bukti</span>
                                    @endif
                                </div>
                            </summary>

                            <div class="mission-playfield">
                                <div class="mission-lane">
                                    <div class="info-tile">
                                        <strong>Pertanyaan</strong>
                                        <span>{{ $instrument->pertanyaan }}</span>
                                    </div>
                                    <div class="info-tile">
                                        <strong>Jawaban Auditee</strong>
                                        <span>{{ $assessment->jawaban_naratif ?? '-' }}</span>
                                    </div>
                                    <div class="info-tile">
                                        <strong>Realisasi</strong>
                                        <span>{{ $assessment->realisasi ?? '-' }}</span>
                                    </div>
                                    <div class="info-tile">
                                        <strong>Kendala</strong>
                                        <span>{{ $assessment->kendala ?? '-' }}</span>
                                    </div>
                                    <div class="info-tile">
                                        <strong>Analisis Gap</strong>
                                        <span>{{ $assessment->analisis_gap ?? '-' }}</span>
                                    </div>
                                    <div class="info-tile">
                                        <strong>Rencana Perbaikan Awal</strong>
                                        <span>{{ $assessment->rencana_perbaikan_awal ?? '-' }}</span>
                                    </div>

                                    <div class="evidence-heading">
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

                                <aside class="review-console">
                                    <h4 class="panel-title">Panel Auditor</h4>
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
                                                <button class="with-icon" type="submit"><x-ui-icon name="save" /> Simpan Penilaian</button>
                                            </div>
                                        @endunless
                                    </form>

                                    @unless ($readOnly)
                                        <form method="post" action="{{ route('auditor.desk-evaluation.clarification', [$assignment, $evaluation]) }}" style="margin-top: 12px">
                                            @csrf
                                            <button class="button secondary with-icon" type="submit"><x-ui-icon name="message" /> Kirim Klarifikasi</button>
                                        </form>
                                    @endunless
                                </aside>
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const consoleEl = document.querySelector('[data-mission-console]');
            if (!consoleEl) return;

            const cards = Array.from(document.querySelectorAll('[data-mission-card]'));
            const worlds = Array.from(document.querySelectorAll('[data-standard-world]'));
            const search = consoleEl.querySelector('[data-mission-search]');
            const filters = Array.from(document.querySelectorAll('[data-mission-filter]'));
            let activeFilter = 'all';

            const matchesFilter = (card) => {
                if (activeFilter === 'all') return true;
                if (activeFilter === 'attention') return card.dataset.hasAttention === '1';
                if (activeFilter === 'unstarted') return card.dataset.status === 'belum_dimulai';
                if (activeFilter === 'clarification') return card.dataset.evidenceStatus === 'perlu_klarifikasi';
                if (activeFilter === 'finding') return card.dataset.hasFinding === '1';
                if (activeFilter === 'no-evidence') return card.dataset.hasEvidence === '0';
                return true;
            };

            const apply = () => {
                const term = (search?.value || '').trim().toLowerCase();
                cards.forEach((card) => {
                    const matchSearch = !term || (card.dataset.search || '').includes(term);
                    card.hidden = !(matchSearch && matchesFilter(card));
                });

                worlds.forEach((world) => {
                    world.hidden = !Array.from(world.querySelectorAll('[data-mission-card]')).some((card) => !card.hidden);
                });
            };

            filters.forEach((filter) => {
                filter.addEventListener('click', () => {
                    activeFilter = filter.dataset.missionFilter || 'all';
                    filters.forEach((item) => item.classList.toggle('is-active', item === filter));
                    apply();
                });
            });

            search?.addEventListener('input', apply);

            document.querySelector('[data-mission-action="focus"]')?.addEventListener('click', () => {
                activeFilter = 'attention';
                filters.forEach((item) => item.classList.toggle('is-active', item.dataset.missionFilter === 'attention'));
                apply();
                const first = cards.find((card) => !card.hidden);
                if (first) {
                    cards.forEach((card) => card.open = false);
                    first.open = true;
                    first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });

            document.querySelector('[data-mission-action="close"]')?.addEventListener('click', () => {
                cards.forEach((card) => card.open = false);
            });

            cards.forEach((card) => {
                card.addEventListener('toggle', () => {
                    if (!card.open) return;
                    cards.forEach((item) => {
                        if (item !== card) item.open = false;
                    });
                });
            });

            apply();
        })();
    </script>
@endpush
