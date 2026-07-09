@extends('layouts.app')

@section('title', 'Verifikasi Perbaikan - SMART SIAMI')
@section('page_title', 'Verifikasi Perbaikan')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    <div class="split-panel">
        <div>
            <div class="panel">
                <div class="toolbar">
                    <div>
                        <h3 class="panel-title">{{ $finding->nomor_temuan }}</h3>
                        <p class="muted">{{ $followUp->assignment->unit->kode }} - {{ $followUp->assignment->unit->nama }} | {{ $followUp->assignment->auditPeriod->nama }}</p>
                    </div>
                    <span class="badge @if ($followUp->status !== 'diajukan') off @endif">{{ $statusOptions[$followUp->status] }}</span>
                </div>

                <div class="table-wrap">
                    <table>
                        <tbody>
                            <tr>
                                <th>Standar</th>
                                <td>{{ $finding->standard->kode }} - {{ $finding->standard->nama }}</td>
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
                                <th>Rekomendasi Auditor</th>
                                <td>{{ $finding->rekomendasi_auditor }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <h3 class="panel-title">Rencana Tindak Lanjut Auditee</h3>
                <div class="table-wrap">
                    <table>
                        <tbody>
                            <tr>
                                <th>Rencana Tindakan</th>
                                <td>{{ $followUp->rencana_tindakan }}</td>
                            </tr>
                            <tr>
                                <th>Penanggung Jawab</th>
                                <td>{{ $followUp->penanggung_jawab }}</td>
                            </tr>
                            <tr>
                                <th>Target</th>
                                <td>{{ $followUp->target_penyelesaian->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Indikator Keberhasilan</th>
                                <td>{{ $followUp->indikator_keberhasilan }}</td>
                            </tr>
                            <tr>
                                <th>Progres</th>
                                <td>{{ $progresOptions[$followUp->progres] }}</td>
                            </tr>
                            <tr>
                                <th>Kendala</th>
                                <td>{{ $followUp->kendala ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Catatan Auditee</th>
                                <td>{{ $followUp->catatan_auditee ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <h3 class="panel-title">Bukti Penyelesaian</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Tipe</th>
                                <th>Uploader</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($followUp->evidences as $evidence)
                                <tr>
                                    <td>{{ $evidence->nama_dokumen }}</td>
                                    <td>{{ $evidence->tipe_sumber === 'file' ? 'File' : 'Tautan' }}</td>
                                    <td>{{ $evidence->uploader->name }}</td>
                                    <td>
                                        <div class="table-actions">
                                            @if ($evidence->tipe_sumber === 'file')
                                                <x-action-icon :href="route('auditor.follow-up-verifications.evidences.download', $evidence)" icon="download" label="Unduh file" tone="view" />
                                            @else
                                                <x-action-icon :href="$evidence->url_tautan" icon="external" label="Buka tautan" tone="view" target="_blank" />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">Belum ada bukti.</td>
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
                            @forelse ($followUp->verifications->sortByDesc('waktu_verifikasi') as $verification)
                                <tr>
                                    <td>{{ $keputusanOptions[$verification->keputusan] }}</td>
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
                <h3 class="panel-title">Keputusan Verifikasi</h3>
                @if ($followUp->status === 'diajukan')
                    <form class="form-grid" method="post" action="{{ route('auditor.follow-up-verifications.verify', $followUp) }}">
                        @csrf
                        <div class="form-field full">
                            <label for="keputusan">Keputusan</label>
                            <select id="keputusan" name="keputusan" required>
                                @foreach ($keputusanOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field full">
                            <label for="catatan_verifikasi">Catatan Verifikasi</label>
                            <textarea id="catatan_verifikasi" name="catatan_verifikasi">{{ old('catatan_verifikasi') }}</textarea>
                        </div>
                        <button type="submit">Simpan Keputusan Verifikasi</button>
                    </form>
                @else
                    <p class="muted">Tindak lanjut ini sudah tidak menunggu verifikasi.</p>
                @endif
                <div class="actions" style="margin-top: 16px">
                    <a class="button secondary" href="{{ route('auditor.follow-up-verifications') }}">Kembali</a>
                </div>
            </div>
        </aside>
    </div>
@endsection
