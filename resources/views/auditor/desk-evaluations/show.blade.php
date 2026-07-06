@extends('layouts.app')

@section('title', 'Desk Evaluation Unit - SMART SIAMI')
@section('page_title', 'Desk Evaluation Unit')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    <div class="panel">
        <div class="toolbar">
            <div>
                <h3 class="panel-title">{{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}</h3>
                <p class="muted">{{ $assignment->auditPeriod->nama }}</p>
            </div>

            @if ($canFinalize && ! $isFinalized)
                <form method="post" action="{{ route('auditor.desk-evaluation.finalize', $assignment) }}" onsubmit="return confirm('Finalisasi desk evaluation? Catatan auditor akan dikunci.');">
                    @csrf
                    <button type="submit">Finalisasi Desk Evaluation</button>
                </form>
            @endif
        </div>

        @if ($isFinalized)
            <div class="status">Desk evaluation sudah final. Catatan auditor tidak dapat diubah dari halaman ini.</div>
        @endif

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
        <div class="panel">
            <h3 class="panel-title">{{ $group['standard']->kode }} - {{ $group['standard']->nama }}</h3>

            @foreach ($group['evaluations'] as $evaluation)
                @php
                    $assessment = $evaluation->selfAssessment;
                    $instrument = $evaluation->instrument;
                    $readOnly = $isFinalized;
                @endphp

                <div class="section-block">
                    <div class="toolbar">
                        <div>
                            <h4 class="panel-title">{{ $instrument->kode }} · {{ $instrument->nama_indikator ?? 'Instrumen Audit' }}</h4>
                            <p class="muted">{{ $statusPemeriksaanOptions[$evaluation->status_pemeriksaan] }}</p>
                        </div>
                        <span class="badge @if ($evaluation->status_pemeriksaan === 'belum_dimulai') off @endif">{{ $statusPemeriksaanOptions[$evaluation->status_pemeriksaan] }}</span>
                    </div>

                    <div class="split-panel">
                        <div>
                            <div class="table-wrap">
                                <table>
                                    <tbody>
                                        <tr>
                                            <th>Pertanyaan</th>
                                            <td>{{ $instrument->pertanyaan }}</td>
                                        </tr>
                                        <tr>
                                            <th>Jawaban Auditee</th>
                                            <td>{{ $assessment->jawaban_naratif ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Realisasi</th>
                                            <td>{{ $assessment->realisasi ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Kendala</th>
                                            <td>{{ $assessment->kendala ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Analisis Gap</th>
                                            <td>{{ $assessment->analisis_gap ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Rencana Perbaikan Awal</th>
                                            <td>{{ $assessment->rencana_perbaikan_awal ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h4>Bukti Auditee</h4>
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

                        <div>
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
                                        <button type="submit">Simpan</button>
                                    </div>
                                @endunless
                            </form>

                            @unless ($readOnly)
                                <form method="post" action="{{ route('auditor.desk-evaluation.clarification', [$assignment, $evaluation]) }}" style="margin-top: 12px">
                                    @csrf
                                    <button class="button secondary" type="submit">Kirim Klarifikasi</button>
                                </form>
                            @endunless
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
@endsection
