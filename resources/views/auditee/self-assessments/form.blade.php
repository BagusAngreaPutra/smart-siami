@extends('layouts.app')

@php
    $instrument = $assessment->instrument;
    $typeLabel = \App\Models\Instrument::jenisJawabanOptions()[$instrument->jenis_jawaban] ?? $instrument->jenis_jawaban;
@endphp

@section('title', 'Isi Evaluasi Diri - SMART SIAMI')
@section('page_title', 'Isi Evaluasi Diri')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    @php
        $answerReady = filled($assessment->jawaban_naratif) || filled($assessment->realisasi);
        $evidenceReady = $assessment->evidences->isNotEmpty();
        $assessmentSent = in_array($assessment->status, ['dikirim', 'final'], true);
    @endphp
    <nav class="auditee-stage-track" aria-label="Tahap pengisian evaluasi diri">
        <span class="is-complete"><b>✓</b><em>Instrumen dibuka</em></span>
        <span class="{{ $answerReady ? 'is-complete' : 'is-current' }}"><b>{{ $answerReady ? '✓' : '2' }}</b><em>Lengkapi jawaban</em></span>
        <span class="{{ $evidenceReady ? 'is-complete' : ($answerReady ? 'is-current' : '') }}"><b>{{ $evidenceReady ? '✓' : '3' }}</b><em>Hubungkan bukti</em></span>
        <span class="{{ $assessmentSent ? 'is-complete' : ($evidenceReady ? 'is-current' : '') }}"><b>{{ $assessmentSent ? '✓' : '4' }}</b><em>Kirim instrumen</em></span>
    </nav>

    <div class="split-panel">
        <div>
            <div class="panel">
                <h3 class="panel-title">{{ $instrument->kode }} - {{ $instrument->standard->nama }}</h3>
                <p class="muted">Jenis jawaban: {{ $typeLabel }} · Status: {{ $statusOptions[$assessment->status] }}</p>

                <div class="table-wrap">
                    <table>
                        <tbody>
                            <tr>
                                <th>Pertanyaan</th>
                                <td>{{ $instrument->pertanyaan }}</td>
                            </tr>
                            <tr>
                                <th>Target/Kriteria</th>
                                <td>{{ $instrument->target_kriteria }}</td>
                            </tr>
                            <tr>
                                <th>Panduan</th>
                                <td>{{ $instrument->panduan_pengisian ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Bukti Diperlukan</th>
                                <td>{{ $instrument->bukti_diperlukan }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                @if (! $canEdit)
                    <div class="warning">Jawaban instrumen ini sedang terkunci. Instrumen hanya dapat diubah saat masih draft/belum diisi, atau ketika auditor meminta klarifikasi.</div>
                @endif

                <form class="form-grid" method="post" action="{{ route('auditee.self-assessments.draft', $assessment) }}">
                    @csrf
                    @method('patch')

                    <div class="form-field full">
                        <label for="jawaban_naratif">Jawaban Naratif</label>
                        @if ($instrument->jenis_jawaban === 'pilihan')
                            <select id="jawaban_naratif" name="jawaban_naratif" @disabled(! $canEdit)>
                                <option value="">Pilih jawaban</option>
                                @foreach (($instrument->opsi_jawaban ?? []) as $option)
                                    <option value="{{ $option }}" @selected(old('jawaban_naratif', $assessment->jawaban_naratif) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        @else
                            <textarea id="jawaban_naratif" name="jawaban_naratif" @disabled(! $canEdit)>{{ old('jawaban_naratif', $assessment->jawaban_naratif) }}</textarea>
                        @endif
                        @error('jawaban_naratif')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="realisasi">Realisasi</label>
                        <input id="realisasi" name="realisasi" type="{{ in_array($instrument->jenis_jawaban, ['angka', 'skor'], true) ? 'number' : 'text' }}" step="0.01" value="{{ old('realisasi', $assessment->realisasi) }}" @disabled(! $canEdit)>
                        @error('realisasi')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="target">Target</label>
                        <input id="target" value="{{ $assessment->target }}" disabled>
                    </div>

                    <div class="form-field full">
                        <label for="kendala">Kendala</label>
                        <textarea id="kendala" name="kendala" @disabled(! $canEdit)>{{ old('kendala', $assessment->kendala) }}</textarea>
                    </div>

                    <div class="form-field full">
                        <label for="analisis_gap">Analisis Gap</label>
                        <textarea id="analisis_gap" name="analisis_gap" @disabled(! $canEdit)>{{ old('analisis_gap', $assessment->analisis_gap) }}</textarea>
                    </div>

                    <div class="form-field full">
                        <label for="rencana_perbaikan_awal">Rencana Perbaikan Awal</label>
                        <textarea id="rencana_perbaikan_awal" name="rencana_perbaikan_awal" @disabled(! $canEdit)>{{ old('rencana_perbaikan_awal', $assessment->rencana_perbaikan_awal) }}</textarea>
                    </div>

                    <div class="form-field full actions">
                        @if ($canEdit)
                            <button type="submit">Simpan Draft</button>
                            <button type="submit" formaction="{{ route('auditee.self-assessments.submit', $assessment) }}">Tandai Selesai</button>
                        @endif
                        <a class="button secondary" href="{{ route('auditee.self-evaluations', ['assignment_id' => $assessment->assignment_id]) }}">Kembali</a>
                    </div>
                </form>

                @if ($assessment->status === 'dikirim')
                    <form method="post" action="{{ route('auditee.self-assessments.withdraw', $assessment) }}" style="margin-top: 12px">
                        @csrf
                        @method('patch')
                        <button class="button secondary" type="submit">Tarik Kembali</button>
                    </form>
                @endif

                <div class="actions" style="margin-top: 18px">
                    @if ($previous)
                        <a class="button secondary" href="{{ route('auditee.self-assessments.edit', $previous) }}">Sebelumnya</a>
                    @endif
                    @if ($next)
                        <a class="button secondary" href="{{ route('auditee.self-assessments.edit', $next) }}">Berikutnya</a>
                    @endif
                </div>
            </div>
        </div>

        <aside>
            <div class="panel">
                <h3 class="panel-title">Bukti Pendukung</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assessment->evidences as $evidence)
                                <tr>
                                    <td>{{ $evidence->nama_dokumen }}</td>
                                    <td>{{ \App\Models\Evidence::statusVerifikasiOptions()[$evidence->status_verifikasi] }}</td>
                                    <td>
                                        <div class="table-actions">
                                            @if ($evidence->tipe_sumber === 'file')
                                                <x-action-icon :href="route('auditee.documents.download', $evidence)" icon="download" label="Unduh file" tone="view" />
                                            @else
                                                <x-action-icon :href="$evidence->url_tautan" icon="external" label="Buka tautan" tone="view" target="_blank" />
                                            @endif
                                            @if ($canEdit && $evidence->status_verifikasi === 'belum_diperiksa')
                                                <x-action-icon
                                                    :action="route('auditee.self-assessments.evidences.destroy', $evidence)"
                                                    method="delete"
                                                    icon="trash"
                                                    label="Hapus bukti"
                                                    tone="danger"
                                                    :confirm="true"
                                                    confirm-title="Hapus bukti pendukung?"
                                                    confirm-message="Bukti yang belum diperiksa akan dihapus dari instrumen ini."
                                                    confirm-label="Ya, Hapus"
                                                />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">Belum ada bukti.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($canEdit)
                <div class="panel">
                    <h3 class="panel-title">Tambah Bukti</h3>
                    <form class="form-grid" method="post" action="{{ route('auditee.self-assessments.evidences.store', $assessment) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-field full">
                            <label for="nama_dokumen">Nama Dokumen</label>
                            <input id="nama_dokumen" name="nama_dokumen" required>
                        </div>
                        <div class="form-field full">
                            <label for="jenis_dokumen">Jenis Dokumen</label>
                            <input id="jenis_dokumen" name="jenis_dokumen">
                        </div>
                        <div class="form-field full">
                            <label for="tipe_sumber">Tipe Sumber</label>
                            <select id="tipe_sumber" name="tipe_sumber" required>
                                <option value="file">File</option>
                                <option value="tautan">Tautan</option>
                            </select>
                        </div>
                        <div class="form-field full">
                            <label for="files">File</label>
                            <input id="files" name="files[]" type="file" accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png" multiple>
                            <span class="muted">Anda dapat memilih lebih dari satu file sekaligus.</span>
                        </div>
                        <div class="form-field full">
                            <label for="url_tautan">URL Tautan</label>
                            <input id="url_tautan" name="url_tautan" type="url">
                        </div>
                        <div class="form-field full">
                            <label for="tahun_dokumen">Tahun Dokumen</label>
                            <input id="tahun_dokumen" name="tahun_dokumen" type="number" min="1900" max="2100">
                        </div>
                        <div class="form-field full">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi"></textarea>
                        </div>
                        <button type="submit">Tambah Bukti</button>
                    </form>
                </div>
            @endif
        </aside>
    </div>
@endsection
