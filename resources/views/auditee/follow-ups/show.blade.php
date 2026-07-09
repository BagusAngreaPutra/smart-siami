@extends('layouts.app')

@php
    $latestVerification = $followUp?->latestVerification;
@endphp

@section('title', 'Detail Tindak Lanjut - SMART SIAMI')
@section('page_title', 'Detail Tindak Lanjut')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    @if ($latestVerification && in_array($latestVerification->keputusan, ['perlu_perbaikan', 'ditolak'], true))
        <div class="warning">Catatan verifikasi auditor: {{ $latestVerification->catatan_verifikasi }}</div>
    @endif

    <div class="split-panel">
        <div>
            <div class="panel">
                <div class="toolbar">
                    <div>
                        <h3 class="panel-title">{{ $finding->nomor_temuan }}</h3>
                        <p class="muted">{{ $finding->standard->kode }} - {{ $finding->standard->nama }} | {{ $findingStatusOptions[$finding->status] }}</p>
                    </div>
                    <span class="badge @if (! $followUp || $followUp->status === 'draft') off @endif">{{ $followUp ? $followUpStatusOptions[$followUp->status] : $followUpStatusOptions['belum_dibuat'] }}</span>
                </div>

                <div class="table-wrap">
                    <table>
                        <tbody>
                            <tr>
                                <th>Kategori</th>
                                <td>{{ $kategoriOptions[$finding->kategori] }}</td>
                            </tr>
                            <tr>
                                <th>Instrumen</th>
                                <td>{{ $finding->instrument->kode }} - {{ $finding->instrument->pertanyaan }}</td>
                            </tr>
                            <tr>
                                <th>Kondisi Aktual</th>
                                <td>{{ $finding->kondisi_aktual }}</td>
                            </tr>
                            <tr>
                                <th>Kriteria</th>
                                <td>{{ $finding->kriteria }}</td>
                            </tr>
                            <tr>
                                <th>Bukti Objektif</th>
                                <td>{{ $finding->bukti_objektif }}</td>
                            </tr>
                            <tr>
                                <th>Rekomendasi Auditor</th>
                                <td>{{ $finding->rekomendasi_auditor }}</td>
                            </tr>
                            <tr>
                                <th>Target Temuan</th>
                                <td>{{ $finding->target_penyelesaian->format('d/m/Y') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <h3 class="panel-title">Rencana Tindak Lanjut</h3>
                <form class="form-grid" method="post" action="{{ route('auditee.findings-followups.save', $finding) }}">
                    @csrf
                    <div class="form-field full">
                        <label for="rencana_tindakan">Rencana Tindakan</label>
                        <textarea id="rencana_tindakan" name="rencana_tindakan" required @disabled(! $canEdit)>{{ old('rencana_tindakan', $followUp?->rencana_tindakan) }}</textarea>
                    </div>
                    <div class="form-field">
                        <label for="penanggung_jawab">Penanggung Jawab</label>
                        <input id="penanggung_jawab" name="penanggung_jawab" value="{{ old('penanggung_jawab', $followUp?->penanggung_jawab) }}" required @disabled(! $canEdit)>
                    </div>
                    <div class="form-field">
                        <label for="target_penyelesaian">Target Penyelesaian</label>
                        <input id="target_penyelesaian" name="target_penyelesaian" type="date" value="{{ old('target_penyelesaian', $followUp?->target_penyelesaian?->format('Y-m-d') ?? $finding->target_penyelesaian->format('Y-m-d')) }}" required @disabled(! $canEdit)>
                    </div>
                    <div class="form-field full">
                        <label for="indikator_keberhasilan">Indikator Keberhasilan</label>
                        <textarea id="indikator_keberhasilan" name="indikator_keberhasilan" required @disabled(! $canEdit)>{{ old('indikator_keberhasilan', $followUp?->indikator_keberhasilan) }}</textarea>
                    </div>
                    <div class="form-field">
                        <label for="progres">Progres</label>
                        <select id="progres" name="progres" required @disabled(! $canEdit)>
                            @foreach ($progresOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('progres', $followUp?->progres ?? 'belum_mulai') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field full">
                        <label for="kendala">Kendala</label>
                        <textarea id="kendala" name="kendala" @disabled(! $canEdit)>{{ old('kendala', $followUp?->kendala) }}</textarea>
                    </div>
                    <div class="form-field full">
                        <label for="catatan_auditee">Catatan Auditee</label>
                        <textarea id="catatan_auditee" name="catatan_auditee" @disabled(! $canEdit)>{{ old('catatan_auditee', $followUp?->catatan_auditee) }}</textarea>
                    </div>
                    @if ($canEdit)
                        <div class="form-field full actions">
                            <button type="submit">Simpan Draft Tindak Lanjut</button>
                        </div>
                    @endif
                </form>
            </div>

            <div class="panel">
                <h3 class="panel-title">Bukti Penyelesaian</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Tipe</th>
                                <th>Tahun</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($followUp?->evidences ?? [] as $evidence)
                                <tr>
                                    <td>{{ $evidence->nama_dokumen }}</td>
                                    <td>{{ $evidence->tipe_sumber === 'file' ? 'File' : 'Tautan' }}</td>
                                    <td>{{ $evidence->tahun_dokumen ?? '-' }}</td>
                                    <td>
                                        <div class="table-actions">
                                            @if ($evidence->tipe_sumber === 'file')
                                                <x-action-icon :href="route('auditee.findings-followups.evidences.download', $evidence)" icon="download" label="Unduh file" tone="view" />
                                            @else
                                                <x-action-icon :href="$evidence->url_tautan" icon="external" label="Buka tautan" tone="view" target="_blank" />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">Belum ada bukti penyelesaian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <h3 class="panel-title">Riwayat Verifikasi</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Keputusan</th>
                                <th>Catatan</th>
                                <th>Verifikator</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($followUp?->verifications?->sortByDesc('waktu_verifikasi') ?? [] as $verification)
                                <tr>
                                    <td>{{ \App\Models\FollowUpVerification::keputusanOptions()[$verification->keputusan] }}</td>
                                    <td>{{ $verification->catatan_verifikasi ?? '-' }}</td>
                                    <td>{{ $verification->verifier->name }}</td>
                                    <td>{{ $verification->waktu_verifikasi->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">Belum ada verifikasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <aside>
            <div class="panel">
                <h3 class="panel-title">Aksi</h3>
                <div class="actions">
                    <a class="button secondary" href="{{ route('auditee.findings-followups') }}">Kembali</a>
                    @if ($followUp && $canEdit)
                        <form method="post" action="{{ route('auditee.findings-followups.submit', $finding) }}">
                            @csrf
                            <button type="submit">Ajukan Verifikasi</button>
                        </form>
                    @endif
                </div>
            </div>

            @if ($followUp && $canEdit)
                <div class="panel">
                    <h3 class="panel-title">Tambah Bukti</h3>
                    <form class="form-grid" method="post" action="{{ route('auditee.findings-followups.evidences.store', $finding) }}" enctype="multipart/form-data">
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
                            <label for="file">File</label>
                            <input id="file" name="file" type="file" accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png">
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
